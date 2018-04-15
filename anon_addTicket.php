<?php

use Xmf\Request;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';

/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();


$language = $xoopsConfig['language'];
require_once XOOPS_ROOT_PATH . "/language/$language/user.php";

$configHandler   = xoops_getHandler('config');
$xoopsConfigUser = [];
$crit            = new \CriteriaCompo(new \Criteria('conf_name', 'allow_register'), 'OR');
$crit->add(new \Criteria('conf_name', 'activation_type'), 'OR');
$myConfigs = $configHandler->getConfigs($crit);

foreach ($myConfigs as $myConf) {
    $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
}

if (0 == $helper->getConfig('xhelp_allowAnonymous')) {
    header('Location: ' . XHELP_BASE_URL . '/error.php');
}

$hTicket    = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
$hGroupPerm = xoops_getHandler('groupperm');
$hMember    = xoops_getHandler('member');
$hFieldDept = new Xhelp\TicketFieldDepartmentHandler($GLOBALS['xoopsDB']);
$module_id  = $xoopsModule->getVar('mid');

if (0 == $xoopsConfigUser['allow_register']) {    // Use to doublecheck that anonymous users are allowed to register
    header('Location: ' . XHELP_BASE_URL . '/error.php');
    exit();
}

if (!isset($dept_id)) {
    $dept_id = Xhelp\Utility::getMeta('default_department');
}

if (!isset($_POST['addTicket'])) {
    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_anon_addTicket.tpl';             // Always set main template before including the header
    include XOOPS_ROOT_PATH . '/header.php';

    $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);    // Department handler
    $crit         = new \Criteria('', '');
    $crit->setSort('department');
    $departments = $hDepartments->getObjects($crit);
    if (0 == count($departments)) {
        $message = _XHELP_MESSAGE_NO_DEPTS;
        redirect_header(XHELP_BASE_URL . '/index.php', 3, $message);
    }

    //XOOPS_GROUP_ANONYMOUS
    foreach ($departments as $dept) {
        $deptid = $dept->getVar('id');
        if ($hGroupPerm->checkRight(_XHELP_GROUP_PERM_DEPT, $deptid, XOOPS_GROUP_ANONYMOUS, $module_id)) {
            $aDept[] = [
                'id'         => $deptid,
                'department' => $dept->getVar('department')
            ];
        }
    }
    if ($helper->getConfig('xhelp_allowUpload')) {
        // Get available mimetypes for file uploading
        $hMime     = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
        $crit      = new \Criteria('mime_user', 1);
        $mimetypes = $hMime->getObjects($crit);
        $mimes     = '';
        foreach ($mimetypes as $mime) {
            if ('' == $mimes) {
                $mimes = $mime->getVar('mime_ext');
            } else {
                $mimes .= ', ' . $mime->getVar('mime_ext');
            }
        }
        $xoopsTpl->assign('xhelp_mimetypes', $mimes);
    }

    // Get current dept's custom fields
    $fields = $hFieldDept->fieldsByDepartment($dept_id, true);

    if (!$savedFields = $_xhelpSession->get('xhelp_custFields')) {
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
            'validation'   => $field->getVar('validation')
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
    var wl = new xhelpweblib(fieldHandler);
    wl.customfieldsbydept(dept.value);
}

