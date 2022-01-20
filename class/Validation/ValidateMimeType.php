<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Validation;

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
 * @copyright    XOOPS Project (https://xoops.org)
 * @license      GNU GPL 2.0 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

/**
 * Class ValidateMimeType
 */
class ValidateMimeType extends Validator
{
    public $file;
    public $mimetype;
    public $allowed_mimetypes;

    /**
     * ValidateMimeType constructor.
     * @param string $file
     * @param string $mimetype
     * @param array  $allowed_mimetypes
     */
    public function __construct(string $file, string $mimetype, array $allowed_mimetypes)
    {
        $this->file              = $file;
        $this->mimetype          = \mb_strtolower($mimetype);
        $this->allowed_mimetypes = $allowed_mimetypes;
        parent::__construct();
    }

    public function validate()
    {
        $allowed_mimetypes = false;
        //Check MimeType
        if (\is_array($this->allowed_mimetypes)) {
            $farray     = \explode('.', $this->file);
            $fextension = \mb_strtolower($farray[\count($farray) - 1]);
            foreach ($this->allowed_mimetypes as $mime) {
                $lower_type = \mb_strtolower($mime['type']);
                $lower_ext  = \mb_strtolower($mime['ext']);
                if ($lower_type == $this->mimetype && $lower_ext == $fextension) {
                    $allowed_mimetypes = $mime['type'];
                    break;
                }
            }
        }

        if (!$allowed_mimetypes) {
            $this->setError('Invalid MimeType');
        }
    }
}
