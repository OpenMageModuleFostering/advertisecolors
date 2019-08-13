<?php
/**
 * Config.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Model_Config extends Varien_Object
{
    /**
     * Config keys
     */
    const ENABLED  =         'importer/importer_group/enabled';
    const DOWNLOAD_URL  =    'importer/importer_group/download_url';
    const ADVERTISE_EMAIL =  'advertise_settings/settings/settings_email';

    /**
     * Is the module enabled?
     * 
     * @return  bool
     */
    public function getIsEnabled()
    {
        return (bool) Mage::getStoreConfig(self::ENABLED);
    }
    
    /**
     * Get the advertise Email settings.
     * 
     * @return  string
     */
    public function getAdvertiseEmail()
    {
        return Mage::getStoreConfig(self::ADVERTISE_EMAIL);
    }
    
    /**
     * Get the feed download url
     * 
     * @return  string
     */
    public function getAdvertiseFeed()
    {
        $restore = Mage::getSingleton('core/app') -> getRequest() -> getParam('restore', null);
        $url = 'http://i.adverti.se/feeds/magento/email:' .
                $this->getAdvertiseEmail() .
                '/site:' . $this->getBaseUrl() . 'restore:' . $restore; // No slash before restore as BaseUrl has a trailing slash already
        Mage::log("Importing feed from URL: ".$url);
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
     * Get our file download url
     * 
     * Get from the advertise module if available with fallback to our own settings.
     * Leaving this in because during dev we don't have the other mod installed.
     * 
     * @return string 
     */
    public function getDownloadUrl()
    {
        // THIS IS FOR LOCAL TESTING ONLY
        //if (true) {
        //    return "http://advertise.local/feeds/magento/email:new-magento@adverti.se/site:magento.adverti.se/restore:true";
        //}

        // FOR PRODUCTION
        $advertiseEmail = $this->getAdvertiseEmail();
        if( ! empty($advertiseEmail)) {
            return $this->getAdvertiseFeed();
        }
        
        return Mage::getStoreConfig(self::DOWNLOAD_URL);
    }
}