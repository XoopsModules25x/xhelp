<?php
//include('header.php');
require('../../mainfile.php');


if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    include_once(XOOPS_ROOT_PATH.'/modules/xhelp/include/constants.php');
}

include_once(XHELP_BASE_PATH.'/functions.php');

if(!$xoopsUser) {
    redirect_header(XOOPS_URL .'/user.php?xoops_redirect='.htmlencode($xoopsRequestUri), 3);
}

if(isset($_GET['id'])){
    $xhelp_id = intval($_GET['id']);
}

$viewFile = false;

$hFiles   =& xhelpGetHandler('file');
$hTicket  =& xhelpGetHandler('ticket');
$hStaff   =& xhelpGetHandler('staff');
$file     =& $hFiles->get($xhelp_id);
$mimeType = $file->getVar('mimetype');
$ticket   =& $hTicket->get($file->getVar('ticketid'));

$filename_full = $file->getVar('filename');
if($file->getVar('responseid') > 0){
    $removeText = $file->getVar('ticketid')."_".$file->getVar('responseid')."_";
} else {
    $removeText = $file->getVar('ticketid')."_";
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
    redirect_header(XHELP_BASE_URL.'/index.php', 3, _NOPERM);
}

//Check if the file exists
$fileAbsPath = XHELP_UPLOAD_PATH . '/'. $filename_full;
if (!file_exists($fileAbsPath)) {
    redirect_header(XHELP_BASE_URL.'/index.php', 3, _XHELP_NO_FILES_ERROR);
    exit();
}

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false);
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($fileAbsPath));

if(isset($mimeType)) {
    header("Content-Type: " . $mimeType);
} else {
    header("Content-Type: application/octet-stream");
}

// Add Header to set filename
header("Content-Disposition: attachment; filename=" . $filename);

// Open the file
if(isset($mimeType) && strstr($mimeType, "text/")) {
    $fp = fopen($fileAbsPath, "r");
} else {
    $fp = fopen($fileAbsPath, "rb");
}

// Write file to browser
fpassthru($fp);


function _userAllowed(&$ticket, &$user) {
    $emails =& $ticket->getEmails(true);
    foreach($emails as $email) {
        if ($email->getVar('email') == $user->getVar('email')) {
            return true;
        }
    }
    return false;
}
?>