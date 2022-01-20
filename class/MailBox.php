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

\define('_XHELP_MAILBOXTYPE_POP3', 1);
\define('_XHELP_MAILBOXTYPE_IMAP', 2);

/**
 * Xhelp\MailBox class
 *
 * Part of the email submission subsystem. Abstract class defining functions
 * needed to interact with a mailstore
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
