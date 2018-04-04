<?php

use XoopsModules\Xhelp;

//Sanity Check: Ensure that /include/constants.php is included
if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

/**
 * Format a time as 'x' years, 'x' weeks, 'x' days, 'x' hours, 'x' minutes, 'x' seconds
 *
 * @param $time
 * @return string formatted time
 *
 * @access public
 */
function formatTime($time)
{
    $values = xhelpGetElapsedTime($time);

    foreach ($values as $key => $value) {
        $$key = $value;
    }

    $ret = [];
    if ($years) {
        $ret[] = $years . ' ' . (1 == $years ? _XHELP_TIME_YEAR : _XHELP_TIME_YEARS);
    }

    if ($weeks) {
        $ret[] = $weeks . ' ' . (1 == $weeks ? _XHELP_TIME_WEEK : _XHELP_TIME_WEEKS);
    }

    if ($days) {
        $ret[] = $days . ' ' . (1 == $days ? _XHELP_TIME_DAY : _XHELP_TIME_DAYS);
    }

    if ($hours) {
        $ret[] = $hours . ' ' . (1 == $hours ? _XHELP_TIME_HOUR : _XHELP_TIME_HOURS);
    }

    if ($minutes) {
        $ret[] = $minutes . ' ' . (1 == $minutes ? _XHELP_TIME_MIN : _XHELP_TIME_MINS);
    }

    $ret[] = $seconds . ' ' . (1 == $seconds ? _XHELP_TIME_SEC : _XHELP_TIME_SECS);

    return implode(', ', $ret);
}

/**
 * Changes UNIX timestamp into array of time units of measure
 *
 * @param  int $time UNIX timestamp
 * @return array $elapsed_time
 *
 * @access public
 */
function xhelpGetElapsedTime($time)
{
    //Define the units of measure
    $units = [
        'years'   => 365 * 60 * 60 * 24 /*Value of Unit expressed in seconds*/,
        'weeks'   => 7 * 60 * 60 * 24,
        'days'    => 60 * 60 * 24,
        'hours'   => 60 * 60,
        'minutes' => 60,
        'seconds' => 1
    ];

    $local_time   = $time;
    $elapsed_time = [];

    //Calculate the total for each unit measure
    foreach ($units as $key => $single_unit) {
        $elapsed_time[$key] = floor($local_time / $single_unit);
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
 * @access public
 */
function xhelpMakeURI($page, $vars = [], $encodeAmp = true)
{
    $joinStr = '';

    $amp = ($encodeAmp ? '&amp;' : '&');

    if (!count($vars)) {
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
 * @param  int $priority
 * @return string $priority
 *
 * @access public
 */
function xhelpGetPriority($priority)
{
    $priorities = [
        1 => _XHELP_TEXT_PRIORITY1,
        2 => _XHELP_TEXT_PRIORITY2,
        3 => _XHELP_TEXT_PRIORITY3,
        4 => _XHELP_TEXT_PRIORITY4,
        5 => _XHELP_TEXT_PRIORITY5
    ];

    $priority = (int)$priority;

    return (isset($priorities[$priority]) ? $priorities[$priority] : $priority);
}

/**
 * Gets a tickets state (unresolved/resolved)
 *
 * @param  int $state
 * @return string $state
 *
 * @access public
 */
function xhelpGetState($state)
{
    $state       = (int)$state;
    $stateValues = [
        1 => _XHELP_STATE1,
        2 => _XHELP_STATE2
    ];

    return (isset($stateValues[$state]) ? $stateValues[$state] : $state);
}

/**
 * Changes a ticket status (int) into its string equivalent
 * Do not use this function in loops
 *
 * @param  int $status
 * @return string Status Description
 *
 * @access public
 */
function xhelpGetStatus($status)
{
    static $statuses;

    $status  = (int)$status;
    $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);

    //Get Statuses from database if first request
    if (!$statuses) {
        $statuses = $hStatus->getObjects(null, true);
    }

    return (isset($statuses[$status]) ? $statuses[$status]->getVar('description') : $status);
}

/**
 * Changes a response rating (int) into its string equivalent
 *
 * @param  int $rating
 * @return string $rating
 *
 * @access public
 */
function xhelpGetRating($rating)
{
    $ratings = [
        0 => _XHELP_RATING0,
        1 => _XHELP_RATING1,
        2 => _XHELP_RATING2,
        3 => _XHELP_RATING3,
        4 => _XHELP_RATING4,
        5 => _XHELP_RATING5
    ];
    $rating  = (int)$rating;

    return (isset($ratings[$rating]) ? $ratings[$rating] : $rating);
}

/**
 * @param $class
 * @return int|mixed
 */
function xhelpGetEventClass($class)
{
    $classes = [
        0 => _XHELP_MAIL_CLASS0,
        1 => _XHELP_MAIL_CLASS1,
        2 => _XHELP_MAIL_CLASS2,
        3 => _XHELP_MAIL_CLASS3
    ];
    $class   = (int)$class;

    return (isset($classes[$class]) ? $classes[$class] : $class);
}

/**
 * Move specified tickets into department
 *
 * @param  array $tickets array of ticket ids (int)
 * @param  int   $dept    department ID
 * @return bool  True on success, False on error
 *
 * @access public
 */
function xhelpSetDept($tickets, $dept)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->updateAll('department', (int)$dept, $crit);
}

/**
 * Set specified tickets to a priority
 *
 * @param  array $tickets  array of ticket ids (int)
 * @param  int   $priority priority value
 * @return bool  True on success, False on error
 *
 * @access public
 */
function xhelpSetPriority($tickets, $priority)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->updateAll('priority', (int)$priority, $crit);
}

/**
 * Set specified tickets to a status
 *
 * @param  array $tickets array of ticket ids (int)
 * @param  int   $status  status value
 * @return bool  True on success, False on error
 *
 * @access public
 */
function xhelpSetStatus($tickets, $status)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->updateAll('status', (int)$status, $crit);
}

