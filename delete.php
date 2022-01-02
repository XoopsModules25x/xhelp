<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';
//require_once XHELP_BASE_PATH . '/functions.php';

/**
 * @todo move these into ticket.php and profile.php respectivly
 */
if ($xoopsUser) {
    $uid = $xoopsUser->getVar('uid');

    if (Request::hasVar('delete_ticket', 'POST')) {
        $ticketHandler = Xhelp\Helper::getInstance()->getHandler('Ticket');
        if (Request::hasVar('ticketid', 'POST')) {
            $xhelp_id = $_POST['ticketid'];
        }
        $ticketInfo = $ticketHandler->get($xhelp_id);      // Retrieve ticket information
        if ($ticketHandler->delete($ticketInfo)) {
            $message = _XHELP_MESSAGE_DELETE_TICKET;
            $_eventsrv->trigger('delete_ticket', [&$ticketInfo]);
        } else {
            $message = _XHELP_MESSAGE_DELETE_TICKET_ERROR;
        }
        redirect_header(XHELP_BASE_URL . '/index.php', 3, $message);
    } elseif (Request::hasVar('delete_responseTpl', 'POST')) {
        //Should only the owner of a template be able to delete it?
        $responseTemplatesHandler = Xhelp\Helper::getInstance()->getHandler('ResponseTemplates');
        $displayTpl               = $responseTemplatesHandler->get($_POST['tplID']);
        if ($xoopsUser->getVar('uid') != $displayTpl->getVar('uid')) {
            $message = _NOPERM;
        } else {
            if ($responseTemplatesHandler->delete($displayTpl)) {
                $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL;
                $_eventsrv->trigger('delete_responseTpl', [$displayTpl]);
            } else {
                $message = _XHELP_MESSAGE_DELETE_RESPONSE_TPL_ERROR;
            }
        }
        redirect_header(XHELP_BASE_URL . '/profile.php', 3, $message);
    }
} else {    // If not a user
    redirect_header(XOOPS_URL . '/user.php', 3);
}
