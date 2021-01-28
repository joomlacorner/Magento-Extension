<?php

namespace Shippop\Ecommerce\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class ShippopStatus extends Column
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
        $specm_shippop_order_statuses_color = [
            'wait'    => ['gray', 'black'],
            'booking' => ['#f9dea7', 'black'],
            'shipping'    => ['#cad7e1', '#546877'],
            'complete'  => ['#c6e2c7', '#5e861f'],
            'cancel'  => ['#eba4a3', '#933d3c'],
            'return'   => ['#e5e5e5', '#a7a7a7']
        ];

        $shippop_status = $this->_coreSession->getShippopStatus();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['shippop_status'])) {
                    $str = "<span style='font-size:10px;padding: 5px;background-color: " . $specm_shippop_order_statuses_color[$item['shippop_status']][0] . ";color: " . $specm_shippop_order_statuses_color[$item['shippop_status']][1] . "'> ";
                    $str .= __($shippop_status[$item['shippop_status']]);
                    $str .= " </span>";
                    $item["shippop_status"] = $str;
                }
            }
        }
        return $dataSource;
    }
}
