<?php

namespace Mageplaza\Customize\Plugin\GoogleTagManager;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Mageplaza\GoogleTagManager\Block\TagManager as Tag;
use Magento\Catalog\Model\Layer;
use Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection;
use Magento\CatalogSearch\Model\Layer\Filter\Attribute as AbstractFilter;


class TagManager  extends AbstractFilter
{
    /**
     * @var Layer
     */
    protected $_catalogLayer;


    /**
     * @var ListProduct
     */
    protected $_listProduct;

    public function __construct(
        ListProduct $listProduct,
        Resolver $layerResolver
    )
    {
        $this->_catalogLayer   = $layerResolver->get();
        $this->_listProduct     = $listProduct;
    }

    public function aroundGetCategotyCollection( Tag $subject, callable $proceed, $category){

        $origCategory = null;

        if ($category) {
            $origCategory = $this->_catalogLayer->getCurrentCategory();
            $this->_catalogLayer->setCurrentCategory($category);
        }
        /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $productCollection */
               $productCollection = $this->getLayer()
                   ->getProductCollection();

        $collection = $productCollection->getCollectionClone();

        $this->_listProduct->prepareSortableFieldsByCategory($this->_catalogLayer->getCurrentCategory());

        if ($origCategory) {
            $this->_catalogLayer->setCurrentCategory($origCategory);
        }

        return $collection;
    }

}
