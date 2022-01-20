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
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}
// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';
// require_once XHELP_CLASS_PATH . '/NaiveBayesian.php';

/**
 * class TicketSolutionHandler
 */
class TicketSolutionHandler extends BaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     */
    public $classname = TicketSolution::class;
    /**
     * DB Table Name
     *
     * @var string
     */
    public $dbtable = 'xhelp_ticket_solutions';

    private const TABLE = 'xhelp_ticket_solutions';
    private const ENTITY = TicketSolution::class;
    private const ENTITYNAME = 'TicketSolution';
    private const KEYNAME = 'id';
    private const IDENTIFIER = 'ticketid';

    /**
     * Constructor
     *
     * @param \XoopsMySQLDatabase|null $db reference to a xoopsDB object
     */
    public function __construct(\XoopsMySQLDatabase $db = null)
    {
        $this->init($db);
        $this->helper = Helper::getInstance();
        parent::__construct($db, static::TABLE, static::ENTITY, static::KEYNAME, static::IDENTIFIER);
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
            'INSERT INTO `%s` (id, ticketid, url, title, description, uid, posted) VALUES (%u, %u, %s, %s, %s, %u, %u)',
            $this->db->prefix($this->dbtable),
            $id,
            $ticketid,
            $this->db->quoteString($url),
            $this->db->quoteString($title),
            $this->db->quoteString($description),
            $uid,
            \time()
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
            'UPDATE `%s` SET ticketid = %u, url = %s, title = %s, description = %s, uid = %u, posted = %u WHERE id = %u',
            $this->db->prefix($this->dbtable),
            $ticketid,
            $this->db->quoteString($url),
            $this->db->quoteString($title),
            $this->db->quoteString($description),
            $uid,
            $posted,
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

    /**
     * Recommend solutions to a ticket based on similarity
     * to previous tickets and their solutions
     * @param Ticket $ticket ticket to search for solutions
     * @return array       Value 1 = bayesian likeness probability, Value 2 = TicketSolution object
     */
    public function &recommendSolutions(Ticket $ticket): array
    {
        $ret = [];

        //1. Get list of bayesian categories(tickets) similar to current ticket
        $bayes    = new NaiveBayesian(new NaiveBayesianStorage());
        $document = $ticket->getVar('subject') . "\r\n" . $ticket->getVar('description');
        $cats     = $bayes->categorize($document);

        //2. Get solutions to those tickets
        $criteria  = new \Criteria('ticketid', '(' . \implode(',', \array_keys($cats)) . ')', 'IN');
        $solutions = $this->getObjects($criteria);

        //3. Sort solutions based on likeness probability
        foreach ($solutions as $solution) {
            $ret[] = [
                'probability' => $cats[$solution->getVar('ticketid')],
                'solution'    => $solution,
            ];
        }
        unset($solutions);

        return $this->multi_sort($ret, 'probability');
    }

    /**
     * @param Ticket                             $ticket
     * @param \XoopsModules\Xhelp\TicketSolution $solution
     * @return bool
     */
    public function addSolution(Ticket $ticket, TicketSolution $solution): bool
    {
        //1. Store solution in db for current ticket
        if ($this->insert($solution)) {
            //2. Train Bayesian DB
            $bayes      = new NaiveBayesian(new NaiveBayesianStorage());
            $documentid = (string)$ticket->getVar('id');
            $categoryid = (string)$ticket->getVar('id');
            $document   = $ticket->getVar('subject') . "\r\n" . $ticket->getVar('description');
            $bayes->train($documentid, $categoryid, $document);

            return true;
        }

        return false;
    }

    /**
     * @param array  $array
     * @param string $akey
     * @return array
     */
    public function &multi_sort(array $array, string $akey): array
    {
        /**
         * @param array $a
         * @param array $b
         * @return string
         */
        function _compare(array $a, array $b): string
        {
            global $key;
            if ($a[$key] > $b[$key]) {
                $varcmp = '1';
            } elseif ($a[$key] < $b[$key]) {
                $varcmp = '-1';
            } elseif ($a[$key] == $b[$key]) {
                $varcmp = '0';
            }

            return $varcmp;
        }

        \usort($array, '_compare');

        return $array;
    }
}
