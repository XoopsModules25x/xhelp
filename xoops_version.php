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
 * @author       XOOPS Development Team
 */
require_once __DIR__ . '/preloads/autoloader.php';

$moduleDirName      = basename(__DIR__);
$moduleDirNameUpper = \mb_strtoupper($moduleDirName);

$modversion['version']             = '1.0.0';
$modversion['module_status']       = 'Alpha 1';
$modversion['release_date']        = '2021/12/28';
$modversion['name']                = _MI_XHELP_NAME;
$modversion['description']         = _MI_XHELP_DESC;
$modversion['author']              = '3dev.org';
$modversion['help']                = 'page=help';
$modversion['license']             = 'GNU GPL 2.0 or later';
$modversion['license_url']         = 'www.gnu.org/licenses/gpl-2.0.html';
$modversion['official']            = 0;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        //1 indicates supported by Xoops CORE Dev Team, 0 means 3rd party supported
$modversion['image']               = 'assets/images/logoModule.png';
$modversion['dirname']             = basename(__DIR__);
$modversion['modicons16']          = 'assets/images/icons/16';
$modversion['modicons32']          = 'assets/images/icons/32';
$modversion['release_file']        = XOOPS_URL . '/modules/' . $modversion['dirname'] . '/docs/changelog.txt';
$modversion['module_website_url']  = 'www.xoops.org';
$modversion['module_website_name'] = 'XOOPS';
$modversion['min_php']             = '7.3';
$modversion['min_xoops']           = '2.5.10';
$modversion['min_admin']           = '1.2';
$modversion['mindb']               = ['mysql' => '5.5'];

// Extra stuff for about page
$modversion['version_info']    = 'Final';
$modversion['creator']      = '3Dev';
$modversion['demo_site']    = 'https://demo.3dev.org';
$modversion['official_site'] = 'https://www.3dev.org';
$modversion['bug_url']       = 'https://dev.xoops.org/modules/xfmod/tracker/?group_id=1058&atid=336';
$modversion['feature_url']   = 'https://dev.xoops.org/modules/xfmod/tracker/?group_id=1058&atid=339';
$modversion['questions_email'] = 'xhelp-questions@3dev.org';

// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => _MI_XHELP_OVERVIEW, 'link' => 'page=help'],
    ['name' => _MI_XHELP_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => _MI_XHELP_LICENSE, 'link' => 'page=license'],
    ['name' => _MI_XHELP_SUPPORT, 'link' => 'page=support'],
];

// Developers
$modversion['contributors']['developers'][0]['name']    = 'Eric Juden';
$modversion['contributors']['developers'][0]['uname'] = 'eric_juden';
$modversion['contributors']['developers'][0]['email'] = 'eric@3dev.org';
$modversion['contributors']['developers'][0]['website'] = '';
$modversion['contributors']['developers'][1]['name']    = 'Brian Wahoff';
$modversion['contributors']['developers'][1]['uname']   = 'ackbarr';
$modversion['contributors']['developers'][1]['email']   = 'ackbarr@xoops.org';
$modversion['contributors']['developers'][1]['website'] = 'https://ackbarr.greatweb.org';

// Translators
$modversion['contributors']['translators'][0]['language'] = 'Brazilian Portuguese';
$modversion['contributors']['translators'][0]['name']     = 'Silvio Palmieri';
$modversion['contributors']['translators'][0]['uname']    = 'silvio';
$modversion['contributors']['translators'][0]['email']    = 'silvio@silviotech.com.br';
$modversion['contributors']['translators'][0]['website']  = 'www.xoops.pr.gov.br';

$modversion['contributors']['translators'][1]['language'] = 'French';
$modversion['contributors']['translators'][1]['name']     = '';
$modversion['contributors']['translators'][1]['uname']    = 'outch';
$modversion['contributors']['translators'][1]['email']    = 'outch@free.fr';
$modversion['contributors']['translators'][1]['website']  = '';

$modversion['contributors']['translators'][2]['language'] = 'German';
$modversion['contributors']['translators'][2]['name']     = '';
$modversion['contributors']['translators'][2]['uname']    = 'Feichtl';
$modversion['contributors']['translators'][2]['email']    = 'werner@feichtlbauer.net';
$modversion['contributors']['translators'][2]['website']  = '';

$modversion['contributors']['translators'][3]['language'] = 'Italian';
$modversion['contributors']['translators'][3]['name']     = 'Alessandro Camisasca';
$modversion['contributors']['translators'][3]['uname']    = 'alexcami';
$modversion['contributors']['translators'][3]['email']    = 'alexcami@gmail.com';
$modversion['contributors']['translators'][3]['website']  = '';

$modversion['contributors']['translators'][4]['language'] = 'Japanese';
$modversion['contributors']['translators'][4]['name']     = 'Nils Valentin';
$modversion['contributors']['translators'][4]['uname']    = 'nils';
$modversion['contributors']['translators'][4]['email']    = 'opensource@seibu-tsushin.co.jp';
$modversion['contributors']['translators'][4]['website']  = '';

