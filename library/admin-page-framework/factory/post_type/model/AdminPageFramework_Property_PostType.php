<?php
class AdminPageFramework_Property_PostType extends AdminPageFramework_Property_Base {
    public $_sPropertyType = 'post_type';
    public $sPostType = '';
    public $aPostTypeArgs = array();
    public $sClassName = '';
    public $aColumnHeaders = array('cb' => '<input type="checkbox" />', 'title' => 'Title', 'author' => 'Author', 'comments' => '<div class="comment-grey-bubble"></div>', 'date' => 'Date',);
    public $aColumnSortable = array('title' => true, 'date' => true,);
    public $sCallerPath = '';
    public $aTaxonomies;
    public $aTaxonomyObjectTypes = array();
    public $aTaxonomyTableFilters = array();
    public $aTaxonomyRemoveSubmenuPages = array();
    public $bEnableAutoSave = true;
    public $bEnableAuthorTableFileter = false;
    public function __construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sFieldsType) {
        parent::__construct($oCaller, $sCallerPath, $sClassName, $sCapability, $sTextDomain, $sFieldsType);
        if (!$sCallerPath) {
            return;
        }
        switch ($this->_getCallerType($sCallerPath)) {
            case 'theme':
                add_action('after_switch_theme', array('AdminPageFramework_WPUtility', 'flushRewriteRules'));
            break;
            case 'plugin':
                register_activation_hook($sCallerPath, array('AdminPageFramework_WPUtility', 'flushRewriteRules'));
                register_deactivation_hook($sCallerPath, array('AdminPageFramework_WPUtility', 'flushRewriteRules'));
            break;
        }
    }
}