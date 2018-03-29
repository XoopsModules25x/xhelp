<?php namespace XoopsModules\Xhelp;

//

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;
/** @var Xhelp\Helper $helper */
$helper = Xhelp\Helper::getInstance();

/**
 * class EmailStore
 */
class EmailStore
{
    public $_hResponse;
    public $_hTicket;
    public $_hMailEvent;
    public $_errors;

    /**
     * Xhelp\EmailStore constructor.
     */
    public function __construct()
    {
        $this->_hResponse  = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $this->_hTicket    = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
        $this->_hMailEvent = new Xhelp\MailEventHandler($GLOBALS['xoopsDB']);
        $this->_errors     = [];
    }

    /**
     * @param $desc
     */
    public function _setError($desc)
    {
        if (is_array($desc)) {
            foreach ($desc as $d) {
                $this->_errors[] = $d;
            }
        }
        $this->_errors[] = $desc;
    }

    /**
     * @return array|int
     */
    public function _getErrors()
    {
        if (count($this->_errors) > 0) {
            return $this->_errors;
        } else {
            return 0;
        }
    }

    public function clearErrors()
    {
        $this->_errors = [];
    }

    public function renderErrors()
    {
    }

    /**
     * Store the parsed message in database
     * @access public
     * @param  object $msg  {@link xhelpParsedMsg} object Message to add
     * @param  object $user {@link xoopsUser} object User that submitted message
     * @param  object $mbox {@link Xhelp\DepartmentMailBox} object. Originating Mailbox for message
     * @param         $errors
     * @return mixed Returns <a href='psi_element://Xhelp\Ticket'>Xhelp\Ticket</a> object if new ticket, <a href='psi_element://Xhelp\Responses'>Xhelp\Responses</a> object if a response, and false if unable to save.
     */
    public function &storeMsg(&$msg, &$user, &$mbox, &$errors)
    {
        //Remove any previous error messages
        $this->clearErrors();

        $type = $msg->getMsgType();
        switch ($type) {
            case _XHELP_MSGTYPE_TICKET:
                $obj = $this->_hTicket->create();
                $obj->setVar('uid', $user->getVar('uid'));
                $obj->setVar('subject', $msg->getSubject());
                $obj->setVar('description', $msg->getMsg());
                $obj->setVar('department', $mbox->getVar('departmentid'));
                $obj->setVar('priority', $mbox->getVar('priority'));
                $obj->setVar('posted', time());
                $obj->setVar('serverid', $mbox->getVar('id'));
                $obj->setVar('userIP', 'via Email');
                $obj->setVar('email', $user->getVar('email'));
                if (!$status = Xhelp\Utility::getMeta('default_status')) {
                    Xhelp\Utility::setMeta('default_status', '1');
                    $status = 1;
                }
                $obj->setVar('status', $status);
                $obj->createEmailHash($msg->getEmail());
                if ($this->_hTicket->insert($obj)) {
                    $obj->addSubmitter($user->getVar('email'), $user->getVar('uid'));
                    $this->_saveAttachments($msg, $obj->getVar('id'));

                    $errors = $this->_getErrors();

                    return [$obj];
                }
                break;

            case _XHELP_MSGTYPE_RESPONSE:
                if (!$ticket = $this->_hTicket->getTicketByHash($msg->getHash())) {
                    $this->_setError(_XHELP_RESPONSE_NO_TICKET);

                    return false;
                }

                if ($msg->getEmail() != $ticket->getVar('email')) {
                    $this->_setError(sprintf(_XHELP_MISMATCH_EMAIL, $msg->getEmail(), $ticket->getVar('email')));

                    return false;
                }

                $obj = $this->_hResponse->create();
                $obj->setVar('ticketid', $ticket->getVar('id'));
                $obj->setVar('uid', $user->getVar('uid'));
                $obj->setVar('message', $msg->getMsg());
                $obj->setVar('updateTime', time());
                $obj->setVar('userIP', 'via Email');

                if ($this->_hResponse->insert($obj)) {
                    $this->_saveAttachments($msg, $ticket->getVar('id'), $obj->getVar('id'));
                    $ticket->setVar('lastUpdated', time());
                    $this->_hTicket->insert($ticket);

                    $errors = $this->_getErrors();

                    return [$ticket, $obj];
                }
                break;

            default:
                //Sanity Check, should never get here

        }

        return false;
    }

    /**
     * @param     $msg
     * @param     $ticketid
     * @param int $responseid
     */
    public function _saveAttachments($msg, $ticketid, $responseid = 0)
    {
        /** @var Xhelp\Helper $helper */
        $helper = Xhelp\Helper::getInstance();

        $attachments       = $msg->getAttachments();
        $dir               = XOOPS_UPLOAD_PATH . '/xhelp';
        $prefix            = (0 != $responseid ? $ticketid . '_' . $responseid . '_' : $ticketid . '_');
        $hMime             = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
        $allowed_mimetypes = $hMime->getArray();

        if (!is_dir($dir)) {
            mkdir($dir, 0757);
        }

        $dir .= '/';

        if ($helper->getConfig('xhelp_allowUpload')) {
            $hFile = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
            foreach ($attachments as $attach) {
                $validators = [];

                //Create Temporary File
                $fname = $prefix . $attach['filename'];
                $fp    = fopen($dir . $fname, 'wb');
                fwrite($fp, $attach['content']);
                fclose($fp);

                $validators[] = new validation\ValidateMimeType($dir . $fname, $attach['content-type'], $allowed_mimetypes);
                $validators[] = new validation\ValidateFileSize($dir . $fname, $helper->getConfig('xhelp_uploadSize'));
                $validators[] = new validation\ValidateImageSize($dir . $fname, $helper->getConfig('xhelp_uploadWidth'), $helper->getConfig('xhelp_uploadHeight'));

                if (!Xhelp\Utility::checkRules($validators, $errors)) {
                    //Remove the file
                    $this->_addAttachmentError($errors, $msg, $fname);
                    unlink($dir . $fname);
                } else {
                    //Add attachment to ticket

                    $file = $hFile->create();
                    $file->setVar('filename', $fname);
                    $file->setVar('ticketid', $ticketid);
                    $file->setVar('mimetype', $attach['content-type']);
                    $file->setVar('responseid', $responseid);
                    $hFile->insert($file, true);
                }
            }
        } else {
            $this->_setError(_XHELP_MESSAGE_UPLOAD_ALLOWED_ERR);   // Error: file uploading is disabled
        }
    }

    /**
     * @param $errors
     * @param $msg
     * @param $fname
     */
    public function _addAttachmentError($errors, $msg, $fname)
    {
        if (0 <> $errors) {
            $aErrors = [];
            foreach ($errors as $err) {
                if (in_array($err, $aErrors)) {
                    continue;
                } else {
                    $aErrors[] = $err;
                }
            }
            $error = implode($aErrors, ', ');
            $this->_setError(sprintf(_XHELP_MESSAGE_UPLOAD_ERR, $fname, $msg->getEmail(), $error));
        }
    }
}
