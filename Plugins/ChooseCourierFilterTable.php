<?php

namespace Shippop\Ecommerce\Plugins;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as CollectionFactory;

class ChooseCourierFilterTable
{
    private $messageManager;
    private $collection;

    public function __construct(
        MessageManager $messageManager,
        SalesOrderGridCollection $collection
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
    }

    public function aroundGetReport(
        CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);

        $args = [
            'shippop_sales_order_grid_data_source', 'shippop_parcel_sales_order_grid_data_source'
        ];

        if (in_array($requestName, $args)) {
            $where_join = "";
            if ($requestName == 'shippop_sales_order_grid_data_source') {
                $where_join = "main_table.entity_id = sales_order_shippop.order_id AND ";
                $where_join .= "sales_order_shippop.shippop_status = 'wait' AND main_table.status = 'processing'";
            } elseif ($requestName == 'shippop_parcel_sales_order_grid_data_source') {
                $where_join = "main_table.entity_id = sales_order_shippop.order_id AND ";
                $where_join .= "sales_order_shippop.shippop_status != 'wait'";
            }

            if ($result instanceof $this->collection) {
                $select = $this->collection->getSelect();
                $select->join(
                    ["sales_order_shippop"],
                    $where_join,
                    [
                        'sales_order_shippop.shippop_status as _shippop_status',
                        'sales_order_shippop.shippop_status',
                        'sales_order_shippop.tracking_code',
                        'sales_order_shippop.courier_tracking_code',
                        'sales_order_shippop.purchase_id',
                        'sales_order_shippop.courier_code'
                    ]
                )->distinct();
            }
        }
        return $this->collection;
    }
}
