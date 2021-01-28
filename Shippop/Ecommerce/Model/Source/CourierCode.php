<?php

namespace Shippop\Ecommerce\Model\Source;

use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Magento\Framework\Serialize\SerializerInterface;

class CourierCode implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_coreSession;
    protected $config;
    protected $_serializerInterface;
    
    public function __construct(
        CoreSession $coreSession,
        \Shippop\Ecommerce\Helper\Config $config,
        SerializerInterface $serializerInterface
    ) {
        $this->_coreSession = $coreSession;
        $this->config = $config;
        $this->_serializerInterface = $serializerInterface;
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
        $courier_list = $this->config->getShippopConfig("auth", "courier_list");
        $courier_list = (!empty($courier_list)) ? $this->_serializerInterface->unserialize($courier_list) : [];
        $couriers = [];
        foreach ($courier_list as $key => $value) {
            $couriers[$key] = $value["courier_name"];
        }
        return $couriers;
    }
}
