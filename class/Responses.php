<?php

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
 * @package
 * @since
 * @author       XOOPS Development Team
 */

use XoopsModules\Xhelp;

if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/BaseObjectHandler.php';

/**
 * Xhelp\Responses class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 * @access  public
 * @package xhelp
 */
class Responses extends \XoopsObject
{
    /**
     * Xhelp\Responses constructor.
     * @param null $id
     */
    public function __construct($id = null)
    {
        $this->initVar('id', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('uid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('ticketid', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('message', \XOBJ_DTYPE_TXTAREA, null, false, 1000000);
        $this->initVar('timeSpent', \XOBJ_DTYPE_INT, null, false);
        $this->initVar('updateTime', \XOBJ_DTYPE_INT, null, true);
        $this->initVar('userIP', \XOBJ_DTYPE_TXTBOX, null, true, 35);
        $this->initVar('private', \XOBJ_DTYPE_INT, null, false);

        if (null !== $id) {
            if (\is_array($id)) {
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
        return \formatTimestamp($this->getVar('updateTime'), $format);
    }

    /**
     * @param      $post_field
     * @param null $response
     * @param null $allowed_mimetypes
     * @return array|bool|string|\XoopsObject
     */
    public function storeUpload($post_field, $response = null, $allowed_mimetypes = null)
    {
        //global $xoopsModuleConfig, $xoopsUser, $xoopsDB, $xoopsModule;
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config = Xhelp\Utility::getModuleConfig();

        $ticketid = $this->getVar('id');

        if (null === $allowed_mimetypes) {
            $hMime             = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
            $allowed_mimetypes = $hMime->checkMimeTypes();
            if (!$allowed_mimetypes) {
                return false;
            }
        }

        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        if (!\is_dir(XHELP_UPLOAD_PATH)) {
            if (!\mkdir($concurrentDirectory = XHELP_UPLOAD_PATH, 0757) && !\is_dir($concurrentDirectory)) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }

        $uploader = new Xhelp\MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($post_field)) {
            if (null === $response) {
                $uploader->setTargetFileName($ticketid . '_' . $uploader->getMediaName());
            } else {
                $uploader->setTargetFileName($ticketid . '_' . $response . '_' . $uploader->getMediaName());
            }
            if ($uploader->upload()) {
                $hFile = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
                $file  = $hFile->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (null !== $response ? (int)$response : 0));

                if ($hFile->insert($file)) {
                    return $file;
                }

                return $uploader->getErrors();
            }

            return $uploader->getErrors();
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
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config        = Xhelp\Utility::getModuleConfig();
        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        $errors        = [];

        if (null === $allowed_mimetypes) {
            $hMime             = new Xhelp\MimetypeHandler($GLOBALS['xoopsDB']);
            $allowed_mimetypes = $hMime->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                $errors[] = _XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new Xhelp\MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

        if ($uploader->fetchMedia($post_field)) {
            return true;
        }

        $errors = \array_merge($errors, $uploader->getErrors(false));

        return false;
    }

    /**
     * Get the ticket to which the response is attached
     * @return Xhelp\Ticket The ticket
     * @access public
     */
    public function getTicket()
    {
        $ticketHandler = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);

        return $ticketHandler->get($this->getVar('ticketid'));
    }
}
