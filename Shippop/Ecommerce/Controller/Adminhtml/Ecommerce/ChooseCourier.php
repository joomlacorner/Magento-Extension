<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Shippop\Ecommerce\Helper\Config;

class ChooseCourier extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $config;
    protected $urlBuilder;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        Config $config
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context);
    }

    public function execute()
    {
        $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");
        if (empty($shippop_bearer_key)) {
            $redirect = $this->urlBuilder->getUrl("shippop/ecommerce/loginregister");
            $this->_redirect($redirect);
        }
        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Choose courier')));

        return $resultPage;
    }
}
