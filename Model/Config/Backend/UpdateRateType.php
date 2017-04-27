<?php

namespace DynamicYield\Integration\Model\Config\Backend;

use DynamicYield\Integration\Api\Data\ProductFeedInterface;

class UpdateRateType extends AbstractUpdateRate
{
    /**
     * @return mixed
     */
    function getTime()
    {
        return $this->_config->getValue(ProductFeedInterface::UPDATE_RATE_TIME);
    }

    /**
     * @return mixed
     */
    function getType()
    {
        return $this->getValue();
    }
}