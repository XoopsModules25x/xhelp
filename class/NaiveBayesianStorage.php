<?php namespace XoopsModules\Xhelp;

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

use XoopsModules\Xhelp;


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
    public $con  = null;
    public $myts = null;

    /**
     * Xhelp\NaiveBayesianStorage constructor.
     */
    public function __construct()
    {
        $this->con  = \XoopsDatabaseFactory::getDatabaseConnection();
        $this->myts = \MyTextSanitizer::getInstance();

        return true;
    }

    /** get the list of categories with basic data.
     *
     * @return array key = category ids, values = array(keys = 'probability', 'word_count')
     */
    public function getCategories()
    {
        $categories = [];

        $ret = $this->con->query('SELECT * FROM ' . $this->con->prefix('xhelp_bayes_categories'));

        while ($arr = $this->con->fetchRow($ret)) {
            $categories[$arr['category_id']] = [
                'probability' => $arr['probability'],
                'word_count'  => $arr['word_count']
            ];
        }

        return $categories;
    }

    /** see if the word is an already learnt word.
     * @return bool
     * @param string word
     */
    public function wordExists($word)
    {
        $crit = new \Criteria('word', $word);

        $ret = $this->con->query('SELECT COUNT(*) AS WordCount FROM ' . $this->con->prefix('xhelp_bayes_wordfreqs') . $crit->renderWhere());

        if (!$ret) {
            return false;
        } else {
            $arr = $this->con->fetchRow($ret);

            return $arr['WordCount'] > 0;
        }
    }

    /** get details of a word in a category.
     * @return array ('count' => count)
     * @param  string word
     * @param  string category id
     */
    public function getWord($word, $category_id)
    {
        $details = [];
        $crit    = new \CriteriaCompo(new \Criteria('word', $word));
        $crit->add(new \Criteria('category_id', $category_id));

        $ret = $this->con->query('SELECT count FROM ' . $this->con->prefix('xhelp_bayes_wordfreqs') . $crit->renderWhere());

        if (!$ret) {
            $details['count'] = 0;
        } else {
            $details = $this->con->fetchRow($ret);
        }

        return $details;
    }

    /** update a word in a category.
     * If the word is new in this category it is added, else only the count is updated.
     *
     *
     * @param $word
     * @param $count
     * @param $category_id
     * @return bool success
     * @internal param word $string
     * @internal param count $int
     * @paran    string category id
     */
    public function updateWord($word, $count, $category_id)
    {
        $oldword = $this->getWord($word, $category_id);
        if (0 == $oldword['count']) {
            $sql = sprintf('INSERT INTO %s (word, category_id, COUNT) VALUES (%s, %s, %d)', $this->con->prefix('xhelp_bayes_wordfreqs'), $this->con->quoteString($this->_cleanVar($word)), $this->con->quoteString($this->_cleanVar($category_id)), (int)$count);
        } else {
            $sql = sprintf('UPDATE %s SET COUNT+=%d WHERE category_id = %s AND word = %s', $this->con->prefix('xhelp_bayes_wordfreqs'), (int)$count, $this->con->quoteString($this->_cleanVar($category_id)), $this->con->quoteString($this->_cleanVar($word)));
        }

        $ret = $this->con->query($sql);

        if (!$ret) {
            return false;
        } else {
            return true;
        }
    }

    /** remove a word from a category.
     *
     * @return bool success
     * @param string word
     * @param int    count
     * @param string category id
     */
    public function removeWord($word, $count, $category_id)
    {
        $oldword = $this->getWord($word, $category_id);
        if (0 != $oldword['count'] && 0 >= ($oldword['count'] - $count)) {
            $sql = sprintf('DELETE FROM %s WHERE word = %s AND category_id = %s', $this->con->prefix('xhelp_bayes_wordfreqs'), $this->con->quoteString($this->_cleanVar($word)), $this->con->quoteString($this->_cleanVar($category_id)));
        } else {
            $sql = sprintf('UPDATE %s SET COUNT-=%d WHERE category_id = %s AND word = %s', $this->con->prefix('xhelp_bayes_wordfreqs'), (int)$count, $this->con->quoteString($this->_cleanVar($category_id)), $this->con->quoteString($this->_cleanVar($word)));
        }
        $ret = $this->con->query($sql);

        if (!$ret) {
            return false;
        } else {
            return true;
        }
    }

    /** update the probabilities of the categories and word count.
     * This function must be run after a set of training
     *
     * @return bool sucess
     */
    public function updateProbabilities()
    {
        // first update the word count of each category
        $ret         = $this->con->query('SELECT category_id, SUM(count) AS total FROM ' . $this->con->prefix('xhelp_bayes_wordfreqs') . ' GROUP BY category_id');
        $total_words = 0;
        while ($arr = $this->con->fetchRow($ret)) {
            $total_words              += $arr['total'];
            $cat[$arr['category_id']] = $arr['total'];
        }
        if (0 == $total_words) {
            $this->con->query('UPDATE ' . $this->con->prefix('xhelp_bayes_wordfreqs') . ' SET word_count=0, probability=0');

            return true;
        }
        foreach ($cat as $cat_id => $cat_total) {
            //Calculate each category's probability
            $proba = $cat_total / $total_words;
            $this->con->query(sprintf('UPDATE %s SET word_count = %d, probability = %f WHERE category_id = %s', $this->con->prefix('xhelp_bayes_wordfreqs'), $cat_total, $proba, $this->con->quoteString($this->_cleanVar($cat_id))));
        }

        return true;
    }

    /** save a reference in the database.
     *
     * @return bool success
     * @param  string reference if, must be unique
     * @param  string category id
     * @param  string content of the reference
     */
    public function saveReference($doc_id, $category_id, $content)
    {
        return true;
    }

    /** get a reference from the database.
     *
     * @return array reference( category_id => ...., content => ....)
     * @param  string id
     */
    public function getReference($doc_id)
    {
        $hTicket = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
        $ticket  = $hTicket->get($doc_id);
        $ref     = [];

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
     * @return bool sucess
     * @param  string reference id
     */
    public function removeReference($doc_id)
    {
        return true;
    }

    /**
     * @param $var
     * @return mixed
     */
    public function _cleanVar($var)
    {
        return $this->myts->stripSlashesGPC($this->myts->censorString($var));
    }
}
