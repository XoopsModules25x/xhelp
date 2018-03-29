<?php

use XoopsModules\Xhelp;
/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

require_once __DIR__ . '/servicemain.php';

//Include xhelp Related Includes
//require_once XHELP_INCLUDE_PATH . '/events.php';
//require XHELP_CLASS_PATH . '/mailboxPOP3.php';
//require XHELP_CLASS_PATH . '/EmailParser.php';
//require XHELP_CLASS_PATH . '/EmailStore.php';
//require XHELP_CLASS_PATH . '/validator.php';

//Initialize xhelp objects
$msgParser  = new Xhelp\EmailParser();
$msgStore   = new Xhelp\EmailStore();
$hDeptBoxes = new Xhelp\DepartmentMailBoxHandler($GLOBALS['xoopsDB']);
$hMailEvent = new Xhelp\MailEventHandler($GLOBALS['xoopsDB']);
$hTicket    = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);

$_eventsrv->advise('new_user_by_email', Xhelp\NotificationService::getInstance(), 'new_user_activation' . $xoopsConfigUser['activation_type']);

//Get All Department Mailboxes
$deptmboxes = $hDeptBoxes->getActiveMailboxes();

//Loop Through All Department Mailboxes
foreach ($deptmboxes as $mbox) {
    $deptid = $mbox->getVar('departmentid');
    //Connect to the mailbox
    if ($mbox->connect()) {
        //Check for new messages
        if ($mbox->hasMessages()) {
            //Retrieve / Store each message
            while ($msg = $mbox->getMessage()) {
                $msg_logs = [];
                $skip_msg = false;

                //Check if there are any errors parsing msg
                if ($parsed = $msgParser->parseMessage($msg)) {

                    //Sanity Check: Disallow emails from other department mailboxes
                    if (_isDepartmentEmail($parsed->getEmail())) {
                        $msg_logs[_XHELP_MAIL_CLASS3][] = sprintf(_XHELP_MESSAGE_EMAIL_DEPT_MBOX, $parsed->getEmail());
                    } else {

                        //Create new user account if necessary

                        if (!$xoopsUser = Xhelp\Utility::emailIsXoopsUser($parsed->getEmail())) {
                            if ($helper->getConfig('xhelp_allowAnonymous')) {
                                switch ($xoopsConfigUser['activation_type']) {
                                    case 1:
                                        $level = 1;
                                        break;

                                    case 0:
                                    case 2:
                                    default:
                                        $level = 0;
                                }
                                $xoopsUser = Xhelp\Utility::getXoopsAccountFromEmail($parsed->getEmail(), $parsed->getName(), $password, $level);
                                $_eventsrv->trigger('new_user_by_email', [$password, $xoopsUser]);
                            } else {
                                $msg_logs[_XHELP_MAIL_CLASS3][] = sprintf(_XHELP_MESSAGE_NO_ANON, $parsed->getEmail());
                                $skip_msg                       = true;
                            }
                        }

                        if (false === $skip_msg) {
                            //Store Message In Server
                            if ($obj = $msgStore->storeMsg($parsed, $xoopsUser, $mbox, $errors)) {
                                switch ($parsed->getMsgType()) {
                                    case _XHELP_MSGTYPE_TICKET:
                                        //Trigger New Ticket Events
                                        $_eventsrv->trigger('new_ticket', $obj);
                                        break;
                                    case _XHELP_MSGTYPE_RESPONSE:
                                        //Trigger New Response Events
                                        $_eventsrv->trigger('new_response', $obj);
                                        break;
                                }
                                //} else {        // If message not stored properly, log event
                                //    $storeEvent = $hMailEvent->newEvent($mbox->getVar('id'), _XHELP_MAILEVENT_DESC2, _XHELP_MAILEVENT_CLASS2);
                            } else {
                                $msg_logs[_XHELP_MAILEVENT_CLASS2] =& $errors;
                            }
                        }
                    }
                } else {
                    $msg_logs[_XHELP_MAILEVENT_CLASS1][] = _XHELP_MAILEVENT_DESC1;
                }
                //Remove Message From Server
                $mbox->deleteMessage($msg);

                //Log Any Messages
                _logMessages($mbox->getVar('id'), $msg_logs);
            }
        }
        //Disconnect from Server
        $mbox->disconnect();
    } else {                        // If mailbox not connected properly, log event
        $connEvent =& $hMailEvent->newEvent($mbox->getVar('id'), _XHELP_MAILEVENT_DESC0, _XHELP_MAILEVENT_CLASS0);
    }
}

/**
 * @param $mbox
 * @param $arr
 */
function _logMessages($mbox, $arr)
{
    global $hMailEvent;
    foreach ($arr as $class => $msg) {
        if (is_array($msg)) {
            $msg = implode("\r\n", $msg);
        }
        $event =& $hMailEvent->newEvent($mbox, $msg, $class);
    }
}

/**
 * @param $email
 * @return bool
 */
function _isDepartmentEmail($email)
{
    static $email_arr;

    if (!isset($email_arr)) {
        global $hDeptBoxes;
        $deptmboxes = $hDeptBoxes->getObjects();
        $email_arr  = [];
        foreach ($deptmboxes as $obj) {
            $email_arr[] = $obj->getVar('emailaddress');
        }
        unset($deptmboxes);
    }

    $ret = in_array($email, $email_arr);

    return $ret;
}
