<?php
//$Id: status.php,v 1.7 2005/11/29 17:48:12 ackbarr Exp $
include('../../../include/cp_header.php');
include_once('admin_header.php');
include_once(XOOPS_ROOT_PATH . '/class/pagenav.php');
require_once(XHELP_CLASS_PATH . '/xhelpForm.php');

global $xoopsModule;
$module_id = $xoopsModule->getVar('mid');

$start = $limit = 0;
if (isset($_REQUEST['limit'])) {
    $limit = intval($_REQUEST['limit']);
}
if (isset($_REQUEST['start'])) {
    $start = intval($_REQUEST['start']);
}
if (!$limit) {
    $limit = 15;
}
if(isset($_REQUEST['order'])){
    $order = $_REQUEST['order'];
} else {
    $order = "ASC";
}
if(isset($_REQUEST['sort'])) {
    $sort = $_REQUEST['sort'];
} else {
    $sort = "id";
}

$aSortBy = array('id' => _AM_XHELP_TEXT_ID, 'description' => _AM_XHELP_TEXT_DESCRIPTION, 'state' => _AM_XHELP_TEXT_STATE);
$aOrderBy = array('ASC' => _AM_XHELP_TEXT_ASCENDING, 'DESC' => _AM_XHELP_TEXT_DESCENDING);
$aLimitBy = array('10' => 10, '15' => 15, '20' => 20, '25' => 25, '50' => 50, '100' => 100);

$op = 'default';

if ( isset( $_REQUEST['op'] ) )
{
    $op = $_REQUEST['op'];
}

switch ( $op )
{
    case "deleteStatus":
        deleteStatus();
        break;

    case "editStatus":
        editStatus();
        break;

    case "manageStatus":
        manageStatus();
        break;

    default:
        header("Location: ".XHELP_ADMIN_URL."/index.php");
        break;
}

function deleteStatus()
{
    if(isset($_GET['statusid'])){
        $statusid = intval($_GET['statusid']);
    } else {
        header("Location: ".XHELP_ADMIN_URL."/status.php?op=manageStatus");
    }

    $hTickets =& xhelpGetHandler('ticket');
    $hStatus =& xhelpGetHandler('status');
    $status =& $hStatus->get($statusid);

    // Check for tickets with this status first
    $crit = new Criteria('status', $statusid);
    $ticketCount =& $hTickets->getCount($crit);

    if($ticketCount > 0){
        redirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, _AM_XHELP_STATUS_HASTICKETS_ERR);
    }

    if($hStatus->delete($status, true)){
        header("Location: ".XHELP_ADMIN_URL."/status.php?op=manageStatus");
    } else {
        $message = _AM_XHELP_DEL_STATUS_ERR;
        redirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, $message);
    }
}

function editStatus()
{
    if(isset($_REQUEST['statusid'])){
        $statusid = intval($_REQUEST['statusid']);
    } else {
        header("Location: ".XHELP_ADMIN_URL."/status.php?op=manageStatus");
    }

    $hStatus =& xhelpGetHandler('status');
    $status = $hStatus->get($statusid);

    if(!isset($_POST['updateStatus'])){
        xoops_cp_header();
        //echo $oAdminButton->renderButtons('modTpl');
        $indexAdmin = new ModuleAdmin();
        echo $indexAdmin->addNavigation('status.php?op=editStatus');

        echo "<form method='post' action='".XHELP_ADMIN_URL."/status.php?op=editStatus&amp;statusid=".$statusid."'>";
        echo "<table width='100%' cellspacing='1' class='outer'>
              <tr><th colspan='2'><label>"._AM_XHELP_TEXT_EDIT_STATUS."</label></th></tr>";
        echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_DESCRIPTION."</td>
                  <td class='even'>
                      <input type='text' name='desc' value='".$status->getVar('description', 'e')."' class='formButton' />
                  </td>
              </tr>";
        echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_STATE."</td><td class='even'>
                  <select name='state'>";
        if($status->getVar('state') == 1){
            echo "<option value='1' selected='selected'>".xhelpGetState(1)."</option>
                          <option value='2'>".xhelpGetState(2)."</option>";
        } else {
            echo "<option value='1'>".xhelpGetState(1)."</option>
                          <option value='2' selected='selected'>".xhelpGetState(2)."</option>";
        }
        echo "</select></td></tr>";
        echo "<tr><td class='foot' colspan='2'><input type='submit' name='updateStatus' value='"._AM_XHELP_BUTTON_UPDATE."' class='formButton' /></td></tr>";
        echo "</table></form>";

        include_once "admin_footer.php";
    } else {
        if($_POST['desc'] == ''){  // If no description supplied
            $message = _AM_XHELP_MESSAGE_NO_DESC;
            redirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, $message);
        }

        $status->setVar('description', $_POST['desc']);
        $status->setVar('state', $_POST['state']);
        if($hStatus->insert($status)){
            header("Location: ".XHELP_ADMIN_URL."/status.php?op=manageStatus");
        } else {
            $message = _AM_MESSAGE_EDIT_STATUS_ERR;
            readirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, $message);
        }
    }
}

