<?php

namespace Shippop\Ecommerce\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class FilterType extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    protected $_coreSession;

    /**
     * Order Id constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param string[] $components
     * @param string[] $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CoreSession $coreSession,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_coreSession = $coreSession;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $filter_type = ['SHIPPING' => __("Delivery date"), 'TRANSFER' => __("COD transfer date")];
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['filter_type'])) {
                    $item['filter_type'] = $filter_type[$item['filter_type']];
                }
            }
        }
        return $dataSource;
    }
}
