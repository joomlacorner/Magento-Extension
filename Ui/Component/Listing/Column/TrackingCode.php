<?php

namespace Shippop\Ecommerce\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class TrackingCode extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

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
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['tracking_code'])) {
                    $link = '';
                    $link .= '<a href="javascript:void(0)" data-order-id="' . $item['increment_id'] . '" ';
                    $link .= 'data-tracking-code="'. $item['tracking_code'] .'" class="shippop-tracking-history">';
                    $link .= $item['tracking_code'] . '</a>';

                    $item['_tracking_code'] = $item['tracking_code'];
                    $item['tracking_code'] = $link;
                }
            }
        }
        return $dataSource;
    }
}