function manageStatus()
{
    global $aSortBy, $aOrderBy, $aLimitBy, $order, $limit, $start, $sort;
    $hStatus =& xhelpGetHandler('status');

    if(isset($_POST['changeDefaultStatus'])){
        xhelpSetMeta("default_status", $_POST['default']);
    }

    if(isset($_POST['newStatus'])){
        if($_POST['desc'] == ''){  // If no description supplied
            $message = _AM_XHELP_MESSAGE_NO_DESC;
            redirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, $message);
        }
        $newStatus =& $hStatus->create();

        $newStatus->setVar('state', intval($_POST['state']));
        $newStatus->setVar('description', $_POST['desc']);
        if($hStatus->insert($newStatus)){
            header("Location: ".XHELP_ADMIN_URL."/status.php?op=manageStatus");
        } else {
            $message = _AM_MESSAGE_ADD_STATUS_ERR;
            redirect_header(XHELP_ADMIN_URL."/status.php?op=manageStatus", 3, $message);
        }
    }
    xoops_cp_header();
    //echo $oAdminButton->renderButtons('manStatus');
    $indexAdmin = new ModuleAdmin();
    echo $indexAdmin->addNavigation('status.php?op=manageStatus');

    echo "<form method='post' action='".XHELP_ADMIN_URL."/status.php?op=manageStatus'>";
    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='2'><label>"._AM_XHELP_TEXT_ADD_STATUS."</label></th></tr>";
    echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_DESCRIPTION."</td>
              <td class='even'>
                  <input type='text' name='desc' value='' class='formButton' />
              </td>
          </tr>";
    echo "<tr><td class='head' width='20%'>"._AM_XHELP_TEXT_STATE."</td><td class='even'>
              <select name='state'>
              <option value='1'>".xhelpGetState(1)."</option>
              <option value='2'>".xhelpGetState(2)."</option>
          </select></td></tr>";
    echo "<tr><td class='foot' colspan='2'><input type='submit' name='newStatus' value='"._AM_XHELP_TEXT_ADD_STATUS."' class='formButton' /></td></tr>";
    echo "</table></form>";

    // Get list of existing statuses
    $crit = new Criteria('', '');
    $crit->setOrder($order);
    $crit->setSort($sort);
    $crit->setLimit($limit);
    $crit->setStart($start);
    $statuses = $hStatus->getObjects($crit);
    $total = $hStatus->getCount($crit);

    $aStatuses = array();
    foreach($statuses as $status){
        $aStatuses[$status->getVar('id')] = $status->getVar('description');
    }

    if(!$default_status = xhelpGetMeta("default_status")){
        xhelpSetMeta("default_status", "1");
        $default_status = 1;
    }
    $form = new xhelpForm(_AM_XHELP_TEXT_DEFAULT_STATUS, 'default_status', xhelpMakeURI(XHELP_ADMIN_URL.'/status.php', array('op'=>'manageStatus')));
    $status_select = new XoopsFormSelect(_AM_XHELP_TEXT_STATUS, 'default', $default_status);
    $status_select->addOptionArray($aStatuses);
    $btn_tray = new XoopsFormElementTray('');
    $btn_tray->addElement(new XoopsFormButton('', 'changeDefaultStatus', _AM_XHELP_BUTTON_SUBMIT, 'submit'));
    $form->addElement($status_select);
    $form->addElement($btn_tray);
    $form->setLabelWidth('20%');
    echo $form->render();

    $nav = new XoopsPageNav($total, $limit, $start, 'start', "op=manageStatus&amp;limit=$limit");

    echo "<form action='". XHELP_ADMIN_URL."/status.php?op=manageStatus' style='margin:0; padding:0;' method='post'>";
    echo "<table width='100%' cellspacing='1' class='outer'>";
    echo "<tr><td align='right'>"._AM_XHELP_TEXT_SORT_BY."
                  <select name='sort'>";
    foreach($aSortBy as $value=>$text){
        ($sort == $value) ? $selected = "selected='selected'" : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo "</select>
                &nbsp;&nbsp;&nbsp;
                  "._AM_XHELP_TEXT_ORDER_BY."
                  <select name='order'>";
    foreach($aOrderBy as $value=>$text){
        ($order == $value) ? $selected = "selected='selected'" : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo "</select>
                  &nbsp;&nbsp;&nbsp;
                  "._AM_XHELP_TEXT_NUMBER_PER_PAGE."
                  <select name='limit'>";
    foreach($aLimitBy as $value=>$text){
        ($limit == $value) ? $selected = "selected='selected'" : $selected = '';
        echo "<option value='$value' $selected>$text</option>";
    }
    echo "</select>
                  <input type='submit' name='status_sort' id='status_sort' value='"._AM_XHELP_BUTTON_SUBMIT."' />
              </td>
          </tr>";
    echo "</table></form>";

    echo "<table width='100%' cellspacing='1' class='outer'>
          <tr><th colspan='4'><label>"._AM_XHELP_TEXT_MANAGE_STATUSES."</label></th></tr>";
    echo "<tr class='head'>
              <td>"._AM_XHELP_TEXT_ID."</td>
              <td>"._AM_XHELP_TEXT_DESCRIPTION."</td>
              <td>"._AM_XHELP_TEXT_STATE."</td>
              <td>"._AM_XHELP_TEXT_ACTIONS."</td>
          </tr>";
    foreach($statuses as $status){
        echo "<tr class='even'><td>".$status->getVar('id')."</td><td>".$status->getVar('description')."</td>
              <td>".xhelpGetState($status->getVar('state'))."</td>
              <td>
                  <a href='status.php?op=editStatus&amp;statusid=".$status->getVar('id')."'><img src='".XHELP_IMAGE_URL."/button_edit.png' title='"._AM_XHELP_TEXT_EDIT."' name='editStatus' /></a>&nbsp;
                  <a href='status.php?op=deleteStatus&amp;statusid=".$status->getVar('id')."'><img src='".XHELP_IMAGE_URL."/button_delete.png' title='"._AM_XHELP_TEXT_DELETE."' name='deleteStatus' /></a></td></tr>
              </td></tr>";
    }
    echo "</table>";
    echo "<div id='status_nav'>".$nav->renderNav()."</div>";
    include_once "admin_footer.php";

}
