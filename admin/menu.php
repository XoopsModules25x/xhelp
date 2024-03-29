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

use Xmf\Module\Admin;
use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Helper;

require \dirname(__DIR__) . '/preloads/autoloader.php';

/** @var \XoopsModules\Xhelp\Helper $helper */
$moduleDirName      = \basename(\dirname(__DIR__));
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$helper = Helper::getInstance();
$helper->loadLanguage('common');
$helper->loadLanguage('feedback');

$pathIcon32    = Admin::menuIconPath('');
$pathModIcon32 = XOOPS_URL . '/modules/' . $moduleDirName . '/assets/images/icons/32/';
if (is_object($helper->getModule())
    && false !== $helper->getModule()
        ->getInfo('modicons32')) {
    $pathModIcon32 = $helper->url(
        $helper->getModule()
            ->getInfo('modicons32')
    );
}

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_HOME,
    'link'  => 'admin/index.php',
    'icon'  => $pathIcon32 . '/home.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MANAGER,
    'link'  => 'admin/main.php',
    'icon'  => $pathIcon32 . '/manage.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MANAGE_DEPARTMENTS,
    'link'  => 'admin/department.php?op=manageDepartments',
    'icon'  => $pathIcon32 . '/category.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MANAGE_STAFF,
    'link'  => 'admin/staff.php?op=manageStaff',
    'icon'  => $pathIcon32 . '/users.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_TEXT_NOTIFICATIONS,
    'link'  => 'admin/notifications.php',
    'icon'  => $pathIcon32 . '/face-smile.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MANAGE_FILES,
    'link'  => 'admin/file.php?op=manageFiles',
    'icon'  => $pathIcon32 . '/content.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_TEXT_MANAGE_STATUSES,
    'link'  => 'admin/status.php?op=manageStatus',
    'icon'  => $pathIcon32 . '/stats.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_TEXT_MANAGE_FIELDS,
    'link'  => 'admin/fields.php',
    'icon'  => $pathIcon32 . '/insert_table_row.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_TEXT_MANAGE_FAQ,
    'link'  => 'admin/faqAdapter.php',
    'icon'  => $pathIcon32 . '/faq.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_CHECK_TABLES,
    'link'  => 'admin/upgrade.php?op=checkTables',
    'icon'  => $pathIcon32 . '/index.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MIMETYPES,
    'link'  => 'admin/mimetypes.php',
    'icon'  => $pathIcon32 . '/type.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_MENU_MAIL_EVENTS,
    'link'  => 'admin/main.php?op=mailEvents',
    'icon'  => $pathIcon32 . '/mail_foward.png',
];

// Blocks Admin
$adminmenu[] = [
    'title' => constant('CO_' . $moduleDirNameUpper . '_' . 'BLOCKS'),
    'link'  => 'admin/blocksadmin.php',
    'icon'  => $pathIcon32 . '/block.png',
];

//Clone
$adminmenu[] = [
    'title' => _CLONE,
    'link'  => 'admin/clone.php',
    'icon'  => $pathIcon32 . '/page_copy.png',
];

$adminmenu[] = [
    'title' => _MI_XHELP_ADMIN_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png',
];

/*
$oAdminButton->addTopLink(_AM_XHELP_MENU_PREFERENCES, XOOPS_URL ."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=". $module_id);
$oAdminButton->addTopLink(_AM_XHELP_UPDATE_MODULE, XOOPS_URL ."/modules/system/admin.php?fct=modulesadmin&amp;op=update&amp;module=xhelp");
$oAdminButton->addTopLink(_MI_XHELP_MENU_CHECK_TABLES, XHELP_ADMIN_URL."/upgrade.php?op=checkTables");
$oAdminButton->addTopLink(_AM_XHELP_ADMIN_GOTOMODULE, XHELP_BASE_URL."/index.php");
$oAdminButton->addTopLink(_AM_XHELP_ADMIN_ABOUT, XHELP_ADMIN_URL."/index.php?op=about");
*/
