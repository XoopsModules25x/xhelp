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

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

$helper = Xhelp\Helper::getInstance();
//require_once XHELP_BASE_PATH . '/functions.php';
// require_once XHELP_CLASS_PATH . '/session.php';
$helper->loadLanguage('main');

/**
 * @param array $options
 * @return array|false
 */
function b_xhelp_open_show(array $options)
{
    global $xoopsUser;
    if (!class_exists(Xhelp\Helper::class)) {
        return false;
    }
    $helper = Xhelp\Helper::getInstance();

    $max_char_in_title = $options[0];
    $block             = [];

    if ($xoopsUser) {
        $uid = $xoopsUser->getVar('uid');   // Get uid
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');  // Get ticket handler
        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');
        $isStaff      = $staffHandler->isStaff($xoopsUser->getVar('uid'));
        if ($isStaff) {
            $criteria = new \CriteriaCompo(new \Criteria('ownership', $uid));
            $criteria->add(new \Criteria('status', '2', '<'));
            $criteria->setOrder('DESC');
            $criteria->setSort('priority, posted');
            $criteria->setLimit(5);
            $tickets = $ticketHandler->getObjects($criteria);

            foreach ($tickets as $ticket) {
                $overdue = false;
                if ($ticket->isOverdue()) {
                    $overdue = true;
                }
                $block['ticket'][] = [
                    'id'             => $ticket->getVar('id'),
                    'uid'            => $ticket->getVar('uid'),
                    'subject'        => $ticket->getVar('subject'),
                    'truncSubject'   => xoops_substr($ticket->getVar('subject'), 0, $max_char_in_title),
                    'description'    => $ticket->getVar('description'),
                    //'department'=>$department->getVar('department'),
                    'priority'       => $ticket->getVar('priority'),
                    'status'         => $ticket->getVar('status'),
                    'posted'         => $ticket->posted(),
                    //'ownership'=>$owner->getVar('uname'),
                    'closedBy'       => $ticket->getVar('closedBy'),
                    'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                    //'uname'=>$user->getVar('uname'),
                    'userinfo'       => XOOPS_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                    //'ownerinfo'=>XOOPS_URL . '/userinfo.php?uid=' . $ticket->getVar('ownership'),
                    'url'            => XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'),
                    'overdue'        => $overdue,
                ];
            }

            $block['isStaff']      = true;
            $block['viewAll']      = XOOPS_URL . '/modules/xhelp/index.php?op=staffViewAll';
            $block['viewAllText']  = _MB_XHELP_TEXT_VIEW_ALL_OPEN;
            $block['priorityText'] = _MB_XHELP_TEXT_PRIORITY;
            $block['noTickets']    = _MB_XHELP_TEXT_NO_TICKETS;
        } else {
            $criteria = new \CriteriaCompo(new \Criteria('uid', $uid));
            $criteria->add(new \Criteria('status', '2', '<'));
            $criteria->setOrder('DESC');
            $criteria->setSort('priority, posted');
            $criteria->setLimit(5);
            $tickets = $ticketHandler->getObjects($criteria);
            /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
            $departmentHandler = $helper->getHandler('Department');

            foreach ($tickets as $ticket) {
                //$department = $departmentHandler->get($ticket->getVar('department'));
                $block['ticket'][] = [
                    'id'             => $ticket->getVar('id'),
                    'uid'            => $ticket->getVar('uid'),
                    'subject'        => $ticket->getVar('subject'),
                    'truncSubject'   => xoops_substr($ticket->getVar('subject'), 0, $max_char_in_title),
                    'description'    => $ticket->getVar('description'),
                    //'department'=>($department->getVar('department'),
                    'priority'       => $ticket->getVar('priority'),
                    'status'         => $ticket->getVar('status'),
                    'posted'         => $ticket->posted(),
                    //'ownership'=>$owner->getVar('uname'),
                    'closedBy'       => $ticket->getVar('closedBy'),
                    'totalTimeSpent' => $ticket->getVar('totalTimeSpent'),
                    //'uname'=>$user->getVar('uname'),
                    'userinfo'       => XOOPS_URL . '/userinfo.php?uid=' . $ticket->getVar('uid'),
                    //'ownerinfo'=>XOOPS_URL . '/userinfo.php?uid=' . $ticket->getVar('ownership'),
                    'url'            => XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'),
                ];
            }
        }
        $block['numTickets'] = count($tickets);
        $block['noTickets']  = _MB_XHELP_TEXT_NO_TICKETS;
        unset($tickets);
        $block['picPath'] = XOOPS_URL . '/modules/xhelp/assets/images/';
    }
    return $block;
}

