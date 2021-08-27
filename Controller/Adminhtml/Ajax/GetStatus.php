<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class GetStatus extends \Magento\Backend\App\Action
{
    protected $config;
    protected $formKeyValidator;
    protected $resultJsonFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Shippop\Ecommerce\Helper\Config $config
    ) {
        $this->config = $config;
        $this->formKeyValidator = $formKeyValidator;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $response = [
            'status'    => false,
            'login'     => false,
            'cod'       => false
        ];
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $is_login = $this->config->getShippopConfig("auth", "is_login");
            $is_thailand = $this->config->getShippopConfig("auth", "is_thailand");
            $address_pickup = $this->config->getShippopConfig("address", "pickup");

            $_is_login = (bool) $is_login;
            $_is_thailand = (bool) $is_thailand;
            $response = [
                'status'    => true,
                'login'     => $_is_login,
                'cod'       => $_is_thailand,
                'address_pickup' => (isset($address_pickup)) ? true : false
                // 'login'     => $is_login,
                // 'cod'       => $is_thailand,
                // '_is_login' => $_is_login,
                // '_is_thailand' => $_is_thailand
            ];
        }
        return $result->setData($response);
    }
}
