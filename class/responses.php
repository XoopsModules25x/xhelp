<?php
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
 * @license      {@link http://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @package
 * @since
 * @author       XOOPS Development Team
 */

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once XHELP_CLASS_PATH . '/xhelpBaseObjectHandler.php';

/**
 * xhelpResponses class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class XHelpResponses extends XoopsObject
{
    /**
     * XHelpResponses constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('ticketid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('message', XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('timeSpent', XOBJ_DTYPE_INT, null, false);
        $this->initVar('updateTime', XOBJ_DTYPE_INT, null, true);
        $this->initVar('userIP', XOBJ_DTYPE_TXTBOX, null, true, 35);
        $this->initVar('private', XOBJ_DTYPE_INT, null, false);

        if (isset($id)) {
            if (is_array($id)) {
                $this->assignVars($id);
            }
        } else {
            $this->setNew();
        }
    }

    /**
     * Formats the posted date as the XOOPS date formate
     *
     * @param string $format
     * @return string Formatted posted date
     * @access public
     */
    public function posted($format = 'l')
    {
        return formatTimestamp($this->getVar('updateTime'), $format);
    }

    /**
     * @param      $post_field
     * @param null $response
     * @param null $allowed_mimetypes
     * @return array|bool|string|XoopsObject
     */
    public function storeUpload($post_field, $response = null, $allowed_mimetypes = null)
    {
        //global $xoopsModuleConfig, $xoopsUser, $xoopsDB, $xoopsModule;
        require_once XHELP_CLASS_PATH . '/uploader.php';
        $config = xhelpGetModuleConfig();

        $ticketid = $this->getVar('id');

        if (!isset($allowed_mimetypes)) {
            $hMime             = xhelpGetHandler('mimetype');
            $allowed_mimetypes = $hMime->checkMimeTypes();
            if (!$allowed_mimetypes) {
                return false;
            }
        }

        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        if (!is_dir(XHELP_UPLOAD_PATH)) {
            mkdir(XHELP_UPLOAD_PATH, 0757);
        }

        $uploader = new XoopsMediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($post_field)) {
            if (!isset($response)) {
                $uploader->setTargetFileName($ticketid . '_' . $uploader->getMediaName());
            } else {
                $uploader->setTargetFileName($ticketid . '_' . $response . '_' . $uploader->getMediaName());
            }
            if ($uploader->upload()) {
                $hFile = xhelpGetHandler('file');
                $file  = $hFile->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (isset($response) ? (int)$response : 0));

                if ($hFile->insert($file)) {
                    return $file;
                } else {
                    return $uploader->getErrors();
                }
            } else {
                return $uploader->getErrors();
            }
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
        //global $xoopsModuleConfig;
        require_once XHELP_CLASS_PATH . '/uploader.php';
        $config        = xhelpGetModuleConfig();
        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        $errors        = [];

        if (!isset($allowed_mimetypes)) {
            $hMime             = xhelpGetHandler('mimetype');
            $allowed_mimetypes = $hMime->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                $errors[] = _XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new XoopsMediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

        if ($uploader->fetchMedia($post_field)) {
            return true;
        } else {
            $errors = array_merge($errors, $uploader->getErrors(false));

            return false;
        }
    }

    /**
     * Get the ticket to which the response is attached
     * @return xhelpTicket The ticket
     * @access public
     */
    public function &getTicket()
    {
        $hTicket = xhelpGetHandler('ticket');

        return $hTicket->get($this->getVar('ticketid'));
    }
}

/**
 * xhelpResponsesHandler class
 *
 * Response Handler for xhelpResponses class
 *
 * @author  Eric Juden <ericj@epcusa.com> &
 * @access  public
 * @package xhelp
 */
class XHelpResponsesHandler extends xhelpBaseObjectHandler
{
    /**
     * Name of child class
     *
     * @var string
     * @access  private
     */
    public $classname = 'xhelpresponses';

    /**
     * DB table name
     *
     * @var string
     * @access private
     */
    public $_dbtable = 'xhelp_responses';

    /**
     * Constructor
     *
     * @param object|XoopsDatabase $db reference to a xoopsDB object
     */
    public function __construct(XoopsDatabase $db)
    {
        parent::init($db);
    }

    /**
     * @param $obj
     * @return string
     */
    public function _insertQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf('INSERT INTO %s (id, uid, ticketid, message, timeSpent, updateTime, userIP, private)
            VALUES (%u, %u, %u, %s, %u, %u, %s, %u)', $this->_db->prefix($this->_dbtable), $id, $uid, $ticketid, $this->_db->quoteString($message), $timeSpent, time(), $this->_db->quoteString($userIP), $private);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _updateQuery($obj)
    {
        // Copy all object vars into local variables
        foreach ($obj->cleanVars as $k => $v) {
            ${$k} = $v;
        }

        $sql = sprintf('UPDATE %s SET uid = %u, ticketid = %u, message = %s, timeSpent = %u,
            updateTime = %u, userIP = %s, private = %u WHERE id = %u', $this->_db->prefix($this->_dbtable), $uid, $ticketid, $this->_db->quoteString($message), $timeSpent, time(), $this->_db->quoteString($userIP), $private, $id);

        return $sql;
    }

    /**
     * @param $obj
     * @return string
     */
    public function _deleteQuery($obj)
    {
        $sql = sprintf('DELETE FROM %s WHERE id = %u', $this->_db->prefix($this->_dbtable), $obj->getVar('id'));

        return $sql;
    }

    /**
     * delete a response from the database
     *
     * @param object|XoopsObject $obj reference to the {@link xhelpResponse}
     *                                obj to delete
     * @param  bool              $force
     * @return bool FALSE if failed.
     * @access  public
     */
    public function delete(XoopsObject $obj, $force = false)
    {

        // Remove file associated with this response
        $hFiles = xhelpGetHandler('file');
        $crit   = new CriteriaCompo(new Criteria('ticketid', $obj->getVar('ticketid')));
        $crit->add(new Criteria('responseid', $obj->getVar('responseid')));
        if (!$hFiles->deleteAll($crit)) {
            return false;
        }

        $ret = parent::delete($obj, $force);

        return $ret;
    }

    /**
     * Get number of responses by staff members
     *
     * @param  int $ticketid ticket to get count
     * @return int Number of staff responses
     * @access  public
     */
    public function getStaffResponseCount($ticketid)
    {
        $sql = sprintf('SELECT COUNT(*) FROM %s r INNER JOIN %s s ON r.uid = s.uid WHERE r.ticketid = %u', $this->_db->prefix($this->_dbtable), $this->_db->prefix('xhelp_staff'), $ticketid);

        $ret = $this->_db->query($sql);

        list($count) = $this->_db->fetchRow($ret);

        return $count;
    }

    /**
     * Get number of responses by ticketid
     *
     * @param  array $tickets where ticketid is key
     * @return array key = ticketid, value = response count
     * @access public
     */
    public function getResponseCounts($tickets)
    {
        if (is_array($tickets)) {
            //$crit = new Criteria('ticketid', "(". implode(array_keys($tickets), ',') .")", 'IN');
            $sql = sprintf('SELECT COUNT(*) AS numresponses, ticketid FROM %s WHERE ticketid IN (%s) GROUP BY ticketid', $this->_db->prefix($this->_dbtable), implode(array_keys($tickets), ','));
        } else {
            return false;
        }
        $result = $this->_db->query($sql);

        if (!$result) {
            return false;
        }

        // Add each returned record to the result array
        while ($myrow = $this->_db->fetchArray($result)) {
            $tickets[$myrow['ticketid']] = $myrow['numresponses'];
        }

        return $tickets;
    }
}