/**
 * @param array $options
 * @return array|bool
 */
function b_xhelp_performance_show(array $options)
{
    global $xoopsUser, $xoopsDB;
    if (!class_exists(Xhelp\Helper::class)) {
        return false;
    }
    $helper  = Xhelp\Helper::getInstance();
    $dirname = $helper->getDirname();
    $block   = [];

    if (!$xoopsUser) {
        return false;
    }

    //Determine if the GD library is installed
    $block['use_img'] = function_exists('imagecreatefrompng');

    $xoopsModule = Xhelp\Utility::getModule();

    if ($xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
        $sql = sprintf(
            'SELECT COUNT(*) AS TicketCount, d.department, d.id FROM `%s` t INNER JOIN %s d ON t.department = d.id  INNER JOIN %s s ON t.status = s.id WHERE s.state = 1 GROUP BY d.department, d.id ORDER BY d.department',
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_status')
        );
    } else {
        $sql = sprintf(
            'SELECT COUNT(*) AS TicketCount, d.department, d.id FROM `%s` t INNER JOIN %s j ON t.department = j.department INNER JOIN %s d ON t.department = d.id INNER JOIN %s s ON t.status = s.id WHERE s.state = 1 AND j.uid = %u GROUP BY d.department, d.id',
            $xoopsDB->prefix('xhelp_tickets'),
            $xoopsDB->prefix('xhelp_jstaffdept'),
            $xoopsDB->prefix('xhelp_departments'),
            $xoopsDB->prefix('xhelp_status'),
            $xoopsUser->getVar('uid')
        );
    }

    $ret = $xoopsDB->query($sql);

    $depts    = [];
    $max_open = 0;
    while (false !== ($myrow = $xoopsDB->fetchArray($ret))) {
        $max_open = max($max_open, $myrow['TicketCount']);
        $url      = Xhelp\Utility::createURI(XHELP_BASE_URL . '/index.php', ['op' => 'staffViewAll', 'dept' => $myrow['id'], 'state' => 1]);
        $depts[]  = [
            'id'      => $myrow['id'],
            'tickets' => $myrow['TicketCount'],
            'name'    => $myrow['department'],
            'url'     => $url,
        ];
    }

    if (0 == count($depts)) {
        return false;
    }

    if ($block['use_img']) {
        //Retrieve the image path for each department
        foreach ($depts as $i => $iValue) {
            $depts[$i]['img'] = getDeptImg($iValue['id'], (int)$iValue['tickets'], (int)$max_open, $i);
        }
    }

    $block['departments'] = $depts;

    return $block;
}

/**
 * @param int|string $dept
 * @param int        $tickets
 * @param int        $max
 * @param int        $counter
 * @return string
 */
