<?php

namespace DataCue\MagentoModule\Modules;

use DataCue\Client;
use DataCue\MagentoModule\Utils\Info;

abstract class Base
{
    public function __construct()
    {
        Client::setIntegrationAndVersion('Magento2', Info::getPackageVersion());
    }
}