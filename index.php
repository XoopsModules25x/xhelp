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
use XoopsModules\News;
use XoopsModules\Xhelp\{
    Helper,
    Utility,
    WebLib
};

/** @var Helper $helper */
require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
xoops_load('XoopsPagenav');

// Setup event handlers for page

$helper = Helper::getInstance();

//Initialise Necessary Data Handler Classes
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');
/** @var \XoopsMemberHandler $memberHandler */
$memberHandler = xoops_getHandler('member');
/** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
$departmentHandler = $helper->getHandler('Department');
/** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
$membershipHandler = $helper->getHandler('Membership');
/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
/** @var \XoopsModules\Xhelp\TicketListHandler $ticketListHandler */
$ticketListHandler = $helper->getHandler('TicketList');
/** @var \XoopsModules\Xhelp\SavedSearchHandler $savedSearchHandler */
$savedSearchHandler = $helper->getHandler('SavedSearch');

//Determine default 'op' (if none is specified)
$uid = 0;
if ($xoopsUser) {
    $uid = $xoopsUser->getVar('uid');
    if ($xhelp_isStaff) {
        $op = 'staffMain';
    } else {
        $op = 'userMain';
    }
} else {
    $op = 'anonMain';
}

// Page Global Variables
$status_opt   = [_XHELP_TEXT_SELECT_ALL => -1, _XHELP_STATUS0 => 0, _XHELP_STATUS1 => 1, _XHELP_STATUS2 => 2];
$state_opt    = [_XHELP_TEXT_SELECT_ALL => -1, _XHELP_STATE1 => 1, _XHELP_STATE2 => 2];
$sort_columns = [];
$sort_order   = ['ASC', 'DESC'];
$vars         = ['op', 'limit', 'start', 'sort', 'order', 'refresh'];
$all_users    = [];
$refresh      = $start = $limit = 0;
$sort         = '';
$order        = '';

//Initialize Variables
foreach ($vars as $var) {
    if (isset($_REQUEST[$var])) {
        $$var = $_REQUEST[$var];
    }
}

//Ensure Criteria Fields hold valid values
$limit = $limit;
$start = $start;
$sort  = \mb_strtolower($sort);
$order = (in_array(mb_strtoupper($order), $sort_order) ? $order : 'ASC');

$displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

switch ($op) {
    case 'staffMain':
        staffmain_display();
        break;
    case 'staffViewAll':
        staffviewall_display();
        break;
    case 'userMain':
        usermain_display();
        break;
    case 'userViewAll':
        userviewall_display();
        break;
    case 'setdept':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }

        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_EDIT)) {
         $message = _XHELP_MESSAGE_NO_EDIT_TICKET;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('setdept', 'POST')) {
            setdept_action();
        } else {
            setdept_display();
        }
        break;
    case 'setpriority':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }
        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_PRIORITY)) {
         $message = _XHELP_MESSAGE_NO_CHANGE_PRIORITY;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('setpriority', 'POST')) {
            setpriority_action();
        } else {
            setpriority_display();
        }
        break;
    case 'setstatus':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }
        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_STATUS)) {
         $message = _XHELP_MESSAGE_NO_CHANGE_STATUS;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('setstatus', 'POST')) {
            setstatus_action();
        } else {
            setstatus_display();
        }
        break;
    case 'setowner':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }
        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP)) {
         $message = _XHELP_MESSAGE_NO_CHANGE_OWNER;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('setowner', 'POST')) {
            setowner_action();
        } else {
            setowner_display();
        }
        break;
    case 'addresponse':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }
        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD)) {
         $message = _XHELP_MESSAGE_NO_ADD_RESPONSE;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('addresponse', 'POST')) {
            addresponse_action();
        } else {
            addresponse_display();
        }
        break;
    case 'delete':
        if (!$xhelp_isStaff) {
            $helper->redirect(basename(__FILE__), 3, _NOPERM);
        }
        /*
         if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_TICKET_DELETE)) {
         $message = _XHELP_MESSAGE_NO_DELETE_TICKET;
         redirect_header(XHELP_BASE_URL."/".basename(__FILE__), 3, $message);
         }
         */
        if (Request::hasVar('delete', 'POST')) {
            delete_action();
        } else {
            delete_display();
        }
        break;
    case 'anonMain':
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler   = xoops_getHandler('config');
        $xoopsConfigUser = [];
        $criteria        = new \CriteriaCompo(new \Criteria('conf_name', 'allow_register'), 'OR');
        $criteria->add(new \Criteria('conf_name', 'activation_type'), 'OR');
        $myConfigs = $configHandler->getConfigs($criteria);

        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }

        if (0 == $xoopsConfigUser['allow_register']) {
            $helper->redirect('error.php');
        } else {
            $helper->redirect('addTicket.php');
        }
        exit();
        break;
    default:
        $helper->redirect(basename(__FILE__), 3);
        break;
}

/**
 * Assign the selected tickets to the specified department
 */
function setdept_action()
{
    global $eventService, $staff;
    $helper = Helper::getInstance();

    //Sanity Check: tickets and department are supplied
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    if (!isset($_POST['department'])) {
        $helper->redirect(basename(__FILE__), 3, _AM_XHELP_MESSAGE_NO_DEPARTMENT);
    }
    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_TICKET_EDIT, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_EDIT_TICKET);
    }
    $department = Request::getInt('department', 0, 'POST');
    $ret        = Utility::setDept($tickets, $department);
    if ($ret) {
        $eventService->trigger('batch_dept', [@$oTickets, $department]);
        if (count($oTickets) > 0) {
            $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_DEPARTMENT);
        } else {
            $helper->redirect('ticket.php?id=' . $oTickets[0]->getVar('id'), 3, _XHELP_MESSAGE_UPDATE_DEPARTMENT);
        }
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_DEPARTMENT_ERROR);
}

/**
 * Display form for the Batch Action: Set Department
 */
function setdept_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $displayName;
    $helper = Helper::getInstance();

    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_TICKET_EDIT);
    unset($oTickets);

    $tplDepts = [];
    foreach ($depts as $dept) {
        $tplDepts[$dept->getVar('id')] = $dept->getVar('department');
    }
    unset($depts);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_setdept.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                     // Include the page header
    $xoopsTpl->assign('xhelp_department_options', $tplDepts);
    $xoopsTpl->assign('xhelp_tickets', implode(',', $_POST['tickets']));
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_EDIT_TICKET);
    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function setpriority_action()
{
    $helper = Helper::getInstance();
    global $eventService, $staff;
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    if (!isset($_POST['priority'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_PRIORITY);
    }
    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_TICKET_PRIORITY, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_CHANGE_PRIORITY);
    }

    $ret = Utility::setPriority($tickets, $_POST['priority']);
    if ($ret) {
        $eventService->trigger('batch_priority', [@$oTickets, $_POST['priority']]);
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_PRIORITY);
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_PRIORITY_ERROR);
}

