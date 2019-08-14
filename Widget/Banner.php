<?php

namespace DataCue\MagentoModule\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
 
class Banner extends Template implements BlockInterface
{
		protected $_template = "DataCue_MagentoModule::widget/banner.phtml";
}
