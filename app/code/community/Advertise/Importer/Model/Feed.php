<?php
/**
 * Feed.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Feed extends Mage_AdminNotification_Model_Feed
{
    /**
     * Get the url for the feed.
     */
    public function getFeedUrl() 
    {
        $url = Mage::getStoreConfigFlag(self::XML_USE_HTTPS_PATH) ? 'https://' : 'http://';
        
        $url .= 'www.......';
    }
 
    /**
     * Observe
     */
    public function observe() 
    {
        $model = Mage::getModel('importer/feed');
        $model->checkUpdate();
    }
    
    /**
     * Generate the feed.
     */
    public function createXmlFeed()
    {
        
    }
}