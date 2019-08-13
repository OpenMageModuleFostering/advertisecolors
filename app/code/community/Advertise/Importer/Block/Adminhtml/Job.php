<?php  
/**
 * Job.php
 * 
 * @package Advertise_Importer
 */
class Advertise_Importer_Block_Adminhtml_Job extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {        
        $this->_controller = 'adminhtml_job';
        $this->_blockGroup = 'importer';
        
        $this->_headerText = Mage::helper('importer')->__('Job Queue');
        $this->_addButtonLabel = Mage::helper('importer')->__('Add Item');
        
        parent::__construct();
    }
}