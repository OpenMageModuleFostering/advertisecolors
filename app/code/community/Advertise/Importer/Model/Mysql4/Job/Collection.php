<?php
/**
 * Collection.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Mysql4_Job_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Construct
     */
    public function _construct()
    {
        $this->_init("importer/job");
    }
}