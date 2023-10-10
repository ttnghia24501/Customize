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

namespace Mageplaza\ProductFeed\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Message
 * @package Mageplaza\ProductFeed\Ui\Component\Listing\Columns
 */
class Message extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $successMes = $item['success_message'];
                $errorMes = $item['error_message'];
                $item[$this->getData('name')] = '<p style="color: green">' . $successMes . '</p>';
                $item[$this->getData('name')] .= '<p style="color: red">' . $errorMes . '</p>';
            }
        }

        return $dataSource;
    }
}
