<?php
//$Id: msgParser.php,v 1.14 2006/01/24 20:01:43 ackbarr Exp $

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

define('MD5SIGNATUREPATTERN', '/{([^ ]*)}/i');
define('HEADER_PRIORITY', 'Importance');
define('_XHELP_MSGTYPE_TICKET', 1);
define('_XHELP_MSGTYPE_RESPONSE', 2);

require_once(XHELP_PEAR_PATH.'/Mail/mimeDecode.php');

/**
 * xhelpMessageParser class
 *
 * Part of the email submission subsystem
 *
 * @author Brian Wahoff <ackbarr@xoops.org>
 * @access public
 * @package xhelp
 * @subpackage email
 */

class xhelpEmailParser
{
    var $_params = array();

    /**
     * Class Constructor
     * @return void
     * @access public
     */
    function xhelpEmailParser()
    {
        $this->_params['include_bodies'] = true;
        $this->_params['decode_bodies'] = true;
        $this->_params['decode_headers'] = true;
        $this->_params['input'] = '';
    }

    /**
     * Parses Message
     */
    function &parseMessage(&$msg)
    {
        $struct  = $this->_parseMsg($msg);
        $newMsg  = new xhelpParsedMessage($struct);

        return $newMsg;
    }

     
    function &_parseMsg(&$msg)
    {
        $arr = array();
        //Parse out attachments/HTML
        $this->_params['input'] = $msg['msg'];

        $structure   = Mail_mimeDecode::decode($this->_params);
        $body        = $this->_getBody($structure);
        $arr['hash'] = $this->_parseTicketID($structure->headers['subject']);
        $arr['msg']  = $this->_parseBody($body);
        $arr['mime_struct'] = $structure;
        $arr = array_merge($arr, $this->_parseFrom($structure->headers['from']));

        return $arr;
    }

    function &_parseFrom($from)
    {
        //Extract Name & Email from supplied message
        //eregi("From: (.*)\nTo:",$headers, $addr);
        preg_match('/"?([^"<@]*)"?/i',$from, $name);
        //eregi("<(.*)>",$from, $email);

        $arr['name'] = $name[1];

        $pattern['email'] = "/([a-z0-9\-_\.]+?)@([^, \r\n\"\(\)'<>\[\]]+)/i";

        preg_match($pattern['email'], $from, $matches);
        $arr['email'] = $matches[0];

        //$arr['email'] = $email[1];
        return $arr;
    }
    function _parseTicketID($msg)
    {
        $matches = array();
        $ret = preg_match(MD5SIGNATUREPATTERN, $msg, $matches);

        if ($ret) {
            // This function assumes that the ticket id is stored in the first
            // regex subquery. If the regex needs to be changed, this logic
            // may need to be changed as well.
            return($matches[1]);
        }

        return '';
    }

    function _parseBody($msg)
    {
        $msg = $this->_quoteBody($msg);
        $msg = $this->_stripMd5Key($msg);

        return $msg;
    }

    function _quoteBody($msg)
    {
        $current = 0;

        $msg = explode("\r\n", $msg);

        for($i = 0; $i < count($msg); $i++) {
            $pattern = array();
            $replace = array();
            $next = $current + 1;
            $prev = $current - 1;
            $pattern[0] = '/^(>[\s]?){'. $next .'}(.*)/i';
            $replace[0] = '[quote]\\2';
            $pattern[1]  = '/^(>[\s]?){'.$current.'}(.*)/i';
            $replace[1]  = '\\2';

            $pattern[2]  = '/^(>[\s]?){'.$prev.'}(.*)/i';
            $replace[2]  = '[/quote]\\2';

            //Check if current line indicates a quote
            if (preg_match($pattern[0], $msg[$i])) {

                $msg[$i] = preg_replace($pattern[0], $replace[0], $msg[$i]);
                $current++;

            } else {
                if ($current) {
                    //Check if line indicates a closed quote
                    if (preg_match($pattern[1], $msg[$i])) {

                        $msg[$i] = preg_replace($pattern[1], $replace[1], $msg[$i]);

                    } else {

                        $msg[$i] = preg_replace($pattern[2], $replace[2], $msg[$i]);
                        $current --;
                    }
                }

            }

        }

        return implode("\r\n",$msg);
    }

