<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;
use Shippop\Ecommerce\Helper\Config;

class CancelPurchase extends Action
{
    protected $_utility;
    protected $_messageManager;
    protected $formKeyValidator;
    protected $urlBuilder;

    /**
     * Index constructor.
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param DataRepositoryInterface $dataRepository
     * @param DataInterfaceFactory $dataInterfaceFactory
     */
    public function __construct(
        Context $context,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        Config $config
    ) {
        parent::__construct($context);
        $this->_utility = $Utility;
        $this->_messageManager = $messageManager;
        $this->urlBuilder = $urlBuilder;
        $this->formKeyValidator = $formKeyValidator;
        $this->config = $config;
    }

    public function execute()
    {
        $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");
        if (empty($shippop_bearer_key)) {
            $redirect = $this->urlBuilder->getUrl("shippop/ecommerce/loginregister");
            $this->_redirect($redirect);
        }
        
        if (!empty($this->getRequest()->getParam("tracking_code"))) {
            $tracking_code = $this->getRequest()->getParam("tracking_code");
            $response = $this->_utility->cancelPurchase($tracking_code);
            if ($response["status"]) {
                $this->_messageManager->addSuccessMessage(__("Success"));
            } else {
                $this->_messageManager->addNoticeMessage(__("Notice") . " " . $response["message"]);
            }
        } else {
            $this->_messageManager->addErrorMessage(__("Error"));
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
