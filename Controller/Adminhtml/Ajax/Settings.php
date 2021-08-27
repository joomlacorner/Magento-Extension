<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

use Magento\Framework\Serialize\SerializerInterface;

class Settings extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;
    protected $_resourceConfig;
    protected $scopeConfig;
    protected $urlBuilder;
    protected $_messageManager;
    protected $_serializerInterface;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        SerializerInterface $serializerInterface
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->urlBuilder = $urlBuilder;
        $this->_messageManager = $messageManager;
        $this->_serializerInterface = $serializerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $request_fields = [
            "address_name",
            "address_tel",
            "address_address",
            "address_state",
            "address_district",
            "address_province",
            "address_postcode"
        ];
        if ($this->formKeyValidator->validate($this->getRequest()) &&
            $this->validate_request($request_fields)) {
            $pickup_address = [
                "address" => $this->getRequest()->getParam("address_address"),
                "state" => $this->getRequest()->getParam("address_state"),
                "district" => $this->getRequest()->getParam("address_district"),
                "province" => $this->getRequest()->getParam("address_province"),
                "postcode" => $this->getRequest()->getParam("address_postcode"),
                "address_tel" => $this->getRequest()->getParam("address_tel"),
                "address_name" => $this->getRequest()->getParam("address_name")
            ];
            $this->_utility->setShippopConfig(
                "address",
                "pickup",
                $this->_serializerInterface->serialize($pickup_address)
            );

            // if ($this->getRequest()->getParam("billing_name_title")) {
            //     $name_title = $this->getRequest()->getParam("billing_name_title");
            // } else {
            //     $name_title = "";
            // }
            // $billing_update = [
            //     "phone" => $this->getRequest()->getParam("billing_tel"),
            //     "address" => $this->getRequest()->getParam("billing_address"),
            //     "tax_id" => $this->getRequest()->getParam("billing_tax_id"),
            //     "name" => $this->getRequest()->getParam("billing_name"),
            //     "name_title" => $name_title
            // ];
            // $response = $this->_utility->updateBilling($billing_update);
            // if (!empty($response) && $response["status"]) {
            //     $this->_messageManager->addSuccessMessage(__("Information updated"));
            // } else {
            //     $this->_messageManager->addErrorMessage(__("Information can't update") . " " . $response["message"]);
            // }
            $response = [
                'status' => true,
            ];
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }

    public function validate_request($fields)
    {
        foreach ($fields as $field) {
            if (!in_array($field, $this->getRequest()->getParams()) && !$this->getRequest()->getParam($field)) {
                return false;
            }
        }
        return true;
    }
}
