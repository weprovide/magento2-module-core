<?php

declare(strict_types=1);

namespace WeProvide\Core\Model\ResourceModel;

use Magento\Framework\EntityManager\Operation\ExtensionInterface;
use Magento\Framework\Exception\LocalizedException;
use WeProvide\Core\Model\ResourceModel\AbstractStoreLinkedResource;

/**
 * This class can be used for saveHandlers for entities which have a store view link
 * Do keep in mind that the right resourceModel should be passed as argument.
 *
 * Note: This class is actually not abstract to make it possible create a virtual type of this class.
 */
class AbstractStoreLinkedSaveHandler implements ExtensionInterface
{
    /**
     * @var AbstractStoreLinkedResource
     */
    protected $resourceModel;

    /**
     * @param AbstractStoreLinkedResource $resourcePage
     */
    public function __construct(
        AbstractStoreLinkedResource $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
    }

    /**
     * @param $entity
     * @param $arguments
     * @return bool|object
     * @throws LocalizedException
     */
    public function execute($entity, $arguments = [])
    {
        $connection = $this->resourceModel->getConnection();

        $entityId = (int) $entity->getId();

        $oldStores = $this->resourceModel->lookupStoreIds((int)$entity->getId());
        $newStores = (array)$entity->getStoreId();

        $table = $this->resourceModel->getTable($this->resourceModel->getStoreLinkLinkTable());

        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = [
                sprintf('%s = ?', $this->resourceModel->getStoreLinkLinkTableEntityIdField()) => $entityId,
                sprintf('%s IN (?)', $this->resourceModel->getStoreLinkLinkTableStoreIdField())  => $delete,
            ];
            $connection->delete($table, $where);
        }

        $insert = array_diff($newStores, $oldStores);
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = [
                    $this->resourceModel->getStoreLinkLinkTableEntityIdField() => $entityId,
                    $this->resourceModel->getStoreLinkLinkTableStoreIdField() => (int)$storeId
                ];
            }
            $connection->insertMultiple($table, $data);
        }

        return $entity;
    }
}
