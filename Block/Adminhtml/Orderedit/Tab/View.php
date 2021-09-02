<?php

namespace Shippop\Ecommerce\Block\Adminhtml\Orderedit\Tab;

use Magento\Framework\Serialize\SerializerInterface;

class View extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $urlBuilder;
    protected $_template = 'tab/view/parcelinfo.phtml';
    protected $_checkoutSession;
    protected $_crud;
    protected $config;
    protected $_serializerInterface;
    // protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Shippop\Ecommerce\Helper\Crud $crud,
        \Shippop\Ecommerce\Helper\Config $config,
        // \Magento\Framework\Registry $registry,
        SerializerInterface $serializerInterface,
        array $data = []
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->formKey = $formKey;
        $this->urlBuilder = $urlBuilder;
        $this->_crud = $crud;
        $this->config = $config;
        $this->_serializerInterface = $serializerInterface;
        // $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return object
     */
    public function getOrder()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        // $order = $this->_coreRegistry->registry('current_order');
        return $order;
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
        // return $this->getOrder()->getEntityId();
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        $order = $this->_crud->get_post_meta($this->getOrderId(), "extra");
        $extra = [];
        if (!empty($order)) {
            $extra = $this->_serializerInterface->unserialize($order);
        }
        return $extra;
    }

    /**
     * @return array
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
        $shippop_testing_mode = $this->config->getShippopConfig("auth", "shippop_testing_mode");
        if ( $shippop_testing_mode == "1" ) {
            $domain = ".dev";
        } else {
            $domain = ".com";
        }
        if (strtoupper($shippop_server) === "TH") {
            return "https://www.shippop" . $domain . "/tracking/?tracking_code=";
        } else {
            return "https://www.shippop.my/tracking/?tracking_code=";
        }
    }

    /**
     * @return int
     */
    public function getOrderIncrementId()
    {
        return $this->getOrder()->getIncrementId();
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('SHIPPOP Parcel information');
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return __('SHIPPOP Parcel information');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getFormAction()
    {
        return $this->urlBuilder->getUrl("shippop/order/updateparcel");
    }

    /**
     * get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