function setpriority_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $displayName;
    $helper = Helper::getInstance();
    //Make sure that some tickets were selected
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_TICKET_PRIORITY);
    unset($oTickets);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    //Get Array of priorities/descriptions
    $aPriority = [
        1 => _XHELP_PRIORITY1,
        2 => _XHELP_PRIORITY2,
        3 => _XHELP_PRIORITY3,
        4 => _XHELP_PRIORITY4,
        5 => _XHELP_PRIORITY5,
    ];

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_setpriority.tpl';    // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';
    $xoopsTpl->assign('xhelp_priorities_desc', $aPriority);
    $xoopsTpl->assign('xhelp_priorities', array_keys($aPriority));
    $xoopsTpl->assign('xhelp_priority', 4);
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_tickets', implode(',', $_POST['tickets']));
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_CHANGE_PRIORITY);
    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function setstatus_action()
{
    global $eventService, $staff;
    $helper = Helper::getInstance();
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    if (!isset($_POST['status'])) {
        $helper->redirect(basename(__FILE__), 3, _AM_XHELP_MESSAGE_NO_STATUS);
    }
    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_TICKET_STATUS, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_CHANGE_STATUS);
    }

    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
    $statusHandler = $helper->getHandler('Status');
    $status        = $statusHandler->get($_POST['status']);
    $ret           = Utility::setStatus($tickets, $_POST['status']);
    if ($ret) {
        $eventService->trigger('batch_status', [&$oTickets, &$status]);
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_STATUS);
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_UPDATE_STATUS_ERROR);
}

function setstatus_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $displayName;
    $helper = Helper::getInstance();
    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
    $statusHandler = $helper->getHandler('Status');
    $criteria      = new \Criteria('', '');
    $criteria->setOrder('ASC');
    $criteria->setSort('description');
    $statuses = $statusHandler->getObjects($criteria);

    //Make sure that some tickets were selected
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_TICKET_STATUS);
    unset($oTickets);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    //Get Array of Status/Descriptions
    $aStatus = [];
    foreach ($statuses as $status) {
        $aStatus[$status->getVar('id')] = $status->getVar('description');
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_setstatus.tpl'; // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';
    $xoopsTpl->assign('xhelp_status_options', $aStatus);
    $xoopsTpl->assign('xhelp_tickets', implode(',', $_POST['tickets']));
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_CHANGE_STATUS);
    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function setowner_action()
{
    global $eventService, $staff;
    $helper = Helper::getInstance();
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    if (!isset($_POST['owner'])) {
        $helper->redirect(basename(__FILE__), 3, _AM_XHELP_MESSAGE_NO_OWNER);
    }
    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_TICKET_OWNERSHIP, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_CHANGE_OWNER);
    }
    $ret = Utility::setOwner($tickets, $_POST['owner']);

    if ($ret) {
        $eventService->trigger('batch_owner', [&$oTickets, $_POST['owner']]);
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_ASSIGN_OWNER);
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_ASSIGN_OWNER_ERROR);
}

function setowner_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $displayName;
    $helper = Helper::getInstance();

    //Make sure that some tickets were selected
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_TICKET_OWNERSHIP);
    unset($oTickets);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    $aOwners = [];
    foreach ($users as $uid => $user) {
        $aOwners[$uid] = $uid;
    }
    $criteria = new \Criteria('uid', '(' . implode(',', array_keys($aOwners)) . ')', 'IN');
    $owners   = Utility::getUsers($criteria, $helper->getConfig('xhelp_displayName'));

    $a_users    = [];
    $a_users[0] = _XHELP_NO_OWNER;
    foreach ($owners as $owner_id => $owner_name) {
        $a_users[$owner_id] = $owner_name;
    }
    unset($users, $owners, $aOwners);

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_setowner.tpl'; // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';
    $xoopsTpl->assign('xhelp_staff_ids', $a_users);
    $xoopsTpl->assign('xhelp_tickets', implode(',', $_POST['tickets']));
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_CHANGE_OWNER);
    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function addresponse_action()
{
    global $eventService, $session, $staff;
    $helper = Helper::getInstance();
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    if (!isset($_POST['response'])) {
        $helper->redirect(basename(__FILE__), 3, _AM_XHELP_MESSAGE_NO_RESPONSE);
    }
    $private = isset($_POST['private']);

    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_ADD_RESPONSE);
    }
    $ret = Utility::addResponse($tickets, $_POST['response'], $_POST['timespent'], $private);
    if ($ret) {
        $session->del('xhelp_batch_addresponse');
        $session->del('xhelp_batch_response');
        $session->del('xhelp_batch_timespent');
        $session->del('xhelp_batch_private');

        $eventService->trigger('batch_response', [&$oTickets, &$ret]);
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_ADDRESPONSE);
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_ADDRESPONSE_ERROR);
}

function addresponse_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $session, $displayName;
    $helper = Helper::getInstance();
    /** @var \XoopsModules\Xhelp\ResponseTemplatesHandler $responseTemplatesHandler */
    $responseTemplatesHandler = $helper->getHandler('ResponseTemplates');
    $ticketVar                = 'xhelp_batch_addresponse';
    $tpl                      = 0;
    $uid                      = $xoopsUser->getVar('uid');

    //Make sure that some tickets were selected
    if (isset($_POST['tickets'])) {
        $tickets = $_POST['tickets'];
    } else {
        if (!$tickets = $session->get($ticketVar)) {
            $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
        }
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_RESPONSE_ADD);
    unset($oTickets);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    //Store tickets in session so they won't be in URL
    $session->set($ticketVar, $tickets);

    //Check if a predefined response was selected
    if (Request::hasVar('tpl', 'REQUEST')) {
        $tpl = $_REQUEST['tpl'];
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_batch_response.tpl';
    require_once XOOPS_ROOT_PATH . '/header.php';
    $xoopsTpl->assign('xhelp_tickets', implode(',', $tickets));
    $xoopsTpl->assign('xhelp_formaction', basename(__FILE__));
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_timespent', ($timespent = $session->get('xhelp_batch_timespent')) ? $timespent : '');
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_ADD_RESPONSE);
    $xoopsTpl->assign('xhelp_responseTpl', $tpl);

    //Get all staff defined templates
    $criteria = new \Criteria('uid', $uid);
    $criteria->setSort('name');
    $responseTpl = $responseTemplatesHandler->getObjects($criteria, true);

    //Fill Response Template Array
    $tpls    = [];
    $tpls[0] = '------------------';

    foreach ($responseTpl as $key => $obj) {
        $tpls[$key] = $obj->getVar('name');
    }
    $xoopsTpl->assign('xhelp_responseTpl_options', $tpls);
    //Get response message to display
    if (isset($responseTpl[$tpl])) {    // Display Template Text
        $xoopsTpl->assign('xhelp_response_message', $responseTpl[$tpl]->getVar('response', 'e'));
    } else {
        $response = $session->get('xhelp_batch_response');
        if ($response) {  //Display Saved Text
            $xoopsTpl->assign('xhelp_response_message', $response);
        }
    }

    //Private Message?
    $xoopsTpl->assign('xhelp_private', ($private = $session->get('xhelp_batch_private')) ? $private : false);

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function delete_action()
{
    global $eventService, $staff;
    $helper = Helper::getInstance();
    if (!isset($_POST['tickets'])) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    $tickets  = implode(',', $_POST['tickets']);
    $tickets  = cleanTickets($tickets);
    $oTickets = Utility::getTickets($tickets);

    $depts = [];
    foreach ($oTickets as $ticket) {
        $depts[$ticket->getVar('department')] = $ticket->getVar('department');
    }

    // Check staff permissions
    if (!$staff->checkRoleRights(XHELP_SEC_TICKET_DELETE, $depts)) {
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_NO_DELETE_TICKET);
    }

    $ret = Utility::deleteTickets($tickets);
    if ($ret) {
        $eventService->trigger('batch_delete_ticket', [@$oTickets]);
        $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_DELETE_TICKETS);
    }
    $helper->redirect(basename(__FILE__), 3, _XHELP_MESSAGE_DELETE_TICKETS_ERROR);
}

