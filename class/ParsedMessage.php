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
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

if (!\defined('XHELP_CONSTANTS_INCLUDED')) {
    exit();
}

\define('MD5SIGNATUREPATTERN', '/{([^ ]*)}/i');
\define('HEADER_PRIORITY', 'Importance');
\define('_XHELP_MSGTYPE_TICKET', 1);
\define('_XHELP_MSGTYPE_RESPONSE', 2);

require_once \XHELP_PEAR_PATH . '/Mail/mimeDecode.php';

/**
 * class ParsedMessage
 */
class ParsedMessage
{
    public $_email;
    public $_msgtype;
    public $_name;
    public $_msg;
    public $_hash;
    public $_headers;
    public $_ticket;
    public $_attachments;

    /**
     * Class Constructor
     * @param array $msg Array of message values
     */
    public function __construct(array $msg)
    {
        $struct         = $msg['mime_struct'];
        $this->_email   = $msg['email'];
        $this->_name    = $msg['name'];
        $this->_headers = $struct->headers;

        $this->_hash = $msg['hash'];
        $this->_msg  = $msg['msg'];

        $this->_msgtype     = ('' === $msg['hash'] ? _XHELP_MSGTYPE_TICKET : _XHELP_MSGTYPE_RESPONSE);
        $this->_attachments = [];
        $this->loadAttachments($struct);
    }

    /**
     * @return int
     */
    public function getMsgType(): int
    {
        return $this->_msgtype;
    }

    /**
     * @return bool
     */
    public function getPriority(): ?bool
    {
        $pri = $this->getHeader(HEADER_PRIORITY);

        switch ($pri) {
            default:
                return $pri;
        }
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->_headers['subject'];
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->_msg;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->_email;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $header
     * @return bool
     */
    public function getHeader(string $header): bool
    {
        return $this->_headers[$header] ?? false;
    }

    /**
     * @return mixed
     */
    public function &getAllHeaders()
    {
        return $this->_headers;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->_hash;
    }

    /**
     * @return array
     */
    public function &getAttachments(): array
    {
        return $this->_attachments;
    }

    /**
     * @param array|object $part
     */
    public function loadAttachments($part): void
    {
        if (\is_array($part)) {
            foreach ($part as $subpart) {
                $this->loadAttachments($subpart);
            }
        } elseif (isset($part->parts)) {
            $this->loadAttachments($part->parts);
        } else {
            if ('text' === $part->ctype_primary && 'plain' === $part->ctype_secondary) {
                if (isset($part->disposition) && 'attachment' === $part->disposition) {
                    $this->addAttachment($part);
                }
                // Do Nothing
            } else {
                $this->addAttachment($part);
            }
        }
    }

    /**
     * @param object $part
     */
    private function addAttachment(object $part): void
    {
        $_attach                 = [];
        $_attach['content-type'] = $part->ctype_primary . '/' . $part->ctype_secondary;
        $_attach['filename']     = (isset($part->d_parameters) ? $this->cleanFilename($part->d_parameters['filename']) : 'content_' . $part->ctype_primary . '_' . $part->ctype_secondary);
        $_attach['content']      = $part->body;
        $this->_attachments[]    = $_attach;
        unset($_attach);
    }

    /**
     * Removes unsafe characters from the attachment filename
     *
     * @param string $name Original Filename
     * @return string "cleaned" filename
     * @todo   Get list of other unsafe characters by platform
     */
    private function cleanFilename(string $name): string
    {
        $name = \str_replace(' ', '_', $name);

        return $name;
    }
}
