<?php

declare(strict_types=1);

namespace XoopsModules\Xhelp;

/**
 * class FaqAdapterAbstract
 */
interface FaqAdapterInterface
{
    /**
     * Perform any initilization needed
     */
    public function init();

    /**
     * Stub function (does nothing)
     */
    public function getCategories();

    /**
     * @return bool true (success)/false (failure)
     */
    public function storeFaq(): bool;

    /**
     * @return Faq object
     */
    public function createFaq(): Faq;

    /**
     * @return bool true (success) / false (failure)
     */
    public function isActive(): bool;

    /**
     * @param \XoopsModules\Xhelp\Xhelp\Faq $faq
     * @return string
     */
    public function makeFaqUrl(Xhelp\Faq $faq): string;
}
