<?php

use XoopsModules\Xhelp;

//include('header.php');
require_once __DIR__ . '/../../mainfile.php';

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    require_once XOOPS_ROOT_PATH . '/modules/xhelp/include/constants.php';
}

//require_once XHELP_BASE_PATH . '/functions.php';

if (!$xoopsUser) {
    redirect_header(XOOPS_URL . '/user.php?xoops_redirect=' . htmlencode($xoopsRequestUri), 3);
}

if (\Xmf\Request::hasVar('id', 'GET')) {
 $xhelp_id = \Xmf\Request::getInt('id', 0, 'GET');
}

$viewFile = false;

$hFiles   = new Xhelp\FileHandler($GLOBALS['xoopsDB']);
$hTicket  = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
$hStaff   = new Xhelp\StaffHandler($GLOBALS['xoopsDB']);
$file     =& $hFiles->get($xhelp_id);
$mimeType = $file->getVar('mimetype');
$ticket   =& $hTicket->get($file->getVar('ticketid'));

$filename_full = $file->getVar('filename');
if ($file->getVar('responseid') > 0) {
    $removeText = $file->getVar('ticketid') . '_' . $file->getVar('responseid') . '_';
} else {
    $removeText = $file->getVar('ticketid') . '_';
}
$filename = str_replace($removeText, '', $filename_full);

//Security:
// Only Staff Members, Admins, or ticket Submitter should be able to see file
if (_userAllowed($ticket, $xoopsUser)) {
    $viewFile = true;
} elseif ($hStaff->isStaff($xoopsUser->getVar('uid'))) {
    $viewFile = true;
} elseif ($xoopsUser->isAdmin($xoopsModule->getVar('mid'))) {
    $viewFile = true;
}

if (!$viewFile) {
    redirect_header(XHELP_BASE_URL . '/index.php', 3, _NOPERM);
}

//Check if the file exists
$fileAbsPath = XHELP_UPLOAD_PATH . '/' . $filename_full;
if (!file_exists($fileAbsPath)) {
    redirect_header(XHELP_BASE_URL . '/index.php', 3, _XHELP_NO_FILES_ERROR);
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
if (isset($mimeType) && false !== strpos($mimeType, 'text/')) {
    $fp = fopen($fileAbsPath, 'rb');
} else {
    $fp = fopen($fileAbsPath, 'rb');
}

// Write file to browser
fpassthru($fp);

/**
 * @param $ticket
 * @param $user
 * @return bool
 */
function _userAllowed(&$ticket, &$user)
{
    $emails =& $ticket->getEmails(true);
    foreach ($emails as $email) {
        if ($email->getVar('email') == $user->getVar('email')) {
            return true;
        }
    }

    return false;
}
