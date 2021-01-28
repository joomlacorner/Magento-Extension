<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class AddressCorrector extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;
    protected $_resourceConfig;
    protected $scopeConfig;
    protected $urlBuilder;
    protected $config;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Shippop\Ecommerce\Helper\Config $config
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $address_address = $this->getRequest()->getParam("address_address");
            $billing_address = $this->getRequest()->getParam("billing_address");
            
            $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
            $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");

            $address_address_suggestion = [
                'type' => 1,
                'status' => true,
                'suggestion' => [
                    [
                        "state" => "",
                        "district" => "",
                        "province" => "",
                        "postcode" => "",
                    ]
                ]
            ];

            $billing_address_suggestion = [
                'type' => 1,
                'status' => true
            ];

            if (!empty($shippop_bearer_key) && $shippop_server === "TH") {
                if (!empty($address_address)) {
                    $_address_address_suggestion = $this->_utility->addressCorrector($address_address);
                } else {
                    $_address_address_suggestion = $address_address_suggestion;
                }

                if (!empty($billing_address)) {
                    $_billing_address_suggestion = $this->_utility->addressCorrector($billing_address);
                } else {
                    $_billing_address_suggestion = $billing_address_suggestion;
                }

                $response = [
                    'address_address_suggestion' => $_address_address_suggestion,
                    'billing_address_suggestion' => $_billing_address_suggestion,
                    'status' => true,
                    'shippop_server' => $shippop_server,
                ];
            } else {
                $response = [
                    'address_address_suggestion' => $address_address_suggestion,
                    'billing_address_suggestion' => $billing_address_suggestion,
                    'status' => true,
                    'shippop_server' => $shippop_server,
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }
}
