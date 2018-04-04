<?php namespace XoopsModules\Xhelp;

//

/**
 *Global Application Constants
 *
 * @author Brian Wahoff
 * @access Public
 */

//define('XHELP_DIR_NAME', 'xhelp');
define('XHELP_DIR_NAME', basename(dirname(__DIR__)));

//Uncomment for XOOPS 2.2 support
//define('XOOPS_2.2', '');

// XOOPS ROOT URL path
if (defined('XOOPS_2.2')) {
    define('XHELP_SITE_URL', XOOPS_ABS_URL);
} else {
    define('XHELP_SITE_URL', XOOPS_URL);
}

define('XHELP_DEFAULT_PRIORITY', 4);           //Used to control the default ticket priority
//Values: 1(Highest)-5(Lowest)

//Security Permission Bits
// @todo - these bits should be listed as their actual value
// not their exponent value (e.g. XHELP_SEC_TICKET_EDIT should be 2^1 = 2 instead of 1)
define('XHELP_SEC_TICKET_ADD', 0);             //Add Ticket
define('XHELP_SEC_TICKET_EDIT', 1);            //Modify a ticket
define('XHELP_SEC_TICKET_DELETE', 2);          //Delete Ticket
define('XHELP_SEC_TICKET_OWNERSHIP', 3);       //Change Ownership
define('XHELP_SEC_TICKET_STATUS', 4);          //Change Ticket Status
define('XHELP_SEC_TICKET_PRIORITY', 5);        //Change Ticket Priority
define('XHELP_SEC_TICKET_LOGUSER', 6);         //Log a ticket as a different user
define('XHELP_SEC_RESPONSE_ADD', 7);           //Respond to ticket
define('XHELP_SEC_RESPONSE_EDIT', 8);          //Modify a response
define('XHELP_SEC_TICKET_MERGE', 9);           //Merge two tickets together
define('XHELP_SEC_FILE_DELETE', 10);           //Remove File Attachments
define('XHELP_SEC_FAQ_ADD', 11);               //Add FAQ article from ticket
define('XHELP_SEC_TICKET_TAKE_OWNERSHIP', 12); //Take ownership of ticket

//Default Security Permissions
// @todo - I'm not happy with pow(2, ...) to work with each security bit, should we store each bit as its mask?
//Ticket Manager Role - Can do everything
define(
    'XHELP_ROLE_PERM_1',
    (2 ** XHELP_SEC_TICKET_ADD)
    | (2 ** XHELP_SEC_TICKET_EDIT)
    | (2 ** XHELP_SEC_TICKET_DELETE)
    | (2 ** XHELP_SEC_TICKET_OWNERSHIP)
    | (2 ** XHELP_SEC_TICKET_STATUS)
    | (2 ** XHELP_SEC_TICKET_PRIORITY)
    | (2 ** XHELP_SEC_TICKET_LOGUSER)
    | (2 ** XHELP_SEC_RESPONSE_ADD)
    | (2 ** XHELP_SEC_RESPONSE_EDIT)
    | (2 ** XHELP_SEC_TICKET_MERGE)
    | (2 ** XHELP_SEC_FILE_DELETE)
    | (2 ** XHELP_SEC_FAQ_ADD)
    | (2 ** XHELP_SEC_TICKET_TAKE_OWNERSHIP)
);

//Support Role - Log Tickets and Responses, Change Status/Priority, Log tickets as user
define('XHELP_ROLE_PERM_2', (2 ** XHELP_SEC_TICKET_ADD) | (2 ** XHELP_SEC_TICKET_STATUS) | (2 ** XHELP_SEC_TICKET_PRIORITY) | (2 ** XHELP_SEC_TICKET_LOGUSER) | (2 ** XHELP_SEC_RESPONSE_ADD) | (2 ** XHELP_SEC_FAQ_ADD) | (2 ** XHELP_SEC_TICKET_TAKE_OWNERSHIP));

//Browser Role - Read-Only
define('XHELP_ROLE_PERM_3', 0);

//Application Folders

