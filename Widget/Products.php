<?php

namespace DataCue\MagentoModule\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\Option\ArrayInterface;
 
class Products extends BaseWidget implements BlockInterface, ArrayInterface
{
	protected $_template = "DataCue_MagentoModule::widget/products.phtml";

	public function toOptionArray()
    {
        return [
            ['value' => 'all', 'label' => 'All'],
            ['value' => 'recent', 'label' => 'Recently Viewed'],
            ['value' => 'similar', 'label' => 'Similar to current product'],
            ['value' => 'related', 'label' => 'Related Products'],
        ];
    }
}
