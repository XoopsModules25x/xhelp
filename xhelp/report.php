<?php
//$Id: report.php,v 1.7 2006/02/06 22:14:00 eric_juden Exp $
require_once('header.php');
require_once(XHELP_INCLUDE_PATH.'/events.php');

if(!file_exists(XHELP_JPGRAPH_PATH .'/jpgraph.php')){
    redirect_header(XHELP_BASE_URL .'/index.php', 5, _XHELP_TEXT_NO_JPGRAPH);
}

require_once(XHELP_CLASS_PATH.'/reportFactory.php');
require_once(XHELP_CLASS_PATH.'/reportRendererFactory.php');

if(!$xhelp_isStaff){        // Is user a staff member?
    redirect_header(XHELP_BASE_URL .'/index.php', 3, _NO_PERM);
}

$op = 'default';
if(isset($_GET['op'])){
    $op = $_GET['op'];
}

switch($op)
{
    case 'run':
        if(!isset($_REQUEST['name']) && $_REQUEST['name'] == ''){
            redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT);
        }
        $reportName = $_REQUEST['name'];
        /*
         if(!array_key_exists($reportName, $reports)){         // If the report name is not in the acceptable names array
         redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT_LOAD);
         }
         */
        runReport($reportName);
        break;

    case 'graph':
        if(!isset($_GET['name']) && $_GET['name'] == ''){
            redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT);
        }
        $reportName = $_REQUEST['name'];
        /*if(!array_key_exists($reportName, $reports)){         // If the report name is not in the acceptable names array
         redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT_LOAD);
         }*/
        makeGraph($reportName);
        break;

    default:        // Display list of reports
        $reports = xhelpReportFactory::getReports();

        $rptNames = array();
        foreach($reports as $rpt=>$obj){
            $rptNames[] = $rpt;
        }

        displayReports();
        break;
}

function displayReports()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser;

    $xoopsOption['template_main'] = 'xhelp_report.html';   // Set template
    require(XOOPS_ROOT_PATH.'/header.php');                 // Include page header

    $aReports = _getReportsMeta();

    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL .'/');
    $xoopsTpl->assign('xhelp_reports', $aReports);
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

    require(XOOPS_ROOT_PATH.'/footer.php');					// Include page footer
}

function _getReportsMeta()
{
    global $reports;

    $aMeta = array();
    foreach($reports as $name=>$report){
        $aMeta[$name] = $report->meta;
    }
    return $aMeta;
}

function runReport($reportName)
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xhelp_module_header, $paramVals;

    $classname = 'xhelp'.$reportName.'Report';
    include_once(XHELP_REPORT_PATH .'/'.$reportName.'.php');
    $report = new $classname();

    // Get any parameters for report
    $reportParams = $report->getParams();

    // Fill reportParameters with updated information
    if(count($reportParams) > 0){
        foreach($reportParams as $param){
            if(isset($_REQUEST[$param->fieldname])){
                if($param->controltype == XHELP_CONTROL_DATETIME){
                    $param->value = strtotime($_REQUEST[$param->fieldname]);
                } else {
                    $param->value = $_REQUEST[$param->fieldname];
                }
            } else {
                if(isset($paramVals[$param->fieldname])){
                    if($param->controltype == XHELP_CONTROL_DATETIME){
                        $param->value = strtotime($paramVals[$param->fieldname]);
                    } else {
                        if(is_array($paramVals[$param->fieldname])){
                            $param->values = $paramVals[$param->fieldname];
                        } else {
                            $param->value = $paramVals[$param->fieldname];
                        }
                    }
                }

            }
        }
        $report->extraWhere = $report->makeWhere($reportParams);
    }

    //$xoopsOption['template_main'] = 'xhelp_report.html';   // Set template
    require(XOOPS_ROOT_PATH.'/header.php');                 // Include page header

    generateHeader($report);

    $oRenderer =& xhelpReportRendererFactory::getRenderer('html', $report);
    echo $oRenderer->render();

    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL .'/');
    $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);

    require(XOOPS_ROOT_PATH.'/footer.php');
}

function makeGraph($reportName)
{
    $classname = 'xhelp'.$reportName.'Report';
    include_once(XHELP_REPORT_PATH .'/'.$reportName .'.php');
    $report = new $classname();

    // Get any parameters for report
    $reportParams = $report->getParams();

    // Fill reportParameters with updated information
    foreach($reportParams as $param){
        if(isset($_REQUEST[$param->fieldname])){
            if($param->controltype == XHELP_CONTROL_DATETIME){
                $param->value = strtotime($_REQUEST[$param->fieldname]);
            } else {
                $param->value = $_REQUEST[$param->fieldname];
            }

        }
    }
    $report->extraWhere = $report->makeWhere($reportParams);

    $report->generateGraph();      // Display graph
}

function generateHeader($report)
{
    global $paramVals;

    // Get any parameters for report
    $reportParams = $report->getParams();

    if(count($reportParams) > 0){
        echo "<div id='xhelp_reportParams'>";
        echo "<form method='post' action='".XHELP_BASE_URL."/report.php?op=run&name=".$report->name."'>";

        foreach($reportParams as $param){
            echo $param->displayParam($paramVals);
        }
        echo "<input type='submit' name='updateReport' id='updateReport' value='"._XHELP_TEXT_VIEW_REPORT."' />";
        echo "</div>";
    }

    // display report name
    echo "<div id='xhelp_reportHeader'>";
    echo "<h2>".$report->meta['name']."</h2>";
    echo "</div>";
}
?>