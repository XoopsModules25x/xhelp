<?php
//$Id: anon_addTicket.php,v 1.30 2005/12/01 22:36:21 ackbarr Exp $

require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');

$language = $xoopsConfig['language'];
include_once(XOOPS_ROOT_PATH ."/language/$language/user.php");

$config_handler =& xoops_gethandler('config');
$xoopsConfigUser = array();
$crit = new CriteriaCompo(new Criteria('conf_name', 'allow_register'), 'OR');
$crit->add(new Criteria('conf_name', 'activation_type'), 'OR');
$myConfigs =& $config_handler->getConfigs($crit);

foreach($myConfigs as $myConf){
    $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
}



if($xoopsModuleConfig['xhelp_allowAnonymous'] == 0){
    header("Location: ".XHELP_BASE_URL."/error.php");
}

$hTicket =& xhelpGetHandler('ticket');
$hGroupPerm =& xoops_gethandler('groupperm');
$hMember =& xoops_gethandler('member');
$hFieldDept =& xhelpGetHandler('ticketFieldDepartment');
$module_id = $xoopsModule->getVar('mid');

if ($xoopsConfigUser['allow_register'] == 0) {    // Use to doublecheck that anonymous users are allowed to register
    header("Location: ".XHELP_BASE_URL."/error.php");
    exit();
}

if(!isset($dept_id)){
    $dept_id = xhelpGetMeta("default_department");
}