$modversion['contributors']['translators'][5]['language'] = 'Japanese';
$modversion['contributors']['translators'][5]['name']     = 'Mizukoshi Norio';
$modversion['contributors']['translators'][5]['uname']    = 'Norio';
$modversion['contributors']['translators'][5]['email']    = 'opensource@seibu-tsushin.co.jp';
$modversion['contributors']['translators'][5]['website']  = '';

$modversion['contributors']['translators'][6]['language'] = 'Persian';
$modversion['contributors']['translators'][6]['name']     = '';
$modversion['contributors']['translators'][6]['uname']    = 'irmtfan';
$modversion['contributors']['translators'][6]['email']    = 'irmtfan@yahoo.com';
$modversion['contributors']['translators'][6]['website']  = '';

$modversion['contributors']['translators'][7]['language'] = 'Spanish';
$modversion['contributors']['translators'][7]['name']     = '';
$modversion['contributors']['translators'][7]['uname']    = 'chencho';
$modversion['contributors']['translators'][7]['email']    = 'chencho@pc-cito.com';
$modversion['contributors']['translators'][7]['website']  = '';

$modversion['contributors']['translators'][8]['language'] = 'Simplified Chinese';
$modversion['contributors']['translators'][8]['name']     = 'Yu-Ching Lin';
$modversion['contributors']['translators'][8]['uname']    = 'iris';
$modversion['contributors']['translators'][8]['email']    = 'iris.yuchin@gmail.com';
$modversion['contributors']['translators'][8]['website']  = '';

$modversion['contributors']['translators'][9]['language'] = 'Simplified Chinese';
$modversion['contributors']['translators'][9]['name']     = 'Finjon Kiang';
$modversion['contributors']['translators'][9]['uname']    = 'kiang';
$modversion['contributors']['translators'][9]['email']    = '';
$modversion['contributors']['translators'][9]['website']  = 'https://twpug.net';

$modversion['contributors']['translators'][10]['language'] = 'Portuguese';
$modversion['contributors']['translators'][10]['name']     = 'Artur Oliveira';
$modversion['contributors']['translators'][10]['uname']    = '_Vlad_';
$modversion['contributors']['translators'][10]['email']    = '';
$modversion['contributors']['translators'][10]['website']  = 'https://dev.xoops.org/modules/xfmod/forum/forum.php?forum_id=561';

// Testers
$modversion['contributors']['testers'][0]['name']    = 'Alan Juden';
$modversion['contributors']['testers'][0]['uname'] = 'ajuden';
$modversion['contributors']['testers'][0]['email'] = 'alan@3dev.org';
$modversion['contributors']['testers'][0]['website'] = '';

$modversion['contributors']['testers'][1]['name']    = 'Mike Sweeney';
$modversion['contributors']['testers'][1]['uname'] = 'theBIGmick';
$modversion['contributors']['testers'][1]['email'] = 'thebigmick@epcgamers.com';
$modversion['contributors']['testers'][1]['website'] = '';

$modversion['contributors']['testers'][2]['name']    = 'Marc-Andr� Lanciault';
$modversion['contributors']['testers'][2]['uname'] = 'marcan';
$modversion['contributors']['testers'][2]['email'] = '';
$modversion['contributors']['testers'][2]['website'] = 'https://www.smartfactory.ca';

// Documenters
$modversion['contributors']['documenters'][0]['name']    = 'Ryan Johnson';
$modversion['contributors']['documenters'][0]['uname'] = 'rcjohnson';
$modversion['contributors']['documenters'][0]['email'] = 'rcj@austin.rr.com';
$modversion['contributors']['documenters'][0]['website'] = '';

$modversion['contributors']['code'][0]['name']    = 'Nazar Aziz';
$modversion['contributors']['code'][0]['uname'] = 'Nazar';
$modversion['contributors']['code'][0]['email'] = 'nazar@panthersoftware.com';
$modversion['contributors']['code'][0]['website'] = '';

$modversion['contributors']['code'][1]['name']    = 'Marc-Andr� Lanciault';
$modversion['contributors']['code'][1]['uname'] = 'marcan';
$modversion['contributors']['code'][1]['email'] = '';
$modversion['contributors']['code'][1]['website'] = 'https://www.smartfactory.ca';

$modversion['contributors']['code'][2]['name']    = 'Federico Nebiolo';
$modversion['contributors']['code'][2]['uname'] = 'iconeb';
$modversion['contributors']['code'][2]['email'] = 'iconeb@yahoo.it';
$modversion['contributors']['code'][2]['website'] = 'https://www.arturin.it';

$modversion['contributors']['code'][3]['name']    = 'Ricardo Costa';
$modversion['contributors']['code'][3]['uname'] = 'trabis';
$modversion['contributors']['code'][3]['email'] = 'lusopoemas@gmail.com';
$modversion['contributors']['code'][3]['website'] = 'https://www.xuups.com';

