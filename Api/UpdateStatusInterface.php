<?php

namespace Shippop\Ecommerce\Api;

interface UpdateStatusInterface
{
    /**
     * @param mixed $tracking_code
     * @param mixed $order_status
     * @param mixed $data
     *
     * @return string JSON
     */
    public function updateStatusShipping($tracking_code, $order_status, $data);
}
