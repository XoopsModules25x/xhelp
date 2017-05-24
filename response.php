<?php
//$Id: response.php,v 1.68 2005/12/21 16:07:41 eric_juden Exp $
require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');

if(!$xoopsUser){
    redirect_header(XOOPS_URL .'/user.php', 3);
}

$refresh = 0;
if(isset($_GET['refresh'])){
    $refresh = intval($_GET['refresh']);
}

$uid = $xoopsUser->getVar('uid');

// Get the id of the ticket
if(isset($_GET['id'])){
    $ticketid = intval($_GET['id']);
}

if (isset($_GET['responseid'])) {
    $responseid = intval($_GET['responseid']);
}

$hTicket      =& xhelpGetHandler('ticket');
$hResponseTpl =& xhelpGetHandler('responseTemplates');
$hMembership  =& xhelpGetHandler('membership');
$hResponse    =& xhelpGetHandler('responses');
$hStaff       =& xhelpGetHandler('staff');

if (!$ticketInfo =& $hTicket->get($ticketid)) {
    //Invalid ticketID specified
    redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_INV_TICKET);
}

$has_owner = $ticketInfo->getVar('ownership');

$op = 'staffFrm'; //Default Action for page

if(isset($_GET['op'])){
    $op = $_GET['op'];
}

if (isset($_POST['op'])) {
    $op = $_POST['op'];
}

