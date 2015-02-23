<?php
// $ID:$

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

define('XHELP_XFAQ_PATH', XOOPS_ROOT_PATH .'/modules/xoopsfaq');
define('XHELP_XFAQ_URL', XOOPS_URL .'/modules/xoopsfaq');

require_once(XHELP_CLASS_PATH .'/faqAdapter.php');

class xhelpXoopsfaqAdapter extends xhelpFaqAdapter {
    /**
     * Does application support categories?
     * Possible Values:
     * XHELP_FAQ_CATEGORY_SING - entries can be in 1 category
     * XHELP_FAQ_CATEGORY_MULTI - entries can be in more than 1 category
     * XHELP_FAQ_CATEGORY_NONE - No category support
     * @access public
     */
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
        'name' => 'xoopsfaq',
        'author' => 'Eric Juden',
        'author_email' => 'eric@3dev.org',
        'description' => 'Create xoopsfaq entries from xHelp helpdesk tickets',
        'version' => '1.0',
        'tested_versions' => '1.1',
        'url' => 'http://xoops.org',
        'module_dir' => 'xoopsfaq');

    function xhelpXoopsfaqAdapter()
    {
        parent::init();
    }

    function &getCategories()
    {
        global $xoopsDB;

        $ret = array();
        // Create an instance of the xhelpFaqCategoryHandler
        $hFaqCategory =& xhelpGetHandler('faqCategory');

        $sql = sprintf("SELECT category_id, category_title FROM %s ORDER BY category_order", $xoopsDB->prefix("xoopsfaq_categories"));
        $result = $xoopsDB->query($sql);

        if(!$result){
            return $ret;
        }

        //Convert the module specific category to the
        //xhelpFaqCategory object for standarization
        while ($myrow = $xoopsDB->fetchArray($result)) {
            $faqcat = $hFaqCategory->create();
            $faqcat->setVar('id', $myrow['category_id']);
            $faqcat->setVar('name', $myrow['category_title']);
            $faqcat->setVar('parent', 0);
            $ret[] = $faqcat;
        }

        return $ret;
    }

    /**
     * @return bool true (success)/false (failure)
     */
    function storeFaq(&$faq)
    {
        global $xoopsDB, $xoopsUser;

        // Set values before storing to db
        $newid = 0;
        $categories = $faq->getVar('categories');
        $category_id = $categories[0];
        $title = $faq->getVar('problem');
        $contents = $faq->getVar('solution');
        $contents_order = 0;
        $contents_visible = 1;
        $contents_nohtml = 0;
        $contents_nosmiley = 0;
        $contents_noxcode = 0;

        $sql = "INSERT INTO ".$xoopsDB->prefix("xoopsfaq_contents")." (contents_id, category_id, contents_title, contents_contents, contents_time, contents_order, contents_visible, contents_nohtml, contents_nosmiley, contents_noxcode) VALUES (".$newid.", ".$category_id.", '".$title."', '".$contents."', ".time().", ".$contents_order.", ".$contents_visible.", ".$contents_nohtml.", ".$contents_nosmiley.", ".$contents_noxcode.")";
        $ret = $xoopsDB->query($sql);

        $newid = $xoopsDB->getInsertId();   // Get new faq id from db
        if($ret){
            $faq->setVar('id', $newid);
        }
        return $ret;
    }

    function makeFaqUrl(&$faq)
    {
        return XHELP_XFAQ_URL .'/index.php?cat_id='. $faq->getVar('categories') .'#q'. $faq->getVar('id');
    }
}

?>