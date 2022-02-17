<?php

declare(strict_types=1);

namespace WeProvide\Core\Service;

use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;

class CurrentProduct
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
     * @return Product|null
     */
    public function getCurrentProduct(): ?Product
    {
        return $this->registry->registry('current_product');
    }
}