<?php
/**
 * Importermanager.php
 * 
 * Backend block
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Block_Adminhtml_Importermanager extends Mage_Adminhtml_Block_Template
{
    /**
     * Get the number of items in the queue
     * 
     * @return int
     */
    protected function _getQueueCount()
    {
        $import = Mage::getModel('importer/import');
        return $import->getCollection()->count();
    }
    
    /**
     * Get the form action for feed importing submit
     * 
     * @return  string
     */ 
    protected function _getFeedFromAction()
    {
        //$this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id')));
        return $this->getUrl('*/*/importXml');
    }


    const ADVERTISE_EMAIL = 'advertise_settings/settings/settings_email';

    public function getAdvertiseEmail() {
        return Mage::getStoreConfig(self::ADVERTISE_EMAIL);
    }

    /**
     * Get the URL for the Advrtise iframe;
     * include email and url so user can be id'd at i.adverti.se
     * @return  string
     */
    protected function _getAdsIframeUrl() {
        if ($this->getBaseUrl() == '127.0.0.1/magento/') {
            $url = 'http://advertise.local/frameads?email=';
            //Mage::log('Locally testing feeds.');
        } else {
            $url = 'http://i.adverti.se/frameads?email=';
        }
        $url = $url .
               $this->getAdvertiseEmail() .
               '&amp;site=' . $this->getBaseUrl();

        return $url;
    }

    /**
     * Get the base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return str_replace('http://', '', Mage::getStoreConfig('web/unsecure/base_url'));
    }
    
    /**
     * Get the local URL for the Adverti.se logo
     * NOTE: Now served remotely from Adverti.se CDN so no longer used
     * To use again need to add logo file to: skin/frontend/default/default/images/adverti.se/advertise-logo.png
     * @return  string
     */
    protected function _getLogoUrl() {
        $url = $this->getUrl('', array('_direct' => 'frontend/default/default/images/adverti.se/advertise-logo.png', '_type' => 'skin')) ;
        return $url;
    }

    /**
     * Get the form action for product importing submit
     * 
     * @return  string
     */ 
    protected function _getImportFromAction()
    {
        return $this->getUrl('*/*/importProducts');
    }

    protected function _getJobQueueURL()
    {
        return $this->getUrl('*/adminhtml_job');
    }

    protected function _getProductImportQueueURL()
    {
        return $this->getUrl('*/adminhtml_queue');
    }
}