switch ($op) {

    case 'staffAdd':
        //0. Check that the user can perform this action
        $message = '';
        $url = XHELP_BASE_URL.'/index.php';
        $hasErrors = false;
        $errors = array();
        $uploadFile = $ticketReopen = $changeOwner = $ticketClosed = $newStatus = false;

        if ($xhelp_isStaff) {
            // Check if staff has permission to respond to the ticket
            $hTicketEmails =& xhelpGetHandler('ticketEmails');
            $crit = new CriteriaCompo(new Criteria('ticketid', $ticketid));
            $crit->add(new Criteria('uid', $xoopsUser->getVar('uid')));
            $ticketEmails = $hTicketEmails->getObjects($crit);
            if(count($ticketEmails > 0) || $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))){
                //1. Verify Response fields are filled properly
                require_once(XHELP_CLASS_PATH.'/validator.php');
                $v = array();
                $v['response'][] = new ValidateLength($_POST['response'], 2, 50000);
                $v['timespent'][] = new ValidateNumber($_POST['timespent']);

                if($xoopsModuleConfig['xhelp_allowUpload'] && is_uploaded_file($_FILES['userfile']['tmp_name'])){
                    $hMime =& xhelpGetHandler('mimetype');
                    //Add File Upload Validation Rules
                    $v['userfile'][] = new ValidateMimeType($_FILES['userfile']['name'], $_FILES['userfile']['type'], $hMime->getArray());
                    $v['userfile'][] = new ValidateFileSize($_FILES['userfile']['tmp_name'], $xoopsModuleConfig['xhelp_uploadSize']);
                    $v['userfile'][] = new ValidateImageSize($_FILES['userfile']['tmp_name'], $xoopsModuleConfig['xhelp_uploadWidth'], $xoopsModuleConfig['xhelp_uploadHeight']);
                    $uploadFile = true;
                }

                // Perform each validation
                $fields = array();
                $errors = array();
                foreach($v as $fieldname=>$validator) {
                    if (!xhelpCheckRules($validator, $errors)) {
                        $hasErrors = true;
                        //Mark field with error
                        $fields[$fieldname]['haserrors'] = true;
                        $fields[$fieldname]['errors'] = $errors;
                    } else {
                        $fields[$fieldname]['haserrors'] = false;
                    }
                }

                if ($hasErrors) {
                    //Store field values in session
                    //Store error messages in session
                    _setResponseToSession($ticketInfo, $fields);
                    //redirect to response.php?op=staffFrm
                    header("Location: ". XHELP_BASE_URL."/response.php?op=staffFrm&id=$ticketid");
                    exit();
                }

                //Check if status changed
                if ($_POST['status'] <> $ticketInfo->getVar('status')) {
                    $hStatus =& xhelpGetHandler('status');
                    $oldStatus = $hStatus->get($ticketInfo->getVar('status'));
                    $newStatus = $hStatus->get(intval($_POST['status']));

                    if ($oldStatus->getVar('state') == 1 && $newStatus->getVar('state') == 2) {
                        $ticketClosed = true;
                    } elseif ($oldStatus->getVar('state') ==2  && $newStatus->getVar('state') == 1) {
                        $ticketReopen = true;
                    }
                    $ticketInfo->setVar('status', intval($_POST['status']));
                }

                //Check if user claimed ownership
                if (isset($_POST['claimOwner']) && $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
                    $ownerid = intval($_POST['claimOwner']);
                    if ($ownerid > 0) {
                        $oldOwner = $ticketInfo->getVar('ownership');
                        $ticketInfo->setVar('ownership', $ownerid);
                        $changeOwner = true;
                    }
                }

                //2. Fill Response Object
                $response =& $hResponse->create();
                $response->setVar('uid', $xoopsUser->getVar('uid'));
                $response->setVar('ticketid', $ticketid);
                $response->setVar('message', $_POST['response']);
                $response->setVar('timeSpent', $_POST['timespent']);
                $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));
                $response->setVar('userIP', getenv("REMOTE_ADDR"));
                if(isset($_POST['private'])){
                    $response->setVar('private', $_POST['private']);
                }

                //3. Store Response Object in DB
                if ($hResponse->insert($response)) {
                    $_eventsrv->trigger('new_response', array(&$ticketInfo, &$response));
                } else {
                    //Store response fields in session
                    _setResponseToSession($ticketInfo,$fields);
                    //Notify user of error (using redirect_header())'
                    redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE_ERROR);
                }

                //4. Update Ticket object
                if (isset($_POST['timespent'])) {
                    $oldspent = $ticketInfo->getVar('totalTimeSpent');
                    $ticketInfo->setVar('totalTimeSpent', $oldspent + intval($_POST['timespent']));
                }
                if($ticketClosed){
                    $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                }
                $ticketInfo->setVar('lastUpdated', time());

                //5. Store Ticket Object
                if ($hTicket->insert($ticketInfo)) {
                    if ($newStatus) {
                        $_eventsrv->trigger('update_status', array(&$ticketInfo, &$oldStatus, &$newStatus));
                    }
                    if ($ticketClosed) {
                        $_eventsrv->trigger('close_ticket', array(&$ticketInfo));
                    }
                    if ($ticketReopen) {
                        $_eventsrv->trigger('reopen_ticket', array(&$ticketInfo));
                    }
                    if ($changeOwner) {
                        // @todo - Change this event to take the new owner as a parameter as well
                        $_eventsrv->trigger('update_owner', array(&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')));
                    }
                } else {
                    //Ticket Update Error
                    redirect_header(XHELP_BASE_URL."/response.php?op=staffFrm&id=$ticketid", 3, _XHELP_MESSAGE_EDITTICKET_ERROR);
                    exit();
                }

                //6. Save Attachments
                if ($uploadFile) {
                    $allowed_mimetypes = $hMime->checkMimeTypes('userfile');
                    if (!$file = $ticketInfo->storeUpload('userfile', $response->getVar('id'), $allowed_mimetypes)) {
                        redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDFILE_ERROR);
                        exit();
                    }
                }

                //7. Success, clear session, redirect to ticket
                _clearResponseFromSession();
                redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADDRESPONSE);
            } else {
                redirect_header($url, 3, _XHELP_ERROR_NODEPTPERM);
                exit();
            }
        }
        break;

    case 'staffFrm':
        $isSubmitter = false;
        $isStaff = $hMembership->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'));

        // Check if staff has permission to respond to the ticket
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $ticketid));
        $crit->add(new Criteria('uid', $xoopsUser->getVar('uid')));
        $ticketEmails = $hTicketEmails->getObjects($crit);
        if(count($ticketEmails) > 0){
            $isSubmitter = true;
        }
        if($isSubmitter || $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $ticketInfo->getVar('department'))){
            $hStatus =& xhelpGetHandler('status');
            $crit = new Criteria('', '');
            $crit->setSort('description');
            $crit->setOrder('ASC');
            $statuses = $hStatus->getObjects($crit);
            $aStatuses = array();
            foreach($statuses as $status){
                $aStatuses[$status->getVar('id')] = array('id' => $status->getVar('id'),
                                                      'desc' => $status->getVar('description'),
                                                      'state' => $status->getVar('state'));
            }

            $xoopsOption['template_main'] = 'xhelp_response.html';   // Set template
            require(XOOPS_ROOT_PATH.'/header.php');

            $xoopsTpl->assign('xhelp_allowUpload', $xoopsModuleConfig['xhelp_allowUpload']);
            $xoopsTpl->assign('xhelp_has_owner', $has_owner);
            $xoopsTpl->assign('xhelp_currentUser', $xoopsUser->getVar('uid'));
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
            $xoopsTpl->assign('xhelp_ticketID', $ticketid);
            $xoopsTpl->assign('xhelp_ticket_status', $ticketInfo->getVar('status'));
            $xoopsTpl->assign('xhelp_ticket_description', $ticketInfo->getVar('description'));
            $xoopsTpl->assign('xhelp_ticket_subject', $ticketInfo->getVar('subject'));
            $xoopsTpl->assign('xhelp_statuses', $aStatuses);
            $xoopsTpl->assign('xhelp_isSubmitter', $isSubmitter);
            $xoopsTpl->assign('xhelp_ticket_details', sprintf(_XHELP_TEXT_TICKETDETAILS, $ticketInfo->getVar('id')));
            $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);
            $xoopsTpl->assign('xhelp_has_takeOwnership', $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));

            $aElements = array();
            if($validateErrors =& $_xhelpSession->get('xhelp_validateError')){
                $errors = array();
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

            $elements = array('response', 'timespent');
            foreach($elements as $element){         // Foreach element in the predefined list
                $xoopsTpl->assign("xhelp_element_$element", "formButton");
                foreach($aElements as $aElement){   // Foreach that has an error
                    if($aElement == $element){      // If the names are equal
                        $xoopsTpl->assign("xhelp_element_$element", "validateError");
                        break;
                    }
                }
            }

            //Get all staff defined templates
            $crit = new Criteria('uid', $uid);
            $crit->setSort('name');
            $responseTpl = $hResponseTpl->getObjects($crit, true);

            $xoopsTpl->append('xhelp_responseTpl_values', '------------------');
            $xoopsTpl->append('xhelp_responseTpl_ids', 0);

            foreach($responseTpl as $obj) {
                $xoopsTpl->append('xhelp_responseTpl_values', $obj->getVar('name'));
                $xoopsTpl->append('xhelp_responseTpl_ids', $obj->getVar('id'));
            }
            $xoopsTpl->assign('xhelp_hasResponseTpl', (isset($responseTpl) ? count($responseTpl) > 0 : 0));
            $xoopsTpl->append('xhelp_responseTpl_selected', $refresh);
            $xoopsTpl->assign('xhelp_templateID', $refresh);

            //Format Response Message Var
            $message = '';
            if($refresh) {
                if($displayTpl = $responseTpl[$refresh]) {
                    $message = $displayTpl->getVar('response', 'e');
                }
            }
            if ($temp = $_xhelpSession->get('xhelp_response_message')) {
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
            require(XOOPS_ROOT_PATH.'/footer.php');
        }
        break;

    case 'staffEdit':
        //Is current user staff member?
        if (!$hMembership->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_NODEPTPERM);
            exit();
        }

        if (!$response =& $hResponse->get($responseid)) {
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, _XHELP_ERROR_INV_RESPONSE);
        }

        if(!$hasRights = $xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_EDIT, $ticketInfo->getVar('department'))){
            $message = _XHELP_MESSAGE_NO_EDIT_RESPONSE;
            redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, $message);
        }

        $xoopsOption['template_main'] = 'xhelp_editResponse.html';             // Always set main template before including the header
        require(XOOPS_ROOT_PATH . '/header.php');

        $hStatus =& xhelpGetHandler('status');
        $crit = new Criteria('', '');
        $crit->setSort('description');
        $crit->setOrder('ASC');
        $statuses = $hStatus->getObjects($crit);
        $aStatuses = array();
        foreach($statuses as $status){
            $aStatuses[$status->getVar('id')] = array('id' => $status->getVar('id'),
                                                  'desc' => $status->getVar('description'),
                                                  'state' => $status->getVar('state'));
        }
        $xoopsTpl->assign('xhelp_statuses', $aStatuses);
        $xoopsTpl->assign('xhelp_responseid', $responseid);
        $xoopsTpl->assign('xhelp_ticketID', $ticketid);
        $xoopsTpl->assign('xhelp_responseMessage', $response->getVar('message', 'e'));
        $xoopsTpl->assign('xhelp_timeSpent', $response->getVar('timeSpent'));
        $xoopsTpl->assign('xhelp_status', $ticketInfo->getVar('status'));
        $xoopsTpl->assign('xhelp_has_owner', $has_owner);
        $xoopsTpl->assign('xhelp_responsePrivate', (($response->getVar('private') == 1) ? _XHELP_TEXT_YES : _XHELP_TEXT_NO));
        $xoopsTpl->assign('xhelp_currentUser', $uid);
        $xoopsTpl->assign('xhelp_allowUpload', 0);
        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
        $xoopsTpl->assign('xhelp_has_takeOwnership', $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department')));
        //$xoopsTpl->assign('xhelp_text_subject', _XHELP_TEXT_SUBJECT);
        //$xoopsTpl->assign('xhelp_text_description', _XHELP_TEXT_DESCRIPTION);

        $aElements = array();
        $errors = array();
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

        $elements = array('response', 'timespent');
        foreach($elements as $element){         // Foreach element in the predefined list
            $xoopsTpl->assign("xhelp_element_$element", "formButton");
            foreach($aElements as $aElement){   // Foreach that has an error
                if($aElement == $element){      // If the names are equal
                    $xoopsTpl->assign("xhelp_element_$element", "validateError");
                    break;
                }
            }
        }

        $hResponseTpl =& xhelpGetHandler('responseTemplates');          // Used to display responseTemplates
        $crit = new Criteria('uid', $uid);
        $crit->setSort('name');
        $responseTpl = $hResponseTpl->getObjects($crit);

        $aResponseTpl = array();
        foreach($responseTpl as $response){
            $aResponseTpl[] = array('id'=>$response->getVar('id'),
            'uid'=>$response->getVar('uid'),
            'name'=>$response->getVar('name'),
            'response'=>$response->getVar('response'));
        }
        $has_responseTpl = count($responseTpl) > 0;
        unset($responseTpl);
        $displayTpl =& $hResponseTpl->get($refresh);

        $xoopsTpl->assign('xhelp_response_text', ($refresh !=0 ? $displayTpl->getVar('response', 'e') : ''));
        $xoopsTpl->assign('xhelp_responseTpl',  $aResponseTpl);
        $xoopsTpl->assign('xhelp_hasResponseTpl', count($aResponseTpl) > 0);
        $xoopsTpl->assign('xhelp_refresh', $refresh);
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

        require(XOOPS_ROOT_PATH.'/footer.php');                             //Include the page footer
        break;

    case 'staffEditSave':
        require_once(XHELP_CLASS_PATH.'/validator.php');
        $v['response'][] = new ValidateLength($_POST['response'], 2, 50000);
        $v['timespent'][] = new ValidateNumber($_POST['timespent']);

        $responseStored = false;

        //Is current user staff member?
        if (!$hMembership->isStaffMember($xoopsUser->getVar('uid'), $ticketInfo->getVar('department'))) {
            redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_NODEPTPERM);
            exit();
        }

        //Retrieve the original response
        if (!$response =& $hResponse->get($responseid)) {
            redirect_header(XHELP_BASE_URL."/ticket.php?id=".$ticketInfo->getVar('id'), 3, _XHELP_ERROR_INV_RESPONSE);
        }

        //Copy original ticket and response objects
        $oldresponse    = $response;
        $oldticket      = $ticketInfo;

        $url = "response.php?op=staffEditSave&amp;id=$ticketid&amp;responseid=$responseid";
        $ticketReopen = $changeOwner = $ticketClosed = $newStatus = false;

        //Store current fields in session
        $_xhelpSession->set('xhelp_response_ticketid', $oldresponse->getVar('ticketid'));
        $_xhelpSession->set('xhelp_response_uid', $response->getVar('uid'));
        $_xhelpSession->set('xhelp_response_message', $_POST['response']);

        //Check if the ticket status has been changed
        if($_POST['status'] <> $ticketInfo->getVar('status')){
            $ticketInfo->setVar('status', $_POST['status']);
            $newStatus = true;

            if($_POST['status'] == 2) { //Closed Ticket
                $ticketInfo->setVar('closedBy', $xoopsUser->getVar('uid'));
                $ticketClosed = true;
            }

            if($oldticket->getVar('status') == 2) { //Ticket reopened
                $ticketReopen = true;
            }
        }
        $_xhelpSession->set('xhelp_response_status', $ticketInfo->getVar('status'));        // Store in session

        //Check if the current user is claiming the ticket
        if (isset($_POST['claimOwner']) && $_POST['claimOwner'] > 0 && $xhelp_staff->checkRoleRights(XHELP_SEC_TICKET_TAKE_OWNERSHIP, $ticketInfo->getVar('department'))) {
            if ($_POST['claimOwner'] != $oldticket->getVar('ownership')) {
                $oldOwner = $oldticket->getVar('ownership');
                $ticketInfo->setVar('ownership',$_POST['claimOwner']);
                $changeOwner = true;
            }
        }
        $_xhelpSession->set('xhelp_response_ownership', $ticketInfo->getVar('ownership'));  // Store in session

        // Check the timespent
        if (isset($_POST['timespent'])) {
            $timespent = intval($_POST['timespent']);
            $totaltime = $oldticket->getVar('totalTimeSpent') - $oldresponse->getVar('timeSpent') + $timespent;
            $ticketInfo->setVar('totalTimeSpent', $totaltime);
            $response->setVar('timeSpent', $timespent);
        }
        $_xhelpSession->set('xhelp_response_timespent', $response->getVar('timeSpent'));
        $_xhelpSession->set('xhelp_responseStored', true);

        // Perform each validation
        $fields = array();
        $errors = array();
        foreach($v as $fieldname=>$validator){
            if(!xhelpCheckRules($validator, $errors)){
                // Mark field with error
                $fields[$fieldname]['haserrors'] = true;
                $fields[$fieldname]['errors'] = $errors;
            } else {
                $fields[$fieldname]['haserrors'] = false;
            }
        }

        if(!empty($errors)){
            $_xhelpSession->set('xhelp_validateError', $fields);
            $message = _XHELP_MESSAGE_VALIDATE_ERROR;
            header("Location: ".XHELP_BASE_URL."/response.php?id=$ticketid&responseid=". $response->getVar('id') ."&op=staffEdit");
            exit();
        }

        $ticketInfo->setVar('lastUpdated', time());
         
        if ($hTicket->insert($ticketInfo)) {
            if ($newStatus) {
                // @todo - 'update_status' should also supply $newStatus
                $_eventsrv->trigger('update_status', array(&$ticketInfo, $oldStatus));
            }
            if ($ticketClosed) {
                $_eventsrv->trigger('close_ticket', array(&$ticketInfo));
            }
            if ($ticketReopen) {
                $_eventsrv->trigger('reopen_ticket', array(&$ticketInfo));
            }
            if ($changeOwner) {
                $_eventsrv->trigger('update_owner', array(&$ticketInfo, $oldOwner, $xoopsUser->getVar('uid')));
            }

            $message = $_POST['response'];
            $message .= "\n".sprintf(_XHELP_RESPONSE_EDIT, $xoopsUser->getVar('uname'), $ticketInfo->lastUpdated());

            $response->setVar('message', $message);
            if(isset($_POST['timespent'])){
                $response->setVar('timeSpent', intval($_POST['timespent']));
            }
            $response->setVar('updateTime', $ticketInfo->getVar('lastUpdated'));

            if ($hResponse->insert($response)) {
                $_eventsrv->trigger('edit_response', array(&$ticketInfo, &$response, &$oldticket, &$oldresponse));
                $message = _XHELP_MESSAGE_EDITRESPONSE;
                $url = "ticket.php?id=$ticketid";
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

function _setResponseToSession(&$ticket, &$errors)
{
    global $xoopsUser, $_xhelpSession;
    $_xhelpSession->set('xhelp_response_ticketid', $ticket->getVar('id'));
    $_xhelpSession->set('xhelp_response_uid', $xoopsUser->getVar('uid'));
    $_xhelpSession->set('xhelp_response_message', ( isset($_POST['response']) ? $_POST['response'] : '' ) );
    $_xhelpSession->set('xhelp_response_private', ( isset($_POST['private'])? $_POST['private'] : 0 ));
    $_xhelpSession->set('xhelp_response_timespent', ( isset($_POST['timespent']) ? $_POST['timespent'] : 0 ));
    $_xhelpSession->set('xhelp_response_ownership', ( isset($_POST['claimOwner']) && intval($_POST['claimOwner']) > 0 ? $_POST['claimOwner'] : 0) );
    $_xhelpSession->set('xhelp_response_status',  $_POST['status'] );
    $_xhelpSession->set('xhelp_response_private', $_POST['private'] );
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
