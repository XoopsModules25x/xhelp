<?php
//$Id: events.php,v 1.2 2005/12/02 23:16:09 ackbarr Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

//Include the Event Subsystem classes
require_once(XHELP_CLASS_PATH.'/eventService.php');
require_once(XHELP_CLASS_PATH.'/xhelpService.php');
require_once(XHELP_CLASS_PATH.'/cacheService.php');
require_once(XHELP_CLASS_PATH.'/logService.php');
require_once(XHELP_CLASS_PATH.'/notificationService.php');
require_once(XHELP_CLASS_PATH.'/staffService.php');
require_once(XHELP_CLASS_PATH.'/firnService.php');

//Create an instance of each event class
$xhelpEventSrv =& xhelpNewEventService();
$var = xhelpCacheService::singleton();
$var = xhelpLogService::singleton();
$var = xhelpNotificationService::singleton();
$var = xhelpStaffService::singleton();
$var = xhelpFirnService::singleton();
unset($var);

// @todo - update every reference to $_eventsrv to use the new $xhelpEventSrv object
$_eventsrv =& $xhelpEventSrv;

?>