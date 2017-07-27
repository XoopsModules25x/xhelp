<?php
//

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

//Include the Event Subsystem classes
require_once XHELP_CLASS_PATH . '/eventService.php';
require_once XHELP_CLASS_PATH . '/xhelpService.php';
require_once XHELP_CLASS_PATH . '/cacheService.php';
require_once XHELP_CLASS_PATH . '/logService.php';
require_once XHELP_CLASS_PATH . '/notificationService.php';
require_once XHELP_CLASS_PATH . '/staffService.php';
require_once XHELP_CLASS_PATH . '/firnService.php';

//Create an instance of each event class
$xhelpEventSrv =& xhelpNewEventService();
$var           = xhelpCacheService::getInstance();
$var           = xhelpLogService::getInstance();
$var           = xhelpNotificationService::getInstance();
$var           = xhelpStaffService::getInstance();
$var           = xhelpFirnService::getInstance();
unset($var);

// @todo - update every reference to $_eventsrv to use the new $xhelpEventSrv object
$_eventsrv =& $xhelpEventSrv;
