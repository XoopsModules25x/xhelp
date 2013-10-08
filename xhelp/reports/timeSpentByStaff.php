<?php
// $Id: timeSpentByStaff.php,v 1.6 2006/02/06 19:58:23 eric_juden Exp $

include_once(XHELP_JPGRAPH_PATH .'/jpgraph.php');
include_once(XHELP_CLASS_PATH .'/report.php');
xhelpIncludeReportLangFile('timeSpentByStaff');

global $xoopsDB, $paramVals;

$startDate = date('m/d/y h:i:s A', mktime(0, 0, 0, date("m")-1, date("d"), date("Y")));
$endDate = date('m/d/y') ." 12:00:00 AM";

// Cannot fill date values in class...have to fill these values later
$paramVals = array('startDate' => ((isset($_REQUEST['startDate']) && $_REQUEST['startDate'] != '') ? $_REQUEST['startDate'] : $startDate),
                   'endDate' => ((isset($_REQUEST['endDate']) && $_REQUEST['endDate'] != '') ? $_REQUEST['endDate'] : $endDate));

class xhelpTimeSpentByStaffReport extends xhelpReport {
    function xhelpTimeSpentByStaffReport()
    {
        $this->initVar('results', XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', XOBJ_DTYPE_INT, 1, false);
    }
    var $name = 'timeSpentByStaff';

    var $meta = array(
        'name' => _XHELP_TSBS_NAME,
        'author' => 'Eric Juden',
        'authorEmail' => 'eric@3dev.org',
        'description' => _XHELP_TSBS_DESC,
        'version' => '1.0',
        'dbFields' => array(
            'name' => _XHELP_TSBS_DB1, 
            'TotalTime' => _XHELP_TSBS_DB2));

    var $parameters = array(
    _XHELP_TSBS_PARAM1 => array(
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname' => 'startDate',
            'value' => '',      // last month
            'values' => '',
            'fieldlength' => 25,
            'dbfield' => 'r.updateTime',
            'dbaction' => '>'),
    _XHELP_TSBS_PARAM2 => array (
            'controltype' => XHELP_CONTROL_DATETIME,
            'fieldname' => 'endDate',
            'value' => '',      // today
            'values' => '',
            'fieldlength' => 25,
            'dbfield' => 'r.updateTime',
            'dbaction' => '<=')
    );

    /*
     function generateReport()
     {
     global $paramVals;

     if($this->getVar('hasResults') == 0){
     $this->_setResults();
     }
     $aResults = $this->getVar('results');

     if(empty($aResults)){       // If no records found
     $myReport = $this->generateReportNoData();
     return $myReport;
     }

     $params = '';
     foreach($paramVals as $key=>$value){
     $params .= "&$key=$value";
     }

     // Print graph
     $myReport = '';
     $myReport .= "<div id='xhelp_graph'>";
     $myReport .= "<img src='".XHELP_BASE_URL."/report.php?op=graph&name=timeSpentByStaff".$params."' align='center' width='500' height='300' />";
     $myReport .= "</div>";

     // Display report
     $myReport .= "<br />";
     $myReport .= "<div id='xhelp_report'>";
     $myReport .= "<table>";
     $myReport .= "<tr>";
     $dbFields = $this->meta['dbFields'];

     foreach($dbFields as $dbField=>$field){
     $myReport .= "<th>".$field."</th>";
     }
     $myReport .= "</tr>";

     $totalTime = 0;
     foreach($aResults as $result){
     $myReport .= "<tr class='even'>";

     foreach($dbFields as $dbField=>$field){
     $myReport .= "<td>". $result[$dbField] ."</td>";
     if($dbField == 'TotalTime'){
     $totalTime += $result[$dbField];
     }
     }
     $myReport .= "</tr>";
     }
     // Display total time
     $myReport .= "<tr class='foot'><td>"._XHELP_TEXT_TOTAL."</td><td>". $totalTime ."</td></tr>";

     $myReport .= "</table>";
     $myReport .= "</div>";

     return $myReport;
     }
     */

    function generateGraph()
    {
        if($this->getVar('hasGraph') == 0){
            return false;
        }

        if($this->getVar('hasResults') == 0){
            $this->_setResults();
        }
        $aResults = $this->getVar('results');

        $i = 0;
        $data = array();
        foreach($aResults as $result){
            $data[0][] = $result['name'];     // Used for identifier on chart
            $data[1][] = $result['TotalTime'];      // used for data on chart
        }

        $this->generatePie3D($data, 0, 1, XHELP_IMAGE_PATH .'/graph_bg.jpg');
    }

    function _setResults()
    {
        global $xoopsDB;

        $sSQL = sprintf("SELECT u.name, SUM(r.timeSpent) AS TotalTime FROM %s u, %s r WHERE (u.uid = r.uid) %s GROUP BY u.name HAVING (SUM(r.timeSpent) > 0)",
        $xoopsDB->prefix('users'), $xoopsDB->prefix('xhelp_responses'), $this->extraWhere);

        $result = $xoopsDB->query($sSQL);
        $aResults = $this->_arrayFromData($result);
        $this->setVar('results', serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
?>
