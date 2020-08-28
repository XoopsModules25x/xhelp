<?php

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
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
$path = dirname(__DIR__, 3);
require_once $path . '/mainfile.php';
//require_once $path . '/include/cp_functions.php';
require_once $path . '/include/cp_header.php';

global $xoopsUser;

/**
 * Xhelp\Ticket class
 *
 * Information about an individual ticket
 *
 * <code>
 * $ticketHandler = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
 * $ticket = $ticketHandler->get(1);
 * $ticket_id = $ticket->getVar('id');
 * $responses = $ticket->getResponses();
 * echo $ticket->lastUpdated();
 * </code>
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class Ticket extends \XoopsObject
{
    /**
     * Xhelp\Ticket constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, null, false);                      // will store Xoops user id
        $this->initVar('subject', \XOBJ_DTYPE_TXTBOX, null, true, 100);
        $this->initVar('description', \XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('department', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('priority', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('status', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('lastUpdated', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('posted', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('ownership', \XOBJ_DTYPE_INT, null, false);                // will store Xoops user id
        $this->initVar('closedBy', \XOBJ_DTYPE_INT, null, false);                 // will store Xoops user id
        $this->initVar('totalTimeSpent', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('userIP', \XOBJ_DTYPE_TXTBOX, null, false, 25);
        $this->initVar('elapsed', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('lastUpdate', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('emailHash', \XOBJ_DTYPE_TXTBOX, '', true, 100);
        $this->initVar('email', \XOBJ_DTYPE_TXTBOX, '', true, 100);
        $this->initVar('serverid', \XOBJ_DTYPE_INT, null, false);                 //will store email server this was picked up from
        $this->initVar('overdueTime', \XOBJ_DTYPE_INT, null, false);

        if (null !== $id) {
            if (\is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * retrieve the department object associated with this ticket
     *
     * @return object {@link Xhelp\Department} object
     * @access  public
     */
    public function getDepartment()
    {
        $hDept = new Xhelp\DepartmentHandler($GLOBALS['xoopsDB']);

        return $hDept->get($this->getVar('department'));
    }

    /**
     * create an md5 hash based on the ID and emailaddress. Use this as a lookup key when trying to find a ticket.
     *
     * @param text $email
     * @access public
     */
    public function createEmailHash($email)
    {
        if ('' == $this->getVar('posted')) {
            $this->setVar('posted', \time());
        }
        $hash = $this->getVar('posted') . '-' . $email;
        $hash = \md5($hash);

        $this->setVar('email', $email);
        $this->setVar('emailHash', $hash);
    }

    /**
     * retrieve all emails attached to this ticket object
     * @param bool $activeOnly
     * @return array of <a href='psi_element://Xhelp\TicketEmail'>Xhelp\TicketEmail</a> objects
     * objects
     * @access public
     */
    public function getEmails($activeOnly = false)
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }

        $hEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
        $crit    = new \CriteriaCompo(new \Criteria('ticketid', $id));
        if ($activeOnly) {
            $crit->add(new \Criteria('suppress', 0));
        }
        $arr = $hEmails->getObjects($crit);

        return $arr;
    }

    /**
     * retrieve all files attached to this ticket object
     *
     * @return array of {@link Xhelp\File} objects
     * @access  public
     */
    public function getFiles()
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }

        $hFiles = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
        $crit   = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $crit->setSort('responseid');
        $arr = $hFiles->getObjects($crit);

        return $arr;
    }

    /**
     * retrieve all responses attached to this ticket object
     *
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://Xhelp\Responses'>Xhelp\Responses</a> objects
     * objects
     * @access  public
     */
    public function getResponses($limit = 0, $start = 0)
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        $hResponses = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $criteria   = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('updateTime');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $arr = $hResponses->getObjects($criteria);

        return $arr;
    }

    /**
     * Retrieve number of responses for this ticket object
     * @return int Number of Responses
     */
    public function getResponseCount()
    {
        $hResponses = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $crit       = new \Criteria('ticketid', $this->getVar('id'));

        return $hResponses->getCount($crit);
    }

    /**
     *  Get all reviews for the current ticket
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://Xhelp\StaffReview'>Xhelp\StaffReview</a>
     */
    public function getReviews($limit = 0, $start = 0)
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        $hStaffReview = new Xhelp\StaffReviewHandler($GLOBALS['xoopsDB']);
        $crit         = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $crit->setSort('responseid');
        $crit->setOrder('DESC');
        $crit->setLimit($limit);
        $crit->setStart($start);

        $arr = $hStaffReview->getObjects($crit);

        return $arr;
    }

    /**
     * retrieve all log messages attached to this ticket object
     *
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://Xhelp\LogMessages'>Xhelp\LogMessages</a> objects
     * objects
     * @access  public
     */
    public function getLogs($limit = 0, $start = 0)
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        $hLogMessages = new Xhelp\LogMessageHandler($GLOBALS['xoopsDB']);
        $criteria     = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('lastUpdated');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $arr = $hLogMessages->getObjects($criteria);

        return $arr;
    }

    /**
     * @param      $post_field
     * @param null $response
     * @param null $allowed_mimetypes
     * @return array|bool|string|\XoopsObject
     */
    public function storeUpload($post_field, $response = null, $allowed_mimetypes = null)
    {
        global $xoopsUser, $xoopsDB, $xoopsModule;
        // require_once XHELP_CLASS_PATH . '/uploader.php';

        $config = Xhelp\Utility::getModuleConfig();

        $ticketid = $this->getVar('id');

        if (null === $allowed_mimetypes) {
            $hMime             = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
            $allowed_mimetypes = $hMime->checkMimeTypes();
            if (!$allowed_mimetypes) {
                return false;
            }
        }

        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        if (!\is_dir(XHELP_UPLOAD_PATH)) {
            if (!\mkdir($concurrentDirectory = XHELP_UPLOAD_PATH, 0757) && !\is_dir($concurrentDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $uploader = new Xhelp\MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($post_field)) {
            if (null === $response) {
                $uploader->setTargetFileName($ticketid . '_' . $uploader->getMediaName());
            } else {
                if ($response > 0) {
                    $uploader->setTargetFileName($ticketid . '_' . $response . '_' . $uploader->getMediaName());
                } else {
                    $uploader->setTargetFileName($ticketid . '_' . $uploader->getMediaName());
                }
            }
            if ($uploader->upload()) {
                $hFile = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
                $file  = $hFile->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (null !== $response ? (int)$response : 0));

                if ($hFile->insert($file)) {
                    return $file;
                }

                return $uploader->getErrors();
            }

            return $uploader->getErrors();
        }
    }

    /**
     * @param $post_field
     * @param $allowed_mimetypes
     * @param $errors
     * @return bool
     */
    public function checkUpload($post_field, &$allowed_mimetypes, &$errors)
    {
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config = Xhelp\Utility::getModuleConfig();

        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        $errors        = [];

        if (null === $allowed_mimetypes) {
            $hMime             = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
            $allowed_mimetypes = $hMime->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                $errors[] = _XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new Xhelp\MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

        if ($uploader->fetchMedia($post_field)) {
            return true;
        }

        $errors = \array_merge($errors, $uploader->getErrors(false));

        return false;
    }

    /**
     * determine last time the ticket was updated relative to the current user
     *
     * @param string $format
     * @return int Timestamp of last update
     * @access  public
     */
    public function lastUpdated($format = 'l')
    {
        return \formatTimestamp($this->getVar('lastUpdated'), $format);
    }

    /**
     * @param string $format
     * @return string
     */
    public function posted($format = 'l')
    {
        return \formatTimestamp($this->getVar('posted'), $format);
    }

    /**
     * return a simplified measurement of elapsed ticket time
     *
     * @return string Elapsed time
     * @access public
     */
    public function elapsed()
    {
        $tmp = Xhelp\Utility::getElapsedTime($this->getVar('elapsed'));

        return $this->_prettyElapsed($tmp);
    }

    /**
     * @return string
     */
    public function lastUpdate()
    {
        $tmp = Xhelp\Utility::getElapsedTime($this->getVar('lastUpdate'));

        return $this->_prettyElapsed($tmp);
    }

    /**
     * @param $time
     * @return string
     */
    public function _prettyElapsed($time)
    {
        $useSingle = false;

        foreach ($time as $unit => $value) {
            if ($value) {
                if (1 == $value) {
                    $useSingle = true;
                }
                switch ($unit) {
                    case 'years':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_YEAR : _XHELP_TIME_YEARS);
                        break;
                    case 'weeks':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_WEEK : _XHELP_TIME_WEEKS);
                        break;
                    case 'days':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_DAY : _XHELP_TIME_DAYS);
                        break;
                    case 'hours':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_HOUR : _XHELP_TIME_HOURS);
                        break;
                    case 'minutes':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_MIN : _XHELP_TIME_MINS);
                        break;
                    case 'seconds':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_SEC : _XHELP_TIME_SECS);
                        break;
                    default:
                        $unit_dsc = $unit;
                        break;
                }

                return "$value $unit_dsc";
            }
        }
    }

    /**
     * Determine if ticket is overdue
     *
     * @return bool
     * @access public
     */
    public function isOverdue()
    {
        $config  = Xhelp\Utility::getModuleConfig();
        $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
        if (isset($config['xhelp_overdueTime'])) {
            $overdueTime = $config['xhelp_overdueTime'];

            if ($overdueTime) {
                $status = $hStatus->get($this->getVar('status'));
                if (1 == $status->getVar('state')) {
                    if (\time() > $this->getVar('overdueTime')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param     $email
     * @param     $uid
     * @param int $suppress
     * @return bool
     */
    public function addSubmitter($email, $uid, $suppress = 0)
    {
        $uid = (int)$uid;

        if ('' != $email) {
            $hTicketEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
            $tEmail        = $hTicketEmails->create();

            $tEmail->setVar('ticketid', $this->getVar('id'));
            $tEmail->setVar('email', $email);
            $tEmail->setVar('uid', $uid);
            $tEmail->setVar('suppress', $suppress);

            if ($hTicketEmails->insert($tEmail)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $ticket2_id
     * @return bool|mixed
     */
    public function merge($ticket2_id)
    {
        global $xoopsDB;
        $ticket2_id = (int)$ticket2_id;

        // Retrieve $ticket2
        $ticketHandler = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
        $mergeTicket   = $ticketHandler->get($ticket2_id);

        // Figure out which ticket is older
        if ($this->getVar('posted') < $mergeTicket->getVar('posted')) {   // If this ticket is older than the 2nd ticket
            $keepTicket = $this;
            $loseTicket = $mergeTicket;
        } else {
            $keepTicket = $mergeTicket;
            $loseTicket = $this;
        }

        $keep_id = $keepTicket->getVar('id');
        $lose_id = $loseTicket->getVar('id');

        // Copy ticket subject and description of 2nd ticket as response to $this ticket
        $responseid = $keepTicket->addResponse($loseTicket->getVar('uid'), $keep_id, $loseTicket->getVar('subject', 'e') . ' - ' . $loseTicket->getVar('description', 'e'), $loseTicket->getVar('posted'), $loseTicket->getVar('userIP'));

        // Copy 2nd ticket file attachments to $this ticket
        $hFiles = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
        $crit   = new \Criteria('ticketid', $lose_id);
        $files  = $hFiles->getObjects($crit);
        foreach ($files as $file) {
            $file->rename($keep_id, $responseid);
        }
        $success = $hFiles->updateAll('ticketid', $keep_id, $crit);

        // Copy 2nd ticket responses as responses to $this ticket
        $hResponses = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $crit       = new \Criteria('ticketid', $lose_id);
        $success    = $hResponses->updateAll('ticketid', $keep_id, $crit);

        // Change file responseid to match the response added to merged ticket
        $crit = new \CriteriaCompo(new \Criteria('ticketid', $lose_id));
        $crit->add(new \Criteria('responseid', 0));
        $success = $hFiles->updateAll('responseid', $responseid, $crit);

        // Add 2nd ticket submitter to $this ticket via ticketEmails table
        $hTicketEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
        $crit          = new \Criteria('ticketid', $lose_id);
        $success       = $hTicketEmails->updateAll('ticketid', $keep_id, $crit);

        // Remove $loseTicket
        $crit = new \Criteria('id', $lose_id);
        if (!$ticketHandler->deleteAll($crit)) {
            return false;
        }

        return $keep_id;
    }

    /**
     * Check if the supplied user can add a response to the ticket
     * @param \XoopsUser $xoopsUser The user to check
     * @return bool
     */
    public function canAddResponse($xoopsUser)
    {
        //1. If the $xoopsUser a valid XoopsUser Object
        if (!$xoopsUser instanceof \XoopsUser) {
            return false;
        }

        //2. Is the user one of the "ticket submitters"
        $hTicketEmails = new Xhelp\TicketEmailsHandler($GLOBALS['xoopsDB']);
        $crit          = new \CriteriaCompo(new \Criteria('ticketid', $this->getVar('id')));
        $crit->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $count = $hTicketEmails->getCount($crit);

        if ($count > 0) {
            return true;
        }

        //3. Is the user a staff member?
        global $xhelp_isStaff, $xhelp_staff;
        if ($xhelp_isStaff) {
            if ($xhelp_staff->checkRoleRights(\XHELP_SEC_RESPONSE_ADD, $this->getVar('department'))) {
                return true;
            }
        }

        //4. If neither option is true, user cannot add response.
        return false;
    }

    /**
     * @param      $uid
     * @param      $ticketid
     * @param      $message
     * @param      $updateTime
     * @param      $userIP
     * @param int  $private
     * @param int  $timeSpent
     * @param bool $ret_obj
     * @return bool|mixed|\XoopsObject
     */
    public function addResponse(
        $uid,
        $ticketid,
        $message,
        $updateTime,
        $userIP,
        $private = 0,
        $timeSpent = 0,
        $ret_obj = false
    ) {
        $uid        = (int)$uid;
        $ticketid   = (int)$ticketid;
        $updateTime = (int)$updateTime;
        $private    = (int)$private;
        $timeSpent  = (int)$timeSpent;

        $hResponse   = new Xhelp\ResponsesHandler($GLOBALS['xoopsDB']);
        $newResponse = $hResponse->create();
        $newResponse->setVar('uid', $uid);
        $newResponse->setVar('ticketid', $ticketid);
        $newResponse->setVar('message', $message);
        $newResponse->setVar('timeSpent', $timeSpent);
        $newResponse->setVar('updateTime', $updateTime);
        $newResponse->setVar('userIP', $userIP);
        $newResponse->setVar('private', $private);
        if ($hResponse->insert($newResponse)) {
            if ($ret_obj) {
                return $newResponse;
            }

            return $newResponse->getVar('id');
        }

        return false;
    }

    /**
     * @param bool $includeEmptyValues
     * @return array
     */
    public function &getCustFieldValues($includeEmptyValues = false)
    {
        $ticketid = $this->getVar('id');

        $hFields = new Xhelp\TicketFieldHandler($GLOBALS['xoopsDB']);
        $fields  = $hFields->getObjects(null);                  // Retrieve custom fields

        $hFieldValues = new Xhelp\TicketValuesHandler($GLOBALS['xoopsDB']);
        $values       = $hFieldValues->get($ticketid);               // Retrieve custom field values
        $aCustFields  = [];
        foreach ($fields as $field) {
            $fileid   = '';
            $filename = '';
            $value    = '';
            $key      = '';
            $hasValue = false;
            $_arr     = $field->toArray();

            if (false !== $values
                && '' != $values->getVar($field->getVar('fieldname'))) {     // If values for this field has something
                $fieldvalues = $field->getVar('fieldvalues');           // Set fieldvalues
                $value       = $key = $values->getVar($field->getVar('fieldname'));  // Value of current field

                if (\XHELP_CONTROL_YESNO == $field->getVar('controltype')) {
                    $value = ((1 == $value) ? _YES : _NO);
                }

                if (\XHELP_CONTROL_FILE == $field->getVar('controltype')) {
                    $file     = \explode('_', $value);
                    $fileid   = $file[0];
                    $filename = $file[1];
                }

                if (\is_array($fieldvalues)) {
                    foreach ($fieldvalues as $fkey => $fvalue) {
                        if ($fkey == $value) {
                            $value = $fvalue;
                            break;
                        }
                    }
                }

                $hasValue = true;
            }
            $_arr['value']    = $value;
            $_arr['fileid']   = $fileid;
            $_arr['filename'] = $filename;
            $_arr['key']      = $key;

            if ($includeEmptyValues || $hasValue) {
                $aCustFields[$field->getVar('fieldname')] = $_arr;
            }
        }

        return $aCustFields;
    }
}   // end of class
