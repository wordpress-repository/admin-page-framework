<?php
/**
 Admin Page Framework v3.7.3b02 by Michael Uno
 Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
 <http://en.michaeluno.jp/admin-page-framework>
 Copyright (c) 2013-2015, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT>
 */
class AdminPageFramework_Form_Model___FormatDynamicElements extends AdminPageFramework_FrameworkUtility {
    public $aSectionsets = array();
    public $aFieldsets = array();
    public $aSavedFormData = array();
    public function __construct() {
        $_aParameters = func_get_args() + array($this->aSectionsets, $this->aFieldsets, $this->aSavedFormData,);
        $this->aSectionsets = $_aParameters[0];
        $this->aFieldsets = $_aParameters[1];
        $this->aSavedFormData = $_aParameters[2];
    }
    public function get() {
        $this->_setDynamicElements($this->aSavedFormData);
        return $this->aFieldsets;
    }
    private function _setDynamicElements($aOptions) {
        $aOptions = $this->castArrayContents($this->aSectionsets, $aOptions);
        foreach ($aOptions as $_sSectionID => $_aSubSectionOrFields) {
            $_aSubSection = $this->_getSubSectionFromOptions($_sSectionID, $this->getAsArray($_aSubSectionOrFields));
            if (empty($_aSubSection)) {
                continue;
            }
            $this->aFieldsets[$_sSectionID] = $_aSubSection;
        }
    }
    private function _getSubSectionFromOptions($_sSectionID, array $_aSubSectionOrFields) {
        $_aSubSection = array();
        $_iPrevIndex = null;
        foreach ($_aSubSectionOrFields as $_isIndexOrFieldID => $_aSubSectionOrFieldOptions) {
            if (!$this->isNumericInteger($_isIndexOrFieldID)) {
                continue;
            }
            $_iIndex = $_isIndexOrFieldID;
            $_aSubSection[$_iIndex] = $this->_getSubSectionItemsFromOptions($_aSubSection, $_sSectionID, $_iIndex, $_iPrevIndex);
            foreach ($_aSubSection[$_iIndex] as & $_aField) {
                $_aField['_section_index'] = $_iIndex;
            }
            unset($_aField);
            $_iPrevIndex = $_iIndex;
        }
        return $_aSubSection;
    }
    private function _getSubSectionItemsFromOptions(array $_aSubSection, $_sSectionID, $_iIndex, $_iPrevIndex) {
        if (!isset($this->aFieldsets[$_sSectionID])) {
            return array();
        }
        $_aFields = isset($this->aFieldsets[$_sSectionID][$_iIndex]) ? $this->aFieldsets[$_sSectionID][$_iIndex] : $this->getNonIntegerKeyElements($this->aFieldsets[$_sSectionID]);
        return !empty($_aFields) ? $_aFields : $this->getElementAsArray($_aSubSection, $_iPrevIndex, array());
    }
}