<?php

namespace Shippop\Ecommerce\Model\Source;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class SelectLabel implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_coreSession;

    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function toOptionArray()
    {
        $label_size = $this->_coreSession->getLabelSize();
        $args = [];
        $args[] = [
            'value' => '',
            'label' => __("Print waybill")
        ];
        foreach ($label_size as $key => $val) {
            $args[] = [
                'value' => $key,
                'label' => $val
            ];
        }
        return $args;
    }
}
