<?php declare(strict_types=1);

/*
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;
use XoopsModules\Xhelp\Ticket;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';

global $xoopsUser, $xoopsDB, $xoopsConfig, $xoopsModuleConfig, $xoopsModule, $xhelp_isStaff, $staff;

$helper       = Xhelp\Helper::getInstance();
$eventService = Xhelp\EventService::getInstance();

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php', 3);
}

$refresh = Request::getInt('refresh', 0, 'GET');

$uid = $xoopsUser->getVar('uid');

// Get the id of the ticket
if (Request::hasVar('id', 'GET')) {
    $ticketid = Request::getInt('id', 0, 'GET');
}

if (Request::hasVar('responseid', 'GET')) {
    $responseid = Request::getInt('responseid', 0, 'GET');
}

/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler            = $helper->getHandler('Ticket');
$responseTemplatesHandler = $helper->getHandler('ResponseTemplates');
/** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
$membershipHandler = $helper->getHandler('Membership');
/** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
$responseHandler = $helper->getHandler('Response');
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');

if (!$ticketInfo = $ticketHandler->get($ticketid)) {
    //Invalid ticketID specified
    $helper->redirect('index.php', 3, _XHELP_ERROR_INV_TICKET);
}

$has_owner = $ticketInfo->getVar('ownership');

$op = 'staffFrm'; //Default Action for page

if (Request::hasVar('op', 'GET')) {
    $op = $_GET['op'];
}

if (Request::hasVar('op', 'POST')) {
    $op = $_POST['op'];
}

switch ($op) {
    case 'staffAdd':
        //0. Check that the user can perform this action
        $message    = '';
        $url        = XHELP_BASE_URL . '/index.php';
        $hasErrors  = false;
        $errors     = [];
        $uploadFile = $ticketReopen = $changeOwner = $ticketClosed = $newStatus = false;

        if ($xhelp_isStaff) {
            // Check if staff has permission to respond to the ticket
            /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
            $ticketEmailsHandler = $helper->getHandler('TicketEmails');
            $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
            $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
            $ticketEmails = $ticketEmailsHandler->getObjects($criteria);
            if (count($ticketEmails) > 0
                || $staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))) {
                //1. Verify Response fields are filled properly
                // require_once XHELP_CLASS_PATH . '/validator.php';
                $v                = [];
                $v['response'][]  = new Validation\ValidateLength(Request::getString('response', '', 'POST'), 2, 50000);
                $v['timespent'][] = new Validation\ValidateNumber(Request::getString('timespent', '', 'POST'));

                if ($helper->getConfig('xhelp_allowUpload') && is_uploaded_file(($_FILES['userfile']['tmp_name'])??'')) {
                    /** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
                    $mimetypeHandler = $helper->getHandler('Mimetype');
                    //Add File Upload Validation Rules
                    $v['userfile'][] = new Validation\ValidateMimeType($_FILES['userfile']['name'], $_FILES['userfile']['type'], $mimetypeHandler->getArray());
                    $v['userfile'][] = new Validation\ValidateFileSize($_FILES['userfile']['tmp_name'], (int)$helper->getConfig('xhelp_uploadSize'));
                    $v['userfile'][] = new Validation\ValidateImageSize($_FILES['userfile']['tmp_name'], (int)$helper->getConfig('xhelp_uploadWidth'), (int)$helper->getConfig('xhelp_uploadHeight'));
                    $uploadFile      = true;
                }

                // Perform each validation
                $fields = [];
                $errors = [];
                foreach ($v as $fieldname => $validator) {
                    if (Xhelp\Utility::checkRules($validator, $errors)) {
                        $fields[$fieldname]['haserrors'] = false;
                    } else {
                        $hasErrors = true;
                        //Mark field with error
                        $fields[$fieldname]['haserrors'] = true;
                        $fields[$fieldname]['errors']    = $errors;
                    }
                }

                if ($hasErrors) {
                    //Store field values in session
                    //Store error messages in session
                    setResponseToSession($ticketInfo, $fields);
                    //redirect to response.php?op=staffFrm
                    $helper->redirect("response.php?op=staffFrm&id=$ticketid");
                }

                //Check if status changed
                if ($_POST['status'] <> $ticketInfo->getVar('status')) {
                    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
                    $statusHandler = $helper->getHandler('Status');
                    $oldStatus     = $statusHandler->get($ticketInfo->getVar('status'));
                    $newStatus     = $statusHandler->get(Request::getInt('status', 0, 'POST'));

                    if (1 == $oldStatus->getVar('state') && 2 === $newStatus->getVar('state')) {
                        $ticketClosed = true;
                    } elseif (2 == $oldStatus->getVar('state') && 1 === $newStatus->getVar('state')) {
                        $ticketReopen = true;
                    }
                    $ticketInfo->setVar('status', Request::getInt('status', 0, 'POST'));
                }

                //Check if user claimed ownership
                if (Request::hasVar('claimOwner', 'POST') && $staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
                    $ownerid = Request::getInt('claimOwner', 0, 'POST');
                    if ($ownerid > 0) {
                        $oldOwner = (int)$ticketInfo->getVar('ownership');
                        $ticketInfo->setVar('ownership', $ownerid);
                        $changeOwner = true;
                    }
                }

                //2. Fill Response Object
                /** @var \XoopsModules\Xhelp\Response $response */
                $response = $responseHandler->create();
                $response->setVar('uid', $xoopsUser->getVar('uid'));
                $response->setVar('ticketid', $ticketid);
                $response->setVar('message', \Xmf\Request::getString('response', '', 'POST'));
                $response->setVar('timeSpent', $_POST['timespent']);
                $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));
                $response->setVar('userIP', getenv('REMOTE_ADDR'));
                if (Request::hasVar('private', 'POST')) {
                    $response->setVar('private', $_POST['private']);
                }

                //3. Store Response Object in DB
                if ($responseHandler->insert($response)) {
                    $eventService->trigger('new_response', [&$ticketInfo, &$response]);
                } else {
                    //Store response fields in session
                    setResponseToSession($ticketInfo, $fields);
                    //Notify user of error (using redirect_header())'
                    $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE_ERROR);
                }

                //4. Update Ticket object
                if (Request::hasVar('timespent', 'POST')) {
                    $oldspent = $ticketInfo->getVar('totalTimeSpent');
                    $ticketInfo->setVar('totalTimeSpent', $oldspent + Request::getInt('timespent', 0, 'POST'));
                }
                if ($ticketClosed) {
                    $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                }
                $ticketInfo->setVar('lastUpdated', time());

                //5. Store Ticket Object
                if ($ticketHandler->insert($ticketInfo)) {
                    if ($newStatus) {
                        $eventService->trigger('update_status', [&$ticketInfo, &$oldStatus, &$newStatus]);
                    }
                    if ($ticketClosed) {
                        $eventService->trigger('close_ticket', [&$ticketInfo]);
                    }
                    if ($ticketReopen) {
                        $eventService->trigger('reopen_ticket', [&$ticketInfo]);
                    }
                    if ($changeOwner) {
                        // @todo - Change this event to take the new owner as a parameter as well
                        $eventService->trigger('update_owner', [&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')]);
                    }
                } else {
                    //Ticket Update Error
                    $helper->redirect("response.php?op=staffFrm&id=$ticketid", 3, _XHELP_MESSAGE_EDITTICKET_ERROR);
                }

                //6. Save Attachments
                if ($uploadFile) {
                    $allowed_mimetypes = $mimetypeHandler->checkMimeTypes('userfile');
                    if (!$file = $ticketInfo->storeUpload('userfile', $response->getVar('id'), $allowed_mimetypes)) {
                        $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDFILE_ERROR);
                    }
                }

                //7. Success, clear session, redirect to ticket
                clearResponseFromSession();
                $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE);
            } else {
                redirect_header($url, 3, _XHELP_ERROR_NODEPTPERM);
            }
        }
        break;

    case 'staffFrm':
        $isSubmitter = false;
        $isStaff     = $membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'));

        // Check if staff has permission to respond to the ticket
        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
        $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails = $ticketEmailsHandler->getObjects($criteria);
        if (count($ticketEmails) > 0) {
            $isSubmitter = true;
        }
        if ($isSubmitter || $staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))) {
            /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
            $statusHandler = $helper->getHandler('Status');
            $criteria      = new \Criteria('', '');
            $criteria->setSort('description');
            $criteria->setOrder('ASC');
            $statuses  = $statusHandler->getObjects($criteria);
            $aStatuses = [];
            foreach ($statuses as $status) {
                $aStatuses[$status->getVar('id')] = [
                    'id'    => $status->getVar('id'),
                    'desc'  => $status->getVar('description'),
                    'state' => $status->getVar('state'),
                ];
            }

            $GLOBALS['xoopsOption']['template_main'] = 'xhelp_response.tpl';   // Set template
            require_once XOOPS_ROOT_PATH . '/header.php';

            $xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));
            $xoopsTpl->assign('xhelp_has_owner', $has_owner);
            $xoopsTpl->assign('xhelp_currentUser', $xoopsUser->getVar('uid'));
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
            $xoopsTpl->assign('xhelp_ticketID', $ticketid);
            $xoopsTpl->assign('xhelp_ticket_status', $ticketInfo->getVar('status'));
            $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
            $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
            $xoopsTpl->assign('xhelp_statuses', $aStatuses);
            $xoopsTpl->assign('xhelp_isSubmitter', $isSubmitter);
            $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $ticketInfo->getVar('id')));
            $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);
            $xoopsTpl->assign('xhelp_has_takeOwnership', $staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));

            $aElements      = [];
            $validateErrors = $session->get('xhelp_validateError');
            if ($validateErrors) {
                $errors = [];
                foreach ($validateErrors as $fieldname => $error) {
                    if (!empty($error['errors'])) {
                        $aElements[] = $fieldname;
                        foreach ($error['errors'] as $err) {
                            $errors[$fieldname] = $err;
                        }
                    }
                }
                $xoopsTpl->assign('xhelp_errors', $errors);
            } else {
                $xoopsTpl->assign('xhelp_errors', null);
            }

            $elements = ['response', 'timespent'];
            foreach ($elements as $element) {         // Foreach element in the predefined list
                $xoopsTpl->assign("xhelp_element_$element", 'formButton');
                foreach ($aElements as $aElement) {   // Foreach that has an error
                    if ($aElement == $element) {      // If the names are equal
                        $xoopsTpl->assign("xhelp_element_$element", 'validateError');
                        break;
                    }
                }
            }

            //Get all staff defined templates
            $criteria = new \Criteria('uid', $uid);
            $criteria->setSort('name');
            /** @var \XoopsModules\Xhelp\ResponseTemplatesHandler $responseTemplatesHandler */
            $responseTpl = $responseTemplatesHandler->getObjects($criteria, true);

            $xoopsTpl->append('xhelp_responseTpl_values', '------------------');
            $xoopsTpl->append('xhelp_responseTpl_ids', 0);

            foreach ($responseTpl as $obj) {
                $xoopsTpl->append('xhelp_responseTpl_values', $obj->getVar('name'));
                $xoopsTpl->append('xhelp_responseTpl_ids', $obj->getVar('id'));
            }
            $xoopsTpl->assign('xhelp_hasResponseTpl', (isset($responseTpl) ? count($responseTpl) > 0 : 0));
            $xoopsTpl->append('xhelp_responseTpl_selected', $refresh);
            $xoopsTpl->assign('xhelp_templateID', $refresh);

            //Format Response Message Var
            $message = '';
            if ($refresh) {
                $displayTpl = $responseTpl[$refresh];
                if ($displayTpl) {
                    $message = $displayTpl->getVar('response', 'e');
                }
            }
            $temp = $session->get('xhelp_response_message');
            if ($temp) {
                $message = $temp;
            }

            $xoopsTpl->assign('xhelp_response_message', $message);

            //Fill Response Fields (if set in session)
            if ($session->get('xhelp_response_ticketid')) {
                $xoopsTpl->assign('xhelp_response_ticketid', $session->get('xhelp_response_ticketid'));

                $xoopsTpl->assign('xhelp_response_status', $session->get('xhelp_response_status'));
                $xoopsTpl->assign('xhelp_ticket_status', $session->get('xhelp_response_status'));
                $xoopsTpl->assign('xhelp_response_ownership', $session->get('xhelp_response_ownership'));
                $xoopsTpl->assign('xhelp_response_timespent', $session->get('xhelp_response_timespent'));
                $xoopsTpl->assign('xhelp_response_private', $session->get('xhelp_response_private'));
            }
            $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
            require_once XOOPS_ROOT_PATH . '/footer.php';
        }
        break;

    case 'staffEdit':
        //Is current user staff member?
        if (!$membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            $helper->redirect('index.php', 3, _XHELP_ERROR_NODEPTPERM);
        }

        if (!$response = $responseHandler->get($responseid)) {
            $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_ERROR_INV_RESPONSE);
        }

        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_RESPONSE_EDIT, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_EDIT_RESPONSE;
            $helper->redirect("ticket.php?id=$ticketid", 3, $message);
        }

        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_editResponse.tpl';             // Always set main template before including the header
        require_once XOOPS_ROOT_PATH . '/header.php';

        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = $helper->getHandler('Status');
        $criteria      = new \Criteria('', '');
        $criteria->setSort('description');
        $criteria->setOrder('ASC');
        $statuses  = $statusHandler->getObjects($criteria);
        $aStatuses = [];
        foreach ($statuses as $status) {
            $aStatuses[$status->getVar('id')] = [
                'id'    => $status->getVar('id'),
                'desc'  => $status->getVar('description'),
                'state' => $status->getVar('state'),
            ];
        }
        $xoopsTpl->assign('xhelp_statuses', $aStatuses);
        $xoopsTpl->assign('xhelp_responseid', $responseid);
        $xoopsTpl->assign('xhelp_ticketID', $ticketid);
        $xoopsTpl->assign('xhelp_responseMessage', $response->getVar('message', 'e'));
        $xoopsTpl->assign('xhelp_timeSpent', $response->getVar('timeSpent'));
        $xoopsTpl->assign('xhelp_status', $ticketInfo->getVar('status'));
        $xoopsTpl->assign('xhelp_has_owner', $has_owner);
        $xoopsTpl->assign('xhelp_responsePrivate', ((1 == $response->getVar('private')) ? _XHELP_TEXT_YES : _XHELP_TEXT_NO));
        $xoopsTpl->assign('xhelp_currentUser', $uid);
        $xoopsTpl->assign('xhelp_allowUpload', 0);
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xhelp_has_takeOwnership', $staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));
        //$xoopsTpl->assign('xhelp_text_subject', _XHELP_TEXT_SUBJECT);
        //$xoopsTpl->assign('xhelp_text_description', _XHELP_TEXT_DESCRIPTION);

        $aElements      = [];
        $errors         = [];
        $validateErrors = $session->get('xhelp_validateError');
        if ($validateErrors) {
            foreach ($validateErrors as $fieldname => $error) {
                if (!empty($error['errors'])) {
                    $aElements[] = $fieldname;
                    foreach ($error['errors'] as $err) {
                        $errors[$fieldname] = $err;
                    }
                }
            }
            $xoopsTpl->assign('xhelp_errors', $errors);
        } else {
            $xoopsTpl->assign('xhelp_errors', null);
        }

        $elements = ['response', 'timespent'];
        foreach ($elements as $element) {         // Foreach element in the predefined list
            $xoopsTpl->assign("xhelp_element_$element", 'formButton');
            foreach ($aElements as $aElement) {   // Foreach that has an error
                if ($aElement == $element) {      // If the names are equal
                    $xoopsTpl->assign("xhelp_element_$element", 'validateError');
                    break;
                }
            }
        }

        /** @var \XoopsModules\Xhelp\ResponseTemplatesHandler $responseTemplatesHandler */
        $responseTemplatesHandler = $helper->getHandler('ResponseTemplates');          // Used to display responseTemplates
        $criteria                 = new \Criteria('uid', $uid);
        $criteria->setSort('name');
        $responseTpl = $responseTemplatesHandler->getObjects($criteria);

        $aResponseTpl = [];
        foreach ($responseTpl as $response) {
            $aResponseTpl[] = [
                'id'       => $response->getVar('id'),
                'uid'      => $response->getVar('uid'),
                'name'     => $response->getVar('name'),
                'response' => $response->getVar('response'),
            ];
        }
        $has_responseTpl = count($responseTpl) > 0;
        unset($responseTpl);
        $displayTpl = $responseTemplatesHandler->get($refresh);

        $xoopsTpl->assign('xhelp_response_text', (0 != $refresh ? $displayTpl->getVar('response', 'e') : ''));
        $xoopsTpl->assign('xhelp_responseTpl', $aResponseTpl);
        $xoopsTpl->assign('xhelp_hasResponseTpl', count($aResponseTpl) > 0);
        $xoopsTpl->assign('xhelp_refresh', $refresh);
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

        require_once XOOPS_ROOT_PATH . '/footer.php';                             //Include the page footer
        break;

    case 'staffEditSave':
        // require_once XHELP_CLASS_PATH . '/validator.php';
        $v['response'][]  = new Validation\ValidateLength(Request::getString('response', '', 'POST'), 2, 50000);
        $v['timespent'][] = new Validation\ValidateNumber(Request::getString('timespent', '', 'POST'));

        $responseStored = false;

        //Is current user staff member?
        if (!$membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            $helper->redirect('index.php', 3, _XHELP_ERROR_NODEPTPERM);
        }

        //Retrieve the original response
        if (!$response = $responseHandler->get($responseid)) {
            $helper->redirect('ticket.php?id=' . $ticketInfo->getVar('id'), 3, _XHELP_ERROR_INV_RESPONSE);
        }

        //Copy original ticket and response objects
        $oldresponse = $response;
        $oldticket   = $ticketInfo;

        $url          = "response.php?op=staffEditSave&amp;id=$ticketid&amp;responseid=$responseid";
        $ticketReopen = $changeOwner = $ticketClosed = $newStatus = false;

        //Store current fields in session
        $session->set('xhelp_response_ticketid', $oldresponse->getVar('ticketid'));
        $session->set('xhelp_response_uid', $response->getVar('uid'));
        $session->set('xhelp_response_message', \Xmf\Request::getString('response', '', 'POST'));

        //Check if the ticket status has been changed
        if ($_POST['status'] <> $ticketInfo->getVar('status')) {
            $ticketInfo->setVar('status', $_POST['status']);
            $newStatus = true;

            if (2 == $_POST['status']) { //Closed Ticket
                $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                $ticketClosed = true;
            }

            if (2 == $oldticket->getVar('status')) { //Ticket reopened
                $ticketReopen = true;
            }
        }
        $session->set('xhelp_response_status', $ticketInfo->getVar('status'));        // Store in session

        //Check if the current user is claiming the ticket
        if (Request::hasVar('claimOwner', 'POST') && $_POST['claimOwner'] > 0
            && $staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
            if ($_POST['claimOwner'] != $oldticket->getVar('ownership')) {
                $oldOwner = $oldticket->getVar('ownership');
                $ticketInfo->setVar('ownership', $_POST['claimOwner']);
                $changeOwner = true;
            }
        }
        $session->set('xhelp_response_ownership', $ticketInfo->getVar('ownership'));  // Store in session

        // Check the timespent
        if (Request::hasVar('timespent', 'POST')) {
            $timespent = Request::getInt('timespent', 0, 'POST');
            $totaltime = $oldticket->getVar('totalTimeSpent') - $oldresponse->getVar('timeSpent') + $timespent;
            $ticketInfo->setVar('totalTimeSpent', $totaltime);
            $response->setVar('timeSpent', $timespent);
        }
        $session->set('xhelp_response_timespent', $response->getVar('timeSpent'));
        $session->set('xhelp_responseStored', true);

        // Perform each validation
        $fields = [];
        $errors = [];
        foreach ($v as $fieldname => $validator) {
            if (Xhelp\Utility::checkRules($validator, $errors)) {
                $fields[$fieldname]['haserrors'] = false;
            } else {
                // Mark field with error
                $fields[$fieldname]['haserrors'] = true;
                $fields[$fieldname]['errors']    = $errors;
            }
        }

        if (!empty($errors)) {
            $session->set('xhelp_validateError', $fields);
            $message = _XHELP_MESSAGE_VALIDATE_ERROR;
            $helper->redirect("response.php?id=$ticketid&responseid=" . $response->getVar('id') . '&op=staffEdit');
        }

        $ticketInfo->setVar('lastUpdated', time());

        if ($ticketHandler->insert($ticketInfo)) {
            if ($newStatus) {
                // @todo - 'update_status' should also supply $newStatus
                $eventService->trigger('update_status', [&$ticketInfo, $oldStatus]);
            }
            if ($ticketClosed) {
                $eventService->trigger('close_ticket', [&$ticketInfo]);
            }
            if ($ticketReopen) {
                $eventService->trigger('reopen_ticket', [&$ticketInfo]);
            }
            if ($changeOwner) {
                $eventService->trigger('update_owner', [&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')]);
            }

            $message = $_POST['response'];
            $message .= "\n" . sprintf(_XHELP_RESPONSE_EDIT, $xoopsUser->getVar('uname'), $ticketInfo->lastUpdated());

            $response->setVar('message', $message);
            if (Request::hasVar('timespent', 'POST')) {
                $response->setVar('timeSpent', Request::getInt('timespent', 0, 'POST'));
            }
            $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));

            if ($responseHandler->insert($response)) {
                $eventService->trigger('edit_response', [&$ticketInfo, &$response, &$oldticket, &$oldresponse]);
                $message        = _XHELP_MESSAGE_EDITRESPONSE;
                $url            = "ticket.php?id=$ticketid";
                $responseStored = true;
            } else {
                $message = _XHELP_MESSAGE_EDITRESPONSE_ERROR;
            }
        } else {
            $message = _XHELP_MESSAGE_EDITTICKET_ERROR;
        }
        clearResponseFromSession();
        redirect_header($url, 3, $message);

        break;

    default:
        break;
}

