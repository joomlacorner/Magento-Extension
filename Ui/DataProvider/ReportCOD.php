<?php

namespace Shippop\Ecommerce\Ui\DataProvider;

use Magento\Framework\App\Request\Http;
use Magento\Ui\DataProvider\AbstractDataProvider;

class ReportCOD extends AbstractDataProvider
{
    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    private $searchCriteriaBuilder;

    protected $_utility;

    protected $name;
    protected $primaryFieldName;
    protected $requestFieldName;

    const FILTER_TYPE_SHIPPING = "SHIPPING";
    const FILTER_TYPE_TRANSFER = "TRANSFER";
    /**
     * Construct
     *
     * @param \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Shippop\Ecommerce\Helper\Utility $Utility
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Http $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        $name,
        $primaryFieldName,
        $requestFieldName,
        Http $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->request = $request;
        $this->_utility = $Utility;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Preparing Collection For Grid
     *
     * @return Array
     */

    public function getCollection()
    {
        $response = [];
        $all_request = $this->request->getParams();
        $start_date = date('Y-m-d', strtotime(date('Y-m-d') . "-1 days"));
        $end_date = date('Y-m-d');
        $filter_type = self::FILTER_TYPE_SHIPPING;
        if (!empty($all_request["filters"]["datetime_complete"]["from"])) {
            $start_date = date('Y-m-d', strtotime($all_request["filters"]["datetime_complete"]["from"]));
        }
        if (!empty($all_request["filters"]["datetime_complete"]["to"])) {
            $end_date = date('Y-m-d', strtotime($all_request["filters"]["datetime_complete"]["to"]));
        }
        if (!empty($all_request["filters"]["filter_type"]) &&
            $all_request["filters"]["filter_type"] != self::FILTER_TYPE_SHIPPING) {
            $filter_type = self::FILTER_TYPE_TRANSFER;
        }

        $response = $this->_utility->reportCOD($start_date, $end_date, $filter_type);
        $response['start_date'] = $start_date;
        $response['end_date'] = $end_date;

        $items = [];
        if ($response["status"]) {
            $items = $response["data"];
        }

        // $pagesize = (int) $this->request->getParam('paging')['pageSize'];
        if (array_key_exists("paging", $all_request) && !empty($all_request["paging"]["pageSize"])) {
            $pagesize = $all_request["paging"]["pageSize"];
        } else {
            $pagesize = 20;
        }

        // $pageCurrent = (int) $this->request->getParam('paging')['current'];
        if (array_key_exists("paging", $all_request) && !empty($all_request["paging"]["current"])) {
            $pageCurrent = $all_request["paging"]["current"];
        } else {
            $pageCurrent = 1;
        }
        $pageoffset = ($pageCurrent - 1) * $pagesize;

        return [
            'totalRecords' => count($items),
            'items' => array_slice($items, $pageoffset, $pageoffset + $pagesize)
        ];
    }

    /**
     * Return Prepared Data To Admin Grid
     *
     * @return AbstractDataProvider
     */

    public function getData()
    {
        if (!$this->getCollection()) {
            $this->getCollection();
        }
        return $this->getCollection();
    }
    
    /**
     *  Adding Filters To Grid Collection
     *
     */

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $this->searchCriteriaBuilder->addFilter($filter);
    }

    /**
     * Adding Order To Grid Collection
     *
     */

    public function addOrder($field, $direction)
    {
        $this->searchCriteriaBuilder->addSortOrder($field, $direction);
    }

    /**
     * Set Limit To Admin Collection
     *
     */

    public function setLimit($offset, $size)
    {
        $this->searchCriteriaBuilder->setPageSize(1);
        $this->searchCriteriaBuilder->setCurrentPage($offset);
    }
}
