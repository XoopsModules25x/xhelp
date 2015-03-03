<?php
// $Id: xoops_version.php,v 1.105 2006/01/25 20:54:30 eric_juden Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
if (!defined('XOOPS_ROOT_PATH')) {
    die('XOOPS root path not defined');
}

$modversion['name'] = _MI_XHELP_NAME;
$modversion['version'] = '0.81';
$modversion['description'] = _MI_XHELP_DESC;
$modversion['author'] = "3dev.org";
$modversion['help']        = 'page=help';
$modversion['license']     = 'GNU GPL 2.0 or later';
$modversion['license_url'] = "www.gnu.org/licenses/gpl-2.0.html/";
$modversion['official'] = 1;
$modversion['image'] = "images/xhelp_slogo.png";
$modversion['dirname'] = "xhelp";

$modversion['dirmoduleadmin'] = '/Frameworks/moduleclasses/moduleadmin';
$modversion['icons16']        = '../../Frameworks/moduleclasses/icons/16';
$modversion['icons32']        = '../../Frameworks/moduleclasses/icons/32';
//about
$modversion['release_date']        = '2012/07/30';
$modversion["module_website_url"] = "www.xoops.org";
$modversion["module_website_name"] = "XOOPS";
$modversion["module_status"]       = "Beta 1";
$modversion['min_php']             = '5.2';
$modversion['min_xoops']           = "2.5.5";
$modversion['min_admin']           = '1.1';
$modversion['min_db']              = array(
    'mysql'  => '5.0.7',
    'mysqli' => '5.0.7'
);

// Extra stuff for about page
$modversion['release_date']     = '19/09/2010';
$modversion['version_info']     = 'Final';
$modversion['creator']          = '3Dev';
$modversion['demo_site']        = 'http://demo.3dev.org';
$modversion['official_site']    = 'http://www.3dev.org';
$modversion['bug_url']          = 'http://dev.xoops.org/modules/xfmod/tracker/?group_id=1058&atid=336';
$modversion['feature_url']      = 'http://dev.xoops.org/modules/xfmod/tracker/?group_id=1058&atid=339';
$modversion['questions_email']  = 'xhelp-questions@3dev.org';

// Developers
$modversion['contributors']['developers'][0]['name']        = "Eric Juden";
$modversion['contributors']['developers'][0]['uname']       = "eric_juden";
$modversion['contributors']['developers'][0]['email']       = "eric@3dev.org";
$modversion['contributors']['developers'][0]['website']     = "";
$modversion['contributors']['developers'][1]['name']        = "Brian Wahoff";
$modversion['contributors']['developers'][1]['uname']       = "ackbarr";
$modversion['contributors']['developers'][1]['email']       = "ackbarr@xoops.org";
$modversion['contributors']['developers'][1]['website']     = "http://ackbarr.greatweb.org";

// Translators
$modversion['contributors']['translators'][0]['language']   = "Brazilian Portuguese";
$modversion['contributors']['translators'][0]['name']       = "Silvio Palmieri";
$modversion['contributors']['translators'][0]['uname']      = "silvio";
$modversion['contributors']['translators'][0]['email']      = "silvio@silviotech.com.br";
$modversion['contributors']['translators'][0]['website']    = "www.xooops.pr.gov.br";

$modversion['contributors']['translators'][1]['language']   = "French";
$modversion['contributors']['translators'][1]['name']       = "";
$modversion['contributors']['translators'][1]['uname']      = "outch";
$modversion['contributors']['translators'][1]['email']      = "outch@free.fr";
$modversion['contributors']['translators'][1]['website']    = "";

$modversion['contributors']['translators'][2]['language']   = "German";
$modversion['contributors']['translators'][2]['name']       = "";
$modversion['contributors']['translators'][2]['uname']      = "Feichtl";
$modversion['contributors']['translators'][2]['email']      = "werner@feichtlbauer.net";
$modversion['contributors']['translators'][2]['website']    = "";

$modversion['contributors']['translators'][3]['language']   = "Italian";
$modversion['contributors']['translators'][3]['name']       = "Alessandro Camisasca";
$modversion['contributors']['translators'][3]['uname']      = "alexcami";
$modversion['contributors']['translators'][3]['email']      = "alexcami@gmail.com";
$modversion['contributors']['translators'][3]['website']    = "";

$modversion['contributors']['translators'][4]['language']   = "Japanese";
$modversion['contributors']['translators'][4]['name']       = "Nils Valentin";
$modversion['contributors']['translators'][4]['uname']      = "nils";
$modversion['contributors']['translators'][4]['email']      = "opensource@seibu-tsushin.co.jp";
$modversion['contributors']['translators'][4]['website']    = "";

$modversion['contributors']['translators'][5]['language']   = "Japanese";
$modversion['contributors']['translators'][5]['name']       = "Mizukoshi Norio";
$modversion['contributors']['translators'][5]['uname']      = "Norio";
$modversion['contributors']['translators'][5]['email']      = "opensource@seibu-tsushin.co.jp";
$modversion['contributors']['translators'][5]['website']    = "";

$modversion['contributors']['translators'][6]['language']   = "Persian";
$modversion['contributors']['translators'][6]['name']       = "";
$modversion['contributors']['translators'][6]['uname']      = "irmtfan";
$modversion['contributors']['translators'][6]['email']      = "irmtfan@yahoo.com";
$modversion['contributors']['translators'][6]['website']    = "";

