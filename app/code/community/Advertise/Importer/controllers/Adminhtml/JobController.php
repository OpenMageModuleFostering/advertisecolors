<?php
/**
 * JobController.php
 * 
 * @package Advertise_Importer
 */
class Advertise_Importer_Adminhtml_JobController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Job Queue"));
	   $this->renderLayout();
    }
    
    /**
     * Initialise
     *
     * @return Internetware_Connect_Adminhtml_ConnectController
     */
	protected function _initAction()
    {
		$this->loadLayout()
			->_setActiveMenu('importer/job')
			->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Importer'), 
                    Mage::helper('adminhtml')->__('Importer')
            );
		
		return $this;
	}   

    /**
     * Delete Action
     */
	public function deleteAction()
    {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('importer/job');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			}
            catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
    
    /**
     * Mass Deletion
     */
    public function massDeleteAction()
    {
        $connectIds = $this->getRequest()->getParam('job');
        if(!is_array($connectIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        }
        else {
            try {
                foreach ($connectIds as $connectId) {
                    $connect = Mage::getModel('importer/job')->load($connectId);
                    $connect->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($connectIds)
                    )
                );
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
}