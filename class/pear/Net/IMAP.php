<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>       |
// +----------------------------------------------------------------------+


require_once XHELP_PEAR_PATH.'/Net/IMAPProtocol.php';

/**
 * Provides an implementation of the IMAP protocol using PEAR's
 * Net_Socket:: class.
 *
 * @package Net_IMAP
 * @author  Damian Alejandro Fernandez Sosa <damlists@cnba.uba.ar>
 */
class Net_IMAP extends Net_IMAPProtocol
{

    /**
     * Constructor
     *
     * Instantiates a new Net_SMTP object, overriding any defaults
     * with parameters that are passed in.
     *
     * @param string The server to connect to.
     * @param int The port to connect to.
     * @param string The value to give when sending EHLO or HELO.
     */

    public function __construct($host = 'localhost', $port = 143)
    {
        Net_IMAPProtocol::__construct();
        $ret = $this->connect($host, $port);
    }

    /**
     * Attempt to connect to the IMAP server located at $host $port
     * @param string $host The IMAP server
     * @param string $port The IMAP port
     *
     *          It is only useful in a very few circunstances
     *          because the contructor already makes this job
     * @return true on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function connect($host, $port)
    {
        $ret=$this->cmdConnect($host, $port);
        if (true === $ret) {
            return $ret;
        }
        if (empty($ret)) {
            return new PEAR_Error('Unexpected response on connection');
        }
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if (isset($ret['RESPONSE']['CODE'])) {
            if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
                return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
            }
        }

        return $ret;
    }

    /**
     * Attempt to authenticate to the IMAP server.
     * @param string  $user            The userid to authenticate as.
     * @param string  $pass            The password to authenticate with.
     * @param bool    $useauthenticate true: authenticate using
     *                                 the IMAP AUTHENTICATE command. false: authenticate using
     *                                 the IMAP AUTHENTICATE command. 'string': authenticate using
     *                                 the IMAP AUTHENTICATE command but using the authMethod in 'string'
     * @param boolean $selectMailbox   automaticaly select inbox on login (false does not)
     *
     * @return true on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */

