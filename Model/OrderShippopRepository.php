<?php

namespace Shippop\Ecommerce\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;

use Magento\Framework\Api\SearchCriteriaBuilder;

use Shippop\Ecommerce\Api\OrderShippopRepositoryInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterface;
use Shippop\Ecommerce\Api\Data\OrderShippopInterfaceFactory;
use Shippop\Ecommerce\Api\Data\OrderShippopSearchResultsInterfaceFactory;
use Shippop\Ecommerce\Model\ResourceModel\OrderShippop as OrderShippopData;
use Shippop\Ecommerce\Model\ResourceModel\OrderShippop\CollectionFactory as DataCollectionFactory;
use Magento\Framework\Api\FilterBuilder;

class OrderShippopRepository implements OrderShippopRepositoryInterface
{
    protected $_instances = [];
    protected $_resource;
    /**
     * @var DataCollectionFactory
     */
    protected $_dataCollectionFactory;
    /**
     * @var DataSearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;
    /**
     * @var DataInterfaceFactory
     */
    protected $_dataInterfaceFactory;
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $_dataObjectProcessor;

    protected $_filterBuilder;

    protected $_searchCriteriaBuilder;

    public function __construct(
        OrderShippopData $resource,
        DataCollectionFactory $dataCollectionFactory,
        OrderShippopSearchResultsInterfaceFactory $dataSearchResultsInterfaceFactory,
        OrderShippopInterfaceFactory $dataInterfaceFactory,
        FilterBuilder $filterBuilder,
        DataObjectHelper $dataObjectHelper,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->_resource = $resource;
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_searchResultsFactory = $dataSearchResultsInterfaceFactory;
        $this->_dataInterfaceFactory = $dataInterfaceFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * @param DataInterface $data
     * @return DataInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderShippopInterface $data)
    {
        try {
            /** @var DataInterface|\Magento\Framework\Model\AbstractModel $data */
            $this->_resource->save($data);
        } catch (\Exception $e) {
            $this->_utility->specm_writing_log($e->getMessage(), $e);
            throw new CouldNotSaveException(__(
                'Could not save the data: %1',
                $e->getMessage()
            ));
        }
        return $data;
    }

    /**
     * Get data record
     *
     * @param $dataId
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($dataId)
    {
        if (!isset($this->_instances[$dataId])) {
            /** @var \[Vendor]\[Module]\Api\Data\DataInterface|\Magento\Framework\Model\AbstractModel $data */
            $data = $this->_dataInterfaceFactory->create();
            $this->_resource->load($data, $dataId);
            if (!$data->getId()) {
                throw new NoSuchEntityException(__('Requested data doesn\'t exist'));
            }
            $this->_instances[$dataId] = $data;
        }
        return $this->_instances[$dataId];
    }

    public function getByTrackingCode($trackingCode)
    {
        $filter = $this->_filterBuilder
        ->setField("tracking_code")
        ->setConditionType('eq')
        ->setValue($trackingCode)
        ->create();
        $this->_searchCriteriaBuilder->addFilters([$filter]);
        $searchCriteria = $this->_searchCriteriaBuilder->create();

        $productsItems  = $this->getList($searchCriteria)->getItems();
        return $productsItems;
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->_dataInterfaceFactory->create();
        $searchResults->setSearchCriteria($criteria);
 
        $collection = $this->_dataCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrdersData = $criteria->getSortOrders();
        if ($sortOrdersData) {
            foreach ($sortOrdersData as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
 
        $collection->setPageSize($criteria->getPageSize());
        
        $testItem = [];
        /** @var Test $testModel */
        foreach ($collection as $testModel) {
            $testData = $this->_dataInterfaceFactory->create();
            $this->_dataObjectHelper->populateWithArray(
                $testData,
                $testModel->getData(),
                OrderShippopInterface::class
            );
            $testItem[] = $this->_dataObjectProcessor->buildOutputDataArray(
                $testData,
                OrderShippopInterface::class
            );
        }
        $searchResults->setItems($testItem);
        return $searchResults;
    }

    /**
     * @param DataInterface $data
     * @return bool
     * @throws CouldNotSaveException
     * @throws StateException
     */
    public function delete(OrderShippopInterface $data)
    {
        /** @var \[Vendor]\[Module]\Api\Data\DataInterface|\Magento\Framework\Model\AbstractModel $data */
        $id = $data->getId();
        try {
            unset($this->_instances[$id]);
            $this->_resource->delete($data);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            $this->_utility->specm_writing_log($e->getMessage(), $e);
            throw new StateException(
                __('Unable to remove data %1', $id)
            );
        }
        unset($this->_instances[$id]);
        return true;
    }

    /**
     * @param $dataId
     * @return bool
     */
    public function deleteById($dataId)
    {
        $data = $this->getById($dataId);
        return $this->delete($data);
    }
}
