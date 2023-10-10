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

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Mageplaza\ProductFeed\Helper\Data;
use Mageplaza\ProductFeed\Model\Config\Source\Status;

/**
 * Class LogFile
 * @package Mageplaza\ProductFeed\Ui\Component\Listing\Columns
 */
class LogFile extends Column
{
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * LogFile constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $url
     * @param Data $helperData
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $url,
        Data $helperData,
        array $components = [],
        array $data = []
    ) {
        $this->url = $url;
        $this->helperData = $helperData;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

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
                if (isset($item[$this->getData('name')])) {
                    if ($item['status'] === Status::SUCCESS) {
                        $file_name = $item[$this->getData('name')];
                        $item[$this->getData('name')] = '<a href="'
                            . $this->url->getUrl('mpproductfeed/managefeeds/download', ['feed_id' => $item['feed_id']])
                            . '" target="_blank">' . $file_name . '</a>';
                    } else {
                        $item[$this->getData('name')] = 'none';
                    }
                }
            }
        }

        return $dataSource;
    }
}
