<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;
use XoopsModules\Xhelp\Ticket;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
// require_once XHELP_CLASS_PATH . '/validator.php';

global $xoopsTpl, $session, $xoopsUser, $xoopsConfig, $xoopsModule, $xhelp_module_header, $xhelp_isStaff, $staff, $xoopsRequestUri;

$helper       = Xhelp\Helper::getInstance();
$eventService = Xhelp\EventService::getInstance();
$op           = 'user';
$xhelp_id     = 0;

// Get the id of the ticket
if (Request::hasVar('id', 'REQUEST')) {
    $xhelp_id = Request::getInt('id', 0, 'REQUEST');
} else {
    $helper->redirect('index.php', 3, _XHELP_ERROR_INV_TICKET);
}

if (Request::hasVar('op', 'GET')) {
    $op = $_GET['op'];
}

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php?xoops_redirect=' . htmlspecialchars($xoopsRequestUri, ENT_QUOTES | ENT_HTML5), 3);
}

//$xoopsVersion = mb_substr(XOOPS_VERSION, 6);
//(int)$xoopsVersion;

global $ticketInfo;
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
if (!$ticketInfo = $ticketHandler->get($xhelp_id)) {
    $helper->redirect('index.php', 3, _XHELP_ERROR_INV_TICKET);
}

$displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

/** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
$departmentHandler = $helper->getHandler('Department');
$departments       = &$departmentHandler->getObjects(null, true);
$user              = $memberHandler->getUser($ticketInfo->getVar('uid'));
/** @var \XoopsModules\Xhelp\StaffReviewHandler $staffReviewHandler */
$staffReviewHandler = $helper->getHandler('StaffReview');
/** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
$responseHandler = $helper->getHandler('Response');
/** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
$membershipHandler = $helper->getHandler('Membership');
$aResponses        = [];
$all_users         = [];

if (isset($departments[$ticketInfo->getVar('department')])) {
    $department = $departments[$ticketInfo->getVar('department')];
}

//Security Checkpoints to ensure no funny stuff
if (!$xoopsUser) {
    $helper->redirect('index.php', 3, _NOPERM);
}

$op = ($xhelp_isStaff ? 'staff' : $op);

$has_ticketFiles = false;
$files           = $ticketInfo->getFiles();
$aFiles          = [];
foreach ($files as $file) {
    if (0 == $file->getVar('responseid')) {
        $has_ticketFiles = true;
    }

    $filename_full = $file->getVar('filename');
    if (0 != $file->getVar('responseid')) {
        $removeText = $file->getVar('ticketid') . '_' . $file->getVar('responseid') . '_';
    } else {
        $removeText = $file->getVar('ticketid') . '_';
    }
    $filename = str_replace($removeText, '', $filename_full);
    $filesize = round(filesize(XHELP_UPLOAD_PATH . '/' . $filename_full) / 1024, 2);

    $aFiles[] = [
        'id'            => $file->getVar('id'),
        'filename'      => $filename,
        'filename_full' => $filename_full,
        'ticketid'      => $file->getVar('ticketid'),
        'responseid'    => $file->getVar('responseid'),
        'path'          => 'viewFile.php?id=' . $file->getVar('id'),
        'size'          => $filesize . ' ' . _XHELP_SIZE_KB,
    ];
}
$has_files = count($files) > 0;
unset($files);
$message = '';

if ($xhelp_isStaff) {
    //** BTW - What does $giveOwnership do here?
    $giveOwnership = false;
    if (Request::hasVar('op', 'GET')) {
        $op = $_GET['op'];
    } else {
        $op = 'staff';
    }

    //Retrieve all responses to current ticket
    $responses = $ticketInfo->getResponses();
    foreach ($responses as $response) {
        if ($has_files) {
            $hasFiles = false;
            foreach ($aFiles as $file) {
                if ($file['responseid'] == $response->getVar('id')) {
                    $hasFiles = true;
                    break;
                }
            }
        } else {
            $hasFiles = false;
        }

        $aResponses[]                        = [
            'id'          => $response->getVar('id'),
            'uid'         => $response->getVar('uid'),
            'uname'       => '',
            'ticketid'    => $response->getVar('ticketid'),
            'message'     => $response->getVar('message'),
            'timeSpent'   => $response->getVar('timeSpent'),
            'updateTime'  => $response->posted('m'),
            'userIP'      => $response->getVar('userIP'),
            'user_sig'    => '',
            'user_avatar' => '',
            'attachSig'   => '',
            'staffRating' => '',
            'private'     => $response->getVar('private'),
            'hasFiles'    => $hasFiles,
        ];
        $all_users[$response->getVar('uid')] = '';
    }

    $all_users[$ticketInfo->getVar('uid')]       = '';
    $all_users[$ticketInfo->getVar('ownership')] = '';
    $all_users[$ticketInfo->getVar('closedBy')]  = '';

    $has_responses = count($responses) > 0;
    unset($responses);

    $owner = $memberHandler->getUser($ticketInfo->getVar('ownership'));
    if ($owner) {
        $giveOwnership = true;
    }

    //Retrieve all log messages from the database
    $logMessage = $ticketInfo->getLogs();

    $patterns       = [];
    $patterns[]     = '/pri:([1-5])/';
    $replacements   = [];
    $replacements[] = '<img src="assets/images/priority$1.png" alt="Priority: $1">';

    foreach ($logMessage as $msg) {
        $aMessages[]                    = [
            'id'          => $msg->getVar('id'),
            'uid'         => $msg->getVar('uid'),
            'uname'       => '',
            //'uname'=>(($msgLoggedBy)? $msgLoggedBy->getVar('uname'):$xoopsConfig['anonymous']),
            'ticketid'    => $msg->getVar('ticketid'),
            'lastUpdated' => $msg->lastUpdated('m'),
            'action'      => preg_replace($patterns, $replacements, $msg->getVar('action')),
        ];
        $all_users[$msg->getVar('uid')] = '';
    }
    unset($logMessage);

    //For assign to ownership box
    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');

    global $staffArray;
    $staffArray = $staffHandler->getStaffByTask(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'));

    $aOwnership = [];
    // Only run if actions are set to inline style

    if (1 == $helper->getConfig('xhelp_staffTicketActions')) {
        $aOwnership[] = [
            'uid'   => 0,
            'uname' => _XHELP_NO_OWNER,
        ];
        foreach ($staffArray as $stf) {
            $aOwnership[]                   = [
                'uid'   => $stf->getVar('uid'),
                'uname' => '',
            ];
            $all_users[$stf->getVar('uid')] = '';
        }
    }

    // Get list of user's last submitted tickets
    $criteria = new \CriteriaCompo(new \Criteria('uid', $ticketInfo->getVar('uid')));
    $criteria->setSort('posted');
    $criteria->setOrder('DESC');
    $criteria->setLimit(10);
    $lastTickets = $ticketHandler->getObjects($criteria);
    foreach ($lastTickets as $ticket) {
        $dept = $ticket->getVar('department');
        if (isset($departments[$dept])) {
            $dept   = $departments[$dept]->getVar('department');
            $hasUrl = true;
        } else {
            $dept   = _XHELP_TEXT_NO_DEPT;
            $hasUrl = false;
        }
        $aLastTickets[] = [
            'id'         => $ticket->getVar('id'),
            'subject'    => $ticket->getVar('subject'),
            'status'     => Xhelp\Utility::getStatus($ticket->getVar('status')),
            'department' => $dept,
            'dept_url'   => $hasUrl ? XOOPS_URL . '/modules/xhelp/index.php?op=staffViewAll&amp;dept=' . $ticket->getVar('department') : '',
            'url'        => XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'),
        ];
    }
    $has_lastTickets = count($lastTickets);
    unset($lastTickets);
}

switch ($op) {
    case 'addEmail':

        if ('' === \Xmf\Request::getString('newEmail', '', 'POST')) {
            $message = _XHELP_MESSAGE_NO_EMAIL;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        //Check if email is valid
        $validator = new Validation\ValidateEmail(Request::getString('newEmail', '', 'POST'));
        if (!$validator->isValid()) {
            redirect_header(Xhelp\Utility::createURI('ticket.php', ['id' => $xhelp_id], false), 3, _XHELP_MESSAGE_NO_EMAIL);
        }

        /** @var \XoopsUser $newUser */
        if ($newUser = Xhelp\Utility::emailIsXoopsUser(Request::getString('newEmail', '', 'POST'))) {
            $user_id = $newUser->getVar('uid');
        } else {      // If a user doesn't exist with this email
            $user_id = 0;
        }

        // Check that the email doesn't already exist for this ticket
        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $xhelp_id));
        $criteria->add(new \Criteria('email', \Xmf\Request::getString('newEmail', '', 'POST')));
        $existingUsers = $ticketEmailsHandler->getObjects($criteria);
        if (count($existingUsers) > 0) {
            $message = _XHELP_MESSAGE_EMAIL_USED;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        // Create new ticket email object
        /** @var \XoopsModules\Xhelp\TicketEmails $newSubmitter */
        $newSubmitter = $ticketEmailsHandler->create();
        $newSubmitter->setVar('email', \Xmf\Request::getString('newEmail', '', 'POST'));
        $newSubmitter->setVar('uid', $user_id);
        $newSubmitter->setVar('ticketid', $xhelp_id);
        $newSubmitter->setVar('suppress', 0);
        if ($ticketEmailsHandler->insert($newSubmitter)) {
            $message = _XHELP_MESSAGE_ADDED_EMAIL;
            $helper->redirect("ticket.php?id=$xhelp_id#emailNotification");
        } else {
            $message = _XHELP_MESSAGE_ADDED_EMAIL_ERROR;
            $helper->redirect("ticket.php?id=$xhelp_id#emailNotification", 3, $message);
        }
        break;
    case 'changeSuppress':
        if (!$xhelp_isStaff) {
            $message = _XHELP_MESSAGE_NO_MERGE_TICKET;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $_GET['id']));
        $criteria->add(new \Criteria('email', $_GET['email']));
        $suppressUser = $ticketEmailsHandler->getObjects($criteria);

        foreach ($suppressUser as $sUser) {
            if (0 == $sUser->getVar('suppress')) {
                $sUser->setVar('suppress', 1);
            } else {
                $sUser->setVar('suppress', 0);
            }
            if (!$ticketEmailsHandler->insert($sUser, true)) {
                $message = _XHELP_MESSAGE_ADD_EMAIL_ERROR;
                $helper->redirect("ticket.php?id=$xhelp_id#emailNotification", 3, $message);
            }
        }
        $helper->redirect("ticket.php?id=$xhelp_id#emailNotification");
        break;
    case 'delete':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_DELETE, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_DELETE_TICKET;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }
        if (Request::hasVar('delete_ticket', 'POST')) {
            if ($ticketHandler->delete($ticketInfo)) {
                $message = _XHELP_MESSAGE_DELETE_TICKET;
                $eventService->trigger('delete_ticket', [&$ticketInfo]);
            } else {
                $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
            }
        } else {
            $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
        }
        $helper->redirect('index.php', 3, $message);
        break;
    case 'edit':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_EDIT, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_EDIT_TICKET;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }
        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');    // Department handler

        if (isset($_POST['editTicket'])) {
            // require_once XHELP_CLASS_PATH . '/validator.php';

            $v                  = [];
            $v['subject'][]     = new Validation\ValidateLength(Request::getString('subject', '', 'POST'), 2, 100);
            $v['description'][] = new Validation\ValidateLength(Request::getString('description', '', 'POST'), 2, 50000);

            $aFields = [];

            //Temp Ticket object for _getTicketFields
            $_ticket = $ticketInfo;
            $_ticket->setVar('department', Request::getString('departments', '', 'POST'));
            $custFields = getTicketFields($_ticket);
            unset($_ticket);
            if (!empty($custFields)) {
                foreach ($custFields as $field) {
                    $fieldname = $field['fieldname'];
                    $value     = \Xmf\Request::getString($fieldname, '', 'POST');

                    $fileid   = '';
                    $filename = '';
                    $file     = '';
                    if (XHELP_CONTROL_FILE == $field['controltype']) {
                        $file     = explode('_', $value);
                        $fileid   = ((isset($file[0]) && '' != $file[0]) ? $file[0] : '');
                        $filename = ((isset($file[1]) && '' != $file[1]) ? $file[1] : '');
                    }

                    if ('' != $field['validation']) {
                        $v[$fieldname][] = new Validation\ValidateRegex(Request::getString('$fieldname', '', 'POST'), $field['validation'], $field['required']);
                    }

                    $aFields[$field['fieldname']] = [
                        'id'           => $field['id'],
                        'name'         => $field['name'],
                        'description'  => $field['desc'],
                        'fieldname'    => $field['fieldname'],
                        'controltype'  => $field['controltype'],
                        'datatype'     => $field['datatype'],
                        'required'     => $field['required'],
                        'fieldlength'  => $field['fieldlength'],
                        'weight'       => $field['weight'],
                        'fieldvalues'  => $field['fieldvalues'],
                        'defaultvalue' => $field['defaultvalue'],
                        'validation'   => $field['validation'],
                        'value'        => $value,
                        'fileid'       => $fileid,
                        'filename'     => $filename,
                    ];
                }
            }
            unset($custFields);

            $session->set('xhelp_custFields', $aFields);
            $session->set('xhelp_ticket', [
                'subject'     => \Xmf\Request::getString('subject', '', 'POST'),
                'description' => htmlspecialchars(\Xmf\Request::getString('description', '', 'POST'), ENT_QUOTES),
                'department'  => $_POST['departments'],
                'priority'    => $_POST['priority'],
            ]);

            // Perform each validation
            $fields = [];
            $errors = [];
            foreach ($v as $fieldname => $validator) {
                if (Xhelp\Utility::checkRules($validator, $errors)) {
                    $fields[$fieldname]['haserrors'] = false;
                } else {
                    //Mark field with error
                    $fields[$fieldname]['haserrors'] = true;
                    $fields[$fieldname]['errors']    = $errors;
                }
            }

            if (!empty($errors)) {
                $session->set('xhelp_validateError', $fields);
                $message = _XHELP_MESSAGE_VALIDATE_ERROR;
                $helper->redirect("ticket.php?id=$xhelp_id&op=edit");
            }

            $oldTicket = [
                'id'            => $ticketInfo->getVar('id'),
                'subject'       => $ticketInfo->getVar('subject', 'n'),
                'description'   => $ticketInfo->getVar('description', 'n'),
                'priority'      => $ticketInfo->getVar('priority'),
                'status'        => Xhelp\Utility::getStatus($ticketInfo->getVar('status')),
                'department'    => $department->getVar('department'),
                'department_id' => $department->getVar('id'),
            ];

            // Change ticket info to new info
            $ticketInfo->setVar('subject', Request::getString('subject', '', 'POST'));        //$_POST['subject']);
            $ticketInfo->setVar('description', Request::getString('description', '', 'POST'));//$_POST['description']);
            $ticketInfo->setVar('department', $_POST['departments']);
            $ticketInfo->setVar('priority', $_POST['priority']);
            $ticketInfo->setVar('posted', time());

            if ($ticketHandler->insert($ticketInfo)) {
                $message = _XHELP_MESSAGE_EDITTICKET;     // Successfully updated ticket

                // Update custom fields
                /** @var \XoopsModules\Xhelp\TicketValuesHandler $ticketValuesHandler */
                $ticketValuesHandler = $helper->getHandler('TicketValues');
                $ticketValues        = $ticketValuesHandler->get($xhelp_id);

                if (is_object($ticketValues)) {
                    foreach ($aFields as $field) {
                        $ticketValues->setVar($field['fieldname'], $_POST[$field['fieldname']]);
                    }
                    if (!$ticketValuesHandler->insert($ticketValues)) {
                        $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED . $ticketValues->getHtmlErrors();
                    }
                }

                $eventService->trigger('edit_ticket', [&$oldTicket, &$ticketInfo]);

                $session->del('xhelp_ticket');
                $session->del('xhelp_validateError');
                $session->del('xhelp_custFields');
            } else {
                $message = _XHELP_MESSAGE_EDITTICKET_ERROR . $ticketInfo->getHtmlErrors();     // Unsuccessfully updated ticket
            }
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        } else {
            $GLOBALS['xoopsOption']['template_main'] = 'xhelp_editTicket.tpl';             // Always set main template before including the header
            require_once XOOPS_ROOT_PATH . '/header.php';

            $criteria = new \Criteria('', '');
            $criteria->setSort('department');
            $departments = $departmentHandler->getObjects($criteria);
            /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
            $staffHandler = $helper->getHandler('Staff');

            foreach ($departments as $dept) {
                $aDept[] = [
                    'id'         => $dept->getVar('id'),
                    'department' => $dept->getVar('department'),
                ];
            }

            // Form validation stuff
            $errors         = [];
            $aElements      = [];
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

            $elements = ['subject', 'description'];
            foreach ($elements as $element) {         // Foreach element in the predefined list
                $xoopsTpl->assign("xhelp_element_$element", 'formButton');
                foreach ($aElements as $aElement) {   // Foreach that has an error
                    if ($aElement == $element) {      // If the names are equal
                        $xoopsTpl->assign("xhelp_element_$element", 'validateError');
                        break;
                    }
                }
            }
            // end form validation stuff

            $javascript = '<script type="text/javascript" src="' . XHELP_BASE_URL . "/include/functions.js\"></script>
<script type=\"text/javascript\" src='" . XHELP_SCRIPT_URL . "/addTicketDeptChange.php?client'></script>
<script type=\"text/javascript\">
<!--
function departments_onchange()
{
    dept = xoopsGetElementById('departments');
    var wl = new Xhelp\WebLib(fieldHandler);
    wl.editticketcustfields(dept.value, $xhelp_id);
}

var fieldHandler = {
    editticketcustfields: function(result){

        var tbl = gE('tblEditTicket');
        var staffCol = gE('staff');";
            $javascript .= "var beforeele = gE('editButtons');\n";
            $javascript .= "tbody = tbl.tBodies[0];\n";
            $javascript .= "xhelpFillCustomFlds(tbody, result, beforeele);\n
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('departments'), 'change', departments_onchange, true);
}

xhelpDOMAddEvent(window, 'load', window_onload, true);
//-->
</script>";
            $ticket     = $session->get('xhelp_ticket');
            if ($ticket) {
                $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
                $xoopsTpl->assign('xhelp_ticket_subject', $ticket['subject']);
                $xoopsTpl->assign('xhelp_ticket_description', $ticket['description']);
                $xoopsTpl->assign('xhelp_ticket_department', $ticket['department']);
                $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept=' . $ticket['department']);
                $xoopsTpl->assign('xhelp_ticket_priority', $ticket['priority']);
            } else {
                $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
                $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
                $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description', 'e'));
                $xoopsTpl->assign('xhelp_ticket_department', $ticketInfo->getVar('department'));
                $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept=' . $ticketInfo->getVar('department'));
                $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
            }

            //** BTW - why do we need xhelp_allowUpload in the template if it will be always set to 0?
            //$xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));
            $xoopsTpl->assign('xhelp_allowUpload', 0);
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
            $xoopsTpl->assign('xhelp_departments', $aDept);
            $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
            $xoopsTpl->assign('xhelp_priorities_desc', [
                5 => _XHELP_PRIORITY5,
                4 => _XHELP_PRIORITY4,
                3 => _XHELP_PRIORITY3,
                2 => _XHELP_PRIORITY2,
                1 => _XHELP_PRIORITY1,
            ]);

            if (Request::hasVar('logFor', 'POST')) {
                $uid      = $_POST['logFor'];
                $username = Xhelp\Utility::getUsername($uid, $displayName);
                $xoopsTpl->assign('xhelp_username', $username);
                $xoopsTpl->assign('xhelp_user_id', $uid);
            } else {
                $xoopsTpl->assign('xhelp_username', Xhelp\Utility::getUsername($xoopsUser->getVar('uid'), $displayName));
                $xoopsTpl->assign('xhelp_user_id', $xoopsUser->getVar('uid'));
            }
            // Used for displaying transparent-background images in IE
            $xoopsTpl->assign('xoops_module_header', $javascript . $xhelp_module_header);
            $xoopsTpl->assign('xhelp_isStaff', $xhelp_isStaff);

            $savedFields = $session->get('xhelp_custFields');
            if ($savedFields) {
                $custFields = $savedFields;
            } else {
                $custFields = getTicketFields($ticketInfo);
            }
            $xoopsTpl->assign('xhelp_hasCustFields', !empty($custFields));
            $xoopsTpl->assign('xhelp_custFields', $custFields);
            $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

            require_once XOOPS_ROOT_PATH . '/footer.php';
        }
        break;
    case 'merge':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_MERGE, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_MERGE;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }
        if ('' === $_POST['ticket2']) {
            $message = _XHELP_MESSAGE_NO_TICKET2;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        $ticket2_id = Request::getInt('ticket2', 0, 'POST');
        $newTicket  = $ticketInfo->merge($ticket2_id);
        if ($newTicket) {
            $returnTicket = $newTicket;
            $message      = _XHELP_MESSAGE_MERGE;
            $eventService->trigger('merge_tickets', [$xhelp_id, $ticket2_id, $returnTicket]);
        } else {
            $returnTicket = $xhelp_id;
            $message      = _XHELP_MESSAGE_MERGE_ERROR;
        }
        $helper->redirect("ticket.php?id=$returnTicket", 3, $message);

        break;
    case 'ownership':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP, $ticketInfo->getVar('department'))) {
            $message = _XHELP_MESSAGE_NO_CHANGE_OWNER;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        if (Request::hasVar('uid', 'POST')) {
            $uid = Request::getInt('uid', 0, 'POST');
        } else {
            $message = _XHELP_MESSAGE_NO_UID;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }
        if (0 != $ticketInfo->getVar('ownership')) {
            $oldOwner = (int)$ticketInfo->getVar('ownership');
        } else {
            $oldOwner = 0; //_XHELP_NO_OWNER;
        }

        $ticketInfo->setVar('ownership', $uid);
        $ticketInfo->setVar('lastUpdated', time());
        if ($ticketHandler->insert($ticketInfo)) {
            $eventService->trigger('update_owner', [&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')]);
            $message = _XHELP_MESSAGE_UPDATE_OWNER;
        }
        $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);

        break;
    case 'print':
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler         = xoops_getHandler('config');
        $xoopsConfigMetaFooter = $configHandler->getConfigsByCat(XOOPS_CONF_METAFOOTER);

        $patterns     = [];
        $patterns[]   = '/pri:([1-5])/';
        $replacements = [];
        $replacements = '<img src="assets/images/priority$1print.png">';

        foreach ($logMessage as $msg) {
            $msgLoggedBy                    = $memberHandler->getUser($msg->getVar('uid'));
            $aPrintMessages[]               = [
                'id'          => $msg->getVar('id'),
                'uid'         => $msg->getVar('uid'),
                'uname'       => Xhelp\Utility::getUsername($msgLoggedBy->getVar('uid'), $displayName),
                'ticketid'    => $msg->getVar('ticketid'),
                'lastUpdated' => $msg->lastUpdated('m'),
                'action'      => preg_replace($patterns, $replacements, $msg->getVar('action')),
            ];
            $all_users[$msg->getVar('uid')] = '';
        }
        unset($logMessage);

        require_once XOOPS_ROOT_PATH . '/class/template.php';
        $xoopsTpl = new \XoopsTpl();
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xhelp_lang_userlookup', 'User Lookup');
        $xoopsTpl->assign('sitename', $xoopsConfig['sitename']);
        $xoopsTpl->assign('xoops_themecss', xoops_getcss());
        $xoopsTpl->assign('xoops_url', XOOPS_URL);
        $xoopsTpl->assign('xhelp_print_logMessages', $aPrintMessages);
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', $department->getVar('department'));
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', Xhelp\Utility::getStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->lastUpdated('m'));
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        if ($giveOwnership) {
            $xoopsTpl->assign('xhelp_ticket_ownerUid', $owner->getVar('uid'));
            $xoopsTpl->assign('xhelp_ticket_ownership', Xhelp\Utility::getUsername($owner, $displayName));
            $xoopsTpl->assign('xhelp_ownerinfo', XOOPS_URL . '/userinfo.php?uid=' . $owner->getVar('uid'));
        }
        $xoopsTpl->assign('xhelp_ticket_closedBy', $ticketInfo->getVar('closedBy'));
        $xoopsTpl->assign('xhelp_ticket_totalTimeSpent', $ticketInfo->getVar('totalTimeSpent'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', Xhelp\Utility::getUsername($user, $displayName));
        $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $xhelp_id));

        $custFields = $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', !empty($custFields));
        $xoopsTpl->assign('xhelp_custFields', $custFields);

        if (isset($aMessages)) {
            $xoopsTpl->assign('xhelp_logMessages', $aMessages);
        } else {
            $xoopsTpl->assign('xhelp_logMessages', 0);
        }
        $xoopsTpl->assign('xhelp_text_claimOwner', _XHELP_TEXT_CLAIM_OWNER);
        $xoopsTpl->assign('xhelp_aOwnership', $aOwnership);

        if ($has_responses) {
            $users  = [];
            $_users = $memberHandler->getUsers(new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN'), true);
            foreach ($_users as $key => $_user) {
                if ((2 == $displayName) && ('' != $_user->getVar('name'))) {
                    $users[$_user->getVar('uid')] = ['uname' => $_user->getVar('name')];
                } else {
                    $users[$_user->getVar('uid')] = ['uname' => $_user->getVar('uname')];
                }
            }
            unset($_users);

            $myTs = \MyTextSanitizer::getInstance();
            //Update arrays with user information
            if (count($aResponses) > 0) {
                for ($i = 0, $iMax = count($aResponses); $i < $iMax; ++$i) {
                    if (isset($users[$aResponses[$i]['uid']])) {      // Add uname to array
                        $aResponses[$i]['uname'] = $users[$aResponses[$i]['uid']]['uname'];
                    } else {
                        $aResponses[$i]['uname'] = $xoopsConfig['anonymous'];
                    }
                }
            }
            $xoopsTpl->assign('xhelp_aResponses', $aResponses);
        } else {
            $xoopsTpl->assign('xhelp_aResponses', 0);
        }
        $xoopsTpl->assign('xhelp_claimOwner', $xoopsUser->getVar('uid'));
        $xoopsTpl->assign('xhelp_hasResponses', $has_responses);
        $xoopsTpl->assign('xoops_meta_robots', $xoopsConfigMetaFooter['meta_robots']);
        $xoopsTpl->assign('xoops_meta_keywords', $xoopsConfigMetaFooter['meta_keywords']);
        $xoopsTpl->assign('xoops_meta_description', $xoopsConfigMetaFooter['meta_description']);
        $xoopsTpl->assign('xoops_meta_rating', $xoopsConfigMetaFooter['meta_rating']);
        $xoopsTpl->assign('xoops_meta_author', $xoopsConfigMetaFooter['meta_author']);
        $xoopsTpl->assign('xoops_meta_copyright', $xoopsConfigMetaFooter['meta_copyright']);

        $module_dir = $xoopsModule->getVar('mid');
        $xoopsTpl->display('db:xhelp_print.tpl');
        exit();
        break;
    case 'updatePriority':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_ADD)) {
            $message = _XHELP_MESSAGE_NO_ADD_TICKET;
            $helper->redirect('index.php', 3, $message);
        }

        if (Request::hasVar('priority', 'POST')) {
            $priority = $_POST['priority'];
        } else {
            $message = _XHELP_MESSAGE_NO_PRIORITY;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }
        $oldPriority = $ticketInfo->getVar('priority');
        $ticketInfo->setVar('priority', $priority);
        $ticketInfo->setVar('lastUpdated', time());
        if ($ticketHandler->insert($ticketInfo)) {
            $eventService->trigger('update_priority', [&$ticketInfo, $oldPriority]);
            $message = _XHELP_MESSAGE_UPDATE_PRIORITY;
        } else {
            $message = _XHELP_MESSAGE_UPDATE_PRIORITY_ERROR . '. ';
        }
        $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        break;
    case 'updateStatus':
        $addResponse   = $changeStatus = false;
        $statusClosed  = $statusReopened = false;
        $responseError = $ticketError = false;

        //1. Check if either a response was added or status was changed
        $addResponse  = ('' != \Xmf\Request::getString('response', '', 'POST'));
        $changeStatus = ($_POST['status'] != $ticketInfo->getVar('status'));

        if ($addResponse || $changeStatus) {
            //2. Update Ticket LastUpdated time
            $ticketInfo->setVar('lastUpdated', time());

            //3. Add Response (if necessary)
            if (true === $addResponse) {
                if ($ticketInfo->canAddResponse($xoopsUser)) {
                    $userIP        = xoops_getenv('REMOTE_ADDR');
                    $newResponse   = $ticketInfo->addResponse($xoopsUser->getVar('uid'), $xhelp_id, $_POST['response'], $ticketInfo->getVar('lastUpdated'), $userIP, 0, 0, true);
                    $responseError = !is_object($newResponse);
                }
            }

            //4. Update Status (if necessary)
            if (true === $changeStatus) {
                //Check if the current staff member can change status
                if ($staff->checkRoleRights(XHELP_SEC_TICKET_STATUS, $ticketInfo->getVar('department'))) {
                    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
                    $statusHandler = $helper->getHandler('Status');
                    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
                    $staffHandler = $helper->getHandler('Staff');

                    $oldStatus = $statusHandler->get($ticketInfo->getVar('status'));
                    $newStatus = $statusHandler->get(Request::getInt('status', 0, 'POST'));
                    $ticketInfo->setVar('status', $_POST['status']);

                    if (XHELP_STATE_RESOLVED == $newStatus->getVar('state')
                        && XHELP_STATE_UNRESOLVED == $oldStatus->getVar('state')) {
                        //Closing the ticket
                        $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                        $statusClosed = true;
                    } elseif (XHELP_STATE_RESOLVED == $oldStatus->getVar('state')
                              && XHELP_STATE_UNRESOLVED == $newStatus->getVar('state')) {
                        //Re-opening the ticket
                        $ticketInfo->setVar('overdueTime', $ticketInfo->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));
                        $statusReopened = true;
                    }
                }
            }

            //5. Save Ticket
            $ticketError = !$ticketHandler->insert($ticketInfo);

            //6. Fire Necessary Events, set response messages
            if (true === $addResponse && false === $responseError) {
                $eventService->trigger('new_response', [&$ticketInfo, &$newResponse]);
                $message .= _XHELP_MESSAGE_ADDRESPONSE;
            } elseif (true === $addResponse && true === $responseError) {
                $message .= _XHELP_MESSAGE_ADDRESPONSE_ERROR;
            }

            if (true === $changeStatus && false === $ticketError) {
                if ($statusClosed) {
                    $eventService->trigger('close_ticket', [&$ticketInfo]);
                } elseif ($statusReopened) {
                    $eventService->trigger('reopen_ticket', [&$ticketInfo]);
                } else {
                    $eventService->trigger('update_status', [&$ticketInfo, &$oldStatus, &$newStatus]);
                }

                $message .= _XHELP_MESSAGE_UPDATE_STATUS;
            } elseif (true === $changeStatus && true === $ticketError) {
                $message .= _XHELP_MESSAGE_UPDATE_STATUS_ERROR . '. ';
            }
        } else {
            //No Changes Made
            //todo: Add new language constant for this
            $message = _XHELP_MESSAGE_NO_CHANGE_STATUS;
        }

        //Notify user of changes
        $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);

        break;
    case 'staff':
        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = $helper->getHandler('Status');
        $eventService->trigger('view_ticket', [&$ticketInfo]);
        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staff_ticketDetails.tpl';   // Set template
        require_once XOOPS_ROOT_PATH . '/header.php';                                 // Include

        $users  = [];
        $_users = $memberHandler->getUsers(new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN'), true);
        foreach ($_users as $key => $_user) {
            if ((2 == $displayName) && ('' != $_user->getVar('name'))) {
                $users[$key] = [
                    'uname'       => $_user->getVar('name'),
                    'user_sig'    => $_user->getVar('user_sig'),
                    'user_avatar' => $_user->getVar('user_avatar'),
                ];
            } else {
                $users[$key] = [
                    'uname'       => $_user->getVar('uname'),
                    'user_sig'    => $_user->getVar('user_sig'),
                    'user_avatar' => $_user->getVar('user_avatar'),
                ];
            }
        }

        $criteria = new \Criteria('', '');
        $criteria->setSort('department');
        $alldepts = $departmentHandler->getObjects($criteria);
        foreach ($alldepts as $dept) {
            $aDept[$dept->getVar('id')] = $dept->getVar('department');
        }
        unset($_users);
        $staffArray = [];
        $_staff     = $staffHandler->getObjects(new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN'), true);
        foreach ($_staff as $key => $_user) {
            $staffArray[$key] = $_user->getVar('attachSig');
        }
        unset($_staff);
        $staffReviews = $ticketInfo->getReviews();

        $myTs = \MyTextSanitizer::getInstance();
        //Update arrays with user information
        if (count($aResponses) > 0) {
            for ($i = 0, $iMax = count($aResponses); $i < $iMax; ++$i) {
                if (isset($users[$aResponses[$i]['uid']])) {      // Add uname to array
                    $aResponses[$i]['uname']       = $users[$aResponses[$i]['uid']]['uname'];
                    $aResponses[$i]['user_sig']    = $myTs->displayTarea($users[$aResponses[$i]['uid']]['user_sig'], true);
                    $aResponses[$i]['user_avatar'] = XOOPS_URL . '/uploads/' . ($users[$aResponses[$i]['uid']]['user_avatar'] ?: 'blank.gif');
                } else {
                    $aResponses[$i]['uname'] = $xoopsConfig['anonymous'];
                }
                $aResponses[$i]['staffRating'] = _XHELP_RATING0;

                if (isset($staffArray[$aResponses[$i]['uid']])) {       // Add attachSig to array
                    $aResponses[$i]['attachSig'] = $staffArray[$aResponses[$i]['uid']];
                }

                if (count($staffReviews) > 0) {                   // Add staffRating to array
                    foreach ($staffReviews as $review) {
                        if ($aResponses[$i]['id'] == $review->getVar('responseid')) {
                            $aResponses[$i]['staffRating'] = Xhelp\Utility::getRating($review->getVar('rating'));
                        }
                    }
                }
            }
        }
        if (isset($aMessages)) {
            for ($i = 0, $iMax = count($aMessages); $i < $iMax; ++$i) {        // Fill other values for log messages
                if (isset($users[$aMessages[$i]['uid']])) {
                    $aMessages[$i]['uname'] = $users[$aMessages[$i]['uid']]['uname'];
                } else {
                    $aMessages[$i]['uname'] = $xoopsConfig['anonymous'];
                }
            }
        }
        if (1 == $helper->getConfig('xhelp_staffTicketActions')) {
            for ($i = 0, $iMax = count($aOwnership); $i < $iMax; ++$i) {
                if (isset($users[$aOwnership[$i]['uid']])) {
                    $aOwnership[$i]['uname'] = $users[$aOwnership[$i]['uid']]['uname'];
                }
            }
        }
        unset($users);

        // Get list of users notified of changes to ticket
        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \Criteria('ticketid', $xhelp_id);
        $criteria->setOrder('ASC');
        $criteria->setSort('email');
        $notifiedUsers = $ticketEmailsHandler->getObjects($criteria);
        $aNotified     = [];
        foreach ($notifiedUsers as $nUser) {
            $aNotified[] = [
                'email'       => $nUser->getVar('email'),
                'suppress'    => $nUser->getVar('suppress'),
                'suppressUrl' => XOOPS_URL . "/modules/xhelp/ticket.php?id=$xhelp_id&amp;op=changeSuppress&amp;email=" . $nUser->getVar('email'),
            ];
        }
        unset($notifiedUsers);

        $uid = $xoopsUser->getVar('uid');
        $xoopsTpl->assign('xhelp_uid', $uid);

        // Smarty variables
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
        $xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
        $xoopsTpl->assign('xhelp_ticket_uid', $ticketInfo->getVar('uid'));
        $submitUser = $memberHandler->getUser($ticketInfo->getVar('uid'));
        $xoopsTpl->assign(
            'xhelp_user_avatar',
            XOOPS_URL . '/uploads/' . (($submitUser
                                        && '' != $submitUser->getVar('user_avatar')) ? $submitUser->getVar('user_avatar') : 'blank.gif')
        );
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject', 's'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', (isset($departments[$ticketInfo->getVar('department')]) ? $departments[$ticketInfo->getVar('department')]->getVar('department') : _XHELP_TEXT_NO_DEPT));
        $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept=' . $ticketInfo->getVar('department'));
        $xoopsTpl->assign('xhelp_departmentid', $ticketInfo->getVar('department'));
        $xoopsTpl->assign('xhelp_departments', $aDept);
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', $ticketInfo->getVar('status'));
        $xoopsTpl->assign('xhelp_text_status', Xhelp\Utility::getStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_userIP', $ticketInfo->getVar('userIP'));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->lastUpdated('m'));
        $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
        $xoopsTpl->assign('xhelp_priorities_desc', [
            5 => _XHELP_PRIORITY5,
            4 => _XHELP_PRIORITY4,
            3 => _XHELP_PRIORITY3,
            2 => _XHELP_PRIORITY2,
            1 => _XHELP_PRIORITY1,
        ]);
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        if ($giveOwnership) {
            $xoopsTpl->assign('xhelp_ticket_ownerUid', $owner->getVar('uid'));
            $xoopsTpl->assign('xhelp_ticket_ownership', Xhelp\Utility::getUsername($owner, $displayName));
            $xoopsTpl->assign('xhelp_ownerinfo', XOOPS_URL . '/userinfo.php?uid=' . $owner->getVar('uid'));
        }
        $xoopsTpl->assign('xhelp_ticket_closedBy', $ticketInfo->getVar('closedBy'));
        $xoopsTpl->assign('xhelp_ticket_totalTimeSpent', $ticketInfo->getVar('totalTimeSpent'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', ($user ? Xhelp\Utility::getUsername($user, $displayName) : $xoopsConfig['anonymous']));
        $xoopsTpl->assign('xhelp_userlevel', ($user ? $user->getVar('level') : 0));
        $xoopsTpl->assign('xhelp_email', ($user ? $user->getVar('email') : ''));
        $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $xhelp_id));
        $xoopsTpl->assign('xhelp_notifiedUsers', $aNotified);
        $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);

        if (isset($aMessages)) {
            $xoopsTpl->assign('xhelp_logMessages', $aMessages);
        } else {
            $xoopsTpl->assign('xhelp_logMessages', 0);
        }
        $xoopsTpl->assign('xhelp_aOwnership', $aOwnership);
        if ($has_responses) {
            $xoopsTpl->assign('xhelp_aResponses', $aResponses);
        }
        unset($aResponses);
        if ($has_files) {
            $xoopsTpl->assign('xhelp_aFiles', $aFiles);
            $xoopsTpl->assign('xhelp_hasTicketFiles', $has_ticketFiles);
        } else {
            $xoopsTpl->assign('xhelp_aFiles', false);
            $xoopsTpl->assign('xhelp_hasTicketFiles', false);
        }
        $xoopsTpl->assign('xhelp_claimOwner', $xoopsUser->getVar('uid'));
        $xoopsTpl->assign('xhelp_hasResponses', $has_responses);
        $xoopsTpl->assign('xhelp_hasFiles', $has_files);
        $xoopsTpl->assign('xhelp_hasTicketFiles', $has_ticketFiles);
        $xoopsTpl->assign('xhelp_filePath', XOOPS_URL . '/uploads/xhelp/');
        $module_dir = $xoopsModule->getVar('mid');
        $xoopsTpl->assign('xhelp_admin', $xoopsUser->isAdmin($module_dir));
        $xoopsTpl->assign('xhelp_has_lastSubmitted', $has_lastTickets);
        $xoopsTpl->assign('xhelp_lastSubmitted', $aLastTickets);
        $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->getVar('name') . ' - ' . $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_showActions', $helper->getConfig('xhelp_staffTicketActions'));

        $xoopsTpl->assign('xhelp_has_changeOwner', false);
        if ($ticketInfo->getVar('uid') == $xoopsUser->getVar('uid')) {
            $xoopsTpl->assign('xhelp_has_addResponse', true);
        } else {
            $xoopsTpl->assign('xhelp_has_addResponse', false);
        }
        $xoopsTpl->assign('xhelp_has_editTicket', false);
        $xoopsTpl->assign('xhelp_has_deleteTicket', false);
        $xoopsTpl->assign('xhelp_has_changePriority', false);
        $xoopsTpl->assign('xhelp_has_changeStatus', false);
        $xoopsTpl->assign('xhelp_has_editResponse', false);
        $xoopsTpl->assign('xhelp_has_mergeTicket', false);
        $xoopsTpl->assign('xhelp_has_faqAdd', false);
        $colspan = 5;

        $checkRights = [
            XHELP_SEC_TICKET_OWNERSHIP      => ['xhelp_has_changeOwner', false],
            XHELP_SEC_RESPONSE_ADD          => ['xhelp_has_addResponse', true],
            XHELP_SEC_TICKET_EDIT           => ['xhelp_has_editTicket', true],
            XHELP_SEC_TICKET_DELETE         => ['xhelp_has_deleteTicket', true],
            XHELP_SEC_TICKET_MERGE          => ['xhelp_has_mergeTicket', true],
            XHELP_SEC_TICKET_PRIORITY       => ['xhelp_has_changePriority', true],
            XHELP_SEC_TICKET_STATUS         => ['xhelp_has_changeStatus', false],
            XHELP_SEC_RESPONSE_EDIT         => ['xhelp_has_editResponse', false],
            XHELP_SEC_FILE_DELETE           => ['xhelp_has_deleteFile', false],
            XHELP_SEC_FAQ_ADD               => ['xhelp_has_faqAdd', false],
            XHELP_SEC_TICKET_TAKE_OWNERSHIP => ['xhelp_has_takeOwnership', false],
        ];

        // See if this user is accepted for this ticket
        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $xhelp_id));
        $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails = $ticketEmailsHandler->getObjects($criteria);

        foreach ($checkRights as $right => $desc) {
            if ((XHELP_SEC_RESPONSE_ADD == $right) && (count($ticketEmails) > 0)) {
                //Is this user in the ticket emails list (should be treated as a user)
                $xoopsTpl->assign($desc[0], true);
                ++$colspan;
                continue;
            }
            if ((XHELP_SEC_TICKET_STATUS == $right) && count($ticketEmails) > 0) {
                //Is this user in the ticket emails list (should be treated as a user)
                $xoopsTpl->assign($desc[0], true);
                ++$colspan;
                continue;
            }
            $hasRights = $staff->checkRoleRights($right, $ticketInfo->getVar('department'));
            if ($hasRights) {
                $xoopsTpl->assign($desc[0], true);
            } else {
                if ($desc[1]) {
                    $colspan--;
                }
            }
        }
        $xoopsTpl->assign('xhelp_actions_colspan', $colspan);

        $criteria = new \Criteria('', '');
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
        unset($statuses);

        $xoopsTpl->assign('xhelp_statuses', $aStatuses);

        $custFields = $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', !empty($custFields));
        $xoopsTpl->assign('xhelp_custFields', $custFields);
        unset($custFields);
        $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);

        require_once XOOPS_ROOT_PATH . '/footer.php';
        break;
    case 'user':
        // Check if user has permission to view ticket
        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $xhelp_id));
        $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails = $ticketEmailsHandler->getObjects($criteria);
        if (0 == count($ticketEmails)) {
            $helper->redirect('index.php', 3, _XHELP_ERROR_INV_USER);
        }

        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_user_ticketDetails.tpl';   // Set template
        require_once XOOPS_ROOT_PATH . '/header.php';                                // Include
        $responses = $ticketInfo->getResponses();
        foreach ($responses as $response) {
            $hasFiles = false;
            foreach ($aFiles as $file) {
                if ($file['responseid'] == $response->getVar('id')) {
                    $hasFiles = true;
                    break;
                }
            }

            $staffReview = $staffReviewHandler->getReview($xhelp_id, $response->getVar('id'), $xoopsUser->getVar('uid'));
            if (is_iterable($staffReview) && count($staffReview) > 0) {
                $review = $staffReview[0];
            }
            //$responseOwner = $memberHandler->getUser($response->getVar('uid'));

            $aResponses[] = [
                'id'          => $response->getVar('id'),
                'uid'         => $response->getVar('uid'),
                'uname'       => '',
                'ticketid'    => $response->getVar('ticketid'),
                'message'     => $response->getVar('message'),
                'timeSpent'   => $response->getVar('timeSpent'),
                'updateTime'  => $response->posted('m'),
                'userIP'      => $response->getVar('userIP'),
                'rating'      => isset($review) ? Xhelp\Utility::getRating($review->getVar('rating')) : 0,
                'user_sig'    => '',
                'private'     => $response->getVar('private'),
                'hasFiles'    => $hasFiles,
                'user_avatar' => XOOPS_URL . '/uploads/blank.gif',
            ];
            //XOOPS_URL .'/uploads/' .(($responseOwner)?$responseOwner->getVar('user_avatar') : 'blank.gif'));

            $all_users[$response->getVar('uid')] = '';
        }

        if (isset($review)) {
            unset($review);
        }
        $staffArray = [];
        $_staff     = $staffHandler->getObjects(new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN'), true);
        foreach ($_staff as $key => $_user) {
            $staffArray[$key] = $_user->getVar('attachSig');
        }
        unset($_staff);

        $users  = [];
        $_users = $memberHandler->getUsers(new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN'), true);
        foreach ($_users as $key => $_user) {
            $users[$key] = [
                'uname'       => Xhelp\Utility::getUsername($_user, $helper->getConfig('xhelp_displayName')),
                //Display signature if user is a staff member + has set signature to display
                //or user with signature set to display
                'user_sig'    => (isset($staffArray[$key]) && $staffArray[$key])
                                 || (!isset($staffArray[$key])
                                     && $user->getVar('attachsig')) ? $_user->getVar('user_sig') : '',
                'user_avatar' => mb_strlen($_user->getVar('user_avatar')) ? $_user->getVar('user_avatar') : 'blank.gif',
            ];
        }
        unset($_users);
        unset($_user);
        unset($all_users);

        for ($i = 0, $iMax = count($aResponses); $i < $iMax; ++$i) {
            $_response = $aResponses[$i];
            $_uid      = $_response['uid'];
            if (isset($users[$_uid])) {
                $aResponses[$i]['user_sig']    = $users[$_uid]['user_sig'];
                $aResponses[$i]['user_avatar'] = XOOPS_URL . '/uploads/' . $users[$_uid]['user_avatar'];
                $aResponses[$i]['uname']       = $users[$_uid]['uname'];
            }
        }
        unset($users);

        $has_responses = count($responses) > 0;
        unset($responses);

        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = $helper->getHandler('Status');
        $myStatus      = $statusHandler->get($ticketInfo->getVar('status'));

        // Smarty variables
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
        $reopenTicket = $helper->getConfig('xhelp_allowReopen') && 2 === $myStatus->getVar('state');
        $xoopsTpl->assign('xhelp_reopenTicket', $reopenTicket);
        $xoopsTpl->assign('xhelp_allowResponse', (2 != $myStatus->getVar('state')) || $reopenTicket);
        $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
        $xoopsTpl->assign('xhelp_ticket_uid', $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', $department->getVar('department'));
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', $myStatus->getVar('description')); // Xhelp\Utility::getStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->posted('m'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', $user->getVar('uname'));
        $xoopsTpl->assign('xhelp_email', $user->getVar('email'));
        $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
        $xoopsTpl->assign('xhelp_priorities_desc', [
            5 => _XHELP_PRIORITY5,
            4 => _XHELP_PRIORITY4,
            3 => _XHELP_PRIORITY3,
            2 => _XHELP_PRIORITY2,
            1 => _XHELP_PRIORITY1,
        ]);
        $xoopsTpl->assign('xhelp_uid', $xoopsUser->getVar('uid'));
        if ($has_responses) {
            $xoopsTpl->assign('xhelp_aResponses', $aResponses);
        }
        if ($has_files) {
            $xoopsTpl->assign('xhelp_aFiles', $aFiles);
            $xoopsTpl->assign('xhelp_hasTicketFiles', $has_ticketFiles);
        } else {
            $xoopsTpl->assign('xhelp_aFiles', false);
            $xoopsTpl->assign('xhelp_hasTicketFiles', false);
        }
        $xoopsTpl->assign('xhelp_claimOwner', $xoopsUser->getVar('uid'));
        $xoopsTpl->assign('xhelp_hasResponses', $has_responses);
        $xoopsTpl->assign('xhelp_hasFiles', $has_files);
        $xoopsTpl->assign('xhelp_filePath', XOOPS_URL . '/uploads/xhelp/');
        $xoopsTpl->assign('xoops_pagetitle', $xoopsModule->getVar('name') . ' - ' . $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $xhelp_id));

        $custFields = $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', !empty($custFields));
        $xoopsTpl->assign('xhelp_custFields', $custFields);
        $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);
        $xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));

        require_once XOOPS_ROOT_PATH . '/footer.php';
        break;
    case 'userResponse':
        if (Request::hasVar('newResponse', 'POST')) {
            // Check if user has permission to view ticket
            /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
            $ticketEmailsHandler = $helper->getHandler('TicketEmails');
            $criteria            = new \Criteria('ticketid', $xhelp_id);
            $ticketEmails        = $ticketEmailsHandler->getObjects($criteria);
            $canChange           = false;
            foreach ($ticketEmails as $ticketEmail) {
                if ($xoopsUser->getVar('uid') == $ticketEmail->getVar('uid')) {
                    $canChange = true;
                    break;
                }
            }
            $errors = [];
            /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
            $statusHandler = $helper->getHandler('Status');
            if ($canChange) {
                $oldStatus = $statusHandler->get($ticketInfo->getVar('status'));
                if (2 == $oldStatus->getVar('state')) {     //If the ticket is resolved
                    $ticketInfo->setVar('closedBy', 0);
                    $ticketInfo->setVar('status', 1);
                    $ticketInfo->setVar('overdueTime', $ticketInfo->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));
                } elseif (Request::hasVar('closeTicket', 'POST') && 1 === (int)$_POST['closeTicket']) { // If the user closes the ticket
                    $ticketInfo->setVar('closedBy', $ticketInfo->getVar('uid'));
                    $ticketInfo->setVar('status', 2);   // Todo: make moduleConfig for default resolved status?
                }
                $ticketInfo->setVar('lastUpdated', $ticketInfo->lastUpdated('m'));

                if ($ticketHandler->insert($ticketInfo, true)) {   // Insert the ticket
                    $newStatus = $statusHandler->get($ticketInfo->getVar('status'));

                    if (2 == $newStatus->getVar('state')) {
                        $eventService->trigger('close_ticket', [&$ticketInfo]);
                    } elseif ($oldStatus->getVar('id') != $newStatus->getVar('id')
                              && 2 != $newStatus->getVar('state')) {
                        $eventService->trigger('update_status', [&$ticketInfo, &$oldStatus, &$newStatus]);
                    }
                }
                if ('' != \Xmf\Request::getString('userResponse', '', 'POST')) {       // If the user does not add any text in the response
                    /** @var \XoopsModules\Xhelp\Response $newResponse */
                    $newResponse = $responseHandler->create();
                    $newResponse->setVar('uid', $xoopsUser->getVar('uid'));
                    $newResponse->setVar('ticketid', $xhelp_id);
                    $newResponse->setVar('message', \Xmf\Request::getString('userResponse', '', 'POST'));
                    //      $newResponse->setVar('updateTime', $newResponse->posted('m'));
                    $newResponse->setVar('updateTime', time());
                    $newResponse->setVar('userIP', getenv('REMOTE_ADDR'));

                    if ($responseHandler->insert($newResponse)) {
                        $eventService->trigger('new_response', [&$ticketInfo, &$newResponse]);
                        $message = _XHELP_MESSAGE_USER_MOREINFO;

                        if ($helper->getConfig('xhelp_allowUpload')) {    // If uploading is allowed
                            if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
                                if (!$ret = $ticketInfo->checkUpload('userfile', $allowed_mimetypes, $errors)) {
                                    $errorstxt = implode('<br>', $errors);

                                    $message = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                                    $helper->redirect('addTicket.php', 5, $message);
                                }
                                $file = $ticketInfo->storeUpload('userfile', $newResponse->getVar('id'), $allowed_mimetypes);
                            }
                        }
                    } else {
                        $message = _XHELP_MESSAGE_USER_MOREINFO_ERROR;
                    }
                } elseif (2 != $newStatus->getVar('state')) {
                    $message = _XHELP_MESSAGE_USER_NO_INFO;
                } else {
                    $message = _XHELP_MESSAGE_UPDATE_STATUS;
                }
            } else {
                $message = _XHELP_MESSAGE_NOT_USER;
            }
            redirect_header("ticket.php?id=$xhelp_id", 3, $message);
        }
        break;
    case 'deleteFile':
        if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_FILE_DELETE, $ticketInfo->getVar('department'))) {
            $message = _AM_XHELP_MESSAGE_NO_DELETE_FILE;
            $helper->redirect("ticket.php?id=$xhelp_id", 3, $message);
        }

        if (!isset($_GET['fileid'])) {
            $message = '';
            $helper->redirect("ticket.phpid=$xhelp_id", 3, $message);
        }

        if (Request::hasVar('field', 'GET')) {      // Remove filename from custom field
            $field = $_GET['field'];
            /** @var \XoopsModules\Xhelp\TicketValuesHandler $ticketValuesHandler */
            $ticketValuesHandler = $helper->getHandler('TicketValues');
            $ticketValues        = $ticketValuesHandler->get($xhelp_id);

            $ticketValues->setVar($field, '');
            $ticketValuesHandler->insert($ticketValues, true);
        }

        /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
        $fileHandler = $helper->getHandler('File');
        $fileid      = Request::getInt('fileid', 0, 'GET');
        $file        = $fileHandler->get($fileid);

        if (!$fileHandler->delete($file, true)) {
            $helper->redirect("ticket.php?id=$xhelp_id", 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
        }
        $eventService->trigger('delete_file', [&$file]);
        $helper->redirect("ticket.php?id=$xhelp_id");

        break;
    default:
        $helper->redirect('index.php', 3);
        break;
}

/**
 * @param Ticket $ticket
 * @return array
 */
function &getTicketFields(Ticket $ticket): array
{
    $helper = Xhelp\Helper::getInstance();
    $ret    = [];
    /** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
    $ticketFieldDepartmentHandler = $helper->getHandler('TicketFieldDepartment');
    $fields                       = $ticketFieldDepartmentHandler->fieldsByDepartment($ticket->getVar('department'));
    $values                       = $ticket->getCustFieldValues(true);
    if (!empty($fields)) {
        foreach ($fields as $field) {
            $_arr             = $field->toArray();
            $fieldname        = $_arr['fieldname'];
            $_arr['value']    = $values[$fieldname]['value'];
            $_arr['fileid']   = $values[$fieldname]['fileid'];
            $_arr['filename'] = $values[$fieldname]['filename'];
            $ret[]            = $_arr;
        }
    }
    return $ret;
}
