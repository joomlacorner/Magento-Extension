<?php

namespace Shippop\Ecommerce\Model\Source;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class ShippopStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_coreSession;
    
    public function __construct(
        CoreSession $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        $shippop_status = $this->_coreSession->getShippopStatus();
        return $shippop_status;
    }
}
