<?php

declare(strict_types=1);

namespace WeProvide\Core\Model\ResourceModel;

use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * This class can be used for entities which have a store view link
 */
abstract class AbstractStoreLinkedResource extends AbstractDb
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /** @var string */
    protected $storeLinkMainTable = null;

    /** @var string */
    protected $storeLinkLinkTable = null;

    /** @var string */
    protected $metadataClassName = null;

    /** @var string */
    protected $storeLinkIdFieldName = 'store_id';

    /**
     * @param Context $context
     * @param EntityManager $entityManager
     * @param null $resourcePrefix
     */
    public function __construct(
        Context $context,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->entityManager = $entityManager;
        $this->metadataPool  = $metadataPool;
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init($this->storeLinkMainTable, $this->_idFieldName);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata($this->metadataClassName);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['store_link' => $this->getTable($this->storeLinkLinkTable)], $this->storeLinkIdFieldName)
            ->join(
                ['main_table' => $this->getMainTable()],
                'store_link.' . $linkField . ' = main_table.' . $linkField,
                []
            )
            ->where('main_table.' . $entityMetadata->getIdentifierField() . ' = :entity_id');

        return $connection->fetchCol($select, ['entity_id' => (int)$id]);
    }

    /**
     * @param AbstractModel $object
     * @return $this|AbstractStoreLinkedResource
     */
    public function save(AbstractModel $object)
    {
        $this->entityManager->save($object);
        return $this;
    }
}
