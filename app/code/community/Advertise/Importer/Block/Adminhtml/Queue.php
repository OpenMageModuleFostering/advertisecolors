<?php  
/**
 * Queue.php
 * 
 * @package Advertise_Importer
 */
class Advertise_Importer_Block_Adminhtml_Queue extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {        
        $this->_controller = 'adminhtml_importer';
        $this->_blockGroup = 'importer';
        
        $this->_headerText = Mage::helper('importer')->__('Product Import Queue');
        $this->_addButtonLabel = Mage::helper('importer')->__('Add Item');
        
        parent::__construct();
    }
}