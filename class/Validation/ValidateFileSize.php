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
 * @copyright    {@link https://xoops.org/ XOOPS Project}
 * @license      {@link https://www.gnu.org/licenses/gpl-2.0.html GNU GPL 2 or later}
 * @author       Brian Wahoff <ackbarr@xoops.org>
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */


/**
 * Class ValidateFileSize
 */
class ValidateFileSize extends Validator
{
    public $file;
    public $maxsize;

    /**
     * ValidateFileSize constructor.
     * @param string $file
     * @param int    $maxsize
     */
    public function __construct(string $file, int $maxsize)
    {
        $this->file    = $file;
        $this->maxsize = $maxsize;
        parent::__construct();
    }

    public function validate()
    {
        if ($this->maxsize < \filesize($this->file)) {
            $this->setError('File is too large');
        }
    }
}
