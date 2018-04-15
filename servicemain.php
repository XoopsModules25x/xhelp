<?php

use XoopsModules\Xhelp;

error_reporting(E_ALL); //Enable Error Reporting
//if (PHP_VERSION_ID < 50300) {
//    set_magic_quotes_runtime(0);
//}

if (function_exists('mb_http_output')) {
    mb_http_output('pass');
}
$xoopsOption['nocommon'] = 1;
require_once  dirname(dirname(__DIR__)) . '/mainfile.php';

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
defined('NWLINE') or define('NWLINE', "\n");

//Include XOOPS Global Includes
include XOOPS_ROOT_PATH . '/include/functions.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsload.php';
require_once XOOPS_ROOT_PATH . '/class/preload.php';
require_once XOOPS_ROOT_PATH . '/class/logger/xoopslogger.php';
require_once XOOPS_ROOT_PATH . '/class/module.textsanitizer.php';
require_once XOOPS_ROOT_PATH . '/class/database/databasefactory.php';
require_once XOOPS_ROOT_PATH . '/kernel/object.php';
require_once XOOPS_ROOT_PATH . '/class/criteria.php';
require_once XOOPS_ROOT_PATH . '/class/xoopskernel.php';

$xoops = new xos_kernel_Xoops2();
$xoops->pathTranslation();

$xoopsLogger = XoopsLogger::getInstance();
$xoopsLogger->startTime();

define('XOOPS_DB_PROXY', 1);
$xoopsDB = \XoopsDatabaseFactory::getDatabaseConnection();

//End of Xoops globals include

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once XHELP_BASE_PATH . '/functions.php';

$moduleHandler = xoops_getHandler('module');
$module        = $moduleHandler;
$configHandler = xoops_getHandler('config');
$xoopsConfig   = $configHandler->getConfigsByCat(XOOPS_CONF);

xoops_loadLanguage('global');

$xoopsConfigUser = [];
$myConfigs       =& $configHandler->getConfigs();
foreach ($myConfigs as $myConf) {
    $xoopsConfigUser[$myConf->getVar('conf_name')] = $myConf->getVar('conf_value');
}

$xoopsModule       =& Xhelp\Utility::getModule();
$xoopsModuleConfig =& Xhelp\Utility::getModuleConfig();

$helper->loadLanguage('main');
