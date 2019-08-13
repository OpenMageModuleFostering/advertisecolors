<?php
/**
 * Grid.php
 *
 * @package Advertise_Importer
 */
class Advertise_Importer_Block_Adminhtml_Importer_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('importerGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare Collection
     *
     * @return
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('importer/import')->getCollection();
        $this->setCollection($collection);
        
        return parent::_prepareCollection();
    }

    /**
     * Prepare Grid Columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
          'header'    => Mage::helper('importer')->__('ID'),
          'align'     =>'right',
          'width'     => '100px',
          'index'     => 'id',
        ));
        
        $this->addColumn('product_id', array(
          'header'    => Mage::helper('importer')->__('Product ID'),
          'align'     =>'right',
          'width'     => '100px',
          'index'     => 'product_id',
        ));

        $this->addColumn('job', array(
          'header'    => Mage::helper('importer')->__('Job'),
          'align'     => 'left',
          'index'     => 'job',
          'width'     => '200px',
        ));
        
        $this->addColumn('job_date', array(
          'header'    => Mage::helper('importer')->__('Job Date'),
          'align'     => 'left',
          'index'     => 'job_date',
          'width'     => '200px',
        ));
        
        $this->addColumn('created_time', array(
            'header'    => Mage::helper('importer')->__('Created'),
            'type'      => 'timestamp',
            //'align'     => 'center',
            'index'     => 'created_time',
            //'gmtoffset' => true
        ));
//
//        $this->addColumn('product', array(
//          'header'    => Mage::helper('importer')->__('Product'),
//          'align'     =>'left',
//          'width'     => '200px',
//          'index'     => 'product',
//        ));
//


//
//
//        $this->addColumn('status', array(
//            'header'    => Mage::helper('importer')->__('Status'),
//            'align'     => 'left',
//            'width'     => '80px',
//            'index'     => 'status',
//            'type'      => 'options',
//            'options'   => array(
//                1 => 'Enabled',
//                2 => 'Disabled',
//            ),
//        ));
//
        
//
//        $this->addColumn('action',
//            array(
//                'header'    =>  Mage::helper('importer')->__('Action'),
//                'width'     => '100',
//                'type'      => 'action',
//                'getter'    => 'getId',
//                'actions'   => array(
//                    array(
//                        'caption'   => Mage::helper('importer')->__('Edit'),
//                        'url'       => array('base'=> '*/*/edit'),
//                        'field'     => 'id'
//                    )
//                ),
//                'filter'    => false,
//                'sortable'  => false,
//                'index'     => 'stores',
//                'is_system' => true,
//        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare Mass Action
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('importer');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('importer')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('importer')->__('Are you sure?')
        ));
        
        return $this;
    }

    /**
     * Get row url - None editable
     */
    public function getRowUrl($row)
    {
        return '';
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}