define('XHELP_BASE_PATH', XOOPS_ROOT_PATH . '/modules/' . XHELP_DIR_NAME);
define('XHELP_CLASS_PATH', XHELP_BASE_PATH . '/class');
define('XHELP_BASE_URL', XHELP_SITE_URL . '/modules/' . XHELP_DIR_NAME);
define('XHELP_UPLOAD_PATH', XOOPS_ROOT_PATH . '/uploads/' . XHELP_DIR_NAME);
define('XHELP_INCLUDE_PATH', XHELP_BASE_PATH . '/include');
define('XHELP_INCLUDE_URL', XHELP_BASE_URL . '/include');
define('XHELP_IMAGE_PATH', XHELP_BASE_PATH . '/assets/images');
define('XHELP_IMAGE_URL', XHELP_BASE_URL . '/assets/images');
define('XHELP_ADMIN_URL', XHELP_BASE_URL . '/admin');
define('XHELP_ADMIN_PATH', XHELP_BASE_PATH . '/admin');
define('XHELP_PEAR_PATH', XHELP_CLASS_PATH . '/pear');
define('XHELP_CACHE_PATH', XOOPS_ROOT_PATH . '/cache');
define('XHELP_CACHE_URL', XHELP_SITE_URL . '/cache');
define('XHELP_SCRIPT_URL', XHELP_BASE_URL . '/scripts');
define('XHELP_JPSPAN_PATH', XHELP_INCLUDE_PATH . '/jpspan');
define('XHELP_FAQ_ADAPTER_PATH', XHELP_CLASS_PATH . '/faq');
define('XHELP_REPORT_PATH', XHELP_BASE_PATH . '/reports');
define('XHELP_REPORT_URL', XHELP_BASE_URL . '/reports');
define('XHELP_JPGRAPH_PATH', XHELP_INCLUDE_PATH . '/jpgraph');
define('XHELP_JPGRAPH_URL', XHELP_INCLUDE_URL . '/jpgraph');
define('XHELP_RPT_RENDERER_PATH', XHELP_CLASS_PATH . '/reportRenderer');

//Control Types
define('XHELP_CONTROL_TXTBOX', 0);
define('XHELP_CONTROL_TXTAREA', 1);
define('XHELP_CONTROL_SELECT', 2);
define('XHELP_CONTROL_MULTISELECT', 3);
define('XHELP_CONTROL_YESNO', 4);
define('XHELP_CONTROL_CHECKBOX', 5);
define('XHELP_CONTROL_RADIOBOX', 6);
define('XHELP_CONTROL_DATETIME', 7);
define('XHELP_CONTROL_FILE', 8);

//Notification Settings
define('XHELP_NOTIF_STAFF_DEPT', 2);
define('XHELP_NOTIF_STAFF_OWNER', 3);
define('XHELP_NOTIF_STAFF_NONE', 4);

define('XHELP_NOTIF_USER_YES', 1);
define('XHELP_NOTIF_USER_NO', 2);

define('XHELP_NOTIF_NEWTICKET', 1);
define('XHELP_NOTIF_DELTICKET', 2);
define('XHELP_NOTIF_EDITTICKET', 3);
define('XHELP_NOTIF_NEWRESPONSE', 4);
define('XHELP_NOTIF_EDITRESPONSE', 5);
define('XHELP_NOTIF_EDITSTATUS', 6);
define('XHELP_NOTIF_EDITPRIORITY', 7);
define('XHELP_NOTIF_EDITOWNER', 8);
define('XHELP_NOTIF_CLOSETICKET', 9);
define('XHELP_NOTIF_MERGETICKET', 10);

define('XHELP_GLOBAL_UID', -999);   // refers to all users

define('XHELP_QRY_STAFF_HIGHPRIORITY', 0);
define('XHELP_QRY_STAFF_NEW', 1);
define('XHELP_QRY_STAFF_MINE', 2);
define('XHELP_QRY_STAFF_ALL', -1);

define('XHELP_STATE_UNRESOLVED', 1);
define('XHELP_STATE_RESOLVED', 2);

define('XHELP_DISPLAYNAME_UNAME', 1);
define('XHELP_DISPLAYNAME_REALNAME', 2);

define('XHELP_CONSTANTS_INCLUDED', 1);