/**
 * @param Ticket $ticket
 * @param array  $errors
 */
function setResponseToSession(Ticket $ticket, array $errors)
{
    global $xoopsUser, $session;
    $session->set('xhelp_response_ticketid', $ticket->getVar('id'));
    $session->set('xhelp_response_uid', $xoopsUser->getVar('uid'));
    $session->set('xhelp_response_message', \Xmf\Request::getString('response', '', 'POST'));
    $session->set('xhelp_response_private', \Xmf\Request::getInt('private', 0, 'POST'));
    $session->set('xhelp_response_timespent', (\Xmf\Request::getInt('timespent', 0, 'POST')));
    $session->set('xhelp_response_ownership', (isset($_POST['claimOwner']) && Request::getInt('claimOwner', 0, 'POST') > 0 ? $_POST['claimOwner'] : 0));
    $session->set('xhelp_response_status', \Xmf\Request::getInt('status', 0, 'POST'));
    $session->set('xhelp_response_private', \Xmf\Request::getInt('private', 0, 'POST'));
    $session->set('xhelp_validateError', $errors);
}

function clearResponseFromSession()
{
    global $session;
    $session->del('xhelp_response_ticketid');
    $session->del('xhelp_response_uid');
    $session->del('xhelp_response_message');
    $session->del('xhelp_response_timespent');
    $session->del('xhelp_response_ownership');
    $session->del('xhelp_response_status');
    $session->del('xhelp_response_private');
    $session->del('xhelp_validateError');
}
