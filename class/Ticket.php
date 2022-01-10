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
 * @author       XOOPS Development Team
 */

use function md5;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
$path = \dirname(__DIR__, 3);
require_once $path . '/mainfile.php';
//require_once $path . '/include/cp_functions.php';
//require_once $path . '/include/cp_header.php';

global $xoopsUser;

/**
 * Ticket class
 *
 * Information about an individual ticket
 *
 * <code>
 * $ticketHandler = $this->helper->getHandler('Ticket');
 * $ticket = $ticketHandler->get(1);
 * $ticket_id = $ticket->getVar('id');
 * $responses = $ticket->getResponses();
 * echo $ticket->lastUpdated();
 * </code>
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class Ticket extends \XoopsObject
{
    private $helper;

    /**
     * Ticket constructor.
     * @param int|array|null $id
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

        $this->helper = Helper::getInstance();

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
     * @return object {@link Department} object
     */
    public function getDepartment(): object
    {
        $departmentHandler = $this->helper->getHandler('Department');

        return $departmentHandler->get($this->getVar('department'));
    }

    /**
     * create an md5 hash based on the ID and emailaddress. Use this as a lookup key when trying to find a ticket.
     *
     * @param string $email
     */
    public function createEmailHash(string $email)
    {
        if ('' === $this->getVar('posted')) {
            $this->setVar('posted', \time());
        }
        $hash = $this->getVar('posted') . '-' . $email;
        $hash = md5($hash);

        $this->setVar('email', $email);
        $this->setVar('emailHash', $hash);
    }

    /**
     * retrieve all emails attached to this ticket object
     * @param bool $activeOnly
     * @return array of <a href='psi_element://TicketEmail'>TicketEmail</a> objects
     * objects
     */
    public function getEmails(bool $activeOnly = false): array
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }

        $hEmails  = $this->helper->getHandler('TicketEmails');
        $criteria = new \CriteriaCompo(new \Criteria('ticketid', $id));
        if ($activeOnly) {
            $criteria->add(new \Criteria('suppress', 0));
        }
        $arr = $hEmails->getObjects($criteria);

        return $arr;
    }

    /**
     * retrieve all files attached to this ticket object
     *
     * @return array of {@link File} objects
     */
    public function getFiles(): array
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }

        $fileHandler = $this->helper->getHandler('File');
        $criteria    = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('responseid');
        $arr = $fileHandler->getObjects($criteria);

        return $arr;
    }

    /**
     * retrieve all responses attached to this ticket object
     *
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://Response'>Response</a> objects
     * objects
     */
    public function getResponses(int $limit = 0, int $start = 0): array
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        $responseHandler = $this->helper->getHandler('Response');
        $criteria        = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('updateTime');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $arr = $responseHandler->getObjects($criteria);

        return $arr;
    }

    /**
     * Retrieve number of responses for this ticket object
     * @return int Number of Responses
     */
    public function getResponseCount(): int
    {
        $responseHandler = $this->helper->getHandler('Response');
        $criteria        = new \Criteria('ticketid', $this->getVar('id'));

        return $responseHandler->getCount($criteria);
    }

    /**
     *  Get all reviews for the current ticket
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://StaffReview'>StaffReview</a>
     */
    public function getReviews(int $limit = 0, int $start = 0): array
    {
        $helper = Helper::getInstance();
        $arr    = [];
        $id     = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        /** @var \XoopsModules\Xhelp\StaffReviewHandler $staffReviewHandler */
        $staffReviewHandler = $helper->getHandler('StaffReview');
        $criteria           = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('responseid');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $arr = $staffReviewHandler->getObjects($criteria);

        return $arr;
    }

    /**
     * retrieve all log messages attached to this ticket object
     *
     * @param int $limit
     * @param int $start
     * @return array of <a href='psi_element://LogMessages'>LogMessages</a> objects
     * objects
     */
    public function getLogs(int $limit = 0, int $start = 0): array
    {
        $arr = [];
        $id  = (int)$this->getVar('id');
        if (!$id) {
            return $arr;
        }
        /** @var \XoopsModules\Xhelp\LogMessageHandler $this- >logmessageHandler */
        $logMessageHandler = $this->helper->getHandler('LogMessage');
        $criteria          = new \CriteriaCompo(new \Criteria('ticketid', $id));
        $criteria->setSort('lastUpdated');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);

        $arr = $logMessageHandler->getObjects($criteria);

        return $arr;
    }

    /**
     * @param string                                   $post_field
     * @param \XoopsModules\Xhelp\Response|string|null $response
     * @param array|string|null                        $allowed_mimetypes
     * @return array|false|string|void
     */
    public function storeUpload(string $post_field, $response = null, $allowed_mimetypes = null)
    {
        global $xoopsUser, $xoopsDB, $xoopsModule;
        // require_once XHELP_CLASS_PATH . '/uploader.php';

        $config = Utility::getModuleConfig();

        $ticketid = $this->getVar('id');

        if (null === $allowed_mimetypes) {
            $mimetypeHandler   = $this->helper->getHandler('Mimetype');
            $allowed_mimetypes = $mimetypeHandler->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                return false;
            }
        }

        $maxfilesize   = (int)$config['xhelp_uploadSize'];
        $maxfilewidth  = (int)$config['xhelp_uploadWidth'];
        $maxfileheight = (int)$config['xhelp_uploadHeight'];
        if (!\is_dir(XHELP_UPLOAD_PATH)) {
            if (!\mkdir($concurrentDirectory = XHELP_UPLOAD_PATH, 0757) && !\is_dir($concurrentDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $uploader = new MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
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
                $fileHandler = $this->helper->getHandler('File');
                $file        = $fileHandler->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (null !== $response ? (int)$response : 0));

                if ($fileHandler->insert($file)) {
                    return $file;
                }

                return $uploader->getErrors();
            }

            return $uploader->getErrors();
        }
    }

    /**
     * @param string     $post_field
     * @param array|null $allowed_mimetypes
     * @param array      $errors
     * @return bool
     */
    public function checkUpload(string $post_field, ?array &$allowed_mimetypes, array &$errors): bool
    {
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config = Utility::getModuleConfig();

        $maxfilesize   = (int)$config['xhelp_uploadSize'];
        $maxfilewidth  = (int)$config['xhelp_uploadWidth'];
        $maxfileheight = (int)$config['xhelp_uploadHeight'];
        $errors        = [];

        if (null === $allowed_mimetypes) {
            $mimetypeHandler   = $this->helper->getHandler('Mimetype');
            $allowed_mimetypes = $mimetypeHandler->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                $errors[] = \_XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

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
     */
    public function lastUpdated(string $format = 'l'): int
    {
        return (int)\formatTimestamp($this->getVar('lastUpdated'), $format);
    }

    /**
     * @param string $format
     * @return string
     */
    public function posted(string $format = 'l'): string
    {
        return \formatTimestamp($this->getVar('posted'), $format);
    }

    /**
     * return a simplified measurement of elapsed ticket time
     *
     * @return string Elapsed time
     */
    public function elapsed(): string
    {
        $tmp = Utility::getElapsedTime($this->getVar('elapsed'));

        return $this->prettyElapsed($tmp);
    }

    /**
     * @return string
     */
    public function lastUpdate(): string
    {
        $tmp = Utility::getElapsedTime($this->getVar('lastUpdate'));

        return $this->prettyElapsed($tmp);
    }

    /**
     * @param array $time
     * @return string
     */
    private function prettyElapsed(array $time): ?string
    {
        $useSingle = false;

        foreach ($time as $unit => $value) {
            if ($value) {
                if (1 == $value) {
                    $useSingle = true;
                }
                switch ($unit) {
                    case 'years':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_YEAR : \_XHELP_TIME_YEARS);
                        break;
                    case 'weeks':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_WEEK : \_XHELP_TIME_WEEKS);
                        break;
                    case 'days':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_DAY : \_XHELP_TIME_DAYS);
                        break;
                    case 'hours':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_HOUR : \_XHELP_TIME_HOURS);
                        break;
                    case 'minutes':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_MIN : \_XHELP_TIME_MINS);
                        break;
                    case 'seconds':
                        $unit_dsc = ($useSingle ? \_XHELP_TIME_SEC : \_XHELP_TIME_SECS);
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
     */
    public function isOverdue(): bool
    {
        $config        = Utility::getModuleConfig();
        $statusHandler = $this->helper->getHandler('Status');
        if (isset($config['xhelp_overdueTime'])) {
            $overdueTime = $config['xhelp_overdueTime'];

            if ($overdueTime) {
                $status = $statusHandler->get($this->getVar('status'));
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
     * @param string $email
     * @param int    $uid
     * @param int    $suppress
     * @return bool
     */
    public function addSubmitter(string $email, int $uid, int $suppress = 0): bool
    {
        $uid = $uid;

        if ('' != $email) {
            $ticketEmailsHandler = $this->helper->getHandler('TicketEmails');
            $tEmail              = $ticketEmailsHandler->create();

            $tEmail->setVar('ticketid', $this->getVar('id'));
            $tEmail->setVar('email', $email);
            $tEmail->setVar('uid', $uid);
            $tEmail->setVar('suppress', $suppress);

            if ($ticketEmailsHandler->insert($tEmail)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $ticket2_id
     * @return bool|mixed
     */
    public function merge(int $ticket2_id)
    {
        global $xoopsDB;
        $ticket2_id = $ticket2_id;

        // Retrieve $ticket2
        $ticketHandler = $this->helper->getHandler('Ticket');
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
        $fileHandler = $this->helper->getHandler('File');
        $criteria    = new \Criteria('ticketid', $lose_id);
        $files       = $fileHandler->getObjects($criteria);
        foreach ($files as $file) {
            $file->rename($keep_id, $responseid);
        }
        $success = $fileHandler->updateAll('ticketid', $keep_id, $criteria);

        // Copy 2nd ticket responses as responses to $this ticket
        $responseHandler = $this->helper->getHandler('Response');
        $criteria        = new \Criteria('ticketid', $lose_id);
        $success         = $responseHandler->updateAll('ticketid', $keep_id, $criteria);

        // Change file responseid to match the response added to merged ticket
        $criteria = new \CriteriaCompo(new \Criteria('ticketid', $lose_id));
        $criteria->add(new \Criteria('responseid', 0));
        $success = $fileHandler->updateAll('responseid', $responseid, $criteria);

        // Add 2nd ticket submitter to $this ticket via ticketEmails table
        $ticketEmailsHandler = $this->helper->getHandler('TicketEmails');
        $criteria            = new \Criteria('ticketid', $lose_id);
        $success             = $ticketEmailsHandler->updateAll('ticketid', $keep_id, $criteria);

        // Remove $loseTicket
        $criteria = new \Criteria('id', $lose_id);
        if (!$ticketHandler->deleteAll($criteria)) {
            return false;
        }

        return $keep_id;
    }

    /**
     * Check if the supplied user can add a response to the ticket
     * @param \XoopsUser $xoopsUser The user to check
     * @return bool
     */
    public function canAddResponse(\XoopsUser $xoopsUser): bool
    {
        //1. If the $xoopsUser a valid \XoopsUser Object
        if (!$xoopsUser instanceof \XoopsUser) {
            return false;
        }

        //2. Is the user one of the "ticket submitters"
        $ticketEmailsHandler = $this->helper->getHandler('TicketEmails');
        $criteria            = new \CriteriaCompo(new \Criteria('ticketid', $this->getVar('id')));
        $criteria->add(new \Criteria('uid', $xoopsUser->getVar('uid')));
        $count = $ticketEmailsHandler->getCount($criteria);

        if ($count > 0) {
            return true;
        }

        //3. Is the user a staff member?
        global $xhelp_isStaff, $staff;
        if ($xhelp_isStaff) {
            if ($staff->checkRoleRights(\XHELP_SEC_RESPONSE_ADD, $this->getVar('department'))) {
                return true;
            }
        }

        //4. If neither option is true, user cannot add response.
        return false;
    }

    /**
     * @param int    $uid
     * @param int    $ticketid
     * @param string $message
     * @param int    $updateTime
     * @param string $userIP
     * @param int    $private
     * @param int    $timeSpent
     * @param bool   $ret_obj
     * @return bool|mixed|\XoopsObject
     */
    public function addResponse(
        int $uid, int $ticketid, string $message, int $updateTime, string $userIP, int $private = 0, int $timeSpent = 0, bool $ret_obj = false
    ) {
        $uid        = $uid;
        $ticketid   = $ticketid;
        $updateTime = $updateTime;
        $private    = $private;
        $timeSpent  = $timeSpent;

        $responseHandler = $this->helper->getHandler('Response');
        $newResponse     = $responseHandler->create();
        $newResponse->setVar('uid', $uid);
        $newResponse->setVar('ticketid', $ticketid);
        $newResponse->setVar('message', $message);
        $newResponse->setVar('timeSpent', $timeSpent);
        $newResponse->setVar('updateTime', $updateTime);
        $newResponse->setVar('userIP', $userIP);
        $newResponse->setVar('private', $private);
        if ($responseHandler->insert($newResponse)) {
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
    public function &getCustFieldValues(bool $includeEmptyValues = false): array
    {
        $ticketid = $this->getVar('id');

        /** @var \XoopsModules\Xhelp\TicketFieldHandler $ticketFieldHandler */
        $ticketFieldHandler = $this->helper->getHandler('TicketField');
        $fields             = $ticketFieldHandler->getObjects(null);                  // Retrieve custom fields

        $ticketValuesHandler = $this->helper->getHandler('TicketValues');
        $values              = $ticketValuesHandler->get($ticketid);               // Retrieve custom field values
        $aCustFields         = [];
        foreach ($fields as $field) {
            $fileid   = '';
            $filename = '';
            $value    = '';
            $key      = '';
            $hasValue = false;
            $_arr     = $field->toArray();

            if (false !== $values
                && '' != $values->getVar($field->getVar('fieldname'))) {             // If values for this field has something
                $fieldvalues = $field->getVar('fieldvalues');                        // Set fieldvalues
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
