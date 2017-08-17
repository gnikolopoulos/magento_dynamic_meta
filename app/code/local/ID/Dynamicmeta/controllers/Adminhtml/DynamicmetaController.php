<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class ID_Dynamicmeta_Adminhtml_DynamicmetaController extends Mage_Adminhtml_Controller_Action
{

  private $product_ids;

  /*
    Mage::getSingleton('core/session')->addSuccess('Success message');
    Mage::getSingleton('core/session')->addNotice('Notice message');
    Mage::getSingleton('core/session')->addError('Error message');
    // Admin only
    Mage::getSingleton('adminhtml/session')->addWarning('Warning message');

    try{
      /// ...
    } catch (Exception $e) {
      Mage::getSingleton('core/session')->addError('Error ' . $e->getMessage());
    }
  */

  public function indexAction()
  {
    $this->_redirectReferer();
    Mage::getSingleton('core/session')->addNotice('You cannot access this area directly');
    return $this;
  }

  public function createmetaAction()
  {
    if( $this->getRequest()->getParam('product') ) {
      $this->product_ids = $this->getRequest()->getParam('product');

      if( !$this->getRequest()->getParam('keywords') ) {
        $this->updateMeta();
      } else {
        $this->insertMeta( explode(',', $this->getRequest()->getParam('keywords')) );
      }

      $this->_redirectReferer();
      Mage::getSingleton('core/session')->addSuccess('Meta keywords created');
    } else {
      $this->_redirectReferer();
      Mage::getSingleton('core/session')->addError('No Products Selected');
      return false;
    }
  }

  public function updateMeta()
  {
    foreach( $this->product_ids as $pid ) {
      $changes = array();
      // Get product
      $product = Mage::getModel('catalog/product')->load($pid);

      // If there are changes in the product
      if( $product->getVisibility() == 4 ) {
        $included_attributes = explode(',', Mage::getStoreConfig('dynamicmeta/attribute_collection/attributes') );

        $changes[] = $product->getSku();
        $changes[] = $product->getName();

        foreach( $included_attributes as $attribute ) {
          $changes[] = $product->getAttributeText( $attribute );
        }

        // Get product Categories
        $categories = array_unique( $this->getCategories($product) );

        // Update product
        /*
        Mage::getSingleton('catalog/product_action')->updateAttributes(
          array($pid), //products to update
          array('meta_keyword' => implode(',', array_unique(array_merge($changes,$categories))) ), //attributes to update
          0 //store to update. 0 means global values
        );
        */
        $product->setData('meta_keyword', implode(',', array_unique(array_merge($changes,$categories))));
        $product->save();
      }

    }
    return $this;
  }

  public function insertMeta($keywords)
  {
    foreach( $this->product_ids as $pid ) {
      // Get product
      $product = Mage::getModel('catalog/product')->load($pid);

      // If there are changes in the product
      if( $product->getVisibility() == 4 ) {
        // Create array for values
        $existing = explode(',', $product->getMetaKeyword());
        if( count($existing) > 0 ) {
          $product->setData('meta_keyword', implode(',', array_unique(array_merge($existing,$keywords))));
        } else {
          $product->setData('meta_keyword', implode(',', array_unique($keywords)));
        }
        $product->save();
      }

    }
    return $this;
  }

  private function getCategories($oProduct) {
    $aIds = $oProduct->getCategoryIds();
    $aCategories = array();

    foreach($aIds as $iCategory){
      $oCategory = Mage::getModel('catalog/category')->load($iCategory);
      $catPath = explode('/', $oCategory->getPath());
      foreach($catPath as $cpath){
        $pCategory = Mage::getModel('catalog/category')->load($cpath);
        $excluded = explode(',', Mage::getStoreConfig('dynamicmeta/excluded/categories') );

        if( !in_array($pCategory->getId(), $excluded) ) {
          $aCategories[] = $pCategory->getName();
        }
      }
    }

    return $aCategories;
  }

  public function createUrlAction()
  {
    if( $this->getRequest()->getParam('product') ) {
      $this->product_ids = $this->getRequest()->getParam('product');
      foreach( $this->product_ids as $pid ) {
        // Get product
        $product = Mage::getModel('catalog/product')->load($pid);

        // If product is visible
        if( $product->getVisibility() == 4 ) {
          $tmp_url = $product->getAttributeText('manufacturer') . '-' . $product->getName() . '-' . $product->getSku();
          $url = Mage::getModel('catalog/product_url')->formatUrlKey($tmp_url);

          //Mage::log($url);
          // Set the url
          $product->setUrlKey($url);

          // Save the product
          $product->save();
        }
      }

      $this->_redirectReferer();
      Mage::getSingleton('core/session')->addSuccess('New URL key created');
    } else {
      $this->_redirectReferer();
      Mage::getSingleton('core/session')->addError('No Products Selected');
      return false;
    }
  }

}