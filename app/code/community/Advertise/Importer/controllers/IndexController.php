<?php
/**
 * IndexController.php
 *
 * Frontend Import Controller
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Default Action
     */
    public function IndexAction() 
    {
        $this->loadLayout();   
        $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link"  => Mage::getBaseUrl()
        ));

        $breadcrumbs->addCrumb("titlename", array(
            "label" => $this->__("Titlename"),
            "title" => $this->__("Titlename")
        ));

        $this->renderLayout();
    }
    
    /**
     * List Import table
     */
    public function listAction()
    {
        $import = Mage::getModel('importer/import');
        
        foreach($import->getCollection() as $item) {
            echo $item->getId() . "<br />";
        }
    }
    
    /**
     * List Import table
     */
    public function listJobsAction()
    {
        $import = Mage::getModel('importer/job');
        
        foreach($import->getCollection() as $item) {
            echo $item->getId() . "<br />";
        }
    }
    
    /**
     * Testing Product import..
     */
    public function importProductsAction()
    {
        echo "Importing Products...<br />";
        $importer = Mage::getModel('importer/importer');
        $importer->importProducts();
    }
    
    /**
     * Testing XML import
     */
    public function importXmlAction()
    {        
        echo "Importing Feed...<br />";
        $importer = Mage::getModel('importer/importer');
        $importer->importXml();
    }
    
    public function testAction()
    {        
        //$test = new Mage_Catalog_Model_Product_Attribute_Source_Inputtype;
        //var_dump($test->toOptionArray());
    }
}