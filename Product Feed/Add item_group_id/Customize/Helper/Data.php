<?php

namespace Mageplaza\Customize\Helper;

use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;

class Data extends CoreHelper
{
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManager,
        ProductRepository $productRepository,

    ){
        $this->productFactory            = $productFactory;
        $this->productRepository         = $productRepository;

        parent::__construct($context, $objectManager, $storeManager);
    }
    public function getProductsData($feed, $productAttributes = [], $productIds = [])
    {
        $storeId     = $feed->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();
        $matchingProductIds = !empty($productIds) ? $productIds : $feed->getMatchingProductIds();

        $productCollection = $this->productFactory->create()->getCollection()
            ->addAttributeToSelect($productAttributes)->addStoreFilter($storeId)
            ->addFieldToFilter('entity_id', ['in' => $matchingProductIds])->addMediaGalleryData();
        foreach ($productCollection as $product) {

            try {
                $oriProduct = $this->productRepository->getById($product->getId(), false, $storeId);
            } catch (Exception $e) {
                $oriProduct = $this->productFactory->create()->setStoreId($storeId)->load($product->getId());
            }

            $configurable = $this->createObject(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::class);
            $parentConfigObject = $configurable->getParentIdsByChild($oriProduct->getEntityId());
            $itemGroupId = '';
            if (count($parentConfigObject)) {
                $parentProduct = $this->productRepository->getById($parentConfigObject[0], false, $storeId);
                $itemGroupId   = $parentProduct->getSku();
            }
            if ($itemGroupId) {
                $product->setData('item_group_id', $itemGroupId);
            }
        }
    }
}
