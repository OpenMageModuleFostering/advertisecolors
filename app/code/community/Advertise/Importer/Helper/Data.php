<?php
/**
 * Data.php
 * 
 * Product Import Helper
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * From product helper
     * @var string
     */
    protected $_storeIdSessionField = 'product_store_id';

    /**
     * Get a product module, by Id
     *
     * @todo modify so we can get from Sku aswell
     * @param   mixed
     * @return  Mage_Catalog_Model_Product
     */
    public function getProduct($product)
    {
        if( ! $product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
        }

        return $product;
    }

    /**
     * Load a product model by it's SKU
     *
     * @param   string
     * @return  Mage_Catalog_Model_Product|false
     */
    public function getProductFromSku($sku)
    {
        $product = Mage::getModel('catalog/product');
        $id = $product->getIdBySku($sku);
        if($id) {
            $product->load($id);
            return $product;
        }
        return FALSE;
    }

    /**
     * Does the product already exist in Magento?
     *
     * @param string $sku
     * @return bool
     */
    public function productExists($sku)
    {
        $product = Mage::getModel('catalog/product');
        $productId = $product->getIdBySku($sku);
        unset($product);
        if($productId) {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Get the Stock item for a given product
     *
     * @param   int     Product id
     * @return
     */
    public function getProductStockItem($productId)
    {
        $stockItem = Mage::getModel('cataloginventory/stock_item');
        $stockItem->loadByProduct($productId);

        return $stockItem;
    }

    public function getProductQty($productId)
    {
        $stock = $this->getProductStockItem($productId);
        return $stock->getQty();
    }

    /**
     * Is the product in stock?
     * 
     * @param   int
     * @return  bool
     */
    public function productisInStock($productId)
    {
        $qty = Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($productId)
            ->getQty();

        return $qty > 0;
    }

    /**
     * Set the stock level for a product
     *
     * @param   int
     * @param   int
     */
    public function setProductStock($productId, $qty = 0)
    {
        Mage::getModel('cataloginventory/stock_item')
            ->loadByProduct($productId)
            ->setQty($qty)
            ->setIsInStock((bool)$qty > 0)
            ->save();
    }

    /**
     * Get a product collection
     *
     * @param   string                      simple|configurable
     * @param   array                       Attribute array
     * @return  Varien_Data_Collection
     */
    public function getProductCollection($type = NULL, $attributes = NULL)
    {
        $collection = Mage::getResourceModel('catalog/product_collection');
        //->addCategoryFilter($_category); // we could add this too!
        //->addAttributeToSort('last_name', 'ASC') sorting too

        /**
         * Select Attributes
         */
        if($attributes === NULL) {
            $collection->addAttributeToSelect('*');
        }
        elseif(is_array($attributes) && ! empty($attributes)) {
            $collection->addAttributeToSelect($attributes);
        }
        /**
         * Select Type, configurable or simple..
         */
        if($type !== NULL) {
            $collection->addAttributeToFilter('type_id', array('eq' => $type));
        }

        return $collection;
    }
    
    /**
     * Get all PRODUCT tax options
     * 
     * @return  array
     */
    public function getProductTaxClassOptions()
    {
        $options = Mage::getResourceModel('tax/class_collection')
            ->addFieldToFilter('class_type', 'PRODUCT')
            ->load()
            ->toOptionArray();

        array_unshift($options, array('value'=>'0', 'label' => Mage::helper('tax')->__('None')));

        return $options;
    }

    /**
     * Add a new option for an attribute
     *
     * Working in 1.4.2
     *
     * @param   string          $arg_attribute
     * @param   mixed           $arg_value
     * @return  bool
     */
    public function addAttributeOption($arg_attribute, $arg_value)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_options_model = Mage::getModel('eav/entity_attribute_source_table') ;

        $attribute_code = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
        $attribute = $attribute_model->load($attribute_code);

        $attribute_table = $attribute_options_model->setAttribute($attribute);
        $options = $attribute_options_model->getAllOptions(false);

        if( ! $this->attributeOptionExists($arg_attribute, $arg_value)) {
            $value['option'] = array($arg_value, $arg_value);
            $result = array('value' => $value);
            $attribute->setData('option',$result);
            $attribute->save();
        }

        foreach($options as $option) {
            if ($option['label'] == $arg_value) {
                return $option['value'];
            }
        }
        return true;
    }

    /**
     * Alternative to above, still needs testing
     *
     * @param   int
     * @param   array
     * @return  bool
     */
    public function addAttributeOptionAlternative($attributeId, $attributeOptions)
    {
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        for($i = 0; $i < sizeof($attributeOptions); $i++) {
            $option = array();
            $option['attribute_id'] = $attributeId;
            /** @todo can't work as $value isn't set?? **/
            $option['value'][$value][0] = $attributeOptions[$i];

            $setup->addAttributeOption($option);
        }

        return true;
    }

    /**
     * Get a list of Attribute sets
     * 
     * @return  array   array('set_id', 'name')
     */
    public function getAttributeSets()
    {
        $api = new Mage_Catalog_Model_Product_Attribute_Set_Api;
        return $api->items();
    }

    /**
     * This will get all PRODCT Attribute sets as an array
     *
     * @return  array   name => id pairs
     */
    public function getAttributeSetsAlternative()
    {
        $productAttributeSets = array();
        $entityTypeId = Mage::getModel('eav/entity')
            ->setType('catalog_product')
            ->getTypeId();
        $collection = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter($entityTypeId);

        foreach ($collection as $set) {
            $productAttributeSets[$set->getAttributeSetName()] = $set->getId();
        }
        return $productAttributeSets;
    }

    /**
     * Get all attribute set Ids
     *
     * @todo
     * View the setup installer, there's lots of attribute functions
     * for various uses.
     *
     * @param   string      Entity type
     * @return  array       Array of ids
     */
    public function getAllAttributeSetIds($entityType = 'catalog_product')
    {
        $installer = Mage::getModel('eav/entity_setup');
        $attributeSetIds = $installer->getAllAttributeSetIds($installer->getEntityTypeId($entityType));
        /** See below functions for adding groups and attributes to sets! **/
        //$installer->addAttributeGroup( $entityType , $setId, $groupName );
        //$installer->addAttributeToSet( $entityType , $setId, $groupName, $attributeId );

        return $attributeSetIds;
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
    public function createAttribute($attributeName, $attributeData, $storeId = null)
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
     * Create a new attribute set based upon onother set.
     * We can use the default set as a starting point for a new set.
     *
     * @param   string      New attribute set name
     * @param   int         Base set to copy from, 4 is "usually" the default
     * @retrun  int         attribute set id, or false
     */
    public function createAttributeSetFromBase($setName, $baseSetId = NULL)
    {
        if($baseSetId == NULL) {
            $baseSetId = Mage::getModel('catalog/product')->getDefaultAttributeSetId(); // 4
        }

        /** @todo check if it exists already.. **/
        try {
            $entityTypeId = Mage::getModel('catalog/product')
                ->getResource()->getEntityType()->getId();

            $attributeSet = Mage::getModel('eav/entity_attribute_set')
                ->setEntityTypeId($entityTypeId)
                ->setAttributeSetName($setName);

            $attributeSet->validate();
            $attributeSet->save();
            $attributeSet
                ->initFromSkeleton($baseSetId)
                ->save();
        }
        catch(Exception $e) {
            return FALSE;
        }
        return $attributeSet->getId();
    }

    /**
     * Create a new attribute set, note this will be totally empty of attributes
     * 
     * @param   string
     * @param   int
     * @param   int
     */
    public function createAttributeSet($name, $entityTypeId = NULL, $sortOrder = NULL)
    {
        if($entityTypeId == NULL) {
            $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        }
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        $installer->addAttributeSet($entityTypeId, $name, $sortOrder);
    }

    /**
     * Does the product attribute set exist?
     * 
     * @param   string
     * @return  int|FALSE
     */
    public function attributeSetExists($name)
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        return $installer->getAttributeSet($entityTypeId, $name, 'attribute_set_id'); // ID|False
    }

    /**
     * Remove an attribute set
     *
     * @param   mixed       Id or Name
     */
    public function removeAttributeSet($id)
    {
        $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');

        $installer->deleteTableRow(
            'eav/attribute_set',
            is_numeric($id) ? 'attribute_set_id' : 'attribute_set_name',
            $installer->getAttributeSetId($entityTypeId, $id)
        );
    }

    /**
     * Delete an attribute
     *
     * @todo    change so we can have id or attribute code!
     * @param   int
     */
    public function removeAttribute($id, $entityTypeId = NULL)
    {
        if($entityTypeId == NULL) {
            $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        }
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        $installer->removeAttribute($entityTypeId, $id);
    }


    /**
     * Retrieve Attribute Id Data By Id or Code
     *
     * @param   mixed
     * @param   int
     * @return  int
     */
    public function getAttributeId($id, $entityTypeId = NULL)
    {
        if($entityTypeId == NULL) {
            $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        }
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        if (!is_numeric($id)) {
            $id = $installer->getAttribute($entityTypeId, $id, 'attribute_id');
        }
        if (!is_numeric($id)) {
            //throw Mage::exception('Mage_Eav', Mage::helper('eav')->__('Wrong attribute ID.'));
            return FALSE;
        }
        return $id;
    }

    /**
     * Get all Attributes
     */
    public function getAllAttributes($entityTypeId = NULL)
    {
        if($entityTypeId == NULL) {
            $entityTypeId = Mage::getModel('catalog/product')->getResource()->getEntityType()->getId();
        }
        // to follow..
    }
    

    /**
     *
     * @example $this->createAttribute(strtolower("Swatch"), "Swatch", "text", "simple")
     * @see     http://ajzele.net/programatically-create-attribute-in-magento-useful-for-the-on-the-fly-import-system
     * @param <type> $code
     * @param <type> $label
     * @param <type> $attribute_type
     * @param <type> $product_type
     */
    public function createAttributeAlternative($code, $label, $attribute_type, $product_type)
    {
        $_attribute_data = array(
            'attribute_code' => 'old_site_attribute_'.(($product_type) ? $product_type : 'joint').'_'.$code,
            'is_global' => '1',
            'frontend_input' => $attribute_type, //'boolean',
            'default_value_text' => '',
            'default_value_yesno' => '0',
            'default_value_date' => '',
            'default_value_textarea' => '',
            'is_unique' => '0',
            'is_required' => '0',
            'apply_to' => array($product_type), //array('grouped')
            'is_configurable' => '0',
            'is_searchable' => '0',
            'is_visible_in_advanced_search' => '0',
            'is_comparable' => '0',
            'is_used_for_price_rules' => '0',
            'is_wysiwyg_enabled' => '0',
            'is_html_allowed_on_front' => '1',
            'is_visible_on_front' => '0',
            'used_in_product_listing' => '0',
            'used_for_sort_by' => '0',
            'frontend_label' => array('Old Site Attribute '.(($product_type) ? $product_type : 'joint').' '.$label)
        );


        $model = Mage::getModel('catalog/resource_eav_attribute');

        if (!isset($_attribute_data['is_configurable'])) {
            $_attribute_data['is_configurable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable'])) {
            $_attribute_data['is_filterable'] = 0;
        }
        if (!isset($_attribute_data['is_filterable_in_search'])) {
            $_attribute_data['is_filterable_in_search'] = 0;
        }

        if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
            $_attribute_data['backend_type'] = $model->getBackendTypeByInput($_attribute_data['frontend_input']);
        }

        $defaultValueField = $model->getDefaultValueByInput($_attribute_data['frontend_input']);
        if ($defaultValueField) {
            $_attribute_data['default_value'] = $this->getRequest()->getParam($defaultValueField);
        }
        $model->addData($_attribute_data);

        $model->setEntityTypeId(Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId());
        $model->setIsUserDefined(1);

        try {
            $model->save();
        } catch (Exception $e) { echo '<p>Sorry, error occured while trying to save the attribute. Error: '.$e->getMessage().'</p>'; }
    }

    /**
     * Add an attribute to an attribute set
     *
     * @param <type> $attributeSetId
     * @param <type> $attributeGroupId
     * @param <type> $attributeId
     */
    public function addAttributeToSet($attributeSetId, $attributeId, $attributeGroupId = NULL)
    {
        $installer = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('core_setup');
        if(is_null($attributeGroupId)) {
            $attributeGroupId = $installer->getDefaultAttributeGroupId('catalog_product', $attributeSetId);
        }

        $installer->addAttributeToSet('catalog_product', $attributeSetId, $attributeGroupId, $attributeId);
    }

    /**
     * Does an attribute have an option?
     * modified so we can pass the attribute ID, or the Code
     *
     * Working in 1.4.2
     *
     * @param   string|int  Attribute code or id, eg. 'manufacturer' or 99
     * @param   mixed       value
     * @param   bool        If set to TRUE we will use $attributeCode as the ID not code
     * @return  bool
     */
    public function attributeOptionExists($attributeCode, $value, $useId = FALSE)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_options_model = Mage::getModel('eav/entity_attribute_source_table');

        if($useId === FALSE) {
            $attributeCode = $attribute_model->getIdByCode('catalog_product', $attributeCode);
        }
        $attribute = $attribute_model->load($attributeCode);

        $attribute_table = $attribute_options_model->setAttribute($attribute);
        $options = $attribute_options_model->getAllOptions(false);

        foreach($options as $option) {
            if ($option['label'] == $value) {
                return $option['value'];
            }
        }
        
        return false;
    }

    /**
     * Get the value on an attribute
     *
     * @param   string      $attributeCode
     * @param   int         $optionId
     * @return
     */
    public function getAttributeOptionValue($attributeCode, $optionId)
    {
        $attribute_model = Mage::getModel('eav/entity_attribute');
        $attribute_table = Mage::getModel('eav/entity_attribute_source_table');
        $attribute_code = $attribute_model->getIdByCode('catalog_product', $attributeCode);
        $attribute = $attribute_model->load($attribute_code);
        $attribute_table->setAttribute($attribute);                     
        $option = $attribute_table->getOptionText($optionId);
        
        return $option;
    }

    /**
     * Assign product to category
     *
     * @param int $categoryId
     * @param int $productId
     * @param int $position
     * @return boolean
     */
    public function assignProductToCategory($categoryId, $productId, $position = null, $identifierType = null)
    {
        $api = new Mage_Catalog_Model_Category_Api_V2;
        return $api->assignProduct($categoryId, $productId, $position, $identifierType);
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
            throw new Internetware_Connect_Exception('store_not_exists:' . $store);
            //$this->_fault('store_not_exists');
        }

        return $storeId;
    }
}
	 