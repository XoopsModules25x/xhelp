<?php
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
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use Xmf\Module\Admin;
use XoopsModules\Xhelp\{
    Helper,
    Session
};

/** @var Admin $adminObject */
/** @var Helper $helper */

require dirname(__DIR__) . '/preloads/autoloader.php';

require dirname(dirname(dirname(__DIR__))) . '/include/cp_header.php';
require dirname(__DIR__) . '/include/common.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once dirname(__DIR__) . '/preloads/autoloader.php';

$moduleDirName = basename(dirname(__DIR__));
$helper = Helper::getInstance();
$adminObject = Admin::getInstance();

//require_once XHELP_BASE_PATH . '/admin/AdminButtons.php';
//require_once XHELP_BASE_PATH . '/functions.php';
require_once XHELP_INCLUDE_PATH . '/functions_admin.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
// require_once XHELP_CLASS_PATH . '/session.php';

require_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
require_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

//global $xoopsModule;
/*
$module_id = $xoopsModule->getVar('mid');
$oAdminButton = new AdminButtons();
$oAdminButton->addTitle(sprintf(_AM_XHELP_ADMIN_TITLE, $xoopsModule->getVar('name')));
$oAdminButton->addButton(_AM_XHELP_INDEX, XHELP_ADMIN_URL."/index.php", 'index');
$oAdminButton->addButton(_AM_XHELP_MENU_MANAGE_DEPARTMENTS, XHELP_ADMIN_URL."/department.php?op=manageDepartments", 'manDept');
$oAdminButton->addButton(_AM_XHELP_TEXT_MANAGE_FILES, XHELP_ADMIN_URL."/file.php?op=manageFiles", 'manFiles');
$oAdminButton->addButton(_AM_XHELP_MENU_MANAGE_STAFF, XHELP_ADMIN_URL."/staff.php?op=manageStaff", 'manStaff');
$oAdminButton->addButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, XHELP_ADMIN_URL."/notifications.php", 'manNotify');
$oAdminButton->addButton(_AM_XHELP_TEXT_MANAGE_STATUSES, XHELP_ADMIN_URL."/status.php?op=manageStatus", 'manStatus');
$oAdminButton->addButton(_AM_XHELP_TEXT_MANAGE_FIELDS, XHELP_ADMIN_URL.'/fields.php', 'manfields');
$oAdminButton->addButton(_AM_XHELP_MENU_MANAGE_FAQ, "faqAdapter.php?op=manage", 'manFaqAdapters');
$oAdminButton->addButton(_AM_XHELP_MENU_MIMETYPES, XHELP_ADMIN_URL."/mimetypes.php", 'mimetypes');
$oAdminButton->addButton(_AM_XHELP_TEXT_MAIL_EVENTS, XHELP_ADMIN_URL."/index.php?op=mailEvents", 'mailEvents');
$oAdminButton->addTopLink(_AM_XHELP_MENU_PREFERENCES, XOOPS_URL ."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=". $module_id);
//$oAdminButton->addTopLink(_AM_XHELP_BLOCK_TEXT, XHELP_ADMIN_URL."/index.php?op=blocks");
$oAdminButton->addTopLink(_AM_XHELP_UPDATE_MODULE, XOOPS_URL ."/modules/system/admin.php?fct=modulesadmin&amp;op=update&amp;module=xhelp");
$oAdminButton->addTopLink(_MI_XHELP_MENU_CHECK_TABLES, XHELP_ADMIN_URL."/upgrade.php?op=checkTables");
$oAdminButton->addTopLink(_AM_XHELP_ADMIN_GOTOMODULE, XHELP_BASE_URL."/index.php");
$oAdminButton->addTopLink(_AM_XHELP_ADMIN_ABOUT, XHELP_ADMIN_URL."/index.php?op=about");
*/

// Load language files
$helper->loadLanguage('admin');
$helper->loadLanguage('modinfo');
$helper->loadLanguage('main');
$helper->loadLanguage('common');

//$myts = \MyTextSanitizer::getInstance();

//if (!isset($GLOBALS['xoopsTpl']) || !($GLOBALS['xoopsTpl'] instanceof \XoopsTpl)) {
//    require_once $GLOBALS['xoops']->path('class/template.php');
//    $xoopsTpl = new \XoopsTpl();
//}

//$imagearray = [
//    'editimg'   => "<img src='" . XHELP_IMAGE_URL . "/button_edit.png' alt='" . _AM_XHELP_ICO_EDIT . "' align='middle'>",
//    'deleteimg' => "<img src='" . XHELP_IMAGE_URL . "/button_delete.png' alt='" . _AM_XHELP_ICO_DELETE . "' align='middle'>",
//    'online'    => "<img src='" . XHELP_IMAGE_URL . "/on.png' alt='" . _AM_XHELP_ICO_ONLINE . "' align='middle'>",
//    'offline'   => "<img src='" . XHELP_IMAGE_URL . "/off.png' alt='" . _AM_XHELP_ICO_OFFLINE . "' align='middle'>",
//];

// Overdue time
// require_once XHELP_CLASS_PATH . '/session.php';

$_xhelpSession = new Session();

if (!$overdueTime = $_xhelpSession->get('xhelp_overdueTime')) {
    $_xhelpSession->set('xhelp_overdueTime', $helper->getConfig('xhelp_overdueTime'));
    $overdueTime = $_xhelpSession->get('xhelp_overdueTime');
}

if ($overdueTime != $helper->getConfig('xhelp_overdueTime')) {
    $_xhelpSession->set('xhelp_overdueTime', $helper->getConfig('xhelp_overdueTime'));   // Set new value for overdueTime

    // Change overdueTime in all of tickets (OPEN & HOLD)
    $hTickets       = Helper::getInstance()->getHandler('Ticket');
    $crit           = new \Criteria('status', 2, '<>');
    $tickets        = $hTickets->getObjects($crit);
    $updatedTickets = [];
    foreach ($tickets as $ticket) {
        $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($helper->getConfig('xhelp_overdueTime') * 60 * 60));
        if (!$hTickets->insert($ticket, true)) {
            $updatedTickets[$ticket->getVar('id')] = false; // Not used anywhere
        } else {
            $updatedTickets[$ticket->getVar('id')] = true;  // Not used anywhere
        }
    }
}

//require_once $GLOBALS['xoops']->path($pathModuleAdmin . '/moduleadmin.php');
