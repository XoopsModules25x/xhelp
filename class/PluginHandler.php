<?php

namespace XoopsModules\Xhelp;

use XoopsModules\Xhelp;

/**
 * Manages the retrieval, loading, and unloading of plugins
 *
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @access  public
 * @package xhelp
 */
class PluginHandler
{
    /**
     * Database connection
     *
     * @var object
     * @access  private
     */
    public $_db;
    public $_active;
    public $_plugins;

    /**
     * Xhelp\PluginHandler constructor.
     * @param \XoopsDatabase|null $db
     */
    public function __construct(\XoopsDatabase $db = null)
    {
        $this->_db     = $db;
        $this->_active = \unserialize(Xhelp\Utility::getMeta('plugins'));
    }

    /**
     * @return array
     */
    public function _pluginList()
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

    public function getActivePlugins()
    {
        $plugin_files = $this->_pluginList();

        foreach ($plugin_files as $plugin) {
            if (\in_array($plugin, $this->_active)) {
            }
        }
    }

    /**
     * @param $script
     */
    public function activatePlugin($script)
    {
    }

    /**
     * @param $script
     */
    public function deactivatePlugin($script)
    {
    }

    /**
     * @param $filename
     * @return bool
     */
    public function getPluginInstance($filename)
    {
        if (!isset($this->_plugins[$filename])) {
            if (\is_file($plug_file = XHELP_PLUGIN_PATH . '/' . $filename . '.php')) {
                require_once $plug_file;
            }
            $class = mb_strtolower(XHELP_DIRNAME) . \ucfirst($filename);
            if (\class_exists($class)) {
                $this->_plugins[$filename] = new $class($GLOBALS['_eventsrv']);
            }
        }
        if (!isset($this->_plugins[$filename])) {
            \trigger_error('Plugin does not exist<br>Module: ' . XHELP_DIRNAME . '<br>Name: ' . $filename, \E_USER_ERROR);
        }

        return $this->_plugins[$filename] ?? false;
    }
}
