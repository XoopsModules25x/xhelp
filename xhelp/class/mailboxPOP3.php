<?php
//$Id: mailboxPOP3.php,v 1.7 2005/02/15 16:58:03 ackbarr Exp $
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}


require_once(XHELP_CLASS_PATH.'/mailbox.php');
require_once(XHELP_PEAR_PATH.'/Net/POP3.php');

/**
 * xhelpMailBoxPop3 class
 *
 * Part of the email submission subsystem. Implements access to a POP3 Mailbox
 *
 * @author Nazar Aziz <nazar@panthersoftware.com>
 * @access public
 * @package xhelp
 */
class xhelpMailBoxPOP3 extends xhelpMailBox
{
    /**
     * Instances of PEAR::POP3 class
     * @access private
     */
    var $_pop3;

    /**
     * Class Constructor
     * @return void
     */
    function xhelpMailBoxPOP3()
    {
        $this->_pop3 = new Net_POP3();
    }

    /**
     * Connect to mailbox
     * @param string IP or DNS name of server
     * @param int Service Port Number
     * @return bool
     */
    function connect($server, $port = 110)
    {
        if ($this->_pop3->connect($server,$port)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send Authentication Credentials to mail server
     * @param string account name
     * @param string account password
     * @return bool
     */
    function login($username, $password)
    {
        if (!PEAR::isError($this->_pop3->login($username,$password,false))) {
            return true;
        }  else {
            return false;
        }
    }

    /**
     * Number of messages on server
     * @return int Number of messages
     */
    function messageCount()
    {
        return $this->_pop3->numMsg();
    }

    /**
     * Get Headers for message
     * @param  $msg_id Message number
     * Either raw headers or false on error
     */
    function getHeaders($i)
    {
        return $this->_pop3->getRawHeaders($i);
    }

    /**
     * Get Message Body
     * @param  $msg_id Message number
     * @return mixed   Either message body or false on error
     */
    function getBody($i)
    {
        return $this->_pop3->getBody($i);
    }

    /**
     * Returns the entire message with given message number.
     *
     * @param  $msg_id Message number
     * @return mixed   Either entire message or false on error
     */
    function getMsg($i)
    {
        return $this->_pop3->getMsg($i);
    }

    /**
     * Marks a message for deletion. Only will be deleted if the
     * disconnect() method is called.
     *
     * @param  $msg_id Message to delete
     * @return bool Success/Failure
     */
    function deleteMessage($i)
    {
        return $this->_pop3->deleteMsg($i);
    }

    /**
     * Disconnect function. Sends the QUIT command
     * and closes the socket.
     *
     * @return bool Success/Failure
     */
    function disconnect()
    {
        return $this->_pop3->disconnect();
    }
}
?>