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
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

/**
 * class EmailStore
 */
class EmailStore
{
    public $responseHandler;
    public $ticketHandler;
    public $mailEventHandler;
    public $_errors;

    /**
     * EmailStore constructor.
     */
    public function __construct()
    {
        $helper                = Helper::getInstance();
        $this->responseHandler = $helper->getHandler('Response');
        /** @var TicketHandler $this- >ticketHandler */
        $this->ticketHandler = $helper->getHandler('Ticket');
        /** @var MailEventHandler $this- >mailEventHandler */
        $this->mailEventHandler = $helper->getHandler('MailEvent');
        $this->_errors          = [];
    }

    /**
     * @param string|array $desc
     */
    public function _setError($desc)
    {
        if (\is_array($desc)) {
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
        if (\count($this->_errors) > 0) {
            return $this->_errors;
        }

        return 0;
    }

    /**
     *
     */
    public function clearErrors()
    {
        $this->_errors = [];
    }

    /**
     *
     */
    public function renderErrors()
    {
    }

    /**
     * Store the parsed message in database
     * @param ParsedMessage     $msg  {@link ParsedMessage} object Message to add
     * @param \XoopsUser        $user {@link xoopsUser} object User that submitted message
     * @param DepartmentMailBox $mbox {@link DepartmentMailBox} object. Originating Mailbox for message
     * @param mixed             $errors
     * @return array|false Returns <a href='psi_element://Ticket'>Ticket</a> object if new ticket, <a href='psi_element://Response'>Response</a> object if a response, and false if unable to save.
     */
    public function storeMsg(ParsedMessage $msg, \XoopsUser $user, DepartmentMailBox $mbox, &$errors)
    {
        //Remove any previous error messages
        $this->clearErrors();

        $type = $msg->getMsgType();
        switch ($type) {
            case _XHELP_MSGTYPE_TICKET:
                $obj = $this->ticketHandler->create();
                $obj->setVar('uid', $user->getVar('uid'));
                $obj->setVar('subject', $msg->getSubject());
                $obj->setVar('description', $msg->getMsg());
                $obj->setVar('department', $mbox->getVar('departmentid'));
                $obj->setVar('priority', $mbox->getVar('priority'));
                $obj->setVar('posted', \time());
                $obj->setVar('serverid', $mbox->getVar('id'));
                $obj->setVar('userIP', 'via Email');
                $obj->setVar('email', $user->getVar('email'));
                if (!$status = Utility::getMeta('default_status')) {
                    Utility::setMeta('default_status', '1');
                    $status = 1;
                }
                $obj->setVar('status', $status);
                $obj->createEmailHash($msg->getEmail());
                if ($this->ticketHandler->insert($obj)) {
                    $obj->addSubmitter($user->getVar('email'), $user->getVar('uid'));
                    $this->saveAttachments($msg, $obj->getVar('id'));

                    $errors = $this->_getErrors();

                    return [$obj];
                }
                break;
            case _XHELP_MSGTYPE_RESPONSE:
                if (!$ticket = $this->ticketHandler->getTicketByHash($msg->getHash())) {
                    $this->_setError(\_XHELP_RESPONSE_NO_TICKET);

                    return false;
                }

                if ($msg->getEmail() != $ticket->getVar('email')) {
                    $this->_setError(\sprintf(\_XHELP_MISMATCH_EMAIL, $msg->getEmail(), $ticket->getVar('email')));

                    return false;
                }

                $obj = $this->responseHandler->create();
                $obj->setVar('ticketid', $ticket->getVar('id'));
                $obj->setVar('uid', $user->getVar('uid'));
                $obj->setVar('message', $msg->getMsg());
                $obj->setVar('updateTime', \time());
                $obj->setVar('userIP', 'via Email');

                if ($this->responseHandler->insert($obj)) {
                    $this->saveAttachments($msg, $ticket->getVar('id'), $obj->getVar('id'));
                    $ticket->setVar('lastUpdated', \time());
                    $this->ticketHandler->insert($ticket);

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
     * @param ParsedMessage $msg
     * @param int           $ticketid
     * @param int           $responseid
     */
    public function saveAttachments(ParsedMessage $msg, int $ticketid, int $responseid = 0)
    {
        $helper = Helper::getInstance();

        $attachments = $msg->getAttachments();
        $dir         = XOOPS_UPLOAD_PATH . '/xhelp';
        $prefix      = (0 != $responseid ? $ticketid . '_' . $responseid . '_' : $ticketid . '_');
        /** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
        $mimetypeHandler   = $helper->getHandler('Mimetype');
        $allowed_mimetypes = $mimetypeHandler->getArray();

        if (!\is_dir($dir)) {
            if (!\mkdir($dir, 0757) && !\is_dir($dir)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $dir));
            }
        }

        $dir .= '/';

        if ($helper->getConfig('xhelp_allowUpload')) {
            /** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
            $fileHandler = $helper->getHandler('File');
            foreach ($attachments as $attach) {
                $validators = [];

                //Create Temporary File
                $fname = $prefix . $attach['filename'];
                $fp    = \fopen($dir . $fname, 'wb');
                \fwrite($fp, $attach['content']);
                \fclose($fp);

                $validators[] = new Validation\ValidateMimeType($dir . $fname, $attach['content-type'], $allowed_mimetypes);
                $validators[] = new Validation\ValidateFileSize($dir . $fname, $helper->getConfig('xhelp_uploadSize'));
                $validators[] = new Validation\ValidateImageSize($dir . $fname, $helper->getConfig('xhelp_uploadWidth'), $helper->getConfig('xhelp_uploadHeight'));

                if (Utility::checkRules($validators, $errors)) {
                    //Add attachment to ticket

                    /** @var \XoopsModules\Xhelp\File $file */
                    $file = $fileHandler->create();
                    $file->setVar('filename', $fname);
                    $file->setVar('ticketid', $ticketid);
                    $file->setVar('mimetype', $attach['content-type']);
                    $file->setVar('responseid', $responseid);
                    $fileHandler->insert($file, true);
                } else {
                    //Remove the file
                    $this->addAttachmentError($errors, $msg, $fname);
                    \unlink($dir . $fname);
                }
            }
        } else {
            $this->_setError(\_XHELP_MESSAGE_UPLOAD_ALLOWED_ERR);   // Error: file uploading is disabled
        }
    }

    /**
     * @param array         $errors
     * @param ParsedMessage $msg
     * @param string        $fname
     */
    public function addAttachmentError(array $errors, ParsedMessage $msg, string $fname)
    {
        if (0 != $errors) {
            $aErrors = [];
            foreach ($errors as $err) {
                if (\in_array($err, $aErrors)) {
                    continue;
                }
                $aErrors[] = $err;
            }
            $error = \implode(', ', $aErrors);
            $this->_setError(\sprintf(\_XHELP_MESSAGE_UPLOAD_ERR, $fname, $msg->getEmail(), $error));
        }
    }
}
