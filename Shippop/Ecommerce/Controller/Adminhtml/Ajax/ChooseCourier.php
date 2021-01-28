<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class ChooseCourier extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;
    protected $config;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Shippop\Ecommerce\Helper\Config $config
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->config = $config;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->formKeyValidator->validate($this->getRequest()) && $this->getRequest()->getParam("order_ids")) {

            $address_pickup = $this->config->getShippopConfig("address", "pickup");
            if (empty($address_pickup)) {
                $response = [
                    'status' => false,
                    'message' => __("Shipping address's incorrect. Please edit shipping address again")
                ];
                return $result->setData($response);
            }

            $order_ids = $this->getRequest()->getParam("order_ids");
            $response = $this->_utility->chooseCourier($order_ids);
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }
}
