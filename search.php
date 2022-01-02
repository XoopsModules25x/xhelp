<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';
require_once XOOPS_ROOT_PATH . '/class/pagenav.php';

/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

$staffHandler       = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
$departmentHandler  = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
$ticketHandler      = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
$savedSearchHandler = new Xhelp\SavedSearchHandler($GLOBALS['xoopsDB']);
$ticketFieldHandler = new Xhelp\TicketFieldHandler($GLOBALS['xoopsDB']);

if (!$xoopsUser) {
    redirect_header(XOOPS_URL, 3, _NOPERM);
}

if (!$staffHandler->isStaff($xoopsUser->getVar('uid'))) {
    redirect_header(XHELP_BASE_URL . '/index.php', 3, _NOPERM);
}

if ($xoopsUser) {
    $start        = $limit = 0;
    $page_vars    = ['limit', 'start', 'sort', 'order'];
    $sort_order   = ['ASC', 'DESC'];
    $sort         = '';
    $order        = '';
    $displayName  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed
    $returnPage   = false;
    $aReturnPages = ['profile'];
    if (Request::hasVar('return', 'GET') && in_array($_GET['return'], $aReturnPages)) {
        $returnPage = $_GET['return'];
    }

    foreach ($page_vars as $var) {
        if (isset($_REQUEST[$var])) {
            $$var = $_REQUEST[$var];
        }
    }
    $limit         = $limit;
    $start         = $start;
    $sort          = \mb_strtolower($sort);
    $order         = (in_array(mb_strtoupper($order), $sort_order) ? $order : 'ASC');
    $sort_columns  = [
        'id',
        'priority',
        'elapsed',
        'lastupdate',
        'status',
        'subject',
        'department',
        'ownership',
        'uid',
    ];
    $sort          = (in_array($sort, $sort_columns) ? $sort : '');
    $hasCustFields = false;

    // Make sure start is greater than 0
    $start = max($start, 0);

    // Make sure limit is set
    if (!$limit) {
        $limit = $helper->getConfig('xhelp_staffTicketCount');
    }

    $pagenav_vars = "limit=$limit";
    $uid          = $xoopsUser->getVar('uid');

    $viewResults = false;
    $op          = 'default';
    if (Request::hasVar('op', 'REQUEST')) {
        $op = $_REQUEST['op'];
    }

    switch ($op) {
        case 'edit':
            if (Request::hasVar('id', 'REQUEST') && 0 != $_REQUEST['id']) {
                $searchid = Request::getInt('id', 0, 'REQUEST');
                if (!array_key_exists($searchid, $aSavedSearches)) {
                    if (false !== $returnPage) {
                        redirect_header(XHELP_BASE_URL . '/' . $returnPage . '.php', 3, _XHELP_MSG_NO_EDIT_SEARCH);
                    } else {
                        redirect_header(XHELP_BASE_URL . '/search.php', 3, _XHELP_MSG_NO_EDIT_SEARCH);
                    }
                }
            } else {
                if (false !== $returnPage) {
                    redirect_header(XHELP_BASE_URL . '/' . $returnPage . '.php', 3, _XHELP_MSG_NO_ID);
                } else {
                    redirect_header(XHELP_BASE_URL . '/search.php', 3, _XHELP_MSG_NO_ID);
                }
            }
            $GLOBALS['xoopsOption']['template_main'] = 'xhelp_editSearch.tpl';   // Set template
            require_once XOOPS_ROOT_PATH . '/header.php';                     // Include the page header
            $mySearch = $savedSearchHandler->get($searchid);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
            if (is_object($mySearch)) {   // Go through saved search info, set values on page
                $vars        = [
                    'ticketid',
                    'department',
                    'description',
                    'subject',
                    'priority',
                    'status',
                    'state',
                    'uid',
                    'submittedBy',
                    'ownership',
                    'closedBy',
                ];
                $ticketid    = '';
                $department  = -1;
                $description = '';
                $subject     = '';
                $priority    = -1;
                $status      = -1;
                $state       = -1;
                $uid         = '';
                $submittedBy = '';
                $ownership   = '';
                $closedBy    = '';

                $fields      = $ticketFieldHandler->getObjects();
                $aFields     = [];
                $aFieldnames = [];
                foreach ($fields as $field) {
                    $vars[]                        = $field->getVar('fieldname');
                    ${$field->getVar('fieldname')} = '';
                    $values                        = $field->getVar('fieldvalues');
                    if (XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
                        $values = ((1 == $values) ? _YES : _NO);
                    }
                    $defaultValue                      = $field->getVar('defaultvalue');
                    $aFields[$field->getVar('id')]     = [
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
                    $aFieldnames[$field->getVar('id')] = $field->getVar('fieldname');
                }
                unset($fields);

                $criteria           = unserialize($mySearch->getVar('search'));
                $pagenav_vars   = $mySearch->getVar('pagenav_vars');
                $searchLimit    = $criteria->getLimit();
                $searchStart    = $criteria->getStart();
                $criteria           = get_object_vars($criteria);
                $critElements   = $criteria['criteriaElements'];
                $hasSubmittedBy = false;
                foreach ($critElements as $critEle) {
                    $critEle = get_object_vars($critEle);
                    $colName = $critEle['column'];
                    if (in_array($colName, $vars)) {
                        switch ($colName) {
                            case 'department':
                            case 'status':
                                $eleValue   = str_replace('(', '', $critEle['value']);
                                $eleValue   = str_replace(')', '', $eleValue);
                                ${$colName} = $eleValue;
                                ${$colName} = explode(',', ${$colName});
                                break;
                            case 'uid':
                                if (!$hasSubmittedBy) {
                                    $submitted_string = mb_strstr($pagenav_vars, 'submittedBy=');
                                    if ($submitted_string) {
                                        $end_string           = mb_strpos($submitted_string, '&');
                                        $submitted_string_sub = mb_substr($submitted_string, 0, $end_string);
                                        if ($submitted_string_sub) {
                                            $submitted_string = $submitted_string_sub;
                                        }
                                        $submitted_string = explode('=', $submitted_string);
                                        $submitted_string = $submitted_string[1];
                                        $submittedBy      = $xoopsUser::getUnameFromId((int)$submitted_string);
                                        $hasSubmittedBy   = true;
                                    }
                                }
                                break;
                            default:
                                $eleValue  = $critEle['value'];
                                $eleLength = mb_strlen($eleValue);
                                $firstSpot = mb_strpos($eleValue, '%');
                                $lastSpot  = mb_strrpos($eleValue, '%');
                                if (false !== $firstSpot && false !== $lastSpot) {
                                    $eleValue = mb_substr($eleValue, 1, $eleLength - 2);
                                }
                                ${$colName} = $eleValue;
                                break;
                        }
                        $arr_key = array_search($colName, $aFieldnames, true);
                        if (false !== $arr_key) {
                            $aFields[$arr_key]['defaultvalue'] = ${$colName};
                        }
                    }
                }
                foreach ($vars as $var) {
                    $xoopsTpl->assign('xhelp_search' . $var, $$var);
                }

                $xoopsTpl->assign('xhelp_custFields', $aFields);
                if (!empty($aFields)) {
                    $xoopsTpl->assign('xhelp_hasCustFields', true);
                } else {
                    $xoopsTpl->assign('xhelp_hasCustFields', false);
                }
                $_xhelpSession->set('xhelp_custFields', $aFields);
                $staff = Xhelp\Utility::getStaff($displayName);
                $xoopsTpl->assign('xhelp_staff', $staff);
                $memberHandler = new Xhelp\MembershipHandler($GLOBALS['xoopsDB']);
                if (1 == $helper->getConfig('xhelp_deptVisibility')) {    // Apply dept visibility to staff members?
                    $depts = $memberHandler->getVisibleDepartments($xoopsUser->getVar('uid'));
                } else {
                    $depts = $memberHandler->membershipByStaff($xoopsUser->getVar('uid'));
                }
                foreach ($depts as $dept) {
                    $myDepts[$dept->getVar('id')] = $dept->getVar('department');
                }
                unset($depts);
                asort($myDepts);
                $myDepts[-1] = _XHELP_TEXT_SELECT_ALL;
                $xoopsTpl->assign('xhelp_depts', $myDepts);

                $statusHandler = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
                $crit_stat     = new \Criteria('', '');
                $crit_stat->setSort('description');
                $crit_stat->setOrder('ASC');
                $statuses  = $statusHandler->getObjects($crit_stat);
                $aStatuses = [];
                foreach ($statuses as $status) {
                    $aStatuses[$status->getVar('id')] = $status->getVar('description');
                }
                unset($statuses);
                $xoopsTpl->assign('xhelp_statuses', $aStatuses);
                $xoopsTpl->assign('xhelp_searchid', $mySearch->getVar('id'));
                $xoopsTpl->assign('xhelp_searchName', $mySearch->getVar('name'));
                $xoopsTpl->assign('xhelp_searchLimit', $searchLimit);
                $xoopsTpl->assign('xhelp_searchStart', $searchStart);
                $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
                $xoopsTpl->assign(
                    'xhelp_priorities_desc',
                    [
                        '5' => _XHELP_PRIORITY5,
                        '4' => _XHELP_PRIORITY4,
                        '3' => _XHELP_PRIORITY3,
                        '2' => _XHELP_PRIORITY2,
                        '1' => _XHELP_PRIORITY1,
                    ]
                );
                $xoopsTpl->assign('xhelp_imagePath', XHELP_BASE_URL . '/assets/images/');
                $xoopsTpl->assign('xhelp_returnPage', $returnPage);
            }

            break;
        case 'editSave':

            break;
        case 'search':
        default:
            $GLOBALS['xoopsOption']['template_main'] = 'xhelp_search.tpl';   // Set template
            require_once XOOPS_ROOT_PATH . '/header.php';                     // Include the page header

            $xoopsTpl->assign('xhelp_imagePath', XHELP_BASE_URL . '/assets/images/');
            $xoopsTpl->assign('xhelp_uid', $uid);
            $xoopsTpl->assign('xhelp_returnPage', $returnPage);
            $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
            $viewResults = false;

            // Start of hack by trabis/tdm
            $recieve_datemin = 0;
            $recieve_datemax = 0;
            $datemin_use     = 0;
            $datemax_use     = 0;

            if (Request::hasVar('datemin_use', 'REQUEST')) {
                $datemin_use = 1;
            }
            if (Request::hasVar('datemax_use', 'REQUEST')) {
                $datemax_use = 1;
            }

            $date_criteria = new \CriteriaCompo();
            if (Request::hasVar('recieve_datemin', 'REQUEST') && 1 == $datemin_use) {
                $recieve_datemin = strtotime($_REQUEST['recieve_datemin']);
                $date_criteria->add(new \Criteria('t.posted', (string)$recieve_datemin, '>='));
            }
            if (Request::hasVar('recieve_datemax', 'REQUEST') && 1 == $datemax_use) {
                $recieve_datemax = strtotime($_REQUEST['recieve_datemax']) + 60 * 60 * 24 - 1;
                $date_criteria->add(new \Criteria('t.posted', (string)$recieve_datemax, '<='));
            }

            //recherche recieve_date
            xoops_load('XoopsFormLoader');
            $aff_date = new \XoopsFormElementTray('', '');
            $date_min = new \XoopsFormTextDateSelect(_XHELP_TEXT_DATE_MIN, 'recieve_datemin', 10, strtotime((string)$recieve_datemin));
            //No request done, set default value for form
            if (0 == $recieve_datemin) {
                $datemin_use = 1;
            }
            $date_min_use = new \XoopsFormCheckBox('', 'datemin_use', $datemin_use);
            $date_min_use->addOption(1, _XHELP_TEXT_USE);
            //No request done, set default value for form
            $date_max = new \XoopsFormTextDateSelect(_XHELP_TEXT_DATE_MAX, 'recieve_datemax', 10, strtotime((string)$recieve_datemax));
            if (0 == $recieve_datemax) {
                $datemax_use = 1;
            }
            $date_max_use = new \XoopsFormCheckBox('', 'datemax_use', $datemax_use);
            $date_max_use->addOption('1', _XHELP_TEXT_USE);

            $aff_date->addElement($date_min);
            $aff_date->addElement($date_min_use);
            $aff_date->addElement($date_max);
            $aff_date->addElement($date_max_use);
            $dateform = $aff_date->render();

            $xoopsTpl->assign('dateform', $dateform);
            // End of hack

            // If search submitted, or moving to another page of search results, or submitted a saved search
            if (Request::hasVar('search', 'POST') || isset($_GET['start']) || isset($_REQUEST['savedSearch'])) {
                if (Request::hasVar('savedSearch', 'REQUEST') && 0 != $_REQUEST['savedSearch']) {     // If this is a saved search
                    if (!isset($_POST['delete_savedSearch'])) {   // If not deleting saved search
                        $mySavedSearch = $savedSearchHandler->get($_REQUEST['savedSearch']);
                        $criteria          = unserialize($mySavedSearch->getVar('search'));                   // Set $criteria object
                        $pagenav_vars  = $mySavedSearch->getVar('pagenav_vars');     // set pagenav vars

                        if (0 != $criteria->getLimit()) {
                            $limit = $criteria->getLimit();                         // Set limit
                        }
                        $start = $criteria->getStart();                         // Set start

                        $custFields = $_xhelpSession->get('xhelp_custFields');
                        if ($custFields) {     // Custom fields
                            $hasCustFields = true;
                        }
                    } else {        // If deleting saved search
                        $mySavedSearch = $aSavedSearches[Request::getInt('savedSearch', 0, 'REQUEST')];   // Retrieve saved search
                        if (XHELP_GLOBAL_UID == $mySavedSearch['uid']) {
                            redirect_header(XHELP_BASE_URL . '/search.php', 3, _XHELP_MSG_NO_DEL_SEARCH);
                        }
                        $criteria = new \Criteria('id', $mySavedSearch['id']);
                        if ($savedSearchHandler->deleteAll($criteria)) {
                            $_xhelpSession->del('xhelp_savedSearches');
                            redirect_header(XHELP_BASE_URL . '/search.php');
                        } else {
                            redirect_header(XHELP_BASE_URL . '/search.php', 3, _XHELP_MESSAGE_DELETE_SEARCH_ERR);
                        }
                    }
                } elseif (Request::hasVar('search', 'POST')
                          || isset($_GET['start'])) { // If this is a new search or next page in search results
                    $criteria = new \CriteriaCompo(new \Criteria('uid', $xoopsUser->getVar('uid'), '=', 'j'));
                    $vars = [
                        'ticketid',
                        'department',
                        'description',
                        'subject',
                        'priority',
                        'status',
                        'state',
                        'submittedBy',
                        'ownership',
                        'closedBy',
                    ];
                    //hack
                    $criteria->add($date_criteria);
                    //end of hack
                    $custFields = $_xhelpSession->get('xhelp_custFields');
                    if ($custFields) {     // Custom fields
                        $hasCustFields = false;
                        foreach ($custFields as $field) {
                            $fieldname = $field['fieldname'];
                            if (isset($_REQUEST[$fieldname]) && '' != $_REQUEST[$fieldname]
                                && -1 != $_REQUEST[$fieldname]) {
                                $hasCustFields = true;
                                $criteria->add(new \Criteria($fieldname, '%' . $_REQUEST[$fieldname] . '%', 'LIKE', 'f'));
                            }
                        }
                    }
                    // Finished with session var - delete it now
                    $_xhelpSession->del('xhelp_custFields');

                    foreach ($vars as $var) {
                        if (isset($_POST[$var])) {
                            $$var = $_POST[$var];
                        } elseif (isset($_GET[$var])) {
                            $$var = $_GET[$var];
                        }
                    }

                    if (isset($ticketid) && $ticketid = (int)$ticketid) {
                        $criteria->add(new \Criteria('id', (string)$ticketid, '=', 't'));
                        $pagenav_vars .= "&amp;ticketid=$ticketid";
                    }

                    if (isset($department)) {
                        if (!in_array('-1', $department)) {
                            $department = array_filter($department);
                            $criteria->add(new \Criteria('department', '(' . implode(',', $department) . ')', 'IN', 't'));
                            $pagenav_vars .= '&amp;department[]=' . implode('&amp;department[]=', $department);
                        }
                    }

                    if (isset($description) && $description) {
                        $criteria->add(new \Criteria('description', "%$description%", 'LIKE', 't'));
                        $pagenav_vars .= "&amp;description=$description";
                    }

                    if (isset($subject) && $subject) {
                        $criteria->add(new \Criteria('subject', "%$subject%", 'LIKE', 't'));
                        $pagenav_vars .= "&amp;subject=$subject";
                    }

                    if (isset($priority) && (-1 != $priority)) {
                        $priority = (int)$priority;
                        $criteria->add(new \Criteria('priority', (string)$priority, '=', 't'));
                        $pagenav_vars .= "&amp;priority=$priority";
                    }

                    if (isset($status)) {
                        if (is_array($status)) {
                            $status = array_filter($status);
                            $criteria->add(new \Criteria('status', '(' . implode(',', $status) . ')', 'IN', 't'));
                            $pagenav_vars .= '&amp;status[]=' . implode('&amp;status[]=', $status);
                        } else {
                            $criteria->add(new \Criteria('status', (int)$status, '=', 't'));
                            $pagenav_vars .= "&amp;status=$status";
                        }
                    } else {        // Only evaluate if status is not set
                        if (isset($state) && -1 != $state) {
                            $criteria->add(new \Criteria('state', (int)$state, '=', 's'));
                            $pagenav_vars .= "&amp;state=$state";
                        }
                    }

                    if (isset($submittedBy) && $submittedBy) {
                        if (mb_strlen($submittedBy) > 0) {
                            if (!is_numeric($submittedBy)) {
                                /** @var \XoopsMemberHandler $memberHandler */
                                $memberHandler = xoops_getHandler('member');
                                $users         = $memberHandler->getUsers(new \Criteria('uname', $submittedBy));
                                if ($users) {
                                    $submittedBy = $users[0]->getVar('uid');
                                } elseif ($users = $memberHandler->getUsers(new \Criteria('email', "%$submittedBy%", 'LIKE'))) {
                                    $submittedBy = $users[0]->getVar('uid');
                                } else {
                                    $submittedBy = -1;
                                }
                            }
                            $submittedBy = (int)$submittedBy;
                            $criteria->add(new \Criteria('uid', (string)$submittedBy, '=', 't'));
                            $pagenav_vars .= "&amp;submittedBy=$submittedBy";
                        }
                    }
                    if (isset($ownership) && (-1 != $ownership)) {
                        $ownership = (int)$ownership;
                        $criteria->add(new \Criteria('ownership', (string)$ownership, '=', 't'));
                        $pagenav_vars .= "&amp;ownership=$ownership";
                    }
                    if (isset($closedBy) && (-1 != $closedBy)) {
                        $closedBy = (int)$closedBy;
                        $criteria->add(new \Criteria('closedBy', (string)$closedBy, '=', 't'));
                        $pagenav_vars .= "&amp;closedBy=$closedBy";
                    }
                    $criteria->setStart($start);
                    $criteria->setLimit($limit);
                    $criteria->setSort($sort);
                    $criteria->setOrder($order);

                    if (Request::hasVar('save', 'POST') && 1 == $_POST['save']) {
                        if (Request::hasVar('searchid', 'POST') && 0 != $_POST['searchid']) {
                            $exSearch = $savedSearchHandler->get(Request::getInt('searchid', 0, 'POST'));
                            $exSearch->setVar('uid', $xoopsUser->getVar('uid'));
                            $exSearch->setVar('name', $_POST['searchName']);
                            $exSearch->setVar('search', serialize($criteria));
                            $exSearch->setVar('pagenav_vars', $pagenav_vars);
                            $exSearch->setVar('hasCustFields', ($hasCustFields ? 1 : 0));

                            if ($savedSearchHandler->insert($exSearch)) {  // If saved, store savedSearches in a session var
                                $_xhelpSession->del('xhelp_savedSearches');
                            }
                            unset($exSearch);
                            if (false !== $returnPage) {
                                redirect_header(XHELP_BASE_URL . '/' . $returnPage . '.php');
                            }
                        } else {
                            if ('' != $_POST['searchName']) {
                                $newSearch = $savedSearchHandler->create();
                                $newSearch->setVar('uid', $xoopsUser->getVar('uid'));
                                $newSearch->setVar('name', $_POST['searchName']);
                                $newSearch->setVar('search', serialize($criteria));
                                $newSearch->setVar('pagenav_vars', $pagenav_vars);
                                $newSearch->setVar('hasCustFields', ($hasCustFields ? 1 : 0));

                                if ($savedSearchHandler->insert($newSearch)) {  // If saved, store savedSearches in a session var
                                    $_xhelpSession->del('xhelp_savedSearches');
                                }
                                unset($newSearch);
                                if (false !== $returnPage) {
                                    redirect_header(XHELP_BASE_URL . '/' . $returnPage . '.php');
                                }
                            }
                        }
                    }
                }
                $viewResults = true;

                $tickets = $ticketHandler->getObjectsByStaff($criteria, false, $hasCustFields);

                $total = $ticketHandler->getCountByStaff($criteria, $hasCustFields);
                //$pageNav = new  XoopsPageNav($total, $limit, $start, "start", "limit=$limit&department=$search_department&description=$search_description&subject=$search_subject&priority=$search_priority&status=$search_status&submittedBy=$search_submittedBy&ownership=$search_ownership&closedBy=$search_closedBy");   // New PageNav object
                $pageNav = new \XoopsPageNav($total, $limit, $start, 'start', $pagenav_vars);
                $xoopsTpl->assign('xhelp_pagenav', $pageNav->renderNav());
                unset($pageNav);
                /** @var \XoopsMemberHandler $memberHandler */
                $memberHandler = xoops_getHandler('member');
                foreach ($tickets as $ticket) {
                    $user  = $memberHandler->getUser($ticket->getVar('uid'));
                    $owner = $memberHandler->getUser($ticket->getVar('ownership'));
                    //$closer = $memberHandler->getUser($ticket->getVar('closedBy'));
                    $department = $departmentHandler->get($ticket->getVar('department'));
                    //if ($owner) {
                    $overdue = false;
                    if ($ticket->isOverdue()) {
                        $overdue = true;
                    }

                    $aTickets[$ticket->getVar('id')] = [
                        'id'             => $ticket->getVar('id'),
                        'uid'            => $ticket->getVar('uid'),
                        'uname'          => $user ? $user->getVar('uname') : $xoopsConfig['anonymous'],
                        'userinfo'       => XOOPS_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                        'subject'        => xoops_substr($ticket->getVar('subject'), 0, 35),
                        'full_subject'   => $ticket->getVar('subject'),
                        'description'    => $ticket->getVar('description'),
                        'department'     => $department->getVar('department'),
                        'departmentid'   => $department->getVar('id'),
                        'departmenturl'  => Xhelp\Utility::createURI(
                            'index.php',
                            [
                                'op'   => 'staffViewAll',
                                'dept' => $department->getVar('id'),
                            ]
                        ),
                        'priority'       => $ticket->getVar('priority'),
                        'status'         => Xhelp\Utility::getStatus($ticket->getVar('status')),
                        'posted'         => $ticket->posted(),
                        'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                        'ownership'      => ($owner
                                             && '' != $owner->getVar('uname')) ? $owner->getVar('uname') : _XHELP_NO_OWNER,
                        'ownerid'        => ($owner && 0 != $owner->getVar('uid')) ? $owner->getVar('uid') : 0,
                        'ownerinfo'      => ($owner && 0 != $owner->getVar('uid')) ? XOOPS_URL . '/userinfo.php?uid=' . $owner->getVar('uid') : 0,
                        'closedBy'       => $ticket->getVar('closedBy'),
                        'closedByUname'  => $xoopsUser::getUnameFromId($ticket->getVar('closedBy')),
                        'url'            => XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'),
                        'elapsed'        => $ticket->elapsed(),
                        'lastUpdate'     => $ticket->lastUpdate(),
                        'overdue'        => $overdue,
                    ];
                    unset($user);
                    unset($owner);
                    //$closer = $memberHandler->getUser($ticket->getVar('closedBy'));
                    unset($department);
                }
                unset($tickets);
                $xoopsTpl->assign('xhelp_viewResults', $viewResults);
                if (isset($aTickets)) {
                    $xoopsTpl->assign('xhelp_allTickets', $aTickets);
                    $xoopsTpl->assign('xhelp_has_tickets', true);
                } else {
                    $xoopsTpl->assign('xhelp_allTickets', 0);
                    $xoopsTpl->assign('xhelp_has_tickets', false);
                }

                $tpl_cols = [];
                //Setup Column Sorting Vars
                foreach ($sort_columns as $col) {
                    $col_qs = ['sort' => $col];
                    if ($sort == $col) {
                        $col_qs_order = ($order == $sort_order[0] ? $sort_order[1] : $sort_order[0]);
                        $col_sortby   = true;
                    } else {
                        $col_qs_order = $order;
                        $col_sortby   = false;
                    }
                    $tpl_cols[$col] = [
                        'url'      => "search.php?$pagenav_vars&amp;start=$start&amp;sort=$col&amp;order=$col_qs_order",
                        'urltitle' => _XHELP_TEXT_SORT_TICKETS,
                        'sortby'   => $col_sortby,
                        'sortdir'  => \mb_strtolower($col_qs_order),
                    ];
                }
                $xoopsTpl->assign('xhelp_cols', $tpl_cols);
            } else {
                $xoopsTpl->assign('xhelp_viewResults', $viewResults);
            }
            $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);
            $xoopsTpl->assign('xhelp_text_allTickets', _XHELP_TEXT_SEARCH_RESULTS);
            $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
            $xoopsTpl->assign(
                'xhelp_priorities_desc',
                [
                    '5' => _XHELP_PRIORITY5,
                    '4' => _XHELP_PRIORITY4,
                    '3' => _XHELP_PRIORITY3,
                    '2' => _XHELP_PRIORITY2,
                    '1' => _XHELP_PRIORITY1,
                ]
            );
            $staff = Xhelp\Utility::getStaff($displayName);
            $xoopsTpl->assign('xhelp_staff', $staff);
            $memberHandler = new Xhelp\MembershipHandler($GLOBALS['xoopsDB']);
            if (1 == $helper->getConfig('xhelp_deptVisibility')) {    // Apply dept visibility to staff members?
                $membershipHandler = new Xhelp\MembershipHandler($GLOBALS['xoopsDB']);
                $depts             = $membershipHandler->getVisibleDepartments($xoopsUser->getVar('uid'));
            } else {
                $depts = $memberHandler->membershipByStaff($xoopsUser->getVar('uid'));
            }
            foreach ($depts as $dept) {
                $myDepts[$dept->getVar('id')] = $dept->getVar('department');
            }
            unset($depts);
            asort($myDepts);
            $myDepts[-1] = _XHELP_TEXT_SELECT_ALL;
            $xoopsTpl->assign('xhelp_depts', $myDepts);
            $xoopsTpl->assign('xhelp_batch_form', 'index.php');
            $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);

            $statusHandler = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
            $crit_stat     = new \Criteria('', '');
            $crit_stat->setSort('description');
            $crit_stat->setOrder('ASC');
            $statuses  = $statusHandler->getObjects($crit_stat);
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

            $fields  = $ticketFieldHandler->getObjects();
            $aFields = [];
            foreach ($fields as $field) {
                $values = $field->getVar('fieldvalues');
                if (XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
                    //$values = array(1 => _YES, 0 => _NO);
                    $values = ((1 == $values) ? _YES : _NO);
                }
                $defaultValue = $field->getVar('defaultvalue');

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
            unset($fields);
            $xoopsTpl->assign('xhelp_custFields', $aFields);
            if (!empty($aFields)) {
                $xoopsTpl->assign('xhelp_hasCustFields', true);
            } else {
                $xoopsTpl->assign('xhelp_hasCustFields', false);
            }

            $_xhelpSession->set('xhelp_custFields', $aFields);
            break;
    }

    require_once XOOPS_ROOT_PATH . '/footer.php';
} else {    // If not a user
    redirect_header(XOOPS_URL . '/user.php', 3);
}
