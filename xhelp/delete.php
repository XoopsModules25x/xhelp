<?php
//$Id: delete.php,v 1.19 2005/12/01 22:36:21 ackbarr Exp $
require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');
include_once(XHELP_BASE_PATH.'/functions.php');

/**
 * @todo move these into ticket.php and profile.php respectivly
 */
if($xoopsUser){
    $uid = $xoopsUser->getVar('uid');

    if(isset($_POST['delete_ticket'])){
        $hTicket =& xhelpGetHandler('ticket');
        if(isset($_POST['ticketid'])){
            $xhelp_id = $_POST['ticketid'];
        }
        $ticketInfo =& $hTicket->get($xhelp_id);      // Retrieve ticket information
        if($hTicket->delete(& $ticketInfo)){
            $message = _XHELP_MESSAGE_DELETE_TICKET;
            $_eventsrv->trigger('delete_ticket', array(&$ticketInfo));
        } else {
            $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
        }
        redirect_header(XHELP_BASE_URL.'/index.php', 3, $message);
    } elseif(isset($_POST['delete_responseTpl'])){
        //Should only the owner of a template be able to delete it?
        $hResponseTpl = xhelpGetHandler('responseTemplates');
        $displayTpl =& $hResponseTpl->get($_POST['tplID']);
        if ($xoopsUser->getVar('uid') != $displayTpl->getVar('uid')) {
            $message = _NOPERM;
        } else {

            if($hResponseTpl->delete($displayTpl)){
                $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL;
                $_eventsrv->trigger('delete_responseTpl', array($displayTpl));
            } else {
                $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL_ERROR;
            }
        }
        redirect_header(XHELP_BASE_URL."/profile.php", 3, $message);
    }
} else {    // If not a user
    redirect_header(XOOPS_URL .'/user.php', 3);
}

?>