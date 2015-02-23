<?php
//$Id: ticketSolution.php,v 1.2 2005/12/02 23:16:09 ackbarr Exp $
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
if (!defined('XHELP_CLASS_PATH')) {
    exit();
}
require_once(XHELP_CLASS_PATH.'/xhelpBaseObjectHandler.php');
require_once(XHELP_CLASS_PATH.'/xhelpNaiveBayesian.php');

/**
 * xhelpTicketSolution class
 *
 * Represents an individual ticket solution
 *
 * @author Brian Wahoff <brianw@epcusa.com>
 * @access public
 * @package xhelp
 */

class xhelpTicketSolution extends XoopsObject {
    function xhelpTicketSolution($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('url', XOBJ_DTYPE_TXTAREA, null, true, 4096);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 10000);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('posted', XOBJ_DTYPE_INT, null, true);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    function posted($format="l")
    {
        return formatTimestamp($this->getVar('posted'), $format);
    }
}   // end of class

class xhelpTicketSolutionHandler extends xhelpBaseObjectHandler{
    /**
     * Name of child class
     *
     * @var	string
     * @access	private
     */
    var $classname = 'xhelpticketsolution';

    /**
     * DB Table Name
     *
     * @var 		string
     * @access 	private
     */
    var $_dbtable = 'xhelp_ticket_solutions';

    /**
     * Constructor
     *
     * @param	object   $db    reference to a xoopsDB object
     */
    function xhelpTicketSolutionHandler(&$db)
    {
        parent::init($db);
    }

    function _insertQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("INSERT INTO %s (id, ticketid, url, title, description, uid, posted) VALUES (%u, %u, %s, %s, %s, %u, %u)",
        $this->_db->prefix($this->_dbtable), $id, $ticketid, $this->_db->quoteString($url),
        $this->_db->quoteString($title), $this->_db->quoteString($description), $uid, time());

        return $sql;

    }

    function _updateQuery(&$obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf("UPDATE %s SET ticketid = %u, url = %s, title = %s, description = %s, uid = %u, posted = %u WHERE id = %u",
        $this->_db->prefix($this->_dbtable), $ticketid, $this->_db->quoteString($url),
        $this->_db->quoteString($title), $this->_db->quoteString($description), $uid, $posted, $id);

        return $sql;
    }

    function _deleteQuery(&$obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));
        return $sql;
    }

    /**
     * Recommend solutions to a ticket based on similarity
     * to previous tickets and their solutions
     * @param xhelpTicket $ticket ticket to search for solutions
     * @return array Value 1 = bayesian likeness probability, Value 2 = xhelpTicketSolution object
     * @access public
     */
    function &recommendSolutions($ticket)
    {
        $ret = array();

        //1. Get list of bayesian categories(tickets) similar to current ticket
        $bayes = new xhelpNaiveBayesian(new xhelpNaiveBayesianStorage);
        $document = $ticket->getVar('subject') . "\r\n" . $ticket->getVar('description');
        $cats = $bayes->categorize($document);

        //2. Get solutions to those tickets
        $crit = new Criteria('ticketid', "(". implode(array_keys($cats), ',') .")", 'IN');
        $solutions =& $this->getObjects($crit);

        //3. Sort solutions based on likeness probability
        foreach ($solutions as $solution) {
            $ret[] = array( 'probability'=> $cats[$solution->getVar('ticketid')],
                            'solution' => $solution);
        }
        unset($solutions);
        return $this->multi_sort($ret, 'probability');
    }

    function addSolution($ticket, $solution)
    {
        //1. Store solution in db for current ticket
        if ($this->insert($solution)) {
            //2. Train Bayesian DB
            $bayes = new xhelpNaiveBayesian(new xhelpNaiveBayesianStorage);
            $documentid = (string) $ticket->getVar('id');
            $categoryid = (string) $ticket->getVar('id');
            $document   = $ticket->getVar('subject') . "\r\n" . $ticket->getVar('description');
            $bayes->train($documentid, $categoryid, $document);

            return true;
        }
        return false;
    }
     

    function &multi_sort($array, $akey)
    {
        function _compare($a, $b)
        {
            global $key;
            if ($a[$key]>$b[$key]) {
                $varcmp = "1";
            } elseif ($a[$key]<$b[$key]) {
                $varcmp = "-1";
            } elseif ($a[$key]==$b[$key]) {
                $varcmp = "0";
            }

            return $varcmp;
        }
        usort($array, "_compare");
        return $array;
    }
}
?>