// ------------------- Mysql ------------------- //
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
// Tables created by sql file (without prefix!)
$modversion['tables'] = [
    $moduleDirName . '_' . 'departments',
    $moduleDirName . '_' . 'files',
    $moduleDirName . '_' . 'logmessages',
    $moduleDirName . '_' . 'responses',
    $moduleDirName . '_' . 'staff',
    $moduleDirName . '_' . 'staffreview',
    $moduleDirName . '_' . 'tickets',
    $moduleDirName . '_' . 'jstaffdept',
    $moduleDirName . '_' . 'responsetemplates',
    $moduleDirName . '_' . 'mimetypes',
    $moduleDirName . '_' . 'department_mailbox',
    $moduleDirName . '_' . 'roles',
    $moduleDirName . '_' . 'staffroles',
    $moduleDirName . '_' . 'meta',
    $moduleDirName . '_' . 'mailevent',
    $moduleDirName . '_' . 'ticket_submit_emails',
    $moduleDirName . '_' . 'status',
    $moduleDirName . '_' . 'saved_searches',
    $moduleDirName . '_' . 'ticket_field_departments',
    $moduleDirName . '_' . 'notifications',
    $moduleDirName . '_' . 'ticket_fields',
    $moduleDirName . '_' . 'ticket_values',
    $moduleDirName . '_' . 'ticket_lists',
    $moduleDirName . '_' . 'bayes_categories',
    $moduleDirName . '_' . 'bayes_wordfreqs',
    $moduleDirName . '_' . 'ticket_solutions',
];

// Admin things
$modversion['hasAdmin']    = 1;
$modversion['system_menu'] = 1;
$modversion['adminindex']  = 'admin/index.php';
$modversion['adminmenu']   = 'admin/menu.php';

// ------------------- Help files ------------------- //
$modversion['helpsection'] = [
    ['name' => _MI_XHELP_OVERVIEW, 'link' => 'page=help'],
    ['name' => _MI_XHELP_DISCLAIMER, 'link' => 'page=disclaimer'],
    ['name' => _MI_XHELP_LICENSE, 'link' => 'page=license'],
    ['name' => _MI_XHELP_SUPPORT, 'link' => 'page=support'],

    //    array('name' => _MI_XHELP_HELP1, 'link' => 'page=help1'),
    //    array('name' => _MI_XHELP_HELP2, 'link' => 'page=help2'),
    //    array('name' => _MI_XHELP_HELP3, 'link' => 'page=help3'),
    //    array('name' => _MI_XHELP_HELP4, 'link' => 'page=help4'),
    //    array('name' => _MI_XHELP_HOWTO, 'link' => 'page=__howto'),
    //    array('name' => _MI_XHELP_REQUIREMENTS, 'link' => 'page=__requirements'),
    //    array('name' => _MI_XHELP_CREDITS, 'link' => 'page=__credits'),
];

// ------------------- Templates ------------------- //

$modversion['templates'] = [
    ['file' => 'xhelp_staff_header.tpl', 'description' => _MI_XHELP_TEMP_STAFF_HEADER],
    ['file' => 'xhelp_user_header.tpl', 'description' => _MI_XHELP_TEMP_USER_HEADER],
    ['file' => 'xhelp_staff_ticket_table.tpl', 'description' => _MI_XHELP_TEMP_STAFF_TICKET_TABLE],
    ['file' => 'xhelp_addTicket.tpl', 'description' => _MI_XHELP_TEMP_ADDTICKET],
    ['file' => 'xhelp_search.tpl', 'description' => _MI_XHELP_TEMP_SEARCH],
    ['file' => 'xhelp_staff_index.tpl', 'description' => _MI_XHELP_TEMP_STAFF_INDEX],
    ['file' => 'xhelp_staffReview.tpl', 'description' => _MI_XHELP_TEMP_STAFFREVIEW],
    ['file' => 'xhelp_staff_profile.tpl', 'description' => _MI_XHELP_TEMP_STAFF_PROFILE],
    ['file' => 'xhelp_staff_ticketDetails.tpl', 'description' => _MI_XHELP_TEMP_STAFF_TICKETDETAILS],
    ['file' => 'xhelp_user_index.tpl', 'description' => _MI_XHELP_TEMP_USER_INDEX],
    ['file' => 'xhelp_user_ticketDetails.tpl', 'description' => _MI_XHELP_TEMP_USER_TICKETDETAILS],
    ['file' => 'xhelp_response.tpl', 'description' => _MI_XHELP_TEMP_STAFF_RESPONSE],
    ['file' => 'xhelp_lookup.tpl', 'description' => _MI_XHELP_TEMP_LOOKUP],
    ['file' => 'xhelp_editTicket.tpl', 'description' => _MI_XHELP_TEMP_EDITTICKET],
    ['file' => 'xhelp_editResponse.tpl', 'description' => _MI_XHELP_TEMP_EDITRESPONSE],
    ['file' => 'xhelp_announcement.tpl', 'description' => _MI_XHELP_TEMP_ANNOUNCEMENT],
    ['file' => 'xhelp_print.tpl', 'description' => _MI_XHELP_TEMP_PRINT],
    ['file' => 'xhelp_staff_viewall.tpl', 'description' => _MI_XHELP_TEMP_STAFF_ALL],
    ['file' => 'xhelp_batchTickets.tpl', 'description' => _MI_XHELP_TEMP_BATCH_TICKETS],
    ['file' => 'xhelp_setdept.tpl', 'description' => _MI_XHELP_TEMP_SETDEPT],
    ['file' => 'xhelp_setpriority.tpl', 'description' => _MI_XHELP_TEMP_SETPRIORITY],
    ['file' => 'xhelp_setowner.tpl', 'description' => _MI_XHELP_TEMP_SETOWNER],
    ['file' => 'xhelp_setstatus.tpl', 'description' => _MI_XHELP_TEMP_SETSTATUS],
    ['file' => 'xhelp_deletetickets.tpl', 'description' => _MI_XHELP_TEMP_DELETE],
    ['file' => 'xhelp_batch_response.tpl', 'description' => _MI_XHELP_TEMP_BATCHRESPONSE],
    ['file' => 'xhelp_anon_addTicket.tpl', 'description' => _MI_XHELP_TEMP_ANON_ADDTICKET],
    ['file' => 'xhelp_error.tpl', 'description' => _MI_XHELP_TEMP_ERROR],
    ['file' => 'xhelp_editSearch.tpl', 'description' => _MI_XHELP_TEMP_EDITSEARCH],
    ['file' => 'xhelp_user_viewall.tpl', 'description' => _MI_XHELP_TEMP_USER_ALL],
    ['file' => 'xhelp_addFaq.tpl', 'description' => _MI_XHELP_TEMP_ADD_FAQ],
    ['file' => 'xhelp_report.tpl', 'description' => _MI_XHELP_TEMP_REPORT],
];
// ------------------- Blocks ------------------- //
// Block that displays open tickets

