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

namespace Mageplaza\RewardPoints\Helper;

use Exception;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\Media;
use Mageplaza\RewardPoints\Model\Source\DisplayPointLabel;
use Mageplaza\RewardPoints\Model\Source\RoundingMethod;

/**
 * Class Point
 * @package Mageplaza\RewardPoints\Helper
 */
class Point extends Data
{
    /**
     * @var AssetRepository
     */
    protected $assetRepo;

    /**
     * @var Media
     */
    protected $mediaHelper;

    /**
     * Point constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param SessionFactory $sessionFactory
     * @param TimezoneInterface $timeZone
     * @param AssetRepository $assetRepo
     * @param Media $mediaHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        SessionFactory $sessionFactory,
        TimezoneInterface $timeZone,
        AssetRepository $assetRepo,
        Media $mediaHelper
    ) {
        $this->assetRepo   = $assetRepo;
        $this->mediaHelper = $mediaHelper;

        parent::__construct(
            $context,
            $objectManager,
            $storeManager,
            $priceCurrency,
            $timeZone,
            $sessionFactory
        );
    }

    /**
     * @param float $amount
     * @param bool $highlight
     * @param null $storeId
     *
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function format($amount, $highlight = true, $storeId = null)
    {
        $amount = $this->round($amount, $storeId);
        if ($amount == 0) {
            return $this->getConfigGeneral('zero_amount', $storeId);
        }

        if (in_array($amount, [1, -1])) {
            $label = $this->getPointLabel($storeId);
        } else {
            $label = $this->getPluralPointLabel($storeId);
        }

        $pointLabel = ($this->getPointLabelPosition($storeId) == DisplayPointLabel::AFTER_AMOUNT)
            ? $amount . ' ' . $label : $label . ' ' . $amount;

        if ($highlight) {
            if ($this->getFullActionName() === 'catalog_product_view') {
                if ($this->checkHighlightEnabledByType('product')) {
                    return '<span class="mp-rw-highlight">' . $pointLabel . '</span>';
                }

                return $pointLabel;
            }

            if ($this->checkHighlightEnabledByType('category') && $this->getFullActionName() !== '__') {
                return '<span class="mp-rw-highlight">' . $pointLabel . '</span>';
            }
        }

        return $pointLabel;
    }

    /**
     * @param float $point
     * @param null $storeId
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function round($point, $storeId = null)
    {
        $point = $point ?: 0;
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $roundingMethod = $this->getConfigEarning('round_method', $storeId);
        switch ($roundingMethod) {
            case RoundingMethod::ROUNDING_DOWN:
                $point = floor($point);
                break;
            case RoundingMethod::ROUNDING_UP:
                $point = ceil($point);
                break;
            default:
                $point = round($point);
        }

        return $point;
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getIconHtml($storeId = null)
    {
        if (!$this->getConfigGeneral('show_point_icon', $storeId)) {
            return '';
        }

        $iconUrl = $this->getIconUrl($storeId);

        return '<img src="' . $iconUrl . '" alt="' . __('Reward Points') . '" style="height :15px; width: 15px !important;" />';
    }

    /**
     * @param null $storeId
     *
     * @return string
     */
    public function getIconUrl($storeId = null)
    {
        try {
            $icon = $this->getConfigGeneral('icon', $storeId);
            if ($icon && $this->mediaHelper->getMediaDirectory()->isExist('mageplaza/rewardpoints/' . $icon)) {
                $iconUrl = $this->mediaHelper->getMediaUrl('mageplaza/rewardpoints/' . $icon);
            } else {
                $iconUrl = $this->assetRepo->getUrlWithParams(
                    'Mageplaza_RewardPoints::images/default/point.png',
                    ['_secure' => $this->_getRequest()->isSecure()]
                );
            }
        } catch (Exception $e) {
            $iconUrl = '';
        }

        return $iconUrl;
    }

    /**
     * Get zero point label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getZeroPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('zero_amount', $storeId);
    }

    /**
     * Get point Label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('point_label', $storeId);
    }

    /**
     * Get plural point label
     *
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPluralPointLabel($storeId = null)
    {
        return $this->getConfigGeneral('plural_point_label', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPointLabelPosition($storeId = null)
    {
        return $this->getConfigGeneral('display_point_label', $storeId);
    }
}
