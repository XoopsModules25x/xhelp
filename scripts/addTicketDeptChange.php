<?php

use Xoopsmodules\xhelp;

require_once __DIR__ . '/../../../mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}
require_once XHELP_JPSPAN_PATH . '/JPSpan.php';       // Including this sets up the JPSPAN constants
require_once JPSPAN . 'Server/PostOffice.php';      // Load the PostOffice server
//require_once XHELP_BASE_PATH . '/functions.php';

// Create the PostOffice server
$server = new JPSpan_Server_PostOffice();
$server->addHandler(new xhelpweblib());

if (isset($_SERVER['QUERY_STRING']) && 0 == strcasecmp($_SERVER['QUERY_STRING'], 'client')) {

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


