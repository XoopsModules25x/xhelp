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
 *  ValidatorUname subclass of Validator
 *  Validates a username
 */
class ValidateUname extends Validator
{
    /**
     * Private
     * $uname the username to validate
     */
    public $uname;
    //! A constructor.

    /**
     * Constructs a new ValidateUname object subclass or Validator
     * @param string $uname the string to validate
     */
    public function __construct(string $uname)
    {
        $this->uname = $uname;
        parent::__construct();
    }

    //! A manipulator

    /**
     * Validates an email address
     */
    public function validate()
    {
        /** @var \XoopsConfigHandler $configHandler */
        $configHandler = \xoops_getHandler('config');
        //$xoopsConfigUser = $configHandler->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $criteria        = new \Criteria('conf_catid', 2);
        $myConfigs       = $configHandler->getConfigs($criteria);
        foreach ($myConfigs as $myConf) {
            $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
        }
        $xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

        switch ($xoopsConfigUser['uname_test_level']) {
            case 0:
                // strict
                $restriction = '/[^a-zA-Z0-9\_\-]/';
                break;
            case 1:
                // medium
                $restriction = '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/';
                break;
            case 2:
                // loose
                $restriction = '/[\000-\040]/';
                break;
        }

        if (empty($this->uname) || \preg_match($restriction, $this->uname)) {
            $this->setError(\_XHELP_MESSAGE_INVALID);
        }
        if (mb_strlen($this->uname) > $xoopsConfigUser['maxuname']) {
            $this->setError(\sprintf(\_XHELP_MESSAGE_LONG, $xoopsConfigUser['maxuname']));
        }
        if (mb_strlen($this->uname) < $xoopsConfigUser['minuname']) {
            $this->setError(\sprintf(\_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minuname']));
        }
        foreach ($xoopsConfigUser['bad_unames'] as $bu) {
            if (!empty($bu) && \preg_match('/' . $bu . '/i', $this->uname)) {
                $this->setError(\_XHELP_MESSAGE_RESERVED);
                break;
            }
        }
        if (mb_strrpos($this->uname, ' ') > 0) {
            $this->setError(\_XHELP_MESSAGE_NO_SPACES);
        }
        $sql    = 'SELECT COUNT(*) FROM ' . $xoopsDB->prefix('users') . " WHERE uname='" . \addslashes($this->uname) . "'";
        $result = $xoopsDB->query($sql);
        [$count] = $xoopsDB->fetchRow($result);
        if ($count > 0) {
            $this->setError(\_XHELP_MESSAGE_UNAME_TAKEN);
        }
    }
}
