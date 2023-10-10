<?php

namespace Mageplaza\Customize\Plugin\ProductFeed\Helper;

use Exception;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Url as UrlAbstract;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Mageplaza\ProductFeed\Helper\Data as HelperData;
use Mageplaza\ProductFeed\Model\Feed;

class Data extends CoreHelper
{
    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var StockRegistryInterface
     */
    protected $stockState;

    /**
     * @var CatalogHelper
     */
    protected $catalogHelper;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $prdAttrCollectionFactory;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        ProductCollection $productCollection,
        ProductRepository $productRepository,
        ProductFactory $productFactory,
        StockRegistryInterface $stockState,
        CatalogHelper $catalogHelper,
        CategoryCollectionFactory $categoryCollectionFactory,
        CollectionFactory $prdAttrCollectionFactory
    ) {
        $this->productCollection         = $productCollection;
        $this->productRepository         = $productRepository;
        $this->productFactory            = $productFactory;
        $this->stockState                = $stockState;
        $this->catalogHelper             = $catalogHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->prdAttrCollectionFactory  = $prdAttrCollectionFactory;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param HelperData $subject
     * @param callable $proceed
     * @param Feed $feed
     * @param array $productAttributes
     * @param array $productIds
     * @param bool $isSync
     * @return array
     * @throws LocalizedException
     */
    public function aroundGetProductsData($subject, callable $proceed, $feed, $productAttributes = [], $productIds = [], $isSync = false)
    {
        $categoryMap = $this->unserialize($feed->getCategoryMap());
        $storeId     = $feed->getStoreId() ?: $this->storeManager->getDefaultStoreView()->getId();

        $allCategory = $this->categoryCollectionFactory->create();
        $allCategory->setStoreId($storeId)->addAttributeToSelect('name');
        $categoriesName = [];
        /** @var $item Category */
        foreach ($allCategory as $item) {
            $categoriesName[$item->getId()] = $item->getName();
        }

        $allSelectProductAttributes = $this->prdAttrCollectionFactory->create()
            ->addFieldToFilter('frontend_input', ['in' => ['multiselect', 'select']])
            ->getColumnValues('attribute_code');
        $matchingProductIds = !empty($productIds) ? $productIds : $feed->getMatchingProductIds();
        if ($isSync) {
            $productCollection = $this->productCollection->create()->addUrlRewrite()
                ->addAttributeToSelect('*')->addStoreFilter($storeId)
                ->addFieldToFilter('entity_id', ['in' => $matchingProductIds])->addMediaGalleryData();
        } else {
            $productCollection = $this->productCollection->create()->addUrlRewrite()
                ->addAttributeToSelect($productAttributes)->addStoreFilter($storeId)
                ->addFieldToFilter('entity_id', ['in' => $matchingProductIds])->addMediaGalleryData();
        }

        $objectManager = ObjectManager::getInstance();
        $result = [];
        /** @var $product Product */
        foreach ($productCollection as $product) {
            $typeInstance           = $product->getTypeInstance();
            $childProductCollection = $typeInstance->getAssociatedProducts($product);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            if ($childProductCollection) {
                $associatedData = [];
                foreach ($childProductCollection as $item) {
                    $associatedData = $item->getData();
                }
                $product->setAssociatedProducts($associatedData);
            } else {
                $product->setAssociatedProducts([]);
            }
            $stockItem       = $this->stockState->getStockItem(
                $product->getId(),
                $feed->getStoreId()
            );
            $qty             = $stockItem->getQty();
            $product->setData('qty', $qty);
            $categories      = $product->getCategoryCollection()->addAttributeToSelect('*');
            $relatedProducts = [];
            foreach ($product->getRelatedProducts() as $item) {
                $relatedProducts[] = $item->getData();
            }
            $crossSellProducts = [];
            foreach ($product->getCrossSellProducts() as $item) {
                $crossSellProducts[] = $item->getData();
            }
            $upSellProducts = [];
            foreach ($product->getUpSellProducts() as $item) {
                $upSellProducts[] = $item->getData();
            }
            try {
                $oriProduct = $this->productRepository->getById($product->getId(), false, $storeId);
            } catch (Exception $e) {
                $oriProduct = $this->productFactory->create()->setStoreId($storeId)->load($product->getId());
            }
            if ($oriProduct->getResource()->getAttribute('material')) {
                $material = $oriProduct->getAttributeText('material');
                if (is_array($material)) {
                    $material = implode('/', $material);
                }
                $product->setData('material', $material);
            }
            $finalPrice = $subject->getProductPrice($oriProduct);
            $finalPrice = $this->catalogHelper->getTaxPrice($oriProduct, $finalPrice, true);
            $finalPrice = $subject->convertPrice($finalPrice, $storeId);

            $productLink = $subject->getProductUrl($product, $storeId);
            if ($this->getConfigGeneral('reports')) {
                $productLink .= $this->getCampaignUrl($feed);
            }
            $imageLink = $oriProduct->getImage() ? $this->storeManager->getStore($storeId)
                    ->getBaseUrl(UrlAbstract::URL_TYPE_MEDIA)
                . 'catalog/product' . $oriProduct->getImage() : '';
            $images    = $oriProduct->getMediaGalleryImages()->getSize() ?
                $oriProduct->getMediaGalleryImages() : [[]];
            if (is_object($images)) {
                $imagesData = [];
                foreach ($images->getItems() as $item) {
                    $imagesData[] = $item->getData();
                }
                $images = $imagesData;
            }
            /** @var $category Category */
            $lv             = 0;
            $categoryPath   = '';
            $cat            = new DataObject();
            $categoriesData = [];
            foreach ($categories as $category) {
                if ($lv < $category->getLevel()) {
                    $lv  = $category->getLevel();
                    $cat = $category;
                }
                $categoriesData[] = $category->getData();
            }
            $mapping = '';
            if (isset($categoryMap[$cat->getId()])) {
                $mapping = $categoryMap[$cat->getId()];
            }
            $categoryId =$cat->getId();
            $product->setData('category_id',$categoryId);
            $catPaths = $cat->getPathInStore() ? array_reverse(explode(',', $cat->getPathInStore())) : [];
            foreach ($catPaths as $index => $catId) {
                if ($index === (count($catPaths) - 1)) {
                    $categoryPath .= isset($categoriesName[$catId]) ? $categoriesName[$catId] : '';
                } else {
                    $categoryPath .= (isset($categoriesName[$catId]) ? $categoriesName[$catId] : '') . ' > ';
                }
            }
            $oriProduct->isAvailable() ? $product->setData('quantity_and_stock_status', 'in stock')
                : $product->setData('quantity_and_stock_status', 'out of stock');
            if ($oriProduct->getGiftCardAmounts()) {
                $giftcardAmounts = $oriProduct->getGiftCardAmounts();
                $giftcardAmountsArr = [];
                foreach ($giftcardAmounts as $giftcardAmount) {
                    $giftcardAmountsArr[] = $giftcardAmount['price'];
                }
                $giftcardAmount = max($giftcardAmountsArr);
                $product->setData('gift_card_amounts', $giftcardAmount);
            }

            $noneAttr = [
                'categoryCollection',
                'relatedProducts',
                'crossSellProducts',
                'upSellProducts',
                'final_price',
                'link',
                'image_link',
                'images',
                'category_path',
                'mapping',
                'qty',
            ];

            // Convert attribute value to attribute text
            foreach ($productAttributes as $attributeCode) {
                try {
                    if ($attributeCode === 'quantity_and_stock_status'
                        || in_array($attributeCode, $noneAttr, true)
                        || !in_array($attributeCode, $allSelectProductAttributes, true)
                        || !$product->getData($attributeCode)
                    ) {
                        continue;
                    }
                    $attributeText = $product->getResource()->getAttribute($attributeCode)
                        ->setStoreId($feed->getStoreId())->getFrontend()->getValue($product);
                    if (is_array($attributeText)) {
                        $attributeText = implode(',', $attributeText);
                    }
                    if ($attributeText) {
                        $product->setData($attributeCode, $attributeText);
                    }
                } catch (Exception $e) {
                    continue;
                }
            }

            $product->setData('categoryCollection', $categoriesData);
            $product->setData('relatedProducts', $relatedProducts);
            $product->setData('crossSellProducts', $crossSellProducts);
            $product->setData('upSellProducts', $upSellProducts);
            $product->setData('link', $productLink);
            $product->setData('image_link', $imageLink);
            $product->setData('images', $images);
            $product->setData('category_path', $categoryPath);
            $product->setData('mapping', $mapping);
            $product->setData('final_price', $finalPrice);
            $result[] = self::jsonDecode(self::jsonEncode($product->getData()));
        }
        return $result;
    }
}
