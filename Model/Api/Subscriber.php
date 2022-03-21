<?php /** @noinspection SenselessProxyMethodInspection */

namespace MailCampaigns\SubscriberApi\Model\Api;

use MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface;

class Subscriber extends \Magento\Newsletter\Model\Subscriber implements SubscriberInterface
{
    /**
     * @return int|null
     */
    public function getSubscriberId(): ?int
    {
        return parent::getSubscriberId();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setSubscriberId(int $value)
    {
        return parent::setSubscriberId($value);
    }

    /**
     * @return int|null
     */
    public function getStoreId(): ?int
    {
        return parent::getStoreId();
    }

    /**
     * @param int|null $value
     * @return $this
     */
    public function setStoreId(?int $value)
    {
        return parent::setStoreId($value);
    }

    /**
     * @return string|null
     */
    public function getChangeStatusAt(): ?string
    {
        return parent::getChangeStatusAt();
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setChangeStatusAt(?string $value)
    {
        return parent::setChangeStatusAt($value);
    }

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int
    {
        return parent::getCustomerId();
    }

    /**
     * @param int|null $value
     * @return Subscriber
     */
    public function setCustomerId(?int $value)
    {
        return parent::setCustomerId($value);
    }

    /**
     * @return string|null
     */
    public function getSubscriberEmail(): ?string
    {
        return parent::getSubscriberEmail();
    }

    /**
     * @param string|null $value
     * @return $this
     */
    public function setSubscriberEmail(?string $value)
    {
        return parent::setSubscriberEmail($value);
    }

    /**
     * @return int
     */
    public function getSubscriberStatus()
    {
        return parent::getSubscriberStatus();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setSubscriberStatus(int $value)
    {
        return parent::setSubscriberStatus($value);
    }

    /**
     * @param int $value
     * @return bool
     */
    public function isValidStatus(int $value): bool
    {
        return in_array($value, self::STATUSES, true);
    }

    /**
     * @return string|null
     */
    public function getSubscriberConfirmCode(): ?string
    {
        return parent::getSubscriberConfirmCode();
    }

    /**
     * @param string|null $value
     * @return Subscriber
     */
    public function setSubscriberConfirmCode(?string $value)
    {
        return parent::setSubscriberConfirmCode($value);
    }
}