$modversion['contributors']['translators'][7]['language']   = "Spanish";
$modversion['contributors']['translators'][7]['name']       = "";
$modversion['contributors']['translators'][7]['uname']      = "chencho";
$modversion['contributors']['translators'][7]['email']      = "chencho@pc-cito.com";
$modversion['contributors']['translators'][7]['website']    = "";

$modversion['contributors']['translators'][8]['language']   = "Simplified Chinese";
$modversion['contributors']['translators'][8]['name']       = "Yu-Ching Lin";
$modversion['contributors']['translators'][8]['uname']      = "iris";
$modversion['contributors']['translators'][8]['email']      = "iris.yuchin@gmail.com";
$modversion['contributors']['translators'][8]['website']    = "";

$modversion['contributors']['translators'][9]['language']   = "Simplified Chinese";
$modversion['contributors']['translators'][9]['name']       = "Finjon Kiang";
$modversion['contributors']['translators'][9]['uname']      = "kiang";
$modversion['contributors']['translators'][9]['email']      = "";
$modversion['contributors']['translators'][9]['website']    = "http://twpug.net";

$modversion['contributors']['translators'][10]['language']   = 'Portuguese';
$modversion['contributors']['translators'][10]['name']       = 'Artur Oliveira';
$modversion['contributors']['translators'][10]['uname']      = '_Vlad_';
$modversion['contributors']['translators'][10]['email']      = '';
$modversion['contributors']['translators'][10]['website']    = 'http://dev.xoops.org/modules/xfmod/forum/forum.php?forum_id=561';

// Testers
$modversion['contributors']['testers'][0]['name']       = "Alan Juden";
$modversion['contributors']['testers'][0]['uname']      = "ajuden";
$modversion['contributors']['testers'][0]['email']      = "alan@3dev.org";
$modversion['contributors']['testers'][0]['website']    = "";

$modversion['contributors']['testers'][1]['name']       = "Mike Sweeney";
$modversion['contributors']['testers'][1]['uname']      = "theBIGmick";
$modversion['contributors']['testers'][1]['email']      = "thebigmick@epcgamers.com";
$modversion['contributors']['testers'][1]['website']    = "";

$modversion['contributors']['testers'][2]['name']       = "Marc-André Lanciault";
$modversion['contributors']['testers'][2]['uname']      = "marcan";
$modversion['contributors']['testers'][2]['email']      = "";
$modversion['contributors']['testers'][2]['website']    = "http://www.smartfactory.ca";

// Documenters
$modversion['contributors']['documenters'][0]['name']       = "Ryan Johnson";
$modversion['contributors']['documenters'][0]['uname']      = "rcjohnson";
$modversion['contributors']['documenters'][0]['email']      = "rcj@austin.rr.com";
$modversion['contributors']['documenters'][0]['website']    = "";

$modversion['contributors']['code'][0]['name']       = "Nazar Aziz";
$modversion['contributors']['code'][0]['uname']      = "Nazar";
$modversion['contributors']['code'][0]['email']      = "nazar@panthersoftware.com";
$modversion['contributors']['code'][0]['website']    = "";

$modversion['contributors']['code'][1]['name']       = "Marc-André Lanciault";
$modversion['contributors']['code'][1]['uname']      = "marcan";
$modversion['contributors']['code'][1]['email']      = "";
$modversion['contributors']['code'][1]['website']    = "http://www.smartfactory.ca";

$modversion['contributors']['code'][2]['name']       = "Federico Nebiolo";
$modversion['contributors']['code'][2]['uname']      = "iconeb";
$modversion['contributors']['code'][2]['email']      = "iconeb@yahoo.it";
$modversion['contributors']['code'][2]['website']    = "http://www.arturin.it";

$modversion['contributors']['code'][3]['name']       = "Ricardo Costa";
$modversion['contributors']['code'][3]['uname']      = "trabis";
$modversion['contributors']['code'][3]['email']      = "lusopoemas@gmail.com";
$modversion['contributors']['code'][3]['website']    = "http://www.xuups.com";

// Sql file (must contain sql generated by phpMyAdmin or phpPgAdmin)
// All tables should not have any prefix!
$modversion['sqlfile']['mysql'] = "sql/mysql.sql";
//$modversion['sqlfile']['postgresql'] = "sql/pgsql.sql";

// Tables created by sql file (without prefix!)
$modversion['tables'][0]    = "xhelp_departments";
$modversion['tables'][1]    = "xhelp_files";
$modversion['tables'][2]    = "xhelp_logmessages";
$modversion['tables'][3]    = "xhelp_responses";
$modversion['tables'][4]    = "xhelp_staff";
$modversion['tables'][5]    = "xhelp_staffreview";
$modversion['tables'][6]    = "xhelp_tickets";
$modversion['tables'][7]    = "xhelp_jstaffdept";
$modversion['tables'][8]    = "xhelp_responsetemplates";
$modversion['tables'][9]   = "xhelp_mimetypes";
$modversion['tables'][10]   = 'xhelp_department_mailbox';
$modversion['tables'][11]   = 'xhelp_roles';
$modversion['tables'][12]   = 'xhelp_staffroles';
$modversion['tables'][13]   = "xhelp_meta";
$modversion['tables'][14]   = "xhelp_mailevent";
$modversion['tables'][15]   = "xhelp_ticket_submit_emails";
$modversion['tables'][16]   = "xhelp_status";
$modversion['tables'][17]   = "xhelp_saved_searches";
$modversion['tables'][18]   = "xhelp_ticket_field_departments";
$modversion['tables'][19]   = "xhelp_notifications";
$modversion['tables'][20]   = "xhelp_ticket_fields";
$modversion['tables'][21]   = "xhelp_ticket_values";
$modversion['tables'][22]   = "xhelp_ticket_lists";
$modversion['tables'][23]   = "xhelp_bayes_categories";
$modversion['tables'][24]   = "xhelp_bayes_wordfreqs";
$modversion['tables'][25]   = "xhelp_ticket_solutions";

