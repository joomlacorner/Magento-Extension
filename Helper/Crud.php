<?php

namespace Shippop\Ecommerce\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Api\DataObjectHelper;

use Shippop\Ecommerce\Api\Data\OrderShippopInterface;
use Shippop\Ecommerce\Api\OrderShippopRepositoryInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterfaceFactory;

use Magento\Framework\Serialize\SerializerInterface;

class Crud extends AbstractHelper
{
    protected $_dataRepository;
    protected $_serializerInterface;

    public function __construct(
        OrderShippopRepositoryInterface $dataRepository,
        OrderShippopInterfaceFactory $dataInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        SerializerInterface $serializerInterface
    ) {
        $this->_dataRepository = $dataRepository;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataFactory = $dataInterfaceFactory;
        $this->_serializerInterface = $serializerInterface;
    }

    /**
     * @param mixed $order_id
     * @param mixed $key
     *
     * @return mixed
     */
    public function get_post_meta($order_id, $key)
    {
        $entity = $this->_dataRepository->getById($order_id);
        $data = $entity->getData();
        if (!empty($key) && !empty($data[$key])) {
            return $data[$key];
        } else {
            return false;
        }
    }

    /**
     * @param mixed $field_where
     * @param mixed $value
     * @param string $condition
     * @param bool $single
     *
     * @return array
     */
    public function get_post_by_meta($field_where, $value, $condition = "=", $single = false)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchCriteriaBuilder = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            $field_where,
            $value,
            $condition
        )->create();

        try {
            $items = $this->_dataRepository->getList($searchCriteria);
            $data = $items->getItems();
            if ($single) {
                return (!empty($data[0])) ? $data[0] : [];
            } else {
                return $data;
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    public function update_post_meta($data)
    {
        if (!empty($data["extra"])) {
            $extra = [];
            $order = $this->get_post_meta($data["order_id"], "extra");
            if (!empty($order)) {
                $extra = $this->_serializerInterface->unserialize($order);
            }
            $data["extra"] = $this->_serializerInterface->serialize(array_merge($extra, $data["extra"]));
        }

        $model = $this->_dataFactory->create();
        $this->_dataObjectHelper->populateWithArray($model, $data, OrderShippopInterface::class);
        return $this->_dataRepository->save($model);
    }

    /**
     * @param mixed $order_id
     *
     * @return mixed
     */
    public function delete_post_meta($order_id)
    {
        return $this->_dataRepository->deleteById($order_id);
    }

    /**
     * @param mixed $order_ids
     *
     * @return boolean
     */
    public function update_confirm_purchase($order_ids)
    {
        foreach (explode(",", $order_ids) as $order_id) {
            $data = [
                'order_id' => $order_id,
                'confirm_purchase_status' => 1
            ];

            $this->update_post_meta($data);
        }

        return true;
    }
}
