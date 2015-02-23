<?php
require('../../mainfile.php');

if (!defined('XHELP_CONSTANTS_INCLUDED')) {
    include_once(XOOPS_ROOT_PATH.'/modules/xhelp/include/constants.php');
}

include_once(XHELP_BASE_PATH.'/functions.php');
require_once(XHELP_CLASS_PATH.'/session.php');
require_once(XHELP_CLASS_PATH.'/eventService.php');

$_xhelpSession = new Session();

$roleReset = false;
$xhelp_isStaff = false;

// Is the current user a staff member?
if($xoopsUser){
    $hStaff =& xhelpGetHandler('staff');
    if($xhelp_staff =& $hStaff->getByUid($xoopsUser->getVar('uid'))){
        $xhelp_isStaff = true;

        // Check if the staff member permissions have changed since the last page request
        if(!$myTime = $_xhelpSession->get("xhelp_permTime")){
            $roleReset = true;
        } else {
            $dbTime = $xhelp_staff->getVar('permTimestamp');
            if($dbTime > $myTime){
                $roleReset = true;
            }
        }

        // Update staff member permissions (if necessary)
        if($roleReset){
            $updateRoles = $xhelp_staff->resetRoleRights();
            $_xhelpSession->set("xhelp_permTime", time());
        }

        //Retrieve the staff member's saved searches
        if(!$aSavedSearches = $_xhelpSession->get("xhelp_savedSearches")){
            $aSavedSearches =& xhelpGetSavedSearches($xoopsUser->getVar('uid'));
            $_xhelpSession->set('xhelp_savedSearches', $aSavedSearches);
        }
    }
}

$xhelp_module_css = XHELP_BASE_URL . '/styles/xhelp.css';
$xhelp_module_header = '<link rel="stylesheet" type="text/css" media="all" href="'.$xhelp_module_css.'" /><!--[if gte IE 5.5000]><script src="iepngfix.js" language="JavaScript" type="text/javascript"></script><![endif]-->';

// @todo - this line is for compatiblity, remove once all references to $isStaff have been modified
//$isStaff = $xhelp_isStaff;


?>