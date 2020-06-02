<?php

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Utility\StringUtility;

/***************************************************************
*  Copyright notice
*
*  (c) 2016-2017 Rene Nitzsche (rene@system25.de)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Rendert ein einfaches Input-Field.
 */
class Tx_Rnbase_Backend_Form_Element_InputText extends AbstractFormElement
{
    /**
     * @param array $data not used right now!
     */
    public function __construct(NodeFactory $nodeFactory, array $data)
    {
        // nodeFactory is not used and will be removed in later version
        parent::__construct($nodeFactory, $data);
    }

    public function render()
    {
    }

    public function renderHtml($name, $value, $config)
    {
        $width = $config['width'];
        $evalList = '';
        if (isset($config['eval'])) {
            //TYPO3 7.6 uses $config['eval'][0]
            if (is_array($config['eval']) && isset($config['eval'][0])) {
                $evalList = $config['eval'][0];
            } else {
                $evalList = $config['eval'];
            }
        }
        $evalList = Tx_Rnbase_Utility_Strings::trimExplode(',', $evalList, true);

        $classes = [];
        $attributes = [];

        if (in_array('datetime', $evalList, true) || in_array('date', $evalList)) {
            $classes[] = 't3js-datetimepicker';
            if (in_array('datetime', $evalList)) {
                $attributes['data-date-type'] = 'datetime';
            } elseif (in_array('date', $evalList)) {
                $attributes['data-date-type'] = 'date';
            }
            if (isset($config['range']['lower'])) {
                $attributes['data-date-minDate'] = (int) $config['range']['lower'];
            }
            if (isset($config['range']['upper'])) {
                $attributes['data-date-maxDate'] = (int) $config['range']['upper'];
            }
            $icon = 'actions-edit-pick-date';
        } elseif (in_array('time', $evalList)) {
            $classes[] = 't3js-datetimepicker';
            $attributes['data-date-type'] = 'time';
            $icon = 'actions-edit-pick-date';
        } elseif (in_array('timesec', $evalList)) {
            $classes[] = 't3js-datetimepicker';
            $attributes['data-date-type'] = 'timesec';
            $icon = 'actions-edit-pick-date';
        }

        // for data-formengine-input-params
        $paramsList = [
            'field' => $name,
            'evalList' => implode(',', $evalList),
            'is_in' => '',
        ];

        $attributes['data-formengine-validation-rules'] = $this->getValidationDataAsJsonString($config);
        $attributes['data-formengine-input-params'] = json_encode($paramsList);
        $attributes['data-formengine-input-name'] = htmlspecialchars($name);
        $attributes['id'] = StringUtility::getUniqueId('formengine-input-');
        $attributes['value'] = '';

        if (isset($config['max']) && (int) $config['max'] > 0) {
            $attributes['maxlength'] = (int) $config['max'];
        }
        if (!empty($classes)) {
            $attributes['class'] = implode(' ', $classes);
        }

        $attributeString = '';
        foreach ($attributes as $attributeName => $attributeValue) {
            $attributeString .= ' '.$attributeName.'="'.htmlspecialchars($attributeValue).'"';
        }

        $width = $width ? 'width:' . ceil($width * 9.58) . 'px;' : 'form-control form-control-adapt';
        $style = sprintf(' style="%s"', $width);
        $html = '
         <input type="text"'
                .$attributeString
                .$style
        .' />';

        // This is the ACTUAL form field - values from the EDITABLE field must be transferred to this field which is the one that is written to the database.
        $html .= '<input type="hidden" name="'.$name.'" value="'.htmlspecialchars($value).'" />';

        if ($icon) {
            $html .= Tx_Rnbase_Backend_Utility_Icons::getSpriteIcon($icon);
        }

        return $html;
    }
}
