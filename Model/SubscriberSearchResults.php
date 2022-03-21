<?php

declare(strict_types=1);

namespace MailCampaigns\SubscriberApi\Model;

use Magento\Framework\Api\SearchResults;
use MailCampaigns\SubscriberApi\Api\Data\SubscriberSearchResultsInterface;

/**
 * Service Data Object with Subscriber search results.
 */
class SubscriberSearchResults extends SearchResults implements SubscriberSearchResultsInterface
{
}
