<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\RewardPoints\Api\Data;

/**
 * Interface RewardRateSearchResultInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface RewardRateSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get items.
     *
     * @return \Mageplaza\RewardPoints\Api\Data\RewardRateInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set items.
     *
     * @param \Mageplaza\RewardPoints\Api\Data\RewardRateInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null);
}