$modversion['blocks'][] = [
    'file'        => 'xhelp_blocks.php',
    'name' => _MI_XHELP_BNAME1,
    'description' => _MI_XHELP_BNAME1_DESC,
    'show_func'   => 'b_xhelp_open_show',
    'edit_func'   => 'b_xhelp_actions_edit',
    'options'     => '19',
    'template'    => 'xhelp_block_open.tpl',
];

// Block that displays staff performance
$modversion['blocks'][] = [
    'file'        => 'xhelp_blocks.php',
    'name' => _MI_XHELP_BNAME2,
    'description' => _MI_XHELP_BNAME2_DESC,
    'show_func'   => 'b_xhelp_performance_show',
    'template'    => 'xhelp_block_performance.tpl',
];

// Block that displays recent tickets
$modversion['blocks'][] = [
    'file'        => 'xhelp_blocks.php',
    'name' => _MI_XHELP_BNAME3,
    'description' => _MI_XHELP_BNAME3_DESC,
    'show_func'   => 'b_xhelp_recent_show',
    'template'    => 'xhelp_block_recent.tpl',
];

// Block that displays recent tickets
$modversion['blocks'][] = [
    'file'        => 'xhelp_blocks.php',
    'name' => _MI_XHELP_BNAME4,
    'description' => _MI_XHELP_BNAME4_DESC,
    'show_func'   => 'b_xhelp_actions_show',
    'template'    => 'xhelp_block_actions.tpl',
];

// Block that displays main actions
$modversion['blocks'][] = [
    'file'        => 'xhelp_blocks.php',
    'name' => _MI_XHELP_BNAME5,
    'description' => _MI_XHELP_BNAME5_DESC,
    'show_func'   => 'b_xhelp_mainactions_show',
    'template'    => 'xhelp_block_mainactions.tpl',
    'edit_func'   => 'b_xhelp_mainactions_edit',
    'options'     => '0|1',
];

// Menu
global $xhelp_isStaff;
$modversion['hasMain']        = 1;
$modversion['sub'][1]['name'] = _MI_XHELP_SMNAME1;
$modversion['sub'][1]['url']  = 'index.php';
$modversion['sub'][2]['name'] = _MI_XHELP_SMNAME2;
$modversion['sub'][2]['url']  = 'addTicket.php';
if ($xhelp_isStaff) {
    $modversion['sub'][3]['name'] = _MI_XHELP_SMNAME3;
    $modversion['sub'][3]['url']  = 'profile.php';
    $modversion['sub'][4]['name'] = _MI_XHELP_SMNAME4;
    $modversion['sub'][4]['url']  = 'index.php?op=staffViewAll';
    $modversion['sub'][5]['name'] = _MI_XHELP_SMNAME5;
    $modversion['sub'][5]['url']  = 'search.php';
    $modversion['sub'][6]['name'] = _MI_XHELP_SMNAME6;
    $modversion['sub'][6]['url']  = 'report.php';
} else {
    $modversion['sub'][4]['name'] = _MI_XHELP_SMNAME4;
    $modversion['sub'][4]['url']  = 'index.php?op=userViewAll';
}

// Search
$modversion['hasSearch'] = 0;

