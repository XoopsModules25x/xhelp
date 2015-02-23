<?php
// $Id: reportRendererFactory.php,v 1.1 2006/02/06 19:37:59 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

class xhelpReportRendererFactory {
    function xhelpReportRendererFactory()
    {
        // Constructor
    }

    function &getRenderer($type, &$report)
    {
        $ret = false;
        if($type == ''){
            return $ret;
        }

        // Check rendererValid function
        $isValid = xhelpReportRendererFactory::_rendererValid($type);

        if($isValid){
            // Step 2 - include script with faq adapter class
            require_once(XHELP_RPT_RENDERER_PATH .'/'.$type.'ReportRenderer.php');

            // Step 3 - create instance of adapter class
            $classname = 'xhelp'.$type.'ReportRenderer';

            // Step 4 - return adapter class
            $ret = new $classname($report);
            return $ret;
        } else {
            return $ret;
        }
        //XHELP_RPT_RENDERER_PATH
    }

    function _rendererValid($type)
    {
        // Make sure this is a valid file
        if (is_file(XHELP_RPT_RENDERER_PATH . '/'. $type. 'ReportRenderer.php')) {
            return true;
        } else {
            return false;
        }
    }
}

?>