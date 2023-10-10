<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_ProductAttachments
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Ui\Component\Listing\Column;

use Magento\Catalog\Model\Product;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class CustomerName
 * @package Mageplaza\ProductAttachments\Ui\Component\Listing\Columns
 */
class ProductName extends Column
{
    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Escaper
     *
     * @var Escaper
     */
    protected $_escaper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ProductName constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param ProductRepositoryInterface $productRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder       = $urlBuilder;
        $this->_escaper          = $escaper;
        $this->productRepository = $productRepository;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $productInfo = $item['product_info'] ? json_decode($item['product_info'], true) : [];
                $html        = $item['product_name'];

                if (isset($productInfo['super_attribute']) && $productInfo['super_attribute']) {
                    $attributes = $this->getAttributesData($item['product_id']);

                    foreach ($productInfo['super_attribute'] as $attr => $value) {
                        if (isset($attributes[$attr])) {
                            $html .= '|';
                            $html .= $attributes[$attr]['frontend_label'] . ': ';
                            if ($option = $this->findData('value', $value, $attributes[$attr]['options'])) {
                                $html .= $option['label'];
                            }
                        }
                    }

                }

                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }

    /**
     * @param $productId
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getAttributesData($productId)
    {
        /** @var Product $product */
        $product = $this->productRepository->getById($productId);
        if ($product->getTypeId() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return [];
        }

        return $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
    }

    /**
     * @param $index
     * @param $value
     * @param $array
     *
     * @return false
     */
    public function findData($index, $value, $array)
    {
        $key = array_search($value, array_column($array, $index));
        if ($key !== false) {
            return $array[$key];
        }

        return false;
    }
}
