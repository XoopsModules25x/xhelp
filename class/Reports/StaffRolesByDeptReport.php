<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Reports;

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

use XoopsModules\Xhelp;
use Amenadiel\JpGraph\Plot;
use Amenadiel\JpGraph\Graph;

// require_once XHELP_CLASS_PATH . '/report.php';
Xhelp\Utility::includeReportLangFile('reports/staffRolesByDept');

//require_once \XHELP_JPGRAPH_PATH . '/jpgraph.php';
// require_once XHELP_CLASS_PATH . '/report.php';

global $xoopsDB;

/**
 * class StaffRolesByDeptReport
 */
class StaffRolesByDeptReport extends Xhelp\Reports\Report
{
    /**
     * Xhelp\StaffRolesByDeptReport constructor.
     */
    public function __construct()
    {
        $this->initVar('results', \XOBJ_DTYPE_ARRAY, null, false);
        $this->initVar('hasResults', \XOBJ_DTYPE_INT, 0, false);
        $this->initVar('hasGraph', \XOBJ_DTYPE_INT, 0, false);
    }

    public $name = 'StaffRolesByDeptReport';
    public $meta = [
        'name'         => \_XHELP_SRD_NAME,
        'author'       => 'Eric Juden',
        'author_email' => 'eric@3dev.org',
        'description'  => \_XHELP_SRD_DESC,
        'version'      => '1.0',
        'dbFields'     => [
            'Department' => \_XHELP_SRD_DB3,
            'Role'       => \_XHELP_SRD_DB2,
            'name'       => \_XHELP_SRD_DB1,
        ],
    ];

    public function generateReport(): string
    {
        if (0 == $this->getVar('hasResults')) {
            $this->setResults();
        }
        $aResults = $this->getVar('results');

        if (empty($aResults)) {       // If no records found
            $myReport = $this->generateReportNoData();

            return $myReport;
        }

        // Print graph
        $myReport = '';
        $myReport .= "<div id='xhelp_graph'>";
        $myReport .= '</div>';

        // Display report
        $myReport .= '<br>';
        $myReport .= "<div id='xhelp_report'>";
        $myReport .= '<table>';
        $myReport .= '<tr>';

        $dbFields = $this->meta['dbFields'];
        $myReport .= '<th>' . $dbFields['Department'] . '</th>';
        $myReport .= '<th>' . $dbFields['Role'] . '</th>';
        $myReport .= '<th>' . $dbFields['name'] . '</th>';

        $myReport .= '</tr>';

        $dept = '';
        $role = '';
        foreach ($aResults as $result) {
            if ($result['Department'] != $dept) {
                $myReport .= "<tr class='even'><td>" . $result['Department'] . '</td>';
                $dept     = $result['Department'];
                $role     = '';
            } else {
                $myReport .= "<tr class='even'><td></td>";
            }
            if ($result['Role'] != $role) {
                $myReport .= '<td>' . $result['Role'] . '</td>';
                $role     = $result['Role'];
            } else {
                $myReport .= '<td></td>';
            }
            $myReport .= '<td>' . $result['name'] . '</td></tr>';
        }

        $myReport .= '</table>';
        $myReport .= '</div>';

        return $myReport;
    }



    /**
     * @return void
     */
    //    public function generateGraph()
    //    {
    //    }

    /**
     * @return bool
     */
    public function setResults(): bool
    {
        global $xoopsDB;

        $sSQL = \sprintf(
            'SELECT u.name, r.name AS Role, d.department AS Department FROM `%s` u, %s s, %s sr, %s r, %s d WHERE (u.uid = s.uid) AND (u.uid = sr.uid) AND (sr.roleid = r.id) AND (sr.deptid = d.id) AND (u.uid = sr.uid) AND (u.uid = s.uid) ORDER BY d.department, u.name',
            $xoopsDB->prefix('users'),
            $xoopsDB->prefix('xhelp_staff'),
            $xoopsDB->prefix('xhelp_staffroles'),
            $xoopsDB->prefix('xhelp_roles'),
            $xoopsDB->prefix('xhelp_departments')
        );

        $result   = $xoopsDB->query($sSQL);
        $aResults = $this->arrayFromData($result);
        $this->setVar('results', \serialize($aResults));
        $this->setVar('hasResults', 1);

        return true;
    }
}
