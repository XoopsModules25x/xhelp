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
 * Response class
 *
 * @author  Eric Juden <ericj@epcusa.com>
 */
class Response extends \XoopsObject
{
    private $helper;

    /**
     * Response constructor.
     * @param int|array|null $id
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

        $this->helper = Helper::getInstance();
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
     */
    public function posted(string $format = 'l'): string
    {
        return \formatTimestamp($this->getVar('updateTime'), $format);
    }

    /**
     * @param string $post_field
     * @param null   $response
     * @param null   $allowed_mimetypes
     * @return array|false|object|string|void
     */
    public function storeUpload(string $post_field, $response = null, $allowed_mimetypes = null)
    {
        //global $xoopsModuleConfig, $xoopsUser, $xoopsDB, $xoopsModule;
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config = Utility::getModuleConfig();

        $ticketid = $this->getVar('id');

        if (null === $allowed_mimetypes) {
            /** @var \XoopsModules\Xhelp\MimetypeHandler $mimetypeHandler */
            $mimetypeHandler   = $this->helper->getHandler('Mimetype');
            $allowed_mimetypes = $mimetypeHandler->checkMimeTypes($post_field);
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

        $uploader = new MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);
        if ($uploader->fetchMedia($post_field)) {
            if (null === $response) {
                $uploader->setTargetFileName($ticketid . '_' . $uploader->getMediaName());
            } else {
                $uploader->setTargetFileName($ticketid . '_' . $response . '_' . $uploader->getMediaName());
            }
            if ($uploader->upload()) {
                $fileHandler = $this->helper->getHandler('File');
                $file        = $fileHandler->create();
                $file->setVar('filename', $uploader->getSavedFileName());
                $file->setVar('ticketid', $ticketid);
                $file->setVar('mimetype', $allowed_mimetypes);
                $file->setVar('responseid', (null !== $response ? (int)$response : 0));

                if ($fileHandler->insert($file)) {
                    return $file;
                }

                return $uploader->getErrors();
            }

            return $uploader->getErrors();
        }
    }

    /**
     * @param string $post_field
     * @param array  $allowed_mimetypes
     * @param array  $errors
     * @return bool
     */
    public function checkUpload(string $post_field, array &$allowed_mimetypes, array &$errors): bool
    {
        //global $xoopsModuleConfig;
        // require_once XHELP_CLASS_PATH . '/uploader.php';
        $config        = Utility::getModuleConfig();
        $maxfilesize   = $config['xhelp_uploadSize'];
        $maxfilewidth  = $config['xhelp_uploadWidth'];
        $maxfileheight = $config['xhelp_uploadHeight'];
        $errors        = [];

        if (null === $allowed_mimetypes) {
            $mimetypeHandler   = $this->helper->getHandler('Mimetype');
            $allowed_mimetypes = $mimetypeHandler->checkMimeTypes($post_field);
            if (!$allowed_mimetypes) {
                $errors[] = \_XHELP_MESSAGE_WRONG_MIMETYPE;

                return false;
            }
        }
        $uploader = new MediaUploader(XHELP_UPLOAD_PATH . '/', $allowed_mimetypes, $maxfilesize, $maxfilewidth, $maxfileheight);

        if ($uploader->fetchMedia($post_field)) {
            return true;
        }

        $errors = \array_merge($errors, $uploader->getErrors(false));

        return false;
    }

    /**
     * Get the ticket to which the response is attached
     * @return \XoopsObject|null The ticket ID
     */
    public function getTicket(): ?\XoopsObject
    {
        $ticketHandler = $this->helper->getHandler('Ticket');

        return $ticketHandler->get($this->getVar('ticketid'));
    }
}