// Admin things
$modversion['hasAdmin'] = 1;
$modversion['system_menu'] = 1;
$modversion['adminindex'] = "admin/index.php";
$modversion['adminmenu'] = "admin/menu.php";

// Templates
$modversion['templates'][1]['file'] = 'xhelp_staff_header.html';
$modversion['templates'][1]['description'] = _MI_XHELP_TEMP_STAFF_HEADER;
$modversion['templates'][2]['file'] = 'xhelp_user_header.html';
$modversion['templates'][2]['description'] = _MI_XHELP_TEMP_USER_HEADER;
$modversion['templates'][3]['file'] = 'xhelp_staff_ticket_table.html';
$modversion['templates'][3]['description'] = _MI_XHELP_TEMP_STAFF_TICKET_TABLE;
$modversion['templates'][4]['file'] = 'xhelp_addTicket.html';
$modversion['templates'][4]['description'] = _MI_XHELP_TEMP_ADDTICKET;
$modversion['templates'][5]['file'] = 'xhelp_search.html';
$modversion['templates'][5]['description'] = _MI_XHELP_TEMP_SEARCH;
$modversion['templates'][6]['file'] = 'xhelp_staff_index.html';
$modversion['templates'][6]['description'] = _MI_XHELP_TEMP_STAFF_INDEX;
$modversion['templates'][7]['file'] = 'xhelp_staffReview.html';
$modversion['templates'][7]['description'] = _MI_XHELP_TEMP_STAFFREVIEW;
$modversion['templates'][8]['file'] = 'xhelp_staff_profile.html';
$modversion['templates'][8]['description'] = _MI_XHELP_TEMP_STAFF_PROFILE;
$modversion['templates'][9]['file'] = 'xhelp_staff_ticketDetails.html';
$modversion['templates'][9]['description'] = _MI_XHELP_TEMP_STAFF_TICKETDETAILS;
$modversion['templates'][10]['file'] = 'xhelp_user_index.html';
$modversion['templates'][10]['description'] = _MI_XHELP_TEMP_USER_INDEX;
$modversion['templates'][11]['file'] = 'xhelp_user_ticketDetails.html';
$modversion['templates'][11]['description'] = _MI_XHELP_TEMP_USER_TICKETDETAILS;
$modversion['templates'][12]['file'] = 'xhelp_response.html';
$modversion['templates'][12]['description'] = _MI_XHELP_TEMP_STAFF_RESPONSE;
$modversion['templates'][13]['file'] = 'xhelp_lookup.html';
$modversion['templates'][13]['description'] = _MI_XHELP_TEMP_LOOKUP;
$modversion['templates'][14]['file'] = 'xhelp_editTicket.html';
$modversion['templates'][14]['description'] = _MI_XHELP_TEMP_EDITTICKET;
$modversion['templates'][15]['file'] = 'xhelp_editResponse.html';
$modversion['templates'][15]['description'] = _MI_XHELP_TEMP_EDITRESPONSE;
$modversion['templates'][16]['file'] = 'xhelp_announcement.html';
$modversion['templates'][16]['description'] = _MI_XHELP_TEMP_ANNOUNCEMENT;
$modversion['templates'][17]['file'] = 'xhelp_print.html';
$modversion['templates'][17]['description'] = _MI_XHELP_TEMP_PRINT;
$modversion['templates'][18]['file'] = 'xhelp_staff_viewall.html';
$modversion['templates'][18]['description'] = _MI_XHELP_TEMP_STAFF_ALL;
$modversion['templates'][19]['file'] = 'xhelp_batchTickets.html';
$modversion['templates'][19]['description'] = _MI_XHELP_TEMP_BATCH_TICKETS;
$modversion['templates'][20]['file'] = 'xhelp_setdept.html';
$modversion['templates'][20]['description'] = _MI_XHELP_TEMP_SETDEPT;
$modversion['templates'][21]['file'] = 'xhelp_setpriority.html';
$modversion['templates'][21]['description'] = _MI_XHELP_TEMP_SETPRIORITY;
$modversion['templates'][22]['file'] = 'xhelp_setowner.html';
$modversion['templates'][22]['description'] = _MI_XHELP_TEMP_SETOWNER;
$modversion['templates'][23]['file'] = 'xhelp_setstatus.html';
$modversion['templates'][23]['description'] = _MI_XHELP_TEMP_SETSTATUS;
$modversion['templates'][24]['file'] = 'xhelp_deletetickets.html';
$modversion['templates'][24]['description'] = _MI_XHELP_TEMP_DELETE;
$modversion['templates'][25]['file'] = 'xhelp_batch_response.html';
$modversion['templates'][25]['description'] = _MI_XHELP_TEMP_BATCHRESPONSE;
$modversion['templates'][26]['file'] = 'xhelp_anon_addTicket.html';
$modversion['templates'][26]['description'] = _MI_XHELP_TEMP_ANON_ADDTICKET;
$modversion['templates'][27]['file'] = 'xhelp_error.html';
$modversion['templates'][27]['description'] = _MI_XHELP_TEMP_ERROR;
$modversion['templates'][28]['file'] = 'xhelp_editSearch.html';
$modversion['templates'][28]['description'] = _MI_XHELP_TEMP_EDITSEARCH;
$modversion['templates'][29]['file'] = 'xhelp_user_viewall.html';
$modversion['templates'][29]['description'] = _MI_XHELP_TEMP_USER_ALL;
$modversion['templates'][30]['file'] = 'xhelp_addFaq.html';
$modversion['templates'][30]['description'] = _MI_XHELP_TEMP_ADD_FAQ;
$modversion['templates'][31]['file'] = 'xhelp_report.html';
$modversion['templates'][31]['description'] = _MI_XHELP_TEMP_REPORT;

