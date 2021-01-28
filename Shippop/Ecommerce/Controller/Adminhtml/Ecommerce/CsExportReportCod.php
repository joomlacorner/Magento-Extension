<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\Http;

class CsExportReportCod extends \Magento\Backend\App\Action
{
    protected $uploaderFactory;
    protected $_utility;
    protected $_locationFactory;
    protected $request;
    protected $_messageManager;
    protected $urlBuilder;

    const FILTER_TYPE_SHIP = "SHIPPING";
    const FILTER_TYPE_TRAN = "TRANSFER";

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Http $request
    ) {
        $this->_messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->_fileFactory = $fileFactory;
        $this->request = $request;
        $this->_utility = $Utility;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $response = [];
        $all_request = $this->request->getParams();
        $start_date = date('Y-m-d', strtotime(date('Y-m-d') . "-1 days"));
        $end_date = date('Y-m-d');
        $filter_type = self::FILTER_TYPE_SHIP;
        if (!empty($all_request["filters"]["datetime_complete"]["from"])) {
            $start_date = date('Y-m-d', strtotime($all_request["filters"]["datetime_complete"]["from"]));
        }
        if (!empty($all_request["filters"]["datetime_complete"]["to"])) {
            $end_date = date('Y-m-d', strtotime($all_request["filters"]["datetime_complete"]["to"]));
        }
        if (!empty($all_request["filters"]["filter_type"]) &&
            $all_request["filters"]["filter_type"] != self::FILTER_TYPE_SHIP) {
            $filter_type = self::FILTER_TYPE_TRAN;
        }

        $response = $this->_utility->reportCOD($start_date, $end_date, $filter_type);
        $items = [];
        if ($response["status"]) {
            $items = $response["data"];
        } else {
            $this->_messageManager->addErrorMessage($response["message"]);
            $redirect = $this->urlBuilder->getUrl("shippop/ecommerce/reportcod");
            $this->_redirect($redirect);
        }

        $filepath = "SHIPPOP_Report_COD_" . time() . ".csv";
        $this->directory->create('export');
        $stream = $this->directory->openFile($filepath, 'w+');
        $stream->lock();

        $columns = [
            __("Order"),
            __("SHIPPIP's tracking number"),
            __("Courier's tracking number"),
            __("Delivery date"),
            __("Date of delivery completion"),
            __("Status"),
            __("Receiver name"),
            __("COD transfer date"),
            __("COD amount"),
            __("Fee"),
            __("Transferring amount"),
            __("Billing ID"),
            __("Transferring status"),
            __("Filter Type"),
        ];

        $stream->writeCsv($columns);
        foreach ($items as $item) {
            $itemData = [];
            $itemData[] = $item["increment_id"];
            $itemData[] = $item["tracking_code"];
            $itemData[] = $item["courier_tracking_code"];
            $itemData[] = $item["datetime_shipping"];
            $itemData[] = $item["datetime_complete"];
            $itemData[] = $item["_shippop_status"];
            $itemData[] = $item["destination_name"];
            $itemData[] = $item["datetime_transfer"];
            $itemData[] = $item["cod_amount"];
            $itemData[] = $item["cod_charge"];
            $itemData[] = $item["cod_total"];
            $itemData[] = $item["receipt_id"];
            $itemData[] = $item["_cod_status"];
            $itemData[] = $item["filter_type"];

            $stream->writeCsv($itemData);
        }

        $content = [];
        $content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = '1';

        return $this->_fileFactory->create($filepath, $content, DirectoryList::VAR_DIR);
    }
}
