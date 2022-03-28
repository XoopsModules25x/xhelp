<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Common;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */
/**
 * Configurator Class
 *
 * @copyright   XOOPS Project (https://xoops.org)
 * @license     https://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      XOOPS Development Team
 */

/**
 * Class Configurator
 */
class Configurator
{
    private $data            = [];
    private $default;
    public  $name;
    public  $paths           = [];
    public  $icons           = [];
    public  $uploadFolders   = [];
    public  $copyBlankFiles  = [];
    public  $copyTestFolders = [];
    public  $templateFolders = [];
    public  $oldFiles        = [];
    public  $oldFolders      = [];
    public  $renameTables    = [];
    public  $renameColumns   = [];
    public  $moduleStats     = [];
    public  $modCopyright;
    //    public $icons;

    /**
     * Configurator constructor.
     */
    public function __construct()
    {
        $config = require \dirname(__DIR__, 2) . '/config/config.php';

        $this->name            = $config->name;
        $this->paths           = $config->paths;
        $this->uploadFolders   = $config->uploadFolders;
        $this->copyBlankFiles  = $config->copyBlankFiles;
        $this->copyTestFolders = $config->copyTestFolders;
        $this->templateFolders = $config->templateFolders;
        $this->oldFiles        = $config->oldFiles;
        $this->oldFolders      = $config->oldFolders;
        $this->renameTables    = $config->renameTables;
        $this->renameColumns   = $config->renameColumns;
//        $this->moduleStats     = $config->moduleStats;
        $this->modCopyright    = $config->modCopyright;
        //        $this->testimages      = $config->testimages;

        $this->icons = require \dirname(__DIR__, 2) . '/config/icons.php';
        $this->paths = require \dirname(__DIR__, 2) . '/config/paths.php';
    }

    /**
     * load a particular config file
     *
     * @param string $file
     */
    final public function load(string $file): void
    {
        $this->data = require $file;
    }

    /**
     * get a config value
     *
     * @param string     $key
     * @param mixed|null $default
     * @return mixed|null
     */
    final public function get(string $key, $default = null)
    {
        $this->default = $default;

        $sections = \explode('.', $key);
        $data     = $this->data;

        foreach ($sections as $section) {
            if (isset($data[$section])) {
                $data = $data[$section];
            } else {
                $data = $default;
                break;
            }
        }

        return $data;
    }

    /**
     * check if a config value exists
     *
     * @param string $key
     * @return bool
     */
    final public function exists(string $key): bool
    {
        return $this->get($key) !== $this->default;
    }

    /**
     * merge config values replacements
     *
     * @param array $base
     * @param array $replacements
     * @return array|null
     */
    final public function merge(array $base, array $replacements): ?array
    {
        return array_replace_recursive($base, $replacements);
    }
}

