<?php

namespace Shippop\Ecommerce\Controller\Adminhtml\Ecommerce;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

use Magento\Framework\Api\DataObjectHelper;
use Shippop\Ecommerce\Api\Data\OrderShippopInterface;
use Shippop\Ecommerce\Api\OrderShippopRepositoryInterface;

use Shippop\Ecommerce\Api\Data\OrderShippopInterfaceFactory;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;

class Index extends Action
{
    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var DataRepositoryInterface
     */
    protected $_dataRepository;

    /**
     * @var DataInterfaceFactory
     */
    protected $_dataFactory;

    protected $request;

    protected $_utility;
    protected $_coreSession;
    /**
     * Index constructor.
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param DataRepositoryInterface $dataRepository
     * @param DataInterfaceFactory $dataInterfaceFactory
     */
    public function __construct(
        Context $context,
        DataObjectHelper $dataObjectHelper,
        OrderShippopRepositoryInterface $dataRepository,
        OrderShippopInterfaceFactory $dataInterfaceFactory,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        Http $request,
        CoreSession $coreSession
    ) {
        parent::__construct($context);
        $this->_utility = $Utility;
        $this->_coreSession = $coreSession;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_dataRepository = $dataRepository;
        $this->_dataFactory = $dataInterfaceFactory;
        $this->request = $request;
    }

    public function execute()
    {
        return false;
    }
}
