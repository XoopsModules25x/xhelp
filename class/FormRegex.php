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
 * @author       XOOPS Development Team
 */

/**
 * class FormRegex
 */
class FormRegex extends \XoopsFormElement
{
    public $_tray;
    public $_select;
    public $_txtbox;
    public $_value;
    //    public $_caption;

    /**
     * Xhelp\FormRegex constructor.
     * @param string $caption
     * @param string $name
     * @param string $value
     */
    public function __construct(string $caption, string $name, string $value)
    {
        $select_js     = 'onchange="' . $name . '_txtbox.value = this.options[this.selectedIndex].value"';
        $this->_tray   = new \XoopsFormElementTray('', '<br><br>', $name);
        $this->_select = new \XoopsFormSelect('', $name . '_select', '');
        $this->_txtbox = new \XoopsFormText('', $name . '_txtbox', 30, 255, '');
        $this->_select->setExtra($select_js);
        $this->setValue($value);
        $this->setCaption($caption);
    }

    /**
     * @param array $regexArray
     */
    public function addOptionArray(array $regexArray): void
    {
        $this->_select->addOptionArray($regexArray);
    }

    /**
     * @param string $value
     * @param string $name
     */
    public function addOption(string $value, string $name = ''): void
    {
        $this->_select->addOption($value, $name);
    }

    /**
     * @param string $value
     */
    public function setValue(string $value): void
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
    public function getOptions(): array
    {
        return $this->_select->getOptions();
    }

    /**
     * @param bool $encode
     * @return string
     */
    public function getCaption($encode = false): string
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
    public function render(): string
    {
        //Determine value for selectbox
        $values = $this->_select->getOptions();
        $value  = $this->getValue();

        if (\array_key_exists($value, $values)) {
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
