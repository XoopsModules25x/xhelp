<?php
// $Id: faqAdapter.php,v 1.4 2005/11/23 16:05:02 ackbarr Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

require_once(XHELP_CLASS_PATH .'/faq.php');


define('XHELP_FAQ_CATEGORY_SING', 0);
define('XHELP_FAQ_CATEGORY_MULTI', 1);
define('XHELP_FAQ_CATEGORY_NONE', 2);

class xhelpFaqAdapter {
    var $categoryType = XHELP_FAQ_CATEGORY_SING;

    /**
     * Adapter Details
     * Required Values:
     * name - name of adapter
     * author - who wrote the plugin
     * author_email - contact email
     * version - version of this plugin
     * tested_versions - supported application versions
     * url - support url for plugin
     * module_dir - module directory name (not needed if class overloads the isActive() function from xhelpFAQAdapter)
     * @access public
     */
    var $meta = array(
            'name' => '', 
            'author' => '',
            'author_email' => '',
            'version' => '',
            'tested_versions' => '',
            'url' => '',
            'module_dir' => '');

    /**
     * Perform any initilization needed
     */
    function init()
    {
    }

    /**
     * Stub function (does nothing)
     * @return array of xhelpFaqCategory objects
     */
    function &getCategories()
    {

    }

    /**
     * @return bool true (success)/false (failure)
     */
    function storeFaq()
    {
        // Store an faq
        return false;
    }

    /**
     * @return xhelpFaq object
     */
    function &createFaq()
    {
        // Create an faq
        $faq = new xhelpFaq();

        return $faq;
    }

    /**
     * @return bool true (success) / false (failure)
     */
    function isActive()
    {
        $module_dir = $this->meta['module_dir'];
        $module_name = $this->meta['name'];

        if($module_dir == '' || $module_name == ''){      // Sanity check
            return false;
        }

        // Make sure that module is active
        $hModule =& xoops_gethandler('module');
        $mod =& $hModule->getByDirname($module_dir);

        if(is_object($mod)){
            if($mod->getVar('isactive')){   // Module active?
                $activeAdapter = xhelpGetMeta('faq_adapter');
                if($activeAdapter = $module_name){
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>