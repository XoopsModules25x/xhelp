<?php

use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php', 3);
}

$refresh = \Xmf\Request::getInt('refresh', 0, 'GET');

$uid = $xoopsUser->getVar('uid');

// Get the id of the ticket
if (\Xmf\Request::hasVar('id', 'GET')) {
    $ticketid = \Xmf\Request::getInt('id', 0, 'GET');
}

if (\Xmf\Request::hasVar('responseid', 'GET')) {
    $responseid = \Xmf\Request::getInt('responseid', 0, 'GET');
}

$ticketHandler     = Xhelp\Helper::getInstance()->getHandler('Ticket');
$hResponseTpl      = Xhelp\Helper::getInstance()->getHandler('ResponseTemplates');
$membershipHandler = Xhelp\Helper::getInstance()->getHandler('Membership');
$hResponse         = Xhelp\Helper::getInstance()->getHandler('Responses');
$staffHandler      = Xhelp\Helper::getInstance()->getHandler('Staff');

if (!$ticketInfo = $ticketHandler->get($ticketid)) {
    //Invalid ticketID specified
    redirect_header(XHELP_BASE_URL . '/index.php', 3, _XHELP_ERROR_INV_TICKET);
}

$has_owner = $ticketInfo->getVar('ownership');

$op = 'staffFrm'; //Default Action for page

if (\Xmf\Request::hasVar('op', 'GET')) {
    $op = $_GET['op'];
}