//Install/Uninstall Functions
$modversion['onInstall']   = 'include/oninstall.php';
$modversion['onUpdate']  = 'include/onupdate.php';
$modversion['onUninstall'] = 'include/onuninstall.php';

// Config
$modversion['config'][] = [
    'name'        => 'xhelp_allowUpload',     // Allows users to upload files when adding a ticket
    'title' => '_MI_XHELP_ALLOW_UPLOAD',
    'description' => '_MI_XHELP_ALLOW_UPLOAD_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];

// --------------Uploads : mimetypes of image --------------
$modversion['config'][] = [
    'name'        => 'mimetypes',
    'title' => 'MI_XHELP_MIMETYPES',
    'description' => 'MI_XHELP_MIMETYPES_DESC',
    'formtype'    => 'select_multi',
    'valuetype'   => 'array',
    'default'     => ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'],
    'options'     => [
        'bmp'   => 'image/bmp',
        'gif' => 'image/gif',
        'pjpeg' => 'image/pjpeg',
        'jpeg'  => 'image/jpeg',
        'jpg'   => 'image/jpg',
        'jpe'   => 'image/jpe',
        'png'   => 'image/png',
    ],
];

$modversion['config'][] = [
    'name'        => 'xhelp_uploadSize',      // Size of file upload allowed
    'title' => '_MI_XHELP_UPLOAD_SIZE',
    'description' => '_MI_XHELP_UPLOAD_SIZE_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'string',
    'default'     => '50000',
];

$modversion['config'][] = [
    'name'        => 'xhelp_uploadWidth',      // Max width for upload
    'title' => '_MI_XHELP_UPLOAD_WIDTH',
    'description' => '_MI_XHELP_UPLOAD_WIDTH_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'string',
    'default'     => '1200',
];

$modversion['config'][] = [
    'name'        => 'xhelp_uploadHeight',      // Max height for upload
    'title' => '_MI_XHELP_UPLOAD_HEIGHT',
    'description' => '_MI_XHELP_UPLOAD_HEIGHT_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'string',
    'default'     => '800',
];

$modversion['config'][] = [
    'name'        => 'xhelp_numTicketUploads',      // Number of ticket uploads allowed
    'title' => '_MI_XHELP_NUM_TICKET_UPLOADS',
    'description' => '_MI_XHELP_NUM_TICKET_UPLOADS_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => '1',
];

$modversion['config'][] = [
    'name'        => 'xhelp_allowReopen',     // Allows users to reopen tickets
    'title' => '_MI_XHELP_ALLOW_REOPEN',
    'description' => '_MI_XHELP_ALLOW_REOPEN_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];

$modversion['config'][] = [
    'name'        => 'xhelp_announcements',   // Name of topic for announcments
    'title' => '_MI_XHELP_ANNOUNCEMENTS',
    'description' => '_MI_XHELP_ANNOUNCEMENTS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'string',
    'default'     => '',
];

$modversion['config'][] = [
    'name'        => 'xhelp_staffTicketCount',
    'title' => '_MI_XHELP_STAFF_TC',
    'description' => '_MI_XHELP_STAFF_TC_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 10,
    'options'     => ['5' => 5, '10' => 10, '15' => 15, '20' => 20],
];

$modversion['config'][] = [
    'name'        => 'xhelp_staffTicketActions',
    'title' => '_MI_XHELP_STAFF_ACTIONS',
    'description' => '_MI_XHELP_STAFF_ACTIONS_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => [_MI_XHELP_ACTION1 => 1, _MI_XHELP_ACTION2 => 2],
];

$modversion['config'][] = [
    'name'        => 'xhelp_overdueTime',
    'title' => '_MI_XHELP_OVERDUE_TIME',
    'description' => '_MI_XHELP_OVERDUE_TIME_DSC',
    'formtype'    => 'textbox',
    'valuetype'   => 'int',
    'default'     => 0,
];

$modversion['config'][] = [
    'name'        => 'xhelp_allowAnonymous',     // Allows anonymous user submission
    'title' => '_MI_XHELP_ALLOW_ANON',
    'description' => '_MI_XHELP_ALLOW_ANON_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

$modversion['config'][] = [
    'name'        => 'xhelp_deptVisibility',     // Apply dept visibility to staff members
    'title' => '_MI_XHELP_APPLY_VISIBILITY',
    'description' => '_MI_XHELP_APPLY_VISIBILITY_DSC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

$modversion['config'][] = [
    'name'        => 'xhelp_displayName',
    'title' => '_MI_XHELP_DISPLAY_NAME',
    'description' => '_MI_XHELP_DISPLAY_NAME_DSC',
    'formtype'    => 'select',
    'valuetype'   => 'int',
    'default'     => 1,
    'options'     => [_MI_XHELP_USERNAME => 1, _MI_XHELP_REALNAME => 2],
];

/**
 * Make Sample button visible?
 */
$modversion['config'][] = [
    'name'        => 'displaySampleButton',
    'title' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_SAMPLE_BUTTON_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 1,
];
/**
 * Show Developer Tools?
 */
$modversion['config'][] = [
    'name'        => 'displayDeveloperTools',
    'title' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS',
    'description' => 'CO_' . $moduleDirNameUpper . '_' . 'SHOW_DEV_TOOLS_DESC',
    'formtype'    => 'yesno',
    'valuetype'   => 'int',
    'default'     => 0,
];

// Email templates
$modversion['_email_tpl'][1]['name']          = 'new_ticket';                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    // Add ticket
$modversion['_email_tpl'][1]['category'] = 'dept';
$modversion['_email_tpl'][1]['mail_template'] = 'dept_newticket_notify';
$modversion['_email_tpl'][1]['mail_subject']  = _MI_XHELP_DEPT_NEWTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][1]['bit_value']     = 0;
$modversion['_email_tpl'][1]['title']         = _MI_XHELP_DEPT_NEWTICKET_NOTIFY;
$modversion['_email_tpl'][1]['caption']       = _MI_XHELP_DEPT_NEWTICKET_NOTIFYCAP;
$modversion['_email_tpl'][1]['description']   = _MI_XHELP_DEPT_NEWTICKET_NOTIFYDSC;

$modversion['_email_tpl'][2]['name']          = 'removed_ticket';    // Delete ticket
$modversion['_email_tpl'][2]['category'] = 'dept';
$modversion['_email_tpl'][2]['mail_template'] = 'dept_removedticket_notify';
$modversion['_email_tpl'][2]['mail_subject']  = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][2]['bit_value']     = 1;
$modversion['_email_tpl'][2]['title']         = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFY;
$modversion['_email_tpl'][2]['caption']       = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][2]['description']   = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][3]['name']          = 'modified_ticket';   // Edit ticket information
$modversion['_email_tpl'][3]['category'] = 'dept';
$modversion['_email_tpl'][3]['mail_template'] = 'dept_modifiedticket_notify';
$modversion['_email_tpl'][3]['mail_subject']  = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][3]['bit_value']     = 2;
$modversion['_email_tpl'][3]['title']         = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFY;
$modversion['_email_tpl'][3]['caption']       = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][3]['description']   = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][4]['name']          = 'new_response';
$modversion['_email_tpl'][4]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][4]['mail_template'] = 'dept_newresponse_notify';
$modversion['_email_tpl'][4]['mail_subject']  = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][4]['bit_value']     = 3;
$modversion['_email_tpl'][4]['title']         = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFY;
$modversion['_email_tpl'][4]['caption']       = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][4]['description']   = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][5]['name']          = 'modified_response';
$modversion['_email_tpl'][5]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][5]['mail_template'] = 'dept_modifiedresponse_notify';
$modversion['_email_tpl'][5]['mail_subject']  = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][5]['bit_value']     = 4;
$modversion['_email_tpl'][5]['title']         = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFY;
$modversion['_email_tpl'][5]['caption']       = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][5]['description']   = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][6]['name']          = 'changed_status';     // Update status
$modversion['_email_tpl'][6]['category'] = 'dept';               // All tickets
$modversion['_email_tpl'][6]['mail_template'] = 'dept_changedstatus_notify';
$modversion['_email_tpl'][6]['mail_subject']  = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYSBJ;
$modversion['_email_tpl'][6]['bit_value']     = 5;
$modversion['_email_tpl'][6]['title']         = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFY;
$modversion['_email_tpl'][6]['caption']       = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYCAP;
$modversion['_email_tpl'][6]['description']   = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYDSC;

