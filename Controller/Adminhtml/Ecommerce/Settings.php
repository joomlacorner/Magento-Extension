<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Api\DataObjectHelper;

use Shippop\Ecommerce\Helper\Config;

class Settings extends Action
{
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var DataInterfaceFactory
     */
    protected $_dataFactory;

    protected $request;

    protected $_utility;
    protected $_coreSession;
    protected $urlBuilder;
    protected $config;

    /**
     * Index constructor.
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param DataRepositoryInterface $dataRepository
     * @param DataInterfaceFactory $dataInterfaceFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\UrlInterface $urlBuilder,
        Config $config
    ) {
        parent::__construct($context);
        $this->urlBuilder = $urlBuilder;
        $this->config = $config;
    }

    public function execute()
    {
        $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");
        if (!isset($shippop_bearer_key) && empty($shippop_bearer_key)) {
            $redirect = $this->urlBuilder->getUrl("shippop/ecommerce/loginregister");
            $this->_redirect($redirect);
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->set(__("Settings"));
        $this->_view->renderLayout();
    }
}
