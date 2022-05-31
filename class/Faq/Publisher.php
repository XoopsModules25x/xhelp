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
 * @author       XOOPS Development Team
 */

namespace XoopsModules\Xhelp\Faq;

use XoopsModules\Publisher\Helper as AdapterHelper;
use XoopsModules\Publisher\Constants;
use XoopsModules\Xhelp;

//Sanity Check: make sure that file is not being accessed directly
if (!\defined('XHELP_CLASS_PATH')) {
    exit();
}

// ** Define any site specific variables here **
\define('XHELP_SSECTION_PATH', XOOPS_ROOT_PATH . '/modules/publisher');
\define('XHELP_SSECTION_URL', \XHELP_SITE_URL . '/modules/publisher');
// ** End site specific variables **

// What features should be enabled for new publisher items
\define('XHELP_SSECTION_DOHTML', 0);
\define('XHELP_SSECTION_DOSMILEY', 1);
\define('XHELP_SSECTION_DOBBCODE', 1);
\define('XHELP_SSECTION_DOIMAGE', 0);
\define('XHELP_SSECTION_DOBR', 1);
\define('XHELP_SSECTION_NOTIFYPUB', 1);
\define('XHELP_SSECTION_FORCEAPPROVAL', 0); //Should articles be reviewed prior to submission (0 = Always No, 1 = Always Yes, 2 = Follow Module Config

// @todo - can this declaration be moved into the initialization sequence so
// that each class does not need to include its interface?
//Include the base faqAdapter interface (required)
// require_once XHELP_CLASS_PATH . '/faqAdapter.php';

//These functions are required to work with the publisher application directly
//@require \XHELP_SSECTION_PATH . '/include/functions.php';

/**
 * class Publisher
 */