$modversion['_email_tpl'][7]['name']          = 'changed_priority';     // Update priority
$modversion['_email_tpl'][7]['category'] = 'dept';                 // All tickets
$modversion['_email_tpl'][7]['mail_template'] = 'dept_changedpriority_notify';
$modversion['_email_tpl'][7]['mail_subject']  = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYSBJ;
$modversion['_email_tpl'][7]['bit_value']     = 6;
$modversion['_email_tpl'][7]['title']         = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFY;
$modversion['_email_tpl'][7]['caption']       = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYCAP;
$modversion['_email_tpl'][7]['description']   = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYDSC;

$modversion['_email_tpl'][8]['name']          = 'new_owner';
$modversion['_email_tpl'][8]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][8]['mail_template'] = 'dept_newowner_notify';
$modversion['_email_tpl'][8]['mail_subject']  = _MI_XHELP_DEPT_NEWOWNER_NOTIFYSBJ;
$modversion['_email_tpl'][8]['bit_value']     = 7;
$modversion['_email_tpl'][8]['title']         = _MI_XHELP_DEPT_NEWOWNER_NOTIFY;
$modversion['_email_tpl'][8]['caption']       = _MI_XHELP_DEPT_NEWOWNER_NOTIFYCAP;
$modversion['_email_tpl'][8]['description']   = _MI_XHELP_DEPT_NEWOWNER_NOTIFYDSC;

