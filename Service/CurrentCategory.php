<?php

declare(strict_types=1);

namespace WeProvide\Core\Service;

use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;

class CurrentCategory
{
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
     * @return Category|null
     */
    public function getCurrentCategory(): ?Category
    {
        return $this->registry->registry('current_category');
    }
}