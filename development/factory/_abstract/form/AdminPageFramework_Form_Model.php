<?php
/**
 * Admin Page Framework
 * 
 * http://en.michaeluno.jp/admin-page-framework/
 * Copyright (c) 2013-2015 Michael Uno; Licensed MIT
 * 
 */

/**
 * Provides methods to build forms.
 * 
 * @package     AdminPageFramework
 * @subpackage  Form
 * @since       DEVVER
 */
class AdminPageFramework_Form_Model extends AdminPageFramework_Form_Base {

    /**
     * Sets up hooks.
     * @since       DEVVER
     */
    public function __construct() {

        // If the passed action hook is already triggerd, it will trigger the callback right away.
        $this->registerAction(
            $this->aArguments[ 'action_hook_form_registration' ],
            array( $this, '_replyToRegisterFormItems' ),
            100 // priority - low value is set as meta boxes uses the current_screen action hook for `setUp()`.
        );
        
    }
    
    /**
     * Retrieves the submitted form data from $_POST.
     * @since       DEVVER
     * @return      array
     */
    public function getSubmittedData( array $aDataToParse, $bExtractFromFieldStructure=true, $bStripSlashes=true ) {
                
        // Extracts the form data from the subject data for parsing
        $_aSubmittedFormData    = $bExtractFromFieldStructure
            ? $this->castArrayContents( 
                $this->getDataStructureFromAddedFieldsets(), // form data (options) structure
                $aDataToParse   // the subject data array, usually $_POST.
            )
            : $aDataToParse;

        // 3.6.0 - sorts dynamic eleemnts.        
        $_aSubmittedFormData    = $this->getSortedInputs( $_aSubmittedFormData ); 
        
        return $bStripSlashes
            ? stripslashes_deep( $_aSubmittedFormData ) // fixes magic quotes
            : $_aSubmittedFormData;
        
    }
    
    /**
     * Sorts dynamic elements.
     * 
     * The main routine which instantiates the this form calass object will call this method when they retrieve 
     * the submitted form data.
     * 
     * @since       3.6.0
     * @since       DEVVER      Moved from `AdminPageFramework_Factory_Model`.
     * Renamed from `_getSortedInputs()`.
     * @return      array       The sorted input array.
     */
    public function getSortedInputs( array $aFormInputs ) {
        
        $_aDynamicFieldAddressKeys = array_unique(
            array_merge(
                $this->getElementAsArray( 
                    $_POST,
                    '__repeatable_elements_' . $this->aArguments[ 'structure_type' ],
                    array()
                ),
                $this->getElementAsArray( 
                    $_POST,
                    '__sortable_elements_' . $this->aArguments[ 'structure_type' ],
                    array()
                )
            )
        );

        if ( empty( $_aDynamicFieldAddressKeys ) ) {
            return $aFormInputs;
        }

        $_oInputSorter = new AdminPageFramework_Form_Model___Modifier_SortInput( 
            $aFormInputs, 
            $_aDynamicFieldAddressKeys
        );
        return $_oInputSorter->get();
        
    }    
    
    /**
     * Returns a fields model array that represents the structure of the array of saving data from the given fields definition array.
     * 
     * The passed fields array should be structured like the following. This is used for page meta boxes.
     * <code>
     *     array(  
     *         '_default' => array( // _default is reserved for the system.
     *             'my_field_id' => array( .... ),
     *             'my_field_id2' => array( .... ),
     *         ),
     *         'my_secion_id' => array(
     *             'my_field_id' => array( ... ),
     *             'my_field_id2' => array( ... ),
     *             'my_field_id3' => array( ... ),
     *     
     *         ),
     *         'my_section_id2' => array(
     *             'my_field_id' => array( ... ),
     *         ),
     *         ...
     * )
     * </code>
     * It will be converted to 
     * <code>
     *     array(  
     *         'my_field_id' => array( .... ),
     *         'my_field_id2' => array( .... ),
     *         'my_secion_id' => array(
     *             'my_field_id' => array( ... ),
     *             'my_field_id2' => array( ... ),
     *             'my_field_id3' => array( ... ),
     *     
     *         ),
     *         'my_section_id2' => array(
     *             'my_field_id' => array( ... ),
     *         ),
     *         ...
     * )
     * </code>
     * @remark      Just the `_default` section elements get extracted to the upper dimension.
     * @since       3.0.0
     * @since       DEVVER      Moved from `AdminPageFramework_FormDefinition_Base`.
     * Changed the name from `getFieldsModel()`.
     * @return      array
     */
    public function getDataStructureFromAddedFieldsets()  {
                    
        $_aFormDataStructure  = array();
        foreach ( $this->getAsArray( $this->aFieldsets ) as $_sSectionID => $_aFieldsets ) {

            if ( $_sSectionID != '_default' ) {                
                $_aFormDataStructure[ $_sSectionID ] = $_aFieldsets;
                continue;
            }
            
            // For default field items.
            foreach( $_aFieldsets as $_sFieldID => $_aFieldset ) {
                $_aFormDataStructure[ $_aFieldset[ 'field_id' ] ] = $_aFieldset;
            }

        }
        return $_aFormDataStructure;
        
    }    
    
