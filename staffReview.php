<?php
//$Id: staffReview.php,v 1.16 2005/12/01 22:36:21 ackbarr Exp $
require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');

if($xoopsUser){
    if(isset($_POST['submit'])){
        if(isset($_POST['staffid'])){
            $staffid = intval($_POST['staffid']);
        }
        if(isset($_POST['ticketid'])){
            $ticketid = intval($_POST['ticketid']);
        }
        if(isset($_POST['responseid'])){
            $responseid = intval($_POST['responseid']);
        }
        if(isset($_POST['rating'])){
            $rating = intval($_POST['rating']);
        }
        if(isset($_POST['comments'])){
            $comments = $_POST['comments'];
        }
        $hStaffReview =& xhelpGetHandler('staffReview');
        $hTicket =& xhelpGetHandler('ticket');
        $hResponse =& xhelpGetHandler('responses');

        $review =& $hStaffReview->create();
        $review->setVar('staffid', $staffid);
        $review->setVar('rating', $rating);
        $review->setVar('ticketid', $ticketid);
        $review->setVar('responseid', $responseid);
        $review->setVar('comments', $comments);
        $review->setVar('submittedBy', $xoopsUser->getVar('uid'));
        $review->setVar('userIP', getenv("REMOTE_ADDR"));
        if($hStaffReview->insert($review)){
            $message = _XHELP_MESSAGE_ADD_STAFFREVIEW;
            $ticket =& $hTicket->get($ticketid);
            $response =& $hResponse->get($responseid);
            $_eventsrv->trigger('new_response_rating', array(&$review, &$ticket, &$response));
        } else {
            $message = _XHELP_MESSAGE_ADD_STAFFREVIEW_ERROR;
        }
        redirect_header(XHELP_BASE_URL."/ticket.php?id=$ticketid", 3, $message);
    } else {
        $xoopsOption['template_main'] = 'xhelp_staffReview.html';   // Set template
        require(XOOPS_ROOT_PATH.'/header.php');                     // Include

        if(isset($_GET['staff'])){
            $xoopsTpl->assign('xhelp_staffid', intval($_GET['staff']));
        }
        if(isset($_GET['ticketid'])){
            $xoopsTpl->assign('xhelp_ticketid', intval($_GET['ticketid']));
        }
        if(isset($_GET['responseid'])){
            $xoopsTpl->assign('xhelp_responseid', intval($_GET['responseid']));
        }

        $xoopsTpl->assign('xhelp_imagePath', XOOPS_URL . '/modules/xhelp/images/');
        $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);
        $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

        require(XOOPS_ROOT_PATH.'/footer.php');
    }
} else {    // If not a user
    redirect_header(XOOPS_URL .'/user.php', 3);
}
