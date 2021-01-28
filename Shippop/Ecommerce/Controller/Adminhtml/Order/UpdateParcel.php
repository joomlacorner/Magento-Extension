<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Order;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\SerializerInterface;

class UpdateParcel extends Action
{
    protected $request;
    protected $urlBuilder;
    protected $_utility;
    protected $_crud;
    protected $_response;
    protected $_serializerInterface;

    public function __construct(
        Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseInterface $response,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Shippop\Ecommerce\Helper\Crud $crud,
        Http $request,
        SerializerInterface $serializerInterface
    ) {
        parent::__construct($context);
        $this->_utility = $Utility;
        $this->request = $request;
        $this->urlBuilder = $urlBuilder;
        $this->_crud = $crud;
        $this->_response = $response;
        $this->_serializerInterface = $serializerInterface;
    }

    public function execute()
    {
        if ($this->getRequest()->getParam('_order_total_weight') &&
            $this->getRequest()->getParam('_order_width') &&
            $this->getRequest()->getParam('_order_length') &&
            $this->getRequest()->getParam('_order_height') &&
            $this->getRequest()->getParam('_order_id')) {
            $_order_total_weight = (float) $this->getRequest()->getParam('_order_total_weight');
            $_order_width = (float) $this->getRequest()->getParam('_order_width');
            $_order_length = (float) $this->getRequest()->getParam('_order_length');
            $_order_height = (float) $this->getRequest()->getParam('_order_height');
            $_order_id = (float) $this->getRequest()->getParam('_order_id');

            $order = $this->_crud->get_post_meta($_order_id, "extra");
            $extra = [];
            if (!empty($order)) {
                $extra = $this->_serializerInterface->unserialize($order);
            }
            $extra['weight'] = $_order_total_weight;
            $extra['width'] = $_order_width;
            $extra['length'] = $_order_length;
            $extra['height'] = $_order_height;

            $this->_crud->update_post_meta([
                'order_id' => $_order_id,
                'extra' => $extra
            ]);
            $this->messageManager->addSuccessMessage(__("Information updated"));
        }

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
