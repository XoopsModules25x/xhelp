<?php

include_once XOOPS_ROOT_PATH."/class/pagenav.php";

class xhelpPageNav extends XoopsPageNav
{
    var $bookmark='';

    function xhelpPageNav($total_items, $items_perpage, $current_start, $start_name="start", $extra_arg="", $bookmark="")
    {
        $this->total = intval($total_items);
        $this->perpage = intval($items_perpage);
        $this->current = intval($current_start);
        if ($bookmark != '') {
            $this->bookmark = '#'.$bookmark;
        }
        if ( $extra_arg != '' && ( substr($extra_arg, -5) != '&amp;' || substr($extra_arg, -1) != '&' ) ) {
            $extra_arg .= '&amp;';
        }
        $this->url = $_SERVER['PHP_SELF'].'?'.$extra_arg.trim($start_name).'=';
    }

    function renderNav($offset = 4)
    {
        $ret = '';
        if ( $this->total <= $this->perpage ) {
            return $ret;
        }
        $total_pages = ceil($this->total / $this->perpage);
        if ( $total_pages > 1 ) {
            $prev = $this->current - $this->perpage;
            if ( $prev >= 0 ) {
                $ret .= '<a href="'.$this->url.$prev.$this->bookmark.'"><u>&laquo;</u></a> ';
            }
            $counter = 1;
            $current_page = intval(floor(($this->current + $this->perpage) / $this->perpage));
            while ( $counter <= $total_pages ) {
                if ( $counter == $current_page ) {
                    $ret .= '<b>('.$counter.')</b> ';
                } elseif ( ($counter > $current_page-$offset && $counter < $current_page + $offset ) || $counter == 1 || $counter == $total_pages ) {
                    if ( $counter == $total_pages && $current_page < $total_pages - $offset ) {
                        $ret .= '... ';
                    }
                    $ret .= '<a href="'.$this->url.(($counter - 1) * $this->perpage).$this->bookmark.'">'.$counter.'</a> ';
                    if ( $counter == 1 && $current_page > 1 + $offset ) {
                        $ret .= '... ';
                    }
                }
                $counter++;
            }
            $next = $this->current + $this->perpage;
            if ( $this->total > $next ) {
                $ret .= '<a href="'.$this->url.$next.$this->bookmark.'"><u>&raquo;</u></a> ';
            }
        }

        return $ret;
    }

}