/**
 * Assign specified tickets to a staff member
 *
 * Assumes that owner is a member of all departments in specified tickets
 *
 * @param  array $tickets array of ticket ids (int)
 * @param  int   $owner   uid of new owner
 * @return bool  True on success, False on error
 *
 * @access public
 */
function xhelpSetOwner($tickets, $owner)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->updateAll('ownership', (int)$owner, $crit);
}

/**
 * Add the response to each ticket
 *
 *
 * @param  array $tickets   array of ticket ids (int)
 * @param        $sresponse
 * @param  int   $timespent Number of minutes spent on ticket
 * @param  bool  $private   Should this be a private message?
 * @return Xhelp\Responses Response information
 *
 * @internal param string $response response text to add
 * @access   public
 */
function xhelpAddResponse($tickets, $sresponse, $timespent = 0, $private = false)
{
    global $xoopsUser;
    $hResponse    = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
    $hTicket      = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $updateTime   = time();
    $uid          = $xoopsUser->getVar('uid');
    $ret          = true;
    $userIP       = getenv('REMOTE_ADDR');
    $ticket_count = count($tickets);
    $i            = 1;
    foreach ($tickets as $ticketid) {
        $response = $hResponse->create();
        $response->setVar('uid', $uid);
        $response->setVar('ticketid', $ticketid);
        $response->setVar('message', $sresponse);
        $response->setVar('timeSpent', $timespent);
        $response->setVar('updateTime', $updateTime);
        $response->setVar('userIP', $userIP);
        $response->setVar('private', $private);
        $ret = $ret && $hResponse->insert($response);
        if ($ticket_count != $i) {
            unset($response);
        }
        ++$i;
    }
    if ($ret) {
        $crit = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');
        $ret  = $hTicket->incrementAll('totalTimeSpent', $timespent, $crit);
        $ret  = $hTicket->updateAll('lastUpdated', $updateTime, $crit);
        $response->setVar('ticketid', 0);
        $response->setVar('id', 0);

        return $response;
    }

    return false;
}

/**
 * Remove the specified tickets
 *
 * @param  array $tickets array of ticket ids (int)
 * @return bool  True on success, False on error
 *
 * @access public
 */
function xhelpDeleteTickets($tickets)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->deleteAll($crit);
}

/**
 * Retrieves an array of tickets in one query
 *
 * @param  array $tickets array of ticket ids (int)
 * @return array Array of ticket objects
 *
 * @access public
 */
function &xhelpGetTickets(&$tickets)
{
    $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $crit    = new \Criteria('t.id', '(' . implode($tickets, ',') . ')', 'IN');

    return $hTicket->getObjects($crit);
}

/**
 * Check if all supplied rules pass, and return any errors
 *
 * @param  array $rules  array of {@link Validator} classes
 * @param  array $errors array of errors found (if any)
 * @return bool  True if all rules pass, false if any fail
 *
 * @access public
 */
