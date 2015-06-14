<?php
//$Id: ticket.php,v 1.153 2006/01/27 15:31:34 eric_juden Exp $
require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');
include_once(XHELP_CLASS_PATH.'/validator.php');

$op = "user";

// Get the id of the ticket
if(isset($_REQUEST['id'])){
    $xhelp_id = intval($_REQUEST['id']);
} else {
    redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_INV_TICKET);
}

if(isset($_GET['op'])){
    $op = $_GET['op'];
}

if(!$xoopsUser){
    redirect_header(XOOPS_URL .'/user.php?xoops_redirect='.htmlspecialchars($xoopsRequestUri), 3);
}

$xoopsVersion = substr(XOOPS_VERSION, 6);
intval($xoopsVersion);

global $ticketInfo;
$hStaff         =& xhelpGetHandler('staff');
$member_handler =& xoops_gethandler('member');
$hTickets       =& xhelpGetHandler('ticket');
if(!$ticketInfo     =& $hTickets->get($xhelp_id)){
    redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_INV_TICKET);
}

$displayName =& $xoopsModuleConfig['xhelp_displayName'];    // Determines if username or real name is displayed

$hDepartments   =& xhelpGetHandler('department');
$departments    =& $hDepartments->getObjects(null, true);
$user           =& $member_handler->getUser($ticketInfo->getVar('uid'));
$hStaffReview   =& xhelpGetHandler('staffReview');
$hResponses     =& xhelpGetHandler('responses');
$hMembership    =& xhelpGetHandler('membership');
$aResponses = array();
$all_users = array();

if (isset($departments[$ticketInfo->getVar('department')])) {
    $department = $departments[$ticketInfo->getVar('department')];
}

//Security Checkpoints to ensure no funny stuff
if (!$xoopsUser) {
    redirect_header(XHELP_BASE_URL."/index.php", 3, _NOPERM);
    exit();
}

$op = ($xhelp_isStaff ? 'staff' : $op);

$has_ticketFiles = false;
$files = $ticketInfo->getFiles();
$aFiles = array();
foreach($files as $file){
    if($file->getVar('responseid') == 0){
        $has_ticketFiles = true;
    }

    $filename_full = $file->getVar('filename');
    if($file->getVar('responseid') != 0){
        $removeText = $file->getVar('ticketid')."_".$file->getVar('responseid')."_";
    } else {
        $removeText = $file->getVar('ticketid')."_";
    }
    $filename = str_replace($removeText, '', $filename_full);
    $filesize = round(filesize(XHELP_UPLOAD_PATH."/".$filename_full)/1024, 2);

    $aFiles[] = array('id'=>$file->getVar('id'),
                      'filename'=>$filename,
                      'filename_full'=>$filename_full,
                      'ticketid'=>$file->getVar('ticketid'),
                      'responseid'=>$file->getVar('responseid'),
                      'path'=>'viewFile.php?id='. $file->getVar('id'),
                      'size'=>$filesize." "._XHELP_SIZE_KB);
}
$has_files = count($files) > 0;
unset($files);
$message = '';

