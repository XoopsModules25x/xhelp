<?php
//$Id: modinfo.php,v 1.75 2006/01/04 14:59:31 eric_juden Exp $
define('_MI_XHELP_NAME','xhelp');
define('_MI_XHELP_DESC','Used to store client requests for help with their problems');

//Template variables
define('_MI_XHELP_TEMP_ADDTICKET','Template for addTicket.php');
define('_MI_XHELP_TEMP_SEARCH','Template for search.php');
define('_MI_XHELP_TEMP_STAFF_INDEX','Staff template for index.php');
define('_MI_XHELP_TEMP_STAFF_PROFILE','Template for profile.php');
define('_MI_XHELP_TEMP_STAFF_TICKETDETAILS','Staff template for ticket.php');
define('_MI_XHELP_TEMP_USER_INDEX','User template for index.php');
define('_MI_XHELP_TEMP_USER_TICKETDETAILS','User template for ticket.php');
define('_MI_XHELP_TEMP_STAFF_RESPONSE','Template for response.php');
define('_MI_XHELP_TEMP_LOOKUP','Template for lookup.php');
define('_MI_XHELP_TEMP_STAFFREVIEW','Template for reviewing a staff member');
define('_MI_XHELP_TEMP_EDITTICKET','Template for editing a ticket');
define('_MI_XHELP_TEMP_EDITRESPONSE','Template for editing a response');
define('_MI_XHELP_TEMP_ANNOUNCEMENT','Template for announcements');
define('_MI_XHELP_TEMP_STAFF_HEADER','Template for staff menu options');
define('_MI_XHELP_TEMP_USER_HEADER','Template for user menu options');
define('_MI_XHELP_TEMP_PRINT','Template for printer-friendly ticket page');
define('_MI_XHELP_TEMP_STAFF_ALL','Template for Staff View All Page');
define('_MI_XHELP_TEMP_STAFF_TICKET_TABLE','Template to display staff tickets');
define('_MI_XHELP_TEMP_SETDEPT','Template for Set Department Page');
define('_MI_XHELP_TEMP_SETPRIORITY','Template for Set Priority Page');
define('_MI_XHELP_TEMP_SETOWNER','Template for Set Owner Page');
define('_MI_XHELP_TEMP_SETSTATUS','Template for Set Status Page');
define('_MI_XHELP_TEMP_DELETE','Template for Batch Ticket Delete Page');
define('_MI_XHELP_TEMP_BATCHRESPONSE','Template for Batch Add Response Page');
define('_MI_XHELP_TEMP_ANON_ADDTICKET','Template for anonymous user add ticket page');
define('_MI_XHELP_TEMP_ERROR','Template for error page');
define('_MI_XHELP_TEMP_EDITSEARCH','Template for editing a saved search.');
define('_MI_XHELP_TEMP_USER_ALL','Template for user view all page');
define('_MI_XHELP_TEMP_ADD_FAQ','Template for adding an faq item.');
define('_MI_XHELP_TEMP_BATCH_TICKETS','Template for displaying batch tickets.');
define('_MI_XHELP_TEMP_REPORT','Template for reporting main page.');

// Block variables
define('_MI_XHELP_BNAME1','My Open Tickets');
define('_MI_XHELP_BNAME1_DESC','Displays a list of the user\'s open tickets');
define('_MI_XHELP_BNAME2','Department Tickets');
define('_MI_XHELP_BNAME2_DESC','Displays the number of open tickets for each department.');
define('_MI_XHELP_BNAME3','Recently Viewed Tickets');
define('_MI_XHELP_BNAME3_DESC','Displays the tickets a staff member has recently viewed.');
define('_MI_XHELP_BNAME4','Ticket Actions');
define('_MI_XHELP_BNAME4_DESC','Displays all actions a staff member can do to a ticket');
define('_MI_XHELP_BNAME5','Ticket Main Actions');
define('_MI_XHELP_BNAME5_DESC','Displays main actions for ticketing system');

