<?php

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

/**
 * Class XHelpNaiveBayesian
 */
class XHelpNaiveBayesian
{
    /** min token length for it to be taken into consideration */
    public $min_token_length = 3;
    /** max token length for it to be taken into consideration */
    public $max_token_length = 15;
    /** list of token to ignore
     * @see getIgnoreList()
     */
    public $ignore_list = [];
    /** storage object
     * @see class NaiveBayesianStorage
     */
    public $nbs = null;

    /**
     * XHelpNaiveBayesian constructor.
     * @param $nbs
     */
    public function __construct($nbs)
    {
        $this->nbs = $nbs;

        return true;
    }

    /** categorize a document.
     * Get list of categories in which the document can be categorized
     * with a score for each category.
     *
     * @return array keys = category ids, values = scores
     * @param string document
     */
    public function categorize($document)
    {
        $scores     = [];
        $categories = $this->nbs->getCategories();
        $tokens     = $this->_getTokens($document);
        // calculate the score in each category
        $total_words = 0;
        $ncat        = 0;
        //        while (list($category, $data) = each($categories)) {
        foreach ($categories as $category => $data) {
            $total_words += $data['word_count'];
            ++$ncat;
        }
        //        reset($categories);
        //        while (list($category, $data) = each($categories)) {
        foreach ($categories as $category => $data) {
            $scores[$category] = $data['probability'];
            // small probability for a word not in the category
            // maybe putting 1.0 as a 'no effect' word can also be good
            $small_proba = 1.0 / ($data['word_count'] * 2);
            //            reset($tokens);
            //            while (list($token, $count) = each($tokens)) {
            foreach ($tokens as $token => $count) {
                if ($this->nbs->wordExists($token)) {
                    $word = $this->nbs->getWord($token, $category);
                    if ($word['count']) {
                        $proba = $word['count'] / $data['word_count'];
                    } else {
                        $proba = $small_proba;
                    }
                    $scores[$category] *= pow($proba, $count) * pow($total_words / $ncat, $count);
                    // pow($total_words/$ncat, $count) is here to avoid underflow.
                }
            }
        }

        return $this->_rescale($scores);
    }

    /** training against a document.
     * Set a document as being in a specific category. The document becomes a reference
     * and is saved in the table of references. After a set of training is done
     * the updateProbabilities() function must be run.
     *
     * @see updateProbabilities()
     * @see untrain()
     * @return bool success
     * @param string document id, must be unique
     * @param string category_id the category id in which the document should be
     * @param string content of the document
     */
    public function train($doc_id, $category_id, $content)
    {
        $tokens = $this->_getTokens($content);
        //            while (list($token, $count) = each($tokens)) {
        foreach ($tokens as $token => $count) {
            $this->nbs->updateWord($token, $count, $category_id);
        }
        $this->nbs->saveReference($doc_id, $category_id, $content);

        return true;
    }

    /** untraining of a document.
     * To remove just one document from the references.
     *
     * @see updateProbabilities()
     * @see untrain()
     * @return bool success
     * @param string document id, must be unique
     */
    public function untrain($doc_id)
    {
        $ref    = $this->nbs->getReference($doc_id);
        $tokens = $this->_getTokens($ref['content']);
        //            while (list($token, $count) = each($tokens)) {
        foreach ($tokens as $token => $count) {
            $this->nbs->removeWord($token, $count, $ref['category_id']);
        }
        $this->nbs->removeReference($doc_id);

        return true;
    }

    /** rescale the results between 0 and 1.
     *
     * @author Ken Williams, ken@mathforum.org
     * @see    categorize()
     * @return array normalized scores (keys => category, values => scores)
     * @param array scores (keys => category, values => scores)
     */
    public function _rescale($scores)
    {
        // Scale everything back to a reasonable area in
        // logspace (near zero), un-loggify, and normalize
        $total = 0.0;
        $max   = 0.0;
        //        reset($scores);
        //        while (list($cat, $score) = each($scores)) {
        foreach ($scores as $cat => $score) {
            if ($score >= $max) {
                $max = $score;
            }
        }
        //        reset($scores);
        //        while (list($cat, $score) = each($scores)) {
        foreach ($scores as $cat => $score) {
            $scores[$cat] = (float)exp($score - $max);
            $total        += (float)pow($scores[$cat], 2);
        }
        $total = (float)sqrt($total);
        //        reset($scores);
        //        while (list($cat, $score) = each($scores)) {
        foreach ($scores as $cat => $score) {
            $scores[$cat] = (float)$scores[$cat] / $total;
        }
        reset($scores);

        return $scores;
    }

