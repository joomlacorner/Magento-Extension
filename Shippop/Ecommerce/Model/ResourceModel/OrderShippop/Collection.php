<?php

namespace Shippop\Ecommerce\Model\ResourceModel\OrderShippop;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'order_id';
    
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        parent::_init(
            'Shippop\Ecommerce\Model\OrderShippop',
            'Shippop\Ecommerce\Model\ResourceModel\OrderShippop'
        );
    }
}
