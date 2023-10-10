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

namespace Mageplaza\ProductFeed\Plugin\Ui\Model\Export;

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\FileSystemException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as ConvertToCsvExport;
use Magento\Ui\Model\Export\MetadataProvider;
use Mageplaza\ProductFeed\Helper\Data;

/**
 * Class ConvertToCsv
 * @package Mageplaza\ProductFeed\Plugin\Ui\Model\Export
 */
class ConvertToCsv extends ConvertToCsvExport
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * ConvertToCsv constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request,
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 200
    ) {
        $this->helperData = $helperData;
        $this->request    = $request;

        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }

    /**
     * @param ConvertToCsvExport $subject
     * @param Closure $proceed
     * @return array
     * @throws LocalizedException
     */
    public function aroundGetCsvFile(
        ConvertToCsvExport $subject,
        Closure $proceed
    ) {
        $namespace    = $this->request->getParam('namespace');
        $namespaceArr = [
            'mageplaza_productfeed_logs_listing',
            'mageplaza_productfeed_managefeeds_listing'
        ];

        if ($this->helperData->isEnabled() && in_array($namespace, $namespaceArr, true)) {
            $component = $this->filter->getComponent();

            // md5() here is not for cryptographic use.
            // phpcs:ignore Magento2.Security.InsecureFunction
            $name = md5(microtime());
            $file = 'export/'. $component->getName() . $name . '.csv';

            $this->filter->prepareComponent($component);
            $this->filter->applySelectionOnTargetProvider();
            $dataProvider = $component->getContext()->getDataProvider();
            $fields       = $this->metadataProvider->getFields($component);
            $options      = $this->metadataProvider->getOptions();

            $this->directory->create('export');
            $stream         = $this->directory->openFile($file, 'w+');
            $stream->lock();
            $stream->writeCsv($this->metadataProvider->getHeaders($component));
            $i              = 1;
            $searchCriteria = $dataProvider->getSearchCriteria()
                ->setCurrentPage($i)
                ->setPageSize($this->pageSize);
            $totalCount     = (int) $dataProvider->getSearchResult()->getTotalCount();
            while ($totalCount > 0) {
                $items = $dataProvider->getSearchResult()->getItems();
                foreach ($items as $item) {
                    $this->metadataProvider->convertDate($item, $component->getName());
                    if (!$item['last_generated']) {
                        $item['file_name'] = 'none';
                    }
                    $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                }
                $searchCriteria->setCurrentPage(++$i);
                $totalCount = $totalCount - $this->pageSize;
            }
            $stream->unlock();
            $stream->close();

            return [
                'type' => 'filename',
                'value' => $file,
                'rm' => true  // can delete file after use
            ];
        }

        return $proceed();
    }
}