    /** update the probabilities of the categories and word count.
     * This function must be run after a set of training
     *
     * @see train()
     * @see untrain()
     * @return bool sucess
     */
    public function updateProbabilities()
    {
        // this function is really only database manipulation
        // that is why all is done in the NaiveBayesianStorage
        return $this->nbs->updateProbabilities();
    }

    /** Get the list of token to ignore.
     * @return array ignore list
     */
    public function getIgnoreList()
    {
        global $xhelp_noise_words;
        @xhelpIncludeLang('noise_words');

        return $xhelp_noise_words;
    }

    /** get the tokens from a string
     *
     * @author   James Seng. [http://james.seng.cc/] (based on his perl version)
     *
     *
     * @param the $string
     * @return array tokens
     * @internal param the $string string to get the tokens from
     */
    public function _getTokens($string)
    {
        $rawtokens = [];
        $tokens    = [];
        $string    = $this->_cleanString($string);
        if (0 == count($this->ignore_list)) {
            $this->ignore_list = $this->getIgnoreList();
        }
        $rawtokens = preg_split('[^-_A-Za-z0-9]+', $string);
        // remove some tokens
        //        while (list(, $token) = each($rawtokens)) {
        foreach ($rawtokens as $key => $token) {
            $token = trim($token);
            if (!isset($tokens[$token])) {
                $tokens[$token] = 0;
            }
            if (!(('' == $token) || (strlen($token) < $this->min_token_length)
                  || (strlen($token) > $this->max_token_length)
                  || preg_match('/^[0-9]+$/', $token)
                  || in_array($token, $this->ignore_list))) {
                $tokens[$token]++;
            }
        }

        return $tokens;
    }

    /** clean a string from the diacritics
     *
     * @author Antoine Bajolet [phpdig_at_toiletoine.net]
     * @author SPIP [http://uzine.net/spip/]
     *
     * @return string clean string
     * @param  string string with accents
     */
    public function _cleanString($string)
    {
        $diac = /* A */
            chr(192) . chr(193) . chr(194) . chr(195) . chr(196) . chr(197) . /* a */
            chr(224) . chr(225) . chr(226) . chr(227) . chr(228) . chr(229) . /* O */
            chr(210) . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) . /* o */
            chr(242) . chr(243) . chr(244) . chr(245) . chr(246) . chr(248) . /* E */
            chr(200) . chr(201) . chr(202) . chr(203) . /* e */
            chr(232) . chr(233) . chr(234) . chr(235) . /* Cc */
            chr(199) . chr(231) . /* I */
            chr(204) . chr(205) . chr(206) . chr(207) . /* i */
            chr(236) . chr(237) . chr(238) . chr(239) . /* U */
            chr(217) . chr(218) . chr(219) . chr(220) . /* u */
            chr(249) . chr(250) . chr(251) . chr(252) . /* yNn */
            chr(255) . chr(209) . chr(241);

        return strtolower(strtr($string, $diac, 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'));
    }
}

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
class XHelpNaiveBayesianStorage
{
    public $con  = null;
    public $myts = null;

    /**
     * XHelpNaiveBayesianStorage constructor.
     */
    public function __construct()
    {
        $this->con  = XoopsDatabaseFactory::getDatabaseConnection();
        $this->myts = MyTextSanitizer::getInstance();

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
        $crit = new Criteria('word', $word);

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
        $crit    = new CriteriaCompo(new Criteria('word', $word));
        $crit->add(new Criteria('category_id', $category_id));

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
        $hTicket = xhelpGetHandler('ticket');
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
