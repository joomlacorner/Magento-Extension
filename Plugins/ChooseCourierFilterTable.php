<?php

namespace Shippop\Ecommerce\Plugins;

use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as CollectionFactory;

class ChooseCourierFilterTable
{
    private $messageManager;
    private $collection;
    protected $_resource;

    public function __construct(
        MessageManager $messageManager,
        SalesOrderGridCollection $collection,
        \Magento\Framework\App\ResourceConnection $resource
    ) {

        $this->messageManager = $messageManager;
        $this->collection = $collection;
        $this->_resource = $resource;
    }

    public function aroundGetReport(
        CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);
        $connection = $this->_resource->getConnection();
        $sales_order_shippop = $this->_resource->getTableName('sales_order_shippop');

        $args = [
            'shippop_sales_order_grid_data_source', 'shippop_parcel_sales_order_grid_data_source'
        ];

        if (in_array($requestName, $args)) {
            $where_join = "";
            if ($requestName == 'shippop_sales_order_grid_data_source') {
                $where_join = "main_table.entity_id = $sales_order_shippop.order_id";
                $where_join .= " AND $sales_order_shippop.shippop_status = 'wait' AND (main_table.status = 'processing' OR main_table.payment_method = 'cashondelivery')";
            } elseif ($requestName == 'shippop_parcel_sales_order_grid_data_source') {
                $where_join = "main_table.entity_id = $sales_order_shippop.order_id AND ";
                $where_join .= "$sales_order_shippop.shippop_status != 'wait'";
            }

            if ($result instanceof $this->collection) {
                $select = $this->collection->getSelect()->group('main_table.entity_id');
                $select->join(
                    [$sales_order_shippop],
                    $where_join,
                    [
                        $sales_order_shippop . '.shippop_status as _shippop_status',
                        $sales_order_shippop . '.shippop_status',
                        $sales_order_shippop . '.tracking_code',
                        $sales_order_shippop . '.courier_tracking_code',
                        $sales_order_shippop . '.purchase_id',
                        $sales_order_shippop . '.courier_code'
                    ]
                )->distinct();

                // $writer = new \Laminas\Log\Writer\Stream(BP . '/var/log/test.log');
                // $logger = new \Laminas\Log\Logger();
                // $logger->addWriter($writer);
                // $logger->info( $this->collection->getSelect()->__toString() );
            }
            return $this->collection;
        }
        return $result;
    }
}