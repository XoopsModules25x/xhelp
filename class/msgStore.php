<?php
//$Id: msgStore.php,v 1.13 2005/07/05 20:09:35 eric_juden Exp $


class xhelpEmailStore
{
    var $_hResponse;
    var $_hTicket;
    var $_hMailEvent;
    var $_errors;

    function xhelpEmailStore()
    {
        $this->_hResponse  =& xhelpGetHandler('responses');
        $this->_hTicket    =& xhelpGetHandler('ticket');
        $this->_hMailEvent =& xhelpGetHandler('mailEvent');
        $this->_errors     = array();
    }

    function _setError($desc)
    {
        if(is_array($desc)){
            foreach($desc as $d) {
                $this->_errors[] = $d;
            }
        }
        $this->_errors[] = $desc;
    }

    function _getErrors()
    {
        if(count($this->_errors) > 0){
            return $this->_errors;
        } else {
            return 0;
        }
    }

    function clearErrors()
    {
        $this->_errors = array();
    }

    function renderErrors()
    {

    }

    /**
     * Store the parsed message in database
     * @access public
     * @param  object $msg  {@link xhelpParsedMsg} object Message to add
     * @param  object $user {@link xoopsUser} object User that submitted message
     * @param  object $mbox {@link xhelpDepartmentMailBox} object. Originating Mailbox for message
     * @return mixed  Returns {@link xhelpTicket} object if new ticket, {@link xhelpResponses} object if a response, and false if unable to save.
     */
    function &storeMsg(&$msg, &$user, &$mbox, &$errors)
    {
        //Remove any previous error messages
        $this->clearErrors();

        $type = $msg->getMsgType();
        switch($type) {
            case _XHELP_MSGTYPE_TICKET:
                $obj =& $this->_hTicket->create();
                $obj->setVar('uid', $user->getVar('uid'));
                $obj->setVar('subject', $msg->getSubject());
                $obj->setVar('description', $msg->getMsg());
                $obj->setVar('department', $mbox->getVar('departmentid'));
                $obj->setVar('priority', $mbox->getVar('priority'));
                $obj->setVar('posted', time());
                $obj->setVar('serverid', $mbox->getVar('id'));
                $obj->setVar('userIP', 'via Email');
                $obj->setVar('email', $user->getVar('email'));
                if(!$status = xhelpGetMeta("default_status")){
                    xhelpSetMeta("default_status", "1");
                    $status = 1;
                }
                $obj->setVar('status', $status);
                $obj->createEmailHash($msg->getEmail());
                if ($this->_hTicket->insert($obj)) {
                    $obj->addSubmitter($user->getVar('email'), $user->getVar('uid'));
                    $this->_saveAttachments($msg, $obj->getVar('id'));

                    $errors = $this->_getErrors();

                    return array($obj);
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

                    return array($ticket, $obj);
                }
                break;

            default:
                //Sanity Check, should never get here

        }

        return false;
    }

    function _saveAttachments($msg, $ticketid, $responseid = 0)
    {
        global $xoopsModuleConfig;
        $attachments = $msg->getAttachments();
        $dir         = XOOPS_UPLOAD_PATH .'/xhelp';
        $prefix      = ($responseid != 0? $ticketid.'_'.$responseid.'_' : $ticketid.'_');
        $hMime       =& xhelpGetHandler('mimetype');
        $allowed_mimetypes = $hMime->getArray();

        if(!is_dir($dir)){
            mkdir($dir, 0757);
        }

        $dir .= '/';

        if($xoopsModuleConfig['xhelp_allowUpload']){
            $hFile =& xhelpGetHandler('file');
            foreach ($attachments as $attach) {
                $validators = array();

                //Create Temporary File
                $fname = $prefix.$attach['filename'];
                $fp = fopen($dir.$fname, 'w');
                fwrite($fp, $attach['content']);
                fclose($fp);

                $validators[] = new ValidateMimeType($dir.$fname, $attach['content-type'], $allowed_mimetypes);
                $validators[] = new ValidateFileSize($dir.$fname, $xoopsModuleConfig['xhelp_uploadSize']);
                $validators[] = new ValidateImageSize($dir.$fname, $xoopsModuleConfig['xhelp_uploadWidth'], $xoopsModuleConfig['xhelp_uploadHeight']);

                if (!xhelpCheckRules($validators, $errors)) {
                    //Remove the file
                    $this->_addAttachmentError($errors, $msg, $fname);
                    unlink($dir.$fname);
                } else {
                    //Add attachment to ticket

                    $file =& $hFile->create();
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

    function _addAttachmentError($errors, $msg, $fname)
    {
        if($errors <> 0){
            $aErrors = array();
            foreach($errors as $err){
                if(in_array($err, $aErrors)){
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
