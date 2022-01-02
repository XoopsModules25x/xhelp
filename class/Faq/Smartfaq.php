<?php declare(strict_types=1);

namespace XoopsModules\Xhelp\Faq;

use XoopsModules\Smartfaq\Helper as AdapterHelper;
use XoopsModules\Xhelp;

//Sanity Check: make sure that file is not being accessed directly
if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// ** Define any site specific variables here **
\define('XHELP_SMARTFAQ_PATH', XOOPS_ROOT_PATH . '/modules/smartfaq');
\define('XHELP_SMARTFAQ_URL', XOOPS_URL . '/modules/smartfaq');
// ** End site specific variables **

//Include the base faqAdapter interface (required)
// require_once XHELP_CLASS_PATH . '/faqAdapter.php';

//These functions are required to work with the smartfaq application directly
//@require \XHELP_SMARTFAQ_PATH . '/include/functions.php';

/**
 * class Smartfaq
 */
class Smartfaq extends Xhelp\FaqAdapterAbstract
{
    /**
     * Does application support categories?
     * Possible Values:
     * XHELP_FAQ_CATEGORY_SING - entries can be in 1 category
     * XHELP_FAQ_CATEGORY_MULTI - entries can be in more than 1 category
     * XHELP_FAQ_CATEGORY_NONE - No category support
     */
    public $categoryType = \XHELP_FAQ_CATEGORY_SING;
    /**
     * Adapter Details
     * Required Values:
     * name - name of adapter
     * author - who wrote the plugin
     * author_email - contact email
     * version - version of this plugin
     * tested_versions - supported application versions
     * url - support url for plugin
     * module_dir - module directory name (not needed if class overloads the isActive() function from Xhelp\FaqAdapterAbstract)
     */
    public $meta = [
        'name'            => 'Smartfaq',
        'author'          => 'Eric Juden',
        'author_email'    => 'eric@3dev.org',
        'description'     => 'Create SmartFAQ entries from xHelp helpdesk tickets',
        'version'         => '1.0',
        'tested_versions' => '1.04',
        'url'             => 'https://www.smartfactory.ca/',
        'module_dir'      => 'smartfaq',
    ];

    /**
     * Class Constructor (Required)
     */
    public function __construct()
    {
        // Every class should call parent::init() to ensure that all class level
        // variables are initialized properly.
        if (\class_exists(AdapterHelper::class)) {
            $this->helper = AdapterHelper::getInstance();
            $this->dirname = $this->helper->dirname();
        }
        parent::init();
    }

    /**
     * getCategories: retrieve the categories for the module
     * @return array|bool Array of Xhelp\FaqCategory
     */
    public function &getCategories()
    {
//        if (!\class_exists('XoopsModules\Smartfaq\Helper')) {
//            return false;
//        }

        if (null !== $this->helper) {
            return false;
        }


        $ret = [];
        // Create an instance of the Xhelp\FaqCategoryHandler
        $faqCategoryHandler = Xhelp\Helper::getInstance()->getHandler('FaqCategory');

        // Get all the categories for the application
        $smartfaqCategoryHandler = $this->helper->getHandler('Category');
        $categories              = $smartfaqCategoryHandler->getCategories(0, 0, -1);

        //Convert the module specific category to the
        //Xhelp\FaqCategory object for standarization
        foreach ($categories as $category) {
            $faqcat = $faqCategoryHandler->create();
            $faqcat->setVar('id', $category->getVar('categoryid'));
            $faqcat->setVar('parent', $category->getVar('parentid'));
            $faqcat->setVar('name', $category->getVar('name'));
            $ret[] = $faqcat;
        }
        unset($categories);

        return $ret;
    }

    /**
     * storeFaq: store the FAQ in the application's specific database (required)
     * @param Xhelp\Faq|null $faq The faq to add
     * @return bool     true (success) / false (failure)
     */
    public function storeFaq($faq = null)
    {
        global $xoopsUser;
        $uid = $xoopsUser->getVar('uid');

        // Take Xhelp\Faq and create faq for smartfaq
        $faqHandler    = Xhelp\Helper::getInstance()->getHandler('Faq');
        $answerHandler = Xhelp\Helper::getInstance()->getHandler('Answer');
        $myFaq         = $faqHandler->create();
        $myAnswer      = $answerHandler->create();            // Creating the answer object

        //$faq->getVar('categories') is an array. If your application
        //only supports single categories use the first element
        //in the array
        $categories = $faq->getVar('categories');
        $categories = (int)$categories[0];       // Change array of categories to 1 category

        $myFaq->setVar('uid', $uid);
        $myFaq->setVar('question', $faq->getVar('problem'));
        $myFaq->setVar('datesub', \time());
        $myFaq->setVar('categoryid', $categories);
        $myFaq->setVar('status', _SF_STATUS_PUBLISHED);

        $ret = $faqHandler->insert($myFaq);
        $faq->setVar('id', $myFaq->getVar('faqid'));

        if ($ret) {   // If faq was stored, store answer
            // Trigger event for question being stored

            $myAnswer->setVar('status', _SF_AN_STATUS_APPROVED);
            $myAnswer->setVar('faqid', $myFaq->faqid());
            $myAnswer->setVar('answer', $faq->getVar('solution'));
            $myAnswer->setVar('uid', $uid);

            $ret = $answerHandler->insert($myAnswer);
        }

        if ($ret && null !== $faq) {
            // Set the new url for the saved FAQ
            $faq->setVar('url', $this->makeFaqUrl($faq));

            // Trigger any module events
            $myFaq->sendNotifications([_SF_NOT_FAQ_PUBLISHED]);
        }

        return $ret;
    }

    /**
     * Create the url going to the faq article
     *
     * @param Xhelp\Faq $faq object
     * @return string
     */
    public function makeFaqUrl($faq): string
    {
        return $this->helper->url('/faq.php?faqid=' . $faq->getVar('id'));
    }
}