// Submenu
define('_MI_XHELP_SMNAME1','Summary');
define('_MI_XHELP_SMNAME2','Log Ticket');
define('_MI_XHELP_SMNAME3','My Profile');
define('_MI_XHELP_SMNAME4','View All Tickets');
define('_MI_XHELP_SMNAME5','Search');
define('_MI_XHELP_SMNAME6','View Reports');

// Config variables
define('_MI_XHELP_TITLE','Helpdesk Title');
define('_MI_XHELP_TITLE_DSC','Give your helpdesk a name:');
define('_MI_XHELP_UPLOAD','Upload Directory');
define('_MI_XHELP_UPLOAD_DSC','Path where user stores files that are added to a ticket');
define('_MI_XHELP_ALLOW_UPLOAD','Allow Uploads');
define('_MI_XHELP_ALLOW_UPLOAD_DSC','Allow users to add a file to their ticket requests?');
define('_MI_XHELP_UPLOAD_SIZE','Upload Size');
define('_MI_XHELP_UPLOAD_SIZE_DSC','Max size of upload (in bytes)');
define('_MI_XHELP_UPLOAD_WIDTH','Upload Width');
define('_MI_XHELP_UPLOAD_WIDTH_DSC','Max width of upload (in pixels)');
define('_MI_XHELP_UPLOAD_HEIGHT','Upload Height');
define('_MI_XHELP_UPLOAD_HEIGHT_DSC','Max height of upload (in pixels)');
define('_MI_XHELP_NUM_TICKET_UPLOADS','Max Number of Files to Upload');
define('_MI_XHELP_NUM_TICKET_UPLOADS_DSC','This is the maximum number of files that can be uploaded to a ticket on ticket submission (does not include file custom fields).');
define('_MI_XHELP_ANNOUNCEMENTS','Announcements News Topic');
//define('_MI_XHELP_ANNOUNCEMENTS_DSC','This is the news topic that pulls announcements for xhelp. Update the xHelp module to see newly added news categories');
define('_MI_XHELP_ANNOUNCEMENTS_DSC',"This is the news topic that pulls announcements for xhelp. <a href='javascript:openWithSelfMain(\"".XOOPS_URL."/modules/xhelp/install.php?op=updateTopics\", \"xoops_module_install_xhelp\",400, 300);'>Click here</a> to update the news categories.");
define('_MI_XHELP_ANNOUNCEMENTS_NONE','***Disable announcements***');
define('_MI_XHELP_ALLOW_REOPEN','Allow Ticket Re-open');
define('_MI_XHELP_ALLOW_REOPEN_DSC','Allow users to re-open a ticket after it has been closed?');
define('_MI_XHELP_STAFF_TC','Staff Index Ticket Count');
define('_MI_XHELP_STAFF_TC_DSC','How many tickets should be displayed for each section on the staff index page?');
define('_MI_XHELP_STAFF_ACTIONS','Staff Actions');
define('_MI_XHELP_STAFF_ACTIONS_DSC','What style would you like the staff actions to show up as? Inline is the default, Block-Style requires you to enable the Staff Actions block.');
define('_MI_XHELP_ACTION1','Inline-Style');
define('_MI_XHELP_ACTION2','Block-Style');
define('_MI_XHELP_DEFAULT_DEPT','Default Department');
define('_MI_XHELP_DEFAULT_DEPT_DSC',"This will be the default department that is selected in the list when adding a ticket. <a href='javascript:openWithSelfMain(\"".XOOPS_URL."/modules/xhelp/install.php?op=updateDepts\", \"xoops_module_install_xhelp\",400, 300);'>Click here</a> to update the departments.");
define('_MI_XHELP_OVERDUE_TIME','Ticket Overdue Time');
define('_MI_XHELP_OVERDUE_TIME_DSC','This determines how long the staff have to finish a ticket before it is late (in hours).');
define('_MI_XHELP_ALLOW_ANON','Allow Anonymous User Ticket Submission');
define('_MI_XHELP_ALLOW_ANON_DSC','This allows anyone to create a ticket on your site. When an anonymous user submits a ticket, they are also prompted to create an account.');
define('_MI_XHELP_APPLY_VISIBILITY','Apply department visibility to staff members?');
define('_MI_XHELP_APPLY_VISIBILITY_DSC','This determines if staff are limited to what departments they can submit tickets to. If "yes" is selected, staff members will be limited to submitting tickets to departments where the XOOPS group they belong to is selected.');
define('_MI_XHELP_DISPLAY_NAME','Display username or real name?');
define('_MI_XHELP_DISPLAY_NAME_DSC','This allows for the real name to be shown in all places where the username would normally be (username will display if there is no real name).');
define('_MI_XHELP_USERNAME','Username');
define('_MI_XHELP_REALNAME','Real Name');