if (\Xmf\Request::hasVar('op', 'POST')) {
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
            $hTicketEmails = Xhelp\Helper::getInstance()->getHandler('TicketEmails');
            $crit          = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
            $crit->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
            $ticketEmails = $hTicketEmails->getObjects($crit);
            if (count($ticketEmails > 0)
                || $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))) {
                //1. Verify Response fields are filled properly
                // require_once XHELP_CLASS_PATH . '/validator.php';
                $v                = [];
                $v['response'][]  = new validation\ValidateLength(Request::getString('response', '', 'POST'), 2, 50000);
                $v['timespent'][] = new validation\ValidateNumber(Request::getString('timespent', '', 'POST'));

                if ($helper->getConfig('xhelp_allowUpload') && is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                    $hMime = Xhelp\Helper::getInstance()->getHandler('Mimetype');
                    //Add File Upload Validation Rules
                    $v['userfile'][] = new validation\ValidateMimeType($_FILES['userfile']['name'], $_FILES['userfile']['type'], $hMime->getArray());
                    $v['userfile'][] = new validation\ValidateFileSize($_FILES['userfile']['tmp_name'], $helper->getConfig('xhelp_uploadSize'));
                    $v['userfile'][] = new validation\ValidateImageSize($_FILES['userfile']['tmp_name'], $helper->getConfig('xhelp_uploadWidth'), $helper->getConfig('xhelp_uploadHeight'));
                    $uploadFile      = true;
                }

                // Perform each validation
                $fields = [];
                $errors = [];
                foreach ($v as $fieldname => $validator) {
                    if (!Xhelp\Utility::checkRules($validator, $errors)) {
                        $hasErrors = true;
                        //Mark field with error
                        $fields[$fieldname]['haserrors'] = true;
                        $fields[$fieldname]['errors']    = $errors;
                    } else {
                        $fields[$fieldname]['haserrors'] = false;
                    }
                }

                if ($hasErrors) {
                    //Store field values in session
                    //Store error messages in session
                    _setResponseToSession($ticketInfo, $fields);
                    //redirect to response.php?op=staffFrm
                    redirect_header(XHELP_BASE_URL . "/response.php?op=staffFrm&id=$ticketid");
                }

                //Check if status changed
                if ($_POST['status'] <> $ticketInfo->getVar('status')) {
                    $hStatus   = Xhelp\Helper::getInstance()->getHandler('Status');
                    $oldStatus = $hStatus->get($ticketInfo->getVar('status'));
                    $newStatus = $hStatus->get(\Xmf\Request::getInt('status', 0, 'POST'));

                    if (1 == $oldStatus->getVar('state') && 2 == $newStatus->getVar('state')) {
                        $ticketClosed = true;
                    } elseif (2 == $oldStatus->getVar('state') && 1 == $newStatus->getVar('state')) {
                        $ticketReopen = true;
                    }
                    $ticketInfo->setVar('status', \Xmf\Request::getInt('status', 0, 'POST'));
                }

                //Check if user claimed ownership
                if (\Xmf\Request::hasVar('claimOwner', 'POST'))
                    && $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
                    $ownerid = \Xmf\Request::getInt('claimOwner', 0, 'POST');
                    if ($ownerid > 0) {
                        $oldOwner = $ticketInfo->getVar('ownership');
                        $ticketInfo->setVar('ownership', $ownerid);
                        $changeOwner = true;
                    }
                }

                //2. Fill Response Object
                $response = $hResponse->create();
                $response->setVar('uid', $xoopsUser->getVar('uid'));
                $response->setVar('ticketid', $ticketid);
                $response->setVar('message', $_POST['response']);
                $response->setVar('timeSpent', $_POST['timespent']);
                $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));
                $response->setVar('userIP', getenv('REMOTE_ADDR'));
                if (\Xmf\Request::hasVar('private', 'POST')) {
                    $response->setVar('private', $_POST['private']);
                }

                //3. Store Response Object in DB
                if ($hResponse->insert($response)) {
                    $_eventsrv->trigger('new_response', [&$ticketInfo, &$response]);
                } else {
                    //Store response fields in session
                    _setResponseToSession($ticketInfo, $fields);
                    //Notify user of error (using redirect_header())'
                    redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE_ERROR);
                }

                //4. Update Ticket object
                if (\Xmf\Request::hasVar('timespent', 'POST')) {
                    $oldspent = $ticketInfo->getVar('totalTimeSpent');
                    $ticketInfo->setVar('totalTimeSpent', $oldspent + \Xmf\Request::getInt('timespent', 0, 'POST'));
                }
                if ($ticketClosed) {
                    $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                }
                $ticketInfo->setVar('lastUpdated', time());

                //5. Store Ticket Object
                if ($ticketHandler->insert($ticketInfo)) {
                    if ($newStatus) {
                        $_eventsrv->trigger('update_status', [&$ticketInfo, &$oldStatus, &$newStatus]);
                    }
                    if ($ticketClosed) {
                        $_eventsrv->trigger('close_ticket', [&$ticketInfo]);
                    }
                    if ($ticketReopen) {
                        $_eventsrv->trigger('reopen_ticket', [&$ticketInfo]);
                    }
                    if ($changeOwner) {
                        // @todo - Change this event to take the new owner as a parameter as well
                        $_eventsrv->trigger('update_owner', [&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')]);
                    }
                } else {
                    //Ticket Update Error
                    redirect_header(XHELP_BASE_URL . "/response.php?op=staffFrm&id=$ticketid", 3, _XHELP_MESSAGE_EDITTICKET_ERROR);
                }

                //6. Save Attachments
                if ($uploadFile) {
                    $allowed_mimetypes = $hMime->checkMimeTypes('userfile');
                    if (!$file = $ticketInfo->storeUpload('userfile', $response->getVar('id'), $allowed_mimetypes)) {
                        redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDFILE_ERROR);
                    }
                }

                //7. Success, clear session, redirect to ticket
                _clearResponseFromSession();
                redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE);
            } else {
                redirect_header($url, 3, _XHELP_ERROR_NODEPTPERM);
            }
        }
        break;

    case 'staffFrm':
        $isSubmitter = false;
        $isStaff     = $membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'));

        // Check if staff has permission to respond to the ticket
        $hTicketEmails = Xhelp\Helper::getInstance()->getHandler('TicketEmails');
        $crit          = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
        $crit->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails = $hTicketEmails->getObjects($crit);
        if (count($ticketEmails) > 0) {
            $isSubmitter = true;
        }
        if ($isSubmitter || $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))) {
            $hStatus = Xhelp\Helper::getInstance()->getHandler('Status');
            $crit    = new \Criteria('', '');
            $crit->setSort('description');
            $crit->setOrder('ASC');
            $statuses  = $hStatus->getObjects($crit);
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
            $xoopsTpl->assign('xhelp_has_takeOwnership', $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));

            $aElements      = [];
            $validateErrors = $_xhelpSession->get('xhelp_validateError');
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
            $crit = new \Criteria('uid', $uid);
            $crit->setSort('name');
            $responseTpl = $hResponseTpl->getObjects($crit, true);

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
            $temp = $_xhelpSession->get('xhelp_response_message');
            if ($temp) {
                $message = $temp;
            }

            $xoopsTpl->assign('xhelp_response_message', $message);

            //Fill Response Fields (if set in session)
            if ($_xhelpSession->get('xhelp_response_ticketid')) {
                $xoopsTpl->assign('xhelp_response_ticketid', $_xhelpSession->get('xhelp_response_ticketid'));

                $xoopsTpl->assign('xhelp_response_status', $_xhelpSession->get('xhelp_response_status'));
                $xoopsTpl->assign('xhelp_ticket_status', $_xhelpSession->get('xhelp_response_status'));
                $xoopsTpl->assign('xhelp_response_ownership', $_xhelpSession->get('xhelp_response_ownership'));
                $xoopsTpl->assign('xhelp_response_timespent', $_xhelpSession->get('xhelp_response_timespent'));
                $xoopsTpl->assign('xhelp_response_private', $_xhelpSession->get('xhelp_response_private'));
            }
            $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
            require_once XOOPS_ROOT_PATH . '/footer.php';
        }
        break;

    case 'staffEdit':
        //Is current user staff member?
        if (!$membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            redirect_header(XHELP_BASE_URL . '/index.php', 3, _XHELP_ERROR_NODEPTPERM);
        }

        if (!$response = $hResponse->get($responseid)) {
            redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, _XHELP_ERROR_INV_RESPONSE);
        }

        if (!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_EDIT, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_EDIT_RESPONSE;
            redirect_header(XHELP_BASE_URL . "/ticket.php?id=$ticketid", 3, $message);
        }

        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_editResponse.tpl';             // Always set main template before including the header
        require_once XOOPS_ROOT_PATH . '/header.php';

        $hStatus = Xhelp\Helper::getInstance()->getHandler('Status');
        $crit    = new \Criteria('', '');
        $crit->setSort('description');
        $crit->setOrder('ASC');
        $statuses  = $hStatus->getObjects($crit);
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
        $xoopsTpl->assign('xhelp_has_takeOwnership', $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));
        //$xoopsTpl->assign('xhelp_text_subject', _XHELP_TEXT_SUBJECT);
        //$xoopsTpl->assign('xhelp_text_description', _XHELP_TEXT_DESCRIPTION);

        $aElements      = [];
        $errors         = [];
        $validateErrors = $_xhelpSession->get('xhelp_validateError');
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

        $hResponseTpl = Xhelp\Helper::getInstance()->getHandler('ResponseTemplates');          // Used to display responseTemplates
        $crit         = new \Criteria('uid', $uid);
        $crit->setSort('name');
        $responseTpl = $hResponseTpl->getObjects($crit);

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
        $displayTpl = $hResponseTpl->get($refresh);

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
        $v['response'][]  = new validation\ValidateLength(Request::getString('response', '', 'POST'), 2, 50000);
        $v['timespent'][] = new validation\ValidateNumber(Request::getString('timespent', '', 'POST'));

        $responseStored = false;

        //Is current user staff member?
        if (!$membershipHandler->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            redirect_header(XHELP_BASE_URL . '/index.php', 3, _XHELP_ERROR_NODEPTPERM);
        }

        //Retrieve the original response
        if (!$response = $hResponse->get($responseid)) {
            redirect_header(XHELP_BASE_URL . '/ticket.php?id=' . $ticketInfo->getVar('id'), 3, _XHELP_ERROR_INV_RESPONSE);
        }

        //Copy original ticket and response objects
        $oldresponse = $response;
        $oldticket   = $ticketInfo;

        $url          = "response.php?op=staffEditSave&amp;id=$ticketid&amp;responseid=$responseid";
        $ticketReopen = $changeOwner = $ticketClosed = $newStatus = false;

        //Store current fields in session
        $_xhelpSession->set('xhelp_response_ticketid', $oldresponse->getVar('ticketid'));
        $_xhelpSession->set('xhelp_response_uid', $response->getVar('uid'));
        $_xhelpSession->set('xhelp_response_message', $_POST['response']);

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
        $_xhelpSession->set('xhelp_response_status', $ticketInfo->getVar('status'));        // Store in session

        //Check if the current user is claiming the ticket
        if (\Xmf\Request::hasVar('claimOwner', 'POST') && $_POST['claimOwner'] > 0
            && $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
            if ($_POST['claimOwner'] != $oldticket->getVar('ownership')) {
                $oldOwner = $oldticket->getVar('ownership');
                $ticketInfo->setVar('ownership', $_POST['claimOwner']);
                $changeOwner = true;
            }
        }
        $_xhelpSession->set('xhelp_response_ownership', $ticketInfo->getVar('ownership'));  // Store in session

        // Check the timespent
        if (\Xmf\Request::hasVar('timespent', 'POST')) {
            $timespent = \Xmf\Request::getInt('timespent', 0, 'POST');
            $totaltime = $oldticket->getVar('totalTimeSpent') - $oldresponse->getVar('timeSpent') + $timespent;
            $ticketInfo->setVar('totalTimeSpent', $totaltime);
            $response->setVar('timeSpent', $timespent);
        }
        $_xhelpSession->set('xhelp_response_timespent', $response->getVar('timeSpent'));
        $_xhelpSession->set('xhelp_responseStored', true);

        // Perform each validation
        $fields = [];
        $errors = [];
        foreach ($v as $fieldname => $validator) {
            if (!Xhelp\Utility::checkRules($validator, $errors)) {
                // Mark field with error
                $fields[$fieldname]['haserrors'] = true;
                $fields[$fieldname]['errors']    = $errors;
            } else {
                $fields[$fieldname]['haserrors'] = false;
            }
        }

        if (!empty($errors)) {
            $_xhelpSession->set('xhelp_validateError', $fields);
            $message = _XHELP_MESSAGE_VALIDATE_ERROR;
            redirect_header(XHELP_BASE_URL . "/response.php?id=$ticketid&responseid=" . $response->getVar('id') . '&op=staffEdit');
        }

        $ticketInfo->setVar('lastUpdated', time());

        if ($ticketHandler->insert($ticketInfo)) {
            if ($newStatus) {
                // @todo - 'update_status' should also supply $newStatus
                $_eventsrv->trigger('update_status', [&$ticketInfo, $oldStatus]);
            }
            if ($ticketClosed) {
                $_eventsrv->trigger('close_ticket', [&$ticketInfo]);
            }
            if ($ticketReopen) {
                $_eventsrv->trigger('reopen_ticket', [&$ticketInfo]);
            }
            if ($changeOwner) {
                $_eventsrv->trigger('update_owner', [&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')]);
            }

            $message = $_POST['response'];
            $message .= "\n" . sprintf(_XHELP_RESPONSE_EDIT, $xoopsUser->getVar('uname'), $ticketInfo->lastUpdated());

            $response->setVar('message', $message);
            if (\Xmf\Request::hasVar('timespent', 'POST')) {
                $response->setVar('timeSpent', \Xmf\Request::getInt('timespent', 0, 'POST'));
            }
            $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));

            if ($hResponse->insert($response)) {
                $_eventsrv->trigger('edit_response', [&$ticketInfo, &$response, &$oldticket, &$oldresponse]);
                $message        = _XHELP_MESSAGE_EDITRESPONSE;
                $url            = "ticket.php?id=$ticketid";
                $responseStored = true;
            } else {
                $message = _XHELP_MESSAGE_EDITRESPONSE_ERROR;
            }
        } else {
            $message = _XHELP_MESSAGE_EDITTICKET_ERROR;
        }
        _clearResponseFromSession();
        redirect_header($url, 3, $message);

        break;

    default:
        break;
}