var fieldHandler = {
    customfieldsbydept: function(result){
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
        '5' => _XHELP_PRIORITY5,
        '4' => _XHELP_PRIORITY4,
        '3' => _XHELP_PRIORITY3,
        '2' => _XHELP_PRIORITY2,
        '1' => _XHELP_PRIORITY1
    ]);
    $xoopsTpl->assign('xhelp_default_priority', XHELP_DEFAULT_PRIORITY);
    $xoopsTpl->assign('xhelp_default_dept', Xhelp\Utility::getMeta('default_department'));
    $xoopsTpl->assign('xhelp_includeURL', XHELP_INCLUDE_URL);
    $xoopsTpl->assign('xhelp_numTicketUploads', $helper->getConfig('xhelp_numTicketUploads'));

    $errors    = [];
    $aElements = [];
    if ($validateErrors = $_xhelpSession->get('xhelp_validateError')) {
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

    if ($ticket = $_xhelpSession->get('xhelp_ticket')) {
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

    if ($user = $_xhelpSession->get('xhelp_user')) {
        $xoopsTpl->assign('xhelp_uid', $user['uid']);
        $xoopsTpl->assign('xhelp_email', $user['email']);
    } else {
        $xoopsTpl->assign('xhelp_uid', null);
        $xoopsTpl->assign('xhelp_email', null);
    }
    include XOOPS_ROOT_PATH . '/footer.php';
} else {
    // require_once XHELP_CLASS_PATH . '/validator.php';

    $v                  = [];
    $v['subject'][]     = new validation\ValidateLength(Request::getString('subject', '', 'POST'), 2, 255);
    $v['description'][] = new validation\ValidateLength(Request::getString('description', '', 'POST'), 2);
    $v['email'][]       = new validation\ValidateEmail(Request::getString('email', '', 'POST'));

    // Get current dept's custom fields
    $fields  = $hFieldDept->fieldsByDepartment($dept_id, true);
    $aFields = [];

    foreach ($fields as $field) {
        $values = $field->getVar('fieldvalues');
        if (XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
            $values = [1 => _YES, 0 => _NO];
        }
        $fieldname = $field->getVar('fieldname');

        if (XHELP_CONTROL_FILE != $field->getVar('controltype')) {
            $checkField = $_POST[$fieldname];
        } else {
            $checkField = $_FILES[$fieldname];
        }

        $v[$fieldname][] = new validation\ValidateRegex($checkField, $field->getVar('validation'), $field->getVar('required'));

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
            'validation'   => $field->getVar('validation')
        ];
    }

    $_xhelpSession->set('xhelp_ticket', [
        'uid'         => 0,
        'subject'     => $_POST['subject'],
        'description' => htmlspecialchars($_POST['description'], ENT_QUOTES),
        'department'  => $_POST['departments'],
        'priority'    => $_POST['priority']
    ]);

    $_xhelpSession->set('xhelp_user', [
        'uid'   => 0,
        'email' => $_POST['email']
    ]);

    if ('' != $fields) {
        $_xhelpSession->set('xhelp_custFields', $fields);
    }

    // Perform each validation
    $fields = [];
    $errors = [];
    foreach ($v as $fieldname => $validator) {
        if (!Xhelp\Utility::checkRules($validator, $errors)) {
            //Mark field with error
            $fields[$fieldname]['haserrors'] = true;
            $fields[$fieldname]['errors']    = $errors;
        } else {
            $fields[$fieldname]['haserrors'] = false;
        }
    }

    if (!empty($errors)) {
        $_xhelpSession->set('xhelp_validateError', $fields);
        $message = _XHELP_MESSAGE_VALIDATE_ERROR;
        header('Location: ' . XHELP_BASE_URL . '/anon_addTicket.php');
        exit();
    }

    //Check email address
    $user_added = false;
    if (!$xoopsUser = Xhelp\Utility::emailIsXoopsUser($_POST['email'])) {      // Email is already used by a member
        switch ($xoopsConfigUser['activation_type']) {
            case 1:
                $level = 1;
                break;

            case 0:
            case 2:
            default:
                $level = 0;
        }

        if ($anon_user = Xhelp\Utility::getXoopsAccountFromEmail($_POST['email'], '', $password, $level)) { // If new user created
            $memberHandler = xoops_getHandler('member');
            $xoopsUser     = $memberHandler->loginUserMd5($anon_user->getVar('uname'), $anon_user->getVar('pass'));
            $user_added    = true;
        } else {        // User not created
            $message = _XHELP_MESSAGE_NEW_USER_ERR;
            redirect_header(XHELP_BASE_URL . '/user.php', 3, $message);
        }
    }
    $ticket = $hTicket->create();
    $ticket->setVar('uid', $xoopsUser->getVar('uid'));
    $ticket->setVar('subject', $_POST['subject']);
    $ticket->setVar('description', $_POST['description']);
    $ticket->setVar('department', $_POST['departments']);
    $ticket->setVar('priority', $_POST['priority']);
    $ticket->setVar('status', 1);
    $ticket->setVar('posted', time());
    $ticket->setVar('userIP', getenv('REMOTE_ADDR'));
    $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));

    $aUploadFiles = [];
    if ($helper->getConfig('xhelp_allowUpload')) {
        foreach ($_FILES as $key => $aFile) {
            $pos = strpos($key, 'userfile');
            if (false !== $pos
                && is_uploaded_file($aFile['tmp_name'])) {     // In the userfile array and uploaded file?
                if ($ret = $ticket->checkUpload($key, $allowed_mimetypes, $errors)) {
                    $aUploadFiles[$key] = $aFile;
                } else {
                    $errorstxt = implode('<br>', $errors);
                    $message   = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                    redirect_header(XHELP_BASE_URL . '/addTicket.php', 5, $message);
                }
            }
        }
    }

    if ($hTicket->insert($ticket)) {
        $ticket->addSubmitter($xoopsUser->getVar('email'), $xoopsUser->getVar('uid'));
        if (count($aUploadFiles) > 0) {   // Has uploaded files?
            foreach ($aUploadFiles as $key => $aFile) {
                $file = $ticket->storeUpload($key, null, $allowed_mimetypes);
                $_eventsrv->trigger('new_file', [&$ticket, &$file]);
            }
        }

        // Add custom field values to db
        $hTicketValues = new Xhelp\TicketValuesHandler($GLOBALS['xoopsDB']);
        $ticketValues  = $hTicketValues->create();

        foreach ($aFields as $field) {
            $fieldname = $field['fieldname'];
            $fieldtype = $field['controltype'];

            if (XHELP_CONTROL_FILE == $fieldtype) {               // If custom field was a file upload
                if ($helper->getConfig('xhelp_allowUpload')) {    // If uploading is allowed
                    if (is_uploaded_file($_FILES[$fieldname]['tmp_name'])) {
                        if (!$ret = $ticket->checkUpload($fieldname, $allowed_mimetypes, $errors)) {
                            $errorstxt = implode('<br>', $errors);
                            $message   = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                            redirect_header(XHELP_BASE_URL . '/addTicket.php', 5, $message);
                        }
                        if ($file = $ticket->storeUpload($fieldname, -1, $allowed_mimetypes)) {
                            $ticketValues->setVar($fieldname, $file->getVar('id') . '_' . $_FILES[$fieldname]['name']);
                        }
                    }
                }
            } else {
                $fieldvalue = $_POST[$fieldname];
                $ticketValues->setVar($fieldname, $fieldvalue);
            }
        }
        $ticketValues->setVar('ticketid', $ticket->getVar('id'));

        if (!$hTicketValues->insert($ticketValues)) {
            $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED;
        }

        $_eventsrv->trigger('new_ticket', [&$ticket]);

        $_xhelpSession->del('xhelp_ticket');
        $_xhelpSession->del('xhelp_ticket');
        $_xhelpSession->del('xhelp_user');
        $_xhelpSession->del('xhelp_validateError');

        $message = _XHELP_MESSAGE_ADDTICKET;
    } else {
        $message = _XHELP_MESSAGE_ADDTICKET_ERROR . $ticket->getHtmlErrors();     // Unsuccessfully added new ticket
    }
    if ($user_added) {
        $_eventsrv->trigger('new_user_by_email', [$password, $xoopsUser]);
    }

    redirect_header(XOOPS_URL . '/user.php', 3, $message);
}
