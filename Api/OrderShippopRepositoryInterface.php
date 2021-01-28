<?php

namespace Shippop\Ecommerce\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterface;

interface OrderShippopRepositoryInterface
{
    public function save(OrderShippopInterface $data);
    public function getById($dataId);
    public function getByTrackingCode($trackingCode);
    public function getList(SearchCriteriaInterface $searchCriteria);
    public function delete(OrderShippopInterface $data);
    public function deleteById($dataId);
}
