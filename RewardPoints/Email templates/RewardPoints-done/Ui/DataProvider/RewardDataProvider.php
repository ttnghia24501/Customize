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

namespace Mageplaza\RewardPoints\Ui\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mageplaza\RewardPoints\Model\ResourceModel\Transaction\CollectionFactory;
use Mageplaza\RewardPoints\Model\Transaction;

/**
 * Class RewardDataProvider
 * @package Mageplaza\RewardPoints\Ui\DataProvider
 */
class RewardDataProvider extends AbstractDataProvider
{
    /**
     * RewardDataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        $collection = $this->getCollection();
        $collection->getSelect()
            ->joinLeft(
                ['cus' => $collection->getTable('customer_grid_flat')],
                'main_table.customer_id = cus.entity_id',
                ['name', 'email']
            );

        $arrItems = [
            'totalRecords' => $collection->getSize(),
            'items' => [],
        ];

        /** @var Transaction $item */
        foreach ($this->getCollection() as $item) {
            $item->addTitle();
            $arrItems['items'][] = $item->toArray([]);
        }

        return $arrItems;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(Filter $filter)
    {
        $field = $filter->getField();
        if (in_array($field, ['name', 'email'])) {
            $filter->setField('cus.' . $field);
        }

        parent::addFilter($filter);
    }
}
