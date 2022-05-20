<?php

declare(strict_types=1);

namespace WeProvide\Core\Model\ResourceModel;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class can be used for collection for entities which have a store view link
 */
abstract class AbstractStoreLinkedCollection extends AbstractCollection
{
    /** @var string */
    protected const STORE_LINK_TABLE_ALIAS = 'store_link_table';

    /** @var string */
    protected const STORE_LINK_TABLE_STORE_ID_ALIAS = 'store_id';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface       $eventManager,
        StoreManagerInterface  $storeManager,
        MetadataPool           $metadataPool,
        AdapterInterface       $connection = null,
        AbstractDb             $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues($this->getIdFieldName());
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select     = $connection->select()
                ->from([static::STORE_LINK_TABLE_ALIAS => $this->getTable($tableName)])
                ->where(static::STORE_LINK_TABLE_ALIAS . '.' . $linkField . ' IN (?)', $linkedIds);

            $result     = $connection->fetchAll($select);

            $storesData = [];
            foreach ($result as $storeData) {
                $storesData[$storeData[$linkField]][] = $storeData[static::STORE_LINK_TABLE_STORE_ID_ALIAS];
            }

            foreach ($this as $item) {
                $linkedId = $item->getData($this->getIdFieldName());
                $storeIds = $storesData[$linkedId] ?? [Store::DEFAULT_STORE_ID];

                $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storeIds, true);
                if ($storeIdKey !== false) {
                    $stores    = $this->storeManager->getStores(false, true);
                    $storeId   = current($stores)->getId();
                    $storeCode = key($stores);
                } else {
                    $storeId   = current($storeIds);
                    $storeCode = $this->storeManager->getStore($storeId)->getCode();
                }
                $item->setData('_first_store_id', $storeId);
                $item->setData('store_code', $storeCode);
                $item->setData('store_id', $storeIds);
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }

        return $this;
    }

    /**
     * Perform adding filter by store
     *
     * @param int|array|Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $conditions = [['in' => $store]];

        // When results when the default store are retrieved, also search for where no store was set yet
        if (in_array(Store::DEFAULT_STORE_ID, $store)) {
            $conditions[] = ['null' => true];
        }

        $this->addFilter('store', $conditions, 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $linkField)
    {
        if ($this->getFilter('store')) {
            $this->getSelect()->joinLeft(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $this->getIdFieldName() . ' = store_table.' . $linkField,
                []
            )->group(
                'main_table.' . $this->getIdFieldName()
            );
        }
    }
}