    public function login($user, $pass, $useauthenticate = true, $selectMailbox=true)
    {
        if ($useauthenticate) {
            //$useauthenticate is a string if the user hardcodes an AUTHMethod
            // (the user calls $imap->login("user","password","CRAM-MD5"); for example!

            $method = is_string($useauthenticate) ? $useauthenticate : null;

            //Try the selected Auth method
            if (PEAR::isError($ret = $this->cmdAuthenticate($user, $pass, $method))) {
                // Verify the methods that we have in common with the server
                if (is_array($this->_serverAuthMethods)) {
                    $commonMethods=array_intersect($this->supportedAuthMethods, $this->_serverAuthMethods);
                } else {
                    $this->_serverAuthMethods=null;
                }
                if (null == $this->_serverAuthMethods || 0 == count($commonMethods) || null == $this->supportedAuthMethods) {
                    // The server does not have any auth method, so I try LOGIN
                    if (PEAR::isError($ret = $this->cmdLogin($user, $pass))) {
                        return $ret;
                    }
                } else {
                    return $ret;
                }
            }
            if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
                return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
            }
        } else {
            //The user request "PLAIN"  auth, we use the login command
            if (PEAR::isError($ret = $this->cmdLogin($user, $pass))) {
                return $ret;
            }
            if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
                return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
            }
        }

        if ($selectMailbox) {
            //Select INBOX
            if (PEAR::isError($ret=$this->cmdSelect($this->getCurrentMailbox()))) {
                return $ret;
            }
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /*
     * Disconnect function. Sends the QUIT command
     * and closes the socket.
     *
     * @return bool Success/Failure
     */
    public function disconnect($expungeOnExit = false)
    {
        if ($expungeOnExit) {
            $ret=$this->cmdExpunge();
            if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
                $ret=$this->cmdLogout();

                return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
            }
        }
        $ret=$this->cmdLogout();
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /*
     * Changes  the default/current mailbox th $mailbox
     *
     *
     * @return bool Success/Pear_Error Failure
     */
    public function selectMailbox($mailbox)
    {
        $ret=$this->cmdSelect($mailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /*
     * Checks  the mailbox $mailbox
     *
     *
     * @return bool Success/Pear_Error Failure
     */
    public function examineMailbox($mailbox)
    {
        $ret=$this->cmdExamine($mailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        //$ret_aux["EXISTS"]=$ret["PARSED"]["EXISTS"];
        //$ret_aux["RECENT"]=$ret["PARSED"]["RECENT"];
        return $ret;
    }

    /*
     * Returns the raw headers of the specified message.
     *
     * @param  $msg_id Message number
     * @return mixed   Either raw headers or false on error
     */
    public function getRawHeaders($msg_id)
    {
        $ret=$this->cmdFetch($msg_id, 'BODY[HEADER]');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        $ret= $ret['PARSED'][0]['EXT']['BODY[HEADER]']['CONTENT'];

        return $ret;
    }

    /*
     * Returns the  headers of the specified message in an
     * associative array. Array keys are the header names, array
     * values are the header values. In the case of multiple headers
     * having the same names, eg Received:, the array value will be
     * an indexed array of all the header values.
     *
     * @param  $msg_id Message number
     * @return mixed   Either array of headers or false on error
     */
    public function getParsedHeaders($msg_id)
    {
        $ret=$this->getRawHeaders($msg_id);

        $raw_headers = rtrim($ret);
        $raw_headers = preg_replace("/\r\n[ \t]+/", ' ', $raw_headers); // Unfold headers
        $raw_headers = explode("\r\n", $raw_headers);
        foreach ($raw_headers as $value) {
            $name  = substr($value, 0, $pos = strpos($value, ':'));
            $value = ltrim(substr($value, $pos + 1));
            if (isset($headers[$name]) and is_array($headers[$name])) {
                $headers[$name][] = $value;
            } elseif (isset($headers[$name])) {
                $headers[$name] = [$headers[$name], $value];
            } else {
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /*
     * Returns an array containing the message ID, the size and the UID
     * of each message selected.
     * message selection can be a valid IMAP command, a number or an array of
     * messages
     *
     * @param  $msg_id Message number
     * @return mixed   Either array of message data or PearError on error
     */

    public function getMessagesList($msg_id = null)
    {
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }
        $ret=$this->cmdFetch($message_set, '(RFC822.SIZE UID)');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        foreach ($ret['PARSED'] as $msg) {
            $ret_aux[]= ['msg_id' => $msg['NRO'], 'size' => $msg['EXT']['RFC822.SIZE'], 'uidl' => $msg['EXT']['UID']];
        }

        return $ret_aux;
    }

    public function getSummary($msg_id = null)
    {
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }
        $ret=$this->cmdFetch($message_set, '(RFC822.SIZE UID FLAGS ENVELOPE INTERNALDATE)');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED'])) {
            for ($i=0, $iMax = count($ret['PARSED']); $i < $iMax; $i++) {
                $a= $ret['PARSED'][$i]['EXT']['ENVELOPE'];
                $a['MSG_NUM']= $ret['PARSED'][$i]['NRO'];
                $a['UID']= $ret['PARSED'][$i]['EXT']['UID'];
                $a['FLAGS']= $ret['PARSED'][$i]['EXT']['FLAGS'];
                $a['INTERNALDATE']= $ret['PARSED'][$i]['EXT']['INTERNALDATE'];
                $a['SIZE']= $ret['PARSED'][$i]['EXT']['RFC822.SIZE'];
                $env[]=$a;
                $a=null;
            }

            return $env;
        }

        //return $ret;
    }

    /*
     * Returns the body of the message with given message number.
     *
     * @param  $msg_id Message number
     * @return mixed   Either message body or false on error
     */
    public function getBody($msg_id)
    {
        $ret=$this->cmdFetch($msg_id, 'BODY[TEXT]');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        $ret= $ret['PARSED'][0]['EXT']['BODY[TEXT]']['CONTENT'];
        //$ret=$resp["PARSED"][0]["EXT"]["RFC822"]["CONTENT"];
        return $ret;
    }

    /*
     * Returns the entire message with given message number.
     *
     * @param  $msg_id Message number
     * @return mixed   Either entire message or false on error
     */
    public function getMessages($msg_id = null, $indexIsMessageNumber=true)
    {
        //$resp=$this->cmdFetch($msg_id,"(BODY[TEXT] BODY[HEADER])");
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }

        $ret=$this->cmdFetch($message_set, 'RFC822');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $msg) {
                if (isset($msg['EXT']['RFC822']['CONTENT'])) {
                    if ($indexIsMessageNumber) {
                        $ret_aux[$msg['NRO']] = $msg['EXT']['RFC822']['CONTENT'];
                    } else {
                        $ret_aux[]= $msg['EXT']['RFC822']['CONTENT'];
                    }
                }
            }

            return $ret_aux;
        }

        return [];
    }

    /*
     * Returns number of messages in this mailbox
     *
     * @param  string $mailbox  the mailbox
     * @return mixed Either number of messages or Pear_Error on error
     */
    public function getNumberOfMessages($mailbox = '')
    {
        if ('' == $mailbox || null == $mailbox) {
            $mailbox=$this->getCurrentMailbox();
        }
        $ret=$this->cmdStatus($mailbox, 'MESSAGES');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'])) {
                // if this array does not exists means that there is no messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['MESSAGES'];
            }
        }

        return 0;
    }

    /*
     * Returns number of UnSeen messages in this mailbox
     *
     * @param  string $mailbox  the mailbox
     * @return mixed Either number of messages or Pear_Error on error
     */
    public function getNumberOfUnSeenMessages($mailbox = '')
    {
        if ('' == $mailbox) {
            $mailbox=$this->getCurrentMailbox();
        }
        $ret=$this->cmdStatus($mailbox, 'UNSEEN');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'])) {
                // if this array does not exists means that there is no messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['UNSEEN'];
            }
        }

        return 0;
    }

    /*
     * Returns number of UnSeen messages in this mailbox
     *
     * @param  string $mailbox  the mailbox
     * @return mixed Either number of messages or Pear_Error on error
     */
    public function getNumberOfRecentMessages($mailbox = '')
    {
        if ('' == $mailbox) {
            $mailbox=$this->getCurrentMailbox();
        }
        $ret=$this->cmdStatus($mailbox, 'RECENT');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        if (isset($ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'])) {
            if (!is_numeric($ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'])) {
                // if this array does not exists means that there is no messages in the mailbox
                return 0;
            } else {
                return $ret['PARSED']['STATUS']['ATTRIBUTES']['RECENT'];
            }
        }

        return 0;
    }

    /*
     * Returns an array containing the message envelope
     *
     * @return mixed Either the envelopes or Pear_Error on error
     */
    public function getEnvelope($mailbox = '', $msg_id = null)
    {
        if ('' == $mailbox) {
            $mailbox=$this->getCurrentMailbox();
        }

        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }

        $ret=$this->cmdFetch($message_set, 'ENVELOPE');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED'])) {
            for ($i=0, $iMax = count($ret['PARSED']); $i < $iMax; $i++) {
                $a= $ret['PARSED'][$i]['EXT']['ENVELOPE'];
                $a['MSG_NUM']= $ret['PARSED'][$i]['NRO'];
                $env[]=$a;
            }

            return $env;
        }

        return new PEAR_Error('Error, undefined number of messages');
    }

    /*
     * Returns the sum of all the sizes of messages in $mailbox
     *           WARNING!!!  The method's performance is not good
     *                       if you have a lot of messages in the mailbox
     *                       Use with care!
     * @return mixed Either size of maildrop or false on error
     */
    public function getMailboxSize($mailbox = '')
    {
        if ('' != $mailbox && $mailbox != $this->getCurrentMailbox()) {
            // store the actual selected mailbox name
            $mailbox_aux = $this->getCurrentMailbox();
            if (PEAR::isError($ret = $this->selectMailbox($mailbox))) {
                return $ret;
            }
        }

        $ret=$this->cmdFetch('1:*', 'RFC822.SIZE');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            // Restore the default mailbox if it was changed
            if ('' != $mailbox && $mailbox != $this->getCurrentMailbox()) {
                if (PEAR::isError($ret = $this->selectMailbox($mailbox_aux))) {
                    return $ret;
                }
            }
            // return 0 because the server says that there is no message in the mailbox
            return 0;
        }

        $sum=0;

        if (!isset($ret['PARSED'])) {
            // if the server does not return a "PARSED"  part
            // we think that it does not suppoprt select or has no messages in it.
            return 0;
        }
        foreach ($ret['PARSED'] as $msgSize) {
            if (isset($msgSize['EXT']['RFC822.SIZE'])) {
                $sum+= $msgSize['EXT']['RFC822.SIZE'];
            }
        }

        if ('' != $mailbox && $mailbox != $this->getCurrentMailbox()) {
            // re-select the  mailbox
            if (PEAR::isError($ret = $this->selectMailbox($mailbox_aux))) {
                return $ret;
            }
        }

        return $sum;
    }

    /*
     * Marks a message for deletion. Only will be deleted if the
     * disconnect() method is called with auto-expunge on true or expunge()
     * method is called.
     *
     * @param  $msg_id Message to delete
     * @return bool Success/Failure
     */
    public function deleteMessages($msg_id = null)
    {
        /* As said in RFC2060...
         C: A003 STORE 2:4 +FLAGS (\Deleted)
         S: * 2 FETCH FLAGS (\Deleted \Seen)
         S: * 3 FETCH FLAGS (\Deleted)
         S: * 4 FETCH FLAGS (\Deleted \Flagged \Seen)
         S: A003 OK STORE completed
         */
        //Called without parammeters deletes all the messages in the mailbox
        // You can also provide an array of numbers to delete those emails
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }

        $dataitem= '+FLAGS.SILENT';
        $value="\Deleted";
        $ret=$this->cmdStore($message_set, $dataitem, $value);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * Copies mail from one folder to another
     *
     * @param string $dest_mailbox   mailbox name to copy sessages to
     * @param null   $msg_id
     * @param string $source_mailbox mailbox name from where the messages are copied
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function copyMessages($dest_mailbox, $msg_id = null, $source_mailbox = null)
    {
        if (null == $source_mailbox) {
            $source_mailbox = $this->getCurrentMailbox();
        } else {
            if (PEAR::isError($ret = $this->selectMailbox($source_mailbox))) {
                return $ret;
            }
        }
        //Called without parammeters copies all messages in the mailbox
        // You can also provide an array of numbers to copy those emails
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }

        if (PEAR::isError($ret = $this->cmdCopy($message_set, $dest_mailbox))) {
            return $ret;
        }

        return true;
    }

    /**
     * Appends a mail to  a mailbox
     *
     * @param string $rfc_message the message to append in RFC822 format
     * @param string $mailbox     mailbox name to append to
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function appendMessage($rfc_message, $mailbox = null)
    {
        if (null == $mailbox) {
            $mailbox = $this->getCurrentMailbox();
        }
        $ret=$this->cmdAppend($mailbox, $rfc_message);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /******************************************************************
     **                                                               **
     **           MAILBOX RELATED METHODS                             **
     **                                                               **
     ******************************************************************/

    /**
     * Gets the HierachyDelimiter character used to create subfolders  cyrus users "."
     *   and wu-imapd uses "/"
     *
     * $param  string  the mailbox to get the hierarchy from
     * @param string $mailbox
     * @return string the hierarchy delimiter
     *
     * @access public
     * @since  1.0
     */
    public function getHierarchyDelimiter($mailbox = '')
    {

        /* RFC2060 says: "the command LIST "" "" means get the hierachy delimiter:
         An empty ("" string) mailbox name argument is a special request to
         return the hierarchy delimiter and the root name of the name given
         in the reference.  The value returned as the root MAY be null if
         the reference is non-rooted or is null.  In all cases, the
         hierarchy delimiter is returned.  This permits a client to get the
         hierarchy delimiter even when no mailboxes by that name currently
         exist."
         */
        if (PEAR::isError($ret = $this->cmdList($mailbox, ''))) {
            return $ret;
        }
        if (isset($ret['PARSED'][0]['EXT']['LIST']['HIERACHY_DELIMITER'])) {
            return $ret['PARSED'][0]['EXT']['LIST']['HIERACHY_DELIMITER'];
        }

        return new PEAR_Error('the IMAP Server does not support HIERACHY_DELIMITER!');
    }

    /**
     * Returns an array containing the names of the selected mailboxes
     *
     * @param string $reference
     * @param int    $restriction_search false or 0 means return all mailboxes  true or 1 return only the mailbox that contains that exact name
     *
     * 2  return all mailboxes in that hierarchy level
     * @param bool   $returnAttributes   true means return an assoc array containing mailbox names and mailbox attributes
     *
     * false - the default - means return an array of mailboxes
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */

    public function getMailboxes($reference = '', $restriction_search = 0, $returnAttributes=false)
    {
        if (is_bool($restriction_search)) {
            $restriction_search = (int) $restriction_search;
        }

        if (is_int($restriction_search)) {
            switch ($restriction_search) {
                case 0:
                    $mailbox = '*';
                    break;
                case 1:
                    $mailbox = $reference;
                    $reference = '%';
                    break;
                case 2:
                    $mailbox = '%';
                    break;
            }
        } else {
            if (is_string($restriction_search)) {
                $mailbox = $restriction_search;
            } else {
                return new PEAR_Error('UPS... you ');
            }
        }

        if (PEAR::isError($ret = $this->cmdList($reference, $mailbox))) {
            return $ret;
        }

        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        $ret_aux= [];
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {

                //If the folder has the \NoSelect atribute we don't put in the list
                // it solves a bug in wu-imap that crash the IMAP server if we select that mailbox
                if (isset($mbox['EXT']['LIST']['NAME_ATTRIBUTES'])) {
                    if (!in_array('\NoSelect', $mbox['EXT']['LIST']['NAME_ATTRIBUTES'])) {
                        if ($returnAttributes) {
                            $ret_aux[]= [
                                'MAILBOX'            => $mbox['EXT']['LIST']['MAILBOX_NAME'],
                                'ATTRIBUTES'         => $mbox['EXT']['LIST']['NAME_ATTRIBUTES'],
                                'HIERACHY_DELIMITER' => $mbox['EXT']['LIST']['HIERACHY_DELIMITER']
                            ];
                        } else {
                            $ret_aux[]= $mbox['EXT']['LIST']['MAILBOX_NAME'];
                        }
                    }
                }
            }
        }

        return $ret_aux;
    }

    /**
     * check if the mailbox name exists
     *
     * @param string $mailbox mailbox name to check existance
     *
     * @return boolean true on Success/false on Failure
     * @since 1.0
     */

    public function mailboxExist($mailbox)
    {
        // true means do an exact match
        if (PEAR::isError($ret = $this->getMailboxes($mailbox, true))) {
            return $ret;
        }
        if (count($ret) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Creates the mailbox $mailbox
     *
     * @param string $mailbox mailbox name to create
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function createMailbox($mailbox)
    {
        $ret=$this->cmdCreate($mailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * Deletes the mailbox $mailbox
     *
     * @param string $mailbox mailbox name to delete
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function deleteMailbox($mailbox)
    {
        // TODO verificar que el mailbox se encuentra vacio y, sino borrar los mensajes antes~!!!!!!
        $ret=$this->cmdDelete($mailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * Renames the mailbox $mailbox
     *
     * @param $oldmailbox
     * @param $newmailbox
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function renameMailbox($oldmailbox, $newmailbox)
    {
        $ret=$this->cmdRename($oldmailbox, $newmailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /******************************************************************
     **                                                               **
     **           SUBSCRIPTION METHODS                                **
     **                                                               **
     ******************************************************************/

    /**
     * Subscribes to the selected mailbox
     *
     * @param string $mailbox mailbox name to subscribe
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function subscribeMailbox($mailbox = null)
    {
        if (null == $mailbox) {
            $mailbox = $this->getCurrentMailbox();
        }
        $ret=$this->cmdSubscribe($mailbox);
        if ('OK' != strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * Removes the subscription to a mailbox
     *
     * @param string $mailbox mailbox name to unsubscribe
     *
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */
    public function unsubscribeMailbox($mailbox = null)
    {
        if (null == $mailbox) {
            $mailbox = $this->getCurrentMailbox();
        }
        $ret=$this->cmdUnsubscribe($mailbox);
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * Lists the subscription to mailboxes
     *
     * @param string $reference
     * @param int    $restriction_search
     * @param bool   $returnAttributes
     * @return mixed true on Success/PearError on Failure
     * @since 1.0
     */

    public function listsubscribedMailboxes($reference = '', $restriction_search = 0, $returnAttributes = false)
    {
        if (is_bool($restriction_search)) {
            $restriction_search = (int) $restriction_search;
        }

        if (is_int($restriction_search)) {
            switch ($restriction_search) {
                case 0:
                    $mailbox = '*';
                    break;
                case 1:
                    $mailbox = $reference;
                    $reference = '%';
                    break;
                case 2:
                    $mailbox = '%';
                    break;
            }
        } else {
            if (is_string($restriction_search)) {
                $mailbox = $restriction_search;
            } else {
                return new PEAR_Error('UPS... you ');
            }
        }

        if (PEAR::isError($ret=$this->cmdLsub($reference, $mailbox))) {
            return $ret;
        }
        //$ret=$this->cmdLsub($mailbox_base, $mailbox_name);


        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        $ret_aux= [];
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {
                if (isset($mbox['EXT']['LSUB']['MAILBOX_NAME'])) {
                    if ($returnAttributes) {
                        $ret_aux[]= [
                            'MAILBOX' => $mbox['EXT']['LSUB']['MAILBOX_NAME'],
                            'ATTRIBUTES' => $mbox['EXT']['LSUB']['NAME_ATTRIBUTES'],
                            'HIERACHY_DELIMITER' => $mbox['EXT']['LSUB']['HIERACHY_DELIMITER']
                        ];
                    } else {
                        $ret_aux[]= $mbox['EXT']['LSUB']['MAILBOX_NAME'];
                    }
                }
            }
        }

        return $ret_aux;
    }

    /******************************************************************
     **                                                               **
     **           FLAGS METHODS                                       **
     **                                                               **
     ******************************************************************/

    /**
     * Lists the flags of the selected messages
     *
     * @param mixes $msg_id the message list
     *
     * @return mixed array on Success/PearError on Failure
     * @since 1.0
     */
    public function getFlags($msg_id = null)
    {
        // You can also provide an array of numbers to those emails
        if (null != $msg_id) {
            if (is_array($msg_id)) {
                $message_set=$this->_getSearchListFromArray($msg_id);
            } else {
                $message_set=$msg_id;
            }
        } else {
            $message_set= '1:*';
        }

        $ret=$this->cmdFetch($message_set, 'FLAGS');
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        $flags= [];
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $msg_flags) {
                if (isset($msg_flags['EXT']['FLAGS'])) {
                    $flags[]= $msg_flags['EXT']['FLAGS'];
                }
            }
        }

        return $flags;
    }

    /**
     * check the Seen flag
     *
     * @param mixes $message_nro the message to check
     *
     * @return mixed true or false if the flag is sert PearError on Failure
     * @since 1.0
     */
    public function isSeen($message_nro)
    {
        return $this->hasFlag($message_nro, "\\Seen");
    }

    /**
     * check the Answered flag
     *
     * @param mixes $message_nro the message to check
     *
     * @return mixed true or false if the flag is sert PearError on Failure
     * @since 1.0
     */
    public function isAnswered($message_nro)
    {
        return $this->hasFlag($message_nro, "\\Answered");
    }

    /**
     * check the flagged flag
     *
     * @param mixes $message_nro the message to check
     *
     * @return mixed true or false if the flag is sert PearError on Failure
     * @since 1.0
     */
    public function isFlagged($message_nro)
    {
        return $this->hasFlag($message_nro, "\\Flagged");
    }

    /**
     * check the Draft flag
     *
     * @param mixes $message_nro the message to check
     *
     * @return mixed true or false if the flag is sert PearError on Failure
     * @since 1.0
     */
    public function isDraft($message_nro)
    {
        return $this->hasFlag($message_nro, "\\Draft");
    }

    /**
     * check the Deleted flag
     *
     * @param mixes $message_nro the message to check
     *
     * @return mixed true or false if the flag is sert PearError on Failure
     * @since 1.0
     */
    public function isDeleted($message_nro)
    {
        return $this->hasFlag($message_nro, "\\Deleted");
    }

    public function hasFlag($message_nro, $flag)
    {
        if (PEAR::isError($resp = $this->getFlags($message_nro))) {
            return $resp;
        }
        if (isset($resp[0])) {
            if (is_array($resp[0])) {
                if (in_array($flag, $resp[0])) {
                    return true;
                }
            }
        }

        return false;
    }

    /******************************************************************
     **                                                               **
     **           MISC METHODS                                        **
     **                                                               **
     ******************************************************************/

    /*
     * expunge function. Sends the EXPUNGE command
     *
     *
     * @return bool Success/Failure
     */
    public function expunge()
    {
        $ret = $this->cmdExpunge();
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /*
     * search function. Sends the SEARCH command
     *
     *
     * @return bool Success/Failure
     */
    public function search($search_list, $uidSearch=false)
    {
        if ($uidSearch) {
            $ret = $this->cmdUidSearch($search_list);
        } else {
            $ret = $this->cmdSearch($search_list);
        }

        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return $ret['PARSED']['SEARCH']['SEARCH_LIST'];
    }

    /******************************************************************
     **                                                               **
     **           QUOTA METHODS                                       **
     **                                                               **
     ******************************************************************/

    /**
     * Returns STORAGE quota details
     * @param  string $mailbox_name Mailbox to get quota info.
     * @return assoc  array contaning the quota info  on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function getStorageQuota($mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdGetQuota($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            // if the error is that the user does not have quota set return  an array
            // and not pear error
            if ('QUOTA' === substr(strtoupper($ret['RESPONSE']['STR_CODE']), 0, 5)) {
                return ['USED' =>'NOT SET', 'QMAX' =>'NOT SET'];
            }

            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['EXT']['QUOTA']['STORAGE'])) {
            return $ret['PARSED']['EXT']['QUOTA']['STORAGE'];
        }

        return ['USED' =>'NOT SET', 'QMAX' =>'NOT SET'];
    }

    /**
     * Returns MESSAGES quota details
     * @param  string $mailbox_name Mailbox to get quota info.
     * @return assoc  array contaning the quota info  on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function getMessagesQuota($mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdGetQuota($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            // if the error is that the user does not have quota set return  an array
            // and not pear error
            if ('QUOTA' === substr(strtoupper($ret['RESPONSE']['STR_CODE']), 0, 5)) {
                return ['USED' =>'NOT SET', 'QMAX' =>'NOT SET'];
            }

            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['EXT']['QUOTA']['MESSAGES'])) {
            return $ret['PARSED']['EXT']['QUOTA']['MESSAGES'];
        }

        return ['USED' =>'NOT SET', 'QMAX' =>'NOT SET'];
    }

    /**
     * sets STORAGE quota details
     * @param  string $mailbox_name Mailbox to get quota info.
     * @param         $quota
     * @return true   on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function setStorageQuota($mailbox_name, $quota)
    {
        if (PEAR::isError($ret = $this->cmdSetQuota($mailbox_name, $quota))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * sets MESSAGES quota details
     * @param  string $mailbox_name Mailbox to get quota info.
     * @param         $quota
     * @return true   on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function setMessagesQuota($mailbox_name, $quota)
    {
        if (PEAR::isError($ret = $this->cmdSetQuota($mailbox_name, '', $quota))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /******************************************************************
     **                                                               **
     **           ACL METHODS                                         **
     **                                                               **
     ******************************************************************/

    /**
     * get the Access Control List details
     * @param  string $mailbox_name Mailbox to get ACL info.
     * @return string on success or PEAR_Error
     *
     * @access public
     * @since  1.0
     */
    public function getACL($mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }
        if (PEAR::isError($ret = $this->cmdGetACL($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }

        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['USERS'])) {
            return $ret['PARSED']['USERS'];
        } else {
            return false;
        }
    }

    /**
     * Set ACL on a mailbox
     *
     * @param  string $mailbox_name the mailbox
     * @param  string $user         user to set the ACL
     * @param  string $acl          ACL list
     * @return mixed  True on success, or PEAR_Error on false
     *
     * @access public
     * @since  1.0
     */
    public function setACL($mailbox_name, $user, $acl)
    {
        if (PEAR::isError($ret = $this->cmdSetACL($mailbox_name, $user, $acl))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * deletes the ACL on a mailbox
     *
     * @param  string $mailbox_name the mailbox
     * @param  string $user         user to set the ACL
     * @return mixed  True on success, or PEAR_Error on false
     *
     * @access public
     * @since  1.0
     */
    public function deleteACL($mailbox_name, $user)
    {
        if (PEAR::isError($ret = $this->cmdDeleteACL($mailbox_name, $user))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    /**
     * returns the rights that the user logged on has on the mailbox
     * this method can be used by any user, not only the administrator
     *
     * @param  string $mailbox_name the mailbox to query rights
     * @return mixed  string contailing the list of rights on success, or PEAR_Error on failure
     *
     * @access public
     * @since  1.0
     */
    public function getMyRights($mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdMyRights($mailbox_name))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['GRANTED'])) {
            return $ret['PARSED']['GRANTED'];
        }

        return new PEAR_Error('Bogus response from server!');
    }

    /**
     * returns an array containing the rights that a user logged on has on the mailbox
     * this method can be used by any user, not only the administrator
     *
     * @param         $user
     * @param  string $mailbox_name the mailbox to query rights
     * @return mixed  string contailing the list of rights on success, or PEAR_Error on failure
     *
     * @access public
     * @since  1.0
     */
    public function getACLRights($user, $mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdListRights($mailbox_name, $user))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        if (isset($ret['PARSED']['GRANTED'])) {
            return $ret['PARSED']['GRANTED'];
        }

        return new PEAR_Error('Bogus response from server!');
    }

    /******************************************************************
     **                                                               **
     **           ANNOTATEMORE METHODS                                **
     **                                                               **
     *****************************************************************
     * @param      $entry
     * @param      $values
     * @param null $mailbox_name
     * @return bool|\PEAR_Error
     */

    public function setAnnotation($entry, $values, $mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdSetAnnotation($mailbox_name, $entry, $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    public function deleteAnnotation($entry, $values, $mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }

        if (PEAR::isError($ret = $this->cmdDeleteAnnotation($mailbox_name, $entry, $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }

        return true;
    }

    public function getAnnotation($entries, $values, $mailbox_name = null)
    {
        if (null == $mailbox_name) {
            $mailbox_name = $this->getCurrentMailbox();
        }
        if (!is_array($entries)) {
            $entries = [$entries];
        }
        if (!is_array($values)) {
            $values = [$values];
        }

        if (PEAR::isError($ret = $this->cmdGetAnnotation($mailbox_name, $entries, $values))) {
            return new PEAR_Error($ret->getMessage());
        }
        if ('OK' !== strtoupper($ret['RESPONSE']['CODE'])) {
            return new PEAR_Error($ret['RESPONSE']['CODE'] . ', ' . $ret['RESPONSE']['STR_CODE']);
        }
        $ret_aux = [];
        if (isset($ret['PARSED'])) {
            foreach ($ret['PARSED'] as $mbox) {
                $rawvalues = $mbox['EXT']['ATTRIBUTES'];
                $values = [];
                for ($i = 0, $iMax = count($rawvalues); $i < $iMax; $i += 2) {
                    $values[$rawvalues[$i]] = $rawvalues[$i + 1];
                }
                $mbox['EXT']['ATTRIBUTES'] = $values;
                $ret_aux[] = $mbox['EXT'];
            }
        }
        if (1 == count($ret_aux) && $ret_aux[0]['MAILBOX'] == $mailbox_name) {
            if (1 == count($entries) && $ret_aux[0]['ENTRY'] == $entries[0]) {
                if (1 == count($ret_aux[0]['ATTRIBUTES']) && 1 == count($values)) {
                    $attrs = array_keys($ret_aux[0]['ATTRIBUTES']);
                    $vals = array_keys($values);
                    if ($attrs[0] == $vals[0]) {
                        return $ret_aux[0]['ATTRIBUTES'][$attrs[0]];
                    }
                }
            }
        }

        return $ret_aux;
    }

    /*
     *   Transform an array to a list to be used in the cmdFetch method
     *
     */
    public function _getSearchListFromArray($arr)
    {
        $txt=implode(',', $arr);

        return $txt;
    }

    /*****************************************************
     Net_POP3 Compatibility functions:

     Warning!!!
     Those functions could dissapear in the future

     *********************************************************/

    public function getSize()
    {
        return $this->getMailboxSize();
    }

    public function numMsg($mailbox = null)
    {
        return $this->getNumberOfMessages($mailbox);
    }

    /*
     * Returns the entire message with given message number.
     *
     * @param  $msg_id Message number
     * @return mixed   Either entire message or false on error
     */
    public function getMsg($msg_id)
    {
        $ret=$this->getMessages($msg_id, false);
        // false means that getMessages() must not use the msg number as array key
        if (isset($ret[0])) {
            return $ret[0];
        } else {
            return $ret;
        }
    }

    public function getListing($msg_id = null)
    {
        return $this->getMessagesList($msg_id);
    }

    public function deleteMsg($msg_id)
    {
        return $this->deleteMessages($msg_id);
    }
}