function delete_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $displayName;
    $helper = Helper::getInstance();
    //Make sure that some tickets were selected
    if (!isset($_POST['tickets'])) {
        $helper->redirect('index.php', 3, _XHELP_MESSAGE_NO_TICKETS);
    }

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    $depts             = $departmentHandler->getObjects(null, true);
    $oTickets          = Utility::getTickets($_POST['tickets']);
    $all_users         = [];
    $j                 = 0;

    $sortedTickets = makeBatchTicketArray($oTickets, $depts, $all_users, $j, XHELP_SEC_TICKET_DELETE);
    unset($oTickets);

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }
    $sortedTickets = updateBatchTicketInfo($sortedTickets, $users, $j);

    $hiddenvars                              = [
        'delete' => _XHELP_BUTTON_SET,        //'tickets' => implode($_POST['tickets'], ','),
        'op'     => 'delete',
    ];
    $aHiddens[]                              = [
        'name'  => 'delete',
        'value' => _XHELP_BUTTON_SET,
    ];
    $aHiddens[]                              = [
        'name'  => 'op',
        'value' => 'delete',
    ];
    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_deletetickets.tpl';
    require_once XOOPS_ROOT_PATH . '/header.php';
    $xoopsTpl->assign('xhelp_message', _XHELP_MESSAGE_TICKET_DELETE_CNFRM);
    $xoopsTpl->assign('xhelp_hiddens', $aHiddens);
    $xoopsTpl->assign('xhelp_goodTickets', $sortedTickets['good']);
    $xoopsTpl->assign('xhelp_badTickets', $sortedTickets['bad']);
    $xoopsTpl->assign('xhelp_hasGoodTickets', count($sortedTickets['good']) > 0);
    $xoopsTpl->assign('xhelp_hasBadTickets', count($sortedTickets['bad']) > 0);
    $xoopsTpl->assign('xhelp_batchErrorMsg', _XHELP_MESSAGE_NO_DELETE_TICKET);
    require_once XOOPS_ROOT_PATH . '/footer.php';
}

/**
 * @param int $topicid
 * @param int $limit
 * @param int $start
 * @return bool
 * @todo make SmartyNewsRenderer class
 */
