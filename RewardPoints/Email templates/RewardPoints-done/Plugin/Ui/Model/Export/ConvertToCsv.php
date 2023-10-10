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

namespace Mageplaza\RewardPoints\Plugin\Ui\Model\Export;

use Closure;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToCsv as ConvertToCsvExport;
use Magento\Ui\Model\Export\MetadataProvider;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class ConvertToCsv
 * @package Mageplaza\RewardPoints\Plugin\Ui\Model\Export
 */
class ConvertToCsv extends ConvertToCsvExport
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

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
     * @param GroupRepositoryInterface $groupRepository
     * @param Data $helperData
     * @param RequestInterface $request
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param int $pageSize
     *
     * @throws FileSystemException
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        Data $helperData,
        RequestInterface $request,
        Filesystem $filesystem,
        Filter $filter,
        MetadataProvider $metadataProvider,
        $pageSize = 200
    ) {
        $this->groupRepository = $groupRepository;
        $this->helperData      = $helperData;
        $this->request         = $request;

        parent::__construct($filesystem, $filter, $metadataProvider, $pageSize);
    }

    /**
     * @param ConvertToCsvExport $subject
     * @param Closure $proceed
     *
     * @return array
     * @throws LocalizedException
     */
    public function aroundGetCsvFile(
        ConvertToCsvExport $subject,
        Closure $proceed
    ) {
        $namespace    = $this->request->getParam('namespace');
        $namespaceArr = [
            'mpreward_earning_listing',
            'mpreward_spending_listing'
        ];

        if ($this->helperData->isEnabled() && in_array($namespace, $namespaceArr, true)) {
            $component = $this->filter->getComponent();

            // $name = md5(microtime());
            $name = hash('sha256', microtime());
            $file = 'export/' . $component->getName() . $name . '.csv';

            $this->filter->prepareComponent($component);
            $this->filter->applySelectionOnTargetProvider();
            $dataProvider = $component->getContext()->getDataProvider();
            $fields       = $this->metadataProvider->getFields($component);
            $options      = $this->metadataProvider->getOptions();

            $this->directory->create('export');
            $stream = $this->directory->openFile($file, 'w+');
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
                    $customerGroupIds = str_contains($item['customer_group_ids'], ',') ?
                        explode(',', $item['customer_group_ids']) : $item['customer_group_ids'];
                    if (is_array($customerGroupIds)) {
                        $groupCode = [];
                        foreach ($customerGroupIds as $id) {
                            $groupCode[] = $this->groupRepository->getById($id)->getCode();
                        }
                        if ($groupCode) {
                            $item['customer_group_ids'] = implode(',', $groupCode);
                        }

                    }
                    $stream->writeCsv($this->metadataProvider->getRowData($item, $fields, $options));
                }
                $searchCriteria->setCurrentPage(++$i);
                $totalCount = $totalCount - $this->pageSize;
            }
            $stream->unlock();
            $stream->close();

            return [
                'type'  => 'filename',
                'value' => $file,
                'rm'    => true  // can delete file after use
            ];
        }

        return $proceed();
    }
}
