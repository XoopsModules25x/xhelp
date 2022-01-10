<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require __DIR__ . '/header.php';
require_once \dirname(__DIR__, 2) . '/mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once XHELP_BASE_PATH . '/functions.php';

global $xoopsUser, $xoopsDB, $xoopsConfig, $xoopsModuleConfig, $xoopsModule, $xoopsTpl, $xoopsRequestUri;
$helper = Xhelp\Helper::getInstance();

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php?xoops_redirect=' . htmlspecialchars($xoopsRequestUri, ENT_QUOTES | ENT_HTML5), 3);
}
$xhelp_id = 0;

if (Request::hasVar('id', 'GET')) {
    $xhelp_id = Request::getInt('id', 0, 'GET');
}

$viewFile = false;

/** @var \XoopsModules\Xhelp\FileHandler $fileHandler */
$fileHandler = $helper->getHandler('File');
/** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
$ticketHandler = $helper->getHandler('Ticket');
/** @var \XoopsModules\Xhelp\StaffHandler $staffHandler */
$staffHandler = $helper->getHandler('Staff');
$file         = $fileHandler->get($xhelp_id);
$mimeType     = $file->getVar('mimetype');
$ticket       = $ticketHandler->get($file->getVar('ticketid'));

$filename_full = $file->getVar('filename');
if ($file->getVar('responseid') > 0) {
    $removeText = $file->getVar('ticketid') . '_' . $file->getVar('responseid') . '_';
} else {
    $removeText = $file->getVar('ticketid') . '_';
}
$filename = str_replace($removeText, '', $filename_full);

//Security:
// Only Staff Members, Admins, or ticket Submitter should be able to see file
if (userAllowed($ticket, $xoopsUser)) {
    $viewFile = true;
} elseif ($staffHandler->isStaff($xoopsUser->getVar('uid'))) {
    $viewFile = true;
} elseif ($xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    $viewFile = true;
}

if (!$viewFile) {
    $helper->redirect('index.php', 3, _NOPERM);
}

//Check if the file exists
$fileAbsPath = XHELP_UPLOAD_PATH . '/' . $filename_full;
if (!file_exists($fileAbsPath)) {
    $helper->redirect('index.php', 3, _XHELP_NO_FILES_ERROR);
}

header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false);
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($fileAbsPath));

if (isset($mimeType)) {
    header('Content-Type: ' . $mimeType);
} else {
    header('Content-Type: application/octet-stream');
}

// Add Header to set filename
header('Content-Disposition: attachment; filename=' . $filename);

// Open the file
if (isset($mimeType) && false !== mb_strpos($mimeType, 'text/')) {
    $fp = fopen($fileAbsPath, 'rb');
} else {
    $fp = fopen($fileAbsPath, 'rb');
}

// Write file to browser
fpassthru($fp);

/**
 * @param Xhelp\Ticket $ticket
 * @param XoopsUser    $user
 * @return bool
 */
function userAllowed(Xhelp\Ticket $ticket, XoopsUser $user): bool
{
    $emails = $ticket->getEmails(true);
    foreach ($emails as $email) {
        if ($email->getVar('email') == $user->getVar('email')) {
            return true;
        }
    }

    return false;
}