function getAnnouncements(int $topicid, int $limit = 5, int $start = 0): bool
{
    global $xoopsUser, $xoopsConfig, $xoopsModule, $xoopsTpl;
    /** @var \XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');

    if (0 == $topicid || (!$count = $moduleHandler->getByDirname('news'))) {
        $xoopsTpl->assign('xhelp_useAnnouncements', false);

        return false;
    }
    //    $news_version = round($count->getVar('version') / 100, 2);
    //
    //    switch ($news_version) {
    //        case '1.1':
    //            $sarray = NewsStory::getAllPublished($limit, $start, $topicid);
    //            break;
    //
    //        case '1.21':
    //        default:

    if (!class_exists(News\NewsStory::class)) {
        return false;
    }

    $sarray = News\NewsStory::getAllPublished($limit, $start, false, $topicid);
    //    }

    $scount = count($sarray);
    foreach ($sarray as $iValue) {
        $story           = [];
        $story['id']     = $iValue->storyid();
        $story['poster'] = $iValue->uname();
        if (false !== $story['poster']) {
            $story['poster'] = "<a href='" . XOOPS_URL . '/userinfo.php?uid=' . $iValue->uid() . "'>" . $story['poster'] . '</a>';
        } else {
            $story['poster'] = $xoopsConfig['anonymous'];
        }
        $story['posttime'] = formatTimestamp($iValue->published());
        $story['text']     = $iValue->hometext();
        $introcount        = mb_strlen($story['text']);
        $fullcount         = mb_strlen($iValue->bodytext());
        $totalcount        = $introcount + $fullcount;
        $morelink          = '';
        if ($fullcount > 1) {
            $morelink .= '<a href="' . XOOPS_URL . '/modules/news/article.php?storyid=' . $iValue->storyid() . '';
            $morelink .= '">' . _XHELP_ANNOUNCE_READMORE . '</a> | ';
            //$morelink .= sprintf(_NW_BYTESMORE,$totalcount);
            //$morelink .= ' | ';
        }
        $ccount    = $iValue->comments();
        $morelink  .= '<a href="' . XOOPS_URL . '/modules/news/article.php?storyid=' . $iValue->storyid() . '';
        $morelink2 = '<a href="' . XOOPS_URL . '/modules/news/article.php?storyid=' . $iValue->storyid() . '';
        if (0 == $ccount) {
            $morelink .= '">' . _XHELP_COMMMENTS . '</a>';
        } else {
            if ($fullcount < 1) {
                if (1 == $ccount) {
                    $morelink .= '">' . _XHELP_ANNOUNCE_READMORE . '</a> | ' . $morelink2 . '">' . _XHELP_ANNOUNCE_ONECOMMENT . '</a>';
                } else {
                    $morelink .= '">' . _XHELP_ANNOUNCE_READMORE . '</a> | ' . $morelink2 . '">';
                    $morelink .= sprintf(_XHELP_ANNOUNCE_NUMCOMMENTS, $ccount);
                    $morelink .= '</a>';
                }
            } else {
                if (1 == $ccount) {
                    $morelink .= '">' . _XHELP_ANNOUNCE_ONECOMMENT . '</a>';
                } else {
                    $morelink .= '">';
                    $morelink .= sprintf(_XHELP_ANNOUNCE_NUMCOMMENTS, $ccount);
                    $morelink .= '</a>';
                }
            }
        }
        $story['morelink']  = $morelink;
        $story['adminlink'] = '';
        if ($xoopsUser && $xoopsUser->isAdmin($xoopsModule->mid())) {
            $story['adminlink'] = $iValue->adminlink();
        }
        //$story['mail_link'] = 'mailto:?subject='.sprintf(_NW_INTARTICLE,$xoopsConfig['sitename']).'&amp;body='.sprintf(_NW_INTARTFOUND, $xoopsConfig['sitename']).':  '.XOOPS_URL.'/modules/news/article.php?storyid='.$sarray[$i]->storyid();
        $story['imglink'] = '';
        $story['align']   = '';
        if ($iValue->topicdisplay()) {
            $story['imglink'] = $iValue->imglink();
            $story['align']   = $iValue->topicalign();
        }
        $story['title'] = $iValue->textlink() . '&nbsp;:&nbsp;' . "<a href='" . XOOPS_URL . '/modules/news/article.php?storyid=' . $iValue->storyid() . "'>" . $iValue->title() . '</a>';
        $story['hits']  = $iValue->counter();
        // The line below can be used to display a Permanent Link image
        // $story['title'] .= "&nbsp;&nbsp;<a href='".XOOPS_URL."/modules/news/article.php?storyid=".$sarray[$i]->storyid()."'><img src='".XOOPS_URL."/modules/news/assets/images/x.gif' alt='Permanent Link'></a>";

        $xoopsTpl->append('xhelp_announcements', $story);
        $xoopsTpl->assign('xhelp_useAnnouncements', true);
        unset($story);
    }
    return true;
    //===========================================
}

/**
 * @param string $dept
 * @return string
 */
function getDepartmentName(string $dept): string
{
    //BTW - I don't like that we rely on the global $depts variable to exist.
    // What if we moved this into the DepartmentsHandler class?
    global $depts;
    if (isset($depts[$dept])) {     // Make sure that ticket has a department
        $department = $depts[$dept]->getVar('department');
    } else {    // Else, fill it with 0
        $department = _XHELP_TEXT_NO_DEPT;
    }

    return $department;
}

/**
 * @param string $tickets
 * @return array
 */
function cleanTickets(string $tickets): array
{
    $t_tickets = explode(',', $tickets);
    $ret       = [];
    foreach ($t_tickets as $ticket) {
        $ticket = (int)$ticket;
        if ($ticket) {
            $ret[] = $ticket;
        }
    }
    unset($t_tickets);

    return $ret;
}

function staffmain_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin;
    global $limit, $start, $refresh, $displayName, $xhelp_isStaff, $session, $eventService, $xhelp_module_header, $aSavedSearches;
    $helper = Helper::getInstance();
    if (!$xhelp_isStaff) {
        $helper->redirect(basename(__FILE__), 3, _NOPERM);
    }

    $xhelpConfig = Utility::getModuleConfig();
    //Get Saved Searches for Current User + Searches for every user
    $allSavedSearches = Utility::getSavedSearches([$xoopsUser->getVar('uid'), XHELP_GLOBAL_UID]);

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\TicketListHandler $ticketListHandler */
    $ticketListHandler = $helper->getHandler('TicketList');

    //Set Number of items in each section
    if (0 == $limit) {
        $limit = $xhelpConfig['xhelp_staffTicketCount'];
    } elseif (-1 == $limit) {
        $limit = 0;
    }
    $uid         = $xoopsUser->getVar('uid');
    $depts       = $departmentHandler->getObjects(null, true);
    $priority    = $ticketHandler->getStaffTickets($uid, XHELP_QRY_STAFF_HIGHPRIORITY, $start, $limit);
    $ticketLists = $ticketListHandler->getListsByUser($uid);
    $all_users   = [];

    $tickets = [];
    $i       = 0;
    foreach ($ticketLists as $ticketList) {
        $searchid           = $ticketList->getVar('searchid');
        $criteria           = $allSavedSearches[$searchid]['search'];
        $searchname         = $allSavedSearches[$searchid]['name'];
        $searchOnCustFields = $allSavedSearches[$searchid]['hasCustFields'];
        $criteria->setLimit($limit);
        $newTickets                = $ticketHandler->getObjectsByStaff($criteria, false, $searchOnCustFields);
        $tickets[$i]               = [];
        $tickets[$i]['tickets']    = [];
        $tickets[$i]['searchid']   = $searchid;
        $tickets[$i]['searchname'] = $searchname;
        $tickets[$i]['tableid']    = safeHTMLId($searchname);
        $tickets[$i]['hasTickets'] = count($newTickets) > 0;
        $j                         = 0;
        foreach ($newTickets as $ticket) {
            $dept                                    = @$depts[$ticket->getVar('department')];
            $tickets[$i]['tickets'][$j]              = [
                'id'             => $ticket->getVar('id'),
                'uid'            => $ticket->getVar('uid'),
                'subject'        => xoops_substr($ticket->getVar('subject'), 0, 35),
                'full_subject'   => $ticket->getVar('subject'),
                'description'    => $ticket->getVar('description'),
                'department'     => safeDepartmentName($dept),
                'departmentid'   => $ticket->getVar('department'),
                'departmenturl'  => Utility::createURI('index.php', [
                    'op'   => 'staffViewAll',
                    'dept' => $ticket->getVar('department'),
                ]),
                'priority'       => $ticket->getVar('priority'),
                'status'         => Utility::getStatus($ticket->getVar('status')),
                'posted'         => $ticket->posted(),
                'ownership'      => _XHELP_MESSAGE_NOOWNER,
                'ownerid'        => $ticket->getVar('ownership'),
                'closedBy'       => $ticket->getVar('closedBy'),
                'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                'uname'          => '',
                'userinfo'       => XHELP_SITE_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                'ownerinfo'      => '',
                'url'            => XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id'),
                'overdue'        => $ticket->isOverdue(),
            ];
            $all_users[$ticket->getVar('uid')]       = '';
            $all_users[$ticket->getVar('ownership')] = '';
            $all_users[$ticket->getVar('closedBy')]  = '';
            ++$j;
        }
        ++$i;
        unset($newTickets);
    }

    //Retrieve all member information for the current page
    if (count($all_users)) {
        $criteria = new \Criteria('uid', '(' . implode(',', array_keys($all_users)) . ')', 'IN');
        $users    = Utility::getUsers($criteria, $displayName);
    } else {
        $users = [];
    }

    //Update tickets with user information
    foreach ($tickets as $i => $iValue) {
        foreach ($iValue['tickets'] as $j => $jValue) {
            if (isset($users[$tickets[$i]['tickets'][$j]['uid']])) {
                $tickets[$i]['tickets'][$j]['uname'] = $users[$tickets[$i]['tickets'][$j]['uid']];
            } else {
                $tickets[$i]['tickets'][$j]['uname'] = $xoopsConfig['anonymous'];
            }
            if ($tickets[$i]['tickets'][$j]['ownerid']) {
                if (isset($users[$tickets[$i]['tickets'][$j]['ownerid']])) {
                    $tickets[$i]['tickets'][$j]['ownership'] = $users[$tickets[$i]['tickets'][$j]['ownerid']];
                    $tickets[$i]['tickets'][$j]['ownerinfo'] = XOOPS_URL . '/userinfo.php?uid=' . $tickets[$i]['tickets'][$j]['ownerid'];
                }
            }
        }
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staff_index.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                         // Include the page header
    if ($refresh > 0) {
        $xhelp_module_header .= "<meta http-equiv=\"Refresh\" content=\"$refresh;url=" . XOOPS_URL . "/modules/xhelp/index.php?refresh=$refresh\">";
    }
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_ticketLists', $tickets);
    $xoopsTpl->assign('xhelp_hasTicketLists', count($tickets) > 0);
    $xoopsTpl->assign('xhelp_refresh', $refresh);
    $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_uid', $xoopsUser->getVar('uid'));
    $xoopsTpl->assign('xhelp_current_file', basename(__FILE__));
    $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);
    $xoopsTpl->assign('xhelp_allSavedSearches', $allSavedSearches);

    getAnnouncements((int)$xhelpConfig['xhelp_announcements']);

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

/**
 * @param string $orig_text
 * @return array|string|string[]|null
 */
function safeHTMLId(string $orig_text)
{
    //Only allow alphanumeric characters
    $match   = ['/[^a-zA-Z0-9]]/', '/\s/'];
    $replace = ['', ''];

    $htmlID = preg_replace($match, $replace, $orig_text);

    return $htmlID;
}

/**
 * @param \XoopsModules\Xhelp\Department $deptObj
 * @return string
 */
function safeDepartmentName(\XoopsModules\Xhelp\Department $deptObj): string
{
    if (is_object($deptObj)) {
        $department = $deptObj->getVar('department');
    } else {    // Else, fill it with 0
        $department = _XHELP_TEXT_NO_DEPT;
    }

    return $department;
}

function staffviewall_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin;
    global $xhelp_isStaff, $sort_order, $start, $limit, $xhelp_module_header, $state_opt, $aSavedSearches;
    $helper = Helper::getInstance();
    if (!$xhelp_isStaff) {
        $helper->redirect(basename(__FILE__), 3, _NOPERM);
    }

    //Sanity Check: sort / order column valid
    $sort  = @$_REQUEST['sort'];
    $order = @$_REQUEST['order'];

    $sort_columns = [
        'id'         => 'DESC',
        'priority'   => 'DESC',
        'elapsed'    => 'ASC',
        'lastupdate' => 'ASC',
        'status'     => 'ASC',
        'subject'    => 'ASC',
        'department' => 'ASC',
        'ownership'  => 'ASC',
        'uid'        => 'ASC',
    ];
    $sort         = array_key_exists(mb_strtolower((string)$sort), $sort_columns) ? $sort : 'id';
    $order        = (in_array(mb_strtoupper((string)$order), $sort_order) ? $order : $sort_columns[$sort]);

    $uid       = $xoopsUser->getVar('uid');
    $dept      = Request::getInt('dept', 0);
    $status    = Request::getInt('status', -1);
    $ownership = Request::getInt('ownership', -1);
    $state     = Request::getInt('state', -1);

    $xhelpConfig = Utility::getModuleConfig();
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');

    if (0 == $limit) {
        $limit = $xhelpConfig['xhelp_staffTicketCount'];
    } elseif (-1 == $limit) {
        $limit = 0;
    }

    //Prepare Database Query and Querystring
    $criteria = new \CriteriaCompo(new \Criteria('uid', $uid, '=', 'j'));
    $qs       = [
        'op'    => 'staffViewAll', //Common Query String Values
        'start' => $start,
        'limit' => $limit,
    ];

    if ($dept) {
        $qs['dept'] = $dept;
        $criteria->add(new \Criteria('department', $dept, '=', 't'));
    }
    if (-1 != $status) {
        $qs['status'] = $status;
        $criteria->add(new \Criteria('status', $status, '=', 't'));
    }
    if (-1 != $ownership) {
        $qs['ownership'] = $ownership;
        $criteria->add(new \Criteria('ownership', $ownership, '=', 't'));
    }

    if (-1 != $state) {
        $qs['state'] = $state;
        $criteria->add(new \Criteria('state', $state, '=', 's'));
    }

    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $criteria->setSort($sort);
    $criteria->setOrder($order);

    //Setup Column Sorting Vars
    $tpl_cols = [];
    foreach ($sort_columns as $col => $initsort) {
        $col_qs = ['sort' => $col];
        //Check if we need to sort by current column
        if ($sort == $col) {
            $col_qs['order'] = ($order == $sort_order[0] ? $sort_order[1] : $sort_order[0]);
            $col_sortby      = true;
        } else {
            $col_qs['order'] = $initsort;
            $col_sortby      = false;
        }
        $tpl_cols[$col] = [
            'url'      => Utility::createURI(basename(__FILE__), array_merge($qs, $col_qs)),
            'urltitle' => _XHELP_TEXT_SORT_TICKETS,
            'sortby'   => $col_sortby,
            'sortdir'  => \mb_strtolower($col_qs['order']),
        ];
    }

    $allTickets = $ticketHandler->getObjectsByStaff($criteria, true);
    $count      = $ticketHandler->getCountByStaff($criteria);
    $nav        = new \XoopsPageNav($count, $limit, $start, 'start', "op=staffViewAll&amp;limit=$limit&amp;sort=$sort&amp;order=$order&amp;dept=$dept&amp;status=$status&amp;ownership=$ownership");
    $tickets    = [];
    $allUsers   = [];
    $depts      = &$membershipHandler->membershipByStaff($xoopsUser->getVar('uid'), true);    //All Departments for Staff Member

    foreach ($allTickets as $ticket) {
        $deptid                                 = $ticket->getVar('department');
        $tickets[]                              = [
            'id'             => $ticket->getVar('id'),
            'uid'            => $ticket->getVar('uid'),
            'subject'        => xoops_substr($ticket->getVar('subject'), 0, 35),
            'full_subject'   => $ticket->getVar('subject'),
            'description'    => $ticket->getVar('description'),
            'department'     => safeDepartmentName($depts[$deptid]),
            'departmentid'   => $deptid,
            'departmenturl'  => Utility::createURI('index.php', ['op' => 'staffViewAll', 'dept' => $deptid]),
            'priority'       => $ticket->getVar('priority'),
            'status'         => Utility::getStatus($ticket->getVar('status')),
            'posted'         => $ticket->posted(),
            'ownership'      => _XHELP_MESSAGE_NOOWNER,
            'ownerid'        => $ticket->getVar('ownership'),
            'closedBy'       => $ticket->getVar('closedBy'),
            'closedByUname'  => '',
            'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
            'uname'          => '',
            'userinfo'       => XHELP_SITE_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
            'ownerinfo'      => '',
            'url'            => XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id'),
            'elapsed'        => $ticket->elapsed(),
            'lastUpdate'     => $ticket->lastUpdate(),
            'overdue'        => $ticket->isOverdue(),
        ];
        $allUsers[$ticket->getVar('uid')]       = '';
        $allUsers[$ticket->getVar('ownership')] = '';
        $allUsers[$ticket->getVar('closedBy')]  = '';
    }
    $has_allTickets = count($allTickets) > 0;
    unset($allTickets);

    //Get all member information needed on this page
    $criteria = new \Criteria('uid', '(' . implode(',', array_keys($allUsers)) . ')', 'IN');
    $users    = Utility::getUsers($criteria, $xhelpConfig['xhelp_displayName']);
    unset($allUsers);

    $staff_opt = Utility::getStaff($xhelpConfig['xhelp_displayName']);

    foreach ($tickets as $i => $iValue) {
        if (isset($users[$iValue['uid']])) {
            $tickets[$i]['uname'] = $users[$iValue['uid']];
        } else {
            $tickets[$i]['uname'] = $xoopsConfig['anonymous'];
        }
        if ($tickets[$i]['ownerid']) {
            if (isset($users[$tickets[$i]['ownerid']])) {
                $tickets[$i]['ownership'] = $users[$tickets[$i]['ownerid']];
                $tickets[$i]['ownerinfo'] = XHELP_SITE_URL . '/userinfo.php?uid=' . $tickets[$i]['ownerid'];
            }
        }
        if ($tickets[$i]['closedBy']) {
            if (isset($users[$tickets[$i]['closedBy']])) {
                $tickets[$i]['closedByUname'] = $users[$tickets[$i]['closedBy']];
            }
        }
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_staff_viewall.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                           // Include the page header

    $javascript = '<script type="text/javascript" src="' . XHELP_BASE_URL . "/include/functions.js\"></script>
//<script type=\"text/javascript\" src='" . XHELP_SCRIPT_URL . "/ChangeSelectedState.php?client'></script>
<script type=\"text/javascript\">
<!--
function states_onchange()
{
    state = xoopsGetElementById('state');
    var sH = new Xhelp\ChangeSelectedState();
    sH.statusesByState(state.value);
}

var stateHandler = {
    statusesByState: function(result){
        var statuses = gE('status');
        xhelpFillSelect(statuses, result);
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('state'), 'change', states_onchange, true);
}

window.setTimeout('window_onload()', 1500);
//-->
</script>";

    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_cols', $tpl_cols);
    $xoopsTpl->assign('xhelp_allTickets', $tickets);
    $xoopsTpl->assign('xhelp_has_tickets', $has_allTickets);
    $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
    $xoopsTpl->assign('xoops_module_header', $javascript . $xhelp_module_header);
    $xoopsTpl->assign('xhelp_priorities_desc', [
        5 => _XHELP_PRIORITY5,
        4 => _XHELP_PRIORITY4,
        3 => _XHELP_PRIORITY3,
        2 => _XHELP_PRIORITY2,
        1 => _XHELP_PRIORITY1,
    ]);
    if (0 != $limit) {
        $xoopsTpl->assign('xhelp_pagenav', $nav->renderNav());
    }
    $xoopsTpl->assign('xhelp_limit_options', [-1 => _XHELP_TEXT_SELECT_ALL, 10 => '10', 15 => '15', 20 => '20', 30 => '30']);
    $xoopsTpl->assign('xhelp_filter', [
        'department' => $dept,
        'status'     => $status,
        'state'      => $state,
        'ownership'  => $ownership,
        'limit'      => $limit,
        'start'      => $start,
        'sort'       => $sort,
        'order'      => $order,
    ]);

    $xoopsTpl->append('xhelp_department_values', 0);
    $xoopsTpl->append('xhelp_department_options', _XHELP_TEXT_SELECT_ALL);

    if (1 == $xhelpConfig['xhelp_deptVisibility']) {    // Apply dept visibility to staff members?
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $helper->getHandler('Membership');
        $depts             = $membershipHandler->getVisibleDepartments($xoopsUser->getVar('uid'));
    }

    foreach ($depts as $xhelp_id => $obj) {
        $xoopsTpl->append('xhelp_department_values', $xhelp_id);
        $xoopsTpl->append('xhelp_department_options', $obj->getVar('department'));
    }

    $xoopsTpl->assign('xhelp_ownership_options', array_values($staff_opt));
    $xoopsTpl->assign('xhelp_ownership_values', array_keys($staff_opt));
    $xoopsTpl->assign('xhelp_state_options', array_keys($state_opt));
    $xoopsTpl->assign('xhelp_state_values', array_values($state_opt));
    $xoopsTpl->assign('xhelp_savedSearches', $aSavedSearches);

    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
    $statusHandler = $helper->getHandler('Status');
    $criteria      = new \Criteria('', '');
    $criteria->setSort('description');
    $criteria->setOrder('ASC');
    $statuses = $statusHandler->getObjects($criteria);

    $xoopsTpl->append('xhelp_status_options', _XHELP_TEXT_SELECT_ALL);
    $xoopsTpl->append('xhelp_status_values', -1);
    foreach ($statuses as $status) {
        $xoopsTpl->append('xhelp_status_options', $status->getVar('description'));
        $xoopsTpl->append('xhelp_status_values', $status->getVar('id'));
    }

    $xoopsTpl->assign('xhelp_department_current', $dept);
    $xoopsTpl->assign('xhelp_status_current', $status);
    $xoopsTpl->assign('xhelp_current_file', basename(__FILE__));
    $xoopsTpl->assign('xhelp_text_allTickets', _XHELP_TEXT_ALL_TICKETS);

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

function usermain_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin;
    global $xhelp_module_header;
    $helper                                  = Helper::getInstance();
    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_user_index.tpl';    // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                         // Include the page header

    $xhelpConfig = Utility::getModuleConfig();
    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
    $staffHandler = $helper->getHandler('Staff');

    $staffCount = $staffHandler->getObjects();
    if (0 == count($staffCount)) {
        $xoopsTpl->assign('xhelp_noStaff', true);
    }
    /**
     * @todo remove calls to these three classes and use the ones in beginning
     */
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');

    $userTickets = $ticketHandler->getMyUnresolvedTickets($xoopsUser->getVar('uid'), true);

    foreach ($userTickets as $ticket) {
        $aUserTickets[] = [
            'id'       => $ticket->getVar('id'),
            'uid'      => $ticket->getVar('uid'),
            'subject'  => $ticket->getVar('subject'),
            'status'   => Utility::getStatus($ticket->getVar('status')),
            'priority' => $ticket->getVar('priority'),
            'posted'   => $ticket->posted(),
        ];
    }
    $has_userTickets = count($userTickets) > 0;
    if ($has_userTickets) {
        $xoopsTpl->assign('xhelp_userTickets', $aUserTickets);
    } else {
        $xoopsTpl->assign('xhelp_userTickets', 0);
    }
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_has_userTickets', $has_userTickets);
    $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
    $xoopsTpl->assign('xhelp_priorities_desc', [
        5 => _XHELP_PRIORITY5,
        4 => _XHELP_PRIORITY4,
        3 => _XHELP_PRIORITY3,
        2 => _XHELP_PRIORITY2,
        1 => _XHELP_PRIORITY1,
    ]);
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);

    getAnnouncements((int)$xhelpConfig['xhelp_announcements']);

    require_once XOOPS_ROOT_PATH . '/footer.php';                     //Include the page footer
}

