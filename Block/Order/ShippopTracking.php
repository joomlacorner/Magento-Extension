<?php

namespace Shippop\Ecommerce\Block\Order;

/**
 * @api
 */
class ShippopTracking extends \Magento\Framework\View\Element\Template
{
    protected $_template = 'sales/order/view.phtml';
    protected $urlBuilder;
    protected $_checkoutSession;
    protected $_crud;
    protected $config;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Shippop\Ecommerce\Helper\Crud $crud,
        \Shippop\Ecommerce\Helper\Config $config,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->formKey = $formKey;
        $this->urlBuilder = $urlBuilder;
        $this->_crud = $crud;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $shippop_status = $this->_crud->get_post_meta($this->getOrderId(), "shippop_status");
        return $shippop_status;
    }
    
    /**
     * @return string
     */
    public function getTrackingCode()
    {
        $courier_tracking_code = $this->_crud->get_post_meta($this->getOrderId(), "courier_tracking_code");
        return $courier_tracking_code;
    }

    /**
     * @return string
     */
    public function getTrackingLocation()
    {
        $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
        if (strtoupper($shippop_server) === "TH") {
            return "https://www.shippop.com/tracking/?tracking_code=";
        } else {
            return "https://www.shippop.my/tracking/?tracking_code=";
        }
    }
}
