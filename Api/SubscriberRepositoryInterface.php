<?php

namespace MailCampaigns\SubscriberApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface;
use MailCampaigns\SubscriberApi\Exception\MailCampaignsWebapiException;

/**
 * Subscriber CRUD interface.
 * @api
 */
interface SubscriberRepositoryInterface
{
    /**
     * Create a new subscriber.
     *
     * @param \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface $subscriber
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface
     * @throws MailCampaignsWebapiException
     */
    public function create(SubscriberInterface $subscriber);

    /**
     * Get subscriber by Subscriber ID.
     *
     * @param int $subscriberId
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface
     * @throws NoSuchEntityException
     * @throws MailCampaignsWebapiException
     */
    public function getById($subscriberId);

    /**
     * Get subscribers by email address.
     *
     * @param string $emailAddress
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface[]
     * @throws MailCampaignsWebapiException
     */
    public function getByEmailAddress(string $emailAddress);

    /**
     * Retrieve subscribers which match a specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberSearchResultsInterface
     * @throws MailCampaignsWebapiException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Update a subscriber.
     *
     * @param \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface $subscriber
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface
     * @throws MailCampaignsWebapiException
     * @throws NoSuchEntityException
     */
    public function update(SubscriberInterface $subscriber);

    /**
     * Delete subscriber by Susbcriber ID.
     *
     * @param int $subscriberId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws MailCampaignsWebapiException
     */
    public function deleteById($subscriberId);
}
