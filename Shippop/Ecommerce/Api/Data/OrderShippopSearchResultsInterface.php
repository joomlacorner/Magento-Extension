<?php

namespace Shippop\Ecommerce\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface OrderShippopSearchResultsInterface extends SearchResultsInterface
{
    public function getItems();
    public function setItems(array $items);
}
