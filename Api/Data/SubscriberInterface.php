<?php

namespace MailCampaigns\SubscriberApi\Api\Data;

use Magento\Newsletter\Model\Subscriber;

/**
 * @api
 */
interface SubscriberInterface
{
    /** @var int[] A list of valid status values. */
    public const STATUSES = [
        Subscriber::STATUS_SUBSCRIBED,
        Subscriber::STATUS_NOT_ACTIVE,
        Subscriber::STATUS_UNSUBSCRIBED,
        Subscriber::STATUS_UNCONFIRMED,
    ];

    /**
     * @return int|null
     */
    public function getSubscriberId(): ?int;

    /**
     * @param int $value
     * @return $this
     */
    public function setSubscriberId(int $value);

    /**
     * @return int|null
     */
    public function getStoreId(): ?int;

    /**
     * @param int $value
     * @return $this
     */
    public function setStoreId(int $value);

    /**
     * @return string|null
     */
    public function getChangeStatusAt(): ?string;

    /**
     * @param string|null $value
     * @return $this
     */
    public function setChangeStatusAt(?string $value);


    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param int|null $value
     * @return $this
     */
    public function setCustomerId(?int $value);

    /**
     * @return string|null
     */
    public function getSubscriberEmail(): ?string;

    /**
     * @param string|null $value
     * @return $this
     */
    public function setSubscriberEmail(?string $value);

    /**
     * @return int
     */
    public function getSubscriberStatus();

    /**
     * @param int $value
     * @return $this
     */
    public function setSubscriberStatus(int $value);

    /**
     * Returns whether or not the given status is a valid one.
     *
     * @param int $value
     * @return bool
     */
    public function isValidStatus(int $value): bool;

    /**
     * @return string|null
     */
    public function getSubscriberConfirmCode(): ?string;

    /**
     * @param string|null $value
     * @return \MailCampaigns\SubscriberApi\Model\Api\Subscriber
     */
    public function setSubscriberConfirmCode(?string $value);
}