// Admin Menu variables
define('_MI_XHELP_MENU_BLOCKS','Manage Blocks');
define('_MI_XHELP_MENU_MANAGE_DEPARTMENTS','Manage Departments');
define('_MI_XHELP_MENU_MANAGE_STAFF','Manage Staff');
define('_MI_XHELP_MENU_MODIFY_EMLTPL','Modify Email Templates');
define('_MI_XHELP_MENU_MODIFY_TICKET_FIELDS','Modify Ticket Fields');
define('_MI_XHELP_MENU_GROUP_PERM','Group Permissions');
define('_MI_XHELP_MENU_ADD_STAFF','Add Staff');
define('_MI_XHELP_MENU_MIMETYPES','Mimetype Management');
define('_MI_XHELP_MENU_CHECK_TABLES','Check Tables');
define('_MI_XHELP_MENU_MANAGE_ROLES','Manage Roles');
define('_MI_XHELP_MENU_MAIL_EVENTS','Mail Events');
define('_MI_XHELP_MENU_CHECK_EMAIL','Check Email');
define('_MI_XHELP_MENU_MANAGE_FILES','Manage Files');
define('_MI_XHELP_ADMIN_ABOUT','About');
define('_MI_XHELP_TEXT_MANAGE_STATUSES','Manage Statuses');
define('_MI_XHELP_TEXT_MANAGE_FIELDS','Custom Fields');
define('_MI_XHELP_TEXT_NOTIFICATIONS','Manage Notifications');
define('_MI_XHELP_TEXT_MANAGE_FAQ','Manage FAQ');

//NOTIFICATION vars
define('_MI_XHELP_DEPT_NOTIFY','Department');
define('_MI_XHELP_DEPT_NOTIFYDSC','Notification options that apply to a certain department');

define('_MI_XHELP_TICKET_NOTIFY','Ticket');
define('_MI_XHELP_TICKET_NOTIFYDSC','Notification options that apply to the current ticket');

define('_MI_XHELP_DEPT_NEWTICKET_NOTIFY','Staff: New Ticket');
define('_MI_XHELP_DEPT_NEWTICKET_NOTIFYCAP','Notify me when tickets get created');
define('_MI_XHELP_DEPT_NEWTICKET_NOTIFYDSC','Receive notification when a new ticket is created');
define('_MI_XHELP_DEPT_NEWTICKET_NOTIFYSBJ','{X_MODULE} Ticket created - id {TICKET_ID}');
define('_MI_XHELP_DEPT_NEWTICKET_NOTIFYTPL','dept_newticket_notify.tpl');

define('_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFY','Staff: Delete Ticket');
define('_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYCAP','Notify me when tickets get deleted');
define('_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYDSC','Receive notification when a ticket is deleted');
define('_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYSBJ','{X_MODULE} Ticket deleted - id {TICKET_ID}');
define('_MI_XHELP_DEPT_REMOVEDTICKET_NOTIFYTPL','dept_removedticket_notify.tpl');