// Blocks
// Block that displays open tickets
$modversion['blocks'][1]['file'] = "xhelp_blocks.php";
$modversion['blocks'][1]['name'] = _MI_XHELP_BNAME1;
$modversion['blocks'][1]['description'] = _MI_XHELP_BNAME1_DESC;
$modversion['blocks'][1]['show_func'] = "b_xhelp_open_show";
$modversion['blocks'][1]['edit_func'] = "b_xhelp_actions_edit";
$modversion['blocks'][1]['options'] = "19";
$modversion['blocks'][1]['template'] = 'xhelp_block_open.html';

// Block that displays staff performance
$modversion['blocks'][2]['file'] = "xhelp_blocks.php";
$modversion['blocks'][2]['name'] = _MI_XHELP_BNAME2;
$modversion['blocks'][2]['description'] = _MI_XHELP_BNAME2_DESC;
$modversion['blocks'][2]['show_func'] = "b_xhelp_performance_show";
$modversion['blocks'][2]['template'] = 'xhelp_block_performance.html';

// Block that displays recent tickets
$modversion['blocks'][3]['file'] = 'xhelp_blocks.php';
$modversion['blocks'][3]['name'] = _MI_XHELP_BNAME3;
$modversion['blocks'][3]['description'] = _MI_XHELP_BNAME3_DESC;
$modversion['blocks'][3]['show_func'] = 'b_xhelp_recent_show';
$modversion['blocks'][3]['template'] = 'xhelp_block_recent.html';

// Block that displays recent tickets
$modversion['blocks'][4]['file'] = 'xhelp_blocks.php';
$modversion['blocks'][4]['name'] = _MI_XHELP_BNAME4;
$modversion['blocks'][4]['description'] = _MI_XHELP_BNAME4_DESC;
$modversion['blocks'][4]['show_func'] = 'b_xhelp_actions_show';
$modversion['blocks'][4]['template'] = 'xhelp_block_actions.html';

// Block that displays main actions
$modversion['blocks'][5]['file'] = 'xhelp_blocks.php';
$modversion['blocks'][5]['name'] = _MI_XHELP_BNAME5;
$modversion['blocks'][5]['description'] = _MI_XHELP_BNAME5_DESC;
$modversion['blocks'][5]['show_func'] = 'b_xhelp_mainactions_show';
$modversion['blocks'][5]['template'] = 'xhelp_block_mainactions.html';
$modversion['blocks'][5]['edit_func'] = 'b_xhelp_mainactions_edit';
$modversion['blocks'][5]['options'] = '0|1';

// Menu
global $xhelp_isStaff;
$modversion['hasMain'] = 1;
$modversion['sub'][1]['name'] = _MI_XHELP_SMNAME1;
$modversion['sub'][1]['url'] = "index.php";
$modversion['sub'][2]['name'] = _MI_XHELP_SMNAME2;
$modversion['sub'][2]['url'] = "addTicket.php";
if($xhelp_isStaff){
    $modversion['sub'][3]['name'] = _MI_XHELP_SMNAME3;
    $modversion['sub'][3]['url'] = "profile.php";
    $modversion['sub'][4]['name'] = _MI_XHELP_SMNAME4;
    $modversion['sub'][4]['url'] = "index.php?op=staffViewAll";
    $modversion['sub'][5]['name'] = _MI_XHELP_SMNAME5;
    $modversion['sub'][5]['url'] = "search.php";
    $modversion['sub'][6]['name'] = _MI_XHELP_SMNAME6;
    $modversion['sub'][6]['url'] = "report.php";
} else {
    $modversion['sub'][4]['name'] = _MI_XHELP_SMNAME4;
    $modversion['sub'][4]['url'] = "index.php?op=userViewAll";
}

// Search
$modversion['hasSearch'] = 0;

// On Install
$modversion['onInstall'] = "install.php";

// Config
$modversion['config'][1]['name'] = 'xhelp_allowUpload';     // Allows users to upload files when adding a ticket
$modversion['config'][1]['title'] = '_MI_XHELP_ALLOW_UPLOAD';
$modversion['config'][1]['description'] = '_MI_XHELP_ALLOW_UPLOAD_DSC';
$modversion['config'][1]['formtype'] = 'yesno';
$modversion['config'][1]['valuetype'] = 'int';
$modversion['config'][1]['default'] = 1;

