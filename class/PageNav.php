<?php declare(strict_types=1);

namespace XoopsModules\Xhelp;

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

\xoops_load('XoopsPagenav');

/**
 * class PageNav
 */
class PageNav extends \XoopsPageNav
{
    public $bookmark = '';

    /**
     * Xhelp\PageNav constructor.
     * @param int    $total_items
     * @param int    $items_perpage
     * @param int    $current_start
     * @param string $start_name
     * @param string $extra_arg
     * @param string $bookmark
     */
    public function __construct(
        $total_items, $items_perpage, $current_start, $start_name = 'start', $extra_arg = '', string $bookmark = ''
    ) {
        $this->total   = (int)$total_items;
        $this->perpage = (int)$items_perpage;
        $this->current = (int)$current_start;
        if ('' != $bookmark) {
            $this->bookmark = '#' . $bookmark;
        }
        if ('' != $extra_arg && ('&amp;' !== mb_substr($extra_arg, -5) || '&' !== mb_substr($extra_arg, -1))) {
            $extra_arg .= '&amp;';
        }
        $this->url = $_SERVER['SCRIPT_NAME'] . '?' . $extra_arg . \trim($start_name) . '=';
    }

    /**
     * @param int $offset
     * @return string
     */
    public function renderNav($offset = 4): string
    {
        $ret = '';
        if ($this->total <= $this->perpage) {
            return $ret;
        }
        $total_pages = \ceil($this->total / $this->perpage);
        if ($total_pages > 1) {
            $prev = $this->current - $this->perpage;
            if ($prev >= 0) {
                $ret .= '<a href="' . $this->url . $prev . $this->bookmark . '"><u>&laquo;</u></a> ';
            }
            $counter      = 1;
            $current_page = (int)\floor(($this->current + $this->perpage) / $this->perpage);
            while ($counter <= $total_pages) {
                if ($counter == $current_page) {
                    $ret .= '<b>(' . $counter . ')</b> ';
                } elseif (($counter > $current_page - $offset && $counter < $current_page + $offset) || 1 === $counter
                          || $counter == $total_pages) {
                    if ($counter == $total_pages && $current_page < $total_pages - $offset) {
                        $ret .= '... ';
                    }
                    $ret .= '<a href="' . $this->url . (($counter - 1) * $this->perpage) . $this->bookmark . '">' . $counter . '</a> ';
                    if (1 == $counter && $current_page > 1 + $offset) {
                        $ret .= '... ';
                    }
                }
                ++$counter;
            }
            $next = $this->current + $this->perpage;
            if ($this->total > $next) {
                $ret .= '<a href="' . $this->url . $next . $this->bookmark . '"><u>&raquo;</u></a> ';
            }
        }

        return $ret;
    }
}