function userviewall_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin;
    global $xhelp_module_header, $sort, $order, $sort_order, $limit, $start, $state_opt, $state;

    $helper = Helper::getInstance();

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_user_viewall.tpl';    // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                           // Include the page header

    //Sanity Check: sort column valid
    $sort_columns = [
        'id'         => 'DESC',
        'priority'   => 'DESC',
        'elapsed'    => 'ASC',
        'lastupdate' => 'ASC',
        'status'     => 'ASC',
        'subject'    => 'ASC',
        'department' => 'ASC',
        'ownership'  => 'ASC',
        'uid'        => 'ASC',
    ];
    $sort         = array_key_exists($sort, $sort_columns) ? $sort : 'id';
    $order        = @$_REQUEST['order'];
    $order        = (in_array(mb_strtoupper($order ?? ''), $sort_order) ? $order : $sort_columns[$sort]);
    $uid          = !empty($xoopsUser) ? $xoopsUser->getVar('uid') : 0;

    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
    $staffHandler = $helper->getHandler('Staff');

    $dept   = Request::getInt('dept', 0);
    $status = Request::getInt('status', -1);
    $state  = Request::getInt('state', -1);

    $depts = $departmentHandler->getObjects(null, true);

    if (0 == $limit) {
        $limit = 10;
    } elseif (-1 == $limit) {
        $limit = 0;
    }

    //Prepare Database Query and Querystring
    $criteria = new \CriteriaCompo(new \Criteria('uid', $uid));
    $qs       = [
        'op'    => 'userViewAll', //Common Query String Values
        'start' => $start,
        'limit' => $limit,
    ];

    if ($dept) {
        $qs['dept'] = $dept;
        $criteria->add(new \Criteria('department', $dept, '=', 't'));
    }
    if (-1 != $status) {
        $qs['status'] = $status;
        $criteria->add(new \Criteria('status', $status, '=', 't'));
    }

    if (-1 != $state) {
        $qs['state'] = $state;
        $criteria->add(new \Criteria('state', $state, '=', 's'));
    }

    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $criteria->setSort($sort);
    $criteria->setOrder($order);

    //Setup Column Sorting Vars
    $tpl_cols = [];
    foreach ($sort_columns as $col => $initsort) {
        $col_qs = ['sort' => $col];
        //Check if we need to sort by current column
        if ($sort == $col) {
            $col_qs['order'] = ($order == $sort_order[0] ? $sort_order[1] : $sort_order[0]);
            $col_sortby      = true;
        } else {
            $col_qs['order'] = $initsort;
            $col_sortby      = false;
        }
        $tpl_cols[$col] = [
            'url'      => Utility::createURI(basename(__FILE__), array_merge($qs, $col_qs)),
            'urltitle' => _XHELP_TEXT_SORT_TICKETS,
            'sortby'   => $col_sortby,
            'sortdir'  => \mb_strtolower($col_qs['order']),
        ];
    }

    $xoopsTpl->assign('xhelp_cols', $tpl_cols);
    $staffCount = $staffHandler->getObjects();
    if (0 == count($staffCount)) {
        $xoopsTpl->assign('xhelp_noStaff', true);
    }

    $userTickets = $ticketHandler->getObjects($criteria);
    foreach ($userTickets as $ticket) {
        $aUserTickets[] = [
            'id'            => $ticket->getVar('id'),
            'uid'           => $ticket->getVar('uid'),
            'subject'       => xoops_substr($ticket->getVar('subject'), 0, 35),
            'full_subject'  => $ticket->getVar('subject'),
            'status'        => Utility::getStatus($ticket->getVar('status')),
            'department'    => safeDepartmentName($depts[$ticket->getVar('department')]),
            'departmentid'  => $ticket->getVar('department'),
            'departmenturl' => Utility::createURI(basename(__FILE__), ['op' => 'userViewAll', 'dept' => $ticket->getVar('department')]),
            'priority'      => $ticket->getVar('priority'),
            'posted'        => $ticket->posted(),
            'elapsed'       => $ticket->elapsed(),
        ];
    }
    $has_userTickets = count($userTickets) > 0;
    if ($has_userTickets) {
        $xoopsTpl->assign('xhelp_userTickets', $aUserTickets);
    } else {
        $xoopsTpl->assign('xhelp_userTickets', 0);
    }

    $javascript = '<script type="text/javascript" src="' . XHELP_BASE_URL . "/include/functions.js\"></script>