$modversion['_email_tpl'][9]['name']          = 'close_ticket';        // Close ticket
$modversion['_email_tpl'][9]['category'] = 'dept';
$modversion['_email_tpl'][9]['mail_template'] = 'dept_closeticket_notify';
$modversion['_email_tpl'][9]['mail_subject']  = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYSBJ;
$modversion['_email_tpl'][9]['bit_value']     = 8;
$modversion['_email_tpl'][9]['title']         = _MI_XHELP_DEPT_CLOSETICKET_NOTIFY;
$modversion['_email_tpl'][9]['caption']       = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYCAP;
$modversion['_email_tpl'][9]['description']   = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYDSC;

$modversion['_email_tpl'][10]['name']          = 'merge_ticket';
$modversion['_email_tpl'][10]['category'] = 'dept';
$modversion['_email_tpl'][10]['mail_template'] = 'dept_mergeticket_notify';
$modversion['_email_tpl'][10]['mail_subject']  = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYSBJ;
$modversion['_email_tpl'][10]['bit_value']     = 9;
$modversion['_email_tpl'][10]['title']         = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFY;
$modversion['_email_tpl'][10]['caption']       = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYCAP;
$modversion['_email_tpl'][10]['description']   = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYDSC;

$modversion['_email_tpl'][11]['name']          = 'new_this_owner';
$modversion['_email_tpl'][11]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][11]['mail_template'] = 'ticket_newowner_notify';
$modversion['_email_tpl'][11]['mail_subject']  = _MI_XHELP_TICKET_NEWOWNER_NOTIFYSBJ;
$modversion['_email_tpl'][11]['bit_value']     = 10;
$modversion['_email_tpl'][11]['title']         = _MI_XHELP_TICKET_NEWOWNER_NOTIFY;
$modversion['_email_tpl'][11]['caption']       = _MI_XHELP_TICKET_NEWOWNER_NOTIFYCAP;
$modversion['_email_tpl'][11]['description']   = _MI_XHELP_TICKET_NEWOWNER_NOTIFYDSC;

$modversion['_email_tpl'][12]['name']          = 'removed_this_ticket';
$modversion['_email_tpl'][12]['category'] = 'ticket';        // Individual ticket
$modversion['_email_tpl'][12]['mail_template'] = 'ticket_removedticket_notify';
$modversion['_email_tpl'][12]['mail_subject']  = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][12]['bit_value']     = 11;
$modversion['_email_tpl'][12]['title']         = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFY;
$modversion['_email_tpl'][12]['caption']       = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][12]['description']   = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][13]['name']          = 'modified_this_ticket';
$modversion['_email_tpl'][13]['category'] = 'ticket';        // Individual ticket
$modversion['_email_tpl'][13]['mail_template'] = 'ticket_modifiedticket_notify';
$modversion['_email_tpl'][13]['mail_subject']  = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][13]['bit_value']     = 12;
$modversion['_email_tpl'][13]['title']         = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFY;
$modversion['_email_tpl'][13]['caption']       = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][13]['description']   = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][14]['name']          = 'new_this_response';
$modversion['_email_tpl'][14]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][14]['mail_template'] = 'ticket_newresponse_notify';
$modversion['_email_tpl'][14]['mail_subject']  = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][14]['bit_value']     = 13;
$modversion['_email_tpl'][14]['title']         = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFY;
$modversion['_email_tpl'][14]['caption']       = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][14]['description']   = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][15]['name']          = 'modified_this_response';
$modversion['_email_tpl'][15]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][15]['mail_template'] = 'ticket_modifiedresponse_notify';
$modversion['_email_tpl'][15]['mail_subject']  = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][15]['bit_value']     = 14;
$modversion['_email_tpl'][15]['title']         = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFY;
$modversion['_email_tpl'][15]['caption']       = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][15]['description']   = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][16]['name']          = 'changed_this_status';     // Update status
$modversion['_email_tpl'][16]['category'] = 'ticket';                  // Individual ticket
$modversion['_email_tpl'][16]['mail_template'] = 'ticket_changedstatus_notify';
$modversion['_email_tpl'][16]['mail_subject']  = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYSBJ;
$modversion['_email_tpl'][16]['bit_value']     = 15;
$modversion['_email_tpl'][16]['title']         = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFY;
$modversion['_email_tpl'][16]['caption']       = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYCAP;
$modversion['_email_tpl'][16]['description']   = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYDSC;

$modversion['_email_tpl'][17]['name']          = 'changed_this_priority';     // Update priority
$modversion['_email_tpl'][17]['category'] = 'ticket';                    // Individual ticket
$modversion['_email_tpl'][17]['mail_template'] = 'ticket_changedpriority_notify';
$modversion['_email_tpl'][17]['mail_subject']  = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYSBJ;
$modversion['_email_tpl'][17]['bit_value']     = 16;
$modversion['_email_tpl'][17]['title']         = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFY;
$modversion['_email_tpl'][17]['caption']       = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYCAP;
$modversion['_email_tpl'][17]['description']   = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYDSC;