if(!isset($_POST['addTicket'])){
    $xoopsOption['template_main'] = 'xhelp_anon_addTicket.html';             // Always set main template before including the header
    include(XOOPS_ROOT_PATH . '/header.php');

    $hDepartments  =& xhelpGetHandler('department');    // Department handler
    $crit = new Criteria('','');
    $crit->setSort('department');
    $departments =& $hDepartments->getObjects($crit);
    if(count($departments) == 0){
        $message = _XHELP_MESSAGE_NO_DEPTS;
        redirect_header(XHELP_BASE_URL.'/index.php', 3, $message);
    }

    //XOOPS_GROUP_ANONYMOUS
    foreach($departments as $dept){
        $deptid = $dept->getVar('id');
        if($hGroupPerm->checkRight(_XHELP_GROUP_PERM_DEPT, $deptid, XOOPS_GROUP_ANONYMOUS, $module_id)){
            $aDept[] = array('id'=>$deptid,
                             'department'=>$dept->getVar('department'));
        }
    }
    if($xoopsModuleConfig['xhelp_allowUpload']){
        // Get available mimetypes for file uploading
        $hMime =& xhelpGetHandler('mimetype');
        $crit = new Criteria('mime_user', 1);
        $mimetypes =& $hMime->getObjects($crit);
        $mimes = '';
        foreach($mimetypes as $mime){
            if($mimes == ''){
                $mimes = $mime->getVar('mime_ext');
            } else {
                $mimes .= ", " . $mime->getVar('mime_ext');
            }
        }
        $xoopsTpl->assign('xhelp_mimetypes', $mimes);
    }

    // Get current dept's custom fields
    $fields =& $hFieldDept->fieldsByDepartment($dept_id, true);

    if (!$savedFields =& $_xhelpSession->get('xhelp_custFields')) {
        $savedFields = array();
    }

    $aFields = array();
    foreach($fields as $field){
        $values = $field->getVar('fieldvalues');
        if ($field->getVar('controltype') == XHELP_CONTROL_YESNO) {
            $values = array(1 => _YES, 0 => _NO);
        }

        // Check for values already submitted, and fill those values in
        if(array_key_exists($field->getVar('fieldname'), $savedFields)){
            $defaultValue = $savedFields[$field->getVar('fieldname')];
        } else {
            $defaultValue = $field->getVar('defaultvalue');
        }

        $aFields[$field->getVar('id')] =
        array('name' => $field->getVar('name'),
                  'desc' => $field->getVar('description'),
                  'fieldname' => $field->getVar('fieldname'),
                  'defaultvalue' => $defaultValue,
                  'controltype' => $field->getVar('controltype'),
                  'required' => $field->getVar('required'),
                  'fieldlength' => $field->getVar('fieldlength'),
                  'maxlength' => ($field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50),
                  'weight' => $field->getVar('weight'),
                  'fieldvalues' => $values,
                  'validation' => $field->getVar('validation'));
    }
    $xoopsTpl->assign('xhelp_custFields', $aFields);
    if(!empty($aFields)){
        $xoopsTpl->assign('xhelp_hasCustFields', true);
    } else {
        $xoopsTpl->assign('xhelp_hasCustFields', false);
    }

    $javascript = "<script type=\"text/javascript\" src=\"". XHELP_BASE_URL ."/include/functions.js\"></script>
<script type=\"text/javascript\" src='".XHELP_SCRIPT_URL."/addTicketDeptChange.php?client'></script>
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

    $xoopsTpl->assign('xoops_module_header', $javascript. $xhelp_module_header);
    $xoopsTpl->assign('xhelp_allowUpload', $xoopsModuleConfig['xhelp_allowUpload']);
    $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
    $xoopsTpl->assign('xhelp_departments', $aDept);
    $xoopsTpl->assign('xhelp_current_file', basename(__file__));
    $xoopsTpl->assign('xhelp_priorities', array(5, 4, 3, 2, 1));
    $xoopsTpl->assign('xhelp_priorities_desc', array('5' => _XHELP_PRIORITY5, '4' => _XHELP_PRIORITY4,'3' => _XHELP_PRIORITY3, '2' => _XHELP_PRIORITY2, '1' => _XHELP_PRIORITY1));
    $xoopsTpl->assign('xhelp_default_priority', XHELP_DEFAULT_PRIORITY);
    $xoopsTpl->assign('xhelp_default_dept', xhelpGetMeta("default_department"));
    $xoopsTpl->assign('xhelp_includeURL', XHELP_INCLUDE_URL);
    $xoopsTpl->assign('xhelp_numTicketUploads', $xoopsModuleConfig['xhelp_numTicketUploads']);

    $errors = array();
    $aElements = array();
    if($validateErrors =& $_xhelpSession->get('xhelp_validateError')){
        foreach($validateErrors as $fieldname=>$error){
            if(!empty($error['errors'])){
                $aElements[] = $fieldname;
                foreach($error['errors'] as $err){
                    $errors[$fieldname] = $err;
                }
            }
        }
        $xoopsTpl->assign('xhelp_errors', $errors);
    } else {
        $xoopsTpl->assign('xhelp_errors', null);
    }

    $elements = array('subject', 'description', 'email');
    foreach($elements as $element){         // Foreach element in the predefined list
        $xoopsTpl->assign("xhelp_element_$element", "formButton");
        foreach($aElements as $aElement){   // Foreach that has an error
            if($aElement == $element){      // If the names are equal
                $xoopsTpl->assign("xhelp_element_$element", "validateError");
                break;
            }
        }
    }

    if ($ticket =& $_xhelpSession->get('xhelp_ticket')) {
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

    if($user =& $_xhelpSession->get('xhelp_user')){
        $xoopsTpl->assign('xhelp_uid', $user['uid']);
        $xoopsTpl->assign('xhelp_email', $user['email']);
    } else {
        $xoopsTpl->assign('xhelp_uid', null);
        $xoopsTpl->assign('xhelp_email', null);
    }
    include(XOOPS_ROOT_PATH . '/footer.php');
} else {
    require_once(XHELP_CLASS_PATH.'/validator.php');

    $v = array();
    $v['subject'][] = new ValidateLength($_POST['subject'], 2, 255);
    $v['description'][] = new ValidateLength($_POST['description'], 2);
    $v['email'][] = new ValidateEmail($_POST['email']);

    // Get current dept's custom fields
    $fields =& $hFieldDept->fieldsByDepartment($dept_id, true);
    $aFields = array();

    foreach($fields as $field){
        $values = $field->getVar('fieldvalues');
        if ($field->getVar('controltype') == XHELP_CONTROL_YESNO) {
            $values = array(1 => _YES, 0 => _NO);
        }
        $fieldname = $field->getVar('fieldname');

        if($field->getVar('controltype') != XHELP_CONTROL_FILE) {
            $checkField = $_POST[$fieldname];
        } else {
            $checkField = $_FILES[$fieldname];
        }

        $v[$fieldname][] = new ValidateRegex($checkField, $field->getVar('validation'), $field->getVar('required'));

        $aFields[$field->getVar('id')] =
        array('name' => $field->getVar('name'),
                  'desc' => $field->getVar('description'),
                  'fieldname' => $field->getVar('fieldname'),
                  'defaultvalue' => $field->getVar('defaultvalue'),
                  'controltype' => $field->getVar('controltype'),
                  'required' => $field->getVar('required'),
                  'fieldlength' => $field->getVar('fieldlength'),
                  'maxlength' => ($field->getVar('fieldlength') < 50 ? $field->getVar('fieldlength') : 50),
                  'weight' => $field->getVar('weight'),
                  'fieldvalues' => $values,
                  'validation' => $field->getVar('validation'));
    }

    $_xhelpSession->set('xhelp_ticket',
    array('uid' => 0,
              'subject' => $_POST['subject'],
              'description' => htmlspecialchars($_POST['description'], ENT_QUOTES),
              'department' => $_POST['departments'],
              'priority' => $_POST['priority']));

    $_xhelpSession->set('xhelp_user',
    array('uid' => 0,
              'email' => $_POST['email']));

    if($fields != ""){
        $_xhelpSession->set('xhelp_custFields', $fields);
    }

    // Perform each validation
    $fields = array();
    $errors = array();
    foreach($v as $fieldname=>$validator) {
        if (!xhelpCheckRules($validator, $errors)) {
            //Mark field with error
            $fields[$fieldname]['haserrors'] = true;
            $fields[$fieldname]['errors'] = $errors;
        } else {
            $fields[$fieldname]['haserrors'] = false;
        }
    }

    if(!empty($errors)){
        $_xhelpSession->set('xhelp_validateError', $fields);
        $message = _XHELP_MESSAGE_VALIDATE_ERROR;
        header("Location: ".XHELP_BASE_URL."/anon_addTicket.php");
        exit();
    }

    //Check email address
    $user_added = false;
    if(!$xoopsUser =& xhelpEmailIsXoopsUser($_POST['email'])){      // Email is already used by a member
        switch($xoopsConfigUser['activation_type']){
            case 1:
                $level = 1;
                break;

            case 0:
            case 2:
            default:
                $level = 0;
        }

        if($anon_user =& xhelpXoopsAccountFromEmail($_POST['email'], '', $password, $level)){ // If new user created
            $member_handler =& xoops_gethandler('member');
            $xoopsUser =& $member_handler->loginUserMd5($anon_user->getVar('uname'), $anon_user->getVar('pass'));
            $user_added = true;
        } else {        // User not created
            $message = _XHELP_MESSAGE_NEW_USER_ERR;
            redirect_header(XHELP_BASE_URL.'/user.php', 3, $message);
        }
    }
    $ticket =& $hTicket->create();
    $ticket->setVar('uid', $xoopsUser->getVar('uid'));
    $ticket->setVar('subject', $_POST['subject']);
    $ticket->setVar('description', $_POST['description']);
    $ticket->setVar('department', $_POST['departments']);
    $ticket->setVar('priority', $_POST['priority']);
    $ticket->setVar('status', 1);
    $ticket->setVar('posted', time());
    $ticket->setVar('userIP', getenv("REMOTE_ADDR"));
    $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($xoopsModuleConfig['xhelp_overdueTime'] *60*60));

    $aUploadFiles = array();
    if($xoopsModuleConfig['xhelp_allowUpload']){
        foreach($_FILES as $key=>$aFile){
            $pos = strpos($key, 'userfile');
            if($pos !== false && is_uploaded_file($aFile['tmp_name'])){     // In the userfile array and uploaded file?
                if ($ret = $ticket->checkUpload($key, $allowed_mimetypes, $errors)) {
                    $aUploadFiles[$key] = $aFile;
                } else {
                    $errorstxt = implode('<br />', $errors);
                    $message = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                    redirect_header(XHELP_BASE_URL."/addTicket.php", 5, $message);
                }
            }
        }
    }

    if($hTicket->insert($ticket)){
        $ticket->addSubmitter($xoopsUser->getVar('email'), $xoopsUser->getVar('uid'));
        if(count($aUploadFiles) > 0){   // Has uploaded files?
            foreach($aUploadFiles as $key=>$aFile){
                $file = $ticket->storeUpload($key, null, $allowed_mimetypes);
                $_eventsrv->trigger('new_file', array(&$ticket, &$file));
            }
        }

        // Add custom field values to db
        $hTicketValues = xhelpGetHandler('ticketValues');
        $ticketValues = $hTicketValues->create();

        foreach($aFields as $field){
            $fieldname = $field['fieldname'];
            $fieldtype = $field['controltype'];

            if($fieldtype == XHELP_CONTROL_FILE){               // If custom field was a file upload
                if($xoopsModuleConfig['xhelp_allowUpload']){    // If uploading is allowed
                    if(is_uploaded_file($_FILES[$fieldname]['tmp_name'])){
                        if (!$ret = $ticket->checkUpload($fieldname, $allowed_mimetypes, $errors)) {
                            $errorstxt = implode('<br />', $errors);
                            $message = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                            redirect_header(XHELP_BASE_URL."/addTicket.php", 5, $message);
                        }
                        if($file = $ticket->storeUpload($fieldname, -1, $allowed_mimetypes)){
                            $ticketValues->setVar($fieldname, $file->getVar('id') . "_" . $_FILES[$fieldname]['name']);
                        }
                    }
                }
            } else {
                $fieldvalue = $_POST[$fieldname];
                $ticketValues->setVar($fieldname, $fieldvalue);
            }
        }
        $ticketValues->setVar('ticketid', $ticket->getVar('id'));

        if(!$hTicketValues->insert($ticketValues)){
            $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED;
        }

        $_eventsrv->trigger('new_ticket', array(&$ticket));

        $_xhelpSession->del('xhelp_ticket');
        $_xhelpSession->del('xhelp_ticket');
        $_xhelpSession->del('xhelp_user');
        $_xhelpSession->del('xhelp_validateError');

        $message = _XHELP_MESSAGE_ADDTICKET;
    } else {
        $message = _XHELP_MESSAGE_ADDTICKET_ERROR . $ticket->getHtmlErrors();     // Unsuccessfully added new ticket
    }
    if ($user_added) {
        $_eventsrv->trigger('new_user_by_email', array($password, $xoopsUser));
    }

    redirect_header(XOOPS_URL.'/user.php', 3, $message);
}
?>