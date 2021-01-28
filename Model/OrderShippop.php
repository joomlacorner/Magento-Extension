<?php

namespace Shippop\Ecommerce\Model;

use Shippop\Ecommerce\Api\Data\OrderShippopInterface;

class OrderShippop extends \Magento\Framework\Model\AbstractModel implements OrderShippopInterface
{
    const CACHE_TAG = 'sales_order_shippop';
    protected $_cacheTag = 'sales_order_shippop';
    protected $_eventPrefix = 'sales_order_shippop';

    protected function _construct()
    {
        parent::_init('Shippop\Ecommerce\Model\ResourceModel\OrderShippop');
    }
    
    /**
     * getOrderId
     *
     * @return void
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param mixed $orderId
     *
     * @return void
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return void
     */
    public function getShippopStatus()
    {
        return $this->getData(self::SHIPPOP_STATUS);
    }

    /**
     * @param mixed $ShippopStatus
     *
     * @return void
     */
    public function setShippopStatus($ShippopStatus)
    {
        return $this->setData(self::SHIPPOP_STATUS, $ShippopStatus);
    }

    /**
     * @return void
     */
    public function getTrackingCode()
    {
        return $this->getData(self::TRACKING_CODE);
    }

    /**
     * @param mixed $TrackingCode
     *
     * @return void
     */
    public function setTrackingCode($TrackingCode)
    {
        return $this->setData(self::TRACKING_CODE, $TrackingCode);
    }

    /**
     * @return void
     */
    public function getCourierTrackingCode()
    {
        return $this->getData(self::COURIER_TRACKING_CODE);
    }

    /**
     * @param mixed $CourierTrackingCode
     *
     * @return void
     */
    public function setCourierTrackingCode($CourierTrackingCode)
    {
        return $this->setData(self::COURIER_TRACKING_CODE, $CourierTrackingCode);
    }

    /**
     * @return void
     */
    public function getPurchaseId()
    {
        return $this->getData(self::PURCHASE_ID);
    }

    /**
     * @param mixed $PurchaseId
     *
     * @return void
     */
    public function setPurchaseId($PurchaseId)
    {
        return $this->setData(self::PURCHASE_ID, $PurchaseId);
    }

    /**
     * @return void
     */
    public function getConfirmPurchaseStatus()
    {
        return $this->getData(self::CONFIRM_PURCHASE_STATUS);
    }

    /**
     * @param mixed $ConfirmPurchaseStatus
     *
     * @return void
     */
    public function setConfirmPurchaseStatus($ConfirmPurchaseStatus)
    {
        return $this->setData(self::CONFIRM_PURCHASE_STATUS, $ConfirmPurchaseStatus);
    }

    /**
     * @return void
     */
    public function getEnvironmentSandbox()
    {
        return $this->getData(self::ENVIRONMENT_SANDBOX);
    }

    /**
     * @param mixed $CourierCode
     *
     * @return void
     */
    public function setCourierCode($CourierCode)
    {
        return $this->setData(self::COURIER_CODE, $CourierCode);
    }

    /**
     * @return void
     */
    public function getCourierCode()
    {
        return $this->getData(self::COURIER_CODE);
    }

    /**
     * @param mixed $EnvironmentSandbox
     *
     * @return void
     */
    public function setEnvironmentSandbox($EnvironmentSandbox)
    {
        return $this->setData(self::ENVIRONMENT_SANDBOX, $EnvironmentSandbox);
    }

    /**
     * @return void
     */
    public function getExtra()
    {
        return $this->getData(self::EXTRA);
    }

    /**
     * @param mixed $Extra
     *
     * @return void
     */
    public function setExtra($Extra)
    {
        return $this->setData(self::EXTRA, $Extra);
    }

    /**
     * @return void
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param mixed $updatedAt
     *
     * @return void
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * @return void
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param mixed $createdAt
     *
     * @return void
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
