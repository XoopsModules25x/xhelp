<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

\define('_XHELP_MAILBOXTYPE_POP3', 1);
\define('_XHELP_MAILBOXTYPE_IMAP', 2);

/**
 * Xhelp\MailBox class
 *
 * Part of the email submission subsystem. Abstract class defining functions
 * needed to interact with a mailstore
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @abstract
 */
class MailBox
{
    /**
     * @param string $server
     * @param int    $port
     */
    public function connect(string $server, int $port = 110)
    {
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function login(string $username, string $password)
    {
    }

    public function messageCount()
    {
    }

    /**
     * @param int $i
     */
    public function getHeaders(int $i)
    {
    }

    /**
     * @param int $i
     */
    public function getBody(int $i)
    {
    }

    /**
     * @param int $i
     */
    public function getMsg(int $i)
    {
    }

    /**
     * @param int $i
     */
    public function deleteMessage(int $i)
    {
    }

    public function disconnect()
    {
    }
}
