<?php

namespace Shippop\Ecommerce\Model;

class UpdateStatus
{
    protected $_utility;
    
    public function __construct(
        \Shippop\Ecommerce\Helper\Utility $Utility
    ) {
        $this->_utility = $Utility;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStatusShipping($tracking_code, $order_status, $data)
    {
        $request = [
            'tracking_code' => $tracking_code,
            'order_status' => $order_status,
            'data' => $data
        ];
        $response = $this->_utility->webHooksUpdate($request);
        return json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
