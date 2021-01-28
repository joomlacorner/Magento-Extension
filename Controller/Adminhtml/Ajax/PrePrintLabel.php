<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ajax;

use Shippop\Ecommerce\Api\OrderShippopRepositoryInterface;

class PrePrintLabel extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;
    protected $formKeyValidator;
    protected $_utility;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        OrderShippopRepositoryInterface $dataRepository
    ) {
        $this->_utility = $Utility;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $this->_dataRepository = $dataRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->formKeyValidator->validate($this->getRequest()) &&
            $this->getRequest()->getParam("label_size") &&
            $this->getRequest()->getParam("order_ids")) {
            $label_size = $this->getRequest()->getParam("label_size");
            $order_ids = $this->getRequest()->getParam("order_ids");
            $type = "html";

            $tracking_codes = [];
            foreach (explode(",", $order_ids) as $order_id) {
                $entity = $this->_dataRepository->getById($order_id);
                $tracking_codes[] = $entity->getTrackingCode();
            }

            $response = $this->_utility->labelPrinting($tracking_codes, $label_size, $type);
            $response['tracking_codes'] = $tracking_codes;
        } else {
            $response = [
                'status' => false,
                'message' => __("Access Denied")
            ];
        }

        return $result->setData($response);
    }
}
