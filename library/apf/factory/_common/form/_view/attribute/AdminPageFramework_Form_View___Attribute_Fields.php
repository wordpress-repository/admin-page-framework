<?php 
/**
	Admin Page Framework v3.7.13 by Michael Uno 
	Generated by PHP Class Files Script Generator <https://github.com/michaeluno/PHP-Class-Files-Script-Generator>
	<http://en.michaeluno.jp/admin-page-framework>
	Copyright (c) 2013-2016, Michael Uno; Licensed under MIT <http://opensource.org/licenses/MIT> */
class AdminPageFramework_Form_View___Attribute_Fields extends AdminPageFramework_Form_View___Attribute_FieldContainer_Base {
    public $sContext = 'fields';
    public $iFieldsCount = 0;
    public function __construct() {
        $_aParameters = func_get_args() + array($this->aArguments, $this->aAttributes, $this->iFieldsCount,);
        $this->aArguments = $_aParameters[0];
        $this->aAttributes = $_aParameters[1];
        $this->iFieldsCount = $_aParameters[2];
    }
    protected function _getAttributes() {
        return array('id' => $this->sContext . '-' . $this->aArguments['tag_id'], 'class' => 'admin-page-framework-' . $this->sContext . $this->getAOrB($this->aArguments['repeatable'], ' repeatable dynamic-fields', '') . $this->getAOrB($this->aArguments['sortable'], ' sortable dynamic-fields', ''), 'data-type' => $this->aArguments['type'], 'data-largest_index' => max(( int )$this->iFieldsCount - 1, 0), 'data-field_name_model' => $this->aArguments['_field_name_model'], 'data-field_name_flat' => $this->aArguments['_field_name_flat'], 'data-field_name_flat_model' => $this->aArguments['_field_name_flat_model'], 'data-field_tag_id_model' => $this->aArguments['_tag_id_model'], 'data-field_address' => $this->aArguments['_field_address'], 'data-field_address_model' => $this->aArguments['_field_address_model'],);
    }
}
