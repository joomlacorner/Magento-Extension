<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

class TrackingHistory extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Shippop\Ecommerce\Helper\Utility $Utility
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->formKeyValidator->validate($this->getRequest()) && $this->getRequest()->getParam("tracking_code")) {
            $tracking_code = $this->getRequest()->getParam("tracking_code");
            $response = $this->_utility->trackingHistory($tracking_code);
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }
}
