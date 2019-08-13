<?php
/**
 * Adapter.php
 * 
 * Product Adapter.
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Adapter extends Varien_Object
{
    /**
     * Save a product
     * 
     * @param   Advertise_Importer_Model_Import
     */
    public function save($data)
    {
        $this->_saveProduct($data);
    }
    
    /**
     * Save a Product
     * 
     * @param   Advertise_Importer_Model_Import
     * @return  int
     */
    protected function _saveProduct($item)
    {
        $product = Mage::getModel('catalog/product')->load($item->getProductId());
        /* @var $product Mage_Catalog_Model_Product */
        
        if ( ! $product) { // update only
            return FALSE;
        }

        $attributeSetId = $product->getAttributeSetId();     
        $attributeSet = $this->_getAttributeSet($attributeSetId);
        /**
         * Get Attributes to update / add from the feed.
         */
        $attributes = $item->getAttributes();        
        foreach($attributes as $name => $updateAttArray) {
            $attributeValue = $updateAttArray[$name];
            $xmlOptions = $this->_getAttributeXmlOptions($updateAttArray);            
            /**
             * Attribute Doesn't exist, let create it.
             */
            if( ! $this->_attributeExists($product, $name)) {
                $attributeData = array('label'  => $xmlOptions['admintitle']);
                
                if(isset($xmlOptions['scope'])) {
                    $attributeData['global'] = $this->_getIsGlobalValue($xmlOptions['scope']);
                }
                
                if(isset($xmlOptions['type'])) {
                    $inputType = $this->_getAttributeTypeValue($xmlOptions['type']);
                    $inputTypeArray = $this->_getInputTypeArray($inputType);
                    $attributeData['input'] = $inputType;                    
                    $attributeData = array_merge($attributeData, $inputTypeArray);
                }
                
                $attributeId = $this->_createAttribute($name, $attributeData);
                $this->_addAttributeToSet($attributeSetId, $attributeId);
            }
            
            $this->_updateAttributeValue($product, $name, $attributeValue);
            
        }         

        try {
            $product->save();
        }
        catch (Mage_Core_Exception $e) {
            echo $e->getMessage(); 
            Mage::log($e->getMessage());
        }

        $id = $product->getId();
        unset($product);
        return $id;
    }
    
    /**
     * Validate the options before we make a new attribute
     * 
     * @param   array
     * @return  bool
     */
    protected function _getDefaultOptionValues($options)
    {
        /**
         * @todo here we should merge with some defaults, but
         * first we would need to remove any empty values...
         */
        return $options;
    }
    
    /**
     * Does this attribute have any attributes?
     * 
     * @param   array
     * @return  bool
     */
    protected function _hasXmlOptions($attribute)
    {
        return isset($attribute['#attributes']);
    }
    
    /**
     * Get the attributes associated with this attribute.
     * 
     * they are the XML attributes/options we have parsed
     * such as scope, adminhtml etc
     * 
     * @param   array
     * @return  array
     */
    protected function _getAttributeXmlOptions($attribute)
    {
        return isset($attribute['#attributes'])
            ? $attribute['#attributes']
            : NULL;
    }
    
    /**
     * Get an attribute by it's code
     * 
     * @param   string
     * @return  
     */
    protected function _getAttribute($attributeCode)
    {
        //$_product->getResource()->getAttribute('my_attribute');
        $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product', $attribute);
        
        return $attribute;
    }
    
    /**
     * Update an attribute value
     * 
     * @param   Mage_Catalog_Model_Product
     * @param   string
     * @param   mixed
     */
    protected function _updateAttributeValue($product, $attributeCode, $value)
    {
        $attribute = $product->getResource()->getAttribute($attributeCode);
        $type = $attribute->getBackendType();
        /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        
        // conver disabled/enabled to int
        if($attributeCode == 'status') {
            $product->setStatus($this->_getDisabledValue($value));
            return;
        }
        // convert yes/no to booleans
        if(($type == 'int' || $type == 'decimal') && in_array($value, array('yes', 'no'))) {
            $value = $this->_getYesNoValue($value);
            $product->setData($attributeCode, $value);
            return;
        }
        
        if($attribute->usesSource()) {
             // update the option...
             $optionValue = $this->_addAttributeOption($attributeCode, $value);
             $product->setData($attributeCode, $optionValue);
         }
         else {
             // just update value on the product...
             $product->setData($attributeCode, $value);
         }
    }
    
    /**
     * Get all availabile attribute types
     * 
     * @return  array
     */
    protected function _getAttributeInputTypes()
    {
        return Mage::getModel('catalog/product_attribute_source_inputtype')->toOptionArray();
    }
    
    /**
     * Get an array of Input type => backend model
     * 
     * @param   string
     * @return  array
     */
    protected function _getInputTypeArray($type)
    {
        //$model = Mage::getModel('catalog/resource_eav_attribute');
        $types = array(
            'text'      => array(
                'type'  => 'varchar', // or 'text'
                'input' => 'text',
                //'source'=> '', //$model->getBackendTypeByInput('varchar');
            ),
            'boolean'   => array(
                'type'  => 'int',
                'input' => 'select',
                'source'=> 'eav/entity_attribute_source_boolean',
            ),
            'textarea'   => array(
                'type'              => 'text',
                'input'             => 'textarea',
                //'source'            => '',
                'wysiwyg_enabled'   => 1,
            ),
            'multiselect'   => array(
                'type'  => 'text',      // varchar??
                'input' => 'multiselect',
                //'source'=> '',
            ),
            'select'   => array(
                'type'  => 'int', // varchar/text ????????????
                'input' => 'select',
                //'source'=> '',
            ),
            'dropdown'  => array(
                'type'  => 'int', // varchar/text ????????????
                'input' => 'select',
                //'source'=> '',
            ),
        );
        
        return $types[$type];
    }
    
    /**
     * Get the type of attribute we are creating given the
     * XML value we recieve.
     * 
     * @param   string
     * @return  string
     */
    protected function _getAttributeTypeValue($type, $default = 'text')
    {
        $types = array(
            'text'              => 'text',
            'dropdown'          => 'select',
            'yes/no'            => 'boolean',
            'multipleselect'    => 'multiselect',
            'textarea'          => 'textarea',
        );
        
        return isset($types[$type]) ? $types[$type] : $default;
    }
    
    /**
     * Convertin inputs
     * 
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract
     * @param   mixed
     */
    protected function _convertInputValue($attribute, $value)
    {
        $type = $attribute->getBackendType();
        /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
        
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        }
        
        if($type == 'int' || $type == 'decimal' && in_array($value, array('yes', 'no'))) {
            $value = $this->_getYesNoValue($value);
        }
        
        //if($attribute->getAttributeCode() == 'status') {
        //    $value = $this->_getDisabledValue($value);
        //}
        
        return $value;
    }
    
    /**
     * Get the boolean value to use for Yes/No
     * 
     * @param   string
     * @return int
     */
    protected function _getYesNoValue($value)
    {
        $value = strtolower($value);
        $values = array('no' => 0, 'yes' => 1);
        
        return isset($values[$value]) ? $values[$value] : 0;
    }
    
    /**
     * Get the value of is_global we will use, from the scope value we have set in 
     * the XML feed.
     *
     * @param   string
     * @param   int
     * @return  int
     */
    protected function _getIsGlobalValue($scope, $default = Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE)
    {
        $array = array(
            'website'   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE,
            'global'    => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
            'store'     => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        );
        
        return isset($array[$scope]) 
            ? $array[$scope] 
            : $default;
    }
    
    /**
     * Get the value to use for disabling products
     * 
     * @param   string
     * @return  int
     */
    protected function _getDisabledValue($disabled)
    {
        return $disabled == 'Disabled' ? 2 : 1;
    }
    
    /**
     * Is an attribute in the given product / set??
     * 
     * NOT TESTED
     * 
     * @param   Mage_Catalog_Model_Product
     */
    protected function _attributeExists(Mage_Catalog_Model_Product $product, $attributeCode)
    {
        $hasAtt = $product->getResource()->getAttribute($attributeCode);
        
        return $hasAtt !== FALSE;
    }
    
    /**
     * Is an attribute in a set?
     * 
     * @param   Mage_Catalog_Model_Product
     * @param   int
     * @return  bool
     */
    protected function _attributeIsInSet(Mage_Catalog_Model_Product $product, $attributeId)
    {
        $attributes = $product->getTypeInstance()->getSetAttributes();
        foreach($attributes as $att) {
            if($attributeId == $att->getId()) {
                return TRUE;
            }
        }
        
        return FALSE;
    }
    
    /**
     * Retrieve attribute options
     *
     * @param int
     * @param string|int
     * @return array
     */
    protected function _getAttributeOptions($attributeId, $store = null)
    {
        //$storeId = $this->_getStoreId($store);
        $attribute = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->getResource()
            ->getAttribute($attributeId)
            ->setStoreId($storeId);

        /* @var $attribute Mage_Catalog_Model_Entity_Attribute */
        if ( ! $attribute) {
            throw new Exception("Attribute doesn't exist: $attributeId");
        }
        $options = array();
        if ($attribute->usesSource()) {
            foreach ($attribute->getSource()->getAllOptions() as $optionId => $optionValue) {
                if (is_array($optionValue)) {
                    $options[] = $optionValue;
                }
                else {
                    $options[] = array(
                        'value' => $optionId,
                        'label' => $optionValue
                    );
                }
            }
        }
        return $options;
    }

    /**
     * Does an attribute have an option?
     *
     * @param   string
     * @param   mixed
     * @return  bool
     */
    protected function _attributeOptionExists($attributeCode, $value)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_options_model = Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code = $attribute_model->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attribute_model->load($attribute_code);

        $attribute_table = $attribute_options_model->setAttribute($attribute);
        $options = $attribute_options_model->getAllOptions(FALSE);

        foreach($options as $option) {
            if ($option['label'] == $value) {
                return $option['value'];
            }
        }

        return FALSE;
    }

    /**
     * Add a new option for an attribute
     *
     * @param   string
     * @param   mixed
     * @return  bool
     */
    protected function _addAttributeOption($argAttribute, $argValue)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_options_model = Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code = $attribute_model->getIdByCode('catalog_product', $argAttribute);
        $attribute = $attribute_model->load($attribute_code);

        $attribute_table = $attribute_options_model->setAttribute($attribute);
        $options = $attribute_options_model->getAllOptions(FALSE);

        if( ! $this->_attributeOptionExists($argAttribute, $argValue)) {
            $value['option'] = array($argValue, $argValue);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }

        foreach($options as $option) {
            if ($option['label'] == $argValue) {
                return $option['value'];
            }
        }
        return TRUE;
    }
    
    /**
     * Load an attribute set
     * @param   int
     */
    protected function _getAttributeSet($id)
    {
        $attributeSet = Mage::getModel('eav/entity_attribute_set')
            ->load($id)
            //->load($attrSetName, 'attribute_set_name')
        ;
        
        return $attributeSet;
    }
    
     /**
     * Add an attribute to an attribute set
     *
     * @param <type> $attributeSetId
     * @param <type> $attributeGroupId
     * @param <type> $attributeId
     */
    protected function _addAttributeToSet($attributeSetId, $attributeId, $attributeGroupId = NULL)
    {
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        if(is_null($attributeGroupId)) {
            $attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_product', $attributeSetId);
        }

        $installer->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
    }
    
    /**
     * Create new product attribute.
     *
     * Alternative to above, needs testing
     *
     * $options = array (
            'value' => array (
                'option_one' 	=> array('SomeValue')
        ));

        $attributeData = array(
            'group'                         => 'General',
            'label'                         => $attributeName,
            'type'                          => 'varchar',
            'input'                         => 'select',
            'global'                        => 1,
            'visible'                       => 1,
            'required'                      => 1,
            'user_defined'                  => 1,
            'searchable'                    => 0,
            'configurable'                  => 1,
            'filterable'                    => 0,
            'comparable'                    => 0,
            'apply_to'                      => 'configurable',
            'option'                        => $options,
            'visible_on_front'              => true,
            'visible_in_advanced_search' 	=> true,
            'visible_in_quick_search'       => true,
        );
     *
     * @param string $attributeName
     * @param array $attributeData
     * @param string|int $store
     * @return int
     */
    protected function _createAttribute($attributeName, $attributeData, $storeId = null)
    {
        //$entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $attributeData = array_merge(array(
            'group'                         => 'General',
            //'label'                         => 'New Attribute',
            'type'                          => 'varchar',
            'input'                         => 'select',
            'global'                        => 1,
            'visible'                       => 1,
            'required'                      => 1,
            'user_defined'                  => 1,
            'searchable'                    => 0,
            'configurable'                  => 1,
            'filterable'                    => 0,
            'comparable'                    => 0,
            'apply_to'                      => 'configurable,simple',
            //'option'                        => $options,
            'visible_on_front'              => true,
            'visible_in_advanced_search' 	=> true,
            'visible_in_quick_search'       => true,
        ), $attributeData);

        // create product attribute
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        $installer->addAttribute('catalog_product', $attributeName, $attributeData);

        $storeId = $this->_getStoreId($storeId);
        $attribute = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->getResource()
            ->getAttribute($attributeName);

        return $attribute->getId();
    }
    
    /**
     * We want to create a new attrite set for the product,
     * and also a new attribute to add to it.
     *
     * @throws  Exception
     * @return  array       array('attributeSetId' => x, 'attributeId' => y)
     */
    protected function _createAttributeAndSet($name, $label)
    {
        $attributeData = array(
            'group'  => NULL, // If this is set it will add to ALL sets!
            'label'  => $label,
            //'option' => $options,
        );
        
        $attributeId = $this->_createAttribute($name, $attributeData);
        
        if ( ! $attributeId) { // exists already
            Mage::throwException('Error creating attribute!');
        }
        
        $this->_addAttributeToSet($attributeSetId, $attributeId);
    }
    
    /**
     * Retrives store id from store code, if no store id specified,
     * it use seted session or admin store
     *
     * @param string|int $store
     * @return int
     */
    protected function _getStoreId($store = null)
    {
        try {
            $storeId = Mage::app()->getStore($store)->getId();
        }
        catch (Mage_Core_Model_Store_Exception $e) {
            throw new Exception('store_not_exists:' . $store);
        }

        return $storeId;
    }
}