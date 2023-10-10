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

namespace Mageplaza\RewardPoints\Model\ResourceModel\Rate\Spending;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Mageplaza\RewardPoints\Model\Source\Direction;

/**
 * Class Collection
 * @package Mageplaza\RewardPoints\Model\ResourceModel\Rate\Spending
 */
class Collection extends SearchResult
{
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter('direction', Direction::POINT_TO_MONEY);

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'customer_group_ids' || $field === 'website_ids') {
            foreach ($condition as $type => $value) {
                if ($type === 'eq') {
                    $condition['finset'] = $value;
                    unset($condition['eq']);
                }
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
