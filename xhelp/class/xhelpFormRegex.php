<?php

class xhelpFormRegex extends XoopsFormElement
{
    var $_tray;
    var $_select;
    var $_txtbox;
    var $_value;
    var $_caption;

    function xhelpFormRegex($caption, $name, $value)
    {
        $select_js = 'onchange="'. $name.'_txtbox.value = this.options[this.selectedIndex].value"';
        $this->_tray = new XoopsFormElementTray('', '<br /><br />', $name);
        $this->_select = new XoopsFormSelect('', $name.'_select', '');
        $this->_txtbox = new XoopsFormText('', $name.'_txtbox', 30, 255, '');
        $this->_select->setExtra($select_js);
        $this->setValue($value);
        $this->setCaption($caption);
    }

    function addOptionArray($regexArray)
    {
        $this->_select->addOptionArray($regexArray);
    }

    function addOption($value, $name="")
    {
        $this->_select->addOption($value, $name);
    }

    function setValue($value)
    {
        $this->_value = $value;
    }

    function getValue()
    {
        return $this->_value;
    }

    function getOptions()
    {
        return $this->_select->getOptions();
    }

    function getCaption()
    {
        return $this->_caption;
    }
     
    function setCaption($caption)
    {
        $this->_caption = $caption;
    }
    function render()
    {
        //Determine value for selectbox
        $values = $this->_select->getOptions();
        $value = $this->getValue();

        if (array_key_exists($value, $values)) {

            $this->_select->setValue($value);
            $this->_txtbox->setValue('');
        } else {
            $this->_select->_value = array('0');
            $this->_txtbox->setValue($value);
        }
         
        $this->_tray->addElement($this->_select);
        $this->_tray->addElement($this->_txtbox);
        return $this->_tray->render();
    }
}
?>