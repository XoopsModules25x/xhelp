<?php declare(strict_types=1);
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * @copyright       XOOPS Project (https://xoops.org)
 * @license         GNU GPL 2 (https://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * @author          XOOPS Project <www.xoops.org> <www.xoops.ir>
 */

/**
 * Class XhelpCorePreload
 */
class XhelpCorePreload extends \XoopsPreloadItem
{
    // to add PSR-4 autoloader
    /**
     * @param array|null $args
     */
    public static function eventCoreIncludeCommonEnd(?array $args)
    {
        require_once __DIR__ . '/autoloader.php';
    }

    /**
     * eventCoreIncludeCommonAuthSuccess
     */
    public static function eventCoreIncludeCommonAuthSuccess(): void
    {
        $autoloader = dirname(__DIR__) . '/vendor/autoload.php';
        if (!is_file($autoloader)) {
            trigger_error("xhelp/vendor/autoload.php not found, was 'composer install' done?");

            return;
        }
        //        xoops_loadLanguage('logger');
        require_once $autoloader;
        //        $permissionHelper = new Permission('xwhoops25');
        //        if ($permissionHelper) {
        //            $permissionName   = 'use_xwhoops';
        //            $permissionItemId = 0;
        //
        //            if ($permissionHelper->checkPermission($permissionName, $permissionItemId, false)) {
        //                $whoops  = new \Whoops\Run();
        //                $handler = new \Whoops\Handler\PrettyPageHandler();
        //                $whoops->pushHandler($handler);
        //                $whoops->register();
        //                $handler->addDataTableCallback(
        //                    _LOGGER_QUERIES,
        //                    function () {
        //                        $logger = XoopsLogger::getInstance();
        //                        if (false === $logger->renderingEnabled) {
        //                            return ['XoopsLogger' => 'off'];  // logger is off so data is incomplete
        //                        }
        //                        $queries = [];
        //                        $count   = 1;
        //                        foreach ($logger->queries as $key => $q) {
        //                            $error     = (null === $q['errno'] ? '' : $q['errno'] . ' ') . (null === $q['error'] ? '' : $q['error']);
        //                            $queryTime = isset($q['query_time']) ? sprintf('%0.6f', $q['query_time']) : '';
        //                            $queryKey  = (string)$count++ . ' - ' . $queryTime;
        //                            if (null !== $q['errno']) {
        //                                $queryKey = (string)$count . ' - Error';
        //                            }
        //                            $queries[$queryKey] = htmlentities($q['sql']) . ' ' . $error;
        //                        }
        //
        //                        return ($queries);
        //                    }
        //                );
        //            }
        //        }
    }
}
