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

require_once __DIR__ . '/header.php';
//require_once XHELP_INCLUDE_PATH . '/events.php';

global $xoopsModule, $xhelp_module_header;

$helper       = Xhelp\Helper::getInstance();
$eventService = Xhelp\EventService::getInstance();
$session      = Xhelp\Session::getInstance();
$aDept        = [];

xoops_loadLanguage('user');

/** @var \XoopsConfigHandler $configHandler */
$configHandler   = xoops_getHandler('config');
$xoopsConfigUser = [];
$criteria        = new \CriteriaCompo(new \Criteria('conf_name', 'allow_register'), 'OR');
$criteria->add(new \Criteria('conf_name', 'activation_type'), 'OR');
$myConfigs = $configHandler->getConfigs($criteria);

foreach ($myConfigs as $myConf) {
    $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
}

if (0 == $helper->getConfig('xhelp_allowAnonymous')) {
    $helper->redirect('error.php');
}

/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
/** @var \XoopsGroupPermHandler $grouppermHandler */
$grouppermHandler = xoops_getHandler('groupperm');
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
/** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
$ticketFieldDepartmentHandler = $helper->getHandler('TicketFieldDepartment');
$module_id                    = $xoopsModule->getVar('mid');

/** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
$mimetypeHandler   = $helper->getHandler('Mimetype');
$allowed_mimetypes = $mimetypeHandler->getArray();

if (0 == $xoopsConfigUser['allow_register']) {    // Use to doublecheck that anonymous users are allowed to register
    $helper->redirect('error.php');
}

if (!isset($dept_id)) {
    $dept_id = Xhelp\Utility::getMeta('default_department');
}

if (isset($_POST['addTicket'])) {
    // require_once XHELP_CLASS_PATH . '/validator.php';

    $v                  = [];
    $v['subject'][]     = new Validation\ValidateLength(Request::getString('subject', '', 'POST'), 2, 255);
    $v['description'][] = new Validation\ValidateLength(Request::getString('description', '', 'POST'), 2);
    $v['email'][]       = new Validation\ValidateEmail(Request::getString('email', '', 'POST'));

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
            $checkField = \Xmf\Request::getString($fieldname, '', 'POST');
        } else {
            $checkField = \Xmf\Request::getString($fieldname, '', 'FILES');
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

    $session->set('xhelp_ticket', [
        'uid'         => 0,
        'subject'     => \Xmf\Request::getString('subject', '', 'POST'),
        'description' => htmlspecialchars(\Xmf\Request::getString('description', '', 'POST'), ENT_QUOTES),
        'department'  => $_POST['departments'],
        'priority'    => $_POST['priority'],
    ]);

    $session->set('xhelp_user', [
        'uid'   => 0,
        'email' => \Xmf\Request::getString('email', '', 'POST'),
    ]);

    if ('' != $fields) {
        $session->set('xhelp_custFields', $fields);
    }

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
        $helper->redirect('anon_addTicket.php');
    }

    //Check email address
    $user_added = false;
    if (!$xoopsUser = Xhelp\Utility::emailIsXoopsUser(\Xmf\Request::getString('email', '', 'POST'))) {      // Email is already used by a member
        switch ($xoopsConfigUser['activation_type']) {
            case 1:
                $level = 1;
                break;
            case 0:
            case 2:
            default:
                $level = 0;
        }

        /** @var \XoopsUser $anon_user */
        $anon_user = Xhelp\Utility::getXoopsAccountFromEmail(\Xmf\Request::getString('email', '', 'POST'), '', $password, $level);
        if ($anon_user) { // If new user created
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = xoops_getHandler('member');
            $xoopsUser     = $memberHandler->loginUser($anon_user->getVar('uname'), $anon_user->getVar('pass'));
            $user_added    = true;
        } else {        // User not created
            $message = _XHELP_MESSAGE_NEW_USER_ERR;
            $helper->redirect('user.php', 3, $message);
        }
    }
    /** @var \XoopsModules\Xhelp\Ticket $ticket */
    $ticket = $ticketHandler->create();
    $ticket->setVar('uid', $xoopsUser->getVar('uid'));
    $ticket->setVar('subject', \Xmf\Request::getString('subject', '', 'POST'));
    $ticket->setVar('description', \Xmf\Request::getString('description', '', 'POST'));
    $ticket->setVar('department', $_POST['departments']);
    $ticket->setVar('priority', $_POST['priority']);
    $ticket->setVar('status', 1);
    $ticket->setVar('posted', time());
    $ticket->setVar('userIP', getenv('REMOTE_ADDR'));
    $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));

    $aUploadFiles = [];
    if ($helper->getConfig('xhelp_allowUpload')) {
        foreach ($_FILES as $key => $aFile) {
            $pos = mb_strpos($key, 'userfile');
            if (false !== $pos
                && is_uploaded_file($aFile['tmp_name'])) {     // In the userfile array and uploaded file?
                $ret = $ticket->checkUpload($key, $allowed_mimetypes, $errors);
                if ($ret) {
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
        $ticket->addSubmitter($xoopsUser->getVar('email'), $xoopsUser->getVar('uid'));
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
                            $message   = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                            $helper->redirect('addTicket.php', 5, $message);
                        }
                        $file = $ticket->storeUpload($fieldname, -1, $allowed_mimetypes);
                        if ($file) {
                            $ticketValues->setVar($fieldname, $file->getVar('id') . '_' . $_FILES[$fieldname]['name']);
                        }
                    }
                }
            } else {
                $fieldvalue = \Xmf\Request::getString($fieldname, '', 'POST');
                $ticketValues->setVar($fieldname, $fieldvalue);
            }
        }
        $ticketValues->setVar('ticketid', $ticket->getVar('id'));

        if (!$ticketValuesHandler->insert($ticketValues)) {
            $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED;
        }

        $eventService->trigger('new_ticket', [&$ticket]);

        $session->del('xhelp_ticket');
        $session->del('xhelp_ticket');
        $session->del('xhelp_user');
        $session->del('xhelp_validateError');

        $message = _XHELP_MESSAGE_ADDTICKET;
    } else {
        $message = _XHELP_MESSAGE_ADDTICKET_ERROR . $ticket->getHtmlErrors();     // Unsuccessfully added new ticket
    }
    if ($user_added) {
        $eventService->trigger('new_user_by_email', [$password, $xoopsUser]);
    }

    redirect_header(XOOPS_URL . '/user.php', 3, $message);
} else {
    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_anon_addTicket.tpl';             // Always set main template before including the header
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

    //XOOPS_GROUP_ANONYMOUS
    foreach ($departments as $dept) {
        $deptid = $dept->getVar('id');
        if ($grouppermHandler->checkRight(_XHELP_GROUP_PERM_DEPT, $deptid, XOOPS_GROUP_ANONYMOUS, $module_id)) {
            $aDept[] = [
                'id'         => $deptid,
                'department' => $dept->getVar('department'),
            ];
        }
    }
    if ($helper->getConfig('xhelp_allowUpload')) {
        // Get available mimetypes for file uploading
        /** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
        $mimetypeHandler = $helper->getHandler('Mimetype');
        $criteria        = new \Criteria('mime_user', '1');
        $mimetypes       = $mimetypeHandler->getObjects($criteria);
        $mimes           = '';
        foreach ($mimetypes as $mime) {
            if ('' === $mimes) {
                $mimes = $mime->getVar('mime_ext');
            } else {
                $mimes .= ', ' . $mime->getVar('mime_ext');
            }
        }
        $xoopsTpl->assign('xhelp_mimetypes', $mimes);
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
            'fieldlength'  => $field->getVar('fieldlength'),
            'maxlength'    => $field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50,
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
    wl.customFieldsByDept(dept.value);
}

var fieldHandler = {
    customFieldsByDept: function(result){
        var tbl = gE('tblAddTicket');
        var beforeele = gE('addButtons');
        tbody = tbl.tBodies[0];
        xhelpFillCustomFlds(tbody, result, beforeele);
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('departments'), 'change', departments_onchange, true);
}

window.setTimeout('window_onload()', 1500);
//-->
</script>";

    $xoopsTpl->assign('xoops_module_header', $javascript . $xhelp_module_header);
    $xoopsTpl->assign('xhelp_allowUpload', $helper->getConfig('xhelp_allowUpload'));
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
    $xoopsTpl->assign('xhelp_default_dept', Xhelp\Utility::getMeta('default_department'));
    $xoopsTpl->assign('xhelp_includeURL', XHELP_INCLUDE_URL);
    $xoopsTpl->assign('xhelp_numTicketUploads', $helper->getConfig('xhelp_numTicketUploads'));

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

    $elements = ['subject', 'description', 'email'];
    foreach ($elements as $element) {         // Foreach element in the predefined list
        $xoopsTpl->assign("xhelp_element_$element", 'formButton');
        foreach ($aElements as $aElement) {   // Foreach that has an error
            if ($aElement == $element) {      // If the names are equal
                $xoopsTpl->assign("xhelp_element_$element", 'validateError');
                break;
            }
        }
    }

    $ticket = $session->get('xhelp_ticket');
    if ($ticket) {
        $xoopsTpl->assign('xhelp_ticket_subject', stripslashes($ticket['subject']));
        $xoopsTpl->assign('xhelp_ticket_description', stripslashes($ticket['description']));
        $xoopsTpl->assign('xhelp_ticket_department', $ticket['department']);
        $xoopsTpl->assign('xhelp_ticket_priority', $ticket['priority']);
    } else {
        $xoopsTpl->assign('xhelp_ticket_uid', null);
        $xoopsTpl->assign('xhelp_ticket_username', null);
        $xoopsTpl->assign('xhelp_ticket_subject', null);
        $xoopsTpl->assign('xhelp_ticket_description', null);
        $xoopsTpl->assign('xhelp_ticket_department', null);
        $xoopsTpl->assign('xhelp_ticket_priority', 4);
    }

    $user = $session->get('xhelp_user');
    if ($user) {
        $xoopsTpl->assign('xhelp_uid', $user['uid']);
        $xoopsTpl->assign('xhelp_email', $user['email']);
    } else {
        $xoopsTpl->assign('xhelp_uid', null);
        $xoopsTpl->assign('xhelp_email', null);
    }
    require_once XOOPS_ROOT_PATH . '/footer.php';
}
