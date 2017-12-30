<?php namespace Xoopsmodules\xhelp;

use Xoopsmodules\xhelp;

define('_XHELP_MAILBOXTYPE_POP3', 1);
define('_XHELP_MAILBOXTYPE_IMAP', 2);

//

/**
 * xhelp\MailBox class
 *
 * Part of the email submission subsystem. Abstract class defining functions
 * needed to interact with a mailstore
 *
 * @author  Nazar Aziz <nazar@panthersoftware.com>
 * @access  public
 * @abstract
 * @package xhelp
 */
class MailBox
{
    /**
     * @param     $server
     * @param int $port
     */
    public function connect($server, $port = 110)
    {
    }

    //

    /**
     * @param $username
     * @param $password
     */
    public function login($username, $password)
    {
    }

    //
    public function messageCount()
    {
    }

    //

    /**
     * @param $i
     */
    public function getHeaders($i)
    {
    }

    //

    /**
     * @param $i
     */
    public function getBody($i)
    {
    }

    /**
     * @param $i
     */
    public function getMsg($i)
    {
    }

    //

    /**
     * @param $i
     */
    public function deleteMessage($i)
    {
    }

    //
    public function disconnect()
    {
    }
}
