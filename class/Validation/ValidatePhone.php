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
 * Class ValidatePhone
 */
class ValidatePhone extends Validator
{
    /**
     * Private
     * $phone the phone number to validate
     */
    public $phone;
    //! A constructor.

    /**
     * Constructs a new ValidatePhone object subclass or Validator
     * @param string $phone the string to validate
     */
    public function __construct(string $phone)
    {
        $this->phone = $phone;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates a phone number
     */
    public function validate()
    {
        $pattern = '/(\d{3})\D*(\d{3})\D*(\d{4})\D*(\d*)$/';
        if (!\preg_match($pattern, $this->phone)) {
            $this->setError(\_XHELP_MESSAGE_INVALID);
        }
    }
}
