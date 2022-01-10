<?php declare(strict_types=1);

/**
 * File: calendar.php | (c) dynarch.com 2004
 *                         Distributed as part of "The Coolest DHTML Calendar"
 *                         under the same terms.
 *                         -----------------------------------------------------------------
 *                         This file implements a simple PHP wrapper for the calendar.  It
 *                         allows you to easily include all the calendar files and setup the
 *                         calendar by instantiating and calling a PHP object.
 */
define('NEWLINE', "\n");

/**
 * DHTML_Calendar
 *
 * @author    John
 * @copyright Copyright (c) 2009
 */
class calendar
{
    public $calendar_lib_path;
    public $calendar_file;
    public $calendar_lang_file;
    public $calendar_setup_file;
    public $calendar_theme_file;
    public $calendar_options;

    /**
     * DHTML_Calendar::DHTML_Calendar()
     *
     * @param string $calendar_lib_path
     * @param string $lang
     * @param string $theme
     * @param mixed  $stripped
     * @param array  $calendar_options
     * @param array  $calendar_field_attributes
     */
    public function __construct(
        string $calendar_lib_path = '',
        string $lang = 'en',
        string $theme = 'calendar-win2k-1',
        $stripped = false,
        array $calendar_options = [],
        array $calendar_field_attributes = []
    ) {
        $this->set_option('date', '');
        $this->set_option('ifFormat', '%m/%d/%Y %H:%M');
        $this->set_option('daFormat', '%m/%d/%Y %H:%M');
        $this->set_option('firstDay', 1); // show Monday first
        $this->set_option('showOthers', true);

        if ($stripped) {
            $this->calendar_file       = 'calendar_stripped.js';
            $this->calendar_setup_file = 'calendar-setup_stripped.js';
        } else {
            $this->calendar_file       = 'calendar.js';
            $this->calendar_setup_file = 'calendar-setup.js';
        }

        $lang                      = file_exists(XOOPS_ROOT_PATH . 'modules/xhelp/assets/js/calendar/lang/calendar-' . _LANGCODE . '.js') ? _LANGCODE : 'en';
        $this->calendar_lang_file  = 'lang/calendar-' . $lang . '.js';
        $this->calendar_lib_path   = '/modules/xhelp/assets/js/calendar/';
        $this->calendar_theme_file = 'calendar-blue.css';
        $this->calendar_theme_url  = 'modules/xhelp/assets/js/calendar/css/';
    }

    /**
     * @param $name
     * @param $value
     */
    public function set_option($name, $value)
    {
        $this->calendar_options[$name] = $value;
    }

    /**
     * DHTML_Calendar::load_files()
     */
    public function load_files()
    {
        $this->get_load_files_code();
    }

    /**
     * DHTML_Calendar::get_load_files_code()
     */
    public function get_load_files_code()
    {
        if (isset($GLOBALS['xo_Theme'])) {
            $GLOBALS['xo_Theme']->addStylesheet($this->calendar_theme_url . $this->calendar_theme_file);
            $GLOBALS['xo_Theme']->addScript($this->calendar_lib_path . $this->calendar_file);
            $GLOBALS['xo_Theme']->addScript($this->calendar_lib_path . $this->calendar_lang_file);
            $GLOBALS['xo_Theme']->addScript($this->calendar_lib_path . $this->calendar_setup_file);
        } else {
            $ret = '<link rel="stylesheet" type="text/css" media="all" href="' . XOOPS_URL . '/' . $this->calendar_theme_url . $this->calendar_theme_file . '">';
            $ret .= '<script type="text/javascript" src="' . XOOPS_URL . '/' . $this->calendar_lib_path . $this->calendar_file . '"></script>';
            $ret .= '<script type="text/javascript" src="' . XOOPS_URL . '/' . $this->calendar_lib_path . $this->calendar_lang_file . '"></script>';
            $ret .= '<script type="text/javascript" src="' . XOOPS_URL . '/' . $this->calendar_lib_path . $this->calendar_setup_file . '"></script>';
            echo $ret;
        }
    }

    /**
     * DHTML_Calendar::_make_calendar()
     *
     * @param array $other_options
     * @return string
     */
    public function _make_calendar(array $other_options = []): string
    {
        $js_options = $this->_make_js_hash(array_merge($this->calendar_options, $other_options));
        $code       = ('<script type="text/javascript">Calendar.setup({' . $js_options . '});</script>');

        return $code;
    }

    /**
     * DHTML_Calendar::make_input_field()
     *
     * @param array $cal_options
     * @param array $field_attributes
     * @param mixed $show
     * @return string
     */
    public function make_input_field(array $cal_options = [], array $field_attributes = [], $show = true): string
    {
        $id      = $this->_gen_id();
        $attrstr = $this->_make_html_attr(array_merge($field_attributes, ['id' => $this->_field_id($id), 'type' => 'text']));
        $data    = '<input ' . $attrstr . '>';
        $data    .= '<a href="#" id="' . $this->_trigger_id($id) . '">' . '&nbsp;<img src="' . XOOPS_URL . '/' . $this->calendar_lib_path . 'img.png" style="vertical-align: middle; border: 0px;" alt=""></a>&nbsp;';
        $options = array_merge(
            $cal_options,
            [
                'inputField' => $this->_field_id($id),
                'button'     => $this->_trigger_id($id),
            ]
        );
        $data    .= $this->_make_calendar($options);
        if ($show) {
            echo $data;

            return '';
        }

        return $data;
    }

    // / PRIVATE SECTION

    /**
     * @param $id
     * @return string
     */
    public function _field_id($id): string
    {
        return 'f-calendar-field-' . $id;
    }

    /**
     * @param $id
     * @return string
     */
    public function _trigger_id($id): string
    {
        return 'f-calendar-trigger-' . $id;
    }

    /**
     * @return int
     */
    public function _gen_id(): int
    {
        static $id = 0;

        return ++$id;
    }

    /**
     * @param $array
     * @return string
     */
    public function _make_js_hash($array): string
    {
        $jstr = '';
        //        reset($array);
        //        while (list($key, $val) = each($array)) {
        foreach ($array as $key => $val) {
            if (is_bool($val)) {
                $val = $val ? 'true' : 'false';
            } elseif (!is_numeric($val)) {
                $val = '"' . $val . '"';
            }
            if ($jstr) {
                $jstr .= ',';
            }
            $jstr .= '"' . $key . '":' . $val;
        }

        return $jstr;
    }

    /**
     * @param $array
     * @return string
     */
    public function _make_html_attr($array): string
    {
        $attrstr = '';
        //        reset($array);
        //        while (list($key, $val) = each($array)) {
        foreach ($array as $key => $val) {
            $attrstr .= $key . '="' . $val . '" ';
        }

        return $attrstr;
    }
}
