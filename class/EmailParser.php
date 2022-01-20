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
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

\define('MD5SIGNATUREPATTERN', '/{([^ ]*)}/i');
\define('HEADER_PRIORITY', 'Importance');
\define('_XHELP_MSGTYPE_TICKET', 1);
\define('_XHELP_MSGTYPE_RESPONSE', 2);

require_once \XHELP_PEAR_PATH . '/Mail/mimeDecode.php';

/**
 * EmailParser class
 *
 * Part of the email submission subsystem
 *
 * @author     Brian Wahoff <ackbarr@xoops.org>
 */
class EmailParser
{
    public $_params = [];

    /**
     * Class Constructor
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
     * @param array $msg
     * @return ParsedMessage
     */
    public function &parseMessage(array $msg): ParsedMessage
    {
        $struct = $this->_parseMsg($msg);
        $newMsg = new ParsedMessage($struct);

        return $newMsg;
    }

    /**
     * @param array $msg
     * @return array
     */
    public function &_parseMsg(array $msg): array
    {
        $arr = [];
        //Parse out attachments/HTML
        $this->_params['input'] = $msg['msg'];

        $structure          = Mail_mimeDecode::decode($this->_params);
        $body               = $this->_getBody($structure);
        $arr['hash']        = $this->parseTicketID($structure->headers['subject']);
        $arr['msg']         = $this->_parseBody($body);
        $arr['mime_struct'] = $structure;
        $arr                = \array_merge($arr, $this->_parseFrom($structure->headers['from']));

        return $arr;
    }

    /**
     * @param string $from
     * @return array
     */
    public function &_parseFrom(string $from): array
    {
        //Extract Name & Email from supplied message
        //eregi("From: (.*)\nTo:",$headers, $addr);
        \preg_match('/"?([^"<@]*)"?/i', $from, $name);
        //eregi("<(.*)>",$from, $email);

        $arr['name'] = $name[1];

        $pattern['email'] = "/([a-z0-9\-_\.]+?)@([^, \r\n\"\(\)'<>\[\]]+)/i";

        \preg_match($pattern['email'], $from, $matches);
        $arr['email'] = $matches[0];

        //$arr['email'] = $email[1];
        return $arr;
    }

    /**
     * @param string $msg
     * @return mixed|string
     */
    public function parseTicketID(string $msg)
    {
        $matches = [];
        $ret     = \preg_match(MD5SIGNATUREPATTERN, $msg, $matches);

        if ($ret) {
            // This function assumes that the ticket id is stored in the first
            // regex subquery. If the regex needs to be changed, this logic
            // may need to be changed as well.
            return $matches[1];
        }

        return '';
    }

    /**
     * @param array $msg
     * @return array|string|string[]|null
     */
    public function _parseBody(array $msg)
    {
        $msg = $this->_quoteBody((string)$msg);
        $msg = $this->_stripMd5Key($msg);

        return $msg;
    }

    /**
     * @param string $msg
     * @return string
     */
    public function _quoteBody(string $msg): string
    {
        $current = 0;

        $msg = \explode("\r\n", $msg);

        foreach ($msg as $i => $iValue) {
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
            if (\preg_match($pattern[0], $iValue)) {
                $msg[$i] = \preg_replace($pattern[0], $replace[0], $iValue);
                ++$current;
            } else {
                if ($current) {
                    //Check if line indicates a closed quote
                    if (\preg_match($pattern[1], $iValue)) {
                        $msg[$i] = \preg_replace($pattern[1], $replace[1], $iValue);
                    } else {
                        $msg[$i] = \preg_replace($pattern[2], $replace[2], $iValue);
                        $current--;
                    }
                }
            }
        }

        return \implode("\r\n", $msg);
    }

    /**
     * @param string $msg
     * @return array|string|string[]|null
     */
    public function _stripMd5Key(string $msg)
    {
        $pattern = '/^\*{4}\s' . \_XHELP_TICKET_MD5SIGNATURE . '\s(.)*\*{4}/im';

        return \preg_replace($pattern, '', $msg);
    }

    /**
     * @param object|array $part
     * @param string       $primary
     * @param string       $secondary
     * @return bool|array
     */
    public function _getBody($part, string $primary = 'text', string $secondary = 'plain')
    {
        $body = false;

        // 1. No subparts:

        // 2. array of subparts
        // 2a. subarray of subparts (recursion)?

        if (\is_array($part)) {
            foreach ($part as $subpart) {
                if (!$body = $this->_getBody($subpart, $primary, $secondary)) {
                    continue;
                }

                return $body;
            }
        } else {
            if (isset($part->parts)) {
                return $this->_getBody($part->parts, $primary, $secondary);
            }
            if ($part->ctype_primary == $primary && $part->ctype_secondary == $secondary) {
                return $part->body;
            }
        }

        return $body;
    }
}
