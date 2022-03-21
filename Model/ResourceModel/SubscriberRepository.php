<?php

namespace MailCampaigns\SubscriberApi\Model\ResourceModel;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Webapi\Exception;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\ResourceModel\Subscriber\Collection as SubscriberCollection;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Store\Model\StoreManagerInterface;
use MailCampaigns\SubscriberApi\Api\Data\SubscriberInterface;
use MailCampaigns\SubscriberApi\Api\Data\SubscriberSearchResultsInterfaceFactory;
use MailCampaigns\SubscriberApi\Api\SubscriberRepositoryInterface;
use MailCampaigns\SubscriberApi\Exception\MailCampaignsWebapiException;

/**
 * Subscriber repository.
 *
 * @api
 */
final class SubscriberRepository implements SubscriberRepositoryInterface
{
    /**
     * @var CollectionFactory
     */
    private $subscriberCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var SubscriberCollection
     */
    private $subscriberCollection;

    /**
     * @var SubscriberSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EmailValidator
     */
    private $emailValidator;

    /**
     * @param CollectionFactory $subscriberCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SubscriberSearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface|null $storeManager
     * @param EmailValidator $emailValidator
     */
    public function __construct(
        CollectionFactory                       $subscriberCollectionFactory,
        CollectionProcessorInterface            $collectionProcessor,
        SubscriberSearchResultsInterfaceFactory $searchResultsFactory,
        EmailValidator                          $emailValidator,
        StoreManagerInterface                   $storeManager = null

    )
    {
        $this->subscriberCollectionFactory = $subscriberCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->emailValidator = $emailValidator;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    public function create(SubscriberInterface $subscriber)
    {
        // Validate input.
        $this->validateRequiredFields($subscriber);
        $this->validateEmailAddressFormat($subscriber->getSubscriberEmail());

        $foundSubscribers = $this->getByEmailAddress($subscriber->getSubscriberEmail());

        if (count($foundSubscribers) > 0) {
            $this->throwException('Can not create: Subscriber with email address already exists!',
                Exception::HTTP_INTERNAL_ERROR
            );
        }

        // Is client allowed to create subscriber in given store?
        if ($this->getCurrentStoreId() !== 0 &&
            $subscriber->getStoreId() !== $this->getCurrentStoreId()) {
            $this->throwException('Not allowed to create subscriber in given store!',
                Exception::HTTP_FORBIDDEN);
        }

        // Check if store id exists.
        $this->validateStoreId($subscriber->getStoreId());

        /** @var Subscriber $newSubscriber */
        $newSubscriber = $this->getCollection()->getNewEmptyItem();

        // When the id is set to 0, it is accepted and 200 is returned, while it
        // actually won't create a new subscriber in the database, so unset it
        // in that case to let an auto increment value be generated instead.
        if ($subscriber->getSubscriberId() !== 0) {
            $newSubscriber->setData('subscriber_id', $subscriber->getSubscriberId());
        }

        $newSubscriber
            ->setData('store_id', $subscriber->getStoreId())
            ->setData('customer_id', $subscriber->getCustomerId())
            ->setData('subscriber_email', $subscriber->getSubscriberEmail())
            ->setData('subscriber_status', $subscriber->getSubscriberStatus())
            ->setData('subscriber_confirm_code', $subscriber->getSubscriberConfirmCode());

        try {
            $this->getCollection()->addItem($newSubscriber)->save();
        } catch (\Exception $e) {
            $this->throwException('Failed to create subscriber! ' . $e->getMessage(),
                Exception::HTTP_INTERNAL_ERROR);
        }

        return $subscriber->setSubscriberId($newSubscriber->getSubscriberId());
    }

    public function getById($subscriberId)
    {
        // Limit results to current store.
        $this->addStoreFilter($this->getCollection());

        /** @var SubscriberInterface $subscriber */
        $subscriber = $this->getCollection()->getItemById($subscriberId);

        $this->handleNotFound($subscriberId, $subscriber);

        return $subscriber;
    }

    public function getByEmailAddress(string $emailAddress)
    {
        // Limit results to current store.
        $this->addStoreFilter($this->getCollection());

        // Email address to look for should be valid.
        $this->validateEmailAddressFormat($emailAddress);

        return $this->getCollection()->getItemsByColumnValue('subscriber_email', $emailAddress);
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // Limit results to current store.
        $this->addStoreFilter($this->getCollection());

        $this->collectionProcessor->process($searchCriteria, $this->getCollection());
        $subscribers = $this->getCollection()->toArray();

        // Return the paginated results.
        return $this->searchResultsFactory->create()
            ->setItems($subscribers['items'])
            ->setSearchCriteria($searchCriteria)
            ->setTotalCount($subscribers['totalRecords']);
    }

    public function update(SubscriberInterface $subscriber)
    {
        // Validate input.
        $this->validateRequiredFields($subscriber);
        $this->validateEmailAddressFormat($subscriber->getSubscriberEmail());

        // Load the 'real' subscriber (not the one use for API requests/responses).
        $realSubscriber = $this->getById($subscriber->getSubscriberId());

        // Only attempt to update data if it differs.
        if (true === $this->hasDataChanges($realSubscriber, $subscriber)) {
            // When changed, make sure client is allowed to alter store id and
            // the store exists.
            if ($realSubscriber->getStoreId() !== $subscriber->getStoreId()) {
                // Is client allowed to change to the new id in current scope?
                if ($this->getCurrentStoreId() !== 0) {
                    $this->throwException('Not allowed to change store in current scope!',
                        Exception::HTTP_FORBIDDEN);
                }

                // Check if store id exists.
                $this->validateStoreId($subscriber->getStoreId());
            }

            // Make sure subscriber with same email address does not already exist.
            if ($subscriber->getSubscriberEmail() !== $realSubscriber->getSubscriberEmail()) {
                $foundSubscribers = $this->getByEmailAddress($subscriber->getSubscriberEmail());

                if (count($foundSubscribers) > 0) {
                    $this->throwException(
                        'Can not update: Subscriber with email address already exists!'
                    );
                }
            }

            $realSubscriber
                ->setStoreId($subscriber->getStoreId())
                ->setChangeStatusAt($subscriber->getChangeStatusAt())
                ->setCustomerId($subscriber->getCustomerId())
                ->setSubscriberEmail($subscriber->getSubscriberEmail())
                ->setSubscriberConfirmCode($subscriber->getSubscriberConfirmCode())
                ->setHasDataChanges(true);
        }

        // Only attempt to update status if it differs.
        if (true === $this->hasStatusChanged($realSubscriber, $subscriber)) {
            // Validate the given subscriber status value.
            $this->validateStatus($subscriber);

            // Update the subscriber's opt-in status.
            $realSubscriber
                ->setChangeStatusAt(date(DateTime::DATETIME_PHP_FORMAT))
                ->setSubscriberStatus($subscriber->getSubscriberStatus())
                ->setStatusChanged(true);
        }

        // Only save on changes.
        if ($realSubscriber->isStatusChanged() || $realSubscriber->hasDataChanges()) {
            $this->getCollection()->save();
        }

        // Return the reloaded subscriber.
        return $this->getById($subscriber->getSubscriberId());
    }

    public function deleteById($subscriberId)
    {
        // Load the subscriber model.
        $subscriber = $this->getById($subscriberId);

        try {
            // Subscriber exists, now remove it.
            $this->getResourceModel()->delete($subscriber);
        } catch (\Exception $e) {
            $this->throwException('Failed to delete subscriber! ' . $e->getMessage(),
                Exception::HTTP_INTERNAL_ERROR);
        }

        // Remove from collection as well.
        $this->getCollection()->removeItemByKey($subscriberId);

        return true;
    }

    /**
     * @throws MailCampaignsWebapiException
     */
    private function addStoreFilter(SubscriberCollection $collection): void
    {
        $currentStoreId = $this->getCurrentStoreId();

        if ($currentStoreId !== 0) {
            $collection->addFieldToFilter('store_id', ['eq' => $currentStoreId]);
        }
    }

    /**
     * @return int
     * @throws MailCampaignsWebapiException
     */
    private function getCurrentStoreId(): int
    {
        try {
            $store = $this->storeManager->getStore();
        } catch (NoSuchEntityException $e) {
            // This should never happen probably.
            $this->throwException('Failed to get store! ' . $e->getMessage(),
                Exception::HTTP_NOT_FOUND);
        }

        return $store->getId();
    }

    /**
     * Returns all available store ids.
     *
     * @return array
     */
    private function getAllStoreIds(): array
    {
        $storeIds = [];

        foreach ($this->storeManager->getStores(true) as $store) {
            $storeIds[] = (int)$store->getId();
        }

        return $storeIds;
    }

    /**
     * Lazily loads subscriber collection.
     *
     * @return SubscriberCollection
     */
    private function getCollection(): SubscriberCollection
    {
        if ($this->subscriberCollection === null) {
            $this->subscriberCollection = $this->subscriberCollectionFactory->create();
        }

        return $this->subscriberCollection;
    }

    /**
     * @return \Magento\Newsletter\Model\ResourceModel\Subscriber
     */
    private function getResourceModel(): \Magento\Newsletter\Model\ResourceModel\Subscriber
    {
        return ObjectManager::getInstance()->get(
            \Magento\Newsletter\Model\ResourceModel\Subscriber::class
        );
    }

    /**
     * @param Subscriber $realSubscriber
     * @param SubscriberInterface $subscriber
     * @return bool
     */
    private function hasDataChanges(Subscriber $realSubscriber, SubscriberInterface $subscriber): bool
    {
        return
            $realSubscriber->getSubscriberEmail() !== $subscriber->getSubscriberEmail() ||
            $realSubscriber->getCustomerId() !== $subscriber->getCustomerId() ||
            $realSubscriber->getStoreId() !== $subscriber->getStoreId() ||
            $realSubscriber->getSubscriberConfirmCode() !== $subscriber->getSubscriberConfirmCode() ||
            $realSubscriber->getChangeStatusAt() !== $subscriber->getChangeStatusAt();
    }

    /**
     * @param Subscriber $realSubscriber
     * @param SubscriberInterface $subscriber
     * @return bool
     */
    private function hasStatusChanged(Subscriber $realSubscriber, SubscriberInterface $subscriber): bool
    {
        // Note: int-casting is needed here to compare since Magento's method returns
        //       it as a string although the return type is supposed to be int.. :(
        /** @noinspection PhpCastIsUnnecessaryInspection */
        return (int)$realSubscriber->getSubscriberStatus() !== (int)$subscriber->getSubscriberStatus();
    }

    /**
     * @param SubscriberInterface $subscriber
     * @return void
     * @throws MailCampaignsWebapiException
     */
    private function validateRequiredFields(SubscriberInterface $subscriber): void
    {
        if ($subscriber->getSubscriberStatus() === null) {
            $this->throwException('Subscriber status can not be null!');
        }

        if ($subscriber->getCustomerId() === null) {
            $this->throwException('Customer id can not be null!');
        }
    }

    /**
     * @throws MailCampaignsWebapiException
     */
    private function validateEmailAddressFormat(string $emailAddress): void
    {
        if (false === $this->emailValidator->isValid($emailAddress)) {
            $this->throwException('Invalid email address!');
        }
    }

    /**
     * @throws MailCampaignsWebapiException
     */
    private function validateStoreId(int $storeId): void
    {
        if (false === in_array($storeId, $this->getAllStoreIds(), true)) {
            $this->throwException(sprintf('Store id `%d` does not exist!', $storeId));
        }
    }

    /**
     * @throws MailCampaignsWebapiException
     */
    private function validateStatus(SubscriberInterface $subscriber): void
    {
        if (false === $subscriber->isValidStatus($subscriber->getSubscriberStatus())) {
            $this->throwException('Invalid subscriber status!');
        }
    }

    /**
     * Throw an exception (which results in a 404 'Not Found' error) if the
     * subscriber could not be found (given subscriber is null).
     *
     * @param int $id Subscriber id.
     * @param object|null $subscriber
     *
     * @throws NoSuchEntityException
     */
    private function handleNotFound(int $id, $subscriber): void
    {
        if ($subscriber === null) {
            throw NoSuchEntityException::singleField('subscriberId', $id);
        }
    }

    /**
     * @param string $msg
     * @param int|null $httpCode Defaults to 400 `Bad Request`.
     * @return void
     * @throws MailCampaignsWebapiException
     */
    private function throwException(string $msg, int $httpCode = Exception::HTTP_BAD_REQUEST): void
    {
        throw new MailCampaignsWebapiException(new Phrase($msg), 0, $httpCode);
    }
}
