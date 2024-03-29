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

use Xmf\Module\Admin;
use Xmf\Request;
use XoopsModules\Xhelp\{
    Helper,
    FaqAdapterFactory,
    Utility
};

require_once __DIR__ . '/admin_header.php';
xoops_load('XoopsPagenav');
// require_once XHELP_CLASS_PATH . '/faqAdapterFactory.php';
// require_once XHELP_CLASS_PATH . '/faqAdapter.php';

$op = 'default';

if (Request::hasVar('op', 'REQUEST')) {
    $op = $_REQUEST['op'];
}

switch ($op) {
    case 'updateActive':
        updateActive();
        break;
    case 'manage':
    default:
        manage();
        break;
}

/**
 *
 */
function manage()
{
    global $icons;
    $faqAdapters = FaqAdapterFactory::installedAdapters();
    $myAdapter   = FaqAdapterFactory::getFaqAdapter();
    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manFaqAdapters');
    $adminObject = Admin::getInstance();
    $adminObject->displayNavigation(basename(__FILE__));

    echo "<form method='post' action='" . XHELP_ADMIN_URL . "/faqAdapter.php?op=updateActive'>";
    echo "<table width='100%' cellspacing='1' class='outer'>";

    if (!empty($faqAdapters)) {
        echo "<tr><th colspan='5'>" . _AM_XHELP_MENU_MANAGE_FAQ . '</th></tr>';
        echo "<tr class='head'>
                  <td>" . _AM_XHELP_TEXT_NAME . '</td>
                  <td>' . _AM_XHELP_TEXT_PLUGIN_VERSION . '</td>
                  <td>' . _AM_XHELP_TEXT_TESTED_VERSIONS . '</td>
                  <td>' . _AM_XHELP_TEXT_AUTHOR . '</td>
                  <td>' . _AM_XHELP_TEXT_ACTIVE . '</td>
              </tr>';

        $activeAdapter = Utility::getMeta('faq_adapter');
        foreach ($faqAdapters as $name => $oAdapter) {
            $modname     = $name;
            $author      = $oAdapter->meta['author'];
            $author_name = $author;

            if ('' != $oAdapter->meta['url']) {                                                         // If a website is specified
                $name = "<a href='" . $oAdapter->meta['url'] . "'>" . $oAdapter->meta['name'] . '</a>'; // Add link to module name
            }
            if ('' != $oAdapter->meta['author_email']) {
                $author = "<a href='mailto:" . $oAdapter->meta['author_email'] . "'>" . $author_name . '</a>';  // Add link to email author
            }
            echo "<tr class='even'>
                      <td>" . $name . '</td>
                      <td>' . $oAdapter->meta['version'] . '</td>
                      <td>' . $oAdapter->meta['tested_versions'] . '</td>
                      <td>' . $author . "</td>
                      <td>
                          <input type='image' src='" . ($activeAdapter == $modname ? XHELP_IMAGE_URL . '/on.png' : XHELP_IMAGE_URL . '/off.png') . "' name='modname' value='" . $modname . "' style='border:0;background:transparent;'>
                      </td>
                  </tr>";
        }
    } else {
        // Display "no adapters found" message
        echo '<tr><th>' . _AM_XHELP_MENU_MANAGE_FAQ . '</th></tr>';
        echo "<tr><td class='even'>" . _AM_XHELP_TEXT_NO_FILES . '</td></tr>';
    }
    echo '</table></form>';

    if (is_object($myAdapter)) {
        $faq = $myAdapter->createFaq();
    }

    require_once __DIR__ . '/admin_footer.php';
}

/**
 *
 */
function updateActive()
{
    $helper = Helper::getInstance();
    if ('' !== Request::getString('modname', '', 'POST')) {
        $helper->redirect('admin/faqAdapter.php', 3, _AM_XHELP_MESSAGE_NO_NAME);
    } else {
        $modname = Request::getString('modname', '', 'POST');
    }

    $currentAdapter = Utility::getMeta('faq_adapter');
    if ($currentAdapter == $modname) {    // Deactivate current adapter?
        $ret = Utility::deleteMeta('faq_adapter');
    } else {
        $ret = FaqAdapterFactory::setFaqAdapter($modname);
    }

    if ($ret) {
        $helper->redirect('admin/faqAdapter.php');
    } else {
        $helper->redirect('admin/faqAdapter.php', 3, _AM_XHELP_MSG_INSTALL_MODULE);
    }
}
