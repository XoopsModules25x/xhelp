<?php
// $Id: wordpress.php,v 1.5 2005/11/29 19:37:55 eric_juden Exp $

if (!defined('XHELP_CLASS_PATH')) {
    exit();
}

define('XHELP_WP_PATH', XOOPS_ROOT_PATH .'/modules/wordpress');
define('XHELP_WP_URL', XOOPS_URL .'/modules/wordpress');

require_once(XHELP_CLASS_PATH .'/faqAdapter.php');

class xhelpWordpressAdapter extends xhelpFaqAdapter {
    /**
     * Does application support categories?
     * Possible Values:
     * XHELP_FAQ_CATEGORY_SING - entries can be in 1 category
     * XHELP_FAQ_CATEGORY_MULTI - entries can be in more than 1 category
     * XHELP_FAQ_CATEGORY_NONE - No category support
     * @access public
     */
    var $categoryType = XHELP_FAQ_CATEGORY_MULTI;

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
        'name' => 'wordpress',
        'author' => 'Eric Juden',
        'author_email' => 'eric@3dev.org',
        'description' => 'Create article in wordpress module for XOOPS from xHelp helpdesk tickets (phppp module version)',
        'version' => '1.0',
        'tested_versions' => '1.52',
        'url' => 'https://xoops.org.cn',
        'module_dir' => 'wordpress');

    function xhelpWordpressAdapter()
    {
        parent::init();
    }

    /**
     * @return array of xhelpFaqCategory objects
     */
    function &getCategories()
    {
        global $xoopsDB;

        $ret = array();
        // Create an instance of the xhelpFaqCategoryHandler
        $hFaqCategory =& xhelpGetHandler('faqCategory');

        $sql = sprintf("SELECT cat_ID, cat_name, category_parent FROM %s", $xoopsDB->prefix("wp_categories"));
        $result = $xoopsDB->query($sql);

        if(!$result){
            return $ret;
        }

        //Convert the module specific category to the
        //xhelpFaqCategory object for standarization
        while ($myrow = $xoopsDB->fetchArray($result)) {
            $faqcat = $hFaqCategory->create();
            $faqcat->setVar('id', $myrow['cat_ID']);
            $faqcat->setVar('name', $myrow['cat_name']);
            $faqcat->setVar('parent', $myrow['category_parent']);
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

        $post_ID = 0;
        $post_author = $xoopsUser->getVar('uid');
        $now = gmdate('Y-m-d H:i:s');
        $now_gmt = gmdate('Y-m-d H:i:s');
        $content = "<h3>". strtoupper(_XHELP_TEXT_PROBLEM) ."</h3><p>". $faq->getVar('problem') ."</p><h3>". strtoupper(_XHELP_TEXT_SOLUTION) ."</h3><p>". $faq->getVar('solution') ."</p>";
        $post_title = $faq->getVar('subject');  // Ticket subject
        $excerpt = '';
        $post_status = 'publish';
        $comment_status = 'open';
        $ping_status = 'open';
        $post_password = '';
        $post_name = strtolower(str_replace(" ", "-", $post_title));
        $trackback = '';
        $post_parent = 0;
        $menu_order = 0;

        $sql = "INSERT INTO ". $xoopsDB->prefix('wp_posts') ."
                    (ID, post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt,  post_status, comment_status, ping_status, post_password, post_name, to_ping, post_modified, post_modified_gmt, post_parent, menu_order)
                    VALUES
                    ('$post_ID', '$post_author', '$now', '$now_gmt', '$content', '$post_title', '$excerpt', '$post_status', '$comment_status', '$ping_status', '$post_password', '$post_name', '$trackback', '$now', '$now_gmt', '$post_parent', '$menu_order')";
        $ret = $xoopsDB->query($sql);

        $post_ID = $xoopsDB->getInsertId();
        if($ret){
            $faq->setVar('id', $post_ID);
        }

        // Loop through categories array and add to category table
        $categories = $faq->getVar('categories');
        foreach($categories as $category)
        {
            $this->_insertCategory($post_ID, $category);
        }

        return $ret;
    }

    function makeFaqUrl(&$faq)
    {
        return XHELP_WP_URL .'/?p=' . $faq->getVar('id');
    }

    function _insertCategory($post_ID, $category)
    {

        global $xoopsDB;
        $sql = "INSERT INTO ". $xoopsDB->prefix('wp_post2cat') ." (post_id, category_id) VALUES ($post_ID, $category)";
        $ret = $xoopsDB->query($sql);
    }
}
