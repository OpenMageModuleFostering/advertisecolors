<?php
set_time_limit(0);
ini_set('memory_limit', '512M');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
/**
 * Scheduler.php
 * 
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Scheduler extends Varien_Object
{    
    /**
     * To be run on a schedule
     * 
     * Import Xml to the database temp table.
     */
    public static function importXml()
    {        
        $importer = Mage::getModel('importer/importer');
        $importer->importXml();
    }
    
    /**
     * Import products from the temportary import table to 
     * Magento.
     */
    public static function importProducts()
    {
        $importer = Mage::getModel('importer/importer');
        $importer->importProducts();
    }
}