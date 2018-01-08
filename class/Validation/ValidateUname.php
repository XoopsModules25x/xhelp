<?php namespace XoopsModules\Xhelp\Validation;

use XoopsModules\Xhelp;
use XoopsModules\Xhelp\Validation;


/**
 *  ValidatorUname subclass of Validator
 *  Validates a username
 */
class ValidateUname extends validation\Validator
{
    /**
     * Private
     * $uname the username to validate
     */
    public $uname;

    //! A constructor.

    /**
     * Constucts a new ValidateUname object subclass or Validator
     * @param string $uname the string to validate
     */
    public function __construct($uname)
    {
        $this->uname = $uname;
        Validator::Validator();
    }

    //! A manipulator

    /**
     * Validates an email address
     * @return void
     */
    public function validate()
    {
        $hConfig = xoops_getHandler('config');
        //$xoopsConfigUser = $hConfig->getConfigsByCat(XOOPS_CONF_USER);
        $xoopsConfigUser = [];
        $crit            = new \Criteria('conf_catid', 2);
        $myConfigs       = $hConfig->getConfigs($crit);
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

        if (empty($this->uname) || preg_match($restriction, $this->uname)) {
            $this->setError(_XHELP_MESSAGE_INVALID);
        }
        if (strlen($this->uname) > $xoopsConfigUser['maxuname']) {
            $this->setError(sprintf(_XHELP_MESSAGE_LONG, $xoopsConfigUser['maxuname']));
        }
        if (strlen($this->uname) < $xoopsConfigUser['minuname']) {
            $this->setError(sprintf(_XHELP_MESSAGE_SHORT, $xoopsConfigUser['minuname']));
        }
        foreach ($xoopsConfigUser['bad_unames'] as $bu) {
            if (!empty($bu) && preg_match('/' . $bu . '/i', $this->uname)) {
                $this->setError(_XHELP_MESSAGE_RESERVED);
                break;
            }
        }
        if (strrpos($this->uname, ' ') > 0) {
            $this->setError(_XHELP_MESSAGE_NO_SPACES);
        }
        $sql    = 'SELECT COUNT(*) FROM ' . $xoopsDB->prefix('users') . " WHERE uname='" . addslashes($this->uname) . "'";
        $result = $xoopsDB->query($sql);
        list($count) = $xoopsDB->fetchRow($result);
        if ($count > 0) {
            $this->setError(_XHELP_MESSAGE_UNAME_TAKEN);
        }
    }
}
