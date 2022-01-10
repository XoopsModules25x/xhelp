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
 * @license      GNU GPL 2 or later (https://www.gnu.org/licenses/gpl-2.0.html)
 * @author       XOOPS Development Team
 */

/**
 * Class ValidateNumber
 */
class ValidateNumber extends Validator
{
    /**
     * Private
     * $text the string to validate
     */
    public $text;
    public $forceentry;
    //! A constructor.

    /**
     * Constructs a new ValidateNumber object subclass or Validator
     * @param string $text
     * @param bool   $forceentry
     */
    public function __construct(string $text, bool $forceentry = false)
    {
        $this->text       = $text;
        $this->forceentry = $forceentry;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a number
     */
    public function validate()
    {
        if (!\is_numeric($this->text) && ('' !== $this->text && !$this->forceentry)) {
            $this->setError(\_XHELP_MESSAGE_NOT_NUMERIC);
        }
    }
}
