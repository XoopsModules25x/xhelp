<?php declare(strict_types=1);

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

//Include the Event Subsystem classes
// require_once XHELP_CLASS_PATH . '/eventService.php';
// require_once XHELP_CLASS_PATH . '/Service.php';
// require_once XHELP_CLASS_PATH . '/cacheService.php';
// require_once XHELP_CLASS_PATH . '/logService.php';
// require_once XHELP_CLASS_PATH . '/NotificationService.php';
// require_once XHELP_CLASS_PATH . '/StaffService.php';
// require_once XHELP_CLASS_PATH . '/firnService.php';

//Create an instance of each event class
//$xhelpEventSrv = Xhelp\Utility::createNewEventService();
$xhelpEventSrv = Xhelp\EventService::getInstance();
$var           = Xhelp\CacheService::getInstance();
$var           = Xhelp\LogService::getInstance();
$var           = Xhelp\NotificationService::getInstance();
$var           = Xhelp\StaffService::getInstance();
$var           = Xhelp\FirnService::getInstance();
unset($var);

// @todo - update every reference to $_eventsrv to use the new $xhelpEventSrv object
$_eventsrv = &$xhelpEventSrv;
