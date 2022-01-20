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
use XoopsModules\Xhelp\{
    EventService,
    DepartmentHandler,
    Helper,
    StaffHandler,
    TicketHandler,
    Utility,
    Validation
};

/** @var DepartmentHandler $departmentHandler */
/** @var Helper $helper */

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';

$helper = Helper::getInstance();

global $xoopsUser, $xhelp_isStaff, $xoopsTpl, $session, $staff, $xhelp_module_header, $allowed_mimetypes, $xoopsUserIsAdmin, $eventService, $xoopsModule;

if (Request::hasVar('deptid', 'GET')) {
    $dept_id = Request::getInt('deptid', 0, 'GET');
}

if (Request::hasVar('view_id', 'GET')) {
    $view_id = Request::getInt('view_id', 0, 'GET');
    setcookie('xhelp_logMode', (string)$view_id, time() + 60 * 60 * 24 * 30);
    if (isset($dept_id)) {
        $helper->redirect("addTicket.php&deptid=$dept_id");
    } else {
        $helper->redirect('addTicket.php');
    }
} else {
    if (isset($_COOKIE['xhelp_logMode'])) {
        setcookie('xhelp_logMode', $_COOKIE['xhelp_logMode'], time() + 60 * 60 * 24 * 30);
    } else {
        setcookie('xhelp_logMode', '1', time() + 60 * 60 * 24 * 30);
    }
}

$eventService = new EventService();

/*
$eventService->advise('new_ticket', xhelp_notificationService::getInstance());
$eventService->advise('new_ticket', xhelp_logService::getInstance());
$eventService->advise('new_ticket', xhelp_cacheService::getInstance());
$eventService->advise('new_response', xhelp_logService::getInstance());
$eventService->advise('new_response', xhelp_notificationService::getInstance());
$eventService->advise('update_owner', xhelp_notificationService::getInstance());
$eventService->advise('update_owner', xhelp_logService::getInstance());
*/

