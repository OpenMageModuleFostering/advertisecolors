<?php
/**
 * ImportermanagerController.php
 *
 * Backend Import controller
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Adminhtml_ImportermanagerController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Default Action
     */
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Product Importer"));
	   $this->renderLayout();
       
       /**
        * Possibly do both actions in here? and just echo out the data..?
        */
    }
    
    /**
     * Import the products from the queue
     */
    public function importProductsAction()
    {
        if($this->getRequest()->isPost()) {
            $importer = Mage::getModel('importer/importer');
            $importer->importProducts();
            Mage::getSingleton('adminhtml/session')->addSuccess('Successfully imported Product Queue.');
            $this->_redirect('*/*');
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError('Incorrect usage.');
        }
    }
    
    /**
     * Import the XML feed.
     */
    public function importXmlAction()
    {        
        if($this->getRequest()->isPost()) {
            $importer = Mage::getModel('importer/importer');
            $success = $importer->importXml();
            if ($success == true) {
                Mage::getSingleton('adminhtml/session')->addSuccess('Successfully imported Feed');
            } else {
                Mage::getSingleton('adminhtml/session')->addError('Error importing Feed; check system log for details.');
            }
            $this->_redirect('*/*');
        }
        else {
            Mage::getSingleton('adminhtml/session')->addError('Incorrect usage.');
        }
    }
    
    public function postAction()
    {
//        $post = $this->getRequest()->getPost();
//        try {
//            if (empty($post)) {
//                Mage::throwException($this->__('Invalid form data.'));
//            }
//            
//            $message = $this->__('Your form has been submitted successfully.');
//            Mage::getSingleton('adminhtml/session')->addSuccess($message);
//        } catch (Exception $e) {
//            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//        }
//        $this->_redirect('*/*');
    }
}