$modversion['config'][2]['name'] = 'xhelp_uploadSize';      // Size of file upload allowed
$modversion['config'][2]['title'] = '_MI_XHELP_UPLOAD_SIZE';
$modversion['config'][2]['description'] = '_MI_XHELP_UPLOAD_SIZE_DSC';
$modversion['config'][2]['formtype'] = 'textbox';
$modversion['config'][2]['valuetype'] = 'string';
$modversion['config'][2]['default'] = '50000';

$modversion['config'][3]['name'] = 'xhelp_uploadWidth';      // Max width for upload
$modversion['config'][3]['title'] = '_MI_XHELP_UPLOAD_WIDTH';
$modversion['config'][3]['description'] = '_MI_XHELP_UPLOAD_WIDTH_DSC';
$modversion['config'][3]['formtype'] = 'textbox';
$modversion['config'][3]['valuetype'] = 'string';
$modversion['config'][3]['default'] = '300';

$modversion['config'][4]['name'] = 'xhelp_uploadHeight';      // Max height for upload
$modversion['config'][4]['title'] = '_MI_XHELP_UPLOAD_HEIGHT';
$modversion['config'][4]['description'] = '_MI_XHELP_UPLOAD_HEIGHT_DSC';
$modversion['config'][4]['formtype'] = 'textbox';
$modversion['config'][4]['valuetype'] = 'string';
$modversion['config'][4]['default'] = '300';

$modversion['config'][5]['name'] = 'xhelp_numTicketUploads';      // Number of ticket uploads allowed
$modversion['config'][5]['title'] = '_MI_XHELP_NUM_TICKET_UPLOADS';
$modversion['config'][5]['description'] = '_MI_XHELP_NUM_TICKET_UPLOADS_DSC';
$modversion['config'][5]['formtype'] = 'textbox';
$modversion['config'][5]['valuetype'] = 'int';
$modversion['config'][5]['default'] = '1';

$modversion['config'][6]['name'] = 'xhelp_allowReopen';     // Allows users to reopen tickets
$modversion['config'][6]['title'] = '_MI_XHELP_ALLOW_REOPEN';
$modversion['config'][6]['description'] = '_MI_XHELP_ALLOW_REOPEN_DSC';
$modversion['config'][6]['formtype'] = 'yesno';
$modversion['config'][6]['valuetype'] = 'int';
$modversion['config'][6]['default'] = 1;

$modversion['config'][7]['name'] = 'xhelp_announcements';   // Name of topic for announcments
$modversion['config'][7]['title'] = '_MI_XHELP_ANNOUNCEMENTS';
$modversion['config'][7]['description'] = '_MI_XHELP_ANNOUNCEMENTS_DSC';
$modversion['config'][7]['formtype'] = 'select';
$modversion['config'][7]['valuetype'] = 'string';
$modversion['config'][7]['default'] = '';

$modversion['config'][8]['name'] = 'xhelp_staffTicketCount';
$modversion['config'][8]['title'] = '_MI_XHELP_STAFF_TC';
$modversion['config'][8]['description'] = '_MI_XHELP_STAFF_TC_DSC';
$modversion['config'][8]['formtype'] = 'select';
$modversion['config'][8]['valuetype'] = 'int';
$modversion['config'][8]['default'] = 10;
$modversion['config'][8]['options'] = array('5' => 5, '10' => 10, '15' => 15, '20' => 20);

$modversion['config'][9]['name'] = 'xhelp_staffTicketActions';
$modversion['config'][9]['title'] = '_MI_XHELP_STAFF_ACTIONS';
$modversion['config'][9]['description'] = '_MI_XHELP_STAFF_ACTIONS_DSC';
$modversion['config'][9]['formtype'] = 'select';
$modversion['config'][9]['valuetype'] = 'int';
$modversion['config'][9]['default'] = 1;
$modversion['config'][9]['options'] = array(_MI_XHELP_ACTION1 => 1, _MI_XHELP_ACTION2 => 2);

$modversion['config'][10]['name'] = 'xhelp_overdueTime';
$modversion['config'][10]['title'] = '_MI_XHELP_OVERDUE_TIME';
$modversion['config'][10]['description'] = '_MI_XHELP_OVERDUE_TIME_DSC';
$modversion['config'][10]['formtype'] = 'textbox';
$modversion['config'][10]['valuetype'] = 'int';
$modversion['config'][10]['default'] = 0;

$modversion['config'][11]['name'] = 'xhelp_allowAnonymous';     // Allows anonymous user submission
$modversion['config'][11]['title'] = '_MI_XHELP_ALLOW_ANON';
$modversion['config'][11]['description'] = '_MI_XHELP_ALLOW_ANON_DSC';
$modversion['config'][11]['formtype'] = 'yesno';
$modversion['config'][11]['valuetype'] = 'int';
$modversion['config'][11]['default'] = 0;

$modversion['config'][12]['name'] = 'xhelp_deptVisibility';     // Apply dept visibility to staff members
$modversion['config'][12]['title'] = '_MI_XHELP_APPLY_VISIBILITY';
$modversion['config'][12]['description'] = '_MI_XHELP_APPLY_VISIBILITY_DSC';
$modversion['config'][12]['formtype'] = 'yesno';
$modversion['config'][12]['valuetype'] = 'int';
$modversion['config'][12]['default'] = 0;