/** @var TicketHandler $ticketHandler */
/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
/** @var StaffHandler $staffHandler */
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');
/** @var \XoopsGroupPermHandler $grouppermHandler */
$grouppermHandler = xoops_getHandler('groupperm');
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
/** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
$membershipHandler = $helper->getHandler('Membership');
/** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
$ticketFieldDepartmentHandler = $helper->getHandler('TicketFieldDepartment');

$module_id = $xoopsModule->getVar('mid');

if ($xoopsUser) {
    if (!isset($dept_id)) {
        $dept_id = (int)Utility::getMeta('default_department');
    }

    if (Request::hasVar('saveTicket', 'GET') && 1 === $_GET['saveTicket']) {
        saveTicket();
    }

    //    if (!isset($_POST['addTicket'])) {                           // Initial load of page
    if (Request::hasVar('addTicket', 'POST')) {
        $dept_id = Request::getInt('departments', 0, 'POST');

        // require_once XHELP_CLASS_PATH . '/validator.php';
        $v                  = [];
        $v['subject'][]     = new Validation\ValidateLength(Request::getString('subject', '', 'POST'), 2, 255);
        $v['description'][] = new Validation\ValidateLength(Request::getString('description', '', 'POST'), 2);

        // Get current dept's custom fields
        $fields  = $ticketFieldDepartmentHandler->fieldsByDepartment($dept_id, true);
        $aFields = [];

        foreach ($fields as $field) {
            $values = $field->getVar('fieldvalues');
            if (XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
                $values = [1 => _YES, 0 => _NO];
            }
            $fieldname = $field->getVar('fieldname');

            if (XHELP_CONTROL_FILE != $field->getVar('controltype')) {
                $checkField = Request::getString('fieldname', '', 'POST'); //array?
            } else {
                $checkField = $_FILES[$fieldname];
            }

            $v[$fieldname][] = new Validation\ValidateRegex($checkField, $field->getVar('validation'), $field->getVar('required'));

            $aFields[$field->getVar('id')] = [
                'name'         => $field->getVar('name'),
                'desc'         => $field->getVar('description'),
                'fieldname'    => $field->getVar('fieldname'),
                'defaultvalue' => $field->getVar('defaultvalue'),
                'controltype'  => $field->getVar('controltype'),
                'required'     => $field->getVar('required'),
                'fieldlength'  => $field->getVar('fieldlength'),
                'maxlength'    => $field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50,
                'weight'       => $field->getVar('weight'),
                'fieldvalues'  => $values,
                'validation'   => $field->getVar('validation'),
            ];
        }

        saveTicket($aFields);      // Save ticket information in a session

        // Perform each validation
        $fields = [];
        $errors = [];
        foreach ($v as $fieldname => $validator) {
            if (Utility::checkRules($validator, $errors)) {
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
            $helper->redirect('addTicket.php');
        }

        //        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        /** @var \XoopsModules\Xhelp\Ticket $ticket */
        $ticket = $ticketHandler->create();
        $ticket->setVar('uid', Request::getInt('user_id', 0, 'POST'));
        $ticket->setVar('subject', Request::getString('subject', '', 'POST'));
        $ticket->setVar('description', Request::getString('description', '', 'POST'));
        $ticket->setVar('department', $dept_id);
        $ticket->setVar('priority', Request::getInt('priority', 0, 'POST'));
        if ($xhelp_isStaff && 2 === $_COOKIE['xhelp_logMode']) {
            $ticket->setVar('status', Request::getInt('status', 0, 'POST'));    // Set status
            //            if (Request::hasVar('owner', 'POST')) {  //Check if user claimed ownership
            if (!Request::hasVar('owner', 'POST')) {
                if (Request::getInt('owner', 0, 'POST') > 0) {
                    $oldOwner = 0;
                    $session->set('xhelp_oldOwner', $oldOwner);
                    $ticket->setVar('ownership', Request::getInt('owner', 0, 'POST'));
                    $session->set('xhelp_changeOwner', true);
                }
            }
            $session->set('xhelp_ticket_ownership', Request::getInt('owner', 0, 'POST'));  // Store in session
        } else {
            $ticket->setVar('status', 1);
        }
        $ticket->setVar('posted', time());
        $ticket->setVar('userIP', getenv('REMOTE_ADDR'));
        $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));

        $aUploadFiles = [];
        if ($helper->getConfig('xhelp_allowUpload')) {
            foreach ($_FILES as $key => $aFile) {
                $pos = mb_strpos($key, 'userfile');
                if (false !== $pos
                    && is_uploaded_file($aFile['tmp_name'])) {     // In the userfile array and uploaded file?
                    if (false !== ($ret = $ticket->checkUpload($key, $allowed_mimetypes, $errors))) {
                        $aUploadFiles[$key] = $aFile;
                    } else {
                        $errorstxt = implode('<br>', $errors);
                        $message   = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                        $helper->redirect('addTicket.php', 5, $message);
                    }
                }
            }
        }

        if ($ticketHandler->insert($ticket)) {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $newUser       = $memberHandler->getUser($ticket->getVar('uid'));
            $ticket->addSubmitter($newUser->getVar('email'), $newUser->getVar('uid'));

            if (count($aUploadFiles) > 0) {   // Has uploaded files?
                foreach ($aUploadFiles as $key => $aFile) {
                    $file = $ticket->storeUpload($key, null, $allowed_mimetypes);
                    $eventService->trigger('new_file', [&$ticket, &$file]);
                }
            }

            // Add custom field values to db
            /** @var \XoopsModules\Xhelp\TicketValuesHandler $ticketValuesHandler */
            $ticketValuesHandler = $helper->getHandler('TicketValues');
            /** @var \XoopsModules\Xhelp\TicketValues $ticketValues */
            $ticketValues = $ticketValuesHandler->create();

            foreach ($aFields as $field) {
                $fieldname = $field['fieldname'];
                $fieldtype = $field['controltype'];

                if (XHELP_CONTROL_FILE == $fieldtype) {               // If custom field was a file upload
                    if ($helper->getConfig('xhelp_allowUpload')) {    // If uploading is allowed
                        if (is_uploaded_file($_FILES[$fieldname]['tmp_name'])) {
                            if (!$ret = $ticket->checkUpload($fieldname, $allowed_mimetypes, $errors)) {
                                $errorstxt = implode('<br>', $errors);

                                $message = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                                $helper->redirect('addTicket.php', 5, $message);
                            }
                            if (false !== ($file = $ticket->storeUpload($fieldname, -1, $allowed_mimetypes))) {
                                $ticketValues->setVar($fieldname, $file->getVar('id') . '_' . $_FILES[$fieldname]['name']);
                            }
                        }
                    }
                } else {
                    $fieldvalue = Request::getString($fieldname, '', 'POST');
                    $ticketValues->setVar($fieldname, $fieldvalue);
                }
            }
            $ticketValues->setVar('ticketid', $ticket->getVar('id'));

            if (!$ticketValuesHandler->insert($ticketValues)) {
                $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED;
            }

            $eventService->trigger('new_ticket', [&$ticket]);

            if ($session->get('xhelp_changeOwner')) {
                $oldOwner = $session->get('xhelp_oldOwner');
                $eventService->trigger('update_owner', [&$ticket, $oldOwner, $xoopsUser->getVar('uid')]);
                $session->del('xhelp_changeOwner');
                $session->del('xhelp_oldOwner');
                $session->del('xhelp_ticket_ownership');
            }

            // Add response
            if ($xhelp_isStaff
                && 2 === $_COOKIE['xhelp_logMode']) {     // Make sure user is a staff member and is using advanced form
                // if ('' != $_POST['response']) {                   // Don't run if no value for response
                if (Request::hasVar('response', 'POST')) {
                    /** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
                    $responseHandler = $helper->getHandler('Response');
                    /** @var \XoopsModules\Xhelp\Response $newResponse */
                    $newResponse = $responseHandler->create();
                    $newResponse->setVar('uid', $xoopsUser->getVar('uid'));
                    $newResponse->setVar('ticketid', $ticket->getVar('id'));
                    $newResponse->setVar('message', Request::getString('response', '', 'POST'));
                    $newResponse->setVar('timeSpent', Request::getInt('timespent', 0, 'POST'));
                    $newResponse->setVar('updateTime', $ticket->getVar('posted'));
                    $newResponse->setVar('userIP', $ticket->getVar('userIP'));
                    if (Request::hasVar('private', 'POST')) {
                        $newResponse->setVar('private', Request::getInt('private', 0, 'POST'));
                    }
                    if ($responseHandler->insert($newResponse)) {
                        $eventService->trigger('new_response', [&$ticket, &$newResponse]);
                        $session->del('xhelp_response');
                    }
                }
            }

            $session->del('xhelp_ticket');
            $session->del('xhelp_validateError');
            $session->del('xhelp_custFields');

            $message = _XHELP_MESSAGE_ADDTICKET;
        } else {
            //$session->set('xhelp_ticket', $ticket);
            $message = _XHELP_MESSAGE_ADDTICKET_ERROR . $ticket->getHtmlErrors();     // Unsuccessfully added new ticket
        }
        $helper->redirect('index.php', 5, $message);
    } else {
        $GLOBALS['xoopsOption']['template_main'] = 'xhelp_addTicket.tpl';             // Always set main template before including the header
        require_once XOOPS_ROOT_PATH . '/header.php';

        /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
        $departmentHandler = $helper->getHandler('Department');    // Department handler
        $criteria          = new \Criteria('', '');
        $criteria->setSort('department');
        $departments = $departmentHandler->getObjects($criteria);
        if (0 == count($departments)) {
            $message = _XHELP_MESSAGE_NO_DEPTS;
            $helper->redirect('index.php', 3, $message);
        }
        $aDept    = [];
        $myGroups = $memberHandler->getGroupsByUser($xoopsUser->getVar('uid'));
        if ($xhelp_isStaff && (0 == $helper->getConfig('xhelp_deptVisibility'))) {     // If staff are not applied
            foreach ($departments as $dept) {
                $deptid  = $dept->getVar('id');
                $aDept[] = [
                    'id'         => $deptid,
                    'department' => $dept->getVar('department'),
                ];
            }
        } else {
            foreach ($departments as $dept) {
                $deptid = $dept->getVar('id');
                foreach ($myGroups as $group) {   // Check for user to be in multiple groups
                    if ($grouppermHandler->checkRight(_XHELP_GROUP_PERM_DEPT, $deptid, $group, $module_id)) {
                        //Assign the first value to $dept_id incase the default department property not set
                        if (null === $dept_id) {
                            $dept_id = $deptid;
                        }
                        $aDept[] = [
                            'id'         => $deptid,
                            'department' => $dept->getVar('department'),
                        ];
                        break;
                    }
                }
            }
        }

        // User Dept visibility check
        if (empty($aDept)) {
            $message = _XHELP_MESSAGE_NO_DEPTS;
            $helper->redirect('index.php', 3, $message);
        }

        $xoopsTpl->assign('xhelp_isUser', true);

        if ($xhelp_isStaff) {
            $checkStaff = $staffHandler->getByUid($xoopsUser->getVar('uid'));
            if (!$hasRights = $checkStaff->checkRoleRights(XHELP_SEC_TICKET_ADD)) {
                $message = _XHELP_MESSAGE_NO_ADD_TICKET;
                $helper->redirect('index.php', 3, $message);
            }
            unset($checkStaff);

            if (false !== ($hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP, $dept_id))) {
                $staff = $membershipHandler->xoopsUsersByDept($dept_id);

                $aOwnership    = [];
                $aOwnership[0] = _XHELP_NO_OWNER;
                foreach ($staff as $stf) {
                    $aOwnership[$stf->getVar('uid')] = $stf->getVar('uname');
                }
                $xoopsTpl->assign('xhelp_aOwnership', $aOwnership);
            } else {
                $xoopsTpl->assign('xhelp_aOwnership', false);
            }
        }

        $has_mimes = false;
        if ($helper->getConfig('xhelp_allowUpload')) {
            // Get available mimetypes for file uploading
            /** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
            $mimetypeHandler = $helper->getHandler('Mimetype');
            $xhelp           = Utility::getModule();
            $mid             = $xhelp->getVar('mid');
            if ($xhelp_isStaff) {
                $criteria = new \Criteria('mime_admin', '1');
            } else {
                $criteria = new \Criteria('mime_user', '1');
            }
            $mimetypes = $mimetypeHandler->getObjects($criteria);
            $mimes     = '';
            foreach ($mimetypes as $mime) {
                if ('' === $mimes) {
                    $mimes = $mime->getVar('mime_ext');
                } else {
                    $mimes .= ', ' . $mime->getVar('mime_ext');
                }
            }
            $xoopsTpl->assign('xhelp_mimetypes', $mimes);
        }

        $xoopsTpl->assign('xhelp_has_logUser', false);
        if ($xhelp_isStaff) {
            $checkStaff = $staffHandler->getByUid($xoopsUser->getVar('uid'));
            if (false !== ($hasRights = $checkStaff->checkRoleRights(XHELP_SEC_TICKET_LOGUSER))) {
                $xoopsTpl->assign('xhelp_has_logUser', true);
            }
            unset($checkStaff);
        }

        // Get current dept's custom fields
        $fields = $ticketFieldDepartmentHandler->fieldsByDepartment($dept_id, true);

        if (!$savedFields = $session->get('xhelp_custFields')) {
            $savedFields = [];
        }

        $aFields = [];
        foreach ($fields as $field) {
            $values = $field->getVar('fieldvalues');
            if (XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
                $values = [1 => _YES, 0 => _NO];
            }

            // Check for values already submitted, and fill those values in
            if (array_key_exists($field->getVar('fieldname'), $savedFields)) {
                $defaultValue = $savedFields[$field->getVar('fieldname')];
            } else {
                $defaultValue = $field->getVar('defaultvalue');
            }

            $aFields[$field->getVar('id')] = [
                'name'         => $field->getVar('name'),
                'desc'         => $field->getVar('description'),
                'fieldname'    => $field->getVar('fieldname'),
                'defaultvalue' => $defaultValue,
                'controltype'  => $field->getVar('controltype'),
                'required'     => $field->getVar('required'),
                'fieldlength'  => $field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50,
                'maxlength'    => $field->getVar('fieldlength'),
                'weight'       => $field->getVar('weight'),
                'fieldvalues'  => $values,
                'validation'   => $field->getVar('validation'),
            ];
        }
        $xoopsTpl->assign('xhelp_custFields', $aFields);
        if (!empty($aFields)) {
            $xoopsTpl->assign('xhelp_hasCustFields', true);
        } else {
            $xoopsTpl->assign('xhelp_hasCustFields', false);
        }

        $javascript = '<script type="text/javascript" src="' . XHELP_BASE_URL . "/include/functions.js\"></script>
<script type=\"text/javascript\" src='" . XHELP_SCRIPT_URL . "/addTicketDeptChange.php?client'></script>
<script type=\"text/javascript\">
<!--
function departments_onchange()
{
    dept = xoopsGetElementById('departments');
    var wl = new Xhelp\WebLib(fieldHandler);
    wl.customFieldsByDept(dept.value);\n";

        if ($xhelp_isStaff) {
            $javascript .= "var w = new Xhelp\WebLib(staffHandler);
        w.staffbydept(dept.value);\n";
        }
        $javascript .= '}

var staffHandler = {
    staffbydept: function(result){';
        if ($xhelp_isStaff) {
            if (Request::hasVar('xhelp_logMode', 'COOKIE') && 2 === $_COOKIE['xhelp_logMode']
                && is_object($staff)
                && $staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP, $dept_id)) {
                $javascript .= "var sel = gE('owner');";
                $javascript .= "xhelpFillStaffSelect(sel, result);\n";
            }
        }
        $javascript .= "}
}

var fieldHandler = {
    customFieldsByDept: function(result){
        var tbl = gE('tblAddTicket');\n";
        if ($xhelp_isStaff && isset($_COOKIE['xhelp_logMode']) && 2 === $_COOKIE['xhelp_logMode']) {
            $javascript .= "var beforeele = gE('privResponse');\n";
        } else {
            $javascript .= "var beforeele = gE('addButtons');\n";
        }
        $javascript .= "tbody = tbl.tBodies[0];\n";
        $javascript .= "xhelpFillCustomFlds(tbody, result, beforeele);
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('departments'), 'change', departments_onchange, true);
}

window.setTimeout('window_onload()', 1500);
//-->
</script>";

        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
        $xoopsTpl->assign('xhelp_includeURL', XHELP_INCLUDE_URL);
        $xoopsTpl->assign('xoops_module_header', $javascript . $xhelp_module_header);
        $xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));
        $xoopsTpl->assign('xhelp_text_lookup', _XHELP_TEXT_LOOKUP);
        $xoopsTpl->assign('xhelp_text_email', _XHELP_TEXT_EMAIL);
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
        $xoopsTpl->assign('xhelp_departments', $aDept);
        $xoopsTpl->assign('xhelp_current_file', basename(__file__));
        $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
        $xoopsTpl->assign('xhelp_priorities_desc', [
            5 => _XHELP_PRIORITY5,
            4 => _XHELP_PRIORITY4,
            3 => _XHELP_PRIORITY3,
            2 => _XHELP_PRIORITY2,
            1 => _XHELP_PRIORITY1,
        ]);
        $xoopsTpl->assign('xhelp_default_priority', XHELP_DEFAULT_PRIORITY);
        $xoopsTpl->assign('xhelp_currentUser', $xoopsUser->getVar('uid'));
        $xoopsTpl->assign('xhelp_numTicketUploads', $helper->getConfig('xhelp_numTicketUploads'));
        //        if (Request::hasVar('logFor', 'POST')) {
        if (Request::hasVar('logFor', 'POST')) {
            $uid      = Request::getInt('logFor', 0, 'POST');
            $username = $xoopsUser::getUnameFromId($uid);
            $xoopsTpl->assign('xhelp_username', $username);
            $xoopsTpl->assign('xhelp_user_id', $uid);
        } else {
            $uid      = $xoopsUser->getVar('uid');
            $username = $xoopsUser->getVar('uname');
            $xoopsTpl->assign('xhelp_username', $username);
            $xoopsTpl->assign('xhelp_user_id', $uid);
        }
        $xoopsTpl->assign('xhelp_isStaff', $xhelp_isStaff);
        if (isset($_COOKIE['xhelp_logMode'])) {
            $xoopsTpl->assign('xhelp_logMode', $_COOKIE['xhelp_logMode']);
        } else {
            $xoopsTpl->assign('xhelp_logMode', 1);
        }

        if ($xhelp_isStaff) {
            if (Request::hasVar('xhelp_logMode', 'COOKIE') && 2 === $_COOKIE['xhelp_logMode']) {
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
            }
            $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);
        }

        $errors    = [];
        $aElements = [];
        if (false !== ($validateErrors = $session->get('xhelp_validateError'))) {
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

        if (false !== ($ticket = $session->get('xhelp_ticket'))) {
            $xoopsTpl->assign('xhelp_ticket_uid', $ticket['uid']);
            $xoopsTpl->assign('xhelp_ticket_username', $xoopsUser::getUnameFromId($ticket['uid']));
            $xoopsTpl->assign('xhelp_ticket_subject', stripslashes($ticket['subject']));
            $xoopsTpl->assign('xhelp_ticket_description', stripslashes($ticket['description']));
            $xoopsTpl->assign('xhelp_ticket_department', $ticket['department']);
            $xoopsTpl->assign('xhelp_ticket_priority', $ticket['priority']);
        } else {
            $xoopsTpl->assign('xhelp_ticket_uid', $uid);
            $xoopsTpl->assign('xhelp_ticket_username', $username);
            $xoopsTpl->assign('xhelp_ticket_subject', null);
            $xoopsTpl->assign('xhelp_ticket_description', null);
            $xoopsTpl->assign('xhelp_ticket_department', $dept_id);
            $xoopsTpl->assign('xhelp_ticket_priority', XHELP_DEFAULT_PRIORITY);
        }

        if (false !== ($response = $session->get('xhelp_response'))) {
            $xoopsTpl->assign('xhelp_response_uid', $response['uid']);
            $xoopsTpl->assign('xhelp_response_message', $response['message']);
            $xoopsTpl->assign('xhelp_response_timespent', $response['timeSpent']);
            $xoopsTpl->assign('xhelp_response_userIP', $response['userIP']);
            $xoopsTpl->assign('xhelp_response_private', $response['private']);
            $xoopsTpl->assign('xhelp_ticket_status', $response['status']);
            $xoopsTpl->assign('xhelp_ticket_ownership', $response['owner']);
        } else {
            $xoopsTpl->assign('xhelp_response_uid', null);
            $xoopsTpl->assign('xhelp_response_message', null);
            $xoopsTpl->assign('xhelp_response_timeSpent', 0);
            $xoopsTpl->assign('xhelp_response_userIP', null);
            $xoopsTpl->assign('xhelp_response_private', null);
            $xoopsTpl->assign('xhelp_ticket_status', 1);
            $xoopsTpl->assign('xhelp_ticket_ownership', 0);
        }

        require_once XOOPS_ROOT_PATH . '/footer.php';                             //Include the page footer
    }
} else {    // If not a user
    /** @var \XoopsConfigHandler $configHandler */
    $configHandler = xoops_getHandler('config');
    //$xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);
    $xoopsConfigUser = [];
    $criteria        = new \CriteriaCompo(new \Criteria('conf_name', 'allow_register'), 'OR');
    $criteria->add(new \Criteria('conf_name', 'activation_type'), 'OR');
    $myConfigs = $configHandler->getConfigs($criteria);

    foreach ($myConfigs as $myConf) {
        $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
    }
    if (0 == $xoopsConfigUser['allow_register']) {    // Use to doublecheck that anonymous users are allowed to register
        $helper->redirect('error.php');
    } else {
        $helper->redirect('anon_addTicket.php');
    }
    exit();
}

