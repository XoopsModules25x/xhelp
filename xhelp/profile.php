<?php
//$Id: profile.php,v 1.42 2005/11/29 17:52:26 ackbarr Exp $
require_once('header.php');
include_once(XHELP_BASE_PATH.'/functions.php');

// Disable module caching in smarty
$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;

if($xoopsUser){
    $responseTplID = 0;

    $op = 'default';
    if(isset($_REQUEST['op'])){
        $op = $_REQUEST['op'];
    }

    if(isset($_GET['responseTplID'])){
        $responseTplID = intval($_GET['responseTplID']);
    }

    $xoopsOption['template_main'] = 'xhelp_staff_profile.html';   // Set template
    require(XOOPS_ROOT_PATH.'/header.php');                     // Include the page header

    $numResponses = 0;
    $uid = $xoopsUser->getVar('uid');
    $hStaff =& xhelpGetHandler('staff');
    if (!$staff =& $hStaff->getByUid($uid)) {
        redirect_header(XHELP_BASE_URL."/index.php", 3, _XHELP_ERROR_INV_STAFF);
        exit();
    }
    $hTicketList =& xhelpGetHandler('ticketList');
    $hResponseTpl =& xhelpGetHandler('responseTemplates');
    $crit = new Criteria('uid', $uid);
    $crit->setSort('name');
    $responseTpl =& $hResponseTpl->getObjects($crit);

    foreach($responseTpl as $response){
        $aResponseTpl[] = array('id'=>$response->getVar('id'),
                              'uid'=>$response->getVar('uid'),
                              'name'=>$response->getVar('name'),
                              'response'=>$response->getVar('response'));
    }
    $has_responseTpl = count($responseTpl) > 0;
    unset($responseTpl);

    $displayTpl =& $hResponseTpl->get($responseTplID);

    switch($op){
        case "responseTpl":
            if(isset($_POST['updateResponse'])){
                if(isset($_POST['attachSig'])){
                    $staff->setVar('attachSig', $_POST['attachSig']);
                    if(!$hStaff->insert($staff)){
                        $message = _XHELP_MESSAGE_UPDATE_SIG_ERROR;
                    }
                }
                if($_POST['name'] == '' || $_POST['replyText'] == ''){
                    redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_ERROR_INV_TEMPLATE);
                }
                if($_POST['responseid'] != 0){
                    $updateTpl =& $hResponseTpl->get($_POST['responseid']);
                } else {
                    $updateTpl =& $hResponseTpl->create();
                }
                $updateTpl->setVar('uid', $uid);
                $updateTpl->setVar('name',$_POST['name']);
                $updateTpl->setVar('response',$_POST['replyText']);
                if($hResponseTpl->insert($updateTpl)){
                    $message = _XHELP_MESSAGE_RESPONSE_TPL;
                } else {
                    $message = _XHELP_MESSAGE_RESPONSE_TPL_ERROR;
                }
                redirect_header(XHELP_BASE_URL."/profile.php", 3, $message);
            } else {        // Delete response template
                $hResponseTpl =& xhelpGetHandler('responseTemplates');
                $displayTpl =& $hResponseTpl->get($_POST['tplID']);
                if($hResponseTpl->delete($displayTpl)){
                    $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL;
                } else {
                    $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL_ERROR;
                }
                redirect_header(XHELP_BASE_URL."/profile.php", 3, $message);
            }
            break;

        case "updateNotification":
            $notArray = (is_array($_POST['notifications']) ?  $_POST['notifications'] : array(0));
            $notValue = array_sum($notArray);
            $staff->setVar('notify', $notValue);
            if(isset($_POST['email']) && $_POST['email'] <> $staff->getVar('email')){
                $staff->setVar('email', $_POST['email']);
            }
            if(!$hStaff->insert($staff)){
                $message = _XHELP_MESSAGE_UPDATE_EMAIL_ERROR;

            }
            $message = _XHELP_MESSAGE_NOTIFY_UPDATE;
            redirect_header(XHELP_BASE_URL."/profile.php", 3, $message);
            break;

        case "addTicketList":
            if(isset($_POST['savedSearch']) && ($_POST['savedSearch'] != 0)){
                $searchid = intval($_POST['savedSearch']);
                $ticketList =& $hTicketList->create();
                $ticketList->setVar('uid', $xoopsUser->getVar('uid'));
                $ticketList->setVar('searchid', $searchid);
                $ticketList->setVar('weight', $hTicketList->createNewWeight($xoopsUser->getVar('uid')));

                if($hTicketList->insert($ticketList)){
                    header("Location: ".XHELP_BASE_URL."/profile.php");
                } else {
                    redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_MSG_ADD_TICKETLIST_ERR);
                }
            }
            break;

        case "editTicketList":
            if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
                $listID = intval($_REQUEST['id']);
            } else {
                redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_MSG_NO_ID);
            }
            break;

        case "deleteTicketList":
            if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
                $listID = intval($_REQUEST['id']);
            } else {
                redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_MSG_NO_ID);
            }
            $ticketList =& $hTicketList->get($listID);
            if($hTicketList->delete($ticketList, true)){
                header("Location: ".XHELP_BASE_URL."/profile.php");
            } else {
                redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_MSG_DEL_TICKETLIST_ERR);
            }
            break;

        case "changeListWeight":
            if(isset($_REQUEST['id']) && $_REQUEST['id'] != 0){
                $listID = intval($_REQUEST['id']);
            } else {
                redirect_header(XHELP_BASE_URL."/profile.php", 3, _XHELP_MSG_NO_ID);
            }
            $up = false;
            if(isset($_REQUEST['up'])){
                $up = $_REQUEST['up'];
            }
            $hTicketList->changeWeight($listID, $up);
            header("Location: ".XHELP_BASE_URL."/profile.php");
            break;

        default:
            $xoopsTpl->assign('xhelp_responseTplID', $responseTplID);
            $module_header = '<!--[if gte IE 5.5000]><script src="iepngfix.js" language="JavaScript" type="text/javascript"></script><![endif]-->';
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL .'/modules/xhelp/images/');
            $xoopsTpl->assign('xhelp_has_sig', $staff->getVar('attachSig'));
            if(isset($aResponseTpl)){
                $xoopsTpl->assign('xhelp_responseTpl', $aResponseTpl);
            } else {
                $xoopsTpl->assign('xhelp_responseTpl', 0);
            }
            $xoopsTpl->assign('xhelp_hasResponseTpl', (isset($aResponseTpl)) ? count($aResponseTpl) > 0 : 0);
            if(!empty($responseTplID)){
                $xoopsTpl->assign('xhelp_displayTpl_id', $displayTpl->getVar('id'));
                $xoopsTpl->assign('xhelp_displayTpl_name', $displayTpl->getVar('name'));
                $xoopsTpl->assign('xhelp_displayTpl_response', $displayTpl->getVar('response', 'e'));
            } else {
                $xoopsTpl->assign('xhelp_displayTpl_id', 0);
                $xoopsTpl->assign('xhelp_displayTpl_name', '');
                $xoopsTpl->assign('xhelp_displayTpl_response', '');
            }
            $xoopsTpl->assign('xoops_module_header', $module_header);
            $xoopsTpl->assign('xhelp_callsClosed', $staff->getVar('callsClosed'));
            $xoopsTpl->assign('xhelp_numReviews', $staff->getVar('numReviews'));
            $xoopsTpl->assign('xhelp_responseTime', xhelpFormatTime( ($staff->getVar('ticketsResponded') ? $staff->getVar('responseTime') / $staff->getVar('ticketsResponded') : 0)));
            $notify_method = $xoopsUser->getVar('notify_method');
            $xoopsTpl->assign('xhelp_notify_method', ($notify_method == 1) ? _XHELP_NOTIFY_METHOD1 : _XHELP_NOTIFY_METHOD2);

            if(($staff->getVar('rating') == 0) || ($staff->getVar('numReviews') == 0)){
                $xoopsTpl->assign('xhelp_rating', 0);
            } else {
                $xoopsTpl->assign('xhelp_rating', intval($staff->getVar('rating')/$staff->getVar('numReviews')));
            }
            $xoopsTpl->assign('xhelp_uid', $xoopsUser->getVar('uid'));
            $xoopsTpl->assign('xhelp_rating0', _XHELP_RATING0);
            $xoopsTpl->assign('xhelp_rating1', _XHELP_RATING1);
            $xoopsTpl->assign('xhelp_rating2', _XHELP_RATING2);
            $xoopsTpl->assign('xhelp_rating3', _XHELP_RATING3);
            $xoopsTpl->assign('xhelp_rating4', _XHELP_RATING4);
            $xoopsTpl->assign('xhelp_rating5', _XHELP_RATING5);
            $xoopsTpl->assign('xhelp_staff_email', $staff->getVar('email'));
            $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);

            $myRoles =& $hStaff->getRoles($xoopsUser->getVar('uid'), true);
            $hNotification =& xhelpGetHandler('notification');
            $settings =& $hNotification->getObjects(null, true);

            $templates =& $xoopsModule->getInfo('_email_tpl');
            $has_notifications = count($templates);

            // Check that notifications are enabled by admin
            $i = 0;
            $staff_enabled = true;
            foreach($templates as $template_id=>$template){
                if($template['category'] == 'dept'){
                    $staff_setting = $settings[$template_id]->getVar('staff_setting');
                    if($staff_setting == 4){
                        $staff_enabled = false;
                    } elseif($staff_setting == 2){
                        $staff_options = $settings[$template_id]->getVar('staff_options');
                        foreach($staff_options as $role){
                            if(array_key_exists($role, $myRoles)){
                                $staff_enabled = true;
                                break;
                            } else {
                                $staff_enabled = false;
                            }
                        }
                    }

                    $deptNotification[] = array('id'=> $template_id,
                                                'name'=>$template['name'],
                                                'category'=>$template['category'],
                                                'template'=>$template['mail_template'],
                                                'subject'=>$template['mail_subject'],
                                                'bitValue'=>(pow(2, $template['bit_value'])),
                                                'title'=>$template['title'],
                                                'caption'=>$template['caption'],
                                                'description'=>$template['description'],
                                                'isChecked'=>($staff->getVar('notify') & pow(2, $template['bit_value'])) > 0,
                                                'staff_setting'=> $staff_enabled);
                }
            }
            if($has_notifications){
                $xoopsTpl->assign('xhelp_deptNotifications', $deptNotification);
            } else {
                $xoopsTpl->assign('xhelp_deptNotifications', 0);
            }

            $hReview  =& xhelpGetHandler('staffReview');
            $hMembers =& xoops_gethandler('member');
            $crit = new Criteria('staffid', $xoopsUser->getVar('uid'));
            $crit->setSort('id');
            $crit->setOrder('DESC');
            $crit->setLimit(5);

            $reviews =& $hReview->getObjects($crit);

            $displayName =& $xoopsModuleConfig['xhelp_displayName'];    // Determines if username or real name is displayed

            foreach ($reviews as $review) {
                $reviewer = $hMembers->getUser($review->getVar('submittedBy'));
                $xoopsTpl->append('xhelp_reviews', array('rating' => $review->getVar('rating'),
                            'ratingdsc' => xhelpGetRating($review->getVar('rating')),
                            'submittedBy' => ($reviewer ? xhelpGetUsername($reviewer, $displayName) : $xoopsConfig['anonymous']),
                            'submittedByUID' => $review->getVar('submittedBy'),
                            'responseid' => $review->getVar('responseid'),
                            'comments' => $review->getVar('comments'),
                            'ticketid' => $review->getVar('ticketid')));
            }
            $xoopsTpl->assign('xhelp_hasReviews', (count($reviews) > 0));

            // Ticket Lists
            $ticketLists =& $hTicketList->getListsByUser($xoopsUser->getVar('uid'));
            $aMySavedSearches = array();
            $mySavedSearches = xhelpGetSavedSearches(array($xoopsUser->getVar('uid'), XHELP_GLOBAL_UID));
            $has_savedSearches = count($aMySavedSearches > 0);
            $ticketListCount = count($ticketLists);
            $aTicketLists = array();
            $aUsedSearches = array();
            $eleNum = 0;
            foreach($ticketLists as $ticketList){
                $weight = $ticketList->getVar('weight');
                $searchid = $ticketList->getVar('searchid');
                $aTicketLists[$ticketList->getVar('id')] = array('id' => $ticketList->getVar('id'),
                                                                 'uid' => $ticketList->getVar('uid'),
                                                                 'searchid' => $searchid,
                                                                 'weight' => $weight,
                                                                 'name' => $mySavedSearches[$ticketList->getVar('searchid')]['name'],
                                                                 'hasWeightUp' => (($eleNum != $ticketListCount - 1) ? true : false),
                                                                 'hasWeightDown' => (($eleNum != 0) ? true : false),
                                                                 'hasEdit' => (($mySavedSearches[$ticketList->getVar('searchid')]['uid'] != -999) ? true : false));
                $eleNum++;
                $aUsedSearches[$searchid] = $searchid;
            }
            unset($ticketLists);

            // Take used searches to get unused searches
            $aSearches = array();
            foreach($mySavedSearches as $savedSearch){
                if(!in_array($savedSearch['id'], $aUsedSearches)){
                    if($savedSearch['id'] != ""){
                        $aSearches[$savedSearch['id']] = $savedSearch;
                    }
                }
            }
            $hasUnusedSearches = count($aSearches) > 0;
            $xoopsTpl->assign('xhelp_ticketLists', $aTicketLists);
            $xoopsTpl->assign('xhelp_hasTicketLists', count($aTicketLists) > 0);
            $xoopsTpl->assign('xhelp_unusedSearches', $aSearches);
            $xoopsTpl->assign('xhelp_hasUnusedSearches', $hasUnusedSearches);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
            break;
    }
} else {
    redirect_header(XOOPS_URL .'/user.php', 3);
}

require(XOOPS_ROOT_PATH.'/footer.php');

?>