function getDeptImg($dept, int $tickets, int $max, int $counter = 0): string
{
    $dept    = (int)$dept;
    $tickets = $tickets;
    $max     = $max;
    $counter = $counter;

    $width = 60;   //Width of resulting image

    $cachedir_local = XHELP_CACHE_PATH . '/';
    $cachedir_www   = XHELP_CACHE_URL . '/';
    $imgdir         = XHELP_IMAGE_PATH . '/';
    $filename       = "xhelp_perf_$dept.png";

    $colors = ['green', 'orange', 'red', 'blue'];

    if (!is_file($cachedir_local . $filename)) {
        //Generate Progress Image
        $cur_color  = $colors[$counter % count($colors)];
        $bg         = @imagecreatefrompng($imgdir . 'dept-bg.png');
        $fill       = @imagecreatefrompng($imgdir . "dept-$cur_color.png");
        $bg_cap     = @imagecreatefrompng($imgdir . 'dept-bg-cap.png');
        $fill_cap   = @imagecreatefrompng($imgdir . 'dept-fill-cap.png');
        $fill_width = round((($width - imagesx($bg_cap)) * $tickets) / $max) - imagesx($fill_cap);

        $image = imagecreatetruecolor($width, imagesy($bg));
        imagecopy($image, $bg, 0, 0, 0, 0, imagesx($bg), $width - imagesx($bg_cap));
        imagecopy($image, $bg_cap, $width - imagesx($bg_cap), 0, 0, 0, imagesx($bg_cap), imagesy($bg_cap));
        imagecopy($image, $fill, 0, 0, 0, 0, (int)$fill_width, imagesy($fill));
        imagecopy($image, $fill_cap, (int)$fill_width, 0, 0, 0, imagesx($fill_cap), imagesy($fill_cap));

        imagepng($image, $cachedir_local . $filename);
    }

    return ($cachedir_www . $filename);
}

/**
 * @param array $options
 * @return array|bool
 */
function b_xhelp_recent_show(array $options)
{
    if (!isset($_COOKIE['xhelp_recent_tickets'])) {
        return false;
    }
    if (!class_exists(Xhelp\Helper::class)) {
        return false;
    }
    $helper = Xhelp\Helper::getInstance();

    $tmp = $_COOKIE['xhelp_recent_tickets'];

    $block = [];

    if ('' != $tmp) {
        $tmp2 = explode(',', $tmp);

        $criteria = new \Criteria('id', '(' . $tmp . ')', 'IN', 't');
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        $tickets       = $ticketHandler->getObjects($criteria, true);

        foreach ($tmp2 as $ele) {
            if (isset($tickets[(int)$ele])) {
                $ticket = $tickets[(int)$ele];

                $overdue = false;
                if ($ticket->isOverdue()) {
                    $overdue = true;
                }

                $block['tickets'][] = [
                    'id'           => $ticket->getVar('id'),
                    'trim_subject' => xoops_substr($ticket->getVar('subject'), 0, 25),
                    'subject'      => $ticket->getVar('subject'),
                    'url'          => XOOPS_URL . '/modules/xhelp/ticket.php?id=' . $ticket->getVar('id'),
                    'overdue'      => $overdue,
                ];
            }
        }
        $block['ticketcount'] = count($tickets);

        return $block;
    }

    return false;
}

/**
 * @return bool|array
 */
