<?php

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';
//require_once XHELP_BASE_PATH . '/functions.php';

/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

// Disable module caching in smarty
$xoopsConfig['module_cache'][$xoopsModule->getVar('mid')] = 0;

if ($xoopsUser) {
    $responseTplID = 0;

    $op = 'default';
    if (Request::hasVar('op', 'REQUEST')) {
        $op = $_REQUEST['op'];
    }

    if (Request::hasVar('responseTplID', 'GET')) {
        $responseTplID = Request::getInt('responseTplID', 0, 'GET');
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staff_profile.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                     // Include the page header

    $numResponses = 0;
    $uid          = $xoopsUser->getVar('uid');
    $staffHandler = Xhelp\Helper::getInstance()->getHandler('Staff');
    if (!$staff = $staffHandler->getByUid($uid)) {
        redirect_header(XHELP_BASE_URL . '/index.php', 3, _XHELP_ERROR_INV_STAFF);
    }
    $hTicketList  = Xhelp\Helper::getInstance()->getHandler('TicketList');
    $hResponseTpl = Xhelp\Helper::getInstance()->getHandler('ResponseTemplates');
    $crit         = new \Criteria('uid', $uid);
    $crit->setSort('name');
    $responseTpl = $hResponseTpl->getObjects($crit);

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

    $displayTpl = $hResponseTpl->get($responseTplID);

    switch ($op) {
        case 'responseTpl':
            if (Request::hasVar('updateResponse', 'POST')) {
                if (Request::hasVar('attachSig', 'POST')) {
                    $staff->setVar('attachSig', $_POST['attachSig']);
                    if (!$staffHandler->insert($staff)) {
                        $message = _XHELP_MESSAGE_UPDATE_SIG_ERROR;
                    }
                }
                if ('' == $_POST['name'] || '' == $_POST['replyText']) {
                    redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_ERROR_INV_TEMPLATE);
                }
                if (0 != $_POST['responseid']) {
                    $updateTpl = $hResponseTpl->get($_POST['responseid']);
                } else {
                    $updateTpl = $hResponseTpl->create();
                }
                $updateTpl->setVar('uid', $uid);
                $updateTpl->setVar('name', $_POST['name']);
                $updateTpl->setVar('response', $_POST['replyText']);
                if ($hResponseTpl->insert($updateTpl)) {
                    $message = _XHELP_MESSAGE_RESPONSE_TPL;
                } else {
                    $message = _XHELP_MESSAGE_RESPONSE_TPL_ERROR;
                }
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, $message);
            } else {        // Delete response template
                $hResponseTpl = Xhelp\Helper::getInstance()->getHandler('ResponseTemplates');
                $displayTpl   = $hResponseTpl->get($_POST['tplID']);
                if ($hResponseTpl->delete($displayTpl)) {
                    $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL;
                } else {
                    $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL_ERROR;
                }
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, $message);
            }
            break;
        case 'updateNotification':
            $notArray = (is_array($_POST['notifications']) ? $_POST['notifications'] : [0]);
            $notValue = array_sum($notArray);
            $staff->setVar('notify', $notValue);
            if (Request::hasVar('email', 'POST') && $_POST['email'] != $staff->getVar('email')) {
                $staff->setVar('email', $_POST['email']);
            }
            if (!$staffHandler->insert($staff)) {
                $message = _XHELP_MESSAGE_UPDATE_EMAIL_ERROR;
            }
            $message = _XHELP_MESSAGE_NOTIFY_UPDATE;
            redirect_header(XHELP_BASE_URL . '/profile.php', 3, $message);
            break;
        case 'addTicketList':
            if (Request::hasVar('savedSearch', 'POST') && (0 != $_POST['savedSearch'])) {
                $searchid   = Request::getInt('savedSearch', 0, 'POST');
                $ticketList = $hTicketList->create();
                $ticketList->setVar('uid', $xoopsUser->getVar('uid'));
                $ticketList->setVar('searchid', $searchid);
                $ticketList->setVar('weight', $hTicketList->createNewWeight($xoopsUser->getVar('uid')));

                if ($hTicketList->insert($ticketList)) {
                    redirect_header(XHELP_BASE_URL . '/profile.php');
                } else {
                    redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_MSG_ADD_TICKETLIST_ERR);
                }
            }
            break;
        case 'editTicketList':
            if (Request::hasVar('id', 'REQUEST') && 0 != $_REQUEST['id']) {
                $listID = Request::getInt('id', 0, 'REQUEST');
            } else {
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_MSG_NO_ID);
            }
            break;
        case 'deleteTicketList':
            if (Request::hasVar('id', 'REQUEST') && 0 != $_REQUEST['id']) {
                $listID = Request::getInt('id', 0, 'REQUEST');
            } else {
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_MSG_NO_ID);
            }
            $ticketList = $hTicketList->get($listID);
            if ($hTicketList->delete($ticketList, true)) {
                redirect_header(XHELP_BASE_URL . '/profile.php');
            } else {
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_MSG_DEL_TICKETLIST_ERR);
            }
            break;
        case 'changeListWeight':
            if (Request::hasVar('id', 'REQUEST') && 0 != $_REQUEST['id']) {
                $listID = Request::getInt('id', 0, 'REQUEST');
            } else {
                redirect_header(XHELP_BASE_URL . '/profile.php', 3, _XHELP_MSG_NO_ID);
            }
            $up = false;
            if (Request::hasVar('up', 'REQUEST')) {
                $up = $_REQUEST['up'];
            }
            $hTicketList->changeWeight($listID, $up);
            redirect_header(XHELP_BASE_URL . '/profile.php');
            break;
        default:
            $xoopsTpl->assign('xhelp_responseTplID', $responseTplID);
            $module_header = '<!--[if lt IE 7]><script src="iepngfix.js" language="JavaScript" type="text/javascript"></script><![endif]-->';
            $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/assets/images/');
            $xoopsTpl->assign('xhelp_has_sig', $staff->getVar('attachSig'));
            if (isset($aResponseTpl)) {
                $xoopsTpl->assign('xhelp_responseTpl', $aResponseTpl);
            } else {
                $xoopsTpl->assign('xhelp_responseTpl', 0);
            }
            $xoopsTpl->assign('xhelp_hasResponseTpl', isset($aResponseTpl) ? count($aResponseTpl) > 0 : 0);
            if (!empty($responseTplID)) {
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
            $xoopsTpl->assign('xhelp_responseTime', Xhelp\Utility::formatTime(($staff->getVar('ticketsResponded') ? $staff->getVar('responseTime') / $staff->getVar('ticketsResponded') : 0)));
            $notify_method = $xoopsUser->getVar('notify_method');
            $xoopsTpl->assign('xhelp_notify_method', (1 == $notify_method) ? _XHELP_NOTIFY_METHOD1 : _XHELP_NOTIFY_METHOD2);

            if ((0 == $staff->getVar('rating')) || (0 == $staff->getVar('numReviews'))) {
                $xoopsTpl->assign('xhelp_rating', 0);
            } else {
                $xoopsTpl->assign('xhelp_rating', (int)($staff->getVar('rating') / $staff->getVar('numReviews')));
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

            $myRoles       = $staffHandler->getRoles($xoopsUser->getVar('uid'), true);
            $hNotification = Xhelp\Helper::getInstance()->getHandler('Notification');
            $settings      = $hNotification->getObjects(null, true);

            $templates         = $xoopsModule->getInfo('_email_tpl');
            $has_notifications = count($templates);

            // Check that notifications are enabled by admin
            $i             = 0;
            $staff_enabled = true;
            foreach ($templates as $template_id => $template) {
                if ('dept' === $template['category']) {
                    if (isset($settings[$template_id])) {
                        $staff_setting = $settings[$template_id]->getVar('staff_setting');
                        if (4 == $staff_setting) {
                            $staff_enabled = false;
                        } elseif (2 == $staff_setting) {
                            $staff_options = $settings[$template_id]->getVar('staff_options');
                            foreach ($staff_options as $role) {
                                if (array_key_exists($role, $myRoles)) {
                                    $staff_enabled = true;
                                    break;
                                }

                                $staff_enabled = false;
                            }
                        }
                    }
                    $deptNotification[] = [
                        'id'            => $template_id,
                        'name'          => $template['name'],
                        'category'      => $template['category'],
                        'template'      => $template['mail_template'],
                        'subject'       => $template['mail_subject'],
                        'bitValue'      => 2 ** $template['bit_value'],
                        'title'         => $template['title'],
                        'caption'       => $template['caption'],
                        'description'   => $template['description'],
                        'isChecked'     => ($staff->getVar('notify') & (2 ** $template['bit_value'])) > 0,
                        'staff_setting' => $staff_enabled,
                    ];
                }
            }
            if ($has_notifications) {
                $xoopsTpl->assign('xhelp_deptNotifications', $deptNotification);
            } else {
                $xoopsTpl->assign('xhelp_deptNotifications', 0);
            }

            $hReview  = Xhelp\Helper::getInstance()->getHandler('StaffReview');
            $hMembers = xoops_getHandler('member');
            $crit     = new \Criteria('staffid', $xoopsUser->getVar('uid'));
            $crit->setSort('id');
            $crit->setOrder('DESC');
            $crit->setLimit(5);

            $reviews = $hReview->getObjects($crit);

            $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

            foreach ($reviews as $review) {
                $reviewer = $hMembers->getUser($review->getVar('submittedBy'));
                $xoopsTpl->append(
                    'xhelp_reviews',
                    [
                        'rating'         => $review->getVar('rating'),
                        'ratingdsc'      => Xhelp\Utility::getRating($review->getVar('rating')),
                        'submittedBy'    => $reviewer ? Xhelp\Utility::getUsername($reviewer, $displayName) : $xoopsConfig['anonymous'],
                        'submittedByUID' => $review->getVar('submittedBy'),
                        'responseid'     => $review->getVar('responseid'),
                        'comments'       => $review->getVar('comments'),
                        'ticketid'       => $review->getVar('ticketid'),
                    ]
                );
            }
            $xoopsTpl->assign('xhelp_hasReviews', count($reviews) > 0);

            // Ticket Lists
            $ticketLists       = $hTicketList->getListsByUser($xoopsUser->getVar('uid'));
            $aMySavedSearches  = [];
            $mySavedSearches   = Xhelp\Utility::getSavedSearches([$xoopsUser->getVar('uid'), XHELP_GLOBAL_UID]);
            $has_savedSearches = (is_array($aMySavedSearches) && count($aMySavedSearches) > 0);
            $ticketListCount   = count($ticketLists);
            $aTicketLists      = [];
            $aUsedSearches     = [];
            $eleNum            = 0;
            foreach ($ticketLists as $ticketList) {
                $weight                                  = $ticketList->getVar('weight');
                $searchid                                = $ticketList->getVar('searchid');
                $aTicketLists[$ticketList->getVar('id')] = [
                    'id'            => $ticketList->getVar('id'),
                    'uid'           => $ticketList->getVar('uid'),
                    'searchid'      => $searchid,
                    'weight'        => $weight,
                    'name'          => $mySavedSearches[$ticketList->getVar('searchid')]['name'],
                    'hasWeightUp'   => ($eleNum != $ticketListCount - 1) ? true : false,
                    'hasWeightDown' => (0 != $eleNum) ? true : false,
                    'hasEdit'       => (-999 != $mySavedSearches[$ticketList->getVar('searchid')]['uid']) ? true : false,
                ];
                ++$eleNum;
                $aUsedSearches[$searchid] = $searchid;
            }
            unset($ticketLists);

            // Take used searches to get unused searches
            $aSearches = [];
            if ($mySavedSearches && is_array($mySavedSearches)) {
                foreach ($mySavedSearches as $savedSearch) {
                    if (!in_array($savedSearch['id'], $aUsedSearches)) {
                        if ('' != $savedSearch['id']) {
                            $aSearches[$savedSearch['id']] = $savedSearch;
                        }
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
    redirect_header(XOOPS_URL . '/user.php', 3);
}

require_once XOOPS_ROOT_PATH . '/footer.php';
