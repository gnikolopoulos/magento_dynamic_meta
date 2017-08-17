<?php

class ID_Dynamicmeta_Model_Categories
{

	public function toOptionArray()
	{
		$categoriesArray = $this->getCategoriesDropdown();
		foreach($categoriesArray as $value){
			foreach($value as $key => $val) {
	            if($key=='label') {
	                $catNameIs = $val;
	            }
	            if($key=='value') {
	                $catIdIs = $val;
	            }
	            if($key=='level') {
	                $catLevelIs = $val;
	                $b ='';
	                for($i=1;$i<$catLevelIs;$i++){
	                    $b = $b."-";
	                }
	            }
	        }
	        
	        $optionsArray[] = array(
	        	'label' => $b.$catNameIs,
	        	'level' => $catLevelIs,
	        	'value' => $catIdIs,
	        );
	    }

	    return $optionsArray;
	}

	private function getCategoriesDropdown() {

	    $categoriesArray = Mage::getModel('catalog/category')
	        ->getCollection()
	        ->addAttributeToSelect('name')
	        ->addAttributeToSort('path', 'asc')
	        ->addFieldToFilter('is_active', array('eq'=>'1'))
	        ->load()
	        ->toArray();

	    foreach ($categoriesArray as $categoryId => $category) {
	        if (isset($category['name'])) {
	            $categories[] = array(
	                'label' => $category['name'],
	                'level' => $category['level'],
	                'value' => $categoryId
	            );
	        }
	    }
	    return $categories;
	}

}