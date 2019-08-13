<?php
/**
 * Update to version 2.0.1
 *
 * @package Advertise_Importer
 */

    $updater = $this;

    $updater->startSetup();

    $updater->addAttribute('catalog_product', 'advertise_colors', array(
        'group'             => 'General',
        'label'             => 'Adverti.se Color',
        'type'              => 'varchar',
        'input'             => 'select',
        'global'            => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'apply_to'          => 'simple,configurable',
        'visible'           => true,
        'visible_on_front'  => true,
        'required'          => false,
        'user_defined'      => true,
        'searchable'        => true,
        'visible_in_advanced_search'  => true,
        'filterable'        => false,
        'comparable'        => false,
        'used_in_product_listing' => true,
        'option' => array (
                           'value' => array(
                                            'option01' => array('Black', 'Black'),
                                            'option02' => array('Gray_Dark', 'Dark Gray'),
                                            'option03' => array('Gray', 'Gray'),
                                            'option04' => array('Gray_Light', 'Light Gray'),
                                            'option05' => array('White', 'White'),
                                            'option06' => array('Cream', 'Cream'),
                                            'option07' => array('Yellow_Light', 'Light Yellow'),
                                            'option08' => array('Yellow', 'Yellow'),
                                            'option09' => array('Yellow_Dark', 'Dark Yellow'),
                                            'option10' => array('Orange', 'Orange'),
                                            'option11' => array('Orange_Dark', 'Dark Orange'),
                                            'option12' => array('Red_Bright', 'Bright Red'),
                                            'option13' => array('Red', 'Red'),
                                            'option14' => array('Red_Dark', 'Dark Red'),
                                            'option15' => array('Brown', 'Brown'),
                                            'option16' => array('Brown_Light', 'Light Brown'),
                                            'option17' => array('Beige', 'Beige'),
                                            'option18' => array('Pink_Light', 'Light Pink'),
                                            'option19' => array('Pink', 'Pink'),
                                            'option20' => array('Pink_Dark', 'Dark Pink'),
                                            'option21' => array('Pink_Bright', 'Bright Pink'),
                                            'option22' => array('Purple_Dark', 'Dark Purple'),
                                            'option23' => array('Purple', 'Purple'),
                                            'option24' => array('Purple_Light', 'Light Purple'),
                                            'option25' => array('Blue_Light', 'Light Blue'),
                                            'option26' => array('Blue', 'Blue'),
                                            'option27' => array('Blue_Dark', 'Dark Blue'),
                                            'option28' => array('Teal', 'Teal'),
                                            'option29' => array('Turquoise', 'Turquoise'),
                                            'option30' => array('Green_Light', 'Light Green'),
                                            'option31' => array('Green', 'Green'),
                                            'option32' => array('Green_Dark', 'Dark Green'),
                                            'option33' => array('Olive', 'Olive'),                                     
                                           )
                        ),            
    ));
    
    // Set sort order for colors using a direct SQL queries... there must be a better way but damned if I can find it!
    // Use gaps of 2 in the ordering to allow possible addition of other colors in future.
    try {
        $resource = Mage::getSingleton('core/resource');
        $writeConnection = $resource->getConnection('core_write');
        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT attribute_id FROM " . $resource->getTableName('eav_attribute') . " WHERE attribute_code = 'advertise_colors'";
        $results = $readConnection->fetchAll($query);
        $attId = $results[0]['attribute_id'];
        $allColorsInOrder = "Black,Gray_Dark,Gray,Gray_Light,White,Cream,Yellow_Light,Yellow,Yellow_Dark,Orange,Orange_Dark,Red_Bright,Red,Red_Dark,Brown,Brown_Light,Beige,Pink_Light,Pink,Pink_Dark,Pink_Bright,Purple_Dark,Purple,Purple_Light,Blue_Light,Blue,Blue_Dark,Teal,Turquoise,Green_Light,Green,Green_Dark,Olive";
        $allColorsArray = explode(',',$allColorsInOrder);
        $nextPosition = 2;
        foreach ($allColorsArray as $nextColor) {
            $query = "SELECT DISTINCT option_id FROM " . $resource->getTableName('eav_attribute_option_value') .
                    " WHERE option_id IN (SELECT option_id FROM " . $resource->getTableName('eav_attribute_option') .
                    " WHERE attribute_id = " . $attId . ") AND value = '" . $nextColor . "'";
            $results = $readConnection->fetchAll($query);
            foreach($results as $nextVal) {
                $query = "UPDATE " . $resource->getTableName('eav_attribute_option') . " SET sort_order = " . $nextPosition . " WHERE option_id = " . $nextVal['option_id'];
                $writeConnection->query($query);
            }
            $nextPosition += 2;
        }
    } catch (Exception $e) {
        Mage::log("Could not set sort_order for advertise_colors because: " . $e->getMessage());
        Mage :: logException($e);
    }
   
    $updater->endSetup();
?>