function b_xhelp_actions_show()
{
    //    $session = new Xhelp\Session();
    $session = Xhelp\Session::getInstance();
    global $ticketInfo, $xoopsUser, $xoopsModule, $ticketInfo, $staff, $xoopsConfig;
    if (!class_exists(Xhelp\Helper::class)) {
        return false;
    }
    $helper = Xhelp\Helper::getInstance();

    /** @var \XoopsModuleHandler $moduleHandler */
    $moduleHandler = xoops_getHandler('module');
    /** @var \XoopsConfigHandler $configHandler */
    $configHandler = xoops_getHandler('config');
    /** @var \XoopsMemberHandler $memberHandler */
    $memberHandler = xoops_getHandler('member');
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
    $membershipHandler = $helper->getHandler('Membership');
    /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
    $staffHandler = $helper->getHandler('Staff');
    /** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
    $departmentHandler = $helper->getHandler('Department');

    //Don't show block for anonymous users or for non-staff members
    if (!$xoopsUser) {
        return false;
    }

    //Don't show block if outside the xhelp module'
    if (null === $xoopsModule || 'xhelp' !== $xoopsModule->getVar('dirname')) {
        return false;
    }

    $block = [];

    $myPage      = $_SERVER['SCRIPT_NAME'];
    $currentPage = mb_substr(mb_strrchr($myPage, '/'), 1);
    if (('ticket.php' !== $currentPage) || (2 != $helper->getConfig('xhelp_staffTicketActions'))) {
        return false;
    }

    if (Request::hasVar('id', 'GET')) {
        $block['ticketid'] = Request::getInt('id', 0, 'GET');
    } else {
        return false;
    }

    //Use Global $ticketInfo object (if exists)
    if (null === $ticketInfo) {
        $ticketInfo = $ticketHandler->get($block['ticketid']);
    }

    if (2 == $helper->getConfig('xhelp_staffTicketActions')) {
        $aOwnership   = [];
        $aOwnership[] = [
            'uid'   => 0,
            'uname' => _XHELP_NO_OWNER,
        ];
        if (null !== $staff) {
            foreach ($staff as $stf) {
                //** BTW - Need to have a way to get all XoopsUser objects for the staff in 1 shot
                //$own = $memberHandler->getUser($stf->getVar('uid'));    // Create user object
                $aOwnership[]                   = [
                    'uid'   => $stf->getVar('uid'),
                    'uname' => '',
                ];
                $all_users[$stf->getVar('uid')] = '';
            }
        } else {
            return false;
        }

        /** @var \XoopsMySQLDatabase $xoopsDB */
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
        $users   = [];

        //@Todo - why is this query here instead of using a function or the XoopsMemberHandler?
        $sql         = sprintf('SELECT uid, uname, name FROM `%s` WHERE uid IN (%s)', $xoopsDB->prefix('users'), implode(',', array_keys($all_users)));
        $ret         = $xoopsDB->query($sql);
        $displayName = $helper->getConfig('xhelp_displayName');
        while (false !== ($member = $xoopsDB->fetchArray($ret))) {
            if ((2 == $displayName) && ('' != $member['name'])) {
                $users[$member['uid']] = $member['name'];
            } else {
                $users[$member['uid']] = $member['uname'];
            }
        }

        foreach ($aOwnership as $i => $iValue) {
            if (isset($users[$iValue['uid']])) {
                $aOwnership[$i]['uname'] = $users[$iValue['uid']];
            }
        }
        $block['ownership'] = $aOwnership;
    }

    $block['imagePath']             = XHELP_IMAGE_URL . '/';
    $block['xhelp_priorities']      = [1, 2, 3, 4, 5];
    $block['xhelp_priorities_desc'] = [
        5 => _XHELP_PRIORITY5,
        4 => _XHELP_PRIORITY4,
        3 => _XHELP_PRIORITY3,
        2 => _XHELP_PRIORITY2,
        1 => _XHELP_PRIORITY1,
    ];
    $block['ticket_priority']       = $ticketInfo->getVar('priority');
    $block['ticket_status']         = $ticketInfo->getVar('status');
    $block['xhelp_status0']         = _XHELP_STATUS0;
    $block['xhelp_status1']         = _XHELP_STATUS1;
    $block['xhelp_status2']         = _XHELP_STATUS2;
    $block['ticket_ownership']      = $ticketInfo->getVar('ownership');

    $block['xhelp_has_changeOwner'] = false;
    if ($ticketInfo->getVar('uid') == $xoopsUser->getVar('uid')) {
        $block['xhelp_has_addResponse'] = true;
    } else {
        $block['xhelp_has_addResponse'] = false;
    }
    $block['xhelp_has_editTicket']     = false;
    $block['xhelp_has_deleteTicket']   = false;
    $block['xhelp_has_changePriority'] = false;
    $block['xhelp_has_changeStatus']   = false;
    $block['xhelp_has_editResponse']   = false;
    $block['xhelp_has_mergeTicket']    = false;
    $rowspan                           = 2;
    $checkRights                       = [
        XHELP_SEC_TICKET_OWNERSHIP      => ['xhelp_has_changeOwner', false],
        XHELP_SEC_RESPONSE_ADD          => ['xhelp_has_addResponse', false],
        XHELP_SEC_TICKET_EDIT           => ['xhelp_has_editTicket', true],
        XHELP_SEC_TICKET_DELETE         => ['xhelp_has_deleteTicket', true],
        XHELP_SEC_TICKET_MERGE          => ['xhelp_has_mergeTicket', true],
        XHELP_SEC_TICKET_PRIORITY       => ['xhelp_has_changePriority', false],
        XHELP_SEC_TICKET_STATUS         => ['xhelp_has_changeStatus', false],
        XHELP_SEC_RESPONSE_EDIT         => ['xhelp_has_editResponse', false],
        XHELP_SEC_FILE_DELETE           => ['xhelp_has_deleteFile', false],
        XHELP_SEC_FAQ_ADD               => ['xhelp_has_addFaq', false],
        XHELP_SEC_TICKET_TAKE_OWNERSHIP => ['xhelp_has_takeOwnership', false],
    ];

    $staff = $staffHandler->getByUid($xoopsUser->getVar('uid'));
    // See if this user is accepted for this ticket
    /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
    $ticketEmailsHandler = $helper->getHandler('TicketEmails');
    $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $ticketInfo->getVar('id')));
    $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
    $ticketEmails = $ticketEmailsHandler->getObjects($criteria);

    //Retrieve all departments
    $criteria = new \Criteria('', '');
    $criteria->setSort('department');
    $alldepts = $departmentHandler->getObjects($criteria);
    $aDept    = [];
    foreach ($alldepts as $dept) {
        $aDept[$dept->getVar('id')] = $dept->getVar('department');
    }
    unset($alldepts);
    $block['departments']  = $aDept;
    $block['departmentid'] = $ticketInfo->getVar('department');

    foreach ($checkRights as $right => $desc) {
        if ((XHELP_SEC_RESPONSE_ADD == $right) && count($ticketEmails) > 0) {
            $block[$desc[0]] = true;
            continue;
        }
        if ((XHELP_SEC_TICKET_STATUS == $right) && count($ticketEmails) > 0) {
            $block[$desc[0]] = true;
            continue;
        }
        $hasRights = $staff->checkRoleRights($right, $ticketInfo->getVar('department'));
        if ($hasRights) {
            $block[$desc[0]] = true;
            if ($desc[1]) {
                ++$rowspan;
            }
        }
    }

    $block['xhelp_actions_rowspan'] = $rowspan;

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

    $block['statuses'] = $aStatuses;

    return $block;
}

