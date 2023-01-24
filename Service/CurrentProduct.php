<?php

declare(strict_types=1);

namespace WeProvide\Core\Service;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;

class CurrentProduct
{
    /** @var string */
    protected const CURRENT_PRODUCT = 'current_product';

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
     * Gets the current product
     *
     * @return ProductInterface|null
     */
    public function getCurrentProduct(): ?ProductInterface
    {
        return $this->registry->registry(static::CURRENT_PRODUCT);
    }

    /**
     * Sets the current product
     *
     * @param ProductInterface|null $product
     * @return CurrentProduct
     */
    public function setCurrentProduct(?ProductInterface $product): CurrentProduct
    {
        $this->registry->register(static::CURRENT_PRODUCT, $product);

        return $this;
    }
}
