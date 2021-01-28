<?php

namespace Shippop\Ecommerce\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Actions extends Column
{

    const URL_PATH_VIEW = 'sales/order/view';
    const URL_PATH_CANCEL = 'shippop/ecommerce/cancelpurchase';
    
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')]['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_VIEW,
                            [
                                'order_id' => $item['increment_id']
                            ]
                        ),
                        'label' => __('View')
                    ];

                if (!empty($item['_shippop_status']) && $item['_shippop_status'] == "booking") {
                    $item[$this->getData('name')]['cacnel'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_CANCEL,
                            [
                                'tracking_code' => $item['_tracking_code']
                            ]
                        ),
                        'label' => __('Cancel order'),
                        'confirm' => [
                            'title' => __('Cancel order'),
                            'message' => __('Are you sure you wan\'t to cancel this order ?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
