<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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

use const _XHELP_MAILBOXTYPE_POP3;
use const XHELP_ROLE_PERM_1;
use const XHELP_ROLE_PERM_2;
use const XHELP_ROLE_PERM_3;
use Xmf\Request;

/**
 * Class Utility
 */
class Utility extends Common\SysUtility
{
    //--------------- Custom module methods -----------------------------
    /**
     * Format a time as 'x' years, 'x' weeks, 'x' days, 'x' hours, 'x' minutes, 'x' seconds
     *
     * @param int $time UNIX timestamp
     * @return string formatted time
     */
    public static function formatTime(int $time): string
    {
        $values = self::getElapsedTime($time);

        foreach ($values as $key => $value) {
            $$key = $value;
        }

        $ret = [];
        if ($years) {
            $ret[] = $years . ' ' . (1 == $years ? \_XHELP_TIME_YEAR : \_XHELP_TIME_YEARS);
        }

        if ($weeks) {
            $ret[] = $weeks . ' ' . (1 == $weeks ? \_XHELP_TIME_WEEK : \_XHELP_TIME_WEEKS);
        }

        if ($days) {
            $ret[] = $days . ' ' . (1 == $days ? \_XHELP_TIME_DAY : \_XHELP_TIME_DAYS);
        }

        if ($hours) {
            $ret[] = $hours . ' ' . (1 == $hours ? \_XHELP_TIME_HOUR : \_XHELP_TIME_HOURS);
        }

        if ($minutes) {
            $ret[] = $minutes . ' ' . (1 == $minutes ? \_XHELP_TIME_MIN : \_XHELP_TIME_MINS);
        }

        $ret[] = $seconds . ' ' . (1 == $seconds ? \_XHELP_TIME_SEC : \_XHELP_TIME_SECS);

        return \implode(', ', $ret);
    }

    /**
     * Changes UNIX timestamp into array of time units of measure
     *
     * @param int $time UNIX timestamp
     * @return array
     */
    public static function getElapsedTime(int $time): array
    {
        //Define the units of measure
        $units = [
            'years'   => 365 * 60 * 60 * 24 /*Value of Unit expressed in seconds*/,
            'weeks'   => 7 * 60 * 60 * 24,
            'days'    => 60 * 60 * 24,
            'hours'   => 60 * 60,
            'minutes' => 60,
            'seconds' => 1,
        ];

        $local_time   = $time;
        $elapsed_time = [];

        //Calculate the total for each unit measure
        foreach ($units as $key => $single_unit) {
            $elapsed_time[$key] = \floor($local_time / $single_unit);
            $local_time         -= ($elapsed_time[$key] * $single_unit);
        }

        return $elapsed_time;
    }

    /**
     * Generate xhelp URL
     *
     * @param string $page
     * @param array  $vars
     * @param bool   $encodeAmp
     * @return string
     */
    public static function createURI(string $page, array $vars = [], bool $encodeAmp = true): string
    {
        $joinStr = '';

        $amp = ($encodeAmp ? '&amp;' : '&');

        if (!\count($vars)) {
            return $page;
        }
        $qs = '';
        foreach ($vars as $key => $value) {
            $qs      .= $joinStr . $key . '=' . $value;
            $joinStr = $amp;
        }

        return $page . '?' . $qs;
    }

    /**
     * Changes a ticket priority (int) into its string equivalent
     *
     * @param int $priority
     * @return string
     */
    public static function getPriority(int $priority)
    {
        $priorities = [
            1 => \_XHELP_TEXT_PRIORITY1,
            2 => \_XHELP_TEXT_PRIORITY2,
            3 => \_XHELP_TEXT_PRIORITY3,
            4 => \_XHELP_TEXT_PRIORITY4,
            5 => \_XHELP_TEXT_PRIORITY5,
        ];

        $priority = $priority;

        return ($priorities[$priority] ?? $priority);
    }

    /**
     * Gets a tickets state (unresolved/resolved)
     *
     * @param int $state
     * @return string
     */
    public static function getState(int $state)
    {
        $state       = $state;
        $stateValues = [
            1 => \_XHELP_STATE1,
            2 => \_XHELP_STATE2,
        ];

        return ($stateValues[$state] ?? $state);
    }

    /**
     * Changes a ticket status (int) into its string equivalent
     * Do not use this function in loops
     *
     * @param string|int $status
     * @return string Status Description
     */
    public static function getStatus($status)
    {
        static $statuses;

        $status = (int)$status;
        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = Helper::getInstance()
            ->getHandler('Status');

        //Get Statuses from database if first request
        if (!$statuses) {
            $statuses = $statusHandler->getObjects(null, true);
        }

        return (isset($statuses[$status]) ? $statuses[$status]->getVar('description') : $status);
    }

