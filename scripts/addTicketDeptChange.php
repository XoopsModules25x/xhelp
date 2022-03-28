<?php declare(strict_types=1);

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
 * @author       Eric Juden <ericj@epcusa.com>
 * @author       XOOPS Development Team
 */

use Xmf\Request;
use XoopsModules\Xhelp;

require_once \dirname(__DIR__, 3) . '/mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}
require_once XHELP_JPSPAN_PATH . '/JPSpan.php';       // Including this sets up the JPSPAN constants
require_once JPSPAN . 'Server/PostOffice.php';        // Load the PostOffice server
//require_once XHELP_BASE_PATH . '/functions.php';

// Create the PostOffice server
$server = new JPSpan_Server_PostOffice();
$webLib = new Xhelp\WebLib();
$server->addHandler($webLib);

if (Request::hasVar('QUERY_STRING', 'SERVER') && 0 === strcasecmp($_SERVER['QUERY_STRING'], 'client')) {
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
