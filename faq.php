<?php declare(strict_types=1);

use Xmf\Request;
use XoopsModules\Xhelp;

require_once __DIR__ . '/header.php';

$helper = Xhelp\Helper::getInstance();

$op = 'default';
//require_once XHELP_INCLUDE_PATH . '/events.php';
// require_once XHELP_CLASS_PATH . '/faqAdapterFactory.php';
// require_once XHELP_CLASS_PATH . '/faqCategory.php';
// require_once XHELP_CLASS_PATH . '/Tree.php';

if (Request::hasVar('op', 'REQUEST')) {
    $op = Request::getString('op', 'default');
}

if (!$xoopsUser) {
    $helper->redirect('', 3, _NOPERM);
} elseif (!$xhelp_isStaff) {
    $helper->redirect('', 3, _NOPERM);
}

switch ($op) {
    case 'add':
        if (isset($_POST['addFaq'])) {
            addFaq_action();
        } else {
            addFaq_display();
        }
        break;
    default:
        addFaq_display();
        break;
}

/**
 *
 */
function addFaq_display()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xoopsLogger, $xoopsUserIsAdmin, $session, $staff;
    $helper = Xhelp\Helper::getInstance();

    if (!isset($_POST['ticketid']) && 0 === Request::getInt('ticketid', 0, 'POST')) {
        $helper->redirect('', 3, _XHELP_MSG_NO_ID);
    }
    $ticketid = Request::getInt('ticketid', 0, 'POST');
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');
    /** @var \XoopsModules\Xhelp\ResponseHandler $responseHandler */
    $responseHandler = $helper->getHandler('Response');
    $ticket          = $ticketHandler->get($ticketid);

    if (!$hasRights = $staff->checkRoleRights(XHELP_SEC_FAQ_ADD, $ticket->getVar('department'))) {
        $helper->redirect("ticket.php?id=$ticketid", 3, _AM_XHELP_MESSAGE_NO_ADD_FAQ);
    }

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_addFaq.tpl';
    require_once XOOPS_ROOT_PATH . '/header.php';

    $criteria     = new \Criteria('ticketid', $ticketid);
    $responses    = $responseHandler->getObjects($criteria, true);
    $responseText = '';

    $allUsers = [];
    foreach ($responses as $response) {
        $allUsers[$response->getVar('uid')] = '';
    }

    $criteria = new \Criteria('uid', '(' . implode(',', array_keys($allUsers)) . ')', 'IN');
    $users    = Xhelp\Utility::getUsers($criteria, $helper->getConfig('xhelp_displayName'));
    unset($allUsers);

    foreach ($responses as $response) {
        $responseText .= sprintf(_XHELP_TEXT_USER_SAID, $users[$response->getVar('uid')]) . "\n";
        $responseText .= $response->getVar('message', 'e') . "\n";
    }

    // Get current faq adapter
    /** @var \XoopsModules\Xhelp\FaqAdapterAbstract $oAdapter */
    $oAdapter = Xhelp\FaqAdapterFactory::getFaqAdapter();
    if (!$oAdapter) {
        $helper->redirect('', 3, _XHELP_MESSAGE_NO_FAQ);
    }
    $categories = &$oAdapter->getCategories();

    $tree = new Xhelp\Tree($categories, 'id', 'parent');

    //    $xoopsTpl->assign('xhelp_categories', $tree->makeSelBox('categories', 'name', '--', 0, false, 0, $oAdapter->categoryType));

    $categorySelect = $tree->makeSelectElement('categories', 'name', '--', 0, false, 0, $oAdapter->categoryType, '');
    $xoopsTpl->assign('xhelp_categories', $categorySelect->render());

    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);
    $xoopsTpl->assign('xhelp_faqProblem', $ticket->getVar('description', 'e'));
    $xoopsTpl->assign('xhelp_faqSolution', $responseText);
    $xoopsTpl->assign('xhelp_hasMultiCats', $oAdapter->categoryType);
    $xoopsTpl->assign('xhelp_ticketID', $ticketid);
    $xoopsTpl->assign('xhelp_faqSubject', $ticket->getVar('subject', 'e'));

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

/**
 *
 */
function addFaq_action()
{
    global $xoopsUser, $eventService;
    $helper = Xhelp\Helper::getInstance();
    /** @var \XoopsModules\Xhelp\TicketHandler $ticketHandler */
    $ticketHandler = $helper->getHandler('Ticket');

    // Retrieve ticket information
    $ticketid = $_POST['ticketid'];
    $ticket   = $ticketHandler->get($ticketid);

    $adapter = Xhelp\FaqAdapterFactory::getFaqAdapter();
    $faq     = $adapter->createFaq();

    // @todo - Make subject user editable
    $faq->setVar('subject', $_POST['subject']);
    $faq->setVar('problem', $_POST['problem']);
    $faq->setVar('solution', $_POST['solution']);
    // BTW - XOBJ_DTYPE_ARRAY vars must be serialized prior to calling setVar in XOOPS 2.0
    $faq->setVar('categories', serialize($_POST['categories']));

    if ($adapter->storeFaq($faq)) {
        // Todo: Run events here
        $eventService->trigger('new_faq', [&$ticket, &$faq]);

        $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ADD_FAQ);
    } else {
        $helper->redirect("ticket.php?id=$ticketid", 3, _XHELP_MESSAGE_ERR_ADD_FAQ);
    }
}
