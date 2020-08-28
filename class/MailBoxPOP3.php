<?php

namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/mailbox.php';
require_once \XHELP_PEAR_PATH . '/Net/POP3.php';

/**
 * Xhelp\MailBoxPop3 class
 *
 * Part of the email submission subsystem. Implements access to a POP3 Mailbox
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @access  public
 * @package xhelp
 */
class MailBoxPOP3 extends Xhelp\MailBox
{
    /**
     * Instances of PEAR::POP3 class
     * @access private
     */
    public $_pop3;

    /**
     * Class Constructor
     */
    public function __construct()
    {
        $this->_pop3 = new Net_POP3();
    }

    /**
     * Connect to mailbox
     * @param mixed $server
     * @param mixed $port
     * @return bool
     */
    public function connect($server, $port = 110)
    {
        if ($this->_pop3->connect($server, $port)) {
            return true;
        }

        return false;
    }

    /**
     * Send Authentication Credentials to mail server
     * @param mixed $username
     * @param mixed $password
     * @return bool
     */
    public function login($username, $password)
    {
        if (!PEAR::isError($this->_pop3->login($username, $password, false))) {
            return true;
        }

        return false;
    }

    /**
     * Number of messages on server
     * @return int Number of messages
     */
    public function messageCount()
    {
        return $this->_pop3->numMsg();
    }

    /**
     * Get Headers for message
     * @param $i
     * @return bool|string|void
     * @internal param Message $msg_id number
     *                 Either raw headers or false on error
     */
    public function getHeaders($i)
    {
        return $this->_pop3->getRawHeaders($i);
    }

    /**
     * Get Message Body
     * @param $i
     * @return mixed Either message body or false on error
     * @internal param Message $msg_id number
     */
    public function getBody($i)
    {
        return $this->_pop3->getBody($i);
    }

    /**
     * Returns the entire message with given message number.
     *
     * @param $i
     * @return mixed Either entire message or false on error
     * @internal param Message $msg_id number
     */
    public function getMsg($i)
    {
        return $this->_pop3->getMsg($i);
    }

    /**
     * Marks a message for deletion. Only will be deleted if the
     * disconnect() method is called.
     *
     * @param $i
     * @return bool Success/Failure
     * @internal param Message $msg_id to delete
     */
    public function deleteMessage($i)
    {
        return $this->_pop3->deleteMsg($i);
    }

    /**
     * Disconnect function. Sends the QUIT command
     * and closes the socket.
     *
     * @return bool Success/Failure
     */
    public function disconnect()
    {
        return $this->_pop3->disconnect();
    }
}
