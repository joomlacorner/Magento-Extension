<?php

namespace Shippop\Ecommerce\Api\Data;

interface OrderShippopInterface
{
    const ORDER_ID = 'order_id';
    const SHIPPOP_STATUS = 'shippop_status';
    const TRACKING_CODE = 'tracking_code';
    const COURIER_TRACKING_CODE = 'courier_tracking_code';
    const PURCHASE_ID = 'purchase_id';
    const CONFIRM_PURCHASE_STATUS = 'confirm_purchase_status';
    const ENVIRONMENT_SANDBOX = 'environment_sandbox';
    const COURIER_CODE = 'courier_code';
    const EXTRA = 'extra';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * @return array
     */
    public function getOrderId();

    /**
     * @param array $orderId
     *
     * @return array
     */
    public function setOrderId($orderId);

    /**
     * @return array
     */
    public function getShippopStatus();

    /**
     * @param array $ShippopStatus
     *
     * @return array
     */
    public function setShippopStatus($ShippopStatus);

    /**
     * @return array
     */
    public function getTrackingCode();

    /**
     * @param array $TrackingCode
     *
     * @return array
     */
    public function setTrackingCode($TrackingCode);

    /**
     * @return array
     */
    public function getCourierTrackingCode();

    /**
     * @param array $CourierTrackingCode
     *
     * @return array
     */
    public function setCourierTrackingCode($CourierTrackingCode);

    /**
     * @return array
     */
    public function getPurchaseId();

    /**
     * @param array $PurchaseId
     *
     * @return array
     */
    public function setPurchaseId($PurchaseId);

    /**
     * @return array
     */
    public function getConfirmPurchaseStatus();

    /**
     * @param array $ConfirmPurchaseStatus
     *
     * @return array
     */
    public function setConfirmPurchaseStatus($ConfirmPurchaseStatus);

    /**
     * @return array
     */
    public function getEnvironmentSandbox();

    /**
     * @param array $EnvironmentSandbox
     *
     * @return array
     */
    public function setEnvironmentSandbox($EnvironmentSandbox);

    /**
     * @return array
     */
    public function getCourierCode();

    /**
     * @param array $CourierCode
     *
     * @return array
     */
    public function setCourierCode($CourierCode);

    /**
     * @return string
     */
    public function getExtra();

    /**
     * @param array $Extra
     *
     * @return array
     */
    public function setExtra($Extra);

    /**
     * @return array
     */
    public function getUpdatedAt();

    /**
     * @param array $updatedAt
     *
     * @return array
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return array
     */
    public function getCreatedAt();

    /**
     * @param array $createdAt
     *
     * @return array
     */
    public function setCreatedAt($createdAt);
}