define('_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFY','Staff: Modified Ticket');
define('_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYCAP','Notify me when tickets get modified');
define('_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYDSC','Receive notification when a ticket is deleted');
define('_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYSBJ','{X_MODULE} Ticket modified - id {TICKET_ID}');
define('_MI_XHELP_DEPT_MODIFIEDTICKET_NOTIFYTPL','dept_modifiedticket_notify.tpl');

define('_MI_XHELP_DEPT_NEWRESPONSE_NOTIFY','Staff: New Response');
define('_MI_XHELP_DEPT_NEWRESPONSE_NOTIFYCAP','Notify me for new responses on my tickets');
define('_MI_XHELP_DEPT_NEWRESPONSE_NOTIFYDSC','Receive notification when a response is created');
define('_MI_XHELP_DEPT_NEWRESPONSE_NOTIFYSBJ','{X_MODULE} Ticket response added - id {TICKET_ID}');
define('_MI_XHELP_DEPT_NEWRESPONSE_NOTIFYTPL','dept_newresponse_notify.tpl');

define('_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFY','Staff: Modified Response');
define('_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYCAP','Notify me when responses are modified');
define('_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYDSC','Receive notification when a response is modified');
define('_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYSBJ','{X_MODULE} Ticket response modified - id {TICKET_ID}');
define('_MI_XHELP_DEPT_MODIFIEDRESPONSE_NOTIFYTPL','dept_modifiedresponse_notify.tpl');

define('_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFY','Staff: Changed Ticket Status');
define('_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYCAP','Notify me when the status of tickets changes');
define('_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYDSC','Receive notification when the status of a ticket is changed');
define('_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYSBJ','{X_MODULE} Ticket status changed - id {TICKET_ID}');
define('_MI_XHELP_DEPT_CHANGEDSTATUS_NOTIFYTPL','dept_changedstatus_notify.tpl');

define('_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFY','Staff: Changed Ticket Priority');
define('_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYCAP','Notify me when the priority of tickets changes');
define('_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYDSC','Receive notification when the priority of a ticket is changed');
define('_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYSBJ','{X_MODULE} Ticket priority changed - id {TICKET_ID}');
define('_MI_XHELP_DEPT_CHANGEDPRIORITY_NOTIFYTPL','dept_changedpriority_notify.tpl');

define('_MI_XHELP_DEPT_NEWOWNER_NOTIFY','Staff: New Ticket Owner');
define('_MI_XHELP_DEPT_NEWOWNER_NOTIFYCAP','Notify me when I gain or lose ownership of a ticket');
define('_MI_XHELP_DEPT_NEWOWNER_NOTIFYDSC','Receive notification when ownership of a ticket is changed');
define('_MI_XHELP_DEPT_NEWOWNER_NOTIFYSBJ','{X_MODULE} Ticket ownership changed - id {TICKET_ID}');
define('_MI_XHELP_DEPT_NEWOWNER_NOTIFYTPL','dept_newowner_notify.tpl');

define('_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFY','User: Ticket Deleted');
define('_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYCAP','Notify me when this ticket is deleted');
define('_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYDSC','Receive notification when this ticket is deleted');
define('_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYSBJ','{X_MODULE} Ticket Deleted - id {TICKET_ID}');
define('_MI_XHELP_TICKET_REMOVEDTICKET_NOTIFYTPL','ticket_removedticket_notify.tpl');

define('_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFY','User: Ticket Modified');
define('_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYCAP','Notify me when this ticket is modified');
define('_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYDSC','Receive notification when this ticket is modified');
define('_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYSBJ','{X_MODULE} Ticket modified - id {TICKET_ID}');
define('_MI_XHELP_TICKET_MODIFIEDTICKET_NOTIFYTPL','ticket_modifiedticket_notify.tpl');

