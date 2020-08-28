<?php

use XoopsModules\Xhelp;

require_once __DIR__ . '/admin_header.php';
require_once XOOPS_ROOT_PATH . '/class/pagenav.php';
// require_once XHELP_CLASS_PATH . '/Form.php';

global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');

$limit = \Xmf\Request::getInt('limit', 0, 'REQUEST');
$start = \Xmf\Request::getInt('start', 0, 'REQUEST');

if (!$limit) {
    $limit = 15;
}
if (\Xmf\Request::hasVar('order', 'REQUEST')) {
    $order = $_REQUEST['order'];
} else {
    $order = 'ASC';
}
if (\Xmf\Request::hasVar('sort', 'REQUEST')) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = 'id';
}

$aSortBy  = [
    'id'          => _AM_XHELP_TEXT_ID,
    'description' => _AM_XHELP_TEXT_DESCRIPTION,
    'state'       => _AM_XHELP_TEXT_STATE,
];
$aOrderBy = ['ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING];
$aLimitBy = ['10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100];

$op = 'default';

if (\Xmf\Request::hasVar('op', 'REQUEST')) {
    $op = $_REQUEST['op'];
}

switch ($op) {
    case 'deleteStatus':
        deleteStatus();
        break;
    case 'editStatus':
        editStatus();
        break;
    case 'manageStatus':
        manageStatus();
        break;
    default:
        redirect_header(XHELP_ADMIN_URL . '/index.php');
        break;
}

function deleteStatus()
{
    if (\Xmf\Request::hasVar('statusid', 'GET')) {
        $statusid = \Xmf\Request::getInt('statusid', 0, 'GET');
    } else {
        redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus');
    }

    $hTickets = new Xhelp\TicketHandler($GLOBALS['xoopsDB']);
    $hStatus  = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
    $status   = $hStatus->get($statusid);

    // Check for tickets with this status first
    $crit        = new \Criteria('status', $statusid);
    $ticketCount = $hTickets->getCount($crit);

    if ($ticketCount > 0) {
        redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, _AM_XHELP_STATUS_HASTICKETS_ERR);
    }

    if ($hStatus->delete($status, true)) {
        redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus');
    } else {
        $message = _AM_XHELP_DEL_STATUS_ERR;
        redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, $message);
    }
}

function editStatus()
{
    if (\Xmf\Request::hasVar('statusid', 'REQUEST')) {
        $statusid = \Xmf\Request::getInt('statusid', 0, 'REQUEST');
    } else {
        redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus');
    }

    $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);
    $status  = $hStatus->get($statusid);

    if (!isset($_POST['updateStatus'])) {
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('modTpl');
        $adminObject = \Xmf\Module\Admin::getInstance();
        $adminObject->displayNavigation('status.php?op=editStatus');

        echo "<form method='post' action='" . XHELP_ADMIN_URL . '/status.php?op=editStatus&amp;statusid=' . $statusid . "'>";
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label>" . _AM_XHELP_TEXT_EDIT_STATUS . '</label></th></tr>';
        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_DESCRIPTION . "</td>
                  <td class='even'>
                      <input type='text' name='desc' value='" . $status->getVar('description', 'e') . "' class='formButton'>
                  </td>
              </tr>";
        echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_STATE . "</td><td class='even'>
                  <select name='state'>";
        if (1 == $status->getVar('state')) {
            echo "<option value='1' selected>" . Xhelp\Utility::getState(1) . "</option>
                          <option value='2'>" . Xhelp\Utility::getState(2) . '</option>';
        } else {
            echo "<option value='1'>" . Xhelp\Utility::getState(1) . "</option>
                          <option value='2' selected>" . Xhelp\Utility::getState(2) . '</option>';
        }
        echo '</select></td></tr>';
        echo "<tr><td class='foot' colspan='2'><input type='submit' name='updateStatus' value='" . _AM_XHELP_BUTTON_UPDATE . "' class='formButton'></td></tr>";
        echo '</table></form>';

        require_once __DIR__ . '/admin_footer.php';
    } else {
        if ('' == $_POST['desc']) {  // If no description supplied
            $message = _AM_XHELP_MESSAGE_NO_DESC;
            redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, $message);
        }

        $status->setVar('description', $_POST['desc']);
        $status->setVar('state', $_POST['state']);
        if ($hStatus->insert($status)) {
            redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus');
        } else {
            $message = _AM_MESSAGE_EDIT_STATUS_ERR;
            readirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, $message);
        }
    }
}

