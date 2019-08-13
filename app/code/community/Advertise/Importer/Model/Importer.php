<?php
/**
 * Importer.php
 *
 * @package Advertise_Importer
 */
set_time_limit(0);
ini_set('memory_limit', '256M');
Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
/**
 * Import helper class.
 */
class Advertise_Importer_Model_Importer extends Varien_Object
{
    /**
     * Delete the XML File after import?
     */
    const DELETE_TEMP_FILE = FALSE;
    /**
     * How many to import in each batch?
     */
    const IMPORT_BATCH_LIMIT = 400;
    
    /**
     * @var Advertise_Importer_Model_Config
     */
    protected $_config;
    
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->_config = Mage::getModel('importer/config');
    }
    
    /**
     * To be run on a schedule
     * Return true if all went OK, false if errors.
     * Import Xml to the database temp table.
     */
    public function importXml()
    {
        $filename = $this->_getConfig()->getDownloadUrl();
        //Mage::log("Filename: " . $filename);
        if(strpos($filename, 'http://') !== FALSE) {
            $tmpFile = BP . DS . "var" . DS . "advertisedata";
            if( ! is_dir($tmpFile)) {
                mkdir($tmpFile);
            }
            $tmpFile .= "/xmldata_" . time() . ".xml";
            file_put_contents($tmpFile, file_get_contents($filename));
            $filename = $tmpFile;
        }
        
        if( ! file_exists($filename)) {
            Mage::log('Cant find xml File: ' . $filename);
            return false;
        }

        $reader = new XMLReader();
        $reader->open($filename);
        $jobData = array(
            'jobid'     => '',
            'id'        => '',
            'job_date'  => '',
            'email'     => '',
        );
        
        while($reader->read()) {
            /** Each Element **/
            if($reader->nodeType == XMLReader::ELEMENT) {
                if($reader->localName == 'advertise') {
                    //$reader->read();
                    $jobData = array(
                        'jobid'     => $reader->getAttribute('jobid'),
                        'id'        => $reader->getAttribute('id'),
                        'job_date'  => $reader->getAttribute('date'),
                        'email'     => $reader->getAttribute('email')
                    );
                    
                    $job = Mage::getModel('importer/job');
                    $id = $job->getIdByJobId($jobData['jobid']);
                    if($id != FALSE) {
                        $reader->next(); // next incase we have multiple jobs per feed!
                        Mage::log("Skipping Job {$jobData['jobid']} Already Imported");
                        continue;
                    }
                    
                    try {
                        $job = Mage::getModel('importer/job');
                        $job->addData(array(
                            'job'      => $jobData['id'],
                            'jobid'    => $jobData['jobid'],
                            'job_date' => $jobData['job_date'],
                        ));
                        $job->save();
                    }
                    catch(Exception $e) {
                        echo $e->getMessage();
                        Mage::log($e->getMessage());
                    }
                    //$test = $reader->value;
                }
                elseif($reader->localName == 'product') {
                    $productArray = $this->_toArray($reader);   
                    try {
                        $import = Mage::getModel('importer/import');
                        $import->addData(array(
                            'job_date'      => $jobData['job_date'],
                            'job'           => $jobData['jobid'],
                            'product_id'    => $productArray['id']['id'],
                            'data'          => serialize($productArray['linkedproducts']),
                            'attributes'    => serialize($productArray['attributes'])
                        ));
                        $import->save();
                    }
                    catch(Exception $e) {
                        echo $e->getMessage();
                        Mage::log($e->getMessage());
                    }
                    
                    unset($simpleXml);
                }
            } // XMLReader::ELEMENT
        }
        
        if(self::DELETE_TEMP_FILE) {
            unlink($filename);
        }
        return true;
    }
    
    /**
     * Convert a node to an array
     * 
     * @param   XMLReader
     * @param   array
     * @return  array
     */
    protected function _toArray($xmlReader, array $array = array())
    {
        $current = $xmlReader->localName;

        while (true) {
            switch ($xmlReader->nodeType) {
                case XMLReader::SIGNIFICANT_WHITESPACE:
                    break;
                case XMLReader::END_ELEMENT:                    
                    if ($xmlReader->localName == $current) {                
                        break 2;
                    }
                    break;
                case XMLReader::ATTRIBUTE;
                    break;
                case XMLReader::COMMENT:
                    break;
                case XMLReader::ELEMENT:
                    // fix, self closing tags only fire ELEMENT, NOT END_ELEMENT
                    if($xmlReader->isEmptyElement) { 
                        break;
                    }
                    $key = $xmlReader->localName;
                    if ($current != $key) {
                        $data = $this->_toArray($xmlReader);
                        if (is_string($data)) {
                            $data = array($key => $data);
                        }
                        if ($xmlReader->hasAttributes) {
                            $data['#attributes'] = array(
                                'scope'     => $xmlReader->getAttribute('scope'),
                                'admintitle'=> $xmlReader->getAttribute('admintitle'),
                                'type'      => $xmlReader->getAttribute('type'),
                            );
                            //$index = 0;
                            //for($index = 0; $index <= $xmlReader->attributeCount; $index++) {
                             //   $xmlReader->moveToAttributeNo($index);
                             //   $data['#attributes'][$xmlReader->name] = $xmlReader->value;
                            //}
                            $xmlReader->moveToElement();
                        }

                        if (true == isset($array[$key])) {
                            if (false === isset($array[$key][0])) {
                                $oldData = $array[ $key ];
                                unset($array[$key]);
                                $array[$key][] = $oldData;
                            } 
                            $array[$key][] = $data;
                        } 
                        else {
                            $array[$key] = $data;
                        }
                    }
                    break;
                case XMLReader::DOC:
                    break;
                case XMLReader::END_ENTITY:
                    break;
                case XMLReader::ENTITY:
                    break;
                case XMLReader::ENTITY_REF:
                    break;
                case XMLReader::LOADDTD:
                    break;
                case XMLReader::NONE:
                    break 2;
                case XMLReader::NOTATION:
                    break;
                case XMLReader::PI:
                    break;
                case XMLReader::TEXT: 
                    $array = $xmlReader->value;
                    break;
                case XMLReader::CDATA:;
                    break;
                default:
                    var_dump($xmlReader->nodeType);
            }
            $xmlReader->read();            
        }

        return $array;
    }

    /**
     * Import products from the temportary import table to 
     * Magento.
     */
    public function importProducts()
    {
        $limit = self::IMPORT_BATCH_LIMIT;
        //echo "Importing Products..<br /><br />";
        $import = Mage::getModel('importer/import');
        $collection = $import->getCollection()
            //->addFieldToFilter('status', array('eq' => '1'))
        ;
        $collection->getSelect()->limit($limit, 0);
        foreach($collection as $item) {
            //echo $item->getSku() . "<br />";            
            $data = $item->toArray(array(
                'vendor',
                'sku',
                'name',
                'short_description',
                'description',
                'primary_category',
                'categories',
                'price',
                'stock',
                'weight',
            ));
            
            /**
             * Save the item
             */
            try {
                $adapter = Mage::getModel('importer/adapter');
                $adapter->save($item);
                $item->delete(); // remove from queue
                
            }
            catch(Exception $e) {
                Mage::Log($e->getMessage());
                echo $e->getMessage(); 
            }
            unset($adapter);
        }
    }
    
    /**
     * Convert a DomNode into an array
     * 
     * @param   DomNode
     * @return  array
     */
    public function domnode_to_array($node) 
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE:
            case XML_TEXT_NODE:
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE:
                for ($i=0, $m=$node->childNodes->length; $i<$m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if(isset($child->tagName)) {
                        $t = $child->tagName;
                        if(!isset($output[$t])) {
                            $output[$t] = array();
                        }
                        $output[$t][] = $v;
                    }
                    elseif($v) {
                        $output = (string) $v;
                    }
                }
                if(is_array($output)) {
                    if($node->hasAttributes()) {
                        $a = array();
                        foreach($node->attributes as $attrName => $attrNode) {
                            $a[$attrName] = (string) $attrNode->value;
                        }
                        $output['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if(is_array($v) && count($v)==1 && $t!='@attributes') {
                            $output[$t] = $v[0];
                        }
                    }
                }
            break;
        }
        
        return $output;
    }
    
    /**
     * Get the config model
     * 
     * @return Advertise_Importer_Model_Config
     */
    protected function _getConfig()
    {
        return $this->_config;
    }
}