class Publisher extends Xhelp\FaqAdapterAbstract
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
        'name'            => 'Publisher',
        'author'          => 'Brian Wahoff, Michael Beck',
        'author_email'    => 'ackbarr@xoops.org',
        'description'     => 'Create Publisher pages from xHelp helpdesk tickets',
        'version'         => '1.0',
        'tested_versions' => '1.05 Beta 1',
        'url'             => 'https://xoops.org/',
        'module_dir'      => 'publisher',
    ];

    /**
     * Class Constructor (Required)
     */
    public function __construct()
    {
        if (\class_exists(AdapterHelper::class)) {
            $this->helper  = AdapterHelper::getInstance();
            $this->dirname = $this->helper->dirname();
        }
        // Every class should call parent::init() to ensure that all class level
        // variables are initialized properly.
        $this->init();
    }

    /**
     * getCategories: retrieve the categories for the module
     * @return array|bool Array of Xhelp\FaqCategory
     */
    public function &getCategories()
    {
        $ret = false;
        //        if (!\class_exists('XoopsModules\Publisher\Helper')) {
        //            return false;
        //        }
        if (null === $this->helper) {
            return $ret;
        }

        $ret    = [];
        $helper = Xhelp\Helper::getInstance();
        // Create an instance of the Xhelp\FaqCategoryHandler
        $faqCategoryHandler = $helper->getHandler('FaqCategory');

        // Get all the categories for the application
        $publisherCategoryHandler = $this->helper->getHandler('Category');
        $categories               = $publisherCategoryHandler->getCategories(0, 0, -1);

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
        \ksort($ret);

        return $ret;
    }

    /**
     * storeFaq: store the FAQ in the application's specific database (required)
     * @param Xhelp\Faq|null $faq The faq to add
     * @return bool     true (success) / false (failure)
     */
    public function storeFaq(Xhelp\Faq $faq = null): bool
    {
        global $xoopsUser, $publisher_itemHandler;

        $uid = $xoopsUser->getVar('uid');

        //        if (!\class_exists('XoopsModules\Publisher\Helper')) {
        //            return false;
        //        }
        if (null === $this->helper) {
            return false;
        }

        $this->helper->loadLanguage('admin');

        //fix for publisherItem::store assuming that publisher handlers are globalized //TODO MB adjust for Publisher
        $publisherItemHandler     = $this->helper->getHandler('Item');
        $publisherCategoryHandler = $this->helper->getHandler('Category');

        //        $ssConfig = XoopsModules\Publisher\Utility::getModuleConfig();

        // Create page in publisher from Xhelp\Faq object
        /** @var \XoopsModules\Publisher\Item $itemObj */
        $itemObj = $publisherItemHandler->create();

        //$faq->getVar('categories') is an array. If your application
        //only supports single categories use the first element
        //in the array
        $categories = $faq->getVar('categories');
        $categories = (int)$categories[0];       // Change array of categories to 1 category

        // Putting the values about the ITEM in the ITEM object
        $itemObj->setVar('categoryid', $categories);
        $itemObj->setVar('title', $faq->getVar('subject', 'e'));
        $itemObj->setVar('summary', '[b]' . \ucfirst(\_XHELP_TEXT_PROBLEM) . "[/b]\r\n" . $faq->getVar('problem', 'e'));
        $itemObj->setVar('body', '[b]' . \ucfirst(\_XHELP_TEXT_SOLUTION) . "[/b]\r\n" . $faq->getVar('solution', 'e'));

        $itemObj->setVar('dohtml', \XHELP_SSECTION_DOHTML);
        $itemObj->setVar('dosmiley', \XHELP_SSECTION_DOSMILEY);
        $itemObj->setVar('doxcode', \XHELP_SSECTION_DOBBCODE);
        $itemObj->setVar('doimage', \XHELP_SSECTION_DOIMAGE);
        $itemObj->setVar('dobr', \XHELP_SSECTION_DOBR);
        $itemObj->setVar('notifypub', \XHELP_SSECTION_NOTIFYPUB);
        $itemObj->setVar('uid', $uid);
        $itemObj->setVar('datesub', \time());

        // Setting the status of the item
        if ($this->articleNeedsApproval()) {
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_SUBMITTED);
        } else {
            $itemObj->setVar('status', Constants::PUBLISHER_STATUS_PUBLISHED);
        }

        // Storing the item object in the database
        $ret = $itemObj->store();
        if ($ret && null !== $faq) {
            $faq->setVar('id', $itemObj->getVar('itemid'));
            $faq->setVar('url', $this->makeFaqUrl($faq));

            if ($this->articleNeedsApproval()) {
                if (\XHELP_SSECTION_NOTIFYPUB) {
                    require_once XOOPS_ROOT_PATH . '/include/notification_constants.php';
                    /** @var \XoopsNotificationHandler $notificationHandler */
                    $notificationHandler = \xoops_getHandler('notification');
                    $notificationHandler->subscribe('item', $itemObj->itemid(), 'approved', \XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE);
                }
                // Send notifications
                $itemObj->sendNotifications([\_AM_PUBLISHER_NOITEMS_SUBMITTED]);
            } else {
                // Send notifications
                $itemObj->sendNotifications([\_AM_PUBLISHER_NOITEMS]);
            }
        }

        return $ret;
    }

    /**
     * Create the url going to the faq article
     *
     * @param \XoopsModules\Xhelp\Xhelp\Faq $faq
     * @return string
     */
    public function makeFaqUrl($faq): string
    {
        return \XHELP_SSECTION_URL . '/item.php?itemid=' . $faq->getVar('id');
    }

    /**
     * @return bool
     */
    private function articleNeedsApproval(): bool
    {
        //        $publisherHelper = XoopsModules\Publisher\Helper::getInstance();
        return (\XHELP_SSECTION_FORCEAPPROVAL == 2 && 0 === $this->helper->getConfig('perm_autoapprove'))
               || \XHELP_SSECTION_FORCEAPPROVAL == 1;
    }
}
