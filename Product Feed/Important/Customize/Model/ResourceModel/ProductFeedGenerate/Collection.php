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
 * @package     Mageplaza_ProductFeed
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Model\ResourceModel\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mageplaza\Customize\Model\ProductFeedGenerate as ProductFeedGenerateModel;
use Mageplaza\Customize\Model\ResourceModel\ProductFeedGenerate as ProductFeedGenerateResource;

/**
 * Class Collection
 * @package Mageplaza\ProductFeed\Model\ResourceModel\ProductFeedGenerate
 */
class Collection extends AbstractCollection
{
    /**
     * ID Field Name
     *
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_productfeed_generate_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'productfeed_generate_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ProductFeedGenerateModel::class, ProductFeedGenerateResource::class);
    }
}