$modversion['config'][13]['name'] = 'xhelp_displayName';
$modversion['config'][13]['title'] = '_MI_XHELP_DISPLAY_NAME';
$modversion['config'][13]['description'] = '_MI_XHELP_DISPLAY_NAME_DSC';
$modversion['config'][13]['formtype'] = 'select';
$modversion['config'][13]['valuetype'] = 'int';
$modversion['config'][13]['default'] = 1;
$modversion['config'][13]['options'] = array(_MI_XHELP_USERNAME => 1, _MI_XHELP_REALNAME => 2);

// Email templates
$modversion['_email_tpl'][1]['name'] = 'new_ticket';        // Add ticket
$modversion['_email_tpl'][1]['category'] = 'dept';
$modversion['_email_tpl'][1]['mail_template'] = 'dept_newticket_notify';
$modversion['_email_tpl'][1]['mail_subject'] = _MI_XHELP_DEPT_NEWTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][1]['bit_value'] = 0;
$modversion['_email_tpl'][1]['title'] = _MI_XHELP_DEPT_NEWTICKET_NOTIFY;
$modversion['_email_tpl'][1]['caption'] = _MI_XHELP_DEPT_NEWTICKET_NOTIFYCAP;
$modversion['_email_tpl'][1]['description'] = _MI_XHELP_DEPT_NEWTICKET_NOTIFYDSC;

$modversion['_email_tpl'][2]['name'] = 'removed_ticket';    // Delete ticket
$modversion['_email_tpl'][2]['category'] = 'dept';
$modversion['_email_tpl'][2]['mail_template'] = 'dept_removedticket_notify';
$modversion['_email_tpl'][2]['mail_subject'] = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][2]['bit_value'] = 1;
$modversion['_email_tpl'][2]['title'] = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFY;
$modversion['_email_tpl'][2]['caption'] = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][2]['description'] = _MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][3]['name'] = 'modified_ticket';   // Edit ticket information
$modversion['_email_tpl'][3]['category'] = 'dept';
$modversion['_email_tpl'][3]['mail_template'] = 'dept_modifiedticket_notify';
$modversion['_email_tpl'][3]['mail_subject'] = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][3]['bit_value'] = 2;
$modversion['_email_tpl'][3]['title'] = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFY;
$modversion['_email_tpl'][3]['caption'] = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][3]['description'] = _MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][4]['name'] = 'new_response';
$modversion['_email_tpl'][4]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][4]['mail_template'] = 'dept_newresponse_notify';
$modversion['_email_tpl'][4]['mail_subject'] = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][4]['bit_value'] = 3;
$modversion['_email_tpl'][4]['title'] = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFY;
$modversion['_email_tpl'][4]['caption'] = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][4]['description'] = _MI_XHELP_DEPT_NEWRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][5]['name'] = 'modified_response';
$modversion['_email_tpl'][5]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][5]['mail_template'] = 'dept_modifiedresponse_notify';
$modversion['_email_tpl'][5]['mail_subject'] = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][5]['bit_value'] = 4;
$modversion['_email_tpl'][5]['title'] = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFY;
$modversion['_email_tpl'][5]['caption'] = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][5]['description'] = _MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][6]['name'] = 'changed_status';     // Update status
$modversion['_email_tpl'][6]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][6]['mail_template'] = 'dept_changedstatus_notify';
$modversion['_email_tpl'][6]['mail_subject'] = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYSBJ;
$modversion['_email_tpl'][6]['bit_value'] = 5;
$modversion['_email_tpl'][6]['title'] = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFY;
$modversion['_email_tpl'][6]['caption'] = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYCAP;
$modversion['_email_tpl'][6]['description'] = _MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYDSC;

$modversion['_email_tpl'][7]['name'] = 'changed_priority';     // Update priority
$modversion['_email_tpl'][7]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][7]['mail_template'] = 'dept_changedpriority_notify';
$modversion['_email_tpl'][7]['mail_subject'] = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYSBJ;
$modversion['_email_tpl'][7]['bit_value'] = 6;
$modversion['_email_tpl'][7]['title'] = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFY;
$modversion['_email_tpl'][7]['caption'] = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYCAP;
$modversion['_email_tpl'][7]['description'] = _MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYDSC;

$modversion['_email_tpl'][8]['name'] = 'new_owner';
$modversion['_email_tpl'][8]['category'] = 'dept';         // All tickets
$modversion['_email_tpl'][8]['mail_template'] = 'dept_newowner_notify';
$modversion['_email_tpl'][8]['mail_subject'] = _MI_XHELP_DEPT_NEWOWNER_NOTIFYSBJ;
$modversion['_email_tpl'][8]['bit_value'] = 7;
$modversion['_email_tpl'][8]['title'] = _MI_XHELP_DEPT_NEWOWNER_NOTIFY;
$modversion['_email_tpl'][8]['caption'] = _MI_XHELP_DEPT_NEWOWNER_NOTIFYCAP;
$modversion['_email_tpl'][8]['description'] = _MI_XHELP_DEPT_NEWOWNER_NOTIFYDSC;