define('_MI_XHELP_TICKET_NEWRESPONSE_NOTIFY','User: New Response');
define('_MI_XHELP_TICKET_NEWRESPONSE_NOTIFYCAP','Notify me when a response is created for this ticket');
define('_MI_XHELP_TICKET_NEWRESPONSE_NOTIFYDSC','Receive notification when a response is created for this ticket');
define('_MI_XHELP_TICKET_NEWRESPONSE_NOTIFYSBJ','RE: {TICKET_SUBJECT} {TICKET_SUPPORT_KEY}');
define('_MI_XHELP_TICKET_NEWRESPONSE_NOTIFYTPL','ticket_newresponse_notify.tpl');

define('_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFY','User: Modified Response');
define('_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYCAP','Notify me when a response is modified for this ticket');
define('_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYDSC','Receive notification when a response is modified for this ticket');
define('_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYSBJ','{X_MODULE} Ticket response modified - id {TICKET_ID}');
define('_MI_XHELP_TICKET_MODIFIEDRESPONSE_NOTIFYTPL','ticket_modifiedresponse_notify.tpl');

define('_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFY','User: Changed Status');
define('_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYCAP','Notify me when the status of this ticket is changed');
define('_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYDSC','Receive notification when the status of this ticket is changed');
define('_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYSBJ','{X_MODULE} Ticket status changed - id {TICKET_ID}');
define('_MI_XHELP_TICKET_CHANGEDSTATUS_NOTIFYTPL','ticket_changedstatus_notify.tpl');

define('_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFY','User: Changed Priority');
define('_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYCAP','Notify me when the priority of this ticket is changed');
define('_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYDSC','Receive notification when the priority of this ticket is changed');
define('_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYSBJ','{X_MODULE} Ticket priority changed - id {TICKET_ID}');
define('_MI_XHELP_TICKET_CHANGEDPRIORITY_NOTIFYTPL','ticket_changedpriority_notify.tpl');

define('_MI_XHELP_TICKET_NEWOWNER_NOTIFY','User: New Owner');
define('_MI_XHELP_TICKET_NEWOWNER_NOTIFYCAP','Notify me when ownership has been changed for this ticket');
define('_MI_XHELP_TICKET_NEWOWNER_NOTIFYDSC','Receive notification when ownership has been changed for this ticket');
define('_MI_XHELP_TICKET_NEWOWNER_NOTIFYSBJ','{X_MODULE} Ticket ownership changed - id {TICKET_ID}');
define('_MI_XHELP_TICKET_NEWOWNER_NOTIFYTPL','ticket_newowner_notify.tpl');

define('_MI_XHELP_TICKET_NEWTICKET_NOTIFY','User: New Ticket');
define('_MI_XHELP_TICKET_NEWTICKET_NOTIFYCAP','Confirm when a new ticket is created');
define('_MI_XHELP_TICKET_NEWTICKET_NOTIFYDSC','Receive notification when a new ticket is created');
define('_MI_XHELP_TICKET_NEWTICKET_NOTIFYSBJ','RE: {TICKET_SUBJECT} {TICKET_SUPPORT_KEY}');
define('_MI_XHELP_TICKET_NEWTICKET_NOTIFYTPL','ticket_newticket_notify.tpl');

define('_MI_XHELP_DEPT_CLOSETICKET_NOTIFY','Staff: Close Ticket');
define('_MI_XHELP_DEPT_CLOSETICKET_NOTIFYCAP','Notify me when a ticket is closed');
define('_MI_XHELP_DEPT_CLOSETICKET_NOTIFYDSC','Receive notification when a ticket is closed');
define('_MI_XHELP_DEPT_CLOSETICKET_NOTIFYSBJ','{X_MODULE} Ticket closed - id {TICKET_ID}');
define('_MI_XHELP_DEPT_CLOSETICKET_NOTIFYTPL','dept_closeticket_notify.tpl');

