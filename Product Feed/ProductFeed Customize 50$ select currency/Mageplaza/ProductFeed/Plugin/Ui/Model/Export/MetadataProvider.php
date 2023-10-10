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
use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider as UiMetadataProvider;
use Mageplaza\ProductFeed\Helper\Data;

/**
 * Class MetadataProvider
 * @package Mageplaza\ProductFeed\Plugin\Ui\Model\Export
 */
class MetadataProvider extends UiMetadataProvider
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
     * MetadataProvider constructor.
     *
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param Data $helperData
     * @param RequestInterface $request
     * @param string $dateFormat
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        Data $helperData,
        RequestInterface $request,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->request    = $request;

        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
    }

    /**
     * @param UiMetadataProvider $subject
     * @param Closure $proceed
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     *
     * @return array|mixed
     * @throws Exception
     */
    public function aroundGetRowData(
        UiMetadataProvider $subject,
        Closure $proceed,
        DocumentInterface $document,
        $fields,
        $options
    ) {
        $namespace    = $this->request->getParam('namespace');
        $namespaceArr = [
            'mageplaza_productfeed_logs_listing',
            'mageplaza_productfeed_managefeeds_listing'
        ];

        if ($this->helperData->isEnabled() && in_array($namespace, $namespaceArr, true)) {
            $row = [];
            foreach ($fields as $column) {
                $key = $document->getCustomAttribute($column)->getValue();
                if ($column === 'success_message') {
                    $key = $document->getCustomAttribute($column)->getValue()
                        . ' ' . $document->getCustomAttribute('error_message')->getValue();
                }

                if (is_array($key)) {
                    $row[] = implode(',', $key);
                } else {
                    $row[] = $key;
                }
            }

            return $row;
        }

        return $proceed($document, $fields, $options);
    }
}
