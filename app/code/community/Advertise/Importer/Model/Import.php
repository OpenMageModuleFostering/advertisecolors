<?php
/**
 * Import.php
 * 
 * Import model, for storing intermediate products while they queue for import.
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Import extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init("importer/import");
    }
    
    /**
     * Get the stored attributes
     * 
     * @return  array
     */
    public function getAttributes()
    {
        return unserialize($this->getData('attributes'));
    }
    
    /**
     * Get the data field, currently storing linked products etc
     * 
     * @return  array
     */
    public function getProductData()
    {
        return unserialize($this->getData('data'));
    }
    
    /**
     * Get the number of atts 
     * 
     * @return int
     */
    public function getAttributeCount()
    {
        return count($this->getAttributes());
    }
}
	 