$modversion['_email_tpl'][9]['name'] = 'close_ticket';        // Close ticket
$modversion['_email_tpl'][9]['category'] = 'dept';
$modversion['_email_tpl'][9]['mail_template'] = 'dept_closeticket_notify';
$modversion['_email_tpl'][9]['mail_subject'] = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYSBJ;
$modversion['_email_tpl'][9]['bit_value'] = 8;
$modversion['_email_tpl'][9]['title'] = _MI_XHELP_DEPT_CLOSETICKET_NOTIFY;
$modversion['_email_tpl'][9]['caption'] = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYCAP;
$modversion['_email_tpl'][9]['description'] = _MI_XHELP_DEPT_CLOSETICKET_NOTIFYDSC;

$modversion['_email_tpl'][10]['name'] = 'merge_ticket';
$modversion['_email_tpl'][10]['category'] = 'dept';
$modversion['_email_tpl'][10]['mail_template'] = 'dept_mergeticket_notify';
$modversion['_email_tpl'][10]['mail_subject'] = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYSBJ;
$modversion['_email_tpl'][10]['bit_value'] = 9;
$modversion['_email_tpl'][10]['title'] = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFY;
$modversion['_email_tpl'][10]['caption'] = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYCAP;
$modversion['_email_tpl'][10]['description'] = _MI_XHELP_DEPT_MERGE_TICKET_NOTIFYDSC;

$modversion['_email_tpl'][11]['name'] = 'new_this_owner';
$modversion['_email_tpl'][11]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][11]['mail_template'] = 'ticket_newowner_notify';
$modversion['_email_tpl'][11]['mail_subject'] = _MI_XHELP_TICKET_NEWOWNER_NOTIFYSBJ;
$modversion['_email_tpl'][11]['bit_value'] = 10;
$modversion['_email_tpl'][11]['title'] = _MI_XHELP_TICKET_NEWOWNER_NOTIFY;
$modversion['_email_tpl'][11]['caption'] = _MI_XHELP_TICKET_NEWOWNER_NOTIFYCAP;
$modversion['_email_tpl'][11]['description'] = _MI_XHELP_TICKET_NEWOWNER_NOTIFYDSC;

$modversion['_email_tpl'][12]['name'] = 'removed_this_ticket';
$modversion['_email_tpl'][12]['category'] = 'ticket';        // Individual ticket
$modversion['_email_tpl'][12]['mail_template'] = 'ticket_removedticket_notify';
$modversion['_email_tpl'][12]['mail_subject'] = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][12]['bit_value'] = 11;
$modversion['_email_tpl'][12]['title'] = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFY;
$modversion['_email_tpl'][12]['caption'] = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][12]['description'] = _MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][13]['name'] = 'modified_this_ticket';
$modversion['_email_tpl'][13]['category'] = 'ticket';        // Individual ticket
$modversion['_email_tpl'][13]['mail_template'] = 'ticket_modifiedticket_notify';
$modversion['_email_tpl'][13]['mail_subject'] = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][13]['bit_value'] = 12;
$modversion['_email_tpl'][13]['title'] = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFY;
$modversion['_email_tpl'][13]['caption'] = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYCAP;
$modversion['_email_tpl'][13]['description'] = _MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYDSC;

$modversion['_email_tpl'][14]['name'] = 'new_this_response';
$modversion['_email_tpl'][14]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][14]['mail_template'] = 'ticket_newresponse_notify';
$modversion['_email_tpl'][14]['mail_subject'] = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][14]['bit_value'] = 13;
$modversion['_email_tpl'][14]['title'] = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFY;
$modversion['_email_tpl'][14]['caption'] = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][14]['description'] = _MI_XHELP_TICKET_NEWRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][15]['name'] = 'modified_this_response';
$modversion['_email_tpl'][15]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][15]['mail_template'] = 'ticket_modifiedresponse_notify';
$modversion['_email_tpl'][15]['mail_subject'] = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYSBJ;
$modversion['_email_tpl'][15]['bit_value'] = 14;
$modversion['_email_tpl'][15]['title'] = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFY;
$modversion['_email_tpl'][15]['caption'] = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYCAP;
$modversion['_email_tpl'][15]['description'] = _MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYDSC;

$modversion['_email_tpl'][16]['name'] = 'changed_this_status';     // Update status
$modversion['_email_tpl'][16]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][16]['mail_template'] = 'ticket_changedstatus_notify';
$modversion['_email_tpl'][16]['mail_subject'] = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYSBJ;
$modversion['_email_tpl'][16]['bit_value'] = 15;
$modversion['_email_tpl'][16]['title'] = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFY;
$modversion['_email_tpl'][16]['caption'] = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYCAP;
$modversion['_email_tpl'][16]['description'] = _MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYDSC;

$modversion['_email_tpl'][17]['name'] = 'changed_this_priority';     // Update priority
$modversion['_email_tpl'][17]['category'] = 'ticket';         // Individual ticket
$modversion['_email_tpl'][17]['mail_template'] = 'ticket_changedpriority_notify';
$modversion['_email_tpl'][17]['mail_subject'] = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYSBJ;
$modversion['_email_tpl'][17]['bit_value'] = 16;
$modversion['_email_tpl'][17]['title'] = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFY;
$modversion['_email_tpl'][17]['caption'] = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYCAP;
$modversion['_email_tpl'][17]['description'] = _MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYDSC;

