<?php

class ID_Dynamicmeta_Model_Observer
{
    /**
     * Update meta keywords when product is saved
     * @pram Varien_Event_Observer $observer
     */
    public function updateMeta(Varien_Event_Observer $observer)
    {
        /*
        // Get product
        $product = $observer->getEvent()->getProduct();

        // If there are changes in the product
        if( $product->getVisibility() == 4 ) {

            // Create array for values
            $changes = array(
                $product->getSku(),
                $product->getName(),
                $product->getAttributeText('product_category'),
                $product->getAttributeText('manufacturer'),
                $product->getAttributeText('gender'),
            );

            // Get product Categories
            $categories = array_unique( $this->getCategories($product) );

            // Update product
            $product->setMetaKeyword( implode(',', array_unique(array_merge($changes,$categories))) );

        }

        return $this;
        */
    }

    public function createUrl(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if( $product->getVisibility() == 4 ) {
            // Create the url
            $tmp_url = $product->getAttributeText('manufacturer') . '-' . $product->getName() . '-' . $product->getSku();
            $url = Mage::getModel('catalog/product_url')->formatUrlKey($tmp_url);

            //Mage::log($url);
            // Set the url
            $product->setUrlKey($url);

            // Save the product
            //$product->save();
        }
    }

    private function getCategories($oProduct) {
        $aIds = $oProduct->getCategoryIds();
        $aCategories = array();

        foreach($aIds as $iCategory){
            $oCategory = Mage::getModel('catalog/category')->load($iCategory);
            $catPath = explode('/', $oCategory->getPath());
            foreach($catPath as $cpath){
                $pCategory = Mage::getModel('catalog/category')->load($cpath);

                $excluded = array(
                    'Root Catalog',
                    'Εταιρείες',
                    'Δραστηριότητες',
                    'Fifth Element',
                    'Default Category',
                );

                if( !in_array($pCategory->getName(), $excluded) && $pCategory->getName() != '' ) {
                    $aCategories[] = $pCategory->getName();
                }
            }
        }

        return $aCategories;
    }

    public function addActions($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if(get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction' && $block->getRequest()->getControllerName() == 'catalog_product')
        {
            if( $this->_isAllowedAction('meta_create') ) {
                $block->addItem('createmeta', array(
                    'label' => 'Create Meta Keywords',
                    'url' => Mage::app()->getStore()->getUrl('*/dynamicmeta/createmeta'),
                ));

                $block->addItem('createurl', array(
                    'label' => 'Create New URL key',
                    'url' => Mage::app()->getStore()->getUrl('*/dynamicmeta/createurl'),
                ));

                $block->addItem('insertmeta', array(
                    'label' => 'Insert Meta Keywords',
                    'url' => Mage::app()->getStore()->getUrl('*/dynamicmeta/createmeta'),
                    'additional' => array(
                        'keywords' => array(
                            'name' => 'keywords',
                            'type' => 'text',
                            'class' => 'required-entry',
                            'label' => 'Keywords'
                        )
                    ),
                ));
            }
        }

      return $this;
    }

    public function updateTitle($observer)
    {
        $head = $observer->getLayout()->getBlock('head');
        if(Mage::registry('current_product')) {
            $title = Mage::registry('current_product')->getAttributeText('manufacturer') . ' ' . Mage::registry('current_product')->getName() . ' - ' . Mage::registry('current_product')->getSku() . ' | ' . Mage::app()->getStore()->getFrontendName();
        }

        if(!empty($title)) {
            $head->setTitle($title);
        }

        return $this;
    }

    protected function _isAllowedAction($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            'catalog/products/' . $action
        );
    }

}