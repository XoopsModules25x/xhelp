<?php namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;
/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();


if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

//require_once XHELP_BASE_PATH . '/functions.php';
// require_once XHELP_CLASS_PATH . '/Service.php';

/**
 * xhelp_notificationService class
 *
 * Part of the Messaging Subsystem.  Uses the xoopsNotificationHandler class to send emails to users
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class NotificationService extends Xhelp\Service
{
    /**
     * Instance of the staff object
     *
     * @var object
     * @access private
     */
    public $_hStaff;

    /**
     * Instance of the xoops text sanitizer
     *
     * @var object
     * @access private
     */
    public $_ts;

    /**
     * Path to the mail_template directory
     *
     * @var string
     * @access private
     */
    public $_template_dir = '';

    /**
     * Instance of the module object
     *
     * @var object
     * @access private
     */
    public $_module;

    /**
     * Instance of the notification object
     *
     * @var object
     * @access private
     */
    public $_hNotification;

    /**
     * Instance of the status object
     *
     * @var object
     * @access private
     */
    public $_hStatus;

    /**
     * Class Constructor
     *
     * @access  public
     */
    public function __construct()
    {
        global $xoopsConfig, $xoopsModule;
        $db = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->_ts            = \MyTextSanitizer::getInstance();
        $this->_template_dir  = $this->_getTemplateDir($xoopsConfig['language']);
        $this->_module        = Xhelp\Utility::getModule();
        $this->_hStaff        = new Xhelp\StaffHandler($db);
        $this->_hNotification = new Xhelp\NotificationHandler($db);
        $this->_hStatus       = new Xhelp\StatusHandler($db);
        $this->init();
    }

    /**
     * Retrieve the email_template object that is requested
     *
     * @param int    $category ID of item
     * @param string $event    name of event
     * @param object $module   $xoopsModule object
     *
     * @param        $template_id
     * @return bool
     * @access private
     */
    public function _getEmailTpl($category, $event, $module, &$template_id)
    {
        $templates = $module->getInfo('_email_tpl');   // Gets $modversion['_email_tpl'] array from xoops_version.php

        foreach ($templates as $tpl_id => $tpl) {
            if ($tpl['category'] == $category && $tpl['name'] == $event) {
                $template_id = $tpl_id;

                return $tpl;
            }
        }

        return false;
    }

    /*
     * Returns a group of $staffRole objects
     *
     * @access int $dept ID of department
     * @access array $aMembers array of all possible staff members
     *
     * @access private
     */
    /**
     * @param $dept
     * @param $aMembers
     * @return mixed
     */
    public function &_getStaffRoles($dept, $aMembers)
    {
        $hStaffRole = new Xhelp\StaffRoleHandler($GLOBALS['xoopsDB']);

        // Retrieve roles of all members
        $crit = new \CriteriaCompo(new \Criteria('uid', '(' . implode($aMembers, ',') . ')', 'IN'));
        $crit->add(new \Criteria('deptid', $dept));
        $staffRoles = $hStaffRole->getObjects($crit, false);    // array of staff role objects

        return $staffRoles;
    }

    /*
     * Gets a list of staff members that have the notification selected
     *
     * @access object $staffRoles staffRole objects
     * @access array $aMembers array of all possible staff members
     * @access array $staff_options array of acceptable departments
     *
     * @access private
     */
    /**
     * @param $staffRoles
     * @param $aMembers
     * @param $staff_options
     * @return array
     */
    public function &_getEnabledStaff(&$staffRoles, $aMembers, $staff_options)
    {
        // Get only staff members that have permission for this notification
        $enabled_staff = [];
        foreach ($aMembers as $aMember) {
            foreach ($staffRoles as $staffRole) {
                if ($staffRole->getVar('uid') == $aMember && in_array($staffRole->getVar('roleid'), $staff_options)) {
                    $enabled_staff[$aMember] = $aMember;
                    break;
                }
            }
        }
        unset($aMembers);

        return $enabled_staff;
    }

    /*
     * Used to retrieve a list of xoops user objects
     *
     * @param array $enabled_staff array of staff members that have the notification enabled
     *
     * @access private
     */
    /**
     * @param      $enabled_staff
     * @param bool $active_only
     * @return array
     */
    public function &_getXoopsUsers($enabled_staff, $active_only = true)
    {
        $xoopsUsers = [];
        $hMember    = xoops_getHandler('member');
        if (count($enabled_staff) > 0) {
            $crit = new \CriteriaCompo(new \Criteria('uid', '(' . implode($enabled_staff, ',') . ')', 'IN'));
        } else {
            return $xoopsUsers;
        }
        if ($active_only) {
            $crit->add(new \Criteria('level', 0, '>'));
        }
        $xoopsUsers = $hMember->getUsers($crit, true);      // xoopsUser objects
        unset($enabled_staff);

        return $xoopsUsers;
    }

    /*
     * Returns only the accepted staff members after having their permissions checked
     *
     * @param  array  $aMembers    array of all possible staff members
     * @param  object $ticket      xhelp_ticket object
     * @param  object $settings    xhelp_notification object
     * @param  int    $submittedBy ID of ticket submitter
     * @return array  of XoopsUser objects
     *
     * @access private
     */
    /**
     * @param $aMembers
     * @param $ticket
     * @param $settings
     * @param $submittedBy
     * @return array
     */
    public function &_checkStaffSetting($aMembers, &$ticket, &$settings, $submittedBy)
    {
        $submittedBy = (int)$submittedBy;
        $dept = $ticket;
        if (is_object($ticket)) {
            $dept = $ticket->getVar('department');
        }
        $staff_setting = $settings->getVar('staff_setting');
        $staff_options = $settings->getVar('staff_options');

        $staffRoles    = $this->_getStaffRoles($dept, $aMembers);     // Get list of the staff members' roles
        $enabled_staff = $this->_getEnabledStaff($staffRoles, $aMembers, $staff_options);
        $xoopsUsers    = $this->_getXoopsUsers($enabled_staff);

        return $xoopsUsers;
    }

    /*
     * Returns an array of staff UID's
     *
     * @access object $members xhelp_staff objects
     * @access boolean $removeSubmitter
     *
     * @access private
     */
    /**
     * @param      $members
     * @param      $submittedBy
     * @param bool $removeSubmitter
     * @return array
     */
    public function &_makeMemberArray(&$members, $submittedBy, $removeSubmitter = false)
    {
        $aMembers    = [];
        $submittedBy = (int)$submittedBy;
        foreach ($members as $member) {   // Full list of dept members
            if ($removeSubmitter) {
                if ($member->getVar('uid') == $submittedBy) { // Remove the staff member that submitted from the email list
                    continue;
                } else {
                    $aMembers[$member->getVar('uid')] = $member->getVar('uid');
                }
            } else {
                $aMembers[$member->getVar('uid')] = $member->getVar('uid');
            }
        }

        return $aMembers;
    }

    /**
     * Returns emails of staff belonging to an event
     *
     * @param     $ticket
     * @param int $event_id    bit_value of event
     * @param     $settings
     * @param int $submittedBy ID of user submitting event - should only be used when there is a response
     * @return array
     * @internal param int $dept ID of department
     * @access   private
     */
    public function &_getSubscribedStaff(&$ticket, $event_id, &$settings, $submittedBy = null)
    {
        global $xoopsUser;

        $arr         = [];
        $hMembership = new Xhelp\MembershipHandler($GLOBALS['xoopsDB']);
        $hMember     = xoops_getHandler('member');

        if (is_object($ticket)) {
            if (!$submittedBy) {
                $submittedBy = $ticket->getVar('uid');
            }
            $owner = $ticket->getVar('ownership');
            $dept  = $ticket->getVar('department');
        } else {
            $dept = (int)$ticket;
        }
        $submittedBy = (int)$submittedBy;

        $staff_setting = $settings->getVar('staff_setting');
        $staff_options = $settings->getVar('staff_options');
        switch ($staff_setting) {
            case XHELP_NOTIF_STAFF_DEPT:   // Department Staff can receive notification
                $members    = $hMembership->membershipByDept($dept);  // Xhelp\Staff objects
                $aMembers   = $this->_makeMemberArray($members, $submittedBy, true);
                $xoopsUsers = $this->_checkStaffSetting($aMembers, $ticket, $settings, $submittedBy);
                break;

            case XHELP_NOTIF_STAFF_OWNER:   // Ticket Owner can receive notification
                $members = $hMembership->membershipByDept($dept);
                if (0 <> $ticket->getVar('ownership')) {      // If there is a ticket owner
                    $ticket_owner            = $ticket->getVar('ownership');
                    $aMembers[$ticket_owner] = $ticket_owner;
                    $crit                    = new \Criteria('uid', '(' . implode($aMembers, ',') . ')', 'IN');
                    unset($aMembers);
                    $xoopsUsers = $hMember->getUsers($crit, true);      // xoopsUser objects
                } else {                                    // If no ticket owner, send to dept staff
                    $aMembers   = $this->_makeMemberArray($members, true);
                    $xoopsUsers = $this->_checkStaffSetting($aMembers, $ticket, $settings, $submittedBy);
                }
                break;

            case XHELP_NOTIF_STAFF_NONE:   // Notification is turned off
            default:
                return $arr;
        }

        //Sort users based on Notification Preference
        foreach ($xoopsUsers as $xUser) {
            $cMember = $members[$xUser->getVar('uid')];

            if (null !== $cMember && ($xUser->uid() != $xoopsUser->uid())) {
                if ($this->_isSubscribed($cMember, $event_id)) {
                    if (2 == $xUser->getVar('notify_method')) {       // Send by email
                        $arr['email'][] = $members[$xUser->getVar('uid')]->getVar('email');
                    } elseif (1 == $xUser->getVar('notify_method')) { // Send by pm
                        $arr['pm'][] = $xUser;
                    }
                }
            }
        }

        return $arr;
    }

    /**
     * Returns emails of users belonging to a ticket
     *
     * @param int $ticketid ID of ticket
     * @access private
     * @return array
     */
    public function &_getSubscribedUsers($ticketid)
    {
        global $xoopsUser;

        $ticketid = (int)$ticketid;

        $hTicketEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
        $hMember       = xoops_getHandler('member');

        //Get all Subscribed users, except for the current user
        $crit = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
        $crit->add(new \Criteria('suppress', 0));
        $crit->add(new \Criteria('email', $xoopsUser->email(), '<>'));

        $users = $hTicketEmails->getObjects($crit);    // xhelp_ticketEmail objects

        $aUsers = [];
        $arr    = [];
        foreach ($users as $user) {
            if (0 != $user->getVar('uid')) {
                $aUsers[$user->getVar('email')] = $user->getVar('uid');
            } else {
                // Add users with just email to array
                $arr['email'][] = $user->getVar('email');
            }
        }

        $xoopsUsers = [];
        if (!empty($aUsers)) {
            $crit       = new \Criteria('uid', '(' . implode($aUsers, ',') . ')', 'IN');
            $xoopsUsers = $hMember->getUsers($crit, true);  // xoopsUser objects
        }
        unset($aUsers);

        // @todo - replace notify_method integers with constants
        // Add users with uid
        foreach ($xoopsUsers as $xUser) {  // Find which method user prefers for sending message
            if (2 == $xUser->getVar('notify_method')) {
                $arr['email'][] = $xUser->getVar('email');
            } elseif (1 == $xUser->getVar('notify_method')) {
                $arr['pm'][] = $xUser;
            }
        }

        return $arr;
    }

    /**
     * Checks to see if the staff member is subscribed to receive the notification for this event
     *
     * @param      int       /object $user     userid/staff object of staff member
     * @param  int $event_id value of the the event
     * @return bool              true is suscribed, false if not
     *
     * @access private
     */
    public function _isSubscribed($user, $event_id)
    {
        if (!is_object($user)) {          //If user is not an object, retrieve a staff object using the uid
            if (is_numeric($user)) {
                $uid    = $user;
                $hStaff = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
                if (!$user = $hStaff->getByUid($uid)) {
                    return false;
                }
            }
        }

        return ($user->getVar('notify') & pow(2, $event_id)) > 0;
    }

    /**
     * Retrieve a user's email address
     *
     * @param  int $uid user's id
     * @return array $member's email
     *
     * @access private
     */
    public function _getUserEmail($uid)
    {
        global $xoopsUser;
        $arr = [];
        $uid = (int)$uid;

        if ($uid == $xoopsUser->getVar('uid')) {      // If $uid == current user's uid
            if (2 == $xoopsUser->getVar('notify_method')) {
                $arr['email'][] = $xoopsUser->getVar('email');     // return their email
            } elseif (1 == $xoopsUser->getVar('notify_method')) {
                $arr['pm'][] = $xoopsUser;
            }
        } else {
            $hMember = xoops_getHandler('member');     //otherwise...
            if ($member = $hMember->getUser($uid)) {
                if (2 == $member->getVar('notify_method')) {
                    $arr['email'][] = $member->getVar('email');
                } elseif (1 == $member->getVar('notify_method')) {
                    $arr['pm'][] = $member;
                }
            } else {
                $arr['email'][] = '';
            }
        }

        return $arr;
    }

    /**
     * Retrieves a staff member's email address
     *
     * @param  int $uid user's id
     * @param      $dept
     * @param      $staff_options
     * @return array $staff member's email
     *
     * @access private
     */
    public function _getStaffEmail($uid, $dept, $staff_options)
    {
        $uid     = (int)$uid;
        $dept    = (int)$dept;
        $hMember = xoops_getHandler('member');
        $arr     = [];

        // Check staff roles to staff options making sure the staff has permission
        $staffRoles = $this->_hStaff->getRolesByDept($uid, $dept, true);
        $bFound     = true;
        foreach ($staff_options as $option) {
            if (array_key_exists($option, $staffRoles)) {
                $bFound = true;
                break;
            } else {
                $bFound = false;
            }
        }
        if (!$bFound) {
            return $arr;
        }

        if ($staff = $this->_hStaff->getByUid($uid)) {
            if ($member = $hMember->getUser($uid)) {
                if (2 == $member->getVar('notify_method')) {
                    $arr['email'][] = $staff->getVar('email');
                } elseif (1 == $member->getVar('notify_method')) {
                    $arr['pm'][] = $member;
                }
            } else {
                $arr['email'][] = '';
            }
        } else {
            $arr['email'][] = '';
        }

        return $arr;
    }

    /**
     * Send pm and email notifications to selected users
     *
     * @param  object $email_tpl object returned from _getEmailTpl() function
     * @param  array  $sendTo    emails and xoopsUser objects
     * @param  array  $tags      array of notification information
     * @param string  $fromEmail
     * @return bool TRUE if success, FALSE if no success
     *
     * @access private
     */
    public function _sendEvents($email_tpl, $sendTo, $tags, $fromEmail = '')
    {
        $ret = true;
        if (array_key_exists('pm', $sendTo)) {
            $ret = $ret && $this->_sendEventPM($email_tpl, $sendTo, $tags, $fromEmail);
        }

        if (array_key_exists('email', $sendTo)) {
            $ret = $ret && $this->_sendEventEmail($email_tpl, $sendTo, $tags, $fromEmail);
        }

        return $ret;
    }

    /**
     * Send the pm notification to selected users
     *
     * @param  object $email_tpl object returned from _getEmailTpl() function
     * @param  array  $sendTo    xoopsUser objects
     * @param  array  $tags      array of notification information
     * @param string  $fromEmail
     * @return bool TRUE if success, FALSE if no success
     *
     * @access private
     */
    public function _sendEventPM($email_tpl, $sendTo, $tags, $fromEmail = '')
    {
        $notify_pm = '';
        global $xoopsConfig, $xoopsUser;

        $notify_pm = $sendTo['pm'];

        $tags        = array_merge($tags, $this->_getCommonTplVars());          // Retrieve the common template vars and add to array
        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->usePM();

        foreach ($tags as $k => $v) {
            $xoopsMailer->assign($k, preg_replace('/&amp;/i', '&', $v));
        }
        $xoopsMailer->setTemplateDir($this->_template_dir);             // Set template dir
        $xoopsMailer->setTemplate($email_tpl['mail_template'] . '.tpl'); // Set the template to be used

        $configHandler     = xoops_getHandler('config');
        $hMember           = xoops_getHandler('member');
        $xoopsMailerConfig = $configHandler->getConfigsByCat(XOOPS_CONF_MAILER);
        $xoopsMailer->setFromUser($hMember->getUser($xoopsMailerConfig['fromuid']));
        $xoopsMailer->setToUsers($notify_pm);
        $xoopsMailer->setSubject($email_tpl['mail_subject']);           // Set the subject of the email
        $xoopsMailer->setFromName($xoopsConfig['sitename']);            // Set a from address
        $success = $xoopsMailer->send(true);

        return $success;
    }

    /**
     * Send the mail notification to selected users
     *
     * @param  object $email_tpl object returned from _getEmailTpl() function
     * @param  array  $sendTo    emails returned from _getSubscribedStaff() function
     * @param  array  $tags      array of notification information
     * @param string  $fromEmail
     * @return bool TRUE if success, FALSE if no success
     *
     * @access private
     */
    public function _sendEventEmail($email_tpl, $sendTo, $tags, $fromEmail = '')
    {
        $notify_email = '';
        global $xoopsConfig;

        $notify_email = $sendTo['email'];

        $tags        = array_merge($tags, $this->_getCommonTplVars());          // Retrieve the common template vars and add to array
        $xoopsMailer = xoops_getMailer();
        $xoopsMailer->useMail();

        foreach ($tags as $k => $v) {
            $xoopsMailer->assign($k, preg_replace('/&amp;/i', '&', $v));
        }
        $xoopsMailer->setTemplateDir($this->_template_dir);             // Set template dir
        $xoopsMailer->setTemplate($email_tpl['mail_template'] . '.tpl'); // Set the template to be used
        if (strlen($fromEmail) > 0) {
            $xoopsMailer->setFromEmail($fromEmail);
        }
        $xoopsMailer->setToEmails($notify_email);                           // Set who the email goes to
        $xoopsMailer->setSubject($email_tpl['mail_subject']);           // Set the subject of the email
        $xoopsMailer->setFromName($xoopsConfig['sitename']);            // Set a from address
        $success = $xoopsMailer->send(true);

        return $success;
    }

    /**
     * Get a list of the common constants required for notifications
     *
     * @return array $tags
     *
     * @access private
     */
    public function &_getCommonTplVars()
    {
        global $xoopsConfig;
        $tags                 = [];
        $tags['X_MODULE']     = $this->_module->getVar('name');
        $tags['X_SITEURL']    = XHELP_SITE_URL;
        $tags['X_SITENAME']   = $xoopsConfig['sitename'];
        $tags['X_ADMINMAIL']  = $xoopsConfig['adminmail'];
        $tags['X_MODULE_URL'] = XHELP_BASE_URL . '/';

        return $tags;
    }

    /**
     * Retrieve the directory where mail templates are stored
     *
     * @param  string $language language used for xoops
     * @return string $template_dir
     *
     * @access private
     */
    public function _getTemplateDir($language)
    {
        $path = XOOPS_ROOT_PATH . '/modules/xhelp/language/' . $language . '/mail_template';
        if (is_dir($path)) {
            return $path;
        } else {
            return XOOPS_ROOT_PATH . '/modules/xhelp/language/english/mail_template';
        }
    }

    /**
     * Returns the number of department notifications
     *
     * @return int $num number of department notifications
     *
     * @access public
     */
    public function getNumDeptNotifications()
    {
        $num       = 0;
        $templates = $this->_module->getInfo('_email_tpl');
        foreach ($templates as $template) {
            if ('dept' === $template['category']) {
                ++$num;
            }
        }

        return $num;
    }

    /**
     * Returns the email address of the person causing the fire of the event
     *
     * @param  int $uid uid of the user
     * @return string email of user
     *
     * @access private
     */
    public function _getEmail($uid)
    {
        if (!$isStaff = $this->_hStaff->isStaff($uid)) {
            return $this->_getUserEmail($uid);
        } else {
            return $this->_getStaffEmail($uid);
        }
    }

    /**
     * Confirm submission to user and notify staff members when new_ticket is triggered
     * @param  Xhelp\Ticket $ticket Ticket that was added
     * @return void True on success, false on error
     * @access  public
     */
    public function new_ticket(&$ticket)
    {
        global $xhelp_isStaff;
        global $xoopsUser;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        $displayName  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_POSTED']      = $ticket->posted();
        $tags['TICKET_CREATED']     = Xhelp\Utility::getUsername($ticket->getVar('uid'), $displayName);
        $tags['TICKET_SUPPORT_KEY'] = ($ticket->getVar('serverid') ? '{' . $ticket->getVar('emailHash') . '}' : '');
        $tags['TICKET_URL']         = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_NEWTICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {    // If staff notification is enabled
            if ($email_tpl = $this->_getEmailTpl('dept', 'new_ticket', $this->_module, $template_id)) {  // Send email to dept members
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {     // If user notification is enabled
            if ($ticket->getVar('serverid') > 0) {
                //this ticket has been submitted by email
                //get department email address
                $hServer = new Xhelp\DepartmentMailBoxHandler($GLOBALS['xoopsDB']);
                $server  = $hServer->get($ticket->getVar('serverid'));
                //
                $tags['TICKET_SUPPORT_EMAIL'] = $server->getVar('emailaddress');
                //
                if ($email_tpl = $this->_getEmailTpl('ticket', 'new_this_ticket_via_email', $this->_module, $template_id)) {
                    $sendTo  = $this->_getUserEmail($ticket->getVar('uid'));
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags, $server->getVar('emailaddress'));
                }
            } else { //this ticket has been submitted via the website
                if (!$xhelp_isStaff) {
                    if ($email_tpl = $this->_getEmailTpl('ticket', 'new_this_ticket', $this->_module, $template_id)) {    // Send confirm email to submitter
                        $sendTo  = $this->_getUserEmail($ticket->getVar('uid'));   // Will be the only person subscribed
                        $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string    $password Password for new account
     * @param \XoopsUser $user     XOOPS user object for new account
     */
    public function new_user_by_email($password, &$user)
    {
        // Send Welcome Email to submitter
        //global $xoopsUser;

        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $user->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $user->getVar('email');
        $tags['XOOPS_USER_ID']       = $user->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;
        $tags['X_UACTLINK']          = XHELP_SITE_URL . '/user.php?op=actv&id=' . $user->getVar('uid') . '&actkey=' . $user->getVar('actkey');

        if ($email_tpl = $this->_getEmailTpl('ticket', 'new_user_byemail', $this->_module, $template_id)) {
            $sendTo  = $this->_getUserEmail($user->getVar('uid'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string    $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation0($password, $xoopsUser)
    {
        global $xoopsConfig;
        $newid = $newuser->getVar('uid');
        $uname = $newuser->getVar('uname');
        $email = $newuser->getVar('email');

        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $newuser->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $newuser->getVar('email');
        $tags['XOOPS_USER_ID']       = $newuser->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;
        $tags['X_UACTLINK']          = XHELP_SITE_URL . '/user.php?op=actv&id=' . $newuser->getVar('uid') . '&actkey=' . $newuser->getVar('actkey');

        if ($email_tpl = $this->_getEmailTpl('ticket', 'new_user_byemail', $this->_module, $template_id)) {
            $sendTo  = $this->_getUserEmail($newuser->getVar('uid'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string    $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation1($password, $xoopsUser)
    {
        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $user->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $user->getVar('email');
        $tags['XOOPS_USER_ID']       = $user->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;

        if ($email_tpl = $this->_getEmailTpl('ticket', 'new_user_activation1', $this->_module, $template_id)) {
            $sendTo  = _getUserEmail($user->getVar('uid'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }

        $_POST['uname'] = $user->getVar('uname');
        $_POST['pass']  = $password;

        // For backward compatibility
        $_POST['uname'] = $user->getVar('uname');
        $_POST['pass']  = $password;

        $filename   = XOOPS_ROOT_PATH . '/kernel/authenticationservice.php';
        $foldername = XOOPS_ROOT_PATH . '/include/authenticationservices';
        if (file_exists($filename) && file_exists($foldername)) {     // check for ldap authentication hack
            if ($authentication_service = xoops_getHandler('authenticationservice')) {
                $authentication_service->checkLogin();
            } else {
                require_once XOOPS_ROOT_PATH . '/include/checklogin.php';
            }
        } else {
            require_once XOOPS_ROOT_PATH . '/include/checklogin.php';
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string    $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation2($password, $xoopsUser)
    {
        global $xoopsConfig;
        $newid = $user->getVar('uid');
        $uname = $user->getVar('uname');
        $email = $user->getVar('email');

        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $user->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $user->getVar('email');
        $tags['XOOPS_USER_ID']       = $user->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;

        if ($email_tpl = $this->_getEmailTpl('ticket', 'new_user_activation2', $this->_module, $template_id)) {
            $sendTo  = _getUserEmail($user->getVar('uid'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_response
     * Triggered after a response has been added to a ticket
     * @param Xhelp\Ticket    $ticket   Ticket containing response
     * @param Xhelp\Responses $response Response that was added
     */
    public function new_response($ticket, $response)
    {

        // If response is from staff member, send message to ticket submitter
        // If response is from submitter, send message to owner, if no owner, send to department

        global $xoopsUser, $xoopsConfig;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        if (!is_object($ticket) && 0 == $ticket) {
            $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
            $ticket  = $hTicket->get($response->getVar('ticketid'));
        }

        $b_email_ticket = false;
        $from           = '';

        $tags                         = [];
        $tags['TICKET_ID']            = $ticket->getVar('id');
        $tags['TICKET_URL']           = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_RESPONSE']      = $this->_ts->stripslashesGPC($response->getVar('message', 'n'));
        $tags['TICKET_SUBJECT']       = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        $tags['TICKET_TIMESPENT']     = $response->getVar('timeSpent');
        $tags['TICKET_STATUS']        = Xhelp\Utility::getStatus($ticket->getVar('status'));
        $displayName                  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed
        $tags['TICKET_RESPONDER']     = Xhelp\Utility::getUsername($xoopsUser->getVar('uid'), $displayName);
        $tags['TICKET_POSTED']        = $response->posted('m');
        $tags['TICKET_SUPPORT_KEY']   = '';
        $tags['TICKET_SUPPORT_EMAIL'] = $xoopsConfig['adminmail'];

        if ($ticket->getVar('serverid') > 0) {
            $hServer = new Xhelp\DepartmentMailBoxHandler($GLOBALS['xoopsDB']);

            if ($server = $hServer->get($ticket->getVar('serverid'))) {
                $from                         = $server->getVar('emailaddress');
                $tags['TICKET_SUPPORT_KEY']   = '{' . $ticket->getVar('emailHash') . '}';
                $tags['TICKET_SUPPORT_EMAIL'] = $from;
            }
        }
        $owner = $ticket->getVar('ownership');
        if (0 == $owner) {
            $tags['TICKET_OWNERSHIP'] = _XHELP_NO_OWNER;
        } else {
            $tags['TICKET_OWNERSHIP'] = Xhelp\Utility::getUsername($owner, $displayName);
        }
        $tags['TICKET_DEPARTMENT'] = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_NEWRESPONSE);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        $sendTo         = [];
        $hMember        = xoops_getHandler('member');
        $response_user  = $hMember->getUser($response->getVar('uid'));
        $response_email = $response_user->getVar('email');

        $aUsers = $this->_getSubscribedUsers($ticket->getVar('id'));

        if (in_array($response_email, $aUsers)) {  // If response from a submitter, send to staff and other submitters
            if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {        // Staff notification is enabled
                if ($email_tpl = $this->_getEmailTpl('dept', 'new_response', $this->_module, $template_id)) {       // Send to staff members
                    $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                }
            }
            unset($aUsers[$ticket->getVar('uid')]); // Remove response submitter from array
            $sendTo = $aUsers;                    // Get array of user emails to send
        } else {    // If response from staff, send to submitters
            // Also send to staff members if no owner
            if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {    // If notification is on
                if ($email_tpl = $this->_getEmailTpl('dept', 'new_response', $this->_module, $template_id)) {       // Send to staff members
                    if (0 == $ticket->getVar('ownership')) {
                        $sendTo = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                    }
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                }
            }
            $sendTo = $aUsers;
        }
        if (2 <> $user_setting && 0 == $response->getVar('private')) {
            if ($email_tpl = $this->_getEmailTpl('ticket', 'new_this_response', $this->_module, $template_id)) {    // Send to users
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags, $from);
            }
        }
    }

    /**
     * Event: update_priority
     * Triggered after a ticket priority is modified
     * Also See: batch_priority
     * @param Xhelp\Ticket $ticket      Ticket that was modified
     * @param int         $oldpriority Previous ticket priority
     */
    public function update_priority($ticket, $oldpriority)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $tags               = [];
        $tags['TICKET_ID']  = $ticket->getVar('id');
        $tags['TICKET_URL'] = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        // End of addition by marcan
        $tags['TICKET_OLD_PRIORITY'] = Xhelp\Utility::getPriority($oldpriority);
        $tags['TICKET_PRIORITY']     = Xhelp\Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_UPDATEDBY']    = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT']   = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITPRIORITY);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if ($email_tpl = $this->_getEmailTpl('dept', 'changed_priority', $this->_module, $template_id)) {   // Notify staff dept
            $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
        if ($email_tpl = $this->_getEmailTpl('ticket', 'changed_this_priority', $this->_module, $template_id)) {    // Notify submitter
            $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: update_status
     * Triggered after a ticket status change
     * Also See: batch_status, close_ticket, reopen_ticket
     * @param Xhelp\Ticket $ticket    The ticket that was modified
     * @param Xhelp\Status $oldstatus The previous ticket status
     * @param Xhelp\Status $newstatus The new ticket status
     */
    public function update_status($ticket, $oldstatus, $newstatus)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $tags               = [];
        $tags['TICKET_ID']  = $ticket->getVar('id');
        $tags['TICKET_URL'] = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        // End of addition by marcan
        $tags['TICKET_OLD_STATUS'] = $oldstatus->getVar('description');
        $tags['TICKET_OLD_STATE']  = Xhelp\Utility::getState($oldstatus->getVar('state'));
        $tags['TICKET_STATUS']     = $newstatus->getVar('description');
        $tags['TICKET_STATE']      = Xhelp\Utility::getState($newstatus->getVar('state'));
        $tags['TICKET_UPDATEDBY']  = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT'] = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITSTATUS);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if ($email_tpl = $this->_getEmailTpl('dept', 'changed_status', $this->_module, $template_id)) {
            $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
        if ($email_tpl = $this->_getEmailTpl('ticket', 'changed_this_status', $this->_module, $template_id)) {
            //$sendTo = $this->_getEmail($ticket->getVar('uid'));
            $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
            $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: update_owner
     * Triggered after ticket ownership change (Individual)
     * Also See: batch_owner
     * @param Xhelp\Ticket $ticket   Ticket that was changed
     * @param int         $oldOwner UID of previous owner
     * @param int         $newOwner UID of new owner
     */
    public function update_owner($ticket, $oldOwner, $newOwner)
    {
        //notify old owner, if assigned
        //notify new owner
        //notify submitter
        global $xoopsUser;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_URL']         = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
        $tags['TICKET_OWNER']       = Xhelp\Utility::getUsername($ticket->getVar('ownership'), $displayName);
        $tags['SUBMITTED_OWNER']    = $xoopsUser->getVar('uname');
        $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITOWNER);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');
        $staff_options = $settings->getVar('staff_options');

        $sendTo = [];
        if (XHELP_NOTIF_STAFF_OWNER == $staff_setting) {
            if (null !== $oldOwner
                && _XHELP_NO_OWNER <> $oldOwner) {                               // If there was an owner
                if ($email_tpl = $this->_getEmailTpl('dept', 'new_owner', $this->_module, $template_id)) {      // Send them an email
                    if ($this->_isSubscribed($oldOwner, $email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->_getStaffEmail($oldOwner, $ticket->getVar('department'), $staff_options);
                        $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
            if ($ticket->getVar('ownership') <> $xoopsUser->getVar('uid')
                && 0 <> $ticket->getVar('ownership')) { // If owner is not current user
                if ($email_tpl = $this->_getEmailTpl('dept', 'new_owner', $this->_module, $template_id)) {      // Send new owner email
                    if ($this->_isSubscribed($ticket->getVar('ownership'), $email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->_getStaffEmail($ticket->getVar('ownership'), $ticket->getVar('department'), $staff_options);
                        $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
        } elseif (XHELP_NOTIF_STAFF_DEPT == $staff_setting) { // Notify entire department
            if ($email_tpl = $this->_getEmailTpl('dept', 'new_owner', $this->_module, $template_id)) {
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            if ($email_tpl = $this->_getEmailTpl('ticket', 'new_this_owner', $this->_module, $template_id)) {   // Send to ticket submitter
                $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: close_ticket
     * Triggered after a ticket's status change from a status
     * with a state of XHELP_STATE_UNRESOLVED to a status
     * with a state of XHELP_STATE_RESOLVED
     * Also See: update_status, reopen_ticket
     * @param Xhelp\Ticket $ticket The ticket that was closed
     */
    public function close_ticket($ticket)
    {
        global $xoopsUser;
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
        $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_CLOSEDBY']    = $xoopsUser->getVar('uname');
        $tags['TICKET_URL']         = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_CLOSETICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        $sendTo = [];
        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($email_tpl = $this->_getEmailTpl('dept', 'close_ticket', $this->_module, $template_id)) {        // Send to department, not to staff member
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            if ($xoopsUser->getVar('uid') <> $ticket->getVar('uid')) {        // If not closed by submitter
                if ($email_tpl = $this->_getEmailTpl('ticket', 'close_this_ticket', $this->_module, $template_id)) {        // Send to submitter
                    //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: delete_ticket
     * Triggered after a ticket is deleted
     * @param Xhelp\Ticket $ticket Ticket that was deleted
     */
    public function delete_ticket($ticket)
    {
        //notify staff department
        //notify submitter
        global $xoopsUser, $xoopsModule;
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_POSTED']      = $ticket->posted();
        $tags['TICKET_DELETEDBY']   = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        $settings      = $this->_hNotification->get(XHELP_NOTIF_DELTICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($email_tpl = $this->_getEmailTpl('dept', 'removed_ticket', $this->_module, $template_id)) { // Send to dept staff
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $status = $this->_hStatus->get($ticket->getVar('status'));
            if (2 <> $status->getVar('state')) {
                if ($email_tpl = $this->_getEmailTpl('ticket', 'removed_this_ticket', $this->_module, $template_id)) {  // Send to submitter
                    //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: edit_ticket
     * Triggered after a ticket is modified
     * @param Xhelp\Ticket $oldTicket  Ticket information before modifications
     * @param Xhelp\Ticket $ticketInfo Ticket information after modifications
     */
    public function edit_ticket($oldTicket, $ticketInfo)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $hDept = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $tags                           = [];
        $tags['TICKET_URL']             = XHELP_BASE_URL . '/ticket.php?id=' . $ticketInfo->getVar('id');
        $tags['TICKET_OLD_SUBJECT']     = $this->_ts->stripslashesGPC($oldTicket['subject']);
        $tags['TICKET_OLD_DESCRIPTION'] = $this->_ts->stripslashesGPC($oldTicket['description']);
        $tags['TICKET_OLD_PRIORITY']    = Xhelp\Utility::getPriority($oldTicket['priority']);
        $tags['TICKET_OLD_STATUS']      = $oldTicket['status'];
        $tags['TICKET_OLD_DEPARTMENT']  = $oldTicket['department'];
        $tags['TICKET_OLD_DEPTID']      = $oldTicket['department_id'];

        $tags['TICKET_ID']          = $ticketInfo->getVar('id');
        $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticketInfo->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticketInfo->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticketInfo->getVar('priority'));
        $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticketInfo->getVar('status'));
        $tags['TICKET_MODIFIED']    = $xoopsUser->getVar('uname');
        if ($tags['TICKET_OLD_DEPTID'] <> $ticketInfo->getVar('department')) {
            $department                = $hDept->get($ticketInfo->getVar('department'));
            $tags['TICKET_DEPARTMENT'] = $department->getVar('department');
        } else {
            $tags['TICKET_DEPARTMENT'] = $tags['TICKET_OLD_DEPARTMENT'];
        }

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITTICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($email_tpl = $this->_getEmailTpl('dept', 'modified_ticket', $this->_module, $template_id)) {            // Send to dept staff
                $sendTo  = $this->_getSubscribedStaff($ticketInfo, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            if ($email_tpl = $this->_getEmailTpl('ticket', 'modified_this_ticket', $this->_module, $template_id)) {     // Send to ticket submitter
                $sendTo  = $this->_getSubscribedUsers($ticketInfo->getVar('id'));
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: edit_response
     * Triggered after a response has been modified
     * Also See: new_response
     * @param                $ticket
     * @param Xhelp\Responses $response    Modified response
     * @param Xhelp\Ticket    $oldticket   Ticket before modifications
     * @param Xhelp\Responses $oldresponse Response modifications
     * @internal param Xhelp\Ticket $nticket Ticket after modifications
     */
    public function edit_response($ticket, $response, $oldticket, $oldresponse)
    {
        //if not modified by response submitter, notify response submitter
        //notify ticket submitter
        global $xoopsUser;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        $displayName  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                         = [];
        $tags['TICKET_URL']           = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_OLD_RESPONSE']  = $this->_ts->stripslashesGPC($oldresponse->getVar('message', 'n'));
        $tags['TICKET_OLD_TIMESPENT'] = $oldresponse->getVar('timeSpent');
        $tags['TICKET_OLD_STATUS']    = Xhelp\Utility::getStatus($oldticket->getVar('status'));
        $tags['TICKET_OLD_RESPONDER'] = Xhelp\Utility::getUsername($oldresponse->getVar('uid'), $displayName);
        $owner                        = $oldticket->getVar('ownership');
        $tags['TICKET_OLD_OWNERSHIP'] = ($owner = 0 ? _XHELP_NO_OWNER : Xhelp\Utility::getUsername($owner, $displayName));
        $tags['TICKET_ID']            = $ticket->getVar('id');
        $tags['RESPONSE_ID']          = $response->getVar('id');
        $tags['TICKET_RESPONSE']      = $this->_ts->stripslashesGPC($response->getVar('message', 'n'));
        $tags['TICKET_TIMESPENT']     = $response->getVar('timeSpent');
        $tags['TICKET_STATUS']        = Xhelp\Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_RESPONDER']     = $xoopsUser->getVar('uname');
        $tags['TICKET_POSTED']        = $response->posted();
        $owner                        = $ticket->getVar('ownership');
        $tags['TICKET_OWNERSHIP']     = ($owner = 0 ? _XHELP_NO_OWNER : Xhelp\Utility::getUsername($owner, $displayName));
        $tags['TICKET_DEPARTMENT']    = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
        // End of addition by marcan

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITRESPONSE);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($email_tpl = $this->_getEmailTpl('dept', 'modified_response', $this->_module, $template_id)) {  // Notify dept staff
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            if (0 == $response->getVar('private')) {  // Make sure if response is private, don't sent to user
                if ($email_tpl = $this->_getEmailTpl('ticket', 'modified_this_response', $this->_module, $template_id)) {   // Notify ticket submitter
                    $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: batch_dept
     * Triggered after a batch ticket department change
     * @param array $oldTickets The Xhelp\Ticket objects that were modified
     * @param int   $dept       The new department for the tickets
     * @return bool
     */
    public function batch_dept($oldTickets, $dept)
    {
        global $xoopsUser;

        $hDept = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);
        $sDept = $hDept->getNameById($dept);

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITTICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($dept_email_tpl = $this->_getEmailTpl('dept', 'modified_ticket', $this->_module, $template_id)) {            // Send to dept staff
                $deptEmails = $this->_getSubscribedStaff($dept, $dept_email_tpl['bit_value'], $settings, $xoopsUser->getVar('uid'));
            }
        } else {
            $dept_email_tpl = false;
        }

        $user_email_tpl = false;
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'modified_this_ticket', $this->_module, $template_id);
        }

        foreach ($oldTickets as $oldTicket) {
            $tags                           = [];
            $tags['TICKET_OLD_SUBJECT']     = $this->_ts->stripslashesGPC($oldTicket->getVar('subject', 'n'));
            $tags['TICKET_OLD_DESCRIPTION'] = $this->_ts->stripslashesGPC($oldTicket->getVar('description', 'n'));
            $tags['TICKET_OLD_PRIORITY']    = Xhelp\Utility::getPriority($oldTicket->getVar('priority'));
            $tags['TICKET_OLD_STATUS']      = Xhelp\Utility::getStatus($oldTicket->getVar('status'));
            $tags['TICKET_OLD_DEPARTMENT']  = $hDept->getNameById($oldTicket->getVar('department'));
            $tags['TICKET_OLD_DEPTID']      = $oldTicket->getVar('department');

            $tags['TICKET_ID']          = $oldTicket->getVar('id');
            $tags['TICKET_SUBJECT']     = $tags['TICKET_OLD_SUBJECT'];
            $tags['TICKET_DESCRIPTION'] = $tags['TICKET_OLD_DESCRIPTION'];
            $tags['TICKET_PRIORITY']    = $tags['TICKET_OLD_PRIORITY'];
            $tags['TICKET_STATUS']      = $tags['TICKET_OLD_STATUS'];
            $tags['TICKET_MODIFIED']    = $xoopsUser->getVar('uname');
            $tags['TICKET_DEPARTMENT']  = $sDept;
            $tags['TICKET_URL']         = XHELP_BASE_URL . '/ticket.php?id=' . $oldTicket->getVar('id');

            if ($dept_email_tpl) {
                $deptEmails = $this->_getSubscribedStaff($oldTicket, $dept_email_tpl['bit_value'], $settings, $xoopsUser->getVar('uid'));
                $success    = $this->_sendEvents($dept_email_tpl, $deptEmails, $tags);
            }
            if ($user_email_tpl) {
                //$sendTo = $this->_getEmail($oldTicket->getVar('uid'));
                $sendTo  = $this->_getSubscribedUsers($oldTicket->getVar('id'));
                $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_priority
     * Triggered after a batch ticket priority change
     * @param array $tickets  The Xhelp\Ticket objects that were modified
     * @param int   $priority The new ticket priority
     */
    public function batch_priority($tickets, $priority)
    {
        global $xoopsUser;

        list($tickets, $priority) = $args;
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITPRIORITY);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            $dept_email_tpl = $this->_getEmailTpl('dept', 'changed_priority', $this->_module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'changed_this_priority', $this->_module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $uname    = $xoopsUser->getVar('uname');
        $uid      = $xoopsUser->getVar('uid');
        $priority = Xhelp\Utility::getPriority($priority);

        foreach ($tickets as $ticket) {
            $tags                        = [];
            $tags['TICKET_ID']           = $ticket->getVar('id');
            $tags['TICKET_OLD_PRIORITY'] = Xhelp\Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_PRIORITY']     = $priority;
            $tags['TICKET_UPDATEDBY']    = $uname;
            $tags['TICKET_URL']          = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            // Added by marcan to get the ticket's subject available in the mail template
            $tags['TICKET_SUBJECT'] = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
            // End of addition by marcan
            $tags['TICKET_DEPARTMENT'] = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->_getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
            }

            if ($user_email_tpl) {
                //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags);
            }
            unset($tags);
        }
    }

    /**
     * Event: batch_owner
     * Triggered after a batch ticket ownership change
     * @param array $tickets The Xhelp\Ticket objects that were modified
     * @param int   $owner   The XOOPS UID of the new owner
     * @return bool
     */
    public function batch_owner($tickets, $owner)
    {
        //notify old owner, if assigned
        //notify new owner
        //notify submitter
        global $xoopsUser;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITOWNER);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');
        $staff_options = $settings->getVar('staff_options');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            $dept_email_tpl = $this->_getEmailTpl('dept', 'new_owner', $this->_module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'new_this_owner', $this->_module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $new_owner    = Xhelp\Utility::getUsername($owner, $displayName);
        $submitted_by = $xoopsUser->getVar('uname');
        $uid          = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $tags                       = [];
            $tags['TICKET_ID']          = $ticket->getVar('id');
            $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
            $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
            $tags['TICKET_OWNER']       = $new_owner;
            $tags['SUBMITTED_OWNER']    = $submitted_by;
            $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_URL']         = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

            $sendTo = [];
            if (0 <> $ticket->getVar('ownership')) {                               // If there was an owner
                if ($dept_email_tpl) {      // Send them an email
                    if ($this->_isSubscribed($ticket->getVar('ownership'), $dept_email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->_getStaffEmail($ticket->getVar('ownership'), $ticket->getVar('department'), $staff_options);
                        $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
                    }
                }
            }
            if ($owner <> $uid) {
                if ($dept_email_tpl) { // Send new owner email
                    if ($this->_isSubscribed($owner, $dept_email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->_getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                        $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
                    }
                }
            }
            if ($user_email_tpl) {
                //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_status
     * Triggered after a batch ticket status change
     * @param array       $tickets   The Xhelp\Ticket objects that were modified
     * @param Xhelp\Status $newstatus The new ticket status
     * @return bool
     */
    public function batch_status($tickets, $newstatus)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $settings      = $this->_hNotification->get(XHELP_NOTIF_EDITSTATUS);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            $dept_email_tpl = $this->_getEmailTpl('dept', 'changed_status', $this->_module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'changed_this_status', $this->_module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $sStatus = Xhelp\Utility::getStatus($newstatus);
        $uname   = $xoopsUser->getVar('uname');
        $uid     = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $tags               = [];
            $tags['TICKET_ID']  = $ticket->getVar('id');
            $tags['TICKET_URL'] = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');

            // Added by marcan to get the ticket's subject available in the mail template
            $tags['TICKET_SUBJECT'] = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
            // End of addition by marcan

            $tags['TICKET_OLD_STATUS'] = Xhelp\Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_STATUS']     = $sStatus;
            $tags['TICKET_UPDATEDBY']  = $uname;
            $tags['TICKET_DEPARTMENT'] = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->_getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
            }
            if ($user_email_tpl) {
                $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_delete_ticket
     * Triggered after a batch ticket deletion
     * @param array $tickets The Xhelp\Ticket objects that were deleted
     * @return bool
     */
    public function batch_delete_ticket($tickets)
    {
        //notify staff department
        //notify submitter (if ticket is not closed)
        global $xoopsUser, $xoopsModule;

        $uname        = $xoopsUser->getVar('uname');
        $uid          = $xoopsUser->getVar('uid');
        $hStaff       = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
        $isStaff      = $hStaff->isStaff($uid);
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $settings      = $this->_hNotification->get(XHELP_NOTIF_DELTICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            $dept_email_tpl = $this->_getEmailTpl('dept', 'removed_ticket', $this->_module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'removed_this_ticket', $this->_module, $template_id);
        } else {
            $user_email_tpl = false;
        }

        foreach ($tickets as $ticket) {
            $tags                       = [];
            $tags['TICKET_ID']          = $ticket->getVar('id');
            $tags['TICKET_SUBJECT']     = $this->_ts->stripslashesGPC($ticket->getVar('subject', 'n'));
            $tags['TICKET_DESCRIPTION'] = $this->_ts->stripslashesGPC($ticket->getVar('description', 'n'));
            $tags['TICKET_PRIORITY']    = Xhelp\Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_STATUS']      = Xhelp\Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_POSTED']      = $ticket->posted();
            $tags['TICKET_DELETEDBY']   = $uname;
            $tags['TICKET_DEPARTMENT']  = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->_getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
            }

            if ($user_email_tpl) {
                $status = $this->_hStatus->get($ticket->getVar('status'));
                if ((!$isStaff && 2 <> $status->getVar('state')) || $isStaff) {           // Send to ticket submitter
                    //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->_getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags);
                }
            }
        }

        return true;
    }

    /**
     * Event: batch_response
     * Triggered after a batch response addition
     * Note: the $response->getVar('ticketid') field is empty for this function
     * @param array          $tickets  The Xhelp\Ticket objects that were modified
     * @param Xhelp\Responses $response The response added to each ticket
     */
    public function batch_response($tickets, $response)
    {
        global $xoopsUser, $xoopsConfig;
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $displayName  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed
        $responseText = $this->_ts->stripslashesGPC($response->getVar('message', 'n'));
        $uname        = $xoopsUser->getVar('uname');
        $uid          = $xoopsUser->getVar('uid');
        $updated      = formatTimestamp(time(), 'm');
        $private      = $response->getVar('private');
        $hMBoxes      = new Xhelp\DepartmentMailBoxHandler($GLOBALS['xoopsDB']);
        $mBoxes       = $hMBoxes->getObjects(null, true);
        $hDepartments = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        $settings      = $this->_hNotification->get(XHELP_NOTIF_NEWRESPONSE);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');
        $staff_options = $settings->getVar('staff_options');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            $dept_email_tpl = $this->_getEmailTpl('dept', 'new_response', $this->_module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            $user_email_tpl = $this->_getEmailTpl('ticket', 'new_this_response', $this->_module, $template_id);
        } else {
            $user_email_tpl = false;
        }

        foreach ($tickets as $ticket) {
            $bFromEmail                = false;
            $tags                      = [];
            $tags['TICKET_ID']         = $ticket->getVar('id');
            $tags['TICKET_RESPONSE']   = $responseText;
            $tags['TICKET_SUBJECT']    = $ticket->getVar('subject');
            $tags['TICKET_TIMESPENT']  = $response->getVar('timeSpent');
            $tags['TICKET_STATUS']     = Xhelp\Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_RESPONDER']  = $uname;
            $tags['TICKET_POSTED']     = $updated;
            $tags['TICKET_URL']        = XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            $tags['TICKET_DEPARTMENT'] = $this->_ts->stripslashesGPC($hDepartments->getNameById($ticket->getVar('department')));

            $owner = $ticket->getVar('ownership');
            if (0 == $owner) {
                $tags['TICKET_OWNERSHIP'] = _XHELP_NO_OWNER;
            } else {
                $tags['TICKET_OWNERSHIP'] = Xhelp\Utility::getUsername($owner, $displayName);
            }

            if ($ticket->getVar('serverid') > 0) {
                //Ticket was submitted via email
                $mBox = $mBoxes[$ticket->getVar('serverid')];
                if (is_object($mBox)) {
                    $bFromEmail = true;
                }
            }

            if ($bFromEmail) {
                $from                         = $server->getVar('emailaddress');
                $tags['TICKET_SUPPORT_EMAIL'] = $from;
                $tags['TICKET_SUPPORT_KEY']   = '{' . $ticket->getVar('emailHash') . '}';
            } else {
                $from                         = '';
                $tags['TICKET_SUPPORT_EMAIL'] = $xoopsConfig['adminmail'];
                $tags['TICKET_SUPPORT_KEY']   = '';
            }

            $sendTo = [];
            if ($ticket->getVar('uid') <> $uid && 0 == $response->getVar('private')) { // If response from staff member
                if (0 == $private) {
                    if ($user_email_tpl) {
                        $sendTo  = $this->_getUserEmail($ticket->getVar('uid'));
                        $success = $this->_sendEvents($user_email_tpl, $sendTo, $tags, $from);
                    }
                } else {
                    if ($dept_email_tpl) {
                        if (0 <> $ticket->getVar('ownership')) {
                            $sendTo = $this->_getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                        } else {
                            $sendTo = $this->_getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                        }
                    }
                }
            } else {        // If response from submitter
                if ($dept_email_tpl) {
                    if (0 <> $ticket->getVar('ownership')) {  // If ticket has owner, send to owner
                        if ($this->_isSubscribed($owner, $email_tpl['bit_value'])) {    // Check if the owner is subscribed
                            $sendTo = $this->_getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                        }
                    } else {                                    // If ticket has no owner, send to department
                        $sendTo = $this->_getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                    }
                    $success = $this->_sendEvents($dept_email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: merge_tickets
     * Triggered after two tickets are merged
     * @param int $ticket1   First ticketid being merged
     * @param int $ticket2   Second ticketid being merged
     * @param int $newTicket Resulting ticketid after merge
     */
    public function merge_tickets($ticket1, $ticket2, $newTicket)
    {
        global $xoopsUser;
        $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
        $ticket  = $hTicket->get($newTicket);

        $tags                  = [];
        $tags['TICKET_MERGER'] = $xoopsUser->getVar('uname');
        $tags['TICKET1']       = $ticket1;
        $tags['TICKET2']       = $ticket2;
        $tags['TICKET_URL']    = XHELP_BASE_URL . '/ticket.php?id=' . $newTicket;

        $settings      = $this->_hNotification->get(XHELP_NOTIF_MERGETICKET);
        $staff_setting = $settings->getVar('staff_setting');
        $user_setting  = $settings->getVar('user_setting');

        if (XHELP_NOTIF_STAFF_NONE <> $staff_setting) {
            if ($email_tpl = $this->_getEmailTpl('dept', 'merge_ticket', $this->_module, $template_id)) {   // Send email to dept members
                $sendTo  = $this->_getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (XHELP_NOTIF_USER_NO <> $user_setting) {
            if ($email_tpl = $this->_getEmailTpl('ticket', 'merge_this_ticket', $this->_module, $template_id)) {    // Send confirm email to submitter
                //$sendTo = $this->_getEmail($ticket->getVar('uid'));
                $sendTo  = $this->_getSubscribedUsers($newTicket);
                $success = $this->_sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param Xhelp\Ticket $ticket Ticket used as base for FAQ
     * @param Xhelp\Faq    $faq    FAQ that was added
     */
    public function new_faq($ticket, $faq)
    {
    }

    /**
     * Only have 1 instance of class used
     * @return object {@link xhelp_notificationService}
     * @access  public
     */

    public static function getInstance()
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    public function _attachEvents()
    {
        $this->_attachEvent('batch_delete_ticket', $this);
        $this->_attachEvent('batch_dept', $this);
        $this->_attachEvent('batch_owner', $this);
        $this->_attachEvent('batch_priority', $this);
        $this->_attachEvent('batch_response', $this);
        $this->_attachEvent('batch_status', $this);
        $this->_attachEvent('close_ticket', $this);
        $this->_attachEvent('delete_ticket', $this);
        $this->_attachEvent('edit_response', $this);
        $this->_attachEvent('edit_ticket', $this);
        $this->_attachEvent('merge_tickets', $this);
        $this->_attachEvent('new_response', $this);
        $this->_attachEvent('new_ticket', $this);
        $this->_attachEvent('update_owner', $this);
        $this->_attachEvent('update_priority', $this);
        $this->_attachEvent('update_status', $this);
    }
}