    /**
     * Changes a response rating (int) into its string equivalent
     *
     * @param int $rating
     * @return string
     */
    public static function getRating(int $rating)
    {
        $ratings = [
            0 => \_XHELP_RATING0,
            1 => \_XHELP_RATING1,
            2 => \_XHELP_RATING2,
            3 => \_XHELP_RATING3,
            4 => \_XHELP_RATING4,
            5 => \_XHELP_RATING5,
        ];
        $rating  = $rating;

        return ($ratings[$rating] ?? $rating);
    }

    /**
     * @param int $class
     * @return int|mixed
     */
    public static function getEventClass(int $class)
    {
        $classes = [
            0 => \_XHELP_MAIL_CLASS0,
            1 => \_XHELP_MAIL_CLASS1,
            2 => \_XHELP_MAIL_CLASS2,
            3 => \_XHELP_MAIL_CLASS3,
        ];
        $class   = $class;

        return ($classes[$class] ?? $class);
    }

    /**
     * Move specified tickets into department
     *
     * @param array $tickets array of ticket ids (int)
     * @param int   $dept    department ID
     * @return bool  True on success, False on error
     */
    public static function setDept(array $tickets, int $dept): bool
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->updateAll('department', $dept, $criteria);
    }

    /**
     * Set specified tickets to a priority
     *
     * @param array $tickets  array of ticket ids (int)
     * @param int   $priority priority value
     * @return bool  True on success, False on error
     */
    public static function setPriority(array $tickets, int $priority): bool
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->updateAll('priority', $priority, $criteria);
    }

    /**
     * Set specified tickets to a status
     *
     * @param array $tickets array of ticket ids (int)
     * @param int   $status  status value
     * @return bool  True on success, False on error
     */
    public static function setStatus(array $tickets, int $status): bool
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->updateAll('status', $status, $criteria);
    }

    /**
     * Assign specified tickets to a staff member
     *
     * Assumes that owner is a member of all departments in specified tickets
     *
     * @param array $tickets array of ticket ids (int)
     * @param int   $owner   uid of new owner
     * @return bool  True on success, False on error
     */
    public static function setOwner(array $tickets, int $owner): bool
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->updateAll('ownership', $owner, $criteria);
    }

    /**
     * Add the response to each ticket
     *
     *
     * @param array  $tickets   array of ticket ids (int)
     * @param string $sresponse
     * @param int    $timespent Number of minutes spent on ticket
     * @param bool   $private   Should this be a private message?
     * @return false|\XoopsModules\Xhelp\Response
     *
     * @internal param string $response response text to add
     */
    public static function addResponse(array $tickets, string $sresponse, int $timespent = 0, bool $private = false)
    {
        global $xoopsUser;
        /** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
        $responseHandler = Helper::getInstance()
            ->getHandler('Response');
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $updateTime    = \time();
        $uid           = $xoopsUser->getVar('uid');
        $ret           = true;
        $userIP        = \getenv('REMOTE_ADDR');
        $ticket_count  = \count($tickets);
        $i             = 1;
        foreach ($tickets as $ticketid) {
            /** @var \XoopsModules\Xhelp\Response $response */
            $response = $responseHandler->create();
            $response->setVar('uid', $uid);
            $response->setVar('ticketid', $ticketid);
            $response->setVar('message', $sresponse);
            $response->setVar('timeSpent', $timespent);
            $response->setVar('updateTime', $updateTime);
            $response->setVar('userIP', $userIP);
            $response->setVar('private', $private);
            $ret = $ret && $responseHandler->insert($response);
            if ($ticket_count != $i) {
                unset($response);
            }
            ++$i;
        }
        if ($ret) {
            $criteria = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');
            $ret      = $ticketHandler->incrementAll('totalTimeSpent', $timespent, $criteria);
            $ret      = $ticketHandler->updateAll('lastUpdated', $updateTime, $criteria);
            $response->setVar('ticketid', 0);
            $response->setVar('id', 0);

            return $response;
        }

        return false;
    }

    /**
     * Remove the specified tickets
     *
     * @param array $tickets array of ticket ids (int)
     * @return bool  True on success, False on error
     */
    public static function deleteTickets(array $tickets): bool
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->deleteAll($criteria);
    }

    /**
     * Retrieves an array of tickets in one query
     *
     * @param array $tickets array of ticket ids (int)
     * @return array Array of ticket objects
     */
    public static function getTickets(array $tickets): array
    {
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = Helper::getInstance()
            ->getHandler('Ticket');
        $criteria      = new \Criteria('t.id', '(' . \implode(',', $tickets) . ')', 'IN');

        return $ticketHandler->getObjects($criteria);
    }

    /**
     * Check if all supplied rules pass, and return any errors
     *
     * @param array|Validation\Validator $rules  array of {@link Validator} classes
     * @param array|null                 $errors array of errors found (if any)
     * @return bool  True if all rules pass, false if any fail
     */
    public static function checkRules($rules, ?array &$errors): bool
    {
        $ret = true;
        if (\is_array($rules)) {
            foreach ($rules as $rule) {
                $ret    = $ret && self::checkRules($rule, $error);
                $errors = \array_merge($errors, $error);
            }
        } else {
            if ($rules->isValid()) {
                $ret    = true;
                $errors = [];
            } else {
                $ret    = false;
                $errors = $rules->getErrors();
            }
        }

        return $ret;
    }

    /**
     * Output the specified variable (for debugging)
     *
     * @param mixed $var Variable to output
     */
    public static function debug($var): void
    {
        echo '<pre>';
        \print_r($var);
        echo '</pre>';
    }

    /**
     * Detemines if a table exists in the current db
     *
     * @param string $table the table name (without XOOPS prefix)
     * @return bool   True if table exists, false if not
     */
    public static function tableExists(string $table): bool
    {
        $bRetVal = false;
        //Verifies that a MySQL table exists
        $xoopsDB  = \XoopsDatabaseFactory::getDatabaseConnection();
        $realname = $xoopsDB->prefix($table);
        $dbname   = XOOPS_DB_NAME;
        $sql      = "SHOW TABLES FROM $dbname";
        $ret      = $xoopsDB->queryF($sql);
        while ([$m_table] = $xoopsDB->fetchRow($ret)) {
            if ($m_table == $realname) {
                $bRetVal = true;
                break;
            }
        }
        $xoopsDB->freeRecordSet($ret);

        return $bRetVal;
    }

    /**
     * Gets a value from a key in the xhelp_meta table
     *
     * @param string $key
     * @return string|bool|null
     */
    public static function getMeta(string $key)
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
        $sql     = \sprintf('SELECT metavalue FROM `%s` WHERE metakey=%s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key));
        $result  = $xoopsDB->query($sql);
        if (!$result || $xoopsDB->getRowsNum($result) < 1) {
            $value = false;
        } else {
            [$value] = $xoopsDB->fetchRow($result);
        }

        return $value;
    }

    /**
     * Sets a value for a key in the xhelp_meta table
     *
     * @param string $key
     * @param string $value
     * @return bool   TRUE if success, FALSE if failure
     */
    public static function setMeta(string $key, string $value): bool
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
        $ret     = self::getMeta($key);
        if (false !== $ret) {
            $sql = \sprintf('UPDATE `%s` SET metavalue = %s WHERE metakey = %s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($value), $xoopsDB->quoteString($key));
        } else {
            $sql = \sprintf('INSERT INTO `%s` (metakey, metavalue) VALUES (%s, %s)', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key), $xoopsDB->quoteString($value));
        }
        $ret = $xoopsDB->queryF($sql);
        if (!$ret) {
            return false;
        }

        return true;
    }

    /**
     * Deletes a record from the xhelp_meta table
     *
     * @param string $key
     * @return bool   TRUE if success, FALSE if failure
     */
    public static function deleteMeta(string $key): bool
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
        $sql     = \sprintf('DELETE FROM `%s` WHERE metakey=%s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key));
        $ret     = $xoopsDB->query($sql);
        if (!$ret) {
            return false;
        }

        return $ret;
    }

    /**
     * Does the supplied email belong to an existing xoops user
     *
     * @param string $email
     * @return \XoopsUser|bool \xoopsUser object if success, FALSE if failure
     * object if success, FALSE if failure
     */
    public static function emailIsXoopsUser(string $email)
    {
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');
        $criteria      = new \Criteria('email', $email);
        $criteria->setLimit(1);

        $users = $memberHandler->getUsers($criteria);
        if (\count($users) > 0) {
            return $users[0];
        }

        return false;
    }

    /**
     * Detemines if a field exists in the current db
     *
     * @param string $table the table name (without XOOPS prefix)
     * @param string $field the field name
     * @return mixed  false if field does not exist, array containing field info if does
     */
    //    public static function fieldExists($table, $field)
    //    {
    //        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    //        $tblname = $xoopsDB->prefix($table);
    //        $ret = $xoopsDB->query("DESCRIBE $tblname");
    //
    //        if (!$ret) {
    //            return false;
    //        }
    //
    //        while (false !== ($row = $xoopsDB->fetchRow($ret))) {
    //            if (0 == \strcasecmp($row['Field'], $field)) {
    //                return $row;
    //            }
    //        }
    //
    //        return false;
    //    }

    /**
     * Creates a xoops account from an email address and password
     *
     * @param string $email
     * @param string $name
     * @param string $password
     * @param int    $level
     * @return \XoopsUser|bool \xoopsUser object if success, FALSE if failure
     */
    public static function getXoopsAccountFromEmail(string $email, string $name, string &$password, int $level)
    {
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');

        $unamecount = 10;
        if ('' === $password) {
            $password = mb_substr(\md5(\uniqid((string)\mt_rand(), true)), 0, 6);
        }

        $usernames = self::generateUserNames($email, $name, $unamecount);
        $newuser   = false;
        $i         = 0;
        while (!$newuser) {
            $criteria = new \Criteria('uname', $usernames[$i]);
            $count    = $memberHandler->getUserCount($criteria);
            if (0 == $count) {
                $newuser = true;
            } else {
                //Move to next username
                ++$i;
                if ($i == $unamecount) {
                    //Get next batch of usernames to try, reset counter
                    $usernames = self::generateUserNames($email->getEmail(), $email->getName(), $unamecount);
                    $i         = 0;
                }
            }
        }

        $xuser = $memberHandler->createUser();
        $xuser->setVar('uname', $usernames[$i]);
        $xuser->setVar('loginname', $usernames[$i]);
        $xuser->setVar('user_avatar', 'blank.gif');
        $xuser->setVar('user_regdate', \time());
        $xuser->setVar('timezone_offset', 0);
        $xuser->setVar('actkey', mb_substr(\md5(\uniqid((string)\mt_rand(), true)), 0, 8));
        $xuser->setVar('email', $email);
        $xuser->setVar('name', $name);
        $xuser->setVar('pass', \md5($password));
        $xuser->setVar('notify_method', 2);
        $xuser->setVar('level', $level);

        if ($memberHandler->insertUser($xuser)) {
            //Add the user to Registered Users group
            $memberHandler->addUserToGroup(XOOPS_GROUP_USERS, $xuser->getVar('uid'));
        } else {
            return false;
        }

        return $xuser;
    }

    /**
     * Generates an array of usernames
     *
     * @param string $email email of user
     * @param string $name  name of user
     * @param int    $count number of names to generate
     * @return array
     */
    public static function generateUserNames(string $email, string $name, int $count = 20): array
    {
        $names  = [];
        $userid = \explode('@', $email);

        $basename    = '';
        $hasbasename = false;
        $emailname   = $userid[0];

        $names[] = $emailname;

        if ('' !== $name) {
            $name = \explode(' ', \trim($name));
            if (\count($name) > 1) {
                $basename = \mb_strtolower(mb_substr($name[0], 0, 1) . $name[\count($name) - 1]);
            } else {
                $basename = \mb_strtolower($name[0]);
            }
            $basename = \xoops_substr($basename, 0, 60, '');
            //Prevent Duplication of Email Username and Name
            if (!\in_array($basename, $names)) {
                $names[]     = $basename;
                $hasbasename = true;
            }
        }

        $i          = \count($names);
        $onbasename = 1;
        while ($i < $count) {
            $num = self::generateRandNumber();
            if ($onbasename < 0 && $hasbasename) {
                $names[] = \xoops_substr($basename, 0, 58, '') . $num;
            } else {
                $names[] = \xoops_substr($emailname, 0, 58, '') . $num;
            }
            $i          = \count($names);
            $onbasename = ~$onbasename;
            $num        = '';
        }

        return $names;
    }

    /**
     * Gives the random number generator a seed to start from
     *
     * @return void
     *
     * @access public
     */
    public static function initRand(): void
    {
        static $randCalled = false;
        if (!$randCalled) {
            // mt_srand((double)microtime() * 1000000);
            $randCalled = true;
        }
    }

    /**
     * Creates a random number with a specified number of $digits
     *
     * @param int $digits number of digits
     * @return string random number
     */
    public static function generateRandNumber(int $digits = 2): string
    {
        self::initRand();
        $tmp = [];

        for ($i = 0; $i < $digits; $i++) {
            $tmp[$i] = (\mt_rand() % 9);
        }

        return \implode('', $tmp);
    }

    /**
     * Converts int $type into its string equivalent
     *
     * @param int $type
     * @return string
     */
    public static function getMBoxType(int $type): string
    {
        $mboxTypes = [
            \_XHELP_MAILBOXTYPE_POP3 => 'POP3',
            \_XHELP_MAILBOXTYPE_IMAP => 'IMAP',
        ];

        return ($mboxTypes[$type] ?? 'NA');
    }

    /**
     * Retrieve list of all staff members
     *
     * @param int $displayName
     * @return array Staff objects
     * objects
     */
    public static function getStaff(int $displayName): array
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

        $sql = \sprintf('SELECT u.uid, u.uname, u.name FROM `%s` u INNER JOIN %s s ON u.uid = s.uid ORDER BY u.uname', $xoopsDB->prefix('users'), $xoopsDB->prefix('xhelp_staff'));
        $ret = $xoopsDB->query($sql);

        $staff[-1] = \_XHELP_TEXT_SELECT_ALL;
        $staff[0]  = \_XHELP_NO_OWNER;
        while (false !== ($member = $xoopsDB->fetchArray($ret))) {
            $staff[$member['uid']] = self::getDisplayName($displayName, $member['name'], $member['uname']);
        }

        return $staff;
    }

    /**
     * Create default staff roles for a new installation
     *
     * @return bool true if success, FALSE if failure
     */
    public static function createRoles(): bool
    {
        if (!\defined('_XHELP_ROLE_NAME1')) {
            self::includeLang('main', 'english');
        }

        $defaultRolePermissions = [
            1 => ['name' => \_XHELP_ROLE_NAME1, 'desc' => \_XHELP_ROLE_DSC1, 'value' => XHELP_ROLE_PERM_1],
            2 => ['name' => \_XHELP_ROLE_NAME2, 'desc' => \_XHELP_ROLE_DSC2, 'value' => XHELP_ROLE_PERM_2],
            3 => ['name' => \_XHELP_ROLE_NAME3, 'desc' => \_XHELP_ROLE_DSC3, 'value' => XHELP_ROLE_PERM_3],
        ];

        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = Helper::getInstance()
            ->getHandler('Role');

        foreach ($defaultRolePermissions as $key => $aRole) {
            /** @var \XoopsModules\Xhelp\Role $role */
            $role = $roleHandler->create();
            $role->setVar('id', $key);
            $role->setVar('name', $aRole['name']);
            $role->setVar('description', $aRole['desc']);
            $role->setVar('tasks', $aRole['value']);
            if (!$roleHandler->insert($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create ticket statuses for a new installation
     *
     * @return bool true if success, FALSE if failure
     */
    public static function createStatuses(): bool
    {
        if (!\defined('_XHELP_STATUS0')) {
            self::includeLang('main', 'english');
        }

        $statuses = [
            1 => ['description' => \_XHELP_STATUS0, 'state' => \XHELP_STATE_UNRESOLVED],
            2 => ['description' => \_XHELP_STATUS1, 'state' => \XHELP_STATE_UNRESOLVED],
            3 => ['description' => \_XHELP_STATUS2, 'state' => \XHELP_STATE_RESOLVED],
        ];

        /** @var \XoopsModules\Xhelp\StatusHandler $statusHandler */
        $statusHandler = Helper::getInstance()
            ->getHandler('Status');
        foreach ($statuses as $id => $status) {
            /** @var \XoopsModules\Xhelp\Status $newStatus */
            $newStatus = $statusHandler->create();
            $newStatus->setVar('id', $id);
            $newStatus->setVar('description', $status['description']);
            $newStatus->setVar('state', $status['state']);

            if (!$statusHandler->insert($newStatus)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert Bytes to a human readable size (GB, MB, KB, etc)
     *
     * @param int $bytes
     * @return string Human readable size
     */
    public static function prettyBytes(int $bytes): string
    {
        $bytes = $bytes;

        if ($bytes >= 1099511627776) {
            $return = \number_format($bytes / 1024 / 1024 / 1024 / 1024, 2);
            $suffix = \_XHELP_SIZE_TB;
        } elseif ($bytes >= 1073741824) {
            $return = \number_format($bytes / 1024 / 1024 / 1024, 2);
            $suffix = \_XHELP_SIZE_GB;
        } elseif ($bytes >= 1048576) {
            $return = \number_format($bytes / 1024 / 1024, 2);
            $suffix = \_XHELP_SIZE_MB;
        } elseif ($bytes >= 1024) {
            $return = \number_format($bytes / 1024, 2);
            $suffix = \_XHELP_SIZE_KB;
        } else {
            $return = $bytes;
            $suffix = \_XHELP_SIZE_BYTES;
        }

        return $return . ' ' . $suffix;
    }

    /**
     * Add a new database field to an existing table
     * MySQL Only!
     *
     * @param string $table
     * @param string $fieldname
     * @param string $fieldtype
     * @param int    $size
     * @param null   $attr
     * @return resource SQL query resource
     */
    public static function addDBField(string $table, string $fieldname, string $fieldtype = 'VARCHAR', int $size = 0, $attr = null)
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

        $column_def = $fieldname;
        if ($size) {
            $column_def .= \sprintf(' %s(%s)', $fieldtype, $size);
        } else {
            $column_def .= " $fieldtype";
        }
        if (\is_array($attr)) {
            if (isset($attr['nullable']) && true === $attr['nullable']) {
                $column_def .= ' NULL';
            } else {
                $column_def .= ' NOT NULL';
            }

            if (isset($attr['default'])) {
                $column_def .= ' DEFAULT ' . $xoopsDB->quoteString($attr['default']);
            }

            if (isset($attr['increment'])) {
                $column_def .= ' AUTO_INCREMENT';
            }

            if (isset($attr['key'])) {
                $column_def .= ' KEY';
            }

            if (isset($attr['comment'])) {
                $column_def .= 'COMMENT ' . $xoopsDB->quoteString($attr['comment']);
            }
        }

        $sql = \sprintf('ALTER TABLE %s ADD COLUMN %s', $xoopsDB->prefix($table), $column_def);
        $ret = $xoopsDB->query($sql);

        return $ret;
    }

    /**
     * Rename an existing database field
     * MySQL Only!
     *
     * @param string $table
     * @param string $oldcol
     * @param string $newcol
     * @param string $fieldtype
     * @param int    $size
     * @return resource SQL query resource
     */
    public static function renameDBField(string $table, string $oldcol, string $newcol, string $fieldtype = 'VARCHAR', int $size = 0)
    {
        $xoopsDB    = \XoopsDatabaseFactory::getDatabaseConnection();
        $column_def = $newcol;
        $column_def .= ($size ? \sprintf(' %s(%s)', $fieldtype, $size) : " $fieldtype");
        $sql        = \sprintf('ALTER TABLE %s CHANGE %s %s', $xoopsDB->prefix($table), $oldcol, $column_def);
        $ret        = $xoopsDB->query($sql);

        return $ret;
    }

    /**
     * Remove an existing database field
     * MySQL Only!
     *
     * @param string $table
     * @param string $column
     * @return resource SQL query resource
     */
    public static function removeDBField(string $table, string $column)
    {
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
        $sql     = \sprintf('ALTER TABLE %s DROP COLUMN `%s`', $xoopsDB->prefix($table), $column);
        $ret     = $xoopsDB->query($sql);

        return $ret;
    }

    /**
     * Mark all staff accounts as being updated
     *
     * @return bool True on success, False on Error
     */
    public static function resetStaffUpdatedTime(): bool
    {
        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = Helper::getInstance()
            ->getHandler('Staff');

        return $staffHandler->updateAll('permTimestamp', \time());
    }

    /**
     * Retrieve the XoopsModule object representing this application
     *
     * @return \XoopsModule object representing this application
     */
    public static function getModule(): \XoopsModule
    {
        global $xoopsModule;
        static $_module;

        if (null !== $_module) {
            return $_module;
        }

        if (null !== $xoopsModule && \is_object($xoopsModule) && \XHELP_DIR_NAME == $xoopsModule->getVar('dirname')) {
            $_module = &$xoopsModule;
        } else {
            /** @var \XoopsModuleHandler $moduleHandler */
            $moduleHandler = \xoops_getHandler('module');
            $_module       = $moduleHandler->getByDirname('xhelp');
        }

        return $_module;
    }

    /**
     * Retrieve this modules configuration variables
     *
     * @return array Key = config variable name, Value = current value
     */
    public static function getModuleConfig(): array
    {
        static $_config;

        if (null === $_config) {
            /** @var \XoopsConfigHandler $configHandler */
            $configHandler = \xoops_getHandler('config');
            $_module       = self::getModule();

            $_config = &$configHandler->getConfigsByCat(0, $_module->getVar('mid'));
        }

        return $_config;
    }

    //    /**
    //     * Wrapper for the xoops_getModuleHandler function
    //     *
    //     * @param string $handler Name of the handler to return
    //     * @return \XoopsObjectHandler The object handler requested
    //     */
    //    public static function getHandler($handler)
    //    {
    //        //        $handler = xoops_getModuleHandler($handler, XHELP_DIR_NAME);
    //        require_once \dirname(__DIR__) . '/preloads/autoloader.php';
    //        $class      = '\\XoopsModules\\Xhelp\\' . $handler . 'Handler';
    //        $newHandler = new $class($GLOBALS['xoopsDB']);
    //
    //        return $newHandler;
    //    }

    /**
     * Retrieve all saved searches for the specified user(s)
     *
     * @param mixed $users Either an integer (UID) or an array of UIDs
     * @return bool|array SavedSearch objects
     */
    public static function getSavedSearches($users)
    {
        /** @var \XoopsModules\Xhelp\SavedSearchHandler $savedSearchHandler */
        $savedSearchHandler = Helper::getInstance()
            ->getHandler('SavedSearch');

        if (\is_array($users)) {
            $criteria = new \Criteria('uid', '(' . \implode(',', $users) . ')', 'IN');
        } else {
            $criteria = new \Criteria('uid', (int)$users);
        }

        $savedSearches = $savedSearchHandler->getObjects($criteria);

        $ret = [];
        foreach ($savedSearches as $obj) {
            $ret[$obj->getVar('id')] = [
                'id'            => $obj->getVar('id'),
                'uid'           => $obj->getVar('uid'),
                'name'          => $obj->getVar('name'),
                'search'        => \unserialize($obj->getVar('search')),
                'pagenav_vars'  => $obj->getVar('pagenav_vars'),
                'hasCustFields' => $obj->getVar('hasCustFields'),
            ];
        }

        return (\count($ret) > 0 ? $ret : false);
    }

    /**
     * Set default notification settings for all xhelp events
     *
     * @return bool True on success, False on failure
     */
    public static function createNotifications(): bool
    {
        /** @var \XoopsModules\Xhelp\RoleHandler $roleHandler */
        $roleHandler = Helper::getInstance()
            ->getHandler('Role');
        /** @var \XoopsModules\Xhelp\NotificationHandler $notificationHandler */
        $notificationHandler = Helper::getInstance()
            ->getHandler('Notification');

        // Get list of all roles
        $roles = $roleHandler->getObjects();

        $allRoles = [];
        foreach ($roles as $role) {
            $allRoles[$role->getVar('id')] = $role->getVar('id');
        }

        $notifications = [
            ['id' => 1, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 2, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 3, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 4, 'staff' => \XHELP_NOTIF_STAFF_OWNER, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 5, 'staff' => \XHELP_NOTIF_STAFF_OWNER, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 6, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 7, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 8, 'staff' => \XHELP_NOTIF_STAFF_OWNER, 'user' => \XHELP_NOTIF_USER_NO],
            ['id' => 9, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
            ['id' => 10, 'staff' => \XHELP_NOTIF_STAFF_DEPT, 'user' => \XHELP_NOTIF_USER_YES],
        ];

        foreach ($notifications as $notif) {
            /** @var \XoopsModules\Xhelp\Notification $template */
            $template = $notificationHandler->create();
            $template->setVar('notif_id', $notif['id']);
            $template->setVar('staff_setting', $notif['staff']);
            $template->setVar('user_setting', $notif['user']);
            //Set the notification for all staff roles (if necessary)
            if (\XHELP_NOTIF_STAFF_DEPT == $notif['staff']) {
                $template->setVar('staff_options', $allRoles);
            } else {
                $template->setVar('staff_options', []);
            }
            $notificationHandler->insert($template, true);
        }

        return true;
    }

    /**
     * Get the XOOPS username or realname for the specified users
     *
     * @param \CriteriaElement|\CriteriaCompo|null $criteria    Which users to retrieve
     * @param int                                  $displayName XHELP_DISPLAYNAME_UNAME for username XHELP_DISPLAYNAME_REALNAME for realname
     * @return array True on success, False on failure
     */
    public static function getUsers(\CriteriaElement $criteria = null, int $displayName = \XHELP_DISPLAYNAME_UNAME): array
    {
        /** @var \XoopsUserHandler $userHandler */
        $userHandler = \xoops_getHandler('user');
        $users       = $userHandler->getObjects($criteria, true);
        $ret         = [];
        foreach ($users as $i => $user) {
            $ret[$i] = self::getDisplayName($displayName, $user->getVar('name'), $user->getVar('uname'));
        }

        return $ret;
    }

    /**
     * Retrieve a user's name or username depending on value of xhelp_displayName preference
     *
     * @param mixed $xUser       {@link $xoopsUser object) or int {userid}
     * @param int   $displayName {xhelp_displayName preference value}
     *
     * @return string username or real name
     */
    public static function getUsername($xUser, int $displayName = \XHELP_DISPLAYNAME_UNAME): string
    {
        global $xoopsUser, $xoopsConfig;
        $user = false;
        /** @var \XoopsMemberHandler $memberHandler */
        $memberHandler = \xoops_getHandler('member');

        if (\is_numeric($xUser)) {
            if ($xUser != (int)$xoopsUser->getVar('uid')) {
                if (0 == $xUser) {
                    return $xoopsConfig['anonymous'];
                }
                $user = $memberHandler->getUser($xUser);
            } else {
                $user = $xoopsUser;
            }
        } elseif (\is_object($xUser)) {
            $user = $xUser;
        } else {
            return $xoopsConfig['anonymous'];
        }

        $ret = self::getDisplayName($displayName, $user->getVar('name'), $user->getVar('uname'));

        return $ret;
    }

    /**
     * Retrieve the Displayname for the user
     *
     * @param int    $displayName {xhelp_displayName preference value}
     * @param string $name        {user's real name}
     * @param string $uname       {user's username}
     * @return string {username or real name}
     */
    public static function getDisplayName(int $displayName, string $name = '', string $uname = ''): string
    {
        return ((\XHELP_DISPLAYNAME_REALNAME == $displayName && '' != $name) ? $name : $uname);
    }

    /**
     * Retrieve the site's active language
     *
     * @return string Name of language
     */
    public static function getSiteLanguage(): string
    {
        global $xoopsConfig;
        if (null !== $xoopsConfig && isset($xoopsConfig['language'])) {
            $language = $xoopsConfig['language'];
        } else {
            /** @var \XoopsConfigHandler $configHandler */
            $configHandler = \xoops_getHandler('config');
            $xoopsConfig   = $configHandler->getConfigsByCat(\XOOPS_CONF);
            $language      = $xoopsConfig['language'];
        }

        return $language;
    }

    /**
     * Include the specified language translation
     *
     * @param string $filename file to include
     * @param null   $language translation to use
     */
    public static function includeLang(string $filename, $language = null): void
    {
        $langFiles = ['admin', 'blocks', 'main', 'modinfo', 'noise_words'];

        if (!\in_array($filename, $langFiles)) {
            \trigger_error('Invalid language file inclusion attempt', \E_USER_ERROR);
        }

        if (null === $language) {
            $language = self::getSiteLanguage();
        }

        \xoops_loadLanguage($filename, 'xhelp');
    }

    /**
     * @param string $reportName
     */
    public static function includeReportLangFile(string $reportName): void
    {
        \xoops_loadLanguage($reportName, 'xhelp');
    }

    /**
     * Retrieve the Displayname for the user
     * @return bool {username or real name}
     * @internal param int $displayName {xhelp_displayName preference value}
     * @internal param string $name {user's real name}
     * @internal param string $uname {user's username}
     */
    public static function createDefaultTicketLists(): bool
    {
        /** @var \XoopsModules\Xhelp\SavedSearchHandler $savedSearchHandler */
        $savedSearchHandler = Helper::getInstance()
            ->getHandler('SavedSearch');
        /** @var \XoopsModules\Xhelp\TicketListHandler $ticketListHandler */
        $ticketListHandler = Helper::getInstance()
            ->getHandler('TicketList');
        /** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
        $staffHandler = Helper::getInstance()
            ->getHandler('Staff');

        $ticketLists = [\XHELP_QRY_STAFF_HIGHPRIORITY, \XHELP_QRY_STAFF_NEW, \XHELP_QRY_STAFF_MINE, \XHELP_QRY_STAFF_ALL];
        $i           = 1;
        foreach ($ticketLists as $ticketList) {
            /** @var \XoopsModules\Xhelp\SavedSearch $newSearch */
            $newSearch = $savedSearchHandler->create();
            $criteria  = new \CriteriaCompo();
            switch ($ticketList) {
                case \XHELP_QRY_STAFF_HIGHPRIORITY:
                    $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID, '=', 'j'));
                    $criteria->add(new \Criteria('state', 1, '=', 's'));
                    $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                    $criteria->setSort('t.priority, t.posted');
                    $newSearch->setVar('name', \_XHELP_TEXT_HIGH_PRIORITY);
                    $newSearch->setVar('pagenav_vars', 'limit=50&state=1');
                    break;
                case \XHELP_QRY_STAFF_NEW:
                    $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID, '=', 'j'));
                    $criteria->add(new \Criteria('ownership', 0, '=', 't'));
                    $criteria->add(new \Criteria('state', 1, '=', 's'));
                    $criteria->setSort('t.posted');
                    $criteria->setOrder('DESC');
                    $newSearch->setVar('name', \_XHELP_TEXT_NEW_TICKETS);
                    $newSearch->setVar('pagenav_vars', 'limit=50&state=1');
                    break;
                case \XHELP_QRY_STAFF_MINE:
                    $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID, '=', 'j'));
                    $criteria->add(new \Criteria('ownership', \XHELP_GLOBAL_UID, '=', 't'));
                    $criteria->add(new \Criteria('state', 1, '=', 's'));
                    $criteria->setSort('t.posted');
                    $newSearch->setVar('name', \_XHELP_TEXT_MY_TICKETS);
                    $newSearch->setVar('pagenav_vars', 'limit=50&state=1&ownership=' . \XHELP_GLOBAL_UID);
                    break;
                case \XHELP_QRY_STAFF_ALL:
                    $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID, '=', 'j'));
                    $criteria->add(new \Criteria('state', 1, '=', 's'));
                    $criteria->add(new \Criteria('uid', \XHELP_GLOBAL_UID, '=', 't'));
                    $newSearch->setVar('name', \_XHELP_TEXT_SUBMITTED_TICKETS);
                    $newSearch->setVar('pagenav_vars', 'limit=50&state=1&submittedBy=' . \XHELP_GLOBAL_UID);
                    break;
                default:
                    return false;
            }

            $newSearch->setVar('uid', \XHELP_GLOBAL_UID);
            $newSearch->setVar('search', \serialize($criteria));
            $newSearch->setVar('hasCustFields', 0);
            $ret = $savedSearchHandler->insert($newSearch, true);

            $staff = $staffHandler->getObjects(null, true);
            foreach ($staff as $stf) {
                /** @var \XoopsModules\Xhelp\TicketList $list */
                $list = $ticketListHandler->create();
                $list->setVar('uid', $stf->getVar('uid'));
                $list->setVar('searchid', $newSearch->getVar('id'));
                $list->setVar('weight', $i);
                $ret = $ticketListHandler->insert($list, true);
            }
            ++$i;
        }

        return true;
    }

    /**
     * Generate publisher URL
     *
     * @param string $page
     * @param array  $vars
     * @param bool   $encodeAmp
     * @return string
     *
     * @credit : xHelp module, developped by 3Dev
     */
    public static function makeUri(string $page, array $vars = [], bool $encodeAmp = true): string
    {
        $joinStr = '';

        $amp = ($encodeAmp ? '&amp;' : '&');

        if (!\count($vars)) {
            return $page;
        }

        $qs = '';
        foreach ($vars as $key => $value) {
            $qs      .= $joinStr . $key . '=' . $value;
            $joinStr = $amp;
        }

        return $page . '?' . $qs;
    }

    /**
     * @return EventService
     */
    //    public static function createNewEventService(): EventService
    //    {
    //        static $instance;
    //
    //        if (null === $instance) {
    //            $instance = new EventService();
    //        }
    //
    //        return $instance;
    //    }

    /**
     * @param string $path
     * @param string $image
     * @param string $alt
     * @return string
     */
    public static function iconSourceTag(string $path, string $image, string $alt): string
    {
        $imgSource = "<img src='" . $path . "$image'  alt='" . $alt . "' title='" . $alt . "' align='middle'>";

        return $imgSource;
    }
}
