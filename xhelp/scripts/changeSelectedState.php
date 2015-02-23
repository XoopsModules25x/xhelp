<?php
require('../../../mainfile.php');

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    include_once(XOOPS_ROOT_PATH.'/modules/xhelp/include/constants.php');
}
require_once XHELP_JPSPAN_PATH.'/JPSpan.php';       // Including this sets up the JPSPAN constants
require_once JPSPAN . 'Server/PostOffice.php';      // Load the PostOffice server
include_once(XHELP_BASE_PATH.'/functions.php');

// Create the PostOffice server
$server = & new JPSpan_Server_PostOffice();
$server->addHandler(new xhelpWebLib());

if (isset($_SERVER['QUERY_STRING']) &&
strcasecmp($_SERVER['QUERY_STRING'], 'client')==0) {

    // Compress the output Javascript (e.g. strip whitespace)
    define('JPSPAN_INCLUDE_COMPRESS',TRUE);

    // Display the Javascript client
    $server->displayClient();
} else {
    // This is where the real serving happens...
    // Include error handler
    // PHP errors, warnings and notices serialized to JS
    require_once JPSPAN . 'ErrorHandler.php';

    // Start serving requests...
    $server->serve();
}

class xhelpWebLib {
    function statusesByState($state)
    {
        $state = intval($state);
        $hStatus =& xhelpGetHandler('status');

        if($state == -1){   // If select all is chosen
            $statuses =& $hStatus->getObjects(null, true);
        } else {
            $statuses =& $hStatus->getStatusesByState($state);
        }
        $aStatuses = array();
        $aStatuses[] = array('key' => -1,
                             'value' => _XHELP_TEXT_SELECT_ALL);
         
        foreach($statuses as $status){
            $aStatuses[] = array('key' => $status->getVar('id'),
                                 'value' => $status->getVar('description'));
        }

        return $aStatuses;
    }
}
?>