<?php

namespace MailCampaigns\SubscriberApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for subscriber search results.
 * @api
 */
interface SubscriberSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get subscribers list.
     *
     * @return \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface[]
     */
    public function getItems();

    /**
     * Set subscribers list.
     *
     * @param \MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
