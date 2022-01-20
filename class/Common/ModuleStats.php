<?php

declare(strict_types=1);

namespace XoopsModules\Xhelp\Common;

/**
 * Created by PhpStorm.
 * User: mamba
 * Date: 2015-07-06
 * Time: 11:27
 */
trait ModuleStats
{
    /**
     * @param Configurator $configurator
     * @return array
     */

    public static function getModuleStats(): array
    {
        $moduleStats = require \dirname(__DIR__, 2) . '/config/stats.php';

        return $moduleStats;
    }
}
