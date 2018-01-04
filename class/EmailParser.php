<?php namespace XoopsModules\Xhelp;

//

use XoopsModules\Xhelp;

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

define('MD5SIGNATUREPATTERN', '/{([^ ]*)}/i');
define('HEADER_PRIORITY', 'Importance');
define('_XHELP_MSGTYPE_TICKET', 1);
define('_XHELP_MSGTYPE_RESPONSE', 2);

require_once XHELP_PEAR_PATH . '/Mail/mimeDecode.php';

/**
 * xhelpMessageParser class
 *
 * Part of the email submission subsystem
 *
 * @author     Brian Wahoff <ackbarr@xoops.org>
 * @access     public
 * @package    xhelp
 * @subpackage email
 */
class EmailParser
{
    public $_params = [];

    /**
     * Class Constructor
     * @access public
     */
    public function __construct()
    {
        $this->_params['include_bodies'] = true;
        $this->_params['decode_bodies']  = true;
        $this->_params['decode_headers'] = true;
        $this->_params['input']          = '';
    }

    /**
     * Parses Message
     * @param $msg
     * @return Xhelp\ParsedMessage
     */
    public function &parseMessage(&$msg)
    {
        $struct = $this->_parseMsg($msg);
        $newMsg = new Xhelp\ParsedMessage($struct);

        return $newMsg;
    }

    /**
     * @param $msg
     * @return array
     */
    public function &_parseMsg(&$msg)
    {
        $arr = [];
        //Parse out attachments/HTML
        $this->_params['input'] = $msg['msg'];

        $structure          = Mail_mimeDecode::decode($this->_params);
        $body               = $this->_getBody($structure);
        $arr['hash']        = $this->_parseTicketID($structure->headers['subject']);
        $arr['msg']         = $this->_parseBody($body);
        $arr['mime_struct'] = $structure;
        $arr                = array_merge($arr, $this->_parseFrom($structure->headers['from']));

        return $arr;
    }

    /**
     * @param $from
     * @return mixed
     */
    public function &_parseFrom($from)
    {
        //Extract Name & Email from supplied message
        //eregi("From: (.*)\nTo:",$headers, $addr);
        preg_match('/"?([^"<@]*)"?/i', $from, $name);
        //eregi("<(.*)>",$from, $email);

        $arr['name'] = $name[1];

        $pattern['email'] = "/([a-z0-9\-_\.]+?)@([^, \r\n\"\(\)'<>\[\]]+)/i";

        preg_match($pattern['email'], $from, $matches);
        $arr['email'] = $matches[0];

        //$arr['email'] = $email[1];
        return $arr;
    }

    /**
     * @param $msg
     * @return mixed|string
     */
    public function _parseTicketID($msg)
    {
        $matches = [];
        $ret     = preg_match(MD5SIGNATUREPATTERN, $msg, $matches);

        if ($ret) {
            // This function assumes that the ticket id is stored in the first
            // regex subquery. If the regex needs to be changed, this logic
            // may need to be changed as well.
            return $matches[1];
        }

        return '';
    }

    /**
     * @param $msg
     * @return mixed|string
     */
    public function _parseBody($msg)
    {
        $msg = $this->_quoteBody($msg);
        $msg = $this->_stripMd5Key($msg);

        return $msg;
    }

    /**
     * @param $msg
     * @return string
     */
    public function _quoteBody($msg)
    {
        $current = 0;

        $msg = explode("\r\n", $msg);

        for ($i = 0, $iMax = count($msg); $i < $iMax; ++$i) {
            $pattern    = [];
            $replace    = [];
            $next       = $current + 1;
            $prev       = $current - 1;
            $pattern[0] = '/^(>[\s]?){' . $next . '}(.*)/i';
            $replace[0] = '[quote]\\2';
            $pattern[1] = '/^(>[\s]?){' . $current . '}(.*)/i';
            $replace[1] = '\\2';

            $pattern[2] = '/^(>[\s]?){' . $prev . '}(.*)/i';
            $replace[2] = '[/quote]\\2';

            //Check if current line indicates a quote
            if (preg_match($pattern[0], $msg[$i])) {
                $msg[$i] = preg_replace($pattern[0], $replace[0], $msg[$i]);
                ++$current;
            } else {
                if ($current) {
                    //Check if line indicates a closed quote
                    if (preg_match($pattern[1], $msg[$i])) {
                        $msg[$i] = preg_replace($pattern[1], $replace[1], $msg[$i]);
                    } else {
                        $msg[$i] = preg_replace($pattern[2], $replace[2], $msg[$i]);
                        $current--;
                    }
                }
            }
        }

        return implode("\r\n", $msg);
    }

    /**
     * @param $msg
     * @return mixed
     */
    public function _stripMd5Key($msg)
    {
        $pattern = '/^\*{4}\s' . _XHELP_TICKET_MD5SIGNATURE . '\s(.)*\*{4}/im';

        return preg_replace($pattern, '', $msg);
    }

    /**
     * @param        $part
     * @param string $primary
     * @param string $secondary
     * @return bool
     */
    public function _getBody(&$part, $primary = 'text', $secondary = 'plain')
    {
        $body = false;

        // 1. No subparts:

        // 2. array of subparts
        // 2a. subarray of subparts (recursion)?

        if (is_array($part)) {
            foreach ($part as $subpart) {
                if (!$body = $this->_getBody($subpart, $primary, $secondary)) {
                    continue;
                } else {
                    return $body;
                }
            }
        } else {
            if (isset($part->parts)) {
                return $this->_getBody($part->parts, $primary, $secondary);
            } else {
                if ($part->ctype_primary == $primary && $part->ctype_secondary == $secondary) {
                    return $part->body;
                }
            }
        }

        return $body;
    }
}
