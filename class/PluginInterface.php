<?php

namespace XoopsModules\Xhelp;

/**
 * Plugin Interface
 *
 * Defines the interface for xhelp plugins
 * @author  Brian Wahoff <ackbarr@xoops.org>
 * @todo    Localization of meta information
 */
interface PluginInterface
{
    /**
     * Retrieve the specified meta field
     * @param string $var name of variable to return
     * @return string if var is set, false if not
     */
    public function getMeta($var);

    /**
     * @param $var
     * @param $value
     */
    public function setMeta($var, $value);

    /**
     * Initialization function, triggered when a plugin is "loaded" by the system
     */
    public function onLoad();

    /**
     * Destruction function, triggered when a plugin is "un-loaded" by the system
     */
    public function onUnload();

    /**
     * Add a function to be called when an event is triggered by the system
     * @param $event_ctx
     * @param $event_func
     */
    public function registerEventHandler($event_ctx, $event_func);

    /**
     * Only have 1 instance of class used
     * @return Plugin {@link Plugin}
     */
    public static function getInstance(): Plugin;
}
