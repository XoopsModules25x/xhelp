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
    public $dbtable = 'xhelp_staffreview';

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
    public function getReview(int $ticketid, int $responseid, int $submittedBy)
    {
        $ticketid    = $ticketid;
        $responseid  = $responseid;
        $submittedBy = $submittedBy;

        $criteria = new \CriteriaCompo(new \Criteria('ticketid', (string)$ticketid));
        $criteria->add(new \Criteria('submittedBy', (string)$submittedBy));
        $criteria->add(new \Criteria('responseid', (string)$responseid));
        $review = [];
        if (!$review = $this->getObjects($criteria)) {
            return false;
        }

        return $review;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function insertQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'INSERT INTO `%s` (id, staffid, rating, ticketid, responseid, comments, submittedBy, userIP)
            VALUES (%u, %u, %u, %u, %u, %s, %u, %s)',
            $this->db->prefix($this->dbtable),
            $id,
            $staffid,
            $rating,
            $ticketid,
            $responseid,
            $this->db->quoteString($comments),
            $submittedBy,
            $this->db->quoteString($userIP)
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function updateQuery(\XoopsObject $object): string
    {
        //TODO mb replace with individual variables
        // Copy all object vars into local variables
        foreach ($object->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = \sprintf(
            'UPDATE `%s` SET staffid = %u, rating = %u, ticketid = %u, responseid = %u, comments = %s, submittedBy = %u, userIP = %s
                WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $staffid,
            $rating,
            $ticketid,
            $responseid,
            $this->db->quoteString($comments),
            $submittedBy,
            $this->db->quoteString($userIP),
            $id
        );

        return $sql;
    }

    /**
     * @param \XoopsObject $object
     * @return string
     */
    public function deleteQuery(\XoopsObject $object): string
    {
        $sql = \sprintf('DELETE FROM `%s` WHERE id = %u', $this->db->prefix($this->dbtable), $object->getVar('id'));

        return $sql;
    }
}
