<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

/*
 ***** BEGIN LICENSE BLOCK *****
 This file is part of PHP Naive Bayesian Filter.

 The Initial Developer of the Original Code is
 Loic d'Anterroches [loic_at_xhtml.net].
 Portions created by the Initial Developer are Copyright (C) 2003
 the Initial Developer. All Rights Reserved.

 Contributor(s):
 See the source

 PHP Naive Bayesian Filter is free software; you can redistribute it
 and/or modify it under the terms of the GNU General Public License as
 published by the Free Software Foundation; either version 2 of
 the License, or (at your option) any later version.

 PHP Naive Bayesian Filter is distributed in the hope that it will
 be useful, but WITHOUT ANY WARRANTY; without even the implied
 warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Foobar; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

 Alternatively, the contents of this file may be used under the terms of
 the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 in which case the provisions of the LGPL are applicable instead
 of those above.

 ***** END LICENSE BLOCK *****
 */

/** Access to the storage of the data for the filter.
 *
 * To avoid dependency with respect to any database, this class handle all the
 * access to the data storage. You can provide your own class as long as
 * all the methods are available. The current one rely on a MySQL database.
 *
 * methods:
 * - array getCategories()
 * - bool  wordExists(string $word)
 * - array getWord(string $word, string $categoryid)
 */
class NaiveBayesianStorage
{
    public $db;
    public $myts;

    /**
     * NaiveBayesianStorage constructor.
     */
    public function __construct()
    {
        $this->db   = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->myts = \MyTextSanitizer::getInstance();
    }

    /** get the list of categories with basic data.
     *
     * @return array key = category ids, values = array(keys = 'probability', 'word_count')
     */
    public function getCategories(): array
    {
        $categories = [];

        $ret = $this->db->query('SELECT * FROM ' . $this->db->prefix('xhelp_bayes_categories'));

        while (false !== ($arr = $this->db->fetchRow($ret))) {
            $categories[$arr['category_id']] = [
                'probability' => $arr['probability'],
                'word_count'  => $arr['word_count'],
            ];
        }

        return $categories;
    }

    /** see if the word is an already learnt word.
     * @param mixed $word
     * @return bool
     */
    public function wordExists($word): bool
    {
        $criteria = new \Criteria('word', $word);

        $ret = $this->db->query('SELECT COUNT(*) AS WordCount FROM ' . $this->db->prefix('xhelp_bayes_wordfreqs') . $criteria->renderWhere());

        if (!$ret) {
            return false;
        }

        $arr = $this->db->fetchRow($ret);

        return $arr['WordCount'] > 0;
    }

    /** get details of a word in a category.
     * @param mixed $word
     * @param mixed $category_id
     * @return array ('count' => count)
     */
    public function getWord($word, $category_id): array
    {
        $details  = [];
        $criteria = new \CriteriaCompo(new \Criteria('word', $word));
        $criteria->add(new \Criteria('category_id', $category_id));

        $ret = $this->db->query('SELECT count FROM ' . $this->db->prefix('xhelp_bayes_wordfreqs') . $criteria->renderWhere());

        if ($ret) {
            $details = $this->db->fetchRow($ret);
        } else {
            $details['count'] = 0;
        }

        return $details;
    }

    /** update a word in a category.
     * If the word is new in this category it is added, else only the count is updated.
     *
     *
     * @param string $word
     * @param int    $count
     * @param string $category_id
     * @return bool success
     * @internal param word $string
     * @internal param count $int
     * @paran    string category id
     */
    public function updateWord(string $word, int $count, string $category_id): bool
    {
        $oldword = $this->getWord($word, $category_id);
        if (0 == $oldword['count']) {
            $sql = \sprintf('INSERT INTO `%s` (word, category_id, COUNT) VALUES (%s, %s, %d)', $this->db->prefix('xhelp_bayes_wordfreqs'), $this->db->quoteString($this->cleanVar($word)), $this->db->quoteString($this->cleanVar($category_id)), $count);
        } else {
            $sql = \sprintf('UPDATE `%s` SET COUNT+=%d WHERE category_id = %s AND word = %s', $this->db->prefix('xhelp_bayes_wordfreqs'), $count, $this->db->quoteString($this->cleanVar($category_id)), $this->db->quoteString($this->cleanVar($word)));
        }

        $ret = $this->db->query($sql);

        if (!$ret) {
            return false;
        }

        return true;
    }

