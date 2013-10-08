<?php
// $Id: reportFactory.php,v 1.1 2005/12/21 16:07:41 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

class xhelpReportFactory {
    function getReport($name)
    {
        $report = false;
        if($name != ''){
            $classname = 'xhelp'.ucfirst($name).'Report';
            include_once(XHELP_REPORT_PATH ."/$name.php");
            $report = new $classname();
        }
        return $report;
    }

    function getReports()
    {
        $aReports = array();

        // Step 1 - directory listing of all files in /reports directory
        $report_dir = @ dir(XHELP_REPORT_PATH);
        if ($report_dir) {
            while(($file = $report_dir->read()) !== false) {
                $meta = array();
                if (preg_match('|^\.+$|', $file)){
                    continue;
                }
                if (preg_match('|\.php$|', $file)){
                    $filename = basename($file, ".php"); // Get name without file extension

                    // Check that class exists in file
                    $report_data = implode('', file(XHELP_REPORT_PATH.'/'.$file));
                    $classname = 'xhelp'.ucfirst($filename).'Report';
                    if(preg_match("|class $classname(.*)|i", $report_data) > 0){
                        include_once(XHELP_REPORT_PATH . "/$file");
                        $aReports[$filename] = new $classname();
                    }
                    unset($report_data);
                }
            }
        }
        return $aReports;
    }
}

?>