/**
 * @param $ticket
 * @param $errors
 */
function _setResponseToSession($ticket, $errors)
{
    global $xoopsUser, $_xhelpSession;
    $_xhelpSession->set('xhelp_response_ticketid', $ticket->getVar('id'));
    $_xhelpSession->set('xhelp_response_uid', $xoopsUser->getVar('uid'));
    $_xhelpSession->set('xhelp_response_message', ($_POST['response'] ?? ''));
    $_xhelpSession->set('xhelp_response_private', ($_POST['private'] ?? 0));
    $_xhelpSession->set('xhelp_response_timespent', ($_POST['timespent'] ?? 0));
    $_xhelpSession->set('xhelp_response_ownership', (isset($_POST['claimOwner']) && \Xmf\Request::getInt('claimOwner', 0, 'POST') > 0 ? $_POST['claimOwner'] : 0));
    $_xhelpSession->set('xhelp_response_status', $_POST['status']);
    $_xhelpSession->set('xhelp_response_private', $_POST['private']);
    $_xhelpSession->set('xhelp_validateError', $errors);
}

function _clearResponseFromSession()
{
    global $_xhelpSession;
    $_xhelpSession->del('xhelp_response_ticketid');
    $_xhelpSession->del('xhelp_response_uid');
    $_xhelpSession->del('xhelp_response_message');
    $_xhelpSession->del('xhelp_response_timespent');
    $_xhelpSession->del('xhelp_response_ownership');
    $_xhelpSession->del('xhelp_response_status');
    $_xhelpSession->del('xhelp_response_private');
    $_xhelpSession->del('xhelp_validateError');
}
