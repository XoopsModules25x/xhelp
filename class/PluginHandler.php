<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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
 * @author       XOOPS Development Team
 */

/**
 * Manages the retrieval, loading, and unloading of plugins
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 */
class PluginHandler
{
    /**
     * Database connection
     *
     * @var object
     */
    public $db;
    public $active;
    public $plugins;

    /**
     * PluginHandler constructor.
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        $this->db     = $db;
        $this->active = \unserialize(Utility::getMeta('plugins'));
    }

    /**
     * @return array
     */
    private function pluginList(): array
    {
        $plugins = [];
        //Open Directory
        $d = @\dir(XHELP_PLUGIN_PATH);

        if ($d) {
            while (false !== ($entry = $d->read())) {
                if (!\preg_match('|^\.+$|', $entry) && \preg_match('|\.php$|', $entry)) {
                    $plugins[] = \basename(XHELP_PLUGIN_PATH . '/' . $entry, '.php');
                }
            }
        }

        return $plugins;
    }

    public function getActivePlugins(): void
    {
        $plugin_files = $this->pluginList();

        foreach ($plugin_files as $plugin) {
            if (\in_array($plugin, $this->active)) {
            }
        }
    }

    /**
     * @param string $script
     */
    public function activatePlugin(string $script): void
    {
    }

    /**
     * @param string $script
     */
    public function deactivatePlugin(string $script): void
    {
    }

    /**
     * @param string $filename
     * @return bool
     */
    public function getPluginInstance(string $filename): bool
    {
        if (!isset($this->plugins[$filename])) {
            if (\is_file($plug_file = XHELP_PLUGIN_PATH . '/' . $filename . '.php')) {
                require_once $plug_file;
            }
            $class = \mb_strtolower(XHELP_DIRNAME) . \ucfirst($filename);
            if (\class_exists($class)) {
                $this->plugins[$filename] = new $class($GLOBALS['_eventsrv']);
            }
        }
        if (!isset($this->plugins[$filename])) {
            \trigger_error('Plugin does not exist<br>Module: ' . XHELP_DIRNAME . '<br>Name: ' . $filename, \E_USER_ERROR);
        }

        return $this->plugins[$filename] ?? false;
    }
}