    /**
     * Drops repeatable section and field elements from the given array.
     * 
     * This is used in the filtering method that merges user input data with the saved options. If the user input data includes repeatable sections
     * and the user removed some elements, then the corresponding elements also need to be removed from the options array. Otherwise, the user's removing element
     * remains in the saved option array as the framework performs recursive array merge.
     * 
     * @remark      The options array structure is slightly different from the fields array. An options array does not have '_default' section keys.
     * @remark      If the user capability is insufficient to display the element, it should not be removed because the element(field/section) itself is not submitted and
     * if the merging saved options array misses the element(which this method is going to deal with), the element will be gone forever. 
     * @remark      This method MUST be called after formatting the form elements because this checks the set user capability.
     * @since       3.0.0
     * @since       3.1.1       Made it not remove the repeatable elements if the user capability is insufficient.
     * @since       3.6.2       Changed the mechanism to detect repeatable elements.
     * @since       DEVVER      Moved from `AdminPageFramework_FormDefinition_Base`.
     * @param       array       $aSubject       The subject array to modify. Usually the saved option data.
     * @return      array       The modified options array.
     */
    public function dropRepeatableElements( array $aSubject ) {        
        $_oFilterRepeatableElements = new AdminPageFramework_Form_Model___Modifier_FilterRepeatableElements( 
            $aSubject,
            $this->getElementAsArray(
                $_POST,
                '__repeatable_elements_' . $this->aArguments[ 'structure_type' ]
            )
        );
        return $_oFilterRepeatableElements->get();
    }        
        
    /**
     * @callback    action      'current_screen' by default but it depends on the factory class.
     * @since       DEVVERs
     */
    public function _replyToRegisterFormItems( /* $oScreen */ ) {

        // Check if the form should be created or not.
        if ( ! $this->isInThePage() ) {
            return;
        }
                
        // Load field type definitions.
        $this->_setFieldTypeDefinitions();  

        // Set the options array
        $this->aSavedData = $this->_getSavedData(
            // Merge with the set property and the generated default valus. 
            // This allows external routines to set custom default values prior to the field registration.
            $this->aSavedData + $this->getDefaultFormValues()
        );
        
        /**
         * Call backs registered functions before loading field resources.
         * The `$this->aSavedData` property should be set because it is passed to the validation callback.
         * Note that in each main routine, it may not be necessary to set this value as they have own validation callback and not necessarily 
         * need saved data at this point, such as the taxonomy factory.
         */
        $this->_handleCallbacks();

        // Do not bail even if there is no added field because there can be any externally added fields including page meta boxes.
        // And the validation callback below needs to be triggered.
        // if ( empty( $this->aFieldsets ) ) {            
            // return;
        // } 
        
        // Set field resources (assets) such as javascripts and stylesheets. 
        $_oFieldResources = new AdminPageFramework_Form_Model___SetFieldResources(
            $this->aArguments,
            $this->aFieldsets,
            self::$_aResources,
            $this->aFieldTypeDefinitions,   // must be called after performing `_setFieldTypeDefinitions()`.
            $this->aCallbacks
        );
        self::$_aResources = $_oFieldResources->get(); // updates the property

        /**
         * Call back a validation routine.
         * 
         * The routines of validation and saving data is not the scope this form class
         * as each main routine has own timing and predetermined callbacks for validation.
         * 
         * Also this must be done after the resources are set because there is a callback for 
         * field registration and custom field types uses that hook to set up custom validation routines.
         */
        $this->callBack(
            $this->aCallbacks[ 'handle_form_data' ],
            array(
                $this->aSavedData,      // 1st parameter
                $this->aArguments,      // 2nd parameter
                $this->aSectionsets,    // 3rd parameter
                $this->aFieldsets,      // 4th parameter
            )
        );        
        
    }    
        /**
         * Triggers callbacks before setting resources.
         */
        private function _handleCallbacks() {
         
            // Let the main routine modify the sectionsets definition array.
            $this->aSectionsets = $this->callBack(
                $this->aCallbacks[ 'secitonsets_before_registration' ],
                array(
                    $this->aSectionsets,    // 1st parameter
                )
            );
            // Let the main routine modify the fieldsets definition array.
            $this->aFieldsets = $this->callBack(
                $this->aCallbacks[ 'fieldsets_before_registration' ],
                array(
                    $this->aFieldsets,    // 1st parameter
                    $this->aSectionsets,  // 2nd parameter
                )
            );

        }
    
        /**
         * Stores the default field definitions. 
         * 
         * Once they are set, it no longer needs to be done. For this reason, the scope must be static.
         * 
         * @since       3.1.3
         * @since       DEVVER      Moved from `AdminPageFramework_Factory_Model`.
         * @internal    
         */
        static private $_aFieldTypeDefinitions = array();
        