$modversion['_email_tpl'][18]['name']          = 'new_this_ticket';        // Add ticket
$modversion['_email_tpl'][18]['category'] = 'ticket';
$modversion['_email_tpl'][18]['mail_template'] = 'ticket_newticket_notify';
$modversion['_email_tpl'][18]['mail_subject']  = _MI_XHELP_TICKET_NEWTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][18]['bit_value']     = 17;
$modversion['_email_tpl'][18]['title']         = _MI_XHELP_TICKET_NEWTICKET_NOTIFY;
$modversion['_email_tpl'][18]['caption']       = _MI_XHELP_TICKET_NEWTICKET_NOTIFYCAP;
$modversion['_email_tpl'][18]['description']   = _MI_XHELP_TICKET_NEWTICKET_NOTIFYDSC;

$modversion['_email_tpl'][19]['name']          = 'close_this_ticket';        // Close ticket
$modversion['_email_tpl'][19]['category'] = 'ticket';
$modversion['_email_tpl'][19]['mail_template'] = 'ticket_closeticket_notify';
$modversion['_email_tpl'][19]['mail_subject']  = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYSBJ;
$modversion['_email_tpl'][19]['bit_value']     = 18;
$modversion['_email_tpl'][19]['title']         = _MI_XHELP_TICKET_CLOSETICKET_NOTIFY;
$modversion['_email_tpl'][19]['caption']       = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYCAP;
$modversion['_email_tpl'][19]['description']   = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYDSC;

$modversion['_email_tpl'][20]['name']          = 'new_this_ticket_via_email';        // Add ticket  via email
$modversion['_email_tpl'][20]['category'] = 'ticket';
$modversion['_email_tpl'][20]['mail_template'] = 'ticket_newticket_byemail_notify';
$modversion['_email_tpl'][20]['mail_subject']  = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYSBJ;
$modversion['_email_tpl'][20]['bit_value']     = 19;
$modversion['_email_tpl'][20]['title']         = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFY;
$modversion['_email_tpl'][20]['caption']       = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYCAP;
$modversion['_email_tpl'][20]['description']   = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYDSC;

$modversion['_email_tpl'][21]['name']          = 'new_user_byemail';        // Add ticket  via email
$modversion['_email_tpl'][21]['category'] = 'ticket';
$modversion['_email_tpl'][21]['mail_template'] = 'ticket_new_user_byemail';
$modversion['_email_tpl'][21]['mail_subject']  = _MI_XHELP_TICKET_NEWUSER_NOTIFYSBJ;
$modversion['_email_tpl'][21]['bit_value']     = 20;
$modversion['_email_tpl'][21]['title']         = _MI_XHELP_TICKET_NEWUSER_NOTIFY;
$modversion['_email_tpl'][21]['caption']       = _MI_XHELP_TICKET_NEWUSER_NOTIFYCAP;
$modversion['_email_tpl'][21]['description']   = _MI_XHELP_TICKET_NEWUSER_NOTIFYDSC;

$modversion['_email_tpl'][22]['name']          = 'new_user_activation1';
$modversion['_email_tpl'][22]['category'] = 'ticket';
$modversion['_email_tpl'][22]['mail_template'] = 'ticket_new_user_activation1';
$modversion['_email_tpl'][22]['mail_subject']  = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYSBJ;
$modversion['_email_tpl'][22]['bit_value']     = 21;
$modversion['_email_tpl'][22]['title']         = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFY;
$modversion['_email_tpl'][22]['caption']       = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYCAP;
$modversion['_email_tpl'][22]['description']   = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYDSC;

$modversion['_email_tpl'][23]['name']          = 'new_user_activation2';
$modversion['_email_tpl'][23]['category'] = 'ticket';
$modversion['_email_tpl'][23]['mail_template'] = 'ticket_new_user_activation2';
$modversion['_email_tpl'][23]['mail_subject']  = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYSBJ;
$modversion['_email_tpl'][23]['bit_value']     = 22;
$modversion['_email_tpl'][23]['title']         = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFY;
$modversion['_email_tpl'][23]['caption']       = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYCAP;
$modversion['_email_tpl'][23]['description']   = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYDSC;

$modversion['_email_tpl'][24]['name']          = 'user_email_error';
$modversion['_email_tpl'][24]['category'] = 'ticket';
$modversion['_email_tpl'][24]['mail_template'] = 'ticket_user_email_error';
$modversion['_email_tpl'][24]['mail_subject']  = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYSBJ;
$modversion['_email_tpl'][24]['bit_value']     = 23;
$modversion['_email_tpl'][24]['title']         = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFY;
$modversion['_email_tpl'][24]['caption']       = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYCAP;
$modversion['_email_tpl'][24]['description']   = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYDSC;

$modversion['_email_tpl'][25]['name']          = 'merge_this_ticket';
$modversion['_email_tpl'][25]['category'] = 'ticket';
$modversion['_email_tpl'][25]['mail_template'] = 'ticket_mergeticket_notify';
$modversion['_email_tpl'][25]['mail_subject']  = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYSBJ;
$modversion['_email_tpl'][25]['bit_value']     = 24;
$modversion['_email_tpl'][25]['title']         = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFY;
$modversion['_email_tpl'][25]['caption']       = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYCAP;
$modversion['_email_tpl'][25]['description']   = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYDSC;
