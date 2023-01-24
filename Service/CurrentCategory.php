<?php

declare(strict_types=1);

namespace WeProvide\Core\Service;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Registry;

class CurrentCategory
{
    /** @var string */
    protected const CURRENT_CATEGORY = 'current_category';

    /** @var Registry */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Gets the current category
     *
     * @return CategoryInterface|null
     */
    public function getCurrentCategory(): ?CategoryInterface
    {
        return $this->registry->registry(static::CURRENT_CATEGORY);
    }

    /**
     * Sets the current category
     *
     * @param CategoryInterface|null $category
     * @return CurrentCategory
     */
    public function setCurrentCategory(?CategoryInterface $category): CurrentCategory
    {
        $this->registry->register(static::CURRENT_CATEGORY, $category);

        return $this;
    }
}