<script type=\"text/javascript\" src='" . XHELP_SCRIPT_URL . "/ChangeSelectedState.php?client'></script>
<script type=\"text/javascript\">
<!--
function states_onchange()
{
    state = xoopsGetElementById('state');
    var sH = new Xhelp\WebLib(stateHandler);
    sH.statusesByState(state.value);
}

var stateHandler = {
    statusesByState: function(result){
        var statuses = gE('status');
        xhelpFillSelect(statuses, result);
    }
}

function window_onload()
{
    xhelpDOMAddEvent(xoopsGetElementById('state'), 'change', states_onchange, true);
}

window.setTimeout('window_onload()', 1500);
//-->
</script>";

    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_has_userTickets', $has_userTickets);
    $xoopsTpl->assign('xhelp_viewAll', true);
    $xoopsTpl->assign('xhelp_priorities', [5, 4, 3, 2, 1]);
    $xoopsTpl->assign('xhelp_priorities_desc', [
        5 => _XHELP_PRIORITY5,
        4 => _XHELP_PRIORITY4,
        3 => _XHELP_PRIORITY3,
        2 => _XHELP_PRIORITY2,
        1 => _XHELP_PRIORITY1,
    ]);
    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xoops_module_header', $javascript . $xhelp_module_header);
    $xoopsTpl->assign('xhelp_limit_options', [-1 => _XHELP_TEXT_SELECT_ALL, 10 => '10', 15 => '15', 20 => '20', 30 => '30']);
    $xoopsTpl->assign('xhelp_filter', [
        'department' => $dept,
        'status'     => $status,
        'limit'      => $limit,
        'start'      => $start,
        'sort'       => $sort,
        'order'      => $order,
        'state'      => $state,
    ]);
    $xoopsTpl->append('xhelp_department_values', 0);
    $xoopsTpl->append('xhelp_department_options', _XHELP_TEXT_SELECT_ALL);

    //$depts = getVisibleDepartments($depts);
    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');
    $depts             = $membershipHandler->getVisibleDepartments($xoopsUser->getVar('uid'));
    foreach ($depts as $xhelp_id => $obj) {
        $xoopsTpl->append('xhelp_department_values', $xhelp_id);
        $xoopsTpl->append('xhelp_department_options', $obj->getVar('department'));
    }

    /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
    $statusHandler = $helper->getHandler('Status');
    $criteria      = new \Criteria('', '');
    $criteria->setSort('description');
    $criteria->setOrder('ASC');
    $statuses = $statusHandler->getObjects($criteria);

    $xoopsTpl->append('xhelp_status_options', _XHELP_TEXT_SELECT_ALL);
    $xoopsTpl->append('xhelp_status_values', -1);
    foreach ($statuses as $status) {
        $xoopsTpl->append('xhelp_status_options', $status->getVar('description'));
        $xoopsTpl->append('xhelp_status_values', $status->getVar('id'));
    }

    $xoopsTpl->assign('xhelp_department_current', $dept);
    $xoopsTpl->assign('xhelp_status_current', $status);
    $xoopsTpl->assign('xhelp_state_options', array_keys($state_opt));
    $xoopsTpl->assign('xhelp_state_values', array_values($state_opt));

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