/**
 * @param string|array $fields
 * @return bool
 */
function saveTicket($fields = ''): bool
{
    global $session, $xhelp_isStaff;
    $session->set('xhelp_ticket', [
        'uid'         => Request::getInt('user_id', 0, 'POST'),
        'subject'     => Request::getString('subject', '', 'POST'),
        'description' => Request::getText('description', '', 'POST'), //htmlspecialchars($_POST['description'], ENT_QUOTES),
        'department'  => Request::getInt('departments', 0, 'POST'), //$_POST['departments'],
        'priority'    => Request::getInt('priority', 0, 'POST'),
    ]);

    if ($xhelp_isStaff && 2 === $_COOKIE['xhelp_logMode']) {
        $session->set('xhelp_response', [
            'uid'       => Request::getInt('user_id', 0, 'POST'),
            'message'   => Request::getString('response', '', 'POST'),
            'timeSpent' => Request::getInt('timespent', 0, 'POST'),
            'userIP'    => getenv('REMOTE_ADDR'),
            'private'   => Request::hasVar('private', 'POST') ? 1 : 0, //isset($_POST['private']) ? 1 : 0,
            'status'    => Request::getInt('status', 0, 'POST'),
            'owner'     => Request::getInt('owner', 0, 'POST'), //$_POST['owner']
        ]);
    }

    if ('' != $fields) {
        $session->set('xhelp_custFields', $fields);
    }

    return true;
}