function manageStatus()
{
    global $aSortBy, $aOrderBy, $aLimitBy, $order, $limit, $start, $sort;
    $hStatus = new Xhelp\StatusHandler($GLOBALS['xoopsDB']);

    if (\Xmf\Request::hasVar('changeDefaultStatus', 'POST')) {
        Xhelp\Utility::setMeta('default_status', $_POST['default']);
    }

    if (\Xmf\Request::hasVar('newStatus', 'POST')) {
        if ('' == $_POST['desc']) {  // If no description supplied
            $message = _AM_XHELP_MESSAGE_NO_DESC;
            redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, $message);
        }
        $newStatus = $hStatus->create();

        $newStatus->setVar('state', \Xmf\Request::getInt('state', 0, 'POST'));
        $newStatus->setVar('description', $_POST['desc']);
        if ($hStatus->insert($newStatus)) {
            redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus');
        } else {
            $message = _AM_MESSAGE_ADD_STATUS_ERR;
            redirect_header(XHELP_ADMIN_URL . '/status.php?op=manageStatus', 3, $message);
        }
    }
    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manStatus');
    $adminObject = \Xmf\Module\Admin::getInstance();
    $adminObject->displayNavigation('status.php?op=manageStatus');

    echo "<form method='post' action='" . XHELP_ADMIN_URL . "/status.php?op=manageStatus'>";
    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='2'><label>" . _AM_XHELP_TEXT_ADD_STATUS . '</label></th></tr>';
    echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_DESCRIPTION . "</td>
              <td class='even'>
                  <input type='text' name='desc' value='' class='formButton'>
              </td>
          </tr>";
    echo "<tr><td class='head' width='20%'>" . _AM_XHELP_TEXT_STATE . "</td><td class='even'>
              <select name='state'>
              <option value='1'>" . Xhelp\Utility::getState(1) . "</option>
              <option value='2'>" . Xhelp\Utility::getState(2) . '</option>
          </select></td></tr>';
    echo "<tr><td class='foot' colspan='2'><input type='submit' name='newStatus' value='" . _AM_XHELP_TEXT_ADD_STATUS . "' class='formButton'></td></tr>";
    echo '</table></form>';

    // Get list of existing statuses
    $crit = new \Criteria('', '');
    $crit->setOrder($order);
    $crit->setSort($sort);
    $crit->setLimit($limit);
    $crit->setStart($start);
    $statuses = $hStatus->getObjects($crit);
    $total    = $hStatus->getCount($crit);

    $aStatuses = [];
    foreach ($statuses as $status) {
        $aStatuses[$status->getVar('id')] = $status->getVar('description');
    }

    if (!$default_status = Xhelp\Utility::getMeta('default_status')) {
        Xhelp\Utility::setMeta('default_status', '1');
        $default_status = 1;
    }
    $form          = new Xhelp\Form(_AM_XHELP_TEXT_DEFAULT_STATUS, 'default_status', Xhelp\Utility::createURI(XHELP_ADMIN_URL . '/status.php', ['op' => 'manageStatus']));
    $status_select = new \XoopsFormSelect(_AM_XHELP_TEXT_STATUS, 'default', $default_status);
    $status_select->addOptionArray($aStatuses);
    $btn_tray = new \XoopsFormElementTray('');
    $btn_tray->addElement(new \XoopsFormButton('', 'changeDefaultStatus', _AM_XHELP_BUTTON_SUBMIT, 'submit'));
    $form->addElement($status_select);
    $form->addElement($btn_tray);
    $form->setLabelWidth('20%');
    echo $form->render();

    $nav = new \XoopsPageNav($total, $limit, $start, 'start', "op=manageStatus&amp;limit=$limit");

    echo "<form action='" . XHELP_ADMIN_URL . "/status.php?op=manageStatus' style='margin:0; padding:0;' method='post'>";
    echo $GLOBALS['xoopsSecurity']->getTokenHTML();
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><td align='right'>" . _AM_XHELP_TEXT_SORT_BY . "
                  <select name='sort'>";
    foreach ($aSortBy as $value => $text) {
        ($sort == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo '</select>
                &nbsp;&nbsp;&nbsp;
                  ' . _AM_XHELP_TEXT_ORDER_BY . "
                  <select name='order'>";
    foreach ($aOrderBy as $value => $text) {
        ($order == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo '</select>
                  &nbsp;&nbsp;&nbsp;
                  ' . _AM_XHELP_TEXT_NUMBER_PER_PAGE . "
                  <select name='limit'>";
    foreach ($aLimitBy as $value => $text) {
        ($limit == $value) ? $selected = 'selected' : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo "</select>
                  <input type='submit' name='status_sort' id='status_sort' value='" . _AM_XHELP_BUTTON_SUBMIT . "'>
              </td>
          </tr>";
    echo '</table></form>';

    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='4'><label>" . _AM_XHELP_TEXT_MANAGE_STATUSES . '</label></th></tr>';
    echo "<tr class='head'>
              <td>" . _AM_XHELP_TEXT_ID . '</td>
              <td>' . _AM_XHELP_TEXT_DESCRIPTION . '</td>
              <td>' . _AM_XHELP_TEXT_STATE . '</td>
              <td>' . _AM_XHELP_TEXT_ACTIONS . '</td>
          </tr>';
    foreach ($statuses as $status) {
        echo "<tr class='even'><td>" . $status->getVar('id') . '</td><td>' . $status->getVar('description') . '</td>
              <td>' . Xhelp\Utility::getState($status->getVar('state')) . "</td>
              <td>
                  <a href='status.php?op=editStatus&amp;statusid=" . $status->getVar('id') . "'><img src='" . XHELP_IMAGE_URL . "/button_edit.png' title='" . _AM_XHELP_TEXT_EDIT . "' name='editStatus'></a>&nbsp;
                  <a href='status.php?op=deleteStatus&amp;statusid=" . $status->getVar('id') . "'><img src='" . XHELP_IMAGE_URL . "/button_delete.png' title='" . _AM_XHELP_TEXT_DELETE . "' name='deleteStatus'></a></td></tr>
              </td></tr>";
    }
    echo '</table>';
    echo "<div id='status_nav'>" . $nav->renderNav() . '</div>';
    require_once __DIR__ . '/admin_footer.php';
}
