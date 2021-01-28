<?php

namespace Shippop\Ecommerce\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Framework\Serialize\SerializerInterface;

class CourierCode extends Column
{
    protected $_coreSession;
    protected $config;
    protected $_serializerInterface;

    /**
     * Order Id constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param string[] $components
     * @param string[] $data
     */
    public function __construct(
        \Shippop\Ecommerce\Helper\Config $config,
        SerializerInterface $serializerInterface,
        UrlInterface $urlBuilder,
        CoreSession $coreSession,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->_coreSession = $coreSession;
        $this->config = $config;
        $this->_serializerInterface = $serializerInterface;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $courier_list = $this->config->getShippopConfig("auth", "courier_list");
        $courier_list = (!empty($courier_list)) ? $this->_serializerInterface->unserialize($courier_list) : [];
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['courier_code'])) {
                    if (!empty($courier_list[$item['courier_code']])) {
                        $item_courier_code = $courier_list[$item['courier_code']]['courier_name'];
                    } else {
                        $item_courier_code = $item['courier_code'];
                    }
                    $item['courier_code'] = $item_courier_code;
                }
            }
        }
        return $dataSource;
    }
}
