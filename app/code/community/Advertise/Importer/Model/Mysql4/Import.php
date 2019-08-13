<?php
/**
 * Import.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Mysql4_Import extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("importer/import", "id");
    }
}