    function _stripMd5Key($msg)
    {
        $pattern = '/^\*{4}\s'. _XHELP_TICKET_MD5SIGNATURE . '\s(.)*\*{4}/im';

        return (preg_replace($pattern, '', $msg));
    }

    function _getBody(&$part, $primary = 'text', $secondary = 'plain')
    {
        $body = false;

        // 1. No subparts:

        // 2. array of subparts
        // 2a. subarray of subparts (recursion)?

        if (is_array($part)) {
            foreach($part as $subpart) {
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
                    return($part->body);
                }
            }
        }

        return $body;
    }

}

class xhelpParsedMessage
{
    var $_email;
    var $_msgtype;
    var $_name;
    var $_msg;
    var $_hash;
    var $_headers;
    var $_ticket;
    var $_attachments;

    /**
     * Class Constructor
     * @access public
     * @param $headers Array of message headers
     * @param $msg Array of message values
     */

    function xhelpParsedMessage(&$msg)
    {
        $struct         =& $msg['mime_struct'];
        $this->_email   = $msg['email'];
        $this->_name    = $msg['name'];
        $this->_headers = $struct->headers;
         
        $this->_hash        = $msg['hash'];
        $this->_msg         = $msg['msg'];
         
        $this->_msgtype     = (strlen($msg['hash']) == 0 ? _XHELP_MSGTYPE_TICKET : _XHELP_MSGTYPE_RESPONSE);
        $this->_attachments = array();
        $this->_loadAttachments($struct);
    }

    function getMsgType()
    {
        return $this->_msgtype;
    }

    function getPriority()
    {
        $pri = $this->getHeader(HEADER_PRIORITY);

        switch($pri)
        {
            default:
                return $pri;
        }
    }

    function getSubject()
    {
        return $this->_headers['subject'];
    }

    function getMsg()
    {
        return $this->_msg;
    }

    function getEmail()
    {
        return $this->_email;
    }

    function getName()
    {
        return $this->_name;
    }

    function getHeader($header)
    {
        if (isset($this->_headers[$header])) {
            return $this->_headers[$header];
        }

        return false;
    }

    function &getAllHeaders()
    {
        return $this->_headers;
    }

    function getHash()
    {
        return $this->_hash;
    }

    function &getAttachments()
    {
        return $this->_attachments;
    }

    function _loadAttachments($part)
    {

        if (is_array($part)) {
            foreach ($part as $subpart) {
                $this->_loadAttachments($subpart);
            }
        } else {
            if (isset($part->parts)) {
                $this->_loadAttachments($part->parts);
            } else {
                if ($part->ctype_primary == 'text' && $part->ctype_secondary == 'plain') {
                    if (isset($part->disposition) && $part->disposition == 'attachment')
                    {
                        $this->_addAttachment($part);
                    }
                    // Do Nothing
                } else {
                    $this->_addAttachment($part);
                }
            }
        }
    }

    function _addAttachment($part)
    {
        $_attach = array();
        $_attach['content-type'] = $part->ctype_primary.'/'.$part->ctype_secondary;
        $_attach['filename'] = (isset($part->d_parameters)? $this->_cleanFilename($part->d_parameters['filename']) : 'content_'. $part->ctype_primary .'_'.$part->ctype_secondary) ;
        $_attach['content'] = $part->body;
        $this->_attachments[] =& $_attach;
        unset($_attach);
    }

    /**
     * Removes unsafe characters from the attachment filename
     *
     * @param  string $name Original Filename
     * @return string "cleaned" filename
     * @access private
     * @todo Get list of other unsafe characters by platform
     */
    function _cleanFilename($name)
    {
        $name = str_replace(' ', '_', $name);

        return $name;
    }

}
