<?php

namespace Shippop\Ecommerce\Block;

use Shippop\Ecommerce\Helper\Config;
use Shippop\Ecommerce\Helper\ShippopApi;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * @api
 */
class Settings extends \Magento\Framework\View\Element\Template
{
    protected $_assetRepo;
    protected $config;
    protected $ShippopAPI;
    protected $urlBuilder;
    protected $_serializerInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        Config $config,
        ShippopApi $shippopApi,
        \Magento\Framework\UrlInterface $urlBuilder,
        SerializerInterface $serializerInterface
    ) {
        $this->_assetRepo = $assetRepo;
        $this->config = $config;
        $this->ShippopAPI = $shippopApi;
        $this->urlBuilder = $urlBuilder;
        $this->_serializerInterface = $serializerInterface;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        $shippop_auth_email = $this->config->getShippopConfig("auth", "shippop_auth_email");
        return $shippop_auth_email;
    }

    /**
     * @return string
     */
    public function getServer()
    {
        $shippop_server = $this->config->getShippopConfig("auth", "shippop_server");
        return $shippop_server;
    }

    /**
     * @return array
     */
    public function getPickup()
    {
        $address = $this->config->getShippopConfig("address", "pickup");
        return (!empty($address)) ? $this->_serializerInterface->unserialize($address) : [];
    }

    /**
     * @return array
     */
    public function getMember()
    {
        $member = $this->ShippopAPI->member();
        return $member;
    }

    /**
     * @return string
     */
    public function logoutUrl()
    {
        return $this->urlBuilder->getUrl("shippop/ecommerce/logout");
    }
}
