<?php

use Xoopsmodules\xhelp;

require_once __DIR__ . '/../class/Helper.php';
//require_once __DIR__ . '/../include/common.php';
$helper = xhelp\Helper::getInstance();

$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
$pathModIcon32 = $helper->getModule()->getInfo('modicons32');


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
    'title' => _MI_XHELP_MENU_MANAGE_FILES,
    'link'  => 'admin/file.php?op=manageFiles',
    'icon'  => $pathIcon32 . '/content.png',
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
    'title' => _MI_XHELP_TEXT_MANAGE_STATUSES,
    'link'  => 'admin/staff.php?op=manageStatus',
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

$adminmenu[] = [
    'title' => _MI_XHELP_ADMIN_ABOUT,
    'link'  => 'admin/about.php',
    'icon'  => $pathIcon32 . '/about.png',
];


/*
$oAdminButton->AddTopLink(_AM_XHELP_MENU_PREFERENCES, XOOPS_URL ."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=". $module_id);
$oAdminButton->addTopLink(_AM_XHELP_UPDATE_MODULE, XOOPS_URL ."/modules/system/admin.php?fct=modulesadmin&amp;op=update&amp;module=xhelp");
$oAdminButton->addTopLink(_MI_XHELP_MENU_CHECK_TABLES, XHELP_ADMIN_URL."/upgrade.php?op=checkTables");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_GOTOMODULE, XHELP_BASE_URL."/index.php");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_ABOUT, XHELP_ADMIN_URL."/index.php?op=about");
*/
