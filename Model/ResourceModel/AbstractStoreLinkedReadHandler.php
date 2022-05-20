<?php

declare(strict_types=1);

namespace WeProvide\Core\Model\ResourceModel;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * This class can be used for readHandlers for entities which have a store view link
 * Do keep in mind that the right resourceModel should be passed as argument.
 *
 * Note: This class is actually not abstract to make it possible create a virtual type of this class.
 */
class AbstractStoreLinkedReadHandler implements ExtensionInterface
{
    /**
     * @var AbstractStoreLinkedResource
     */
    protected $resourceModel;

    /**
     * @param AbstractStoreLinkedResource $resourceModel
     */
    public function __construct(
        AbstractStoreLinkedResource $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceModel->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
