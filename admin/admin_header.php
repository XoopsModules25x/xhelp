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
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Xhelp\Helper;
use XoopsModules\Xhelp\Session;
use XoopsModules\Xhelp\Constants;
use XoopsModules\Xhelp\Common\Configurator;

/** @var Admin $adminObject */
/** @var Helper $helper */
require \dirname(__DIR__) . '/preloads/autoloader.php';

require \dirname(__DIR__, 3) . '/include/cp_header.php';
require \dirname(__DIR__) . '/include/common.php';

require \dirname(__DIR__) . '/include/functions_admin.php';
//require \dirname(__DIR__) . '/include/events.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once \dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName = \basename(\dirname(__DIR__));
$helper        = Helper::getInstance();
$adminObject   = Admin::getInstance();

//require_once XHELP_BASE_PATH . '/admin/AdminButtons.php';
//require_once XHELP_BASE_PATH . '/functions.php';
require_once XHELP_INCLUDE_PATH . '/functions_admin.php';
//require_once XHELP_INCLUDE_PATH . '/events.php';
// require_once XHELP_CLASS_PATH . '/session.php';

require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

//global $xoopsModule;

/** @var \XoopsModules\Xhelp\DepartmentHandler $departmentHandler */
$departmentHandler = $helper->getHandler('Department');
/** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
$fileHandler = $helper->getHandler('File');
/** @var \XoopsModules\Xhelp\LogMessageHandler $logmessageHandler */
$logmessageHandler = $helper->getHandler('LogMessage');
/** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
$responseHandler = $helper->getHandler('Response');
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');
/** @var \XoopsModules\Xhelp\StaffReviewHandler $staffreviewHandler */
$staffreviewHandler = $helper->getHandler('StaffReview');
/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
///** @var \XoopsModules\Xhelp\JstaffdeptHandler $jstaffdeptHandler */
//$jstaffdeptHandler = $helper->getHandler('Jstaffdept');
/** @var \XoopsModules\Xhelp\ResponseTemplatesHandler $responsetemplatesHandler */
$responsetemplatesHandler = $helper->getHandler('ResponseTemplates');
/** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
$mimetypeHandler = $helper->getHandler('Mimetype');
/** @var \XoopsModules\Xhelp\DepartmentMailBoxHandler $departmentMailBoxHandler */
$departmentMailBoxHandler = $helper->getHandler('DepartmentMailBox');
/** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
$roleHandler = $helper->getHandler('Role');
/** @var \XoopsModules\Xhelp\StaffRoleHandler $staffRoleHandler */
$staffRoleHandler = $helper->getHandler('StaffRole');
///** @var \XoopsModules\Xhelp\MetaHandler $metaHandler */
//$metaHandler = $helper->getHandler('Meta');
/** @var \XoopsModules\Xhelp\MailEventHandler $mailEventHandler */
$mailEventHandler = $helper->getHandler('MailEvent');
/** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
$ticketEmailsHandler = $helper->getHandler('TicketEmails');
/** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
$statusHandler = $helper->getHandler('Status');
/** @var \XoopsModules\Xhelp\SavedSearchHandler $savedSearchHandler */
$savedSearchHandler = $helper->getHandler('SavedSearch');
/** @var \XoopsModules\Xhelp\TicketFieldDepartmentHandler $ticketFieldDepartmentHandler */
$ticketFieldDepartmentHandler = $helper->getHandler('TicketFieldDepartment');
/** @var \XoopsModules\Xhelp\NotificationHandler $notificationHandler */
$notificationHandler = $helper->getHandler('Notification');
/** @var \XoopsModules\Xhelp\TicketFieldHandler $ticketFieldHandler */
$ticketFieldHandler = $helper->getHandler('TicketField');
/** @var \XoopsModules\Xhelp\TicketValuesHandler $ticketValuesHandler */
$ticketValuesHandler = $helper->getHandler('TicketValues');
/** @var \XoopsModules\Xhelp\TicketListHandler $ticketListHandler */
$ticketListHandler = $helper->getHandler('TicketList');
///** @var \XoopsModules\Xhelp\ $bayes_categoriesHandler */
//$bayes_categoriesHandler = $helper->getHandler('Bayes_categories');
///** @var \XoopsModules\Xhelp\ $bayes_wordfreqsHandler */
//$bayes_wordfreqsHandler = $helper->getHandler('Bayes_wordfreqs');
/** @var \XoopsModules\Xhelp\TicketSolutionHandler $ticketSolutionHandler */
$ticketSolutionHandler = $helper->getHandler('TicketSolution');

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');
$helper->loadLanguage('common');

$configurator = new Configurator();
$icons        = $configurator->icons;

//$myts = \MyTextSanitizer::getInstance();

//if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
//    require_once $GLOBALS['xoops']->path('class/template.php');
//    $xoopsTpl = new \XoopsTpl();
//}

//$icons = [
//    'editimg'   => "<img src='" . XHELP_IMAGE_URL . "/button_edit.png' alt='" . _AM_XHELP_ICO_EDIT . "' align='middle'>",
//    'deleteimg' => "<img src='" . XHELP_IMAGE_URL . "/button_delete.png' alt='" . _AM_XHELP_ICO_DELETE . "' align='middle'>",
//    'online'    => "<img src='" . XHELP_IMAGE_URL . "/on.png' alt='" . _AM_XHELP_ICO_ONLINE . "' align='middle'>",
//    'offline'   => "<img src='" . XHELP_IMAGE_URL . "/off.png' alt='" . _AM_XHELP_ICO_OFFLINE . "' align='middle'>",
//];

// Overdue time
// require_once XHELP_CLASS_PATH . '/session.php';

$session = Session::getInstance();

if (!$overdueTime = $session->get('xhelp_overdueTime')) {
    $session->set('xhelp_overdueTime', $helper->getConfig('xhelp_overdueTime'));
    $overdueTime = $session->get('xhelp_overdueTime');
}

if ($overdueTime != $helper->getConfig('xhelp_overdueTime')) {
    $session->set('xhelp_overdueTime', $helper->getConfig('xhelp_overdueTime'));   // Set new value for overdueTime

    // Change overdueTime in all of tickets (OPEN & HOLD)
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler  = $helper->getHandler('Ticket');
    $criteria       = new \Criteria('status', '2', '<>');
    $tickets        = $ticketHandler->getObjects($criteria);
    $updatedTickets = [];
    foreach ($tickets as $ticket) {
        $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));
        if ($ticketHandler->insert($ticket, true)) {
            $updatedTickets[$ticket->getVar('id')] = true;  // Not used anywhere
        } else {
            $updatedTickets[$ticket->getVar('id')] = false; // Not used anywhere
        }
    }
}

//require_once $GLOBALS['xoops']->path($pathModuleAdmin . '/moduleadmin.php');
