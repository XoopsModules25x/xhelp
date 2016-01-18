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
 * @copyright    The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license      GNU GPL 2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package
 * @since
 * @author     XOOPS Development Team
 * @version    $Id $
 */

$path = dirname(dirname(dirname(dirname(__FILE__))));
include_once $path . '/mainfile.php';
include_once $path . '/include/cp_functions.php';
require_once $path . '/include/cp_header.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    include_once(XOOPS_ROOT_PATH.'/modules/xhelp/include/constants.php');
}

require(XHELP_BASE_PATH.'/admin/admin_buttons.php');
require_once(XHELP_BASE_PATH.'/functions.php');
require_once(XHELP_INCLUDE_PATH.'/functions_admin.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');
require_once(XHELP_CLASS_PATH.'/session.php');

include_once XOOPS_ROOT_PATH . '/class/xoopstree.php';
include_once XOOPS_ROOT_PATH . '/class/xoopslists.php';
include_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

xhelpIncludeLang('main');
xhelpIncludeLang('modinfo');

global $xoopsModule;
/*
$module_id = $xoopsModule->getVar('mid');
$oAdminButton = new AdminButtons();
$oAdminButton->AddTitle(sprintf(_AM_XHELP_ADMIN_TITLE, $xoopsModule->getVar('name')));
$oAdminButton->AddButton(_AM_XHELP_INDEX, XHELP_ADMIN_URL."/index.php", 'index');
$oAdminButton->AddButton(_AM_XHELP_MENU_MANAGE_DEPARTMENTS, XHELP_ADMIN_URL."/department.php?op=manageDepartments", 'manDept');
$oAdminButton->AddButton(_AM_XHELP_TEXT_MANAGE_FILES, XHELP_ADMIN_URL."/file.php?op=manageFiles", 'manFiles');
$oAdminButton->AddButton(_AM_XHELP_MENU_MANAGE_STAFF, XHELP_ADMIN_URL."/staff.php?op=manageStaff", 'manStaff');
$oAdminButton->AddButton(_AM_XHELP_TEXT_MANAGE_NOTIFICATIONS, XHELP_ADMIN_URL."/notifications.php", 'manNotify');
$oAdminButton->AddButton(_AM_XHELP_TEXT_MANAGE_STATUSES, XHELP_ADMIN_URL."/status.php?op=manageStatus", 'manStatus');
$oAdminButton->addButton(_AM_XHELP_TEXT_MANAGE_FIELDS, XHELP_ADMIN_URL.'/fields.php', 'manfields');
$oAdminButton->AddButton(_AM_XHELP_MENU_MANAGE_FAQ, "faqAdapter.php?op=manage", 'manFaqAdapters');
$oAdminButton->addButton(_AM_XHELP_MENU_MIMETYPES, XHELP_ADMIN_URL."/mimetypes.php", 'mimetypes');
$oAdminButton->addButton(_AM_XHELP_TEXT_MAIL_EVENTS, XHELP_ADMIN_URL."/index.php?op=mailEvents", 'mailEvents');
$oAdminButton->AddTopLink(_AM_XHELP_MENU_PREFERENCES, XOOPS_URL ."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=". $module_id);
//$oAdminButton->AddTopLink(_AM_XHELP_BLOCK_TEXT, XHELP_ADMIN_URL."/index.php?op=blocks");
$oAdminButton->addTopLink(_AM_XHELP_UPDATE_MODULE, XOOPS_URL ."/modules/system/admin.php?fct=modulesadmin&amp;op=update&amp;module=xhelp");
$oAdminButton->addTopLink(_MI_XHELP_MENU_CHECK_TABLES, XHELP_ADMIN_URL."/upgrade.php?op=checkTables");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_GOTOMODULE, XHELP_BASE_URL."/index.php");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_ABOUT, XHELP_ADMIN_URL."/index.php?op=about");
*/
$thisModuleDir = $GLOBALS['xoopsModule']->getVar('dirname');

//if functions.php file exist
//require_once dirname(dirname(__FILE__)) . '/include/functions.php';

// Load language files
xoops_loadLanguage('admin', $thisModuleDir);
xoops_loadLanguage('modinfo', $thisModuleDir);
xoops_loadLanguage('main', $thisModuleDir);

$pathIcon16 = '../'.$xoopsModule->getInfo('icons16');
$pathIcon32 = '../'.$xoopsModule->getInfo('icons32');
$pathModuleAdmin = $xoopsModule->getInfo('dirmoduleadmin');

$imagearray = array(
    'editimg' => "<img src='". XHELP_IMAGE_URL ."/button_edit.png' alt='" . _AM_XHELP_ICO_EDIT . "' align='middle' />",
    'deleteimg' => "<img src='". XHELP_IMAGE_URL ."/button_delete.png' alt='" . _AM_XHELP_ICO_DELETE . "' align='middle' />",
    'online' => "<img src='". XHELP_IMAGE_URL ."/on.png' alt='" . _AM_XHELP_ICO_ONLINE . "' align='middle' />",
    'offline' => "<img src='". XHELP_IMAGE_URL ."/off.png' alt='" . _AM_XHELP_ICO_OFFLINE . "' align='middle' />",
);

// Overdue time
require_once(XHELP_CLASS_PATH.'/session.php');
$_xhelpSession = new Session();

if(!$overdueTime = $_xhelpSession->get("xhelp_overdueTime")){
    $_xhelpSession->set("xhelp_overdueTime", $xoopsModuleConfig['xhelp_overdueTime']);
    $overdueTime = $_xhelpSession->get("xhelp_overdueTime");
}

if($overdueTime != $xoopsModuleConfig['xhelp_overdueTime']){
    $_xhelpSession->set("xhelp_overdueTime", $xoopsModuleConfig['xhelp_overdueTime']);   // Set new value for overdueTime

    // Change overdueTime in all of tickets (OPEN & HOLD)
    $hTickets =& xhelpGetHandler('ticket');
    $crit = new Criteria('status', 2, '<>');
    $tickets =& $hTickets->getObjects($crit);
    $updatedTickets = array();
    foreach($tickets as $ticket){
        $ticket->setVar('overdueTime', $ticket->getVar('posted') + ($xoopsModuleConfig['xhelp_overdueTime'] *60*60));
        if(!$hTickets->insert($ticket, true)){
            $updatedTickets[$ticket->getVar('id')] = false; // Not used anywhere
        } else {
            $updatedTickets[$ticket->getVar('id')] = true;  // Not used anywhere
        }
    }
}

include_once $GLOBALS['xoops']->path($pathModuleAdmin.'/moduleadmin.php');
