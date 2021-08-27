<?php

namespace Shippop\Ecommerce\Model\Source;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class SelectLabel implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $config;
    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession,
        \Shippop\Ecommerce\Helper\Config $config
    ) {
        $this->_coreSession = $coreSession;
        $this->config = $config;
    }

    public function toOptionArray()
    {
        $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
        $label_size = $this->_coreSession->getLabelSize();
        $args = [];
        $args[] = [
            'value' => '',
            'label' => __("Print waybill")
        ];
        foreach ($label_size as $key => $val) {
            if ( $shippop_server == "MY" && in_array( $key , ['sticker' , 'sticker4x6'] )) {
                continue;
            }
            $args[] = [
                'value' => $key,
                'label' => $val
            ];
        }
        return $args;
    }
}
