<?php
//$Id: ticket.php,v 1.80 2006/01/25 16:35:29 ackbarr Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}
require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');

/**
 * xhelpTicket class
 *
 * Information about an individual ticket
 *
 * <code>
 * $hTicket =& xhelpGetHandler('ticket');
 * $ticket =& $hTicket->get(1);
 * $ticket_id = $ticket->getVar('id');
 * $responses =& $ticket->getResponses();
 * echo $ticket->lastUpdated();
 * </code>
 *
 * @author Eric Juden <ericj@epcusa.com>
 * @access public
 * @package xhelp
 */

class xhelpTicket extends XoopsObject {
    function xhelpTicket($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);                      // will store Xoops user id
        $this->initVar('subject', XOBJ_DTYPE_TXTBOX, null, true, 100);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('department', XOBJ_DTYPE_INT, null, false);
        $this->initVar('priority', XOBJ_DTYPE_INT, null, false);
        $this->initVar('status', XOBJ_DTYPE_INT, null, false);
        $this->initVar('lastUpdated', XOBJ_DTYPE_INT, null, false);
        $this->initVar('posted', XOBJ_DTYPE_INT, null, false);
        $this->initVar('ownership', XOBJ_DTYPE_INT, null, false);                // will store Xoops user id
        $this->initVar('closedBy', XOBJ_DTYPE_INT, null, false);                 // will store Xoops user id
        $this->initVar('totalTimeSpent', XOBJ_DTYPE_INT, null, false);
        $this->initVar('userIP', XOBJ_DTYPE_TXTBOX, null, false, 25);
        $this->initVar('elapsed', XOBJ_DTYPE_INT, null, false);
        $this->initVar('lastUpdate', XOBJ_DTYPE_INT, null, false);
        $this->initVar('emailHash',XOBJ_DTYPE_TXTBOX, '', true, 100);
        $this->initVar('email',XOBJ_DTYPE_TXTBOX, '', true, 100);
        $this->initVar('serverid',XOBJ_DTYPE_INT, null, false);                 //will store email server this was picked up from
        $this->initVar('overdueTime', XOBJ_DTYPE_INT, null, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * retrieve the department object associated with this ticket
     *
     * @return object {@link xhelpDepartment} object
     * @access	public
     */

    function getDepartment()
    {
        $hDept =& xhelpGetHandler('department');

        return $hDept->get($this->getVar('department'));
    }

    /**
     * create an md5 hash based on the ID and emailaddress. Use this as a lookup key when trying to find a ticket.
     *
     * @param  text $email
     * @return none
     * @access public
     */
    function createEmailHash($email){
        if ($this->getVar('posted')==''){
            $this->setVar('posted',time());
        }
        $hash = $this->getVar('posted').'-'.$email;
        $hash = md5($hash);
        //
        $this->setVar('email',$email);
        $this->setVar('emailHash',$hash);
    }

    /**
     * retrieve all emails attached to this ticket object
     * @return array of {@link xhelpTicketEmail} objects
     * @access public
     */
    function getEmails($activeOnly = false)
    {
        $arr = array();
        $id = intval($this->getVar('id'));
        if (!$id) {
            return $arr;
        }

        $hEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $id));
        if ($activeOnly) {
            $crit->add(new Criteria('suppress', 0));
        }
        $arr = $hEmails->getObjects($crit);

