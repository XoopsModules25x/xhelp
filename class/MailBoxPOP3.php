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
 * @author       Nazar Aziz <nazar@panthersoftware.com>
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/mailbox.php';
require_once \XHELP_PEAR_PATH . '/Net/POP3.php';

/**
 * MailBoxPop3 class
 *
 * Part of the email submission subsystem. Implements access to a POP3 Mailbox
 *
 */
class MailBoxPOP3 extends MailBox
{
    /**
     * Instances of PEAR::POP3 class
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
     * @param string $server
     * @param int    $port
     * @return bool
     */
    public function connect(string $server, int $port = 110): bool
    {
        if ($this->_pop3->connect($server, $port)) {
            return true;
        }

        return false;
    }

    /**
     * Send Authentication Credentials to mail server
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function login(string $username, string $password): bool
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
    public function messageCount(): int
    {
        return $this->_pop3->numMsg();
    }

    /**
     * Get Headers for message
     * @param int $i
     * @return bool|string|void
     * @internal param Message $msg_id number
     *                 Either raw headers or false on error
     */
    public function getHeaders(int $i)
    {
        return $this->_pop3->getRawHeaders($i);
    }

    /**
     * Get Message Body
     * @param int $i
     * @return mixed Either message body or false on error
     * @internal param Message $msg_id number
     */
    public function getBody(int $i)
    {
        return $this->_pop3->getBody($i);
    }

    /**
     * Returns the entire message with given message number.
     *
     * @param int $i
     * @return mixed Either entire message or false on error
     * @internal param Message $msg_id number
     */
    public function getMsg(int $i)
    {
        return $this->_pop3->getMsg($i);
    }

    /**
     * Marks a message for deletion. Only will be deleted if the
     * disconnect() method is called.
     *
     * @param int $i
     * @return bool Success/Failure
     * @internal param Message $msg_id to delete
     */
    public function deleteMessage(int $i): bool
    {
        return $this->_pop3->deleteMsg($i);
    }

    /**
     * Disconnect function. Sends the QUIT command
     * and closes the socket.
     *
     * @return bool Success/Failure
     */
    public function disconnect(): bool
    {
        return $this->_pop3->disconnect();
    }
}
