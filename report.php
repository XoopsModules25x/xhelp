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

use Xmf\Request;
use XoopsModules\Xhelp;
use Amenadiel\JpGraph\Plot;

require_once __DIR__ . '/header.php';
require_once XHELP_INCLUDE_PATH . '/events.php';

//if (!file_exists(XHELP_JPGRAPH_PATH . '/jpgraph.php')) {
//    $helper->redirect('index.php', 5, _XHELP_TEXT_NO_JPGRAPH);
//}

// require_once XHELP_CLASS_PATH . '/reportFactory.php';
// require_once XHELP_CLASS_PATH . '/ReportRendererFactory.php';

if (!$xhelp_isStaff) {        // Is user a staff member?
    $helper->redirect('index.php', 3, _AM_XHELP_NO_PERM);
}

$op = 'default';
if (Request::hasVar('op', 'GET')) {
    $op = $_GET['op'];
}

switch ($op) {
    case 'run':
        if (!isset($_REQUEST['name']) && '' == \Xmf\Request::getString('name', '', 'REQUEST')) {
            $helper->redirect('report.php', 3, _XHELP_MSG_NO_REPORT);
        }
        $reportName = \Xmf\Request::getString('name', '', 'REQUEST');
        /*
         if (!array_key_exists($reportName, $reports)) {         // If the report name is not in the acceptable names array
         redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT_LOAD);
         }
         */
        runReport($reportName);
        break;
    case 'graph':
        if (!isset($_GET['name']) && '' == \Xmf\Request::getString('name', '', 'GET')) {
            $helper->redirect('report.php', 3, _XHELP_MSG_NO_REPORT);
        }
        $reportName = \Xmf\Request::getString('name', '', 'REQUEST');
        /*if (!array_key_exists($reportName, $reports)) {         // If the report name is not in the acceptable names array
         redirect_header(XHELP_BASE_URL .'/report.php', 3, _XHELP_MSG_NO_REPORT_LOAD);
         }*/
        makeGraph($reportName);
        break;
    default:        // Display list of reports
        $reports = Xhelp\ReportFactory::getReports();

        $rptNames = [];
        foreach ($reports as $rpt => $obj) {
            $rptNames[] = $rpt;
        }

        displayReports();
        break;
}

function displayReports()
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser;

    $GLOBALS['xoopsOption']['template_main'] = 'xhelp_report.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                    // Include page header

    $aReports = getReportsMeta();

    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xhelp_reports', $aReports);
    $xoopsTpl->assign('xhelp_baseURL', XHELP_BASE_URL);

    require_once XOOPS_ROOT_PATH . '/footer.php';                    // Include page footer
}

/**
 * @return array
 */
function getReportsMeta(): array
{
    global $reports;

    $aMeta = [];
    foreach ($reports as $name => $report) {
        $aMeta[$name] = $report->meta;
    }

    return $aMeta;
}

/**
 * @param string $reportName
 */
function runReport(string $reportName)
{
    global $xoopsOption, $xoopsTpl, $xoopsConfig, $xoopsUser, $xhelp_module_header, $paramVals;

    //    $classname = 'xhelp' . $reportName . 'Report';
    //    require_once XHELP_REPORT_PATH . '/' . $reportName . '.php';
    //    if( strpos($filename,'Report') !== false ) {
    //    $classname =  'XoopsModules\Xhelp\Reports\\' . \ucfirst($reportName). 'Report';
    $classname = 'XoopsModules\Xhelp\Reports\\' . \ucfirst($reportName);
    if (!\class_exists($classname)) {
        throw new \RuntimeException("Class '$classname' not found");
    }
    $report = new $classname();

    // Get any parameters for report
    $reportParams = $report->getParams();

    // Fill reportParameters with updated information
    if (is_countable($reportParams) && count($reportParams) > 0) {
        foreach ($reportParams as $param) {
            if (isset($_REQUEST[$param->fieldname])) {
                if (XHELP_CONTROL_DATETIME == $param->controltype) {
                    $param->value = strtotime($_REQUEST[$param->fieldname]);
                } else {
                    $param->value = $_REQUEST[$param->fieldname];
                }
            } else {
                if (isset($paramVals[$param->fieldname])) {
                    if (XHELP_CONTROL_DATETIME == $param->controltype) {
                        $param->value = strtotime($paramVals[$param->fieldname]);
                    } else {
                        if (is_array($paramVals[$param->fieldname])) {
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

    //$GLOBALS['xoopsOption']['template_main'] = 'xhelp_report.tpl';   // Set template
    require_once XOOPS_ROOT_PATH . '/header.php';                 // Include page header

    generateHeader($report);

    /** @var \XoopsModules\Xhelp\ReportRenderer\HtmlReportRenderer $oRenderer */
    $oRenderer = Xhelp\ReportRendererFactory::getRenderer('html', $report);
    echo $oRenderer->render();

    $xoopsTpl->assign('xhelp_imagePath', XHELP_IMAGE_URL . '/');
    $xoopsTpl->assign('xoops_module_header', $xhelp_module_header);

    require_once XOOPS_ROOT_PATH . '/footer.php';
}

/**
 * @param string $reportName
 */
function makeGraph(string $reportName)
{
    //    $classname = 'xhelp' . $reportName . 'Report';
    //    require_once XHELP_REPORT_PATH . '/' . $reportName . '.php';
    $classname = 'XoopsModules\Xhelp\Reports\\' . \ucfirst($reportName);
    if (!\class_exists($classname)) {
        throw new \RuntimeException("Class '$classname' not found");
    }
    $report = new $classname();

    // Get any parameters for report
    $reportParams = $report->getParams();

    // Fill reportParameters with updated information
    foreach ($reportParams as $param) {
        if (isset($_REQUEST[$param->fieldname])) {
            if (XHELP_CONTROL_DATETIME == $param->controltype) {
                $param->value = strtotime(\Xmf\Request::getString($param->fieldname, '', 'REQUEST'));
            } else {
                $param->value = \Xmf\Request::getString($param->fieldname, '', 'REQUEST');
            }
        }
    }
    $report->extraWhere = $report->makeWhere($reportParams);

    $report->generateGraph();      // Display graph
}

/**
 * @param Report $report
 */
function generateHeader(Report $report)
{
    global $paramVals;

    // Get any parameters for report
    $reportParams = $report->getParams();

    if (is_countable($reportParams) && count($reportParams) > 0) {
        echo "<div id='xhelp_reportParams'>";
        echo "<form method='post' action='" . XHELP_BASE_URL . '/report.php?op=run&name=' . $report->name . "'>";

        foreach ($reportParams as $param) {
            echo $param->displayParam($paramVals);
        }
        echo "<input type='submit' name='updateReport' id='updateReport' value='" . _XHELP_TEXT_VIEW_REPORT . "'>";
        echo '</div>';
    }

    // display report name
    echo "<div id='xhelp_reportHeader'>";
    echo '<h2>' . $report->meta['name'] . '</h2>';
    echo '</div>';
}
