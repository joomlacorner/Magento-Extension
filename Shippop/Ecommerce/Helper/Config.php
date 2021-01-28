<?php

namespace Shippop\Ecommerce\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface as ScopeInterface;

class Config extends AbstractHelper
{
    const XML_PATH = 'shippop_configuration';

    /**
     * @param mixed $field
     * @param null $storeId
     *
     * @return mixed
     */
    private function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param string $group
     * @param string $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getShippopConfig($group = "auth", $code = "", $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH . '/' . $group . '/' . $code, $storeId);
    }
}
