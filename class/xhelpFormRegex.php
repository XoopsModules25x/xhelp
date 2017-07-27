<?php

/**
 * Class XHelpFormRegex
 */
class XHelpFormRegex extends XoopsFormElement
{
    public $_tray;
    public $_select;
    public $_txtbox;
    public $_value;
    public $_caption;

    /**
     * XHelpFormRegex constructor.
     * @param $caption
     * @param $name
     * @param $value
     */
    public function __construct($caption, $name, $value)
    {
        $select_js     = 'onchange="' . $name . '_txtbox.value = this.options[this.selectedIndex].value"';
        $this->_tray   = new XoopsFormElementTray('', '<br><br>', $name);
        $this->_select = new XoopsFormSelect('', $name . '_select', '');
        $this->_txtbox = new XoopsFormText('', $name . '_txtbox', 30, 255, '');
        $this->_select->setExtra($select_js);
        $this->setValue($value);
        $this->setCaption($caption);
    }

    /**
     * @param $regexArray
     */
    public function addOptionArray($regexArray)
    {
        $this->_select->addOptionArray($regexArray);
    }

    /**
     * @param        $value
     * @param string $name
     */
    public function addOption($value, $name = '')
    {
        $this->_select->addOption($value, $name);
    }

    /**
     * @param $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_select->getOptions();
    }

    /**
     * @param bool $encode
     * @return mixed
     */
    public function getCaption($encode = false)
    {
        return $this->_caption;
    }

    /**
     * @param string $caption
     */
    public function setCaption($caption)
    {
        $this->_caption = $caption;
    }

    /**
     * @return string
     */
    public function render()
    {
        //Determine value for selectbox
        $values = $this->_select->getOptions();
        $value  = $this->getValue();

        if (array_key_exists($value, $values)) {
            $this->_select->setValue($value);
            $this->_txtbox->setValue('');
        } else {
            $this->_select->_value = ['0'];
            $this->_txtbox->setValue($value);
        }

        $this->_tray->addElement($this->_select);
        $this->_tray->addElement($this->_txtbox);

        return $this->_tray->render();
    }
}