/**
 * @param array $oTickets
 * @param array $depts
 * @param array $all_users
 * @param int   $j
 * @param int   $task
 * @return array
 */
function makeBatchTicketArray(array $oTickets, array $depts, array &$all_users, int &$j, int $task): array
{
    global $staff;

    $sortedTickets['good'] = [];
    $sortedTickets['bad']  = [];
    foreach ($oTickets as $ticket) {
        $dept = @$depts[$ticket->getVar('department')];
        if ($hasRights = $staff->checkRoleRights($task, $ticket->getVar('department'))) {
            $sortedTickets['good'][] = [
                'id'             => $ticket->getVar('id'),
                'uid'            => $ticket->getVar('uid'),
                'subject'        => xoops_substr($ticket->getVar('subject'), 0, 35),
                'full_subject'   => $ticket->getVar('subject'),
                'description'    => $ticket->getVar('description'),
                'department'     => safeDepartmentName($dept),
                'departmentid'   => $ticket->getVar('department'),
                'departmenturl'  => Utility::createURI('index.php', [
                    'op'   => 'staffViewAll',
                    'dept' => $ticket->getVar('department'),
                ]),
                'priority'       => $ticket->getVar('priority'),
                'status'         => Utility::getStatus($ticket->getVar('status')),
                'posted'         => $ticket->posted(),
                'ownership'      => _XHELP_MESSAGE_NOOWNER,
                'ownerid'        => $ticket->getVar('ownership'),
                'closedBy'       => $ticket->getVar('closedBy'),
                'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                'uname'          => '',
                'userinfo'       => XHELP_SITE_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                'ownerinfo'      => '',
                'url'            => XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id'),
                'overdue'        => $ticket->isOverdue(),
            ];
        } else {
            $sortedTickets['bad'][] = [
                'id'             => $ticket->getVar('id'),
                'uid'            => $ticket->getVar('uid'),
                'subject'        => xoops_substr($ticket->getVar('subject'), 0, 35),
                'full_subject'   => $ticket->getVar('subject'),
                'description'    => $ticket->getVar('description'),
                'department'     => safeDepartmentName($dept),
                'departmentid'   => $ticket->getVar('department'),
                'departmenturl'  => Utility::createURI('index.php', [
                    'op'   => 'staffViewAll',
                    'dept' => $ticket->getVar('department'),
                ]),
                'priority'       => $ticket->getVar('priority'),
                'status'         => Utility::getStatus($ticket->getVar('status')),
                'posted'         => $ticket->posted(),
                'ownership'      => _XHELP_MESSAGE_NOOWNER,
                'ownerid'        => $ticket->getVar('ownership'),
                'closedBy'       => $ticket->getVar('closedBy'),
                'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                'uname'          => '',
                'userinfo'       => XHELP_SITE_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                'ownerinfo'      => '',
                'url'            => XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id'),
                'overdue'        => $ticket->isOverdue(),
            ];
        }
        $all_users[$ticket->getVar('uid')]       = '';
        $all_users[$ticket->getVar('ownership')] = '';
        $all_users[$ticket->getVar('closedBy')]  = '';
        ++$j;
    }

    return $sortedTickets;
}

