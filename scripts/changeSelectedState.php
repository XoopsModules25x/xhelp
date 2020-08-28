<?php

use Xmf\Request;
use XoopsModules\Xhelp;

require_once dirname(__DIR__, 3) . '/mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}
require_once XHELP_JPSPAN_PATH . '/JPSpan.php';       // Including this sets up the JPSPAN constants
require_once JPSPAN . 'Server/PostOffice.php';      // Load the PostOffice server
//require_once XHELP_BASE_PATH . '/functions.php'; //moved functions to /Utility

// Create the PostOffice server
$server = new JPSpan_Server_PostOffice();
$server->addHandler(new XHelpWebLib());

if (Request::hasVar('QUERY_STRING', 'SERVER') && 0 == strcasecmp($_SERVER['QUERY_STRING'], 'client')) {
    // Compress the output Javascript (e.g. strip whitespace)
    define('JPSPAN_INCLUDE_COMPRESS', true);

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

/**
 * class WebLib
 */
class XHelpWebLib
{
    /**
     * @param $state
     * @return array
     */
    public function statusesByState($state)
    {
        $state   = (int)$state;
        $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);

        if (-1 == $state) {   // If select all is chosen
            $statuses = $hStatus->getObjects(null, true);
        } else {
            $statuses = &$hStatus->getStatusesByState($state);
        }
        $aStatuses   = [];
        $aStatuses[] = [
            'key'   => -1,
            'value' => _XHELP_TEXT_SELECT_ALL,
        ];

        foreach ($statuses as $status) {
            $aStatuses[] = [
                'key'   => $status->getVar('id'),
                'value' => $status->getVar('description'),
            ];
        }

        return $aStatuses;
    }
}
