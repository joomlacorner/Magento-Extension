<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class LoginRegister extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;
    protected $_resourceConfig;
    protected $scopeConfig;
    protected $urlBuilder;
    protected $_messageManager;
    protected $_clearcache;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Shippop\Ecommerce\Helper\ClearCache $clearcache
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->urlBuilder = $urlBuilder;
        $this->_messageManager = $messageManager;
        $this->_clearcache = $clearcache;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->getParam("shippop_method") &&
            $this->getRequest()->getParam("shippop_method") == "LOGIN") {
            if ($this->formKeyValidator->validate($this->getRequest()) &&
                $this->getRequest()->getParam("shippop_email") &&
                $this->getRequest()->getParam("shippop_password") &&
                $this->getRequest()->getParam("shippop_server")) {
                $shippop_server = ($this->getRequest()->getParam("shippop_server") == "MY") ? "MY" : "TH";
                $this->_utility->setShippopConfig("auth", "shippop_server", $shippop_server);
                $this->_utility->setShippopConfig("auth", "is_thailand", ($shippop_server == "TH") ? "1" : "0");
    
                $shippop_email = $this->getRequest()->getParam("shippop_email");
                $shippop_password = $this->getRequest()->getParam("shippop_password");
                $shippop_email = trim($shippop_email);
                $shippop_password = trim($shippop_password);

                $response = $this->_utility->authShippop($shippop_email, $shippop_password, $shippop_server);
                if ($response["status"]) {
                    $this->_utility->setShippopConfig("auth", "shippop_bearer_key", $response["data"]["token"]);
                    $this->_utility->setShippopConfig("auth", "shippop_auth_email", $shippop_email);
                    $this->_utility->setShippopConfig("auth", "is_login", "1");
                    $response['redirect_url'] = $this->urlBuilder->getUrl("shippop/ecommerce/settings");
                } else {
                    $response['message'] = __("Can’t connect. Please try again later") . " [ " . __($response['message']) . " ] ";
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => __("Access Denied")
                ];
            }
        } elseif ($this->getRequest()->getParam("shippop_method") &&
                    $this->getRequest()->getParam("shippop_method") == "REGISTER") {
            if ($this->formKeyValidator->validate($this->getRequest()) &&
                $this->getRequest()->getParam("shippop_name") &&
                $this->getRequest()->getParam("shippop_tel") &&
                $this->getRequest()->getParam("shippop_email") &&
                $this->getRequest()->getParam("shippop_courier") &&
                $this->getRequest()->getParam("shippop_server")) {
                $shippop_server = ($this->getRequest()->getParam("shippop_server") == "MY") ? "MY" : "TH";
    
                $shippop_company = $this->getRequest()->getParam("shippop_company");
                $shippop_name = $this->getRequest()->getParam("shippop_name");
                $shippop_tel = $this->getRequest()->getParam("shippop_tel");
                $shippop_email = $this->getRequest()->getParam("shippop_email");
                $shippop_courier = $this->getRequest()->getParam("shippop_courier");
                $response = $this->_utility->registerShippop(
                    $shippop_company,
                    $shippop_name,
                    $shippop_tel,
                    $shippop_email,
                    $shippop_courier,
                    $shippop_server
                );

                if ($response["status"]) {
                    $message = "";
                    $message .= "<h3 style='text-align: center;'>";
                    $message .= __("Thank you for your interest in SHIPPOP service. We already received your information");
                    $message .= "</h3>";
                    $response["message"] = $message;

                    $message2 = "";
                    $message2 .= "<h4 style='text-align: center;'>";
                    $message2 .= __("Our team will contact you within 1-2 business days");
                    $message2 .= "</h4>";
                    $response["message2"] = $message2;
                } else {
                    foreach ($response['message']['form_error'] as $key => $msg) {
                        $response['message'] = $key . " : " . $msg;
                        break;
                    }
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => __("Access Denied")
                ];
            }
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        $this->_clearcache->flushCache();
        return $result->setData($response);
    }
}
