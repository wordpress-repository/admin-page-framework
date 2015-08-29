<?php
abstract class AdminPageFramework_Form_Model_Validation_Opiton extends AdminPageFramework_Form_Model_Export {
    protected function _getFilteredOptions($aInput, $aInputRaw, $aStoredData, $aSubmitInformation, array & $aStatus) {
        $_aData = array('sPageSlug' => $aSubmitInformation['page_slug'], 'sTabSlug' => $aSubmitInformation['tab_slug'], 'aInput' => $this->oUtil->getAsArray($aInput), 'aStoredData' => $aStoredData, 'aStoredTabData' => array(), 'aStoredDataWODynamicElements' => $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$this->oProp->sClassName}", $this->oForm->dropRepeatableElements($aStoredData), $this), 'aStoredTabDataWODynamicElements' => array(), 'aEmbeddedDataWODynamicElements' => array(), 'aSubmitInformation' => $aSubmitInformation,);
        $_aData = $this->_validateEachField($_aData, $aInputRaw);
        $_aData = $this->_validateTabFields($_aData);
        $_aData = $this->_validatePageFields($_aData);
        $_aInput = $this->_getValidatedData("validation_{$this->oProp->sClassName}", call_user_func_array(array($this, 'validate'), array($_aData['aInput'], $_aData['aStoredData'], $this, $_aData['aSubmitInformation'])), $_aData['aStoredData'], $_aData['aSubmitInformation']);
        $_aInput = $this->oUtil->getAsArray($_aInput);
        $_aInput = $this->_getInputByUnset($_aInput);
        $_bHasFieldErrors = $this->hasFieldError();
        if (!$_bHasFieldErrors) {
            return $_aInput;
        }
        $this->_setSettingNoticeAfterValidation(empty($_aInput));
        $this->_setLastInput($aInputRaw);
        $aStatus = $aStatus + array('field_errors' => $_bHasFieldErrors);
        $_oException = new Exception('aReturn');
        $_oException->aReturn = $_aInput;
        throw $_oException;
    }
    private function _getInputByUnset(array $aInput) {
        $_sUnsetKey = '__unset_' . $this->oProp->sFieldsType;
        if (!isset($_POST[$_sUnsetKey])) {
            return $aInput;
        }
        $_aUnsetElements = array_unique($_POST[$_sUnsetKey]);
        foreach ($_aUnsetElements as $_sFlatInputName) {
            $_aDimensionalKeys = explode('|', $_sFlatInputName);
            unset($_aDimensionalKeys[0]);
            $this->oUtil->unsetDimensionalArrayElement($aInput, $_aDimensionalKeys);
        }
        return $aInput;
    }
    private function _validateEachField(array $aData, array $aInputToParse) {
        foreach ($aInputToParse as $_sID => $_aSectionOrFields) {
            if ($this->oForm->isSection($_sID)) {
                if (!$this->_isValidSection($_sID, $aData['sPageSlug'], $aData['sTabSlug'])) {
                    continue;
                }
                foreach ($_aSectionOrFields as $_sFieldID => $_aFields) {
                    $aData['aInput'][$_sID][$_sFieldID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}_{$_sFieldID}", $aData['aInput'][$_sID][$_sFieldID], $this->oUtil->getElement($aData, array('aStoredData', $_sID, $_sFieldID), null), $aData['aSubmitInformation']);
                }
                $_aSectionInput = is_array($aData['aInput'][$_sID]) ? $aData['aInput'][$_sID] : array();
                $_aSectionInput = $_aSectionInput + (isset($aData['aStoredDataWODynamicElements'][$_sID]) && is_array($aData['aStoredDataWODynamicElements'][$_sID]) ? $aData['aStoredDataWODynamicElements'][$_sID] : array());
                $aData['aInput'][$_sID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}", $_aSectionInput, $this->oUtil->getElement($aData, array('aStoredData', $_sID), null), $aData['aSubmitInformation']);
                continue;
            }
            if (!$this->_isValidSection('_default', $aData['sPageSlug'], $aData['sTabSlug'])) {
                continue;
            }
            $aData['aInput'][$_sID] = $this->_getValidatedData("validation_{$this->oProp->sClassName}_{$_sID}", $aData['aInput'][$_sID], $this->oUtil->getElement($aData, array('aStoredData', $_sID), null), $aData['aSubmitInformation']);
        }
        return $aData;
    }
    private function _isValidSection($sSectionID, $sPageSlug, $sTabSlug) {
        if ($sPageSlug && isset($this->oForm->aSections[$sSectionID]['page_slug']) && $sPageSlug !== $this->oForm->aSections[$sSectionID]['page_slug']) {
            return false;
        }
        if ($sTabSlug && isset($this->oForm->aSections[$sSectionID]['tab_slug']) && $sTabSlug !== $this->oForm->aSections[$sSectionID]['tab_slug']) {
            return false;
        }
        return true;
    }
    private function _validateTabFields(array $aData) {
        if (!$aData['sTabSlug'] || !$aData['sPageSlug']) {
            return $aData;
        }
        $aData['aStoredTabData'] = $this->oForm->getTabOptions($aData['aStoredData'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabData'] = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aStoredTabData'], $this);
        $_aOtherTabOptions = $this->oForm->getOtherTabOptions($aData['aStoredData'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabDataWODynamicElements'] = $this->oForm->getTabOptions($aData['aStoredDataWODynamicElements'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aStoredTabDataWODynamicElements'] = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aStoredTabDataWODynamicElements'], $this);
        $aData['aStoredDataWODynamicElements'] = $aData['aStoredTabDataWODynamicElements'] + $aData['aStoredDataWODynamicElements'];
        $_aTabOnlyOptionsWODynamicElements = $this->oForm->getTabOnlyOptions($aData['aStoredTabDataWODynamicElements'], $aData['sPageSlug'], $aData['sTabSlug']);
        $aData['aInput'] = $aData['aInput'] + $_aTabOnlyOptionsWODynamicElements;
        $aData['aInput'] = $this->_getValidatedData("validation_{$aData['sPageSlug']}_{$aData['sTabSlug']}", $aData['aInput'], $aData['aStoredTabData'], $aData['aSubmitInformation']);
        $aData['aEmbeddedDataWODynamicElements'] = $this->_getEmbeddedOptions($aData['aInput'], $aData['aStoredTabDataWODynamicElements'], $_aTabOnlyOptionsWODynamicElements);
        $aData['aInput'] = $aData['aInput'] + $_aOtherTabOptions;
        return $aData;
    }
    private function _validatePageFields(array $aData) {
        if (!$aData['sPageSlug']) {
            return $aData['aInput'];
        }
        $_aPageOptions = $this->oForm->getPageOptions($aData['aStoredData'], $aData['sPageSlug']);
        $_aPageOptions = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_{$aData['sPageSlug']}", $_aPageOptions, $this);
        $_aOtherPageOptions = $this->oUtil->invertCastArrayContents($this->oForm->getOtherPageOptions($aData['aStoredData'], $aData['sPageSlug']), $_aPageOptions);
        $_aPageOptionsWODynamicElements = $this->oUtil->addAndApplyFilter($this, "validation_saved_options_without_dynamic_elements_{$aData['sPageSlug']}", $this->oForm->getPageOptions($aData['aStoredDataWODynamicElements'], $aData['sPageSlug']), $this);
        $_aPageOnlyOptionsWODynamicElements = $this->oForm->getPageOnlyOptions($_aPageOptionsWODynamicElements, $aData['sPageSlug']);
        $aData['aInput'] = $aData['aInput'] + $_aPageOnlyOptionsWODynamicElements;
        $aData['aInput'] = $this->_getValidatedData("validation_{$aData['sPageSlug']}", $aData['aInput'], $_aPageOptions, $aData['aSubmitInformation']);
        $_aPageOptions = $aData['sTabSlug'] && !empty($aData['aStoredTabData']) ? $this->oUtil->invertCastArrayContents($_aPageOptions, $aData['aStoredTabData']) : (!$aData['sTabSlug'] ? array() : $_aPageOptions);
        $_aEmbeddedOptionsWODynamicElements = $aData['aEmbeddedDataWODynamicElements'] + $this->_getEmbeddedOptions($aData['aInput'], $_aPageOptionsWODynamicElements, $_aPageOnlyOptionsWODynamicElements);
        $aData['aInput'] = $aData['aInput'] + $this->oUtil->uniteArrays($_aPageOptions, $_aOtherPageOptions, $_aEmbeddedOptionsWODynamicElements);
        return $aData;
    }
    private function _getEmbeddedOptions(array $aInput, array $aOptions, array $aPageSpecificOptions) {
        $_aEmbeddedData = $this->oUtil->invertCastArrayContents($aOptions, $aPageSpecificOptions);
        return $this->oUtil->invertCastArrayContents($_aEmbeddedData, $aInput);
    }
    private function _getValidatedData($sFilterName, $aInput, $aStoredData, $aSubmitInfo = array()) {
        return $this->oUtil->addAndApplyFilter($this, $sFilterName, $aInput, $aStoredData, $this, $aSubmitInfo);
    }
    protected function _setSettingNoticeAfterValidation($bIsInputEmtpy) {
        if ($this->hasSettingNotice()) {
            return;
        }
        $this->setSettingNotice($this->oUtil->getAOrB($bIsInputEmtpy, $this->oMsg->get('option_cleared'), $this->oMsg->get('option_updated')), $this->oUtil->getAOrB($bIsInputEmtpy, 'error', 'updated'), $this->oProp->sOptionKey, false);
    }
}