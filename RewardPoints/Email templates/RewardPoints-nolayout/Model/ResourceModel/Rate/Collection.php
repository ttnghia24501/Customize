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
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Model\ResourceModel\Rate;

use Magento\Sales\Model\ResourceModel\Collection\AbstractCollection;
use Mageplaza\RewardPoints\Api\Data\RewardRateSearchResultInterface;
use Mageplaza\RewardPoints\Model\Rate;
use Mageplaza\RewardPoints\Model\ResourceModel\Rate as ResourceRate;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Model\ResourceModel\Rate
 */
class Collection extends AbstractCollection implements RewardRateSearchResultInterface
{
    /**
     * @type string
     */
    protected $_idFieldName = 'rate_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(Rate::class, ResourceRate::class);
    }
}