    /** remove a word from a category.
     *
     * @param string $word
     * @param int    $count
     * @param string $category_id
     * @return bool success
     */
    public function removeWord(string $word, int $count, string $category_id): bool
    {
        $oldword = $this->getWord($word, $category_id);
        if (0 != $oldword['count'] && 0 >= ($oldword['count'] - $count)) {
            $sql = \sprintf('DELETE FROM `%s` WHERE word = %s AND category_id = %s', $this->db->prefix('xhelp_bayes_wordfreqs'), $this->db->quoteString($this->cleanVar($word)), $this->db->quoteString($this->cleanVar($category_id)));
        } else {
            $sql = \sprintf('UPDATE `%s` SET COUNT-=%d WHERE category_id = %s AND word = %s', $this->db->prefix('xhelp_bayes_wordfreqs'), $count, $this->db->quoteString($this->cleanVar($category_id)), $this->db->quoteString($this->cleanVar($word)));
        }
        $ret = $this->db->query($sql);

        if (!$ret) {
            return false;
        }

        return true;
    }

    /** update the probabilities of the categories and word count.
     * This function must be run after a set of training
     *
     * @return bool sucess
     */
    public function updateProbabilities(): bool
    {
        // first update the word count of each category
        $ret         = $this->db->query('SELECT category_id, SUM(count) AS total FROM ' . $this->db->prefix('xhelp_bayes_wordfreqs') . ' GROUP BY category_id');
        $total_words = 0;
        while (false !== ($arr = $this->db->fetchRow($ret))) {
            $total_words              += $arr['total'];
            $cat[$arr['category_id']] = $arr['total'];
        }
        if (0 == $total_words) {
            $this->db->query('UPDATE ' . $this->db->prefix('xhelp_bayes_wordfreqs') . ' SET word_count=0, probability=0');

            return true;
        }
        foreach ($cat as $cat_id => $cat_total) {
            //Calculate each category's probability
            $proba = $cat_total / $total_words;
            $this->db->query(\sprintf('UPDATE `%s` SET word_count = %d, probability = %f WHERE category_id = %s', $this->db->prefix('xhelp_bayes_wordfreqs'), $cat_total, $proba, $this->db->quoteString($this->cleanVar($cat_id))));
        }

        return true;
    }

    /** save a reference in the database.
     *
     * @param mixed $doc_id
     * @param mixed $category_id
     * @param mixed $content
     * @return bool success
     */
    public function saveReference($doc_id, $category_id, $content): bool
    {
        return true;
    }

    /** get a reference from the database.
     *
     * @param mixed $doc_id
     * @return array reference( category_id => ...., content => ....)
     */
    public function getReference($doc_id): array
    {
        $helper = Helper::getInstance();
        /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
        $ticketHandler = $helper->getHandler('Ticket');
        $ticket        = $ticketHandler->get($doc_id);
        $ref           = [];

        if (!$ticket) {
            return $ref;
        }

        $ref['id']          = $ticket->getVar('ticketid');
        $ref['content']     = $ticket->getVar('subject') . "\r\n" . $ticket->getVar('description');
        $ref['category_id'] = 'P' . $ticket->getVar('ticketid');

        return $ref;
    }

    /** remove a reference from the database
     *
     * @param mixed $doc_id
     * @return bool sucess
     */
    public function removeReference($doc_id): bool
    {
        return true;
    }

    /**
     * @param string $var
     * @return string
     */
    private function cleanVar(string $var): string
    {
        return $this->myts->censorString($var);
    }
}
