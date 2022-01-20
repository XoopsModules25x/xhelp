<?php declare(strict_types=1);

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
     * @return string|bool if var is set, false if not
     */
    public function getMeta(string $var);

    /**
     * @param string $var
     * @param string $value
     */
    public function setMeta(string $var, string $value);

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
     * @param string $event_ctx
     * @param string $event_func
     */
    public function registerEventHandler(string $event_ctx, string $event_func);

    /**
     * Only have 1 instance of class used
     * @return Plugin {@link Plugin}
     */
    public static function getInstance(): Plugin;
}
