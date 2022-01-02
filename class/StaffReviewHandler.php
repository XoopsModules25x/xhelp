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

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * StaffReviewHandler class
 *
 * StaffReview Handler for StaffReview class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 */
class StaffReviewHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = StaffReview::class;
    /**
     * DB table name
     *
     * @var string
     */
    public $_dbtable = 'xhelp_staffreview';

    /**
     * Constructor
     *
     * @param \XoopsDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        parent::init($db);
    }

    /**
     * retrieve a StaffReview object meeting certain criteria
     * @param int $ticketid    ID of ticket
     * @param int $responseid  ID of response
     * @param int $submittedBy UID of ticket submitter
     * @return array|bool (@link StaffReview}
     */
    public function getReview($ticketid, $responseid, $submittedBy)
    {
        $ticketid    = (int)$ticketid;
        $responseid  = (int)$responseid;
        $submittedBy = (int)$submittedBy;

        $criteria = new \CriteriaCompo(new \Criteria('ticketid', $ticketid));
        $criteria->add(new \Criteria('submittedBy', $submittedBy));
        $criteria->add(new \Criteria('responseid', $responseid));
        $review = [];
        if (!$review = $this->getObjects($criteria)) {
            return false;
        }

        return $review;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'INSERT INTO `%s` (id, staffid, rating, ticketid, responseid, comments, submittedBy, userIP)
            VALUES (%u, %u, %u, %u, %u, %s, %u, %s)',
            $this->_db->prefix($this->_dbtable),
            $id,
            $staffid,
            $rating,
            $ticketid,
            $responseid,
            $this->_db->quoteString($comments),
            $submittedBy,
            $this->_db->quoteString($userIP)
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'UPDATE `%s` SET staffid = %u, rating = %u, ticketid = %u, responseid = %u, comments = %s, submittedBy = %u, userIP = %s
                WHERE id = %u',
            $this->_db->prefix($this->_dbtable),
            $staffid,
            $rating,
            $ticketid,
            $responseid,
            $this->_db->quoteString($comments),
            $submittedBy,
            $this->_db->quoteString($userIP),
            $id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $obj
     * @return string
     */
    public function deleteQuery($obj)
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }
}