        return $arr;
    }

    /**
     * retrieve all files attached to this ticket object
     *
     * @return array of {@link xhelpFile} objects
     * @access	public
     */
    function getFiles()
    {
        $arr = array();
        $id = intval($this->getVar('id'));
        if (!$id) {
            return $arr;
        }

        $hFiles =& xhelpGetHandler('file');
        $crit   = new CriteriaCompo(new Criteria('ticketid', $id));
        $crit->setSort('responseid');
        $arr = $hFiles->getObjects($crit);

        return $arr;
    }

    /**
     * retrieve all responses attached to this ticket object
     *
     * @return array of {@link xhelpResponses} objects
     * @access	public
     */
    function getResponses($limit = 0, $start = 0)
    {
        $arr = array();
        $id = intval($this->getVar('id'));
        if (!$id) {
            return $arr;
        }
        $hResponses =& xhelpGetHandler('responses');
        $criteria   = new CriteriaCompo(new Criteria('ticketid', $id));
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
    function getResponseCount()
    {
        $hResponses =& xhelpGetHandler('responses');
        $crit = new Criteria('ticketid', $this->getVar('id'));

        return $hResponses->getCount($crit);
    }

    /**
     *  Get all reviews for the current ticket
     *  @return array of {@link xhelpStaffReview}
     */
    function getReviews($limit = 0, $start = 0)
    {
        $arr = array();
        $id = intval($this->getVar('id'));
        if(!$id){
            return $arr;
        }
        $hStaffReview =& xhelpGetHandler('staffReview');
        $crit = new CriteriaCompo(new Criteria('ticketid', $id));
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
     * @return array of {@link xhelpLogMessages} objects
     * @access	public
     */
    function getLogs($limit = 0, $start = 0)
    {
        $arr = array();
        $id = intval($this->getVar('id'));
        if(!$id) {
            return $arr;
        }
        $hLogMessages =& xhelpGetHandler('logMessage');
        $criteria     = new CriteriaCompo(new Criteria('ticketid', $id));
        $criteria->setSort('lastUpdated');
        $criteria->setOrder('DESC');
        $criteria->setLimit($limit);
        $criteria->setStart($start);
         
        $arr    = $hLogMessages->getObjects($criteria);

        return $arr;
    }

    function storeUpload($post_field, $response = null, $allowed_mimetypes = null)
    {
        global $xoopsUser, $xoopsDB, $xoopsModule;
        include_once (XHELP_CLASS_PATH.'/uploader.php');

        $config =& xhelpGetModuleConfig();

        $ticketid = $this->getVar('id');

        if(!isset($allowed_mimetypes)){
            $hMime =& xhelpGetHandler('mimetype');
            $allowed_mimetypes = $hMime->checkMimeTypes();
            if(!$allowed_mimetypes){
                return false;
            }
        }

        $maxfilesize = $config['xhelp_uploadSize'];
        $maxfilewidth = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        if(!is_dir(XHELP_UPLOAD_PATH)){
            mkdir(XHELP_UPLOAD_PATH, 0757);
        }

        $uploader = new XoopsMediaUploader(XHELP_UPLOAD_PATH.'/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($post_field)) {
            if (!isset($response)) {
                $uploader->setTargetFileName($ticketid."_". $uploader->getMediaName());
            } else {
                if($response > 0){
                    $uploader->setTargetFileName($ticketid."_".$response."_".$uploader->getMediaName());
                } else {
                    $uploader->setTargetFileName($ticketid."_". $uploader->getMediaName());
                }
            }
            if ($uploader->upload()) {
                $hFile =& xhelpGetHandler('file');
                $file =& $hFile->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (isset($response) ? intval($response) : 0));

                if($hFile->insert($file)){
                    return $file;
                } else {
                    return $uploader->getErrors();
                }
                 
            } else {
                return $uploader->getErrors();
            }
        }
    }

    function checkUpload($post_field, &$allowed_mimetypes, &$errors)
    {
         
        include_once (XHELP_CLASS_PATH.'/uploader.php');
        $config =& xhelpGetModuleConfig();
         
         
        $maxfilesize = $config['xhelp_uploadSize'];
        $maxfilewidth = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        $errors = array();

        if(!isset($allowed_mimetypes)){
            $hMime =& xhelpGetHandler('mimetype');
            $allowed_mimetypes = $hMime->checkMimeTypes($post_field);
            if(!$allowed_mimetypes){
                $errors[] = _XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new XoopsMediaUploader(XHELP_UPLOAD_PATH.'/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

        if ($uploader->fetchMedia($post_field)) {
            return true;
        } else {
            $errors = array_merge($errors, $uploader->getErrors(false));

            return false;
        }
    }

    /**
     * determine last time the ticket was updated relative to the current user
     *
     * @return int Timestamp of last update
     * @access	public
     */
    function lastUpdated($format="l")
    {
        return formatTimestamp($this->getVar('lastUpdated'), $format);
    }

    function posted($format="l")
    {
        return formatTimestamp($this->getVar('posted'), $format);
    }

    /**
     * return a simplified measurement of elapsed ticket time
     *
     * @return string Elapsed time
     * @access public
     */
    function elapsed()
    {
        $tmp = xhelpGetElapsedTime($this->getVar('elapsed'));

        return $this->_prettyElapsed($tmp);
    }

    function lastUpdate()
    {
        $tmp = xhelpGetElapsedTime($this->getVar('lastUpdate'));

        return $this->_prettyElapsed($tmp);
    }

    function _prettyElapsed($time)
    {
        $useSingle = false;

        foreach ($time as $unit=>$value) {
            if ($value) {
                if ($value == 1) {
                    $useSingle = true;
                }
                switch($unit) {
                    case 'years':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_YEAR :_XHELP_TIME_YEARS);
                        break;
                    case 'weeks':
                        $unit_dsc = ($useSingle ? _XHELP_TIME_WEEK :_XHELP_TIME_WEEKS);
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
     * @return boolean
     * @access public
     */
    function isOverdue()
    {
        $config =& xhelpGetModuleConfig();
        $hStatus =& xhelpGetHandler('status');
        if (isset($config['xhelp_overdueTime'])) {
            $overdueTime = $config['xhelp_overdueTime'];

            if ($overdueTime) {
                $status =& $hStatus->get($this->getVar('status'));
                if ($status->getVar('state') == 1) {
                    if (time() > $this->getVar('overdueTime')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    function addSubmitter($email, $uid, $suppress = 0)
    {
        $uid = intval($uid);

        if($email != ''){
            $hTicketEmails =& xhelpGetHandler('ticketEmails');
            $tEmail =& $hTicketEmails->create();

            $tEmail->setVar('ticketid', $this->getVar('id'));
            $tEmail->setVar('email', $email);
            $tEmail->setVar('uid', $uid);
            $tEmail->setVar('suppress', $suppress);

            if($hTicketEmails->insert($tEmail)){
                return true;
            }
        }

        return false;
    }

    function merge($ticket2_id)
    {
        global $xoopsDB;
        $ticket2_id = intval($ticket2_id);

        // Retrieve $ticket2
        $hTicket =& xhelpGetHandler('ticket');
        $mergeTicket =& $hTicket->get($ticket2_id);

        // Figure out which ticket is older
        if($this->getVar('posted') < $mergeTicket->getVar('posted')){   // If this ticket is older than the 2nd ticket
            $keepTicket =& $this;
            $loseTicket =& $mergeTicket;
        } else {
            $keepTicket =& $mergeTicket;
            $loseTicket =& $this;
        }

        $keep_id = $keepTicket->getVar('id');
        $lose_id = $loseTicket->getVar('id');

        // Copy ticket subject and description of 2nd ticket as response to $this ticket
        $responseid = $keepTicket->addResponse($loseTicket->getVar('uid'), $keep_id, $loseTicket->getVar('subject', 'e')." - ".$loseTicket->getVar('description', 'e'),
        $loseTicket->getVar('posted'), $loseTicket->getVar('userIP'));

        // Copy 2nd ticket file attachments to $this ticket
        $hFiles =& xhelpGetHandler('file');
        $crit = new Criteria('ticketid', $lose_id);
        $files = $hFiles->getObjects($crit);
        foreach($files as $file){
            $file->rename($keep_id, $responseid);
        }
        $success = $hFiles->updateAll('ticketid', $keep_id, $crit);

        // Copy 2nd ticket responses as responses to $this ticket
        $hResponses =& xhelpGetHandler('responses');
        $crit = new Criteria('ticketid', $lose_id);
        $success = $hResponses->updateAll('ticketid', $keep_id, $crit);

        // Change file responseid to match the response added to merged ticket
        $crit = new CriteriaCompo(new Criteria('ticketid', $lose_id));
        $crit->add(new Criteria('responseid', 0));
        $success = $hFiles->updateAll('responseid', $responseid, $crit);

        // Add 2nd ticket submitter to $this ticket via ticketEmails table
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new Criteria('ticketid', $lose_id);
        $success = $hTicketEmails->updateAll('ticketid', $keep_id, $crit);

        // Remove $loseTicket
        $crit = new Criteria('id', $lose_id);
        if(!$hTicket->deleteAll($crit)){
            return false;
        }

        return $keep_id;
    }

    /**
     * Check if the supplied user can add a response to the ticket
     * @param $xoopsUser XoopsUser The user to check
     * @return boolean
     */
    function canAddResponse(&$xoopsUser)
    {
        //1. If the $xoopsUser a valid XoopsUser Object
        if (!is_a($xoopsUser, 'XoopsUser')) {
            return false;
        }

        //2. Is the user one of the "ticket submitters"
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new CriteriaCompo(new Criteria('ticketid', $this->getVar('id')));
        $crit->add(new Criteria('uid', $xoopsUser->getVar('uid')));
        $count = $hTicketEmails->getCount($crit);

        if ($count > 0) {
            return true;
        }

        //3. Is the user a staff member?
        global $xhelp_isStaff, $xhelp_staff;
        if ($xhelp_isStaff) {
            if ($xhelp_staff->checkRoleRights(XHELP_SEC_RESPONSE_ADD, $this->getVar('department'))) {
                return true;
            }
        }

        //4. If neither option is true, user cannot add response.
        return false;
    }

    function &addResponse($uid, $ticketid, $message, $updateTime, $userIP, $private = 0, $timeSpent = 0, $ret_obj = false)
    {
        $uid = intval($uid);
        $ticketid = intval($ticketid);
        $updateTime = intval($updateTime);
        $private = intval($private);
        $timeSpent = intval($timeSpent);

        $hResponse =& xhelpGetHandler('responses');
        $newResponse =& $hResponse->create();
        $newResponse->setVar('uid', $uid);
        $newResponse->setVar('ticketid', $ticketid);
        $newResponse->setVar('message', $message);
        $newResponse->setVar('timeSpent', $timeSpent);
        $newResponse->setVar('updateTime', $updateTime);
        $newResponse->setVar('userIP', $userIP);
        $newResponse->setVar('private', $private);
        if($hResponse->insert($newResponse)){
            if($ret_obj){
                return $newResponse;
            }

            return $newResponse->getVar('id');
        }

        return false;
    }

    function &getCustFieldValues($includeEmptyValues = false)
    {
        $ticketid = $this->getVar('id');

        $hFields = xhelpGetHandler('ticketField');
        $fields = $hFields->getObjects(null);                  // Retrieve custom fields

        $hFieldValues = xhelpGetHandler('ticketValues');
        $values =& $hFieldValues->get($ticketid);               // Retrieve custom field values
        $aCustFields = array();
        foreach($fields as $field){
            $fileid = '';
            $filename = '';
            $value = '';
            $key = '';
            $hasValue = false;
            $_arr = $field->toArray();

            if($values != false && $values->getVar($field->getVar('fieldname')) != ""){     // If values for this field has something
                $fieldvalues = $field->getVar('fieldvalues');           // Set fieldvalues
                $value = $key = $values->getVar($field->getVar('fieldname'));  // Value of current field

                if ($field->getVar('controltype') == XHELP_CONTROL_YESNO) {

                    $value = (($value == 1) ? _YES : _NO);
                }

                if($field->getVar('controltype') == XHELP_CONTROL_FILE){
                    $file = split("_", $value);
                    $fileid = $file[0];
                    $filename = $file[1];
                }

                if(is_array($fieldvalues)){
                    foreach($fieldvalues as $fkey=>$fvalue){
                        if($fkey == $value){
                            $value = $fvalue;
                            break;
                        }
                    }
                }

                $hasValue = true;
            }
            $_arr['value'] = $value;
            $_arr['fileid'] = $fileid;
            $_arr['filename'] = $filename;
            $_arr['key'] = $key;

            if($includeEmptyValues || $hasValue) {
                $aCustFields[$field->getVar('fieldname')] = $_arr;
            }
        }

        return $aCustFields;
    }

}   // end of class

class xhelpTicketHandler extends xhelpBaseObjectHandler{
    /**
     * Name of child class
     *
     * @var string
     * @access	private
     */
    var $classname = 'xhelpticket';

    /**
     * DB Table Name
     *
     * @var string
     * @access 	private
     */
    var $_dbtable = 'xhelp_tickets';

    /**
     * Constructor
     *
     * @param object $db reference to a xoopsDB object
     */
    function xhelpTicketHandler(&$db)
    {
        parent::init($db);
    }

    /**
     * retrieve an object from the database, based on. use in child classes
     * @param  int   $id ID
     * @return mixed object if id exists, false if not
     * @access public
     */
    function &get($id)
    {
        $id = intval($id);
        if($id > 0) {
            $sql = $this->_selectQuery(new Criteria('id', $id, '=', 't'));
            if(!$result = $this->_db->query($sql)) {
                return false;
            }
            $numrows = $this->_db->getRowsNum($result);
            if($numrows == 1) {
                $obj = new $this->classname($this->_db->fetchArray($result));

                return $obj;
            }
        }

        return false;
    }

    /**
     * find a ticket based on a hash
     *
     * @param  text   $hash
     * @return ticket object
     * @access public
     */
    function getTicketByHash($hash) {
        $sql = $this->_selectQuery(new Criteria('emailHash', $hash, '=', 't'));
        if(!$result = $this->_db->query($sql)) {
            return false;
        }
        $numrows = $this->_db->getRowsNum($result);
        if($numrows == 1) {
            $obj = new $this->classname($this->_db->fetchArray($result));

            return $obj;
        }
    }
     
    /**
     * Retrieve the list of departments for the specified tickets
     * @param  mixed $tickets can be a single value or array consisting of either ticketids or ticket objects
     * @return array array of integers representing the ids of each department
     * @access public
     */
    function getTicketDepartments($tickets)
    {
        $a_tickets = array();
        $a_depts = array();
        if (is_array($tickets)) {
            foreach ($tickets as $ticket) {
                if (is_object($ticket)) {
                    $a_tickets[] = $ticket->getVar('id');
                } else {
                    $a_tickets[] = intval($ticket);
                }
            }
        } else {
            if (is_object($tickets)) {
                $a_tickets[] = $tickets->getVar('id');
            } else {
                $a_tickets[] = intval($tickets);
            }
        }

        $sql = sprintf('SELECT DISTINCT department FROM %s WHERE id IN (%s)', $this->_db->prefix('xhelp_tickets'), implode($a_tickets, ','));
        $ret = $this->_db->query($sql);

        while ($temp = $this->_db->fetchArray($ret)) {
            $a_depts[] = $temp['department'];
        }

        return $a_depts;
    }

    function &getObjectsByStaff($crit, $id_as_key = false, $hasCustFields = false)
    {
        $sql = $this->_selectQuery($crit, true, $hasCustFields);
        if (is_object($crit)) {
            $limit = $crit->getLimit();
            $start = $crit->getStart();
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = array();
        while ($temp = $this->_db->fetchArray($ret)) {
            $tickets = $this->create();
            $tickets->assignVars($temp);
            if ($id_as_key) {
                $arr[$tickets->getVar('id')] = $tickets;
            } else {
                $arr[] = $tickets;
            }
            unset($tickets);
        }

        return $arr;
    }

    function &getMyUnresolvedTickets($uid, $id_as_key = false)
    {
        $uid = intval($uid);

        // Get all ticketEmail objects where $uid is found
        $hTicketEmails =& xhelpGetHandler('ticketEmails');
        $crit = new Criteria('uid', $uid);
        $ticketEmails = $hTicketEmails->getObjectsSortedByTicket($crit);

        // Get friendly array of all ticketids needed
        $aTicketEmails = array();
        foreach($ticketEmails as $ticketEmail){
            $aTicketEmails[$ticketEmail->getVar('ticketid')] = $ticketEmail->getVar('ticketid');
        }
        unset($ticketEmails);

        // Get unresolved statuses and filter out the resolved statuses
        $hStatus =& xhelpGetHandler('status');
        $crit = new Criteria('state', 1);
        $statuses = $hStatus->getObjects($crit, true);
        $aStatuses = array();
        foreach($statuses as $status){
            $aStatuses[$status->getVar('id')] = $status->getVar('id');
        }
        unset($statuses);

        // Get array of tickets.
        // Only want tickets that are unresolved.
        $crit  = new CriteriaCompo(new Criteria('t.id', "(". implode(array_keys($aTicketEmails), ',') .")", 'IN'));
        $crit->add(new Criteria('t.status', "(". implode(array_keys($aStatuses), ',') .")", 'IN'));
        $tickets = $this->getObjects($crit, $id_as_key);

        // Return all tickets
        return $tickets;
    }

    function getObjectsByState($state, $id_as_key = false)
    {
        $crit = new Criteria('state', intval($state), '=', 's');
        $sql = $this->_selectQuery($crit, true);
        if (is_object($crit)) {
            $limit = $crit->getLimit();
            $start = $crit->getStart();
        }

        $ret = $this->_db->query($sql, $limit, $start);
        $arr = array();
        while ($temp = $this->_db->fetchArray($ret)) {
            $tickets = $this->create();
            $tickets->assignVars($temp);
            if ($id_as_key) {
                $arr[$tickets->getVar('id')] = $tickets;
            } else {
                $arr[] = $tickets;
            }
            unset($tickets);
        }

        return $arr;
    }

    function getCountByStaff($criteria, $hasCustFields = false)
    {
        if(!$hasCustFields){
            $sql = sprintf("SELECT COUNT(*) as TicketCount FROM %s t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id", $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'));
        } else {
            $sql = sprintf("SELECT COUNT(*) as TicketCount FROM %s t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid ", $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'), $this->_db->prefix('xhelp_ticket_values'));
        }

        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }

        if (!$result =& $this->_db->query($sql)) {
            return 0;
        }
        list($count) = $this->_db->fetchRow($result);

        return $count;
    }

    /**
     * Get all tickets a staff member is in dept
     * @param  int    $uid   staff user id
     * @param  int    $mode  One of the '_QRY_STAFF_{X}' constants
     * @param  int    $start first record to return
     * @param  int    $limit number of records to return
     * @param  string $sort  Sort Field
     * @param  string $order Sort Order
     * @return array  array of {@link xhelpTicket}> objects
     * @access public
     * @todo Filter by Department, Status
     */
    function getStaffTickets($uid, $mode = -1, $start = 0, $limit = 0, $sort='', $order='')
    {
        $uid = intval($uid);
        $arr = array();
        $crit = new CriteriaCompo();
        $crit->setLimit(intval($limit));
        $crit->setStart(intval($start));
        switch($mode){
            case XHELP_QRY_STAFF_HIGHPRIORITY:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('state', 1, '=', 's'));
                $crit->add(new Criteria('ownership', 0, '=', 't'));
                $crit->setSort('t.priority, t.posted');
                break;

            case XHELP_QRY_STAFF_NEW:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('ownership', 0, '=', 't'));
                $crit->add(new Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                $crit->setOrder('DESC');
                break;

            case XHELP_QRY_STAFF_MINE:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('ownership', $uid, '=', 't'));
                $crit->add(new Criteria('state', 1, '=', 's'));
                $crit->setSort('t.posted');
                break;

            case XHELP_QRY_STAFF_ALL:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                break;

            default:
                return $arr;
                break;
        }

        return $this->getObjectsByStaff($crit);
    }

    /**
     * Get number of tickets based on staff membership
     * @param  int $uid  staff user id
     * @param  int $mode
     * @return int Number of tickets
     * @access public
     * @todo Filter by Department, Status
     */
    function getStaffTicketCount($uid, $mode = -1)
    {
        $crit = new CriteriaCompo();
        switch($mode){
            case XHELP_QRY_STAFF_HIGHPRIORITY:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('status', 2, '<', 't'));
                $crit->add(new Criteria('ownership', 0, '=', 't'));
                //$crit->add($crit2);
                $crit->setSort('t.priority, t.posted');
                break;

            case XHELP_QRY_STAFF_NEW:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('ownership', 0, '=', 't'));
                $crit->add(new Criteria('status', 2, '<', 't'));
                $crit->setSort('t.posted');
                $crit->setOrder('DESC');
                break;

            case XHELP_QRY_STAFF_MINE:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                $crit->add(new Criteria('ownership', $uid, '=', 't'));
                $crit->add(new Criteria('status', 2, '<', 't'));
                $crit->setSort('t.posted');
                break;

            case XHELP_QRY_STAFF_ALL:
                $crit->add(new Criteria('uid', $uid, '=', 'j'));
                break;

            default:
                return 0;
                break;
        }

        return $this->getCountByStaff($crit);
    }
    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, uid, subject, description, department, priority, status, lastUpdated, ownership, closedBy, totalTimeSpent, posted, userIP, emailHash, email, serverid, overdueTime)
            VALUES (%u, %u, %s, %s, %u, %u, %u, %u, %u, %u, %u, %u, %s, %s, %s, %u, %u)", $this->_db->prefix($this->_dbtable), $id,
        $uid, $this->_db->quoteString($subject), $this->_db->quoteString($description), $department, $priority,
        $status, time(), $ownership, $closedBy, $totalTimeSpent, $posted, $this->_db->quoteString($userIP),$this->_db->quoteString($emailHash),$this->_db->quoteString($email), $serverid, $overdueTime);

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET subject = %s, description = %s, department = %u, priority = %u, status = %u, lastUpdated = %u, ownership = %u,
            closedBy = %u, totalTimeSpent = %u, userIP = %s, emailHash = %s, email = %s, serverid = %u, overdueTime = %u WHERE id = %u", $this->_db->prefix($this->_dbtable),
        $this->_db->quoteString($subject), $this->_db->quoteString($description), $department, $priority,
        $status, time(), $ownership, $closedBy, $totalTimeSpent, $this->_db->quoteString($userIP),$this->_db->quoteString($emailHash),$this->_db->quoteString($email), $serverid, $overdueTime, $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }
     
    /**
     * Create a "select" SQL query
     * @param  object $criteria {@link CriteriaElement} to match
     * @return string SQL query
     * @access	private
     */
    function _selectQuery($criteria = null, $join = false, $hasCustFields = false)
    {
        global $xoopsUser;
        if(!$join){
            $sql = sprintf('SELECT t.*, (UNIX_TIMESTAMP() - t.posted) as elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
    		                as lastUpdate  FROM %s t INNER JOIN %s s ON t.status = s.id', $this->_db->prefix($this->_dbtable),
            $this->_db->prefix('xhelp_status'));
        } else {
            if(!$hasCustFields){
                $sql = sprintf("SELECT t.*, (UNIX_TIMESTAMP() - t.posted) as elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
    	                        as lastUpdate FROM %s t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
    	                        ON t.status = s.id", $this->_db->prefix('xhelp_tickets'), $this->_db->prefix('xhelp_jstaffdept'),
                $this->_db->prefix('xhelp_status'));
            } else {
                $sql = sprintf("SELECT t.*, (UNIX_TIMESTAMP() - t.posted) as elapsed, (UNIX_TIMESTAMP() - t.lastUpdated)
    	                        as lastUpdate FROM %s t INNER JOIN %s j ON t.department = j.department INNER JOIN %s s
    	                        ON t.status = s.id INNER JOIN %s f ON t.id = f.ticketid", $this->_db->prefix('xhelp_tickets'),
                $this->_db->prefix('xhelp_jstaffdept'), $this->_db->prefix('xhelp_status'), $this->_db->prefix('xhelp_ticket_values'));
            }
        }
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
            if ($criteria->getSort() != '') {
                $sql .= ' ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
            }
        }
        $sql = str_replace(XHELP_GLOBAL_UID, $xoopsUser->getVar('uid'), $sql);

        return $sql;
    }

    /**
     * delete a ticket from the database
     *
     * @param  object $obj   reference to the {@link xhelpTicket} obj to delete
     * @param  bool   $force
     * @return bool   FALSE if failed.
     * @access	public
     */
    function delete(&$obj, $force = false)
    {
        if (strcasecmp($this->classname, get_class($obj)) != 0) {
            return false;
        }

        // Remove all ticket responses first
        $hResponses  =& xhelpGetHandler('responses');
        if (!$hResponses->deleteAll(new Criteria('ticketid', $obj->getVar('id')))) {
            return false;
        }
         
        // Remove all files associated with this ticket
        $hFiles =& xhelpGetHandler('file');
        if(!$hFiles->deleteAll(new Criteria('ticketid', $obj->getVar('id')))){
            return false;
        }
         
        // Remove custom field values for this ticket
        $hFieldValues =& xhelpGetHandler('ticketValues');
        if(!$hFieldValues->deleteAll(new Criteria('ticketid', $obj->getVar('id')))){
            return false;
        }
         
        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * increment a value to 1 field for tickets matching a set of conditions
     *
     * @param  object $criteria {@link CriteriaElement}
     * @return bool   FALSE if deletion failed
     * @access	public
     */
    function incrementAll($fieldname, $fieldvalue, $criteria = null)
    {
        $set_clause = is_numeric($fieldvalue) ? $fieldname.' = '. $fieldname .'+'.$fieldvalue : $fieldname.' = '.$fieldname .'+'.$this->_db->quoteString($fieldvalue);
        $sql = 'UPDATE '.$this->_db->prefix($this->_dbtable).' SET '.$set_clause;
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = $this->_db->query($sql)) {
            return false;
        }

        return true;
    }
}   // end of handler class
;