/**
 * @param array $options
 * @return string
 */
function b_xhelp_actions_edit(array $options): string
{
    $form = '<table>';
    $form .= '<tr>';
    $form .= '<td>' . _MB_XHELP_TRUNCATE_TITLE . '</td>';
    $form .= '<td>' . "<input type='text' name='options[]' value='" . $options[0] . "'></td>";
    $form .= '</tr>';
    $form .= '</table>';

    return $form;
}

/**
 * @param array $options
 * @return array|false
 */
function b_xhelp_mainactions_show(array $options)
{
    global $xoopsUser, $xhelp_isStaff;
    if (!class_exists(Xhelp\Helper::class)) {
        return false;
    }
    $helper = Xhelp\Helper::getInstance();
    // @todo - use the constant here if possible instead of the raw string
    $dirname                = $helper->getDirname();
    $block['linkPath']      = XHELP_BASE_URL . '/';
    $block['imagePath']     = XHELP_IMAGE_URL . '/';
    $block['menustyle']     = $options[0];
    $block['showicon']      = !$block['menustyle'] && $options[1];
    $block['startitem']     = !$block['menustyle'] ? '<li>' : '';
    $block['enditem']       = !$block['menustyle'] ? '</li>' : '';
    $block['startblock']    = !$block['menustyle'] ? '<ul>' : '<table cellspacing="0"><tr><td id="usermenu">';
    $block['endblock']      = !$block['menustyle'] ? '</ul>' : '</td></tr></table>';
    $block['savedSearches'] = false;
    $block['items'][0]      = [
        'link'  => 'anon_addTicket.php',
        'image' => 'addTicket.png',
        'text'  => _XHELP_MENU_LOG_TICKET,
    ];

    if ($xoopsUser) {
        $block['items'][0] = ['link' => 'index.php', 'image' => 'main.png', 'text' => _XHELP_MENU_MAIN];
        $block['items'][1] = [
            'link'  => 'addTicket.php',
            'image' => 'addTicket.png',
            'text'  => _XHELP_MENU_LOG_TICKET,
        ];
        $block['items'][2] = [
            'link'  => 'index.php?viewAllTickets=1&op=userViewAll',
            'image' => 'ticket.png',
            'text'  => _XHELP_MENU_ALL_TICKETS,
        ];
        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = $helper->getHandler('Staff');
        $staff        = $staffHandler->getByUid($xoopsUser->getVar('uid'));
        if ($staff) {
            $block['whoami']   = 'staff';
            $block['items'][3] = ['link' => 'search.php', 'image' => 'search2.png', 'text' => _XHELP_MENU_SEARCH];
            $block['items'][4] = [
                'link'  => 'profile.php',
                'image' => 'profile.png',
                'text'  => _XHELP_MENU_MY_PROFILE,
            ];
            $block['items'][2] = [
                'link'  => 'index.php?viewAllTickets=1&op=staffViewAll',
                'image' => 'ticket.png',
                'text'  => _XHELP_MENU_ALL_TICKETS,
            ];
            /** @var \XoopsModules\Xhelp\SavedSearchHandler $savedSearchHandler */
            $savedSearchHandler = $helper->getHandler('SavedSearch');
            $savedSearches      = $savedSearchHandler->getByUid($xoopsUser->getVar('uid'));
            $aSavedSearches     = [];
            foreach ($savedSearches as $sSearch) {
                $aSavedSearches[$sSearch->getVar('id')] = [
                    'id'           => $sSearch->getVar('id'),
                    'name'         => $sSearch->getVar('name'),
                    'search'       => $sSearch->getVar('search'),
                    'pagenav_vars' => $sSearch->getVar('pagenav_vars'),
                ];
            }
            $block['savedSearches'] = (count($aSavedSearches) < 1) ? false : $aSavedSearches;
        }
    }

    return $block;
}

/**
 * @param array $options
 * @return string
 */
function b_xhelp_mainactions_edit(array $options): string
{
    $form = "<table border='0'>";

    // Menu style
    $form .= '<tr><td>' . _MB_XHELP_TEXT_MENUSTYLE . '</td><td>';
    $form .= "<input type='radio' name='options[0]' value='0'" . ((0 == $options[0]) ? ' checked' : '') . '>' . _MB_XHELP_OPTION_MENUSTYLE1 . '';
    $form .= "<input type='radio' name='options[0]' value='1'" . ((1 == $options[0]) ? ' checked' : '') . '>' . _MB_XHELP_OPTION_MENUSTYLE2 . '</td></tr>';

    // Auto select last items
    $form .= '<tr><td>' . _MB_XHELP_TEXT_SHOWICON . '</td><td>';
    $form .= "<input type='radio' name='options[1]' value='0'" . ((0 == $options[1]) ? ' checked' : '') . '>' . _NO . '';
    $form .= "<input type='radio' name='options[1]' value='1'" . ((1 == $options[1]) ? ' checked' : '') . '>' . _YES . '</td></tr>';

    $form .= '</table>';

    return $form;
}
