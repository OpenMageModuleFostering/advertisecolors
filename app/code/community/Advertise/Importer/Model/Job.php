<?php
/**
 * Job.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Job extends Mage_Core_Model_Abstract
{
    /**
     * Construct
     */
    protected function _construct()
    {
        $this->_init("importer/job");
    }
    
    /**
     * Check if a jobid exists.
     * 
     * @param   string
     * @return  int|FALSE
     */
    public function getIdByJobId($id)
    {
        return $this->getResource()->getIdByJobId($id);
    }
}