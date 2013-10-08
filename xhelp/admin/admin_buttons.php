<?php
//------------------------------------------------------------------------------||
// Creator:		Alan Juden						||
// Creation Date:	8/10/2004						||
// Filename:		admin_buttons.php					||
// Description:		Draws nice looking tabs in the admin section		||
//										||
//------------------------------------------------------------------------------||
/**
 * @todo - comment code (phpdocumentor)
 * @todo - move to XHELP_CLASS_PATH
 * @todo - rename class to something more descriptive
 */
class AdminButtons
{
    var $arrLinks = array();
    var $arrUrls = array();
    var $arrTopLinks = array();
    var $arrTopUrls = array();
    var $admintitle;
    var $selectedtab;

    function AdminButtons()
    {
         
    }

    function AddButton($linkname, $url, $key = '')
    {
        if(!$key){
            $this->arrLinks[] = $linkname;
            $this->arrUrls[] = $url;
        } else {
            $this->arrLinks[$key] = $linkname;
            $this->arrUrls[$key] = $url;
        }
    }

    function AddTopLink($linkname, $url, $key = '')
    {
        if (!$key) {
            $this->arrTopLinks[] = $linkname;
            $this->arrTopUrls[] = $url;
        } else {
            $this->arrTopLinks[$key] = $linkname;
            $this->arrTopUrls[$key] = $url;
        }
    }

    function AddTitle($title)
    {
        $this->admintitle = $title;
    }

    function renderButtons($selectedtab = 0)
    {
        $section = "";
        $i = 0;
         
        if ($selectedtab) {
            $this->setSelectedTab($selectedtab);
        } else {
            $selectedtab = $this->getSelectedTab();
        }
         
        $section .= "<style type='text/css'>@import \"../styles/admin_buttons.css\";</style>";
         
         
        $section .= "<div id=\"buttonNav\">\n";
        $section .= "<h2 id=\"appTitle\">". $this->admintitle . "</h2>\n";
        $section .= "<ul id=\"linkMenu\">\n";
        for ($i = 0; $i < count($this->arrTopLinks); $i++)
        {
            if ($i) {
                $section .= "<li>";
            } else {
                $section .= "<li class=\"first\">";
            }
            $section = $section . "<a href=\"" . $this->arrTopUrls[$i] . "\">" . $this->arrTopLinks[$i] . "</a></li>\n";
        }


        $section .= "</ul>\n";
        $section .= "<ul id=\"buttonMenu\">\n";
        //Add the Tabs
        foreach ($this->arrLinks as $key=>$value){
            if($key == $selectedtab){
                $section .= "<li id=\"current\">";
            } else {
                $section .= "<li>";
            }
            $section .= "<a href=\"" . $this->arrUrls[$key] . "\"><span>" . $this->arrLinks[$key] . "</span></a></li>\n";
        }
        $section .= "</ul>\n";
        $section .= "<br class=\"lclear\" />\n";
        $section .= "</div>\n";
         
        return $section;
    }

    function setSelectedTab($value)
    {
        $this->selectedtab = $value;
    }

    function getSelectedTab()
    {
        return $this->selectedtab;
    }
}
?>