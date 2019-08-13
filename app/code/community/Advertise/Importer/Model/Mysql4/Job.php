<?php
/**
 * Job.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Mysql4_Job extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("importer/job", "id");
    }
    
    /**
     * Check if a jobid exists.
     * 
     * @param   string
     * @return  int|FALSE
     */
    public function getIdByJobId($id)
    {
        return $this->_getReadAdapter()->fetchOne(
             'SELECT id from '.$this->getMainTable().' where jobid=?',
             $id
         );
    }
}