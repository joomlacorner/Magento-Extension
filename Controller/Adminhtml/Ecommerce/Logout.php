<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Framework\Api\DataObjectHelper;

use Magento\Framework\App\Config\Storage\WriterInterface;

use Shippop\Ecommerce\Helper\Config;

class Logout extends Action
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
        \Shippop\Ecommerce\Helper\Utility $Utility,
        Config $config
    ) {
        parent::__construct($context);
        $this->urlBuilder = $urlBuilder;
        $this->_utility = $Utility;
        $this->config = $config;
    }

    public function execute()
    {
        $group_code = [
            "address/shippop_fullname",
            "address/shippop_telephone",
            "billing_address/shippop_fullname",
            "billing_address/shippop_telephone",
            "billing_address/shippop_address",
            "auth/shippop_server",
            "address/pickup",
            "auth/shippop_auth_email",
            "auth/shippop_bearer_key",
            "auth/is_thailand"
        ];
        $shippop_bearer_key = $this->config->getShippopConfig("auth", "shippop_bearer_key");
        if (isset($shippop_bearer_key)) {
            foreach ($group_code as $path) {
                $this->_utility->deleteShippopConfig($path);
            }

            $redirect = $this->urlBuilder->getUrl("shippop/ecommerce/loginregister");
            $this->_redirect($redirect);
        }
    }
}
