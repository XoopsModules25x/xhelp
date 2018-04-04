<?php namespace XoopsModules\Xhelp;

//

use XoopsModules\Xhelp;

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

// require_once XHELP_CLASS_PATH . '/faq.php';

define('XHELP_FAQ_CATEGORY_SING', 0);
define('XHELP_FAQ_CATEGORY_MULTI', 1);
define('XHELP_FAQ_CATEGORY_NONE', 2);

/**
 * class FaqAdapter
 */
class FaqAdapter
{
    public $categoryType = XHELP_FAQ_CATEGORY_SING;

    /**
     * Adapter Details
     * Required Values:
     * name - name of adapter
     * author - who wrote the plugin
     * author_email - contact email
     * version - version of this plugin
     * tested_versions - supported application versions
     * url - support url for plugin
     * module_dir - module directory name (not needed if class overloads the isActive() function from Xhelp\FaqAdapter)
     * @access public
     */
    public $meta = [
        'name'            => '',
        'author'          => '',
        'author_email'    => '',
        'version'         => '',
        'tested_versions' => '',
        'url'             => '',
        'module_dir'      => ''
    ];

    /**
     * Perform any initilization needed
     */
    public function init()
    {
    }

    /**
     * Stub function (does nothing)
     * @return void of Xhelp\FaqCategory objects
     */
    public function &getCategories()
    {
    }

    /**
     * @return bool true (success)/false (failure)
     */
    public function storeFaq()
    {
        // Store an faq
        return false;
    }

    /**
     * @return Xhelp\Faq object
     */
    public function createFaq()
    {
        // Create an faq
        $faq = new Xhelp\Faq();

        return $faq;
    }

    /**
     * @return bool true (success) / false (failure)
     */
    public function isActive()
    {
        $module_dir  = $this->meta['module_dir'];
        $module_name = $this->meta['name'];

        if ('' == $module_dir || '' == $module_name) {      // Sanity check

            return false;
        }

        // Make sure that module is active
        $hModule = xoops_getHandler('module');
        $mod     = $hModule->getByDirname($module_dir);

        if (is_object($mod)) {
            if ($mod->getVar('isactive')) {   // Module active?
                $activeAdapter = Xhelp\Utility::getMeta('faq_adapter');
                if ($activeAdapter = $module_name) {
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
