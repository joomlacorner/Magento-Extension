<?php

namespace Shippop\Ecommerce\Block;

class TrackingHistory extends \Magento\Framework\View\Element\Template
{
    protected $_utility;
    protected $_coreSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Shippop\Ecommerce\Helper\Utility $Utility,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct($context);
        $this->_coreSession = $coreSession;
        $this->_utility = $Utility;
    }
}
