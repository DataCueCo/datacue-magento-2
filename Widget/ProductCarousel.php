<?php

namespace DataCue\MagentoModule\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
 
class ProductCarousel extends Template implements BlockInterface
{
	protected $_template = "DataCue_MagentoModule::widget/product_carousel.phtml";
}