/**
 * @param array $sortedTickets
 * @param array $users
 * @param int   $j
 * @return array
 */
function updateBatchTicketInfo(array &$sortedTickets, array $users, int &$j): array
{
    global $xoopsConfig;

    //Update tickets with user information
    $aTicketTypes = ['good', 'bad'];
    foreach ($aTicketTypes as $ticketType) {
        foreach ($sortedTickets[$ticketType] as $j => $jValue) {
            if (isset($users[$sortedTickets[$ticketType][$j]['uid']])) {
                $sortedTickets[$ticketType][$j]['uname'] = $users[$sortedTickets[$ticketType][$j]['uid']];
            } else {
                $sortedTickets[$ticketType][$j]['uname'] = $xoopsConfig['anonymous'];
            }
            if ($sortedTickets[$ticketType][$j]['ownerid']) {
                if (isset($users[$sortedTickets[$ticketType][$j]['ownerid']])) {
                    $sortedTickets[$ticketType][$j]['ownership'] = $users[$sortedTickets[$ticketType][$j]['ownerid']];
                    $sortedTickets[$ticketType][$j]['ownerinfo'] = XOOPS_URL . '/userinfo.php?uid=' . $sortedTickets[$ticketType][$j]['ownerid'];
                }
            }
        }
    }

    return $sortedTickets;
}
