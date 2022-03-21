<?php

namespace MailCampaigns\SubscriberApi\Api\Data;

use Magento\Framework\ObjectManagerInterface;

/**
 * Factory class for @see \MailCampaigns\SubscriberApi\Api\Data\SubscriberSearchResultsInterface
 */
class SubscriberSearchResultsInterfaceFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        $instanceName = SubscriberSearchResultsInterface::class
    )
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberSearchResultsInterface
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