        /**
         * Loads the default field type definition.
         * 
         * @since       2.1.5
         * @since       3.5.0       Changed the visibility scope to protected as it is internal. 
         * Changed the name from `_loadDefaultFieldTypeDefinitions()` as it applies filters so custom field types also get registered here.
         * @since       DEVVER      Moved from `AdminPageFramework_Factory_Model`. Changed the visibility scope to private.
         * @internal
         */
        private function _setFieldTypeDefinitions() {
            
            $_sCallerID = $this->aArguments[ 'caller_id' ]; // usually a class name

            // This class adds filters for the field type definitions so that framework's default field types will be added.
            $_aCache = $this->getElement( self::$_aFieldTypeDefinitions, $_sCallerID );
            
            if ( empty( $_aCache ) ) {
                $_oBuiltInFieldTypeDefinitions = new AdminPageFramework_Form_Model___BuiltInFieldTypeDefinitions(
                    $_sCallerID,
                    $this->oMsg                 
                );
                self::$_aFieldTypeDefinitions[ $_sCallerID ] = $_oBuiltInFieldTypeDefinitions->get();
            } 
// @todo Invesitigate whether it is appropriate to apply filters per object instance basis.
            $this->aFieldTypeDefinitions = apply_filters(
                'field_types_admin_page_framework',
                self::$_aFieldTypeDefinitions[ $_sCallerID ]
            );

        }        
       
        /**
         * @return      array
         */
        private function _getSavedData( $aDefaultValues ) {
            
            // Retrieve the saved form data and merge with the default values.
            // Do not merge recursively here because there are field values that get messed 
            // such as `select` field type with multiple options.
            $_aSavedData = $this->getAsArray(
                    $this->callBack(
                        $this->aCallbacks[ 'saved_data' ], 
                        array(
                            $aDefaultValues, // default value
                        )
                    )
                )
                + $aDefaultValues;

            $_aLastInputs = $this->getAOrB(
                $this->getElement( $_GET, 'field_errors' ) || isset( $_GET[ 'confirmation' ] ),
                $this->_getLastInputs(),
                array()
            );
                    
            return $_aLastInputs + $_aSavedData;
        }
            /**
             * Returns the last user form input array.
             * 
             * @remark      This temporary data is not always set. This is only set when the form needs to show a confirmation message to the user such as for sending an email.
             * @since       3.3.0
             * @since       3.4.1       Moved from `AdminPageFramework_Property_Page`.
             * @since       DEVVER      Moved from `AdminPageFramework_Property_Base`.
             * @internal
             * @return      array       The last user form inputs.
             */
            private function _getLastInputs() {
                
                $_sKey      = 'apf_tfd' . md5( 'temporary_form_data_' . $this->aArguments[ 'caller_id' ] . get_current_user_id() );
                $_vValue    = $this->getTransient( $_sKey );
                $this->deleteTransient( $_sKey );
                if ( is_array( $_vValue ) ) {
                    return $_vValue;
                }
                return array();
                
            }
            
    /**
     * Returns the default values of all the added fields.
     * 
     * Analyses the registered form elements and retrieve the default values.
     * 
     * @since       3.0.0
     * @since       DEVVER      Changed the name from `getDefaultOptions()`.
     * Moved from `AdminPageFramework_Property_Page`.
     * @return      array       An array holding default values of form data.
     */
    public function getDefaultFormValues() {
        $_oDefaultValues = new AdminPageFramework_Form_Model___DefaultValues(
            $this->aFieldsets
        );
        return $_oDefaultValues->get();
    }
            
    /**
     * Formates the added section-sets and field-sets definition arrays.
     * 
     * This method is called right before the form gets rendered.
     * 
     * @since       DEVVER
     * @param       array       $aSavedData
     * @param       boolean     $bOnlyFieldsets     Whether to format only the fieldsets. The taxonomy field factory uses this parameter.
     */
    protected function _formatElementDefinitions( array $aSavedData ) {
                
        $_oSectionsetsFormatter = new AdminPageFramework_Form_Model___FormatSectionsets(
            $this->aSectionsets, 
            $this->aArguments[ 'structure_type' ], 
            $this->sCapability,
            $this->aCallbacks,
            $this   // caller form object - set to the element definition array
        );
        $this->aSectionsets = $_oSectionsetsFormatter->get();    

        // This must be done after the section-sets are formatted.
        $_oFieldsetsFormatter = new AdminPageFramework_Form_Model___FormatFieldsets(
            $this->aFieldsets,
            $this->aSectionsets,
            $this->aArguments[ 'structure_type' ],
            $this->aSavedData,
            $this->sCapability,
            $this->aCallbacks,
            $this   // caller form object - set to the element definition array
        );
        $this->aFieldsets = $_oFieldsetsFormatter->get();

    }

    /**
     * Retrieves the settings error array set by the user in the validation callback.
     * 
     * @since       3.0.4    
     * @since       3.6.3       Changed the visibility scope to public as a delegation class needs to access this method.
     * @since       DEVVER      Moved from `AdminPageFramework_Factory_Model`.
     * Changed the name from `_getFieldErrors()`. 
     * @access      public      The field type class accesses this method to render nested fields.
     * @internal
     * @param       boolean     $bDelete    whether or not the transient should be deleted after retrieving it. 
     * @return      array
     */
    public function getFieldErrors() {
        $_aErrors = $this->oFieldError->get();
        $this->oFieldError->delete();
        return $_aErrors;
    }   
  
}