define('_MI_XHELP_TICKET_CLOSETICKET_NOTIFY','User: Close Ticket');
define('_MI_XHELP_TICKET_CLOSETICKET_NOTIFYCAP','Confirm when a ticket is closed');
define('_MI_XHELP_TICKET_CLOSETICKET_NOTIFYDSC','Receive notification when a ticket is closed');
define('_MI_XHELP_TICKET_CLOSETICKET_NOTIFYSBJ','{X_MODULE} Ticket closed - id {TICKET_ID}');
define('_MI_XHELP_TICKET_CLOSETICKET_NOTIFYTPL','ticket_closeticket_notify.tpl');

define('_MI_XHELP_TICKET_NEWUSER_NOTIFY','User: New User created');
define('_MI_XHELP_TICKET_NEWUSER_NOTIFYCAP','Notify user that a new account has been created');
define('_MI_XHELP_TICKET_NEWUSER_NOTIFYDSC','Receive notification when a new user is created from an email submission (Requires Activation)');
define('_MI_XHELP_TICKET_NEWUSER_NOTIFYSBJ','{X_MODULE} New User Created');
define('_MI_XHELP_TICKET_NEWUSER_NOTIFYTPL','ticket_new_user_byemail.tpl');

define('_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFY','User: New User created');
define('_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYCAP','Notify user that a new account has been created');
define('_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYDSC','Receive notification when a new user is created from an email submission (Auto Activation)');
define('_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYSBJ','{X_MODULE} New User Created');
define('_MI_XHELP_TICKET_NEWUSER_ACT1_NOTIFYTPL','ticket_new_user_activation1.tpl');

define('_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFY','User: New User created');
define('_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYCAP','Notify user that a new account has been created');
define('_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYDSC','Receive notification when a new user is created from an email submission (Requires Admin Activation)');
define('_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYSBJ','{X_MODULE} New User Created');
define('_MI_XHELP_TICKET_NEWUSER_ACT2_NOTIFYTPL','ticket_new_user_activation2.tpl');

define('_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFY','User: Email Error');
define('_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYCAP','Notify user that their email was not stored');
define('_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYDSC','Receive notification when an email submission is not stored');
define('_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYSBJ','RE: {TICKET_SUBJECT}');
define('_MI_XHELP_TICKET_EMAIL_ERROR_NOTIFYTPL','ticket_email_error.tpl');

define('_MI_XHELP_DEPT_MERGE_TICKET_NOTIFY','Staff: Merge Tickets');
define('_MI_XHELP_DEPT_MERGE_TICKET_NOTIFYCAP','Notify me when tickets are merged');
define('_MI_XHELP_DEPT_MERGE_TICKET_NOTIFYDSC','Receive notification when tickets are merged');
define('_MI_XHELP_DEPT_MERGE_TICKET_NOTIFYSBJ','{X_MODULE} Tickets merged');
define('_MI_XHELP_DEPT_MERGE_TICKET_NOTIFYTPL','dept_mergeticket_notify.tpl');

define('_MI_XHELP_TICKET_MERGE_TICKET_NOTIFY','User: Merge Tickets');
define('_MI_XHELP_TICKET_MERGE_TICKET_NOTIFYCAP','Notify me when tickets are merged');
define('_MI_XHELP_TICKET_MERGE_TICKET_NOTIFYDSC','Receive notification when tickets are merged');
define('_MI_XHELP_TICKET_MERGE_TICKET_NOTIFYSBJ','{X_MODULE} Tickets merged');
define('_MI_XHELP_TICKET_MERGE_TICKET_NOTIFYTPL','ticket_mergeticket_notify.tpl');

define('_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFY','User: New Ticket By Email');
define('_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYCAP','Confirm when a new ticket is created by email');
define('_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYDSC','Receive notification when a new ticket is created by email');
define('_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYSBJ','RE: {TICKET_SUBJECT} {TICKET_SUPPORT_KEY}');
define('_MI_XHELP_TICKET_NEWTICKET_EMAIL_NOTIFYTPL','ticket_newticket_byemail_notify.tpl');

//0.81
define('_MI_XHELP_MENU_MANAGER','Manager');