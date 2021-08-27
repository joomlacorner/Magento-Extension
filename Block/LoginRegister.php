<?php

namespace Shippop\Ecommerce\Block;

class LoginRegister extends \Magento\Framework\View\Element\Template
{
    protected $_assetRepo;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo
    ) {
        $this->_assetRepo = $assetRepo;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function shippop_logo_register()
    {
        return $this->_assetRepo->getUrl("Shippop_Ecommerce::images/logistic_logo/SPE.png");
    }
}