function xhelpCheckRules(&$rules, &$errors)
{
    $ret = true;
    if (is_array($rules)) {
        foreach ($rules as $rule) {
            $ret    = $ret && xhelpCheckRules($rule, $error);
            $errors = array_merge($errors, $error);
        }
    } else {
        if (!$rules->isValid()) {
            $ret    = false;
            $errors = $rules->getErrors();
        } else {
            $ret    = true;
            $errors = [];
        }
    }

    return $ret;
}

/**
 * Output the specified variable (for debugging)
 *
 * @param  mixed $var Variable to output
 * @return void
 *
 * @access public
 */
function xhelpDebug(&$var)
{
    echo '<pre>';
    print_r($var);
    echo '</pre>';
}

/**
 * Detemines if a table exists in the current db
 *
 * @param  string $table the table name (without XOOPS prefix)
 * @return bool   True if table exists, false if not
 *
 * @access public
 */
function xhelpTableExists($table)
{
    $bRetVal = false;
    //Verifies that a MySQL table exists
    $xoopsDB  = \XoopsDatabaseFactory::getDatabaseConnection();
    $realname = $xoopsDB->prefix($table);
    $dbname   = XOOPS_DB_NAME;
    $sql      = "SHOW TABLES FROM $dbname";
    $ret      = $xoopsDB->queryF($sql);
    while (false !== (list($m_table) = $xoopsDB->fetchRow($ret))) {
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
 * @param  string $key
 * @return string $value
 *
 * @access public
 */
function xhelpGetMeta($key)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $sql     = sprintf('SELECT metavalue FROM `%s` WHERE metakey=%s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key));
    $ret     = $xoopsDB->query($sql);
    if (!$ret) {
        $value = false;
    } else {
        list($value) = $xoopsDB->fetchRow($ret);
    }

    return $value;
}

/**
 * Sets a value for a key in the xhelp_meta table
 *
 * @param  string $key
 * @param  string $value
 * @return bool   TRUE if success, FALSE if failure
 *
 * @access public
 */
function xhelpSetMeta($key, $value)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $ret     = xhelpGetMeta($key);
    if (false !== $ret) {
        $sql = sprintf('UPDATE %s SET metavalue = %s WHERE metakey = %s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($value), $xoopsDB->quoteString($key));
    } else {
        $sql = sprintf('INSERT INTO %s (metakey, metavalue) VALUES (%s, %s)', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key), $xoopsDB->quoteString($value));
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
 * @param  string $key
 * @return bool   TRUE if success, FALSE if failure
 *
 * @access public
 */
function xhelpDeleteMeta($key)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $sql     = sprintf('DELETE FROM `%s` WHERE metakey=%s', $xoopsDB->prefix('xhelp_meta'), $xoopsDB->quoteString($key));
    $ret     = $xoopsDB->query($sql);
    if (!$ret) {
        return false;
    } else {
        return $ret;
    }
}

/**
 * Does the supplied email belong to an existing xoops user
 *
 * @param  string $email
 * @return bool <a href='psi_element://xoopsUser'>xoopsUser</a> object if success, FALSE if failure
 * object if success, FALSE if failure
 * @access public
 */
function &xhelpEmailIsXoopsUser($email)
{
    $hXoopsMember = xoops_getHandler('member');
    $crit         = new \Criteria('email', $email);
    $crit->setLimit(1);

    $users =& $hXoopsMember->getUsers($crit);
    if (count($users) > 0) {
        return $users[0];
    } else {
        return false;
    }
}

/**
 * Detemines if a field exists in the current db
 *
 * @param  string $table the table name (without XOOPS prefix)
 * @param  string $field the field name
 * @return mixed  false if field does not exist, array containing field info if does
 *
 * @access public
 */
function xhelpFieldExists($table, $field)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $tblname = $xoopsDB->prefix($table);
    $ret     = $xoopsDB->query("DESCRIBE $tblname");

    if (!$ret) {
        return false;
    }

    while ($row = $xoopsDB->fetchRow($ret)) {
        if (0 == strcasecmp($row['Field'], $field)) {
            return $row;
        }
    }

    return false;
}

/**
 * Creates a xoops account from an email address and password
 *
 * @param  string $email
 * @param         $name
 * @param  string $password
 * @param         $level
 * @return bool <a href='psi_element://xoopsUser'>xoopsUser</a> object if success, FALSE if failure
 * object if success, FALSE if failure
 * @access public
 */
function &xhelpXoopsAccountFromEmail($email, $name, &$password, $level)
{
    $memberHandler = xoops_getHandler('member');

    $unamecount = 10;
    if ('' == $password) {
        $password = substr(md5(uniqid(mt_rand(), 1)), 0, 6);
    }

    $usernames = xhelpGenUserNames($email, $name, $unamecount);
    $newuser   = false;
    $i         = 0;
    while (false === $newuser) {
        $crit  = new \Criteria('uname', $usernames[$i]);
        $count = $memberHandler->getUserCount($crit);
        if (0 == $count) {
            $newuser = true;
        } else {
            //Move to next username
            ++$i;
            if ($i == $unamecount) {
                //Get next batch of usernames to try, reset counter
                $usernames = xhelpGenUserNames($email->getEmail(), $email->getName(), $unamecount);
                $i         = 0;
            }
        }
    }

    $xuser = $memberHandler->createUser();
    $xuser->setVar('uname', $usernames[$i]);
    $xuser->setVar('loginname', $usernames[$i]);
    $xuser->setVar('user_avatar', 'blank.gif');
    $xuser->setVar('user_regdate', time());
    $xuser->setVar('timezone_offset', 0);
    $xuser->setVar('actkey', substr(md5(uniqid(mt_rand(), 1)), 0, 8));
    $xuser->setVar('email', $email);
    $xuser->setVar('name', $name);
    $xuser->setVar('pass', md5($password));
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
 * @param  string $email email of user
 * @param  string $name  name of user
 * @param  int    $count number of names to generate
 * @return array  $names
 *
 * @access public
 */
function xhelpGenUserNames($email, $name, $count = 20)
{
    $names  = [];
    $userid = explode('@', $email);

    $basename    = '';
    $hasbasename = false;
    $emailname   = $userid[0];

    $names[] = $emailname;

    if (strlen($name) > 0) {
        $name = explode(' ', trim($name));
        if (count($name) > 1) {
            $basename = strtolower(substr($name[0], 0, 1) . $name[count($name) - 1]);
        } else {
            $basename = strtolower($name[0]);
        }
        $basename = xoops_substr($basename, 0, 60, '');
        //Prevent Duplication of Email Username and Name
        if (!in_array($basename, $names)) {
            $names[]     = $basename;
            $hasbasename = true;
        }
    }

    $i          = count($names);
    $onbasename = 1;
    while ($i < $count) {
        $num = xhelpGenRandNumber();
        if ($onbasename < 0 && $hasbasename) {
            $names[] = xoops_substr($basename, 0, 58, '') . $num;
        } else {
            $names[] = xoops_substr($emailname, 0, 58, '') . $num;
        }
        $i          = count($names);
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
function xhelpInitRand()
{
    static $randCalled = false;
    if (!$randCalled) {
        mt_srand((double)microtime() * 1000000);
        $randCalled = true;
    }
}

/**
 * Creates a random number with a specified number of $digits
 *
 * @param  int $digits number of digits
 * @return return int random number
 *
 * @access public
 */
function xhelpGenRandNumber($digits = 2)
{
    xhelpInitRand();
    $tmp = [];

    for ($i = 0; $i < $digits; ++$i) {
        $tmp[$i] = (mt_rand() % 9);
    }

    return implode('', $tmp);
}

/**
 * Converts int $type into its string equivalent
 *
 * @param  int $type
 * @return string $type
 *
 * @access public
 */
function xhelpGetMBoxType($type)
{
    $mboxTypes = [
        _XHELP_MAILBOXTYPE_POP3 => 'POP3',
        _XHELP_MAILBOXTYPE_IMAP => 'IMAP'
    ];

    return (isset($mboxTypes[$type]) ? $mboxTypes[$type] : 'NA');
}

/**
 * Retrieve list of all staff members
 *
 * @param $displayName
 * @return array <a href='psi_element://Xhelp\Staff'>Xhelp\Staff</a> objects
 * objects
 * @access public
 */
function &xhelpGetStaff($displayName)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

    $sql = sprintf('SELECT u.uid, u.uname, u.name FROM `%s` u INNER JOIN %s s ON u.uid = s.uid ORDER BY u.uname', $xoopsDB->prefix('users'), $xoopsDB->prefix('xhelp_staff'));
    $ret = $xoopsDB->query($sql);

    $staff[-1] = _XHELP_TEXT_SELECT_ALL;
    $staff[0]  = _XHELP_NO_OWNER;
    while (false !== ($member = $xoopsDB->fetchArray($ret))) {
        $staff[$member['uid']] = xhelpGetDisplayName($displayName, $member['name'], $member['uname']);
    }

    return $staff;
}

/**
 * Create default staff roles for a new installation
 *
 * @return TRUE if success, FALSE if failure
 *
 * @access public
 */
function xhelpCreateRoles()
{
    if (!defined('_XHELP_ROLE_NAME1')) {
        xhelpIncludeLang('main', 'english');
    }

    $defaultRolePermissions = [
        1 => ['name' => _XHELP_ROLE_NAME1, 'desc' => _XHELP_ROLE_DSC1, 'value' => XHELP_ROLE_PERM_1],
        2 => ['name' => _XHELP_ROLE_NAME2, 'desc' => _XHELP_ROLE_DSC2, 'value' => XHELP_ROLE_PERM_2],
        3 => ['name' => _XHELP_ROLE_NAME3, 'desc' => _XHELP_ROLE_DSC3, 'value' => XHELP_ROLE_PERM_3]
    ];

    $hRole = new Xhelp\RoleHandler($GLOBALS['xoopsDB']);

    foreach ($defaultRolePermissions as $key => $aRole) {
        $role = $hRole->create();
        $role->setVar('id', $key);
        $role->setVar('name', $aRole['name']);
        $role->setVar('description', $aRole['desc']);
        $role->setVar('tasks', $aRole['value']);
        if (!$hRole->insert($role)) {
            return false;
        }
    }

    return true;
}

/**
 * Create ticket statuses for a new installation
 *
 * @return TRUE if success, FALSE if failure
 * @access public
 */
function xhelpCreateStatuses()
{
    if (!defined('_XHELP_STATUS0')) {
        xhelpIncludeLang('main', 'english');
    }

    $statuses = [
        1 => ['description' => _XHELP_STATUS0, 'state' => XHELP_STATE_UNRESOLVED],
        2 => ['description' => _XHELP_STATUS1, 'state' => XHELP_STATE_UNRESOLVED],
        3 => ['description' => _XHELP_STATUS2, 'state' => XHELP_STATE_RESOLVED]
    ];

    $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
    foreach ($statuses as $id => $status) {
        $newStatus = $hStatus->create();
        $newStatus->setVar('id', $id);
        $newStatus->setVar('description', $status['description']);
        $newStatus->setVar('state', $status['state']);

        if (!$hStatus->insert($newStatus)) {
            return false;
        }
    }

    return true;
}

/**
 * Convert Bytes to a human readable size (GB, MB, KB, etc)
 *
 * @param $bytes
 * @return string Human readable size
 * @access public
 */
function xhelpPrettyBytes($bytes)
{
    $bytes = (int)$bytes;

    if ($bytes >= 1099511627776) {
        $return = number_format($bytes / 1024 / 1024 / 1024 / 1024, 2);
        $suffix = _XHELP_SIZE_TB;
    } elseif ($bytes >= 1073741824) {
        $return = number_format($bytes / 1024 / 1024 / 1024, 2);
        $suffix = _XHELP_SIZE_GB;
    } elseif ($bytes >= 1048576) {
        $return = number_format($bytes / 1024 / 1024, 2);
        $suffix = _XHELP_SIZE_MB;
    } elseif ($bytes >= 1024) {
        $return = number_format($bytes / 1024, 2);
        $suffix = _XHELP_SIZE_KB;
    } else {
        $return = $bytes;
        $suffix = _XHELP_SIZE_BYTES;
    }

    return $return . ' ' . $suffix;
}

/**
 * Add a new database field to an existing table
 * MySQL Only!
 *
 * @param        $table
 * @param        $fieldname
 * @param string $fieldtype
 * @param int    $size
 * @param null   $attr
 * @return RESOURCE SQL query resource
 * @access public
 */
function xhelpAddDBField($table, $fieldname, $fieldtype = 'VARCHAR', $size = 0, $attr = null)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

    $column_def = $fieldname;
    if ($size) {
        $column_def .= sprintf(' %s(%s)', $fieldtype, $size);
    } else {
        $column_def .= " $fieldtype";
    }
    if (is_array($attr)) {
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

    $sql = sprintf('ALTER TABLE %s ADD COLUMN %s', $xoopsDB->prefix($table), $column_def);
    $ret = $xoopsDB->query($sql);

    return $ret;
}

/**
 * Rename an existing database field
 * MySQL Only!
 *
 * @param        $table
 * @param        $oldcol
 * @param        $newcol
 * @param string $fieldtype
 * @param int    $size
 * @return RESOURCE SQL query resource
 * @access public
 */
function xhelpRenameDBField($table, $oldcol, $newcol, $fieldtype = 'VARCHAR', $size = 0)
{
    $xoopsDB    = \XoopsDatabaseFactory::getDatabaseConnection();
    $column_def = $newcol;
    $column_def .= ($size ? sprintf(' %s(%s)', $fieldtype, $size) : " $fieldtype");
    $sql        = sprintf('ALTER TABLE %s CHANGE %s %s', $xoopsDB->prefix($table), $oldcol, $column_def);
    $ret        = $xoopsDB->query($sql);

    return $ret;
}

/**
 * Remove an existing database field
 * MySQL Only!
 *
 * @param $table
 * @param $column
 * @return RESOURCE SQL query resource
 * @access public
 */
function xhelpRemoveDBField($table, $column)
{
    $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();
    $sql     = sprintf('ALTER TABLE %s DROP COLUMN `%s`', $xoopsDB->prefix($table), $column);
    $ret     = $xoopsDB->query($sql);

    return $ret;
}

/**
 * Mark all staff accounts as being updated
 *
 * @return BOOL True on success, False on Error
 * @access public
 */
function xhelpResetStaffUpdatedTime()
{
    $hStaff = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);

    return $hStaff->updateAll('permTimestamp', time());
}

/**
 * Retrieve the XoopsModule object representing this application
 *
 * @return XoopsModule object representing this application
 * @access public
 */
function &xhelpGetModule()
{
    global $xoopsModule;
    static $_module;

    if (isset($_module)) {
        return $_module;
    }

    if (isset($xoopsModule) && is_object($xoopsModule) && XHELP_DIR_NAME == $xoopsModule->getVar('dirname')) {
        $_module =& $xoopsModule;
    } else {
        $hModule = xoops_getHandler('module');
        $_module =& $hModule->getByDirname('xhelp');
    }

    return $_module;
}

/**
 * Retrieve this modules configuration variables
 *
 * @return ARRAY Key = config variable name, Value = current value
 * @access public
 */

function &xhelpGetModuleConfig()
{
    static $_config;

    if (!isset($_config)) {
        $hModConfig = xoops_getHandler('config');
        $_module    =& xhelpGetModule();

        $_config =& $hModConfig->getConfigsByCat(0, $_module->getVar('mid'));
    }

    return $_config;
}

/**
 * Wrapper for the xoops_getModuleHandler function
 *
 * @param  string $handler Name of the handler to return
 * @return XoopsObjectHandler The object handler requested
 * @access public
 */
function &xhelpGetHandler($handler)
{
    $handler = xoops_getModuleHandler($handler, XHELP_DIR_NAME);

    return $handler;
}

/**
 * Retrieve all saved searches for the specified user(s)
 *
 * @param  mixed $users Either an integer (UID) or an array of UIDs
 * @return array Xhelp\SavedSearch objects
 * @access public
 */
function xhelpGetSavedSearches($users)
{
    $hSavedSearch = new Xhelp\SavedSearchHandler($GLOBALS['xoopsDB']);

    if (is_array($users)) {
        $crit = new \Criteria('uid', '(' . implode($users, ',') . ')', 'IN');
    } else {
        $crit = new \Criteria('uid', (int)$users);
    }

    $savedSearches = $hSavedSearch->getObjects($crit);

    $ret = [];
    foreach ($savedSearches as $obj) {
        $ret[$obj->getVar('id')] = [
            'id'            => $obj->getVar('id'),
            'uid'           => $obj->getVar('uid'),
            'name'          => $obj->getVar('name'),
            'search'        => unserialize($obj->getVar('search')),
            'pagenav_vars'  => $obj->getVar('pagenav_vars'),
            'hasCustFields' => $obj->getVar('hasCustFields')
        ];
    }

    return (count($ret) > 0 ? $ret : false);
}

/**
 * Set default notification settings for all xhelp events
 *
 * @return BOOL True on success, False on failure
 * @access public
 */
function xhelpCreateNotifications()
{
    $hRole         = new Xhelp\RoleHandler($GLOBALS['xoopsDB']);
    $hNotification = new Xhelp\NotificationHandler($GLOBALS['xoopsDB']);

    // Get list of all roles
    $roles = $hRole->getObjects();

    $allRoles = [];
    foreach ($roles as $role) {
        $allRoles[$role->getVar('id')] = $role->getVar('id');
    }

    $notifications = [
        ['id' => 1, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 2, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 3, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 4, 'staff' => XHELP_NOTIF_STAFF_OWNER, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 5, 'staff' => XHELP_NOTIF_STAFF_OWNER, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 6, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 7, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 8, 'staff' => XHELP_NOTIF_STAFF_OWNER, 'user' => XHELP_NOTIF_USER_NO],
        ['id' => 9, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES],
        ['id' => 10, 'staff' => XHELP_NOTIF_STAFF_DEPT, 'user' => XHELP_NOTIF_USER_YES]
    ];

    foreach ($notifications as $notif) {
        $template = $hNotification->create();
        $template->setVar('notif_id', $notif['id']);
        $template->setVar('staff_setting', $notif['staff']);
        $template->setVar('user_setting', $notif['user']);
        //Set the notification for all staff roles (if necessary)
        if (XHELP_NOTIF_STAFF_DEPT == $notif['staff']) {
            $template->setVar('staff_options', $allRoles);
        } else {
            $template->setVar('staff_options', []);
        }
        $hNotification->insert($template, true);
    }

    return true;
}

/**
 * Get the XOOPS username or realname for the specified users
 *
 * @param  CriteriaElement $criteria    Which users to retrieve
 * @param  INTEGER         $displayName XHELP_DISPLAYNAME_UNAME for username XHELP_DISPLAYNAME_REALNAME for realname
 * @return array True on success, False on failure
 * @access public
 */
function &xhelpGetUsers($criteria = null, $displayName = XHELP_DISPLAYNAME_UNAME)
{
    $hUser = xoops_getHandler('user');
    $users = $hUser->getObjects($criteria, true);
    $ret   = [];
    foreach ($users as $i => $user) {
        $ret[$i] = xhelpGetDisplayName($displayName, $user->getVar('name'), $user->getVar('uname'));
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
 *
 * @access public
 */
function xhelpGetUsername($xUser, $displayName = XHELP_DISPLAYNAME_UNAME)
{
    global $xoopsUser, $xoopsConfig;
    $user    = false;
    $hMember = xoops_getHandler('member');

    if (is_numeric($xUser)) {
        if ($xUser <> $xoopsUser->getVar('uid')) {
            if (0 == $xUser) {
                return $xoopsConfig['anonymous'];
            }
            $user =& $hMember->getUser($xUser);
        } else {
            $user = $xoopsUser;
        }
    } elseif (is_object($xUser)) {
        $user = $xUser;
    } else {
        return $xoopsConfig['anonymous'];
    }

    $ret = xhelpGetDisplayName($displayName, $user->getVar('name'), $user->getVar('uname'));

    return $ret;
}

/**
 * Retrieve the Displayname for the user
 *
 * @param  int    $displayName {xhelp_displayName preference value}
 * @param  string $name        {user's real name}
 * @param  string $uname       {user's username}
 * @return string {username or real name}
 * @access public
 */
function xhelpGetDisplayName($displayName, $name = '', $uname = '')
{
    return ((XHELP_DISPLAYNAME_REALNAME == $displayName && '' <> $name) ? $name : $uname);
}

/**
 * Retrieve the site's active language
 *
 * @return string Name of language
 * @access public
 */
function xhelpGetSiteLanguage()
{
    global $xoopsConfig;
    if (isset($xoopsConfig) && isset($xoopsConfig['language'])) {
        $language = $xoopsConfig['language'];
    } else {
        $configHandler = xoops_getHandler('config');
        $xoopsConfig   = $configHandler->getConfigsByCat(XOOPS_CONF);
        $language      = $xoopsConfig['language'];
    }

    return $language;
}

/**
 * Include the specified language translation
 *
 * @param  string $filename file to include
 * @param  string $language translation to use
 * @return null
 * @access public
 */
function xhelpIncludeLang($filename, $language = null)
{
    $langFiles = ['admin', 'blocks', 'main', 'modinfo', 'noise_words'];

    if (!in_array($filename, $langFiles)) {
        trigger_error('Invalid language file inclusion attempt', E_USER_ERROR);
    }

    if (null === $language) {
        $language = xhelpGetSiteLanguage();
    }

    if (file_exists(XHELP_BASE_PATH . "/language/$language/$filename.php")) {
        require_once XHELP_BASE_PATH . "/language/$language/$filename.php";
    } else {
        if (file_exists(XHELP_BASE_PATH . "/language/english/$filename.php")) {
            require_once XHELP_BASE_PATH . "/language/english/$filename.php";
        } else {
            trigger_error("Unable to load language file $filename", E_USER_NOTICE);
        }
    }
}

/**
 * @param $reportName
 */
function xhelpIncludeReportLangFile($reportName)
{
    $language = xhelpGetSiteLanguage();

    if (file_exists(XHELP_BASE_PATH . "/language/$language/reports/$reportName.php")) {
        require_once XHELP_BASE_PATH . "/language/$language/reports/$reportName.php";
    } else {
        if (file_exists(XHELP_BASE_PATH . "/language/english/reports/$reportName.php")) {
            require_once XHELP_BASE_PATH . "/language/english/reports/$reportName.php";
        }
    }
}

/**
 * Retrieve the Displayname for the user
 * @return string {username or real name}
 * @internal param int $displayName {xhelp_displayName preference value}
 * @internal param string $name {user's real name}
 * @internal param string $uname {user's username}
 * @access   public
 */
function xhelpCreateDefaultTicketLists()
{
    $hSavedSearch = new Xhelp\SavedSearchHandler($GLOBALS['xoopsDB']);
    $hTicketList  = new Xhelp\TicketListHandler($GLOBALS['xoopsDB']);
    $hStaff       = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);

    $ticketLists = [XHELP_QRY_STAFF_HIGHPRIORITY, XHELP_QRY_STAFF_NEW, XHELP_QRY_STAFF_MINE, XHELP_QRY_STAFF_ALL];
    $i           = 1;
    foreach ($ticketLists as $ticketList) {
        $newSearch = $hSavedSearch->create();
        $crit      = new \CriteriaCompo();
        switch ($ticketList) {
            case XHELP_QRY_STAFF_HIGHPRIORITY:
                $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID, '=', 'j'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                $crit->setSort('t.priority, t.posted');
                $newSearch->setVar('name', _XHELP_TEXT_HIGH_PRIORITY);
                $newSearch->setVar('pagenav_vars', 'limit=50&state=1');
                break;

            case XHELP_QRY_STAFF_NEW:
                $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID, '=', 'j'));
                $crit->add(new \Criteria('ownership', 0, '=', 't'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                $crit->setOrder('DESC');
                $newSearch->setVar('name', _XHELP_TEXT_NEW_TICKETS);
                $newSearch->setVar('pagenav_vars', 'limit=50&state=1');
                break;

            case XHELP_QRY_STAFF_MINE:
                $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID, '=', 'j'));
                $crit->add(new \Criteria('ownership', XHELP_GLOBAL_UID, '=', 't'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                $newSearch->setVar('name', _XHELP_TEXT_MY_TICKETS);
                $newSearch->setVar('pagenav_vars', 'limit=50&state=1&ownership=' . XHELP_GLOBAL_UID);
                break;

            case XHELP_QRY_STAFF_ALL:
                $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID, '=', 'j'));
                $crit->add(new \Criteria('state', 1, '=', 's'));
                $crit->add(new \Criteria('uid', XHELP_GLOBAL_UID, '=', 't'));
                $newSearch->setVar('name', _XHELP_TEXT_SUBMITTED_TICKETS);
                $newSearch->setVar('pagenav_vars', 'limit=50&state=1&submittedBy=' . XHELP_GLOBAL_UID);
                break;

            default:
                return false;
                break;
        }

        $newSearch->setVar('uid', XHELP_GLOBAL_UID);
        $newSearch->setVar('search', serialize($crit));
        $newSearch->setVar('hasCustFields', 0);
        $ret = $hSavedSearch->insert($newSearch, true);

        $staff = $hStaff->getObjects(null, true);
        foreach ($staff as $stf) {
            $list = $hTicketList->create();
            $list->setVar('uid', $stf->getVar('uid'));
            $list->setVar('searchid', $newSearch->getVar('id'));
            $list->setVar('weight', $i);
            $ret = $hTicketList->insert($list, true);
        }
        ++$i;
    }

    return true;
}

/**
 * @return Xhelp\EventService
 */
function &xhelpNewEventService()
{
    static $instance;

    if (null === $instance) {
        $instance = new Xhelp\EventService();
    }

    return $instance;
}
