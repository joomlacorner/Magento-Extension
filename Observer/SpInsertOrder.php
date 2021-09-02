<?php

namespace Shippop\Ecommerce\Observer;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Serialize\SerializerInterface;

use Shippop\Ecommerce\Api\OrderShippopRepositoryInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterfaceFactory;

class SpInsertOrder implements \Magento\Framework\Event\ObserverInterface
{
    protected $_dataObjectHelper;
    protected $_dataRepository;
    protected $_dataFactory;
    protected $_serializerInterface;

    public function __construct(
        SerializerInterface $serializerInterface,
        DataObjectHelper $dataObjectHelper,
        OrderShippopRepositoryInterface $dataRepository,
        OrderShippopInterfaceFactory $dataInterfaceFactory
    ) {
        $this->_serializerInterface = $serializerInterface;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataRepository = $dataRepository;
        $this->_dataFactory = $dataInterfaceFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $model = $this->_dataFactory->create();

        // $orderId = $order->getIncrementId();
        $orderId = $order->getId();
        if ( $orderId ) {
            $entity = $this->_dataRepository->getById($orderId);
            $getData = $entity->getData();
            if ( empty($getData) ) {
                $orderItems = $order->getAllItems();
                $_weight = 0;
                foreach ($orderItems as $item) {
                    $itemData = $item->getData();
                    $qty = 1;
                    if (!empty($itemData['product_options']['info_buyRequest']['qty'])) {
                        $qty = $itemData['product_options']['info_buyRequest']['qty'];
                    }
                    $weight = $itemData['weight'] * $qty;
                    $_weight += $weight;
                }
    
                $data = [
                    // 'order_id' => $order->getIncrementId(),
                    'order_id' => $order->getId(),
                    'shippop_status' => 'wait',
                    'extra' => $this->_serializerInterface->serialize([
                        'weight' => $_weight,
                        'width' => 1,
                        'length' => 1,
                        'height' => 1
                    ])
                ];
                $this->_dataObjectHelper->populateWithArray($model, $data, OrderShippopInterface::class);
                $this->_dataRepository->save($model);
            }
        }
    }
}
