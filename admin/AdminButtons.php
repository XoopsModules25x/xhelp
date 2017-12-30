<?php
//------------------------------------------------------------------------------||
// Creator:     Alan Juden                      ||
// Creation Date:   8/10/2004                       ||
// Filename:        AdminButtons.php                   ||
// Description:     Draws nice looking tabs in the admin section        ||
//                                      ||
//------------------------------------------------------------------------------||

/**
 * @todo - comment code (phpdocumentor)
 * @todo - move to XHELP_CLASS_PATH
 * @todo - rename class to something more descriptive
 */
class AdminButtons
{
    public $arrLinks    = [];
    public $arrUrls     = [];
    public $arrTopLinks = [];
    public $arrTopUrls  = [];
    public $admintitle;
    public $selectedtab;

    /**
     * AdminButtons constructor.
     * @param        $linkname
     * @param        $url
     * @param string $key
     */
    public function __construct($linkname, $url, $key = '')
    {
        if (!$key) {
            $this->arrLinks[] = $linkname;
            $this->arrUrls[]  = $url;
        } else {
            $this->arrLinks[$key] = $linkname;
            $this->arrUrls[$key]  = $url;
        }
    }

    /**
     * @param        $linkname
     * @param        $url
     * @param string $key
     */
    public function AddTopLink($linkname, $url, $key = '')
    {
        if (!$key) {
            $this->arrTopLinks[] = $linkname;
            $this->arrTopUrls[]  = $url;
        } else {
            $this->arrTopLinks[$key] = $linkname;
            $this->arrTopUrls[$key]  = $url;
        }
    }

    /**
     * @param $title
     */
    public function AddTitle($title)
    {
        $this->admintitle = $title;
    }

    /**
     * @param int $selectedtab
     * @return string
     */
    public function renderButtons($selectedtab = 0)
    {
        $section = '';
        $i       = 0;

        if ($selectedtab) {
            $this->setSelectedTab($selectedtab);
        } else {
            $selectedtab = $this->getSelectedTab();
        }

        $section .= "<style type='text/css'>@import \"../assets/css/admin_buttons.css\";</style>";

        $section .= "<div id=\"buttonNav\">\n";
        $section .= '<h2 id="appTitle">' . $this->admintitle . "</h2>\n";
        $section .= "<ul id=\"linkMenu\">\n";
        for ($i = 0, $iMax = count($this->arrTopLinks); $i < $iMax; ++$i) {
            if ($i) {
                $section .= '<li>';
            } else {
                $section .= '<li class="first">';
            }
            $section = $section . '<a href="' . $this->arrTopUrls[$i] . '">' . $this->arrTopLinks[$i] . "</a></li>\n";
        }

        $section .= "</ul>\n";
        $section .= "<ul id=\"buttonMenu\">\n";
        //Add the Tabs
        foreach ($this->arrLinks as $key => $value) {
            if ($key == $selectedtab) {
                $section .= '<li id="current">';
            } else {
                $section .= '<li>';
            }
            $section .= '<a href="' . $this->arrUrls[$key] . '"><span>' . $this->arrLinks[$key] . "</span></a></li>\n";
        }
        $section .= "</ul>\n";
        $section .= "<br class=\"lclear\">\n";
        $section .= "</div>\n";

        return $section;
    }

    /**
     * @param $value
     */
    public function setSelectedTab($value)
    {
        $this->selectedtab = $value;
    }

    public function getSelectedTab()
    {
        return $this->selectedtab;
    }
}