if($xhelp_isStaff) {
    //** BTW - What does $giveOwnership do here?
    $giveOwnership = false;
    if(isset($_GET['op'])){
        $op = $_GET['op'];
    } else {
        $op = "staff";
    }

    //Retrieve all responses to current ticket
    $responses = $ticketInfo->getResponses();
    foreach($responses as $response){
        if($has_files){
            $hasFiles = false;
            foreach($aFiles as $file){
                if($file['responseid'] == $response->getVar('id')){
                    $hasFiles = true;
                    break;
                }
            }
        } else {
            $hasFiles = false;
        }

        $aResponses[] = array('id'=>$response->getVar('id'),
                          'uid'=>$response->getVar('uid'),
                          'uname'=>'',
                          'ticketid'=>$response->getVar('ticketid'),
                          'message'=>$response->getVar('message'),
                          'timeSpent'=>$response->getVar('timeSpent'),
                          'updateTime'=>$response->posted('m'),
                          'userIP'=>$response->getVar('userIP'),
                          'user_sig'=>'',
                          'user_avatar' => '',
                          'attachSig'=>'',
                          'staffRating'=>'',
                          'private'=>$response->getVar('private'),
                          'hasFiles' => $hasFiles);
        $all_users[$response->getVar('uid')] = '';
    }

    $all_users[$ticketInfo->getVar('uid')] = '';
    $all_users[$ticketInfo->getVar('ownership')] = '';
    $all_users[$ticketInfo->getVar('closedBy')] = '';

    $has_responses = count($responses) > 0;
    unset($responses);

    if($owner =& $member_handler->getUser($ticketInfo->getVar('ownership'))){
        $giveOwnership = true;
    }

    //Retrieve all log messages from the database
    $logMessage =& $ticketInfo->getLogs();

    $patterns = array();
    $patterns[] = '/pri:([1-5])/';
    $replacements = array();
    $replacements = '<img src="images/priority$1.png" alt="Priority: $1" />';

    foreach($logMessage as $msg){
        $aMessages[] = array('id'=>$msg->getVar('id'),
                             'uid'=>$msg->getVar('uid'),
                             'uname'=>'',
        //'uname'=>(($msgLoggedBy)? $msgLoggedBy->getVar('uname'):$xoopsConfig['anonymous']),
                             'ticketid'=>$msg->getVar('ticketid'),
                             'lastUpdated'=>$msg->lastUpdated('m'),
                             'action'=>preg_replace($patterns, $replacements, $msg->getVar('action')));
        $all_users[$msg->getVar('uid')] = '';
    }
    unset($logMessage);

    //For assign to ownership box
    $hMembership =& xhelpGetHandler('membership');

    global $staff;
    $staff =& $hStaff->getStaffByTask(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'));

    $aOwnership = array();
    // Only run if actions are set to inline style

    if($xoopsModuleConfig['xhelp_staffTicketActions'] == 1){
        $aOwnership[] = array('uid' => 0,
                              'uname' => _XHELP_NO_OWNER);
        foreach($staff as $stf){
            $aOwnership[] = array('uid'=>$stf->getVar('uid'),
                                  'uname'=>'');
            $all_users[$stf->getVar('uid')] = '';
        }
    }

    // Get list of user's last submitted tickets
    $crit = new CriteriaCompo(new Criteria('uid', $ticketInfo->getVar('uid')));
    $crit->setSort('posted');
    $crit->setOrder('DESC');
    $crit->setLimit(10);
    $lastTickets =& $hTickets->getObjects($crit);
    foreach($lastTickets as $ticket){

        $dept = $ticket->getVar('department');
        if (isset($departments[$dept])) {
            $dept = $departments[$dept]->getVar('department');
            $hasUrl = true;
        } else {
            $dept = _XHELP_TEXT_NO_DEPT;
            $hasUrl = false;
        }
        $aLastTickets[] = array('id'=>$ticket->getVar('id'),
                                'subject'=>$ticket->getVar('subject'),
                                'status'=>xhelpGetStatus($ticket->getVar('status')),
                                'department'=>$dept,
                                'dept_url'=>($hasUrl ? XOOPS_URL . '/modules/xhelp/index.php?op=staffViewAll&amp;dept=' . $ticket->getVar('department') : ''),
                                'url'=>XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'));
    }
    $has_lastTickets = count($lastTickets);
    unset($lastTickets);
}

switch($op)
{
    case "addEmail":

        if($_POST['newEmail'] == ''){
            $message = _XHELP_MESSAGE_NO_EMAIL;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        //Check if email is valid
        $validator = new ValidateEmail($_POST['newEmail']);
        if (!$validator->isValid()) {
            redirect_header(xhelpMakeURI('ticket.php', array('id'=>$xhelp_id), false), 3, _XHELP_MESSAGE_NO_EMAIL);
        }

        if(!$newUser = xhelpEmailIsXoopsUser($_POST['newEmail'])){      // If a user doesn't exist with this email
            $user_id = 0;
        } else {
            $user_id = $newUser->getVar('uid');
        }

        // Check that the email doesn't already exist for this ticket
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $xhelp_id));
        $crit->add(new Criteria('email', $_POST['newEmail']));
        $existingUsers =& $hTicketEmails->getObjects($crit);
        if(count($existingUsers) > 0){
            $message = _XHELP_MESSAGE_EMAIL_USED;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        // Create new ticket email object
        $newSubmitter =& $hTicketEmails->create();
        $newSubmitter->setVar('email', $_POST['newEmail']);
        $newSubmitter->setVar('uid', $user_id);
        $newSubmitter->setVar('ticketid', $xhelp_id);
        $newSubmitter->setVar('suppress', 0);
        if($hTicketEmails->insert($newSubmitter)){
            $message = _XHELP_MESSAGE_ADDED_EMAIL;
            header("Location: ".XHELP_BASE_URL."/ticket.php?id=$xhelp_id#emailNotification");
        } else {
            $message = _XHELP_MESSAGE_ADDED_EMAIL_ERROR;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id#emailNotification", 3, $message);
        }
        break;

    case "changeSuppress":
        if(!$xhelp_isStaff){
            $message = _XHELP_MESSAGE_NO_MERGE_TICKET;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $_GET['id']));
        $crit->add(new Criteria('email', $_GET['email']));
        $suppressUser =& $hTicketEmails->getObjects($crit);

        foreach($suppressUser as $sUser){
            if($sUser->getVar('suppress') == 0){
                $sUser->setVar('suppress', 1);
            } else {
                $sUser->setVar('suppress', 0);
            }
            if(!$hTicketEmails->insert($sUser, true)){
                $message = _XHELP_MESSAGE_ADD_EMAIL_ERROR;
                redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id#emailNotification", 3, $message);
            }
        }
        header("Location: ".XHELP_BASE_URL."/ticket.php?id=$xhelp_id#emailNotification");
        break;

    case "delete":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_DELETE, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_DELETE_TICKET;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        if(isset($_POST['delete_ticket'])){
            if($hTickets->delete($ticketInfo)){
                $message = _XHELP_MESSAGE_DELETE_TICKET;
                $_eventsrv->trigger('delete_ticket', array(&$ticketInfo));
            } else {
                $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
            }
        } else {
            $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
        }
        redirect_header(XHELP_BASE_URL."/index.php", 3, $message);
        break;

    case "edit":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_EDIT, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_EDIT_TICKET;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        $hDepartments  =& xhelpGetHandler('department');    // Department handler

        if(!isset($_POST['editTicket'])){
            $xoopsOption['template_main'] = 'xhelp_editTicket.html';             // Always set main template before including the header
            require(XOOPS_ROOT_PATH . '/header.php');

            $crit = new Criteria('','');
            $crit->setSort('department');
            $departments =& $hDepartments->getObjects($crit);
            $hStaff =& xhelpGetHandler('staff');

            foreach($departments as $dept){
                $aDept[] = array('id'=>$dept->getVar('id'),
                                 'department'=>$dept->getVar('department'));
            }

            // Form validation stuff
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

            $elements = array('subject', 'description');
            foreach($elements as $element){         // Foreach element in the predefined list
                $xoopsTpl->assign("xhelp_element_$element", "formButton");
                foreach($aElements as $aElement){   // Foreach that has an error
                    if($aElement == $element){      // If the names are equal
                        $xoopsTpl->assign("xhelp_element_$element", "validateError");
                        break;
                    }
                }
            }
            // end form validation stuff

            $javascript = "<script type=\"text/javascript\" src=\"". XHELP_BASE_URL ."/include/functions.js\"></script>
<script type=\"text/javascript\" src='".XHELP_SCRIPT_URL."/addTicketDeptChange.php?client'></script>
<script type=\"text/javascript\">
<!--
function departments_onchange()
{
    dept = xoopsGetElementById('departments');
    var wl = new xhelpweblib(fieldHandler);
    wl.editticketcustfields(dept.value, $xhelp_id);
}

var fieldHandler = {
    editticketcustfields: function(result){

        var tbl = gE('tblEditTicket');
        var staffCol = gE('staff');";
            $javascript.="var beforeele = gE('editButtons');\n";
            $javascript.="tbody = tbl.tBodies[0];\n";
            $javascript .="xhelpFillCustomFlds(tbody, result, beforeele);\n
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('departments'), 'change', departments_onchange, true);
}

xhelpDOMAddEvent(window, 'load', window_onload, true);
//-->
</script>";
            if ($ticket =& $_xhelpSession->get('xhelp_ticket')) {
                $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
                $xoopsTpl->assign('xhelp_ticket_subject', $ticket['subject']);
                $xoopsTpl->assign('xhelp_ticket_description', $ticket['description']);
                $xoopsTpl->assign('xhelp_ticket_department', $ticket['department']);
                $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept='. $ticket['department']);
                $xoopsTpl->assign('xhelp_ticket_priority', $ticket['priority']);
            } else {
                $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
                $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
                $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description', 'e'));
                $xoopsTpl->assign('xhelp_ticket_department', $ticketInfo->getVar('department'));
                $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept='. $ticketInfo->getVar('department'));
                $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
            }

            //** BTW - why do we need xhelp_allowUpload in the template if it will be always set to 0?
            //$xoopsTpl->assign('xhelp_allowUpload', $xoopsModuleConfig['xhelp_allowUpload']);
            $xoopsTpl->assign('xhelp_allowUpload', 0);
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
            $xoopsTpl->assign('xhelp_departments', $aDept);
            $xoopsTpl->assign('xhelp_priorities', array(5,4,3,2,1));
            $xoopsTpl->assign('xhelp_priorities_desc', array('5' => _XHELP_PRIORITY5, '4' => _XHELP_PRIORITY4,'3' => _XHELP_PRIORITY3, '2' => _XHELP_PRIORITY2, '1' => _XHELP_PRIORITY1));

            if(isset($_POST['logFor'])){
                $uid = $_POST['logFor'];
                $username = xhelpGetUsername($uid, $displayName);
                $xoopsTpl->assign('xhelp_username', $username);
                $xoopsTpl->assign('xhelp_user_id', $uid);
            } else {
                $xoopsTpl->assign('xhelp_username', xhelpGetUsername($xoopsUser->getVar('uid'), $displayName));
                $xoopsTpl->assign('xhelp_user_id', $xoopsUser->getVar('uid'));
            }
            // Used for displaying transparent-background images in IE
            $xoopsTpl->assign('xoops_module_header',$javascript . $xhelp_module_header);
            $xoopsTpl->assign('xhelp_isStaff', $xhelp_isStaff);

            if ($savedFields =& $_xhelpSession->get('xhelp_custFields')) {
                $custFields = $savedFields;
            } else {
                $custFields =& _getTicketFields($ticketInfo);
            }
            $xoopsTpl->assign('xhelp_hasCustFields', (!empty($custFields)) ? true : false);
            $xoopsTpl->assign('xhelp_custFields', $custFields);
            $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

            require(XOOPS_ROOT_PATH.'/footer.php');
        } else {
            require_once(XHELP_CLASS_PATH.'/validator.php');

            $v = array();
            $v['subject'][] = new ValidateLength($_POST['subject'], 2, 100);
            $v['description'][] = new ValidateLength($_POST['description'], 2, 50000);

            $aFields = array();

            //Temp Ticket object for _getTicketFields
            $_ticket = $ticketInfo;
            $_ticket->setVar('department', $_POST['departments']);
            $custFields =& _getTicketFields($_ticket);
            unset ($_ticket);
            foreach($custFields as $field){
                $fieldname = $field['fieldname'];
                $value = $_POST[$fieldname];

                $fileid = '';
                $filename = '';
                $file = '';
                if($field['controltype'] == XHELP_CONTROL_FILE){
                    $file = split("_", $value);
                    $fileid = ((isset($file[0]) && $file[0] != "") ? $file[0] : "");
                    $filename = ((isset($file[1]) && $file[1] != "") ? $file[1] : "");
                }

                if($field['validation'] != ""){
                    $v[$fieldname][] = new ValidateRegex($_POST[$fieldname], $field['validation'], $field['required']);
                }

                $aFields[$field['fieldname']] =
                array('id' => $field['id'],
                          'name' => $field['name'],
                          'description' => $field['desc'],
                          'fieldname' => $field['fieldname'],
                          'controltype' => $field['controltype'],
                          'datatype' => $field['datatype'],
                          'required' => $field['required'],
                          'fieldlength' => $field['fieldlength'],
                          'weight' => $field['weight'],
                          'fieldvalues' => $field['fieldvalues'],
                          'defaultvalue' => $field['defaultvalue'],
                          'validation' => $field['validation'],
                          'value' => $value,
                          'fileid' => $fileid,
                          'filename' => $filename
                );
            }
            unset($custFields);

            $_xhelpSession->set('xhelp_custFields', $aFields);
            $_xhelpSession->set('xhelp_ticket',
            array('subject' => $_POST['subject'],
                    'description' => htmlspecialchars($_POST['description'], ENT_QUOTES),
                    'department' => $_POST['departments'],
                    'priority' => $_POST['priority']));

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
                header("Location: ".XHELP_BASE_URL."/ticket.php?id=$xhelp_id&op=edit");
                exit();
            }

            $oldTicket = array('id'=>$ticketInfo->getVar('id'),
                               'subject'=>$ticketInfo->getVar('subject', 'n'),
                               'description'=>$ticketInfo->getVar('description', 'n'),
                               'priority'=>$ticketInfo->getVar('priority'),
                               'status'=>xhelpGetStatus($ticketInfo->getVar('status')),
                               'department'=>$department->getVar('department'),
                               'department_id'=>$department->getVar('id'));

            // Change ticket info to new info
            $ticketInfo->setVar('subject', $_POST['subject']);
            $ticketInfo->setVar('description', $_POST['description']);
            $ticketInfo->setVar('department', $_POST['departments']);
            $ticketInfo->setVar('priority', $_POST['priority']);
            $ticketInfo->setVar('posted', time());

            if($hTickets->insert($ticketInfo)){
                $message = _XHELP_MESSAGE_EDITTICKET;     // Successfully updated ticket

                // Update custom fields
                $hTicketValues = xhelpGetHandler('ticketValues');
                $ticketValues = $hTicketValues->get($xhelp_id);

                if(is_object($ticketValues)){
                    foreach($aFields as $field){
                        $ticketValues->setVar($field['fieldname'], $_POST[$field['fieldname']]);
                    }
                    if(!$hTicketValues->insert($ticketValues)){
                        $message = _XHELP_MESSAGE_NO_CUSTFLD_ADDED. $ticketValues->getHTMLErrors();
                    }
                }

                $_eventsrv->trigger('edit_ticket', array(&$oldTicket, &$ticketInfo));

                $_xhelpSession->del('xhelp_ticket');
                $_xhelpSession->del('xhelp_validateError');
                $_xhelpSession->del('xhelp_custFields');
            } else {
                $message = _XHELP_MESSAGE_EDITTICKET_ERROR . $ticketInfo->getHtmlErrors();     // Unsuccessfully updated ticket
            }
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        break;

    case "merge":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_MERGE, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_MERGE;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        if($_POST['ticket2'] == ''){
            $message = _XHELP_MESSAGE_NO_TICKET2;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        $ticket2_id = intval($_POST['ticket2']);
        if($newTicket = $ticketInfo->merge($ticket2_id)){
            $returnTicket = $newTicket;
            $message = _XHELP_MESSAGE_MERGE;
            $_eventsrv->trigger('merge_tickets', array($xhelp_id, $ticket2_id, $returnTicket));
        } else {
            $returnTicket = $xhelp_id;
            $message = _XHELP_MESSAGE_MERGE_ERROR;
        }
        redirect_header(XHELP_BASE_URL."/ticket.php?id=$returnTicket", 3, $message);

        break;

    case "ownership":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_CHANGE_OWNER;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        if(isset($_POST['uid'])){
            $uid = intval($_POST['uid']);
        } else {
            $message = _XHELP_MESSAGE_NO_UID;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        if($ticketInfo->getVar('ownership') <> 0){
            $oldOwner = $ticketInfo->getVar('ownership');
        } else {
            $oldOwner = _XHELP_NO_OWNER;
        }

        $ticketInfo->setVar('ownership', $uid);
        $ticketInfo->setVar('lastUpdated', time());
        if($hTickets->insert($ticketInfo)){
            $_eventsrv->trigger('update_owner', array(&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')));
            $message = _XHELP_MESSAGE_UPDATE_OWNER;
        }
        redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);

        break;

    case "print":
        $config_handler =& xoops_gethandler('config');
        $xoopsConfigMetaFooter =& $config_handler->getConfigsByCat(XOOPS_CONF_METAFOOTER);

        $patterns = array();
        $patterns[] = '/pri:([1-5])/';
        $replacements = array();
        $replacements = '<img src="images/priority$1print.png" />';

        foreach($logMessage as $msg){
            $msgLoggedBy =& $member_handler->getUser($msg->getVar('uid'));
            $aPrintMessages[] = array('id'=>$msg->getVar('id'),
                                 'uid'=>$msg->getVar('uid'),
                                 'uname'=>xhelpGetUsername($msgLoggedBy->getVar('uid'), $displayName),
                                 'ticketid'=>$msg->getVar('ticketid'),
                                 'lastUpdated'=>$msg->lastUpdated('m'),
                                 'action'=>preg_replace($patterns, $replacements, $msg->getVar('action')));
            $all_users[$msg->getVar('uid')] = '';
        }
        unset($logMessage);

        require_once XOOPS_ROOT_PATH.'/class/template.php';
        $xoopsTpl = new XoopsTpl();
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL .'/modules/xhelp/images/');
        $xoopsTpl->assign('xhelp_lang_userlookup', 'User Lookup');
        $xoopsTpl->assign('sitename', $xoopsConfig['sitename']);
        $xoopsTpl->assign('xoops_themecss', xoops_getcss());
        $xoopsTpl->assign('xoops_url', XOOPS_URL);
        $xoopsTpl->assign('xhelp_print_logMessages', $aPrintMessages);
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', $department->getVar('department'));
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', xhelpGetStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->lastUpdated('m'));
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        if($giveOwnership){
            $xoopsTpl->assign('xhelp_ticket_ownerUid', $owner->getVar('uid'));
            $xoopsTpl->assign('xhelp_ticket_ownership', xhelpGetUsername($owner, $displayName));
            $xoopsTpl->assign('xhelp_ownerinfo', XOOPS_URL . '/userinfo.php?uid=' . $owner->getVar('uid'));
        }
        $xoopsTpl->assign('xhelp_ticket_closedBy', $ticketInfo->getVar('closedBy'));
        $xoopsTpl->assign('xhelp_ticket_totalTimeSpent', $ticketInfo->getVar('totalTimeSpent'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', xhelpGetUsername($user, $displayName));
        $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $xhelp_id));

        $custFields =& $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', (!empty($custFields)) ? true : false);
        $xoopsTpl->assign('xhelp_custFields', $custFields);

        if(isset($aMessages)){
            $xoopsTpl->assign('xhelp_logMessages', $aMessages);
        } else {
            $xoopsTpl->assign('xhelp_logMessages', 0);
        }
        $xoopsTpl->assign('xhelp_text_claimOwner', _XHELP_TEXT_CLAIM_OWNER);
        $xoopsTpl->assign('xhelp_aOwnership', $aOwnership);

        if($has_responses){
            $users = array();
            $_users = $member_handler->getUsers(new Criteria('uid', '('. implode(array_keys($all_users), ',') . ')', 'IN'), true);
            foreach ($_users as $key=>$_user) {
                if (($displayName == 2) && ($_user->getVar('name') <> '')) {
                    $users[$_user->getVar('uid')] = array('uname' => $_user->getVar('name'));
                } else {
                    $users[$_user->getVar('uid')] = array('uname' => $_user->getVar('uname'));
                }
            }
            unset($_users);

            $myTs =& MyTextSanitizer::getInstance();
            //Update arrays with user information
            if(count($aResponses) > 0){
                for($i=0;$i<count($aResponses);$i++) {
                    if(isset($users[$aResponses[$i]['uid']])){      // Add uname to array
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
        $xoopsTpl->display('db:xhelp_print.html');
        exit();
        break;

    case "updatePriority":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_ADD)){
            $message = _XHELP_MESSAGE_NO_ADD_TICKET;
            redirect_header(XHELP_BASE_URL."/index.php", 3, $message);
        }

        if(isset($_POST['priority'])){
            $priority = $_POST['priority'];
        } else {
            $message = _XHELP_MESSAGE_NO_PRIORITY;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }
        $oldPriority = $ticketInfo->getVar('priority');
        $ticketInfo->setVar('priority', $priority);
        $ticketInfo->setVar('lastUpdated', time());
        if($hTickets->insert($ticketInfo)){
            $_eventsrv->trigger('update_priority', array(&$ticketInfo, $oldPriority));
            $message = _XHELP_MESSAGE_UPDATE_PRIORITY;
        } else {
            $message = _XHELP_MESSAGE_UPDATE_PRIORITY_ERROR .". ";
        }
        redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        break;

    case "updateStatus":
        $addResponse = $changeStatus = false;
        $statusClosed = $statusReopened = false;
        $responseError = $ticketError = false;

        //1. Check if either a response was added or status was changed
        $addResponse = ($_POST['response'] <> '');
        $changeStatus = ($_POST['status'] != $ticketInfo->getVar('status'));

        if ($addResponse || $changeStatus) {

            //2. Update Ticket LastUpdated time
            $ticketInfo->setVar('lastUpdated', time());

            //3. Add Response (if necessary)
            if ($addResponse == true) {
                if ($ticketInfo->canAddResponse($xoopsUser)) {
                    $userIP = xoops_getenv('REMOTE_ADDR');
                    $newResponse =& $ticketInfo->addResponse($xoopsUser->getVar('uid'), $xhelp_id, $_POST['response'],
                    $ticketInfo->getVar('lastUpdated'), $userIP, 0, 0, true);
                    $responseError = !(is_object($newResponse));
                }
            }

            //4. Update Status (if necessary)
            if ($changeStatus == true) {
                //Check if the current staff member can change status
                if ($xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_STATUS, $ticketInfo->getVar('department'))) {
                    $hStatus =& xhelpGetHandler('status');
                    $hStaff =& xhelpGetHandler('staff');

                    $oldStatus =& $hStatus->get($ticketInfo->getVar('status'));
                    $newStatus =& $hStatus->get(intval($_POST['status']));
                    $ticketInfo->setVar('status', $_POST['status']);

                    if($newStatus->getVar('state') == XHELP_STATE_RESOLVED && $oldStatus->getVar('state') == XHELP_STATE_UNRESOLVED){
                        //Closing the ticket
                        $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                        $statusClosed = true;
                    } elseif($oldStatus->getVar('state') == XHELP_STATE_RESOLVED && $newStatus->getVar('state') == XHELP_STATE_UNRESOLVED){
                        //Re-opening the ticket
                        $ticketInfo->setVar('overdueTime', $ticketInfo->getVar('posted') + ($xoopsModuleConfig['xhelp_overdueTime'] *60*60));
                        $statusReopened = true;
                    }
                }
            }

            //5. Save Ticket
            $ticketError = !$hTickets->insert($ticketInfo);

            //6. Fire Necessary Events, set response messages
            if ($addResponse == true && $responseError == false) {
                $_eventsrv->trigger('new_response', array(&$ticketInfo, &$newResponse));
                $message .= _XHELP_MESSAGE_ADDRESPONSE;
            } elseif ($addResponse == true && $responseError == true) {
                $message .= _XHELP_MESSAGE_ADDRESPONSE_ERROR;
            }

            if ($changeStatus == true && $ticketError == false) {
                if ($statusClosed) {
                    $_eventsrv->trigger('close_ticket', array(&$ticketInfo));
                } elseif ($statusReopened) {
                    $_eventsrv->trigger('reopen_ticket', array(&$ticketInfo));
                } else {
                    $_eventsrv->trigger('update_status', array(&$ticketInfo, &$oldStatus, &$newStatus));
                }

                $message .= _XHELP_MESSAGE_UPDATE_STATUS;
            } elseif ($changeStatus == true && $ticketError == true) {
                $message .= _XHELP_MESSAGE_UPDATE_STATUS_ERROR .". ";
            }

        } else {
            //No Changes Made
            //todo: Add new language constant for this
            $message = _XHELP_MESSAGE_NO_CHANGE_STATUS;
        }

        //Notify user of changes
        redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);

        break;

    case "staff":
        $hStatus =& xhelpGetHandler('status');
        $_eventsrv->trigger('view_ticket', array(&$ticketInfo));
        $xoopsOption['template_main'] = 'xhelp_staff_ticketDetails.html';   // Set template
        require(XOOPS_ROOT_PATH.'/header.php');                     // Include

        $users = array();
        $_users = $member_handler->getUsers(new Criteria('uid', "(". implode(array_keys($all_users), ',') . ")", 'IN'), true);
        foreach ($_users as $key=>$_user) {
            if (($displayName == 2) && ($_user->getVar('name') <> '')) {
                $users[$key] = array('uname' => $_user->getVar('name'),
                                'user_sig' => $_user->getVar('user_sig'),
                                'user_avatar' => $_user->getVar('user_avatar'));
            } else {
                $users[$key] = array('uname' => $_user->getVar('uname'),
                                'user_sig' => $_user->getVar('user_sig'),
                                'user_avatar' => $_user->getVar('user_avatar'));
            }
        }

        $crit = new Criteria('','');
        $crit->setSort('department');
        $alldepts = $hDepartments->getObjects($crit);
        foreach($alldepts as $dept){
            $aDept[$dept->getVar('id')] = $dept->getVar('department');
        }
        unset($_users);
        $staff = array();
        $_staff = $hStaff->getObjects(new Criteria('uid', "(". implode(array_keys($all_users), ',') . ")", 'IN'), true);
        foreach($_staff as $key=>$_user) {
            $staff[$key] = $_user->getVar('attachSig');
        }
        unset($_staff);
        $staffReviews =& $ticketInfo->getReviews();

        $myTs =& MyTextSanitizer::getInstance();
        //Update arrays with user information
        if(count($aResponses) > 0){
            for($i=0;$i<count($aResponses);$i++) {
                if(isset($users[$aResponses[$i]['uid']])){      // Add uname to array
                    $aResponses[$i]['uname'] = $users[$aResponses[$i]['uid']]['uname'];
                    $aResponses[$i]['user_sig'] = $myTs->displayTarea($users[$aResponses[$i]['uid']]['user_sig'], true);
                    $aResponses[$i]['user_avatar'] = XOOPS_URL .'/uploads/' . ($users[$aResponses[$i]['uid']]['user_avatar'] ? $users[$aResponses[$i]['uid']]['user_avatar'] : 'blank.gif');
                } else {
                    $aResponses[$i]['uname'] = $xoopsConfig['anonymous'];
                }
                $aResponses[$i]['staffRating'] = _XHELP_RATING0;

                if(isset($staff[$aResponses[$i]['uid']])){       // Add attachSig to array
                    $aResponses[$i]['attachSig'] = $staff[$aResponses[$i]['uid']];
                }

                if(count($staffReviews) > 0){                   // Add staffRating to array
                    foreach($staffReviews as $review){
                        if($aResponses[$i]['id'] == $review->getVar('responseid')){
                            $aResponses[$i]['staffRating'] = xhelpGetRating($review->getVar('rating'));
                        }
                    }
                }
            }
        }

        for($i=0;$i<count($aMessages);$i++){        // Fill other values for log messages
            if(isset($users[$aMessages[$i]['uid']])){
                $aMessages[$i]['uname'] = $users[$aMessages[$i]['uid']]['uname'];
            } else {
                $aMessages[$i]['uname'] = $xoopsConfig['anonymous'];
            }
        }

        if($xoopsModuleConfig['xhelp_staffTicketActions'] == 1){
            for($i=0;$i<count($aOwnership);$i++){
                if(isset($users[$aOwnership[$i]['uid']])){
                    $aOwnership[$i]['uname'] = $users[$aOwnership[$i]['uid']]['uname'];
                }
            }
        }
        unset($users);

        // Get list of users notified of changes to ticket
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new Criteria('ticketid', $xhelp_id);
        $crit->setOrder('ASC');
        $crit->setSort('email');
        $notifiedUsers =& $hTicketEmails->getObjects($crit);
        $aNotified = array();
        foreach($notifiedUsers as $nUser){
            $aNotified[] = array('email' => $nUser->getVar('email'),
                                 'suppress' => $nUser->getVar('suppress'),
                                 'suppressUrl' => XOOPS_URL."/modules/xhelp/ticket.php?id=$xhelp_id&amp;op=changeSuppress&amp;email=".$nUser->getVar('email'));
        }
        unset($notifiedUsers);

        $uid = $xoopsUser->getVar('uid');
        $xoopsTpl->assign('xhelp_uid', $uid);

        // Smarty variables
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
        $xoopsTpl->assign('xhelp_allowUpload', $xoopsModuleConfig['xhelp_allowUpload']);
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL .'/modules/xhelp/images/');
        $xoopsTpl->assign('xoops_module_header',$xhelp_module_header);
        $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
        $xoopsTpl->assign('xhelp_ticket_uid', $ticketInfo->getVar('uid'));
        $submitUser =& $member_handler->getUser($ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_user_avatar', XOOPS_URL .'/uploads/' .(($submitUser && $submitUser->getVar('user_avatar') != "")?$submitUser->getVar('user_avatar') : 'blank.gif'));
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject', 's'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', (isset($departments[$ticketInfo->getVar('department')]) ? $departments[$ticketInfo->getVar('department')]->getVar('department') : _XHELP_TEXT_NO_DEPT));
        $xoopsTpl->assign('xhelp_departmenturl', 'index.php?op=staffViewAll&amp;dept='. $ticketInfo->getVar('department'));
        $xoopsTpl->assign('xhelp_departmentid', $ticketInfo->getVar('department'));
        $xoopsTpl->assign('xhelp_departments', $aDept);
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', $ticketInfo->getVar('status'));
        $xoopsTpl->assign('xhelp_text_status', xhelpGetStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_userIP', $ticketInfo->getVar('userIP'));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->lastUpdated('m'));
        $xoopsTpl->assign('xhelp_priorities', array(5, 4, 3, 2, 1));
        $xoopsTpl->assign('xhelp_priorities_desc', array('5' => _XHELP_PRIORITY5, '4' => _XHELP_PRIORITY4,'3' => _XHELP_PRIORITY3, '2' => _XHELP_PRIORITY2, '1' => _XHELP_PRIORITY1));
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        if($giveOwnership){
            $xoopsTpl->assign('xhelp_ticket_ownerUid', $owner->getVar('uid'));
            $xoopsTpl->assign('xhelp_ticket_ownership', xhelpGetUsername($owner, $displayName));
            $xoopsTpl->assign('xhelp_ownerinfo', XOOPS_URL . '/userinfo.php?uid=' . $owner->getVar('uid'));
        }
        $xoopsTpl->assign('xhelp_ticket_closedBy', $ticketInfo->getVar('closedBy'));
        $xoopsTpl->assign('xhelp_ticket_totalTimeSpent', $ticketInfo->getVar('totalTimeSpent'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', (($user)?xhelpGetUsername($user, $displayName):$xoopsConfig['anonymous']));
        $xoopsTpl->assign('xhelp_userlevel', (($user)?$user->getVar('level'):0));
        $xoopsTpl->assign('xhelp_email', (($user)?$user->getVar('email'):''));
        $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $xhelp_id));
        $xoopsTpl->assign('xhelp_notifiedUsers', $aNotified);
        $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);

        if(isset($aMessages)){
            $xoopsTpl->assign('xhelp_logMessages', $aMessages);
        } else {
            $xoopsTpl->assign('xhelp_logMessages', 0);
        }
        $xoopsTpl->assign('xhelp_aOwnership', $aOwnership);
        if($has_responses){
            $xoopsTpl->assign('xhelp_aResponses', $aResponses);
        }
        unset($aResponses);
        if($has_files){
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
        $xoopsTpl->assign('xhelp_showActions', $xoopsModuleConfig['xhelp_staffTicketActions']);

        $xoopsTpl->assign('xhelp_has_changeOwner', false);
        if($ticketInfo->getVar('uid') == $xoopsUser->getVar('uid')){
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

        $checkRights = array(
        XHELP_SEC_TICKET_OWNERSHIP => array('xhelp_has_changeOwner', false),
        XHELP_SEC_RESPONSE_ADD => array('xhelp_has_addResponse', true),
        XHELP_SEC_TICKET_EDIT => array('xhelp_has_editTicket', true),
        XHELP_SEC_TICKET_DELETE => array('xhelp_has_deleteTicket', true),
        XHELP_SEC_TICKET_MERGE => array('xhelp_has_mergeTicket', true),
        XHELP_SEC_TICKET_PRIORITY => array('xhelp_has_changePriority', true),
        XHELP_SEC_TICKET_STATUS => array('xhelp_has_changeStatus', false),
        XHELP_SEC_RESPONSE_EDIT => array('xhelp_has_editResponse', false),
        XHELP_SEC_FILE_DELETE => array('xhelp_has_deleteFile', false),
        XHELP_SEC_FAQ_ADD => array('xhelp_has_faqAdd', false),
        XHELP_SEC_TICKET_TAKE_OWNERSHIP => array('xhelp_has_takeOwnership', false));

        // See if this user is accepted for this ticket
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $xhelp_id));
        $crit->add(new Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails =& $hTicketEmails->getObjects($crit);

        foreach ($checkRights as $right=>$desc) {
            if(($right == XHELP_SEC_RESPONSE_ADD) && (count($ticketEmails) > 0)){
                //Is this user in the ticket emails list (should be treated as a user)
                $xoopsTpl->assign($desc[0], true);
                $colspan ++;
                continue;
            }
            if(($right == XHELP_SEC_TICKET_STATUS) && count($ticketEmails > 0)){
                //Is this user in the ticket emails list (should be treated as a user)
                $xoopsTpl->assign($desc[0], true);
                $colspan ++;
                continue;
            }
            if ($hasRights = $xhelp_staff->checkRoleRights($right, $ticketInfo->getVar('department'))) {
                $xoopsTpl->assign($desc[0], true);
            } else {
                if ($desc[1]) {
                    $colspan --;
                }

            }

        }
        $xoopsTpl->assign('xhelp_actions_colspan', $colspan);

        $crit = new Criteria('', '');
        $crit->setSort('description');
        $crit->setOrder('ASC');
        $statuses =& $hStatus->getObjects($crit);
        $aStatuses = array();
        foreach($statuses as $status){
            $aStatuses[$status->getVar('id')] = array('id' => $status->getVar('id'),
                                                      'desc' => $status->getVar('description'),
                                                      'state' => $status->getVar('state'));
        }
        unset($statuses);

        $xoopsTpl->assign('xhelp_statuses', $aStatuses);

        $custFields =& $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', (!empty($custFields)) ? true : false);
        $xoopsTpl->assign('xhelp_custFields', $custFields);
        unset($custFields);
        $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);

        require(XOOPS_ROOT_PATH.'/footer.php');
        break;

    case "user":
        // Check if user has permission to view ticket
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $xhelp_id));
        $crit->add(new Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails =& $hTicketEmails->getObjects($crit);
        if(count($ticketEmails) == 0){
            redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_INV_USER);
        }

        $xoopsOption['template_main'] = 'xhelp_user_ticketDetails.html';   // Set template
        require(XOOPS_ROOT_PATH.'/header.php');                     // Include
        $responses = $ticketInfo->getResponses();
        foreach($responses as $response){
            $hasFiles = false;
            foreach($aFiles as $file){
                if($file['responseid'] == $response->getVar('id')){
                    $hasFiles = true;
                    break;
                }
            }

            $staffReview =& $hStaffReview->getReview($xhelp_id, $response->getVar('id'), $xoopsUser->getVar('uid'));
            if (count($staffReview) > 0) {
                $review = $staffReview[0];
            }
            //$responseOwner =& $member_handler->getUser($response->getVar('uid'));

            $aResponses[] = array('id'=>$response->getVar('id'),
                                  'uid'=>$response->getVar('uid'),
                                  'uname'=>'',
                                  'ticketid'=>$response->getVar('ticketid'),
                                  'message'=>$response->getVar('message'),
                                  'timeSpent'=>$response->getVar('timeSpent'),
                                  'updateTime'=>$response->posted('m'),
                                  'userIP'=>$response->getVar('userIP'),
                                  'rating'=>(isset($review)?xhelpGetRating($review->getVar('rating')):0),
                                  'user_sig'=>'',
                                  'private'=>$response->getVar('private'),
                                  'hasFiles' => $hasFiles,
                                  'user_avatar' => XOOPS_URL .'/uploads/blank.gif');
            //XOOPS_URL .'/uploads/' .(($responseOwner)?$responseOwner->getVar('user_avatar') : 'blank.gif'));

            $all_users[$response->getVar('uid')] = '';
        }

        if (isset($review)) {
            unset($review);
        }
        $staff = array();
        $_staff = $hStaff->getObjects(new Criteria('uid', "(". implode(array_keys($all_users), ',') . ")", 'IN'), true);
        foreach($_staff as $key=>$_user) {
            $staff[$key] = $_user->getVar('attachSig');
        }
        unset($_staff);

        $users = array();
        $_users = $member_handler->getUsers(new Criteria('uid', "(". implode(array_keys($all_users), ',') . ")", 'IN'), true);
        foreach ($_users as $key=>$_user) {
            $users[$key] = array('uname' => xhelpGetUsername($_user, $xoopsModuleConfig['xhelp_displayName']),
            //Display signature if user is a staff member + has set signature to display
            //or user with signature set to display
                                'user_sig' => (isset($staff[$key]) && $staff[$key]) || (!isset($staff[$key]) && $user->getVar('attachsig')) ? $_user->getVar('user_sig') : '',
                                'user_avatar' => (strlen($_user->getVar('user_avatar')) ? $_user->getVar('user_avatar') : 'blank.gif'));
        }
        unset($_users);
        unset($_user);
        unset($all_users);

        for($i=0;$i<count($aResponses);$i++) {
            $_response = $aResponses[$i];
            $_uid = $_response['uid'];
            if (isset($users[$_uid])) {
                $aResponses[$i]['user_sig'] = $users[$_uid]['user_sig'];
                $aResponses[$i]['user_avatar'] = XOOPS_URL.'/uploads/'. $users[$_uid]['user_avatar'];
                $aResponses[$i]['uname'] = $users[$_uid]['uname'];
            }
        }
        unset($users);

        $has_responses = count($responses) > 0;
        unset($responses);

        $hStatus =& xhelpGetHandler('status');
        $myStatus =& $hStatus->get($ticketInfo->getVar('status'));

        // Smarty variables
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
        $reopenTicket = $xoopsModuleConfig['xhelp_allowReopen'] && $myStatus->getVar('state') == 2;
        $xoopsTpl->assign('xhelp_reopenTicket', $reopenTicket);
        $xoopsTpl->assign('xhelp_allowResponse', ($myStatus->getVar('state') != 2) || $reopenTicket);
        $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL .'/');
        $xoopsTpl->assign('xoops_module_header',$xhelp_module_header);
        $xoopsTpl->assign('xhelp_ticketID', $xhelp_id);
        $xoopsTpl->assign('xhelp_ticket_uid', $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
        $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
        $xoopsTpl->assign('xhelp_ticket_department', $department->getVar('department'));
        $xoopsTpl->assign('xhelp_ticket_priority', $ticketInfo->getVar('priority'));
        $xoopsTpl->assign('xhelp_ticket_status', $myStatus->getVar('description')); // xhelpGetStatus($ticketInfo->getVar('status')));
        $xoopsTpl->assign('xhelp_ticket_posted', $ticketInfo->posted('m'));
        $xoopsTpl->assign('xhelp_ticket_lastUpdated', $ticketInfo->posted('m'));
        $xoopsTpl->assign('xhelp_userinfo', XOOPS_URL . '/userinfo.php?uid=' . $ticketInfo->getVar('uid'));
        $xoopsTpl->assign('xhelp_username', $user->getVar('uname'));
        $xoopsTpl->assign('xhelp_email', $user->getVar('email'));
        $xoopsTpl->assign('xhelp_priorities', array(5, 4, 3, 2, 1));
        $xoopsTpl->assign('xhelp_priorities_desc', array('5' => _XHELP_PRIORITY5, '4' => _XHELP_PRIORITY4,'3' => _XHELP_PRIORITY3, '2' => _XHELP_PRIORITY2, '1' => _XHELP_PRIORITY1));
        $xoopsTpl->assign('xhelp_uid', $xoopsUser->getVar('uid'));
        if($has_responses){
            $xoopsTpl->assign('xhelp_aResponses', $aResponses);
        }
        if($has_files){
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

        $custFields =& $ticketInfo->getCustFieldValues();
        $xoopsTpl->assign('xhelp_hasCustFields', (!empty($custFields)) ? true : false);
        $xoopsTpl->assign('xhelp_custFields', $custFields);
        $xoopsTpl->assign('xhelp_uploadPath', XHELP_UPLOAD_PATH);
        $xoopsTpl->assign('xhelp_allowUpload', $xoopsModuleConfig['xhelp_allowUpload']);

        require(XOOPS_ROOT_PATH.'/footer.php');
        break;

    case "userResponse":
        if(isset($_POST['newResponse'])){
            // Check if user has permission to view ticket
            $hTicketEmails =& xhelpGetHandler('ticketEmails');
            $crit = new Criteria('ticketid', $xhelp_id);
            $ticketEmails =& $hTicketEmails->getObjects($crit);
            $canChange = false;
            foreach($ticketEmails as $ticketEmail){
                if($xoopsUser->getVar('uid') == $ticketEmail->getVar('uid')){
                    $canChange = true;
                    break;
                }
            }

            $hStatus =& xhelpGetHandler('status');
            if($canChange){
                $oldStatus =& $hStatus->get($ticketInfo->getVar('status'));
                if($oldStatus->getVar('state') == 2){     //If the ticket is resolved
                    $ticketInfo->setVar('closedBy', 0);
                    $ticketInfo->setVar('status', 1);
                    $ticketInfo->setVar('overdueTime', $ticketInfo->getVar('posted') + ($xoopsModuleConfig['xhelp_overdueTime'] *60*60));
                } elseif(isset($_POST['closeTicket']) && $_POST['closeTicket'] == 1){ // If the user closes the ticket
                    $ticketInfo->setVar('closedBy', $ticketInfo->getVar('uid'));
                    $ticketInfo->setVar('status', 3);   // Todo: make moduleConfig for default resolved status?
                }
                $ticketInfo->setVar('lastUpdated', $ticketInfo->lastUpdated('m'));

                if($hTickets->insert($ticketInfo, true)){   // Insert the ticket
                    $newStatus =& $hStatus->get($ticketInfo->getVar('status'));

                    if($newStatus->getVar('state') == 2){
                        $_eventsrv->trigger('close_ticket', array(&$ticketInfo));
                    }elseif($oldStatus->getVar('id') <> $newStatus->getVar('id') && $newStatus->getVar('state') <> 2){
                        $_eventsrv->trigger('update_status', array(&$ticketInfo, &$oldStatus, &$newStatus));
                    }
                }
                if($_POST['userResponse'] <> ''){       // If the user does not add any text in the response
                    $newResponse =& $hResponses->create();
                    $newResponse->setVar('uid', $xoopsUser->getVar('uid'));
                    $newResponse->setVar('ticketid', $xhelp_id);
                    $newResponse->setVar('message', $_POST['userResponse']);
                    //      $newResponse->setVar('updateTime', $newResponse->posted('m'));
                    $newResponse->setVar('updateTime', time());
                    $newResponse->setVar('userIP', getenv("REMOTE_ADDR"));

                    if($hResponses->insert($newResponse)){
                        $_eventsrv->trigger('new_response', array(&$ticketInfo, &$newResponse));
                        $message = _XHELP_MESSAGE_USER_MOREINFO;

                        if($xoopsModuleConfig['xhelp_allowUpload']){    // If uploading is allowed
                            if(is_uploaded_file($_FILES['userfile']['tmp_name'])){
                                if (!$ret = $ticketInfo->checkUpload('userfile', $allowed_mimetypes, $errors)) {
                                    $errorstxt = implode('<br />', $errors);

                                    $message = sprintf(_XHELP_MESSAGE_FILE_ERROR, $errorstxt);
                                    redirect_header(XHELP_BASE_URL."/addTicket.php", 5, $message);
                                }
                                $file = $ticketInfo->storeUpload('userfile', $newResponse->getVar('id'), $allowed_mimetypes);
                            }
                        }
                    } else {
                        $message = _XHELP_MESSAGE_USER_MOREINFO_ERROR;
                    }
                } else {
                    if($newStatus->getVar('state') != 2){
                        $message = _XHELP_MESSAGE_USER_NO_INFO;
                    } else {
                        $message = _XHELP_MESSAGE_UPDATE_STATUS;
                    }
                }
            } else {
                $message = _XHELP_MESSAGE_NOT_USER;
            }
            redirect_header("ticket.php?id=$xhelp_id", 3, $message);
        }
        break;

    case "deleteFile":
        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_FILE_DELETE, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_DELETE_FILE;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, $message);
        }

        if(!isset($_GET['fileid'])){
            $message = '';
            redirect_header(XHELP_BASE_URL."/ticket.phpid=$xhelp_id", 3, $message);
        }

        if(isset($_GET['field'])){      // Remove filename from custom field
            $field = $_GET['field'];
            $hTicketValues =& xhelpGetHandler('ticketValues');
            $ticketValues =& $hTicketValues->get($xhelp_id);

            $ticketValues->setVar($field, "");
            $hTicketValues->insert($ticketValues, true);
        }

        $hFile =& xhelpGetHandler('file');
        $fileid = intval($_GET['fileid']);
        $file =& $hFile->get($fileid);

        if(!$hFile->delete($file, true)){
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$xhelp_id", 3, _XHELP_MESSAGE_DELETE_FILE_ERR);
        }
        $_eventsrv->trigger('delete_file', array(&$file));
        header("Location: ".XHELP_BASE_URL."/ticket.php?id=$xhelp_id");

        break;

    default:
        redirect_header(XHELP_BASE_URL."/index.php", 3);
        break;
}

function &_getTicketFields(&$ticket)
{
    $hFieldDept =& xhelpGetHandler('ticketFieldDepartment');
    $fields =& $hFieldDept->fieldsByDepartment($ticket->getVar('department'));
    $values =& $ticket->getCustFieldValues(true);
    foreach($fields as $field) {
        $_arr = $field->toArray();
        $fieldname = $_arr['fieldname'];
        $_arr['value'] = $values[$fieldname]['value'];
        $_arr['fileid'] = $values[$fieldname]['fileid'];
        $_arr['filename'] = $values[$fieldname]['filename'];
        $ret[] = $_arr;
    }

    return $ret;
}
