<?php

declare(strict_types=1);

namespace XoopsModules\Xhelp;

if (!\defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

//require_once XHELP_BASE_PATH . '/functions.php';
// require_once XHELP_CLASS_PATH . '/Service.php';

/**
 * NotificationService class
 *
 * Part of the Messaging Subsystem.  Uses the xoopsNotificationHandler class to send emails to users
 *
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class NotificationService extends Service
{
    /**
     * Instance of the staff object
     *
     * @var object
     */
    public $staffHandler;
    /**
     * Instance of the xoops text sanitizer
     *
     * @var object
     */
    public $myTextSanitizer;
    /**
     * Path to the mail_template directory
     *
     * @var string
     */
    public $templateDir = '';
    /**
     * Instance of the module object
     *
     * @var object
     */
    public $module;
    /**
     * Instance of the notification object
     *
     * @var object
     */
    public $notificationHandler;
    /**
     * Instance of the status object
     *
     * @var object
     */
    public $statusHandler;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        global $xoopsConfig, $xoopsModule;
        $db                        = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->myTextSanitizer     = \MyTextSanitizer::getInstance();
        $this->templateDir         = $this->getTemplateDir($xoopsConfig['language']);
        $this->module              = Utility::getModule();
        $this->staffHandler        = new StaffHandler($db);
        $this->notificationHandler = new NotificationHandler($db);
        $this->statusHandler       = new StatusHandler($db);
        $this->helper              = Helper::getInstance();
        $this->init();
    }

    /**
     * Retrieve the email_template object that is requested
     *
     * @param string|int $category ID or name of item
     * @param string     $event    name of event
     * @param object     $module   $xoopsModule object
     * @param mixed      $template_id
     *
     * @return bool|array
     */
    public function getEmailTpl($category, string $event, object $module, &$template_id)
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
     * @param int|string $dept
     * @param array      $aMembers
     * @return array
     */
    public function &getStaffRoles($dept, array $aMembers): array
    {
        $staffRoleHandler = $this->helper->getHandler('StaffRole');

        // Retrieve roles of all members
        $criteria = new \CriteriaCompo(new \Criteria('uid', '(' . \implode(',', $aMembers) . ')', 'IN'));
        $criteria->add(new \Criteria('deptid', $dept));
        $staffRoles = $staffRoleHandler->getObjects($criteria, false);    // array of staff role objects

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
     * @param array $staffRoles
     * @param array $aMembers
     * @param array $staff_options
     * @return array
     */
    public function &getEnabledStaff(array $staffRoles, array $aMembers, array $staff_options): array
    {
        // Get only staff members that have permission for this notification
        $enabled_staff = [];
        foreach ($aMembers as $aMember) {
            foreach ($staffRoles as $staffRole) {
                if ($staffRole->getVar('uid') == $aMember && \in_array($staffRole->getVar('roleid'), $staff_options)) {
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
     * @param array $enabled_staff
     * @param bool  $active_only
     * @return array
     */
    public function &getXoopsUsers(array $enabled_staff, bool $active_only = true): array
    {
        $xoopsUsers = [];
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        if (\count($enabled_staff) > 0) {
            $criteria = new \CriteriaCompo(new \Criteria('uid', '(' . \implode(',', $enabled_staff) . ')', 'IN'));
        } else {
            return $xoopsUsers;
        }
        if ($active_only) {
            $criteria->add(new \Criteria('level', '0', '>'));
        }
        $xoopsUsers = $memberHandler->getUsers($criteria, true);      // xoopsUser objects
        unset($enabled_staff);

        return $xoopsUsers;
    }

    /**
     * Returns only the accepted staff members after having their permissions checked
     * @param array        $aMembers    array of all possible staff members
     * @param Ticket|int   $ticket      xhelp_ticket object
     * @param Notification $settings    xhelp_notification object
     * @param int          $submittedBy ID of ticket submitter
     * @return array  of XoopsUser objects
     */
    public function &checkStaffSetting(array $aMembers, $ticket, Notification $settings, int $submittedBy): array
    {
        //        $submittedBy = (int)$submittedBy;
        $dept = $ticket;
        if (\is_object($ticket)) {
            $dept = $ticket->getVar('department');
        }
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $staff_options = $settings->getVar('staff_options') ?? '';

        $staffRoles    = $this->getStaffRoles($dept, $aMembers);     // Get list of the staff members' roles
        $enabled_staff = $this->getEnabledStaff($staffRoles, $aMembers, $staff_options);
        $xoopsUsers    = $this->getXoopsUsers($enabled_staff);

        return $xoopsUsers;
    }

    /**
     * Returns an array of staff UID's
     *
     * @param array      $members xhelp_staff objects
     * @param string|int $submittedBy
     * @param bool       $removeSubmitter
     * @return array
     */
    private function &makeMemberArray(array $members, $submittedBy, bool $removeSubmitter = false): array
    {
        $aMembers    = [];
        $submittedBy = (int)$submittedBy;
        foreach ($members as $member) {   // Full list of dept members
            if ($removeSubmitter) {
                if ($member->getVar('uid') == $submittedBy) { // Remove the staff member that submitted from the email list
                    continue;
                }

                $aMembers[$member->getVar('uid')] = $member->getVar('uid');
            } else {
                $aMembers[$member->getVar('uid')] = $member->getVar('uid');
            }
        }

        return $aMembers;
    }

    /**
     * Returns emails of staff belonging to an event
     *
     * @param Ticket|int   $ticket
     * @param int          $event_id    bit_value of event
     * @param Notification $settings
     * @param int|null     $submittedBy ID of user submitting event - should only be used when there is a response
     * @return array
     * @internal param int $dept ID of department
     */
    public function &getSubscribedStaff($ticket, int $event_id, Notification $settings, int $submittedBy = null): array
    {
        global $xoopsUser;

        $arr = [];
        /** @var \XoopsModules\Xhelp\MembershipHandler $membershipHandler */
        $membershipHandler = $this->helper->getHandler('Membership');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');

        if (\is_object($ticket)) {
            if (!$submittedBy) {
                $submittedBy = $ticket->getVar('uid');
            }
            $owner = $ticket->getVar('ownership');
            $dept  = $ticket->getVar('department');
        } else {
            $dept = (int)$ticket;
        }
        $submittedBy = (int)$submittedBy;

        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $staff_options = $settings->getVar('staff_options') ?? '';
        switch ($staff_setting) {
            case \XHELP_NOTIF_STAFF_DEPT:   // Department Staff can receive notification
                $members    = $membershipHandler->membershipByDept($dept);  // Staff objects
                $aMembers   = $this->makeMemberArray($members, $submittedBy, true);
                $xoopsUsers = $this->checkStaffSetting($aMembers, $ticket, $settings, $submittedBy);
                break;
            case \XHELP_NOTIF_STAFF_OWNER:   // Ticket Owner can receive notification
                $members = $membershipHandler->membershipByDept($dept);
                if (0 != $ticket->getVar('ownership')) {      // If there is a ticket owner
                    $ticket_owner            = $ticket->getVar('ownership');
                    $aMembers[$ticket_owner] = $ticket_owner;
                    $criteria                = new \Criteria('uid', '(' . \implode(',', $aMembers) . ')', 'IN');
                    unset($aMembers);
                    $xoopsUsers = $memberHandler->getUsers($criteria, true);      // xoopsUser objects
                } else {                                    // If no ticket owner, send to dept staff
                    $aMembers   = $this->makeMemberArray($members, 0, true);
                    $xoopsUsers = $this->checkStaffSetting($aMembers, $ticket, $settings, $submittedBy);
                }
                break;
            case \XHELP_NOTIF_STAFF_NONE:   // Notification is turned off
            default:
                return $arr;
        }

        //Sort users based on Notification Preference
        foreach ($xoopsUsers as $xUser) {
            $cMember = $members[$xUser->getVar('uid')];

            if (!empty($cMember) && ($xUser->uid() != $xoopsUser->uid())) {
                if ($this->isSubscribed($cMember, $event_id)) {
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
     * @param int $ticketId ID of ticket
     * @return array
     */
    public function &getSubscribedUsers(int $ticketId): array
    {
        global $xoopsUser;

        //        $ticketId = (int)$ticketId;

        /** @var \XoopsModules\Xhelp\TicketEmailsHandler $ticketEmailsHandler */
        $ticketEmailsHandler = $this->helper->getHandler('TicketEmails');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');

        //Get all Subscribed users, except for the current user
        $criteria = new \CriteriaCompo(new \Criteria('ticketid', (string)$ticketId));
        $criteria->add(new \Criteria('suppress', '0'));
        $criteria->add(new \Criteria('email', $xoopsUser->email(), '<>'));

        $users = $ticketEmailsHandler->getObjects($criteria);    // xhelp_ticketEmail objects

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
            $criteria   = new \Criteria('uid', '(' . \implode(',', $aUsers) . ')', 'IN');
            $xoopsUsers = $memberHandler->getUsers($criteria, true);  // xoopsUser objects
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
     * @param int|Staff $user     userid/staff object of staff member
     * @param int       $event_id value of the event
     * @return bool              true is suscribed, false if not
     */
    public function isSubscribed($user, int $event_id): bool
    {
        if (!\is_object($user)) {          //If user is not an object, retrieve a staff object using the uid
            if (\is_numeric($user)) {
                $uid          = $user;
                $staffHandler = $this->helper->getHandler('Staff');
                if (!$user = $staffHandler->getByUid($uid)) {
                    return false;
                }
            }
        }

        return ($user->getVar('notify') & (2 ** $event_id)) > 0;
    }

    /**
     * Retrieve a user's email address
     *
     * @param int $uid user's id
     * @return array array's email
     */
    public function getUserEmail(int $uid): array
    {
        global $xoopsUser;
        $arr = [];
        $uid = $uid;

        if ($uid == $xoopsUser->getVar('uid')) {      // If $uid == current user's uid
            if (2 == $xoopsUser->getVar('notify_method')) {
                $arr['email'][] = $xoopsUser->getVar('email');     // return their email
            } elseif (1 == $xoopsUser->getVar('notify_method')) {
                $arr['pm'][] = $xoopsUser;
            }
        } else {
            /** @var \XoopsMemberHandler $memberHandler */
            $memberHandler = \xoops_getHandler('member');     //otherwise...
            $member        = $memberHandler->getUser($uid);
            if ($member) {
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
     * @param int             $uid user's id
     * @param int|string|null $dept
     * @param array|null      $staff_options
     * @return array member's email
     */
    public function getStaffEmail(int $uid, $dept = null, ?array $staff_options = null): array
    {
        $uid  = $uid;
        $dept = (int)$dept;
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $arr           = [];

        // Check staff roles to staff options making sure the staff has permission
        $staffRoles = $this->staffHandler->getRolesByDept($uid, $dept, true);
        $bFound     = true;
        foreach ($staff_options as $option) {
            if (\array_key_exists($option, $staffRoles)) {
                $bFound = true;
                break;
            }

            $bFound = false;
        }
        if (!$bFound) {
            return $arr;
        }

        $staff = $this->staffHandler->getByUid($uid);
        if ($staff) {
            $member = $memberHandler->getUser($uid);
            if ($member) {
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
     * @param object|array $email_tpl object returned from getEmailTpl() function
     * @param array        $sendTo    emails and xoopsUser objects
     * @param array        $tags      array of notification information
     * @param string       $fromEmail
     * @return bool TRUE if success, FALSE if no success
     */
    public function sendEvents($email_tpl, array $sendTo, array $tags, string $fromEmail = ''): bool
    {
        $ret = true;
        if (\array_key_exists('pm', $sendTo)) {
            $ret = $ret && $this->sendEventPM($email_tpl, $sendTo, $tags, $fromEmail);
        }

        if (\array_key_exists('email', $sendTo)) {
            $ret = $ret && $this->sendEventEmail($email_tpl, $sendTo, $tags, $fromEmail);
        }

        return $ret;
    }

    /**
     * Send the pm notification to selected users
     *
     * @param object|array $email_tpl object returned from getEmailTpl() function
     * @param array        $sendTo    xoopsUser objects
     * @param array        $tags      array of notification information
     * @param string       $fromEmail
     * @return bool TRUE if success, FALSE if no success
     */
    public function sendEventPM($email_tpl, array $sendTo, array $tags, string $fromEmail = ''): bool
    {
        $notify_pm = '';
        global $xoopsConfig, $xoopsUser;

        $notify_pm = $sendTo['pm'];

        $tags        = \array_merge($tags, $this->getCommonTplVars());          // Retrieve the common template vars and add to array
        $xoopsMailer = \xoops_getMailer();
        $xoopsMailer->usePM();

        foreach ($tags as $k => $v) {
            $xoopsMailer->assign($k, \preg_replace('/&amp;/i', '&', $v));
        }
        $xoopsMailer->setTemplateDir($this->templateDir);                // Set template dir
        $xoopsMailer->setTemplate($email_tpl['mail_template'] . '.tpl'); // Set the template to be used

        /** @var \XoopsConfigHandler $configHandler */
        $configHandler = \xoops_getHandler('config');
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler     = \xoops_getHandler('member');
        $xoopsMailerConfig = $configHandler->getConfigsByCat(\XOOPS_CONF_MAILER);
        $xoopsMailer->setFromUser($memberHandler->getUser($xoopsMailerConfig['fromuid']));
        $xoopsMailer->setToUsers($notify_pm);
        $xoopsMailer->setSubject($email_tpl['mail_subject']);           // Set the subject of the email
        $xoopsMailer->setFromName($xoopsConfig['sitename']);            // Set a from address
        $success = $xoopsMailer->send(true);

        return $success;
    }

    /**
     * Send the mail notification to selected users
     *
     * @param object|array $email_tpl object returned from getEmailTpl() function
     * @param array        $sendTo    emails returned from getSubscribedStaff() function
     * @param array        $tags      array of notification information
     * @param string       $fromEmail
     * @return bool TRUE if success, FALSE if no success
     */
    public function sendEventEmail($email_tpl, array $sendTo, array $tags, string $fromEmail = ''): bool
    {
        $notify_email = '';
        global $xoopsConfig;

        $notify_email = $sendTo['email'];

        $tags        = \array_merge($tags, $this->getCommonTplVars());          // Retrieve the common template vars and add to array
        $xoopsMailer = \xoops_getMailer();
        $xoopsMailer->useMail();

        foreach ($tags as $k => $v) {
            $xoopsMailer->assign($k, \preg_replace('/&amp;/i', '&', $v));
        }
        $xoopsMailer->setTemplateDir($this->templateDir);                // Set template dir
        $xoopsMailer->setTemplate($email_tpl['mail_template'] . '.tpl'); // Set the template to be used
        if ('' !== $fromEmail) {
            $xoopsMailer->setFromEmail($fromEmail);
        }
        $xoopsMailer->setToEmails($notify_email);                           // Set who the email goes to
        $xoopsMailer->setSubject($email_tpl['mail_subject']);               // Set the subject of the email
        $xoopsMailer->setFromName($xoopsConfig['sitename']);                // Set a from address
        $success = $xoopsMailer->send(true);

        return $success;
    }

    /**
     * Get a list of the common constants required for notifications
     *
     * @return array
     */
    public function &getCommonTplVars(): array
    {
        global $xoopsConfig;
        $tags                 = [];
        $tags['X_MODULE']     = $this->module->getVar('name');
        $tags['X_SITEURL']    = \XHELP_SITE_URL;
        $tags['X_SITENAME']   = $xoopsConfig['sitename'];
        $tags['X_ADMINMAIL']  = $xoopsConfig['adminmail'];
        $tags['X_MODULE_URL'] = \XHELP_BASE_URL . '/';

        return $tags;
    }

    /**
     * Retrieve the directory where mail templates are stored
     *
     * @param string $language language used for xoops
     * @return string
     */
    public function getTemplateDir(string $language): string
    {
        $path = XOOPS_ROOT_PATH . '/modules/xhelp/language/' . $language . '/mail_template';
        if (\is_dir($path)) {
            return $path;
        }

        return XOOPS_ROOT_PATH . '/modules/xhelp/language/english/mail_template';
    }

    /**
     * Returns the number of department notifications
     *
     * @return int number of department notifications
     */
    public function getNumDeptNotifications(): int
    {
        $num       = 0;
        $templates = $this->module->getInfo('_email_tpl');
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
     * @param int $uid uid of the user
     * @return array email of user
     */
    public function getEmail(int $uid): array
    {
        if (!$isStaff = $this->staffHandler->isStaff($uid)) {
            return $this->getUserEmail($uid);
        }

        return $this->getStaffEmail($uid);
    }

    /**
     * Confirm submission to user and notify staff members when new_ticket is triggered
     * @param Ticket $ticket Ticket that was added
     */
    public function new_ticket(Ticket $ticket)
    {
        global $xhelp_isStaff;
        global $xoopsUser;
        $helper = Helper::getInstance();

        $departmentHandler = $this->helper->getHandler('Department');
        $displayName       = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_POSTED']      = $ticket->posted();
        $tags['TICKET_CREATED']     = Utility::getUsername($ticket->getVar('uid'), $displayName);
        $tags['TICKET_SUPPORT_KEY'] = ($ticket->getVar('serverid') ? '{' . $ticket->getVar('emailHash') . '}' : '');
        $tags['TICKET_URL']         = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings = $this->notificationHandler->get(\XHELP_NOTIF_NEWTICKET);
        //        $settings      = $this->notificationHandler->create();
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {    // If staff notification is enabled
            $email_tpl = $this->getEmailTpl('dept', 'new_ticket', $this->module, $template_id);
            if ($email_tpl) {  // Send email to dept members
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {     // If user notification is enabled
            if ($ticket->getVar('serverid') > 0) {
                //this ticket has been submitted by email
                //get department email address
                $departmentMailBoxHandler = $this->helper->getHandler('DepartmentMailBox');
                $server                   = $departmentMailBoxHandler->get($ticket->getVar('serverid'));

                $tags['TICKET_SUPPORT_EMAIL'] = $server->getVar('emailaddress');

                $email_tpl = $this->getEmailTpl('ticket', 'new_this_ticket_via_email', $this->module, $template_id);
                if ($email_tpl) {
                    $sendTo  = $this->getUserEmail($ticket->getVar('uid'));
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags, $server->getVar('emailaddress'));
                }
            } else { //this ticket has been submitted via the website
                if (!$xhelp_isStaff) {
                    $email_tpl = $this->getEmailTpl('ticket', 'new_this_ticket', $this->module, $template_id);
                    if ($email_tpl) {                                                                                                                                                                                                                                  // Send confirm email to submitter
                        $sendTo  = $this->getUserEmail(
                            $ticket->getVar('uid')
                        );                                                                                                                                                                                                                                             // Will be the only person subscribed
                        $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string     $password Password for new account
     * @param \XoopsUser $user     XOOPS user object for new account
     */
    public function new_user_by_email(string $password, \XoopsUser $user)
    {
        // Send Welcome Email to submitter
        //global $xoopsUser;

        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $user->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $user->getVar('email');
        $tags['XOOPS_USER_ID']       = $user->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;
        $tags['X_UACTLINK']          = \XHELP_SITE_URL . '/user.php?op=actv&id=' . $user->getVar('uid') . '&actkey=' . $user->getVar('actkey');

        $email_tpl = $this->getEmailTpl('ticket', 'new_user_byemail', $this->module, $template_id);
        if ($email_tpl) {
            $sendTo  = $this->getUserEmail($user->getVar('uid'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string     $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation0(string $password, \XoopsUser $xoopsUser)
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
        $tags['X_UACTLINK']          = \XHELP_SITE_URL . '/user.php?op=actv&id=' . $newuser->getVar('uid') . '&actkey=' . $newuser->getVar('actkey');

        $email_tpl = $this->getEmailTpl('ticket', 'new_user_byemail', $this->module, $template_id);
        if ($email_tpl) {
            $sendTo  = $this->getUserEmail($newuser->getVar('uid'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string     $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation1(string $password, \XoopsUser $xoopsUser)
    {
        $tags                        = [];
        $tags['XOOPS_USER_NAME']     = $user->getVar('uname');
        $tags['XOOPS_USER_EMAIL']    = $user->getVar('email');
        $tags['XOOPS_USER_ID']       = $user->getVar('uname');
        $tags['XOOPS_USER_PASSWORD'] = $password;

        $email_tpl = $this->getEmailTpl('ticket', 'new_user_activation1', $this->module, $template_id);
        if ($email_tpl) {
            $sendTo  = $this->getUserEmail($user->getVar('uid'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }

        $_POST['uname'] = $user->getVar('uname');
        $_POST['pass']  = $password;

        // For backward compatibility
        $_POST['uname'] = $user->getVar('uname');
        $_POST['pass']  = $password;

        //        $filename   = XOOPS_ROOT_PATH . '/kernel/authenticationservice.php';
        //        $foldername = XOOPS_ROOT_PATH . '/include/authenticationservices';
        //        if (\file_exists($filename) && \file_exists($foldername)) {     // check for ldap authentication hack
        //            /** @var \XoopsAuthFactory $authentication_service */
        //            $authentication_service = \xoops_getHandler('authenticationservice');
        //            if ($authentication_service) {
        //                $authentication_service->checkLogin();
        //            } else {
        //                require_once XOOPS_ROOT_PATH . '/include/checklogin.php';
        //            }
        //        } else {
        //            require_once XOOPS_ROOT_PATH . '/include/checklogin.php';
        //        }
        require_once XOOPS_ROOT_PATH . '/include/checklogin.php';
    }

    /**
     * Event: new_user_by_email
     * Triggered after new user account is created during ticket submission
     * @param string     $password  Password for new account
     * @param \XoopsUser $xoopsUser XOOPS user object for new account
     */
    public function new_user_activation2(string $password, \XoopsUser $xoopsUser)
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

        $email_tpl = $this->getEmailTpl('ticket', 'new_user_activation2', $this->module, $template_id);
        if ($email_tpl) {
            $sendTo  = $this->getUserEmail($user->getVar('uid'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: new_response
     * Triggered after a response has been added to a ticket
     * @param Ticket|int $ticket   Ticket containing response
     * @param Response   $response Response that was added
     */
    public function new_response($ticket, Response $response)
    {
        // If response is from staff member, send message to ticket submitter
        // If response is from submitter, send message to owner, if no owner, send to department

        global $xoopsUser, $xoopsConfig;
        $helper = Helper::getInstance();

        $departmentHandler = $this->helper->getHandler('Department');

        if (!\is_object($ticket) && 0 === $ticket) {
            $ticketHandler = $this->helper->getHandler('Ticket');
            $ticket        = $ticketHandler->get($response->getVar('ticketid'));
        }

        $b_email_ticket = false;
        $from           = '';

        $tags                         = [];
        $tags['TICKET_ID']            = $ticket->getVar('id');
        $tags['TICKET_URL']           = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_RESPONSE']      = ($response->getVar('message', 'n'));
        $tags['TICKET_SUBJECT']       = ($ticket->getVar('subject', 'n'));
        $tags['TICKET_TIMESPENT']     = $response->getVar('timeSpent');
        $tags['TICKET_STATUS']        = Utility::getStatus($ticket->getVar('status'));
        $displayName                  = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed
        $tags['TICKET_RESPONDER']     = Utility::getUsername($xoopsUser->getVar('uid'), $displayName);
        $tags['TICKET_POSTED']        = $response->posted('m');
        $tags['TICKET_SUPPORT_KEY']   = '';
        $tags['TICKET_SUPPORT_EMAIL'] = $xoopsConfig['adminmail'];

        if ($ticket->getVar('serverid') > 0) {
            $departmentMailBoxHandler = $this->helper->getHandler('DepartmentMailBox');

            $server = $departmentMailBoxHandler->get($ticket->getVar('serverid'));
            if ($server) {
                $from                         = $server->getVar('emailaddress');
                $tags['TICKET_SUPPORT_KEY']   = '{' . $ticket->getVar('emailHash') . '}';
                $tags['TICKET_SUPPORT_EMAIL'] = $from;
            }
        }
        $owner = $ticket->getVar('ownership');
        if (0 == $owner) {
            $tags['TICKET_OWNERSHIP'] = \_XHELP_NO_OWNER;
        } else {
            $tags['TICKET_OWNERSHIP'] = Utility::getUsername($owner, $displayName);
        }
        $tags['TICKET_DEPARTMENT'] = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_NEWRESPONSE);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        $sendTo = [];
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler  = \xoops_getHandler('member');
        $response_user  = $memberHandler->getUser($response->getVar('uid'));
        $response_email = $response_user->getVar('email');

        $aUsers = $this->getSubscribedUsers($ticket->getVar('id'));

        if (\in_array($response_email, $aUsers)) {  // If response from a submitter, send to staff and other submitters
            if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {        // Staff notification is enabled
                $email_tpl = $this->getEmailTpl('dept', 'new_response', $this->module, $template_id);
                if ($email_tpl) {       // Send to staff members
                    $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                }
            }
            unset($aUsers[$ticket->getVar('uid')]); // Remove response submitter from array
            $sendTo = $aUsers;                      // Get array of user emails to send
        } else {    // If response from staff, send to submitters
            // Also send to staff members if no owner
            if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {    // If notification is on
                $email_tpl = $this->getEmailTpl('dept', 'new_response', $this->module, $template_id);
                if ($email_tpl) {       // Send to staff members
                    if (0 == $ticket->getVar('ownership')) {
                        $sendTo = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                    }
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                }
            }
            $sendTo = $aUsers;
        }
        if (2 != $user_setting && 0 === $response->getVar('private')) {
            $email_tpl = $this->getEmailTpl('ticket', 'new_this_response', $this->module, $template_id);
            if ($email_tpl) {    // Send to users
                $success = $this->sendEvents($email_tpl, $sendTo, $tags, $from);
            }
        }
    }

    /**
     * Event: update_priority
     * Triggered after a ticket priority is modified
     * Also See: batch_priority
     * @param Ticket $ticket      Ticket that was modified
     * @param int    $oldpriority Previous ticket priority
     */
    public function update_priority(Ticket $ticket, int $oldpriority)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;

        $departmentHandler = $this->helper->getHandler('Department');

        $tags               = [];
        $tags['TICKET_ID']  = $ticket->getVar('id');
        $tags['TICKET_URL'] = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = ($ticket->getVar('subject', 'n'));
        // End of addition by marcan
        $tags['TICKET_OLD_PRIORITY'] = Utility::getPriority($oldpriority);
        $tags['TICKET_PRIORITY']     = Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_UPDATEDBY']    = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT']   = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITPRIORITY);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        $email_tpl = $this->getEmailTpl('dept', 'changed_priority', $this->module, $template_id);
        if ($email_tpl) {   // Notify staff dept
            $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
        $email_tpl = $this->getEmailTpl('ticket', 'changed_this_priority', $this->module, $template_id);
        if ($email_tpl) {    // Notify submitter
            $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: update_status
     * Triggered after a ticket status change
     * Also See: batch_status, close_ticket, reopen_ticket
     * @param Ticket $ticket    The ticket that was modified
     * @param Status $oldstatus The previous ticket status
     * @param Status $newstatus The new ticket status
     */
    public function update_status(Ticket $ticket, Status $oldstatus, Status $newstatus)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $departmentHandler = $this->helper->getHandler('Department');

        $tags               = [];
        $tags['TICKET_ID']  = $ticket->getVar('id');
        $tags['TICKET_URL'] = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = ($ticket->getVar('subject', 'n'));
        // End of addition by marcan
        $tags['TICKET_OLD_STATUS'] = $oldstatus->getVar('description');
        $tags['TICKET_OLD_STATE']  = Utility::getState($oldstatus->getVar('state'));
        $tags['TICKET_STATUS']     = $newstatus->getVar('description');
        $tags['TICKET_STATE']      = Utility::getState($newstatus->getVar('state'));
        $tags['TICKET_UPDATEDBY']  = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT'] = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITSTATUS);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        $email_tpl = $this->getEmailTpl('dept', 'changed_status', $this->module, $template_id);
        if ($email_tpl) {
            $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
        $email_tpl = $this->getEmailTpl('ticket', 'changed_this_status', $this->module, $template_id);
        if ($email_tpl) {
            //$sendTo = $this->getEmail($ticket->getVar('uid'));
            $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
            $success = $this->sendEvents($email_tpl, $sendTo, $tags);
        }
    }

    /**
     * Event: update_owner
     * Triggered after ticket ownership change (Individual)
     * Also See: batch_owner
     * @param Ticket          $ticket   Ticket that was changed
     * @param int|string|null $oldOwner UID of previous owner
     * @param int             $newOwner UID of new owner
     */
    public function update_owner(Ticket $ticket, $oldOwner, int $newOwner)
    {
        //notify old owner, if assigned
        //notify new owner
        //notify submitter
        global $xoopsUser;
        $helper = Helper::getInstance();

        $departmentHandler = $this->helper->getHandler('Department');

        $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_URL']         = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
        $tags['TICKET_OWNER']       = Utility::getUsername($ticket->getVar('ownership'), $displayName);
        $tags['SUBMITTED_OWNER']    = $xoopsUser->getVar('uname');
        $tags['TICKET_STATUS']      = Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_PRIORITY']    = Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITOWNER);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';
        $staff_options = $settings->getVar('staff_options') ?? '';

        $sendTo = [];
        if (\XHELP_NOTIF_STAFF_OWNER == $staff_setting) {
            if (!empty($oldOwner)
                && \_XHELP_NO_OWNER != $oldOwner) {                               // If there was an owner
                $email_tpl = $this->getEmailTpl('dept', 'new_owner', $this->module, $template_id);
                if ($email_tpl) {      // Send them an email
                    if ($this->isSubscribed($oldOwner, $email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->getStaffEmail($oldOwner, $ticket->getVar('department'), $staff_options);
                        $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
            if (0 != $ticket->getVar('ownership')
                && $ticket->getVar('ownership') != $xoopsUser->getVar('uid')) { // If owner is not current user
                $email_tpl = $this->getEmailTpl('dept', 'new_owner', $this->module, $template_id);
                if ($email_tpl) {      // Send new owner email
                    if ($this->isSubscribed($ticket->getVar('ownership'), $email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->getStaffEmail($ticket->getVar('ownership'), $ticket->getVar('department'), $staff_options);
                        $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                    }
                }
            }
        } elseif (\XHELP_NOTIF_STAFF_DEPT == $staff_setting) { // Notify entire department
            $email_tpl = $this->getEmailTpl('dept', 'new_owner', $this->module, $template_id);
            if ($email_tpl) {
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $email_tpl = $this->getEmailTpl('ticket', 'new_this_owner', $this->module, $template_id);
            if ($email_tpl) {   // Send to ticket submitter
                $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: close_ticket
     * Triggered after a ticket's status change from a status
     * with a state of XHELP_STATE_UNRESOLVED to a status
     * with a state of XHELP_STATE_RESOLVED
     * Also See: update_status, reopen_ticket
     * @param Ticket $ticket The ticket that was closed
     */
    public function close_ticket(Ticket $ticket)
    {
        global $xoopsUser;
        $departmentHandler = $this->helper->getHandler('Department');

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
        $tags['TICKET_STATUS']      = Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_CLOSEDBY']    = $xoopsUser->getVar('uname');
        $tags['TICKET_URL']         = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_CLOSETICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        $sendTo = [];
        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $email_tpl = $this->getEmailTpl('dept', 'close_ticket', $this->module, $template_id);
            if ($email_tpl) {        // Send to department, not to staff member
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            if ($xoopsUser->getVar('uid') != $ticket->getVar('uid')) {        // If not closed by submitter
                $email_tpl = $this->getEmailTpl('ticket', 'close_this_ticket', $this->module, $template_id);
                if ($email_tpl) {        // Send to submitter
                    //$sendTo = $this->getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: delete_ticket
     * Triggered after a ticket is deleted
     * @param Ticket $ticket Ticket that was deleted
     */
    public function delete_ticket(Ticket $ticket)
    {
        //notify staff department
        //notify submitter
        global $xoopsUser, $xoopsModule;
        $departmentHandler = $this->helper->getHandler('Department');

        $tags                       = [];
        $tags['TICKET_ID']          = $ticket->getVar('id');
        $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Utility::getPriority($ticket->getVar('priority'));
        $tags['TICKET_STATUS']      = Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_POSTED']      = $ticket->posted();
        $tags['TICKET_DELETEDBY']   = $xoopsUser->getVar('uname');
        $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_DELTICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $email_tpl = $this->getEmailTpl('dept', 'removed_ticket', $this->module, $template_id);
            if ($email_tpl) { // Send to dept staff
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $status = $this->statusHandler->get($ticket->getVar('status'));
            if (2 != $status->getVar('state')) {
                $email_tpl = $this->getEmailTpl('ticket', 'removed_this_ticket', $this->module, $template_id);
                if ($email_tpl) {  // Send to submitter
                    //$sendTo = $this->getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: edit_ticket
     * Triggered after a ticket is modified
     * @param array|Ticket $oldTicket  Ticket information before modifications
     * @param Ticket       $ticketInfo Ticket information after modifications
     */
    public function edit_ticket($oldTicket, Ticket $ticketInfo)
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $departmentHandler = $this->helper->getHandler('Department');

        $tags                           = [];
        $tags['TICKET_URL']             = \XHELP_BASE_URL . '/ticket.php?id=' . $ticketInfo->getVar('id');
        $tags['TICKET_OLD_SUBJECT']     = ($oldTicket['subject']);
        $tags['TICKET_OLD_DESCRIPTION'] = ($oldTicket['description']);
        $tags['TICKET_OLD_PRIORITY']    = Utility::getPriority($oldTicket['priority']);
        $tags['TICKET_OLD_STATUS']      = $oldTicket['status'];
        $tags['TICKET_OLD_DEPARTMENT']  = $oldTicket['department'];
        $tags['TICKET_OLD_DEPTID']      = $oldTicket['department_id'];

        $tags['TICKET_ID']          = $ticketInfo->getVar('id');
        $tags['TICKET_SUBJECT']     = ($ticketInfo->getVar('subject', 'n'));
        $tags['TICKET_DESCRIPTION'] = ($ticketInfo->getVar('description', 'n'));
        $tags['TICKET_PRIORITY']    = Utility::getPriority($ticketInfo->getVar('priority'));
        $tags['TICKET_STATUS']      = Utility::getStatus($ticketInfo->getVar('status'));
        $tags['TICKET_MODIFIED']    = $xoopsUser->getVar('uname');
        if ($tags['TICKET_OLD_DEPTID'] != $ticketInfo->getVar('department')) {
            $department                = $departmentHandler->get($ticketInfo->getVar('department'));
            $tags['TICKET_DEPARTMENT'] = $department->getVar('department');
        } else {
            $tags['TICKET_DEPARTMENT'] = $tags['TICKET_OLD_DEPARTMENT'];
        }

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITTICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $email_tpl = $this->getEmailTpl('dept', 'modified_ticket', $this->module, $template_id);
            if ($email_tpl) {            // Send to dept staff
                $sendTo  = $this->getSubscribedStaff($ticketInfo, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $email_tpl = $this->getEmailTpl('ticket', 'modified_this_ticket', $this->module, $template_id);
            if ($email_tpl) {     // Send to ticket submitter
                $sendTo  = $this->getSubscribedUsers($ticketInfo->getVar('id'));
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: edit_response
     * Triggered after a response has been modified
     * Also See: new_response
     * @param Ticket   $ticket
     * @param Response $response    Modified response
     * @param Ticket   $oldticket   Ticket before modifications
     * @param Response $oldresponse Response modifications
     * @internal param Ticket $nticket Ticket after modifications
     */
    public function edit_response(Ticket $ticket, Response $response, Ticket $oldticket, Response $oldresponse)
    {
        //if not modified by response submitter, notify response submitter
        //notify ticket submitter
        global $xoopsUser;
        $helper = Helper::getInstance();

        $departmentHandler = $this->helper->getHandler('Department');
        $displayName       = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $tags                         = [];
        $tags['TICKET_URL']           = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
        $tags['TICKET_OLD_RESPONSE']  = ($oldresponse->getVar('message', 'n'));
        $tags['TICKET_OLD_TIMESPENT'] = $oldresponse->getVar('timeSpent');
        $tags['TICKET_OLD_STATUS']    = Utility::getStatus($oldticket->getVar('status'));
        $tags['TICKET_OLD_RESPONDER'] = Utility::getUsername($oldresponse->getVar('uid'), $displayName);
        $owner                        = $oldticket->getVar('ownership');
        $tags['TICKET_OLD_OWNERSHIP'] = ($owner = 0 ? \_XHELP_NO_OWNER : Utility::getUsername($owner, $displayName));
        $tags['TICKET_ID']            = $ticket->getVar('id');
        $tags['RESPONSE_ID']          = $response->getVar('id');
        $tags['TICKET_RESPONSE']      = ($response->getVar('message', 'n'));
        $tags['TICKET_TIMESPENT']     = $response->getVar('timeSpent');
        $tags['TICKET_STATUS']        = Utility::getStatus($ticket->getVar('status'));
        $tags['TICKET_RESPONDER']     = $xoopsUser->getVar('uname');
        $tags['TICKET_POSTED']        = $response->posted();
        $owner                        = $ticket->getVar('ownership');
        $tags['TICKET_OWNERSHIP']     = ($owner = 0 ? \_XHELP_NO_OWNER : Utility::getUsername($owner, $displayName));
        $tags['TICKET_DEPARTMENT']    = ($departmentHandler->getNameById($ticket->getVar('department')));

        // Added by marcan to get the ticket's subject available in the mail template
        $tags['TICKET_SUBJECT'] = ($ticket->getVar('subject', 'n'));
        // End of addition by marcan

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITRESPONSE);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $email_tpl = $this->getEmailTpl('dept', 'modified_response', $this->module, $template_id);
            if ($email_tpl) {  // Notify dept staff
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings, $response->getVar('uid'));
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            if (0 == $response->getVar('private')) {  // Make sure if response is private, don't sent to user
                $email_tpl = $this->getEmailTpl('ticket', 'modified_this_response', $this->module, $template_id);
                if ($email_tpl) {   // Notify ticket submitter
                    $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->sendEvents($email_tpl, $sendTo, $tags);
                }
            }
        }
    }

    /**
     * Event: batch_dept
     * Triggered after a batch ticket department change
     * @param array $oldTickets The Ticket objects that were modified
     * @param int   $dept       The new department for the tickets
     * @return bool
     */
    public function batch_dept(array $oldTickets, int $dept): bool
    {
        global $xoopsUser;

        $departmentHandler = $this->helper->getHandler('Department');
        $sDept             = $departmentHandler->getNameById($dept);

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITTICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'modified_ticket', $this->module, $template_id);
            if ($dept_email_tpl) {            // Send to dept staff
                $deptEmails = $this->getSubscribedStaff($dept, $dept_email_tpl['bit_value'], $settings, $xoopsUser->getVar('uid'));
            }
        } else {
            $dept_email_tpl = false;
        }

        $user_email_tpl = false;
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'modified_this_ticket', $this->module, $template_id);
        }

        foreach ($oldTickets as $oldTicket) {
            $tags                           = [];
            $tags['TICKET_OLD_SUBJECT']     = ($oldTicket->getVar('subject', 'n'));
            $tags['TICKET_OLD_DESCRIPTION'] = ($oldTicket->getVar('description', 'n'));
            $tags['TICKET_OLD_PRIORITY']    = Utility::getPriority($oldTicket->getVar('priority'));
            $tags['TICKET_OLD_STATUS']      = Utility::getStatus($oldTicket->getVar('status'));
            $tags['TICKET_OLD_DEPARTMENT']  = $departmentHandler->getNameById($oldTicket->getVar('department'));
            $tags['TICKET_OLD_DEPTID']      = $oldTicket->getVar('department');

            $tags['TICKET_ID']          = $oldTicket->getVar('id');
            $tags['TICKET_SUBJECT']     = $tags['TICKET_OLD_SUBJECT'];
            $tags['TICKET_DESCRIPTION'] = $tags['TICKET_OLD_DESCRIPTION'];
            $tags['TICKET_PRIORITY']    = $tags['TICKET_OLD_PRIORITY'];
            $tags['TICKET_STATUS']      = $tags['TICKET_OLD_STATUS'];
            $tags['TICKET_MODIFIED']    = $xoopsUser->getVar('uname');
            $tags['TICKET_DEPARTMENT']  = $sDept;
            $tags['TICKET_URL']         = \XHELP_BASE_URL . '/ticket.php?id=' . $oldTicket->getVar('id');

            if ($dept_email_tpl) {
                $deptEmails = $this->getSubscribedStaff($oldTicket, $dept_email_tpl['bit_value'], $settings, $xoopsUser->getVar('uid'));
                $success    = $this->sendEvents($dept_email_tpl, $deptEmails, $tags);
            }
            if ($user_email_tpl) {
                //$sendTo = $this->getEmail($oldTicket->getVar('uid'));
                $sendTo  = $this->getSubscribedUsers($oldTicket->getVar('id'));
                $success = $this->sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_priority
     * Triggered after a batch ticket priority change
     * @param array $tickets  The Ticket objects that were modified
     * @param int   $priority The new ticket priority
     */
    public function batch_priority(array $tickets, int $priority)
    {
        global $xoopsUser;

        [$tickets, $priority] = $args;
        $departmentHandler = $this->helper->getHandler('Department');

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITPRIORITY);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'changed_priority', $this->module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'changed_this_priority', $this->module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $uname    = $xoopsUser->getVar('uname');
        $uid      = $xoopsUser->getVar('uid');
        $priority = Utility::getPriority($priority);

        foreach ($tickets as $ticket) {
            $tags                        = [];
            $tags['TICKET_ID']           = $ticket->getVar('id');
            $tags['TICKET_OLD_PRIORITY'] = Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_PRIORITY']     = $priority;
            $tags['TICKET_UPDATEDBY']    = $uname;
            $tags['TICKET_URL']          = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            // Added by marcan to get the ticket's subject available in the mail template
            $tags['TICKET_SUBJECT'] = ($ticket->getVar('subject', 'n'));
            // End of addition by marcan
            $tags['TICKET_DEPARTMENT'] = ($departmentHandler->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
            }

            if ($user_email_tpl) {
                //$sendTo = $this->getEmail($ticket->getVar('uid'));
                $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                $success = $this->sendEvents($user_email_tpl, $sendTo, $tags);
            }
            unset($tags);
        }
    }

    /**
     * Event: batch_owner
     * Triggered after a batch ticket ownership change
     * @param array $tickets The Ticket objects that were modified
     * @param int   $owner   The XOOPS UID of the new owner
     * @return bool
     */
    public function batch_owner(array $tickets, int $owner): bool
    {
        //notify old owner, if assigned
        //notify new owner
        //notify submitter
        global $xoopsUser;
        $helper = Helper::getInstance();

        $departmentHandler = $this->helper->getHandler('Department');

        $displayName = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITOWNER);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';
        $staff_options = $settings->getVar('staff_options') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'new_owner', $this->module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'new_this_owner', $this->module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $new_owner    = Utility::getUsername($owner, $displayName);
        $submitted_by = $xoopsUser->getVar('uname');
        $uid          = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $tags                       = [];
            $tags['TICKET_ID']          = $ticket->getVar('id');
            $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
            $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
            $tags['TICKET_OWNER']       = $new_owner;
            $tags['SUBMITTED_OWNER']    = $submitted_by;
            $tags['TICKET_STATUS']      = Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_PRIORITY']    = Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_URL']         = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

            $sendTo = [];
            if (0 != $ticket->getVar('ownership')) {                               // If there was an owner
                if ($dept_email_tpl) {      // Send them an email
                    if ($this->isSubscribed($ticket->getVar('ownership'), $dept_email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->getStaffEmail($ticket->getVar('ownership'), $ticket->getVar('department'), $staff_options);
                        $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
                    }
                }
            }
            if ($owner != $uid) {
                if ($dept_email_tpl) { // Send new owner email
                    if ($this->isSubscribed($owner, $dept_email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo  = $this->getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                        $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
                    }
                }
            }
            if ($user_email_tpl) {
                //$sendTo = $this->getEmail($ticket->getVar('uid'));
                $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                $success = $this->sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_status
     * Triggered after a batch ticket status change
     * @param array $tickets   The Ticket objects that were modified
     * @param int   $newstatus The new ticket status
     * @return bool
     */
    public function batch_status(array $tickets, int $newstatus): bool
    {
        //notify staff department of change
        //notify submitter
        global $xoopsUser;
        $departmentHandler = $this->helper->getHandler('Department');

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_EDITSTATUS);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'changed_status', $this->module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'changed_this_status', $this->module, $template_id);
        } else {
            $user_email_tpl = false;
        }
        $sStatus = Utility::getStatus($newstatus);
        $uname   = $xoopsUser->getVar('uname');
        $uid     = $xoopsUser->getVar('uid');

        foreach ($tickets as $ticket) {
            $tags               = [];
            $tags['TICKET_ID']  = $ticket->getVar('id');
            $tags['TICKET_URL'] = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');

            // Added by marcan to get the ticket's subject available in the mail template
            $tags['TICKET_SUBJECT'] = ($ticket->getVar('subject', 'n'));
            // End of addition by marcan

            $tags['TICKET_OLD_STATUS'] = Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_STATUS']     = $sStatus;
            $tags['TICKET_UPDATEDBY']  = $uname;
            $tags['TICKET_DEPARTMENT'] = ($departmentHandler->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
            }
            if ($user_email_tpl) {
                $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                $success = $this->sendEvents($user_email_tpl, $sendTo, $tags);
            }
        }

        return true;
    }

    /**
     * Event: batch_delete_ticket
     * Triggered after a batch ticket deletion
     * @param array $tickets The Ticket objects that were deleted
     * @return bool
     */
    public function batch_delete_ticket(array $tickets): bool
    {
        //notify staff department
        //notify submitter (if ticket is not closed)
        global $xoopsUser, $xoopsModule;

        $uname             = $xoopsUser->getVar('uname');
        $uid               = $xoopsUser->getVar('uid');
        $staffHandler      = $this->helper->getHandler('Staff');
        $isStaff           = $staffHandler->isStaff($uid);
        $departmentHandler = $this->helper->getHandler('Department');

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_DELTICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'removed_ticket', $this->module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'removed_this_ticket', $this->module, $template_id);
        } else {
            $user_email_tpl = false;
        }

        foreach ($tickets as $ticket) {
            $tags                       = [];
            $tags['TICKET_ID']          = $ticket->getVar('id');
            $tags['TICKET_SUBJECT']     = ($ticket->getVar('subject', 'n'));
            $tags['TICKET_DESCRIPTION'] = ($ticket->getVar('description', 'n'));
            $tags['TICKET_PRIORITY']    = Utility::getPriority($ticket->getVar('priority'));
            $tags['TICKET_STATUS']      = Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_POSTED']      = $ticket->posted();
            $tags['TICKET_DELETEDBY']   = $uname;
            $tags['TICKET_DEPARTMENT']  = ($departmentHandler->getNameById($ticket->getVar('department')));

            if ($dept_email_tpl) {
                $sendTo  = $this->getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
            }

            if ($user_email_tpl) {
                $status = $this->statusHandler->get($ticket->getVar('status'));
                if ($isStaff || (!$isStaff && 2 != $status->getVar('state'))) {           // Send to ticket submitter
                    //$sendTo = $this->getEmail($ticket->getVar('uid'));
                    $sendTo  = $this->getSubscribedUsers($ticket->getVar('id'));
                    $success = $this->sendEvents($user_email_tpl, $sendTo, $tags);
                }
            }
        }

        return true;
    }

    /**
     * Event: batch_response
     * Triggered after a batch response addition
     * Note: the $response->getVar('ticketid') field is empty for this function
     * @param array    $tickets  The Ticket objects that were modified
     * @param Response $response The response added to each ticket
     */
    public function batch_response(array $tickets, Response $response)
    {
        global $xoopsUser, $xoopsConfig;
        $helper         = Helper::getInstance();
        $dept_email_tpl = [];

        $displayName              = $helper->getConfig('xhelp_displayName');    // Determines if username or real name is displayed
        $responseText             = ($response->getVar('message', 'n'));
        $uname                    = $xoopsUser->getVar('uname');
        $uid                      = $xoopsUser->getVar('uid');
        $updated                  = \formatTimestamp(\time(), 'm');
        $private                  = $response->getVar('private');
        $departmentMailBoxHandler = $this->helper->getHandler('DepartmentMailBox');
        $mailBoxes                = $departmentMailBoxHandler->getObjects(null, true);
        $departmentHandler        = $this->helper->getHandler('Department');

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_NEWRESPONSE);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';
        $staff_options = $settings->getVar('staff_options') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $dept_email_tpl = $this->getEmailTpl('dept', 'new_response', $this->module, $template_id);
        } else {
            $dept_email_tpl = false;
        }
        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $user_email_tpl = $this->getEmailTpl('ticket', 'new_this_response', $this->module, $template_id);
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
            $tags['TICKET_STATUS']     = Utility::getStatus($ticket->getVar('status'));
            $tags['TICKET_RESPONDER']  = $uname;
            $tags['TICKET_POSTED']     = $updated;
            $tags['TICKET_URL']        = \XHELP_BASE_URL . '/ticket.php?id=' . $ticket->getVar('id');
            $tags['TICKET_DEPARTMENT'] = ($departmentHandler->getNameById($ticket->getVar('department')));

            $owner = $ticket->getVar('ownership');
            if (0 == $owner) {
                $tags['TICKET_OWNERSHIP'] = \_XHELP_NO_OWNER;
            } else {
                $tags['TICKET_OWNERSHIP'] = Utility::getUsername($owner, $displayName);
            }

            if ($ticket->getVar('serverid') > 0) {
                //Ticket was submitted via email
                $mailBox = $mailBoxes[$ticket->getVar('serverid')];
                if (\is_object($mailBox)) {
                    $bFromEmail = true;
                }
            }

            if ($bFromEmail) {
                $from                         = $mailBox->getVar('emailaddress');
                $tags['TICKET_SUPPORT_EMAIL'] = $from;
                $tags['TICKET_SUPPORT_KEY']   = '{' . $ticket->getVar('emailHash') . '}';
            } else {
                $from                         = '';
                $tags['TICKET_SUPPORT_EMAIL'] = $xoopsConfig['adminmail'];
                $tags['TICKET_SUPPORT_KEY']   = '';
            }

            $sendTo = [];
            if ($ticket->getVar('uid') != $uid && 0 === $response->getVar('private')) { // If response from staff member
                if (0 == $private) {
                    if ($user_email_tpl) {
                        $sendTo  = $this->getUserEmail($ticket->getVar('uid'));
                        $success = $this->sendEvents($user_email_tpl, $sendTo, $tags, $from);
                    }
                } elseif ($dept_email_tpl) {
                    if (0 != $ticket->getVar('ownership')) {
                        $sendTo = $this->getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                    } else {
                        $sendTo = $this->getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                    }
                }
            } elseif ($dept_email_tpl) {// If response from submitter
                if (0 != $ticket->getVar('ownership')) {  // If ticket has owner, send to owner
                    if ($this->isSubscribed($owner, $dept_email_tpl['bit_value'])) {    // Check if the owner is subscribed
                        $sendTo = $this->getStaffEmail($owner, $ticket->getVar('department'), $staff_options);
                    }
                } else {                                    // If ticket has no owner, send to department
                    $sendTo = $this->getSubscribedStaff($ticket, $dept_email_tpl['bit_value'], $settings);
                }
                $success = $this->sendEvents($dept_email_tpl, $sendTo, $tags);
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
    public function merge_tickets(int $ticket1, int $ticket2, int $newTicket)
    {
        global $xoopsUser;
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $this->helper->getHandler('Ticket');
        /** @var \XoopsModules\Xhelp\Ticket $ticket */
        $ticket = $ticketHandler->get($newTicket);

        $tags                  = [];
        $tags['TICKET_MERGER'] = $xoopsUser->getVar('uname');
        $tags['TICKET1']       = $ticket1;
        $tags['TICKET2']       = $ticket2;
        $tags['TICKET_URL']    = \XHELP_BASE_URL . '/ticket.php?id=' . $newTicket;

        $settings      = $this->notificationHandler->get(\XHELP_NOTIF_MERGETICKET);
        $staff_setting = $settings->getVar('staff_setting') ?? '';
        $user_setting  = $settings->getVar('user_setting') ?? '';

        if (\XHELP_NOTIF_STAFF_NONE != $staff_setting) {
            $email_tpl = $this->getEmailTpl('dept', 'merge_ticket', $this->module, $template_id);
            if ($email_tpl) {   // Send email to dept members
                $sendTo  = $this->getSubscribedStaff($ticket, $email_tpl['bit_value'], $settings);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }

        if (\XHELP_NOTIF_USER_NO != $user_setting) {
            $email_tpl = $this->getEmailTpl('ticket', 'merge_this_ticket', $this->module, $template_id);
            if ($email_tpl) {    // Send confirm email to submitter
                //$sendTo = $this->getEmail($ticket->getVar('uid'));
                $sendTo  = $this->getSubscribedUsers($newTicket);
                $success = $this->sendEvents($email_tpl, $sendTo, $tags);
            }
        }
    }

    /**
     * Event: new_faq
     * Triggered after FAQ addition
     * @param Ticket $ticket Ticket used as base for FAQ
     * @param Faq    $faq    FAQ that was added
     */
    public function new_faq(Ticket $ticket, Faq $faq)
    {
    }

    /**
     * Only have 1 instance of class used
     * @return Service {@link Service}
     */
    public static function getInstance(): Service
    {
        static $instance;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    public function attachEvents()
    {
        $this->attachEvent('batch_delete_ticket', $this);
        $this->attachEvent('batch_dept', $this);
        $this->attachEvent('batch_owner', $this);
        $this->attachEvent('batch_priority', $this);
        $this->attachEvent('batch_response', $this);
        $this->attachEvent('batch_status', $this);
        $this->attachEvent('close_ticket', $this);
        $this->attachEvent('delete_ticket', $this);
        $this->attachEvent('edit_response', $this);
        $this->attachEvent('edit_ticket', $this);
        $this->attachEvent('merge_tickets', $this);
        $this->attachEvent('new_response', $this);
        $this->attachEvent('new_ticket', $this);
        $this->attachEvent('update_owner', $this);
        $this->attachEvent('update_priority', $this);
        $this->attachEvent('update_status', $this);
    }
}
