<?php
//

$moduleDirName = basename(dirname(__DIR__));

if (false !== ($moduleHelper = Xmf\Module\Helper::getHelper($moduleDirName))) {
} else {
    $moduleHelper = Xmf\Module\Helper::getHelper('system');
}


$pathIcon32 = \Xmf\Module\Admin::menuIconPath('');
//$pathModIcon32 = $moduleHelper->getModule()->getInfo('modicons32');

$moduleHelper->loadLanguage('modinfo');

$adminObject            = [];
$i                      = 0;
$adminmenu[$i]['title'] = _AM_MODULEADMIN_HOME;
$adminmenu[$i]['link']  = 'admin/index.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/home.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MANAGER;
$adminmenu[$i]['link']  = 'admin/main.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/manage.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MANAGE_DEPARTMENTS;
$adminmenu[$i]['link']  = 'admin/department.php?op=manageDepartments';
$adminmenu[$i]['icon']  = $pathIcon32 . '/category.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MANAGE_FILES;
$adminmenu[$i]['link']  = 'admin/file.php?op=manageFiles';
$adminmenu[$i]['icon']  = $pathIcon32 . '/content.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MANAGE_STAFF;
$adminmenu[$i]['link']  = 'admin/staff.php?op=manageStaff';
$adminmenu[$i]['icon']  = $pathIcon32 . '/users.png';

++$i;
$adminmenu[$i]['title'] = _MI_XHELP_TEXT_NOTIFICATIONS;
$adminmenu[$i]['link']  = 'admin/notifications.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/face-smile.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_TEXT_MANAGE_STATUSES;
$adminmenu[$i]['link']  = 'admin/status.php?op=manageStatus';
$adminmenu[$i]['icon']  = $pathIcon32 . '/stats.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_TEXT_MANAGE_FIELDS;
$adminmenu[$i]['link']  = 'admin/fields.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/insert_table_row.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_TEXT_MANAGE_FAQ;
$adminmenu[$i]['link']  = 'admin/faqAdapter.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/faq.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_CHECK_TABLES;
$adminmenu[$i]['link']  = 'admin/upgrade.php?op=checkTables';
$adminmenu[$i]['icon']  = $pathIcon32 . '/index.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MIMETYPES;
$adminmenu[$i]['link']  = 'admin/mimetypes.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/type.png';
++$i;
$adminmenu[$i]['title'] = _MI_XHELP_MENU_MAIL_EVENTS;
$adminmenu[$i]['link']  = 'admin/main.php?op=mailEvents';
$adminmenu[$i]['icon']  = $pathIcon32 . '/mail_foward.png';

++$i;
$adminmenu[$i]['title'] = _AM_MODULEADMIN_ABOUT;
$adminmenu[$i]['link']  = 'admin/about.php';
$adminmenu[$i]['icon']  = $pathIcon32 . '/about.png';

/*
$oAdminButton->AddTopLink(_AM_XHELP_MENU_PREFERENCES, XOOPS_URL ."/modules/system/admin.php?fct=preferences&amp;op=showmod&amp;mod=". $module_id);
$oAdminButton->addTopLink(_AM_XHELP_UPDATE_MODULE, XOOPS_URL ."/modules/system/admin.php?fct=modulesadmin&amp;op=update&amp;module=xhelp");
$oAdminButton->addTopLink(_MI_XHELP_MENU_CHECK_TABLES, XHELP_ADMIN_URL."/upgrade.php?op=checkTables");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_GOTOMODULE, XHELP_BASE_URL."/index.php");
$oAdminButton->AddTopLink(_AM_XHELP_ADMIN_ABOUT, XHELP_ADMIN_URL."/index.php?op=about");
*/
