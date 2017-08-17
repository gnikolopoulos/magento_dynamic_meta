<?php

class ID_Dynamicmeta_Model_Attributes
{

	public function toOptionArray()
	{
		$attributes_to_array = $this->getAttributesDropdown();

	    return $attributes_to_array;
	}

	private function getAttributesDropdown() {

	    $attributesArray = Mage::getResourceModel('eav/entity_attribute_collection')
			->setEntityTypeFilter(4)
			->addSetInfo()
			->getData(); 

	    foreach ($attributesArray as $attribute) {
	        if (isset($attribute['frontend_label'])) {
	            $attributes[] = array(
	                'label' => $attribute['frontend_label'],
	                'level' => 1,
	                'value' => $attribute['attribute_code']
	            );
	        }
	    }
	    return $attributes;
	}

}