$modversion['_email_tpl'][18]['name'] = 'new_this_ticket';        // Add ticket
$modversion['_email_tpl'][18]['category'] = 'ticket';
$modversion['_email_tpl'][18]['mail_template'] = 'ticket_newticket_notify';
$modversion['_email_tpl'][18]['mail_subject'] = _MI_XHELP_TICKET_NEWTICKET_NOTIFYSBJ;
$modversion['_email_tpl'][18]['bit_value'] = 17;
$modversion['_email_tpl'][18]['title'] = _MI_XHELP_TICKET_NEWTICKET_NOTIFY;
$modversion['_email_tpl'][18]['caption'] = _MI_XHELP_TICKET_NEWTICKET_NOTIFYCAP;
$modversion['_email_tpl'][18]['description'] = _MI_XHELP_TICKET_NEWTICKET_NOTIFYDSC;

$modversion['_email_tpl'][19]['name'] = 'close_this_ticket';        // Close ticket
$modversion['_email_tpl'][19]['category'] = 'ticket';
$modversion['_email_tpl'][19]['mail_template'] = 'ticket_closeticket_notify';
$modversion['_email_tpl'][19]['mail_subject'] = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYSBJ;
$modversion['_email_tpl'][19]['bit_value'] = 18;
$modversion['_email_tpl'][19]['title'] = _MI_XHELP_TICKET_CLOSETICKET_NOTIFY;
$modversion['_email_tpl'][19]['caption'] = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYCAP;
$modversion['_email_tpl'][19]['description'] = _MI_XHELP_TICKET_CLOSETICKET_NOTIFYDSC;

$modversion['_email_tpl'][20]['name'] = 'new_this_ticket_via_email';        // Add ticket  via email
$modversion['_email_tpl'][20]['category'] = 'ticket';
$modversion['_email_tpl'][20]['mail_template'] = 'ticket_newticket_byemail_notify';
$modversion['_email_tpl'][20]['mail_subject'] = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYSBJ;
$modversion['_email_tpl'][20]['bit_value'] = 19;
$modversion['_email_tpl'][20]['title'] = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFY;
$modversion['_email_tpl'][20]['caption'] = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYCAP;
$modversion['_email_tpl'][20]['description'] = _MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYDSC;

$modversion['_email_tpl'][21]['name'] = 'new_user_byemail';        // Add ticket  via email
$modversion['_email_tpl'][21]['category'] = 'ticket';
$modversion['_email_tpl'][21]['mail_template'] = 'ticket_new_user_byemail';
$modversion['_email_tpl'][21]['mail_subject'] = _MI_XHELP_TICKET_NEWUSER_NOTIFYSBJ;
$modversion['_email_tpl'][21]['bit_value'] = 20;
$modversion['_email_tpl'][21]['title'] = _MI_XHELP_TICKET_NEWUSER_NOTIFY;
$modversion['_email_tpl'][21]['caption'] = _MI_XHELP_TICKET_NEWUSER_NOTIFYCAP;
$modversion['_email_tpl'][21]['description'] = _MI_XHELP_TICKET_NEWUSER_NOTIFYDSC;

$modversion['_email_tpl'][22]['name'] = 'new_user_activation1';
$modversion['_email_tpl'][22]['category'] = 'ticket';
$modversion['_email_tpl'][22]['mail_template'] = 'ticket_new_user_activation1';
$modversion['_email_tpl'][22]['mail_subject'] = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYSBJ;
$modversion['_email_tpl'][22]['bit_value'] = 21;
$modversion['_email_tpl'][22]['title'] = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFY;
$modversion['_email_tpl'][22]['caption'] = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYCAP;
$modversion['_email_tpl'][22]['description'] = _MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYDSC;

$modversion['_email_tpl'][23]['name'] = 'new_user_activation2';
$modversion['_email_tpl'][23]['category'] = 'ticket';
$modversion['_email_tpl'][23]['mail_template'] = 'ticket_new_user_activation2';
$modversion['_email_tpl'][23]['mail_subject'] = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYSBJ;
$modversion['_email_tpl'][23]['bit_value'] = 22;
$modversion['_email_tpl'][23]['title'] = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFY;
$modversion['_email_tpl'][23]['caption'] = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYCAP;
$modversion['_email_tpl'][23]['description'] = _MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYDSC;

$modversion['_email_tpl'][24]['name'] = 'user_email_error';
$modversion['_email_tpl'][24]['category'] = 'ticket';
$modversion['_email_tpl'][24]['mail_template'] = 'ticket_user_email_error';
$modversion['_email_tpl'][24]['mail_subject'] = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYSBJ;
$modversion['_email_tpl'][24]['bit_value'] = 23;
$modversion['_email_tpl'][24]['title'] = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFY;
$modversion['_email_tpl'][24]['caption'] = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYCAP;
$modversion['_email_tpl'][24]['description'] = _MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYDSC;

$modversion['_email_tpl'][25]['name'] = 'merge_this_ticket';
$modversion['_email_tpl'][25]['category'] = 'ticket';
$modversion['_email_tpl'][25]['mail_template'] = 'ticket_mergeticket_notify';
$modversion['_email_tpl'][25]['mail_subject'] = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYSBJ;
$modversion['_email_tpl'][25]['bit_value'] = 24;
$modversion['_email_tpl'][25]['title'] = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFY;
$modversion['_email_tpl'][25]['caption'] = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYCAP;
$modversion['_email_tpl'][25]['description'] = _MI_XHELP_TICKET_MERGE_TICKET_NOTIFYDSC;
