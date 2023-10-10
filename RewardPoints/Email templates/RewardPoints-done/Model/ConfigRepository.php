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

namespace Mageplaza\RewardPoints\Model;

use Mageplaza\RewardPoints\Api\ConfigRepositoryInterface;
use Mageplaza\RewardPoints\Helper\Point;
use Mageplaza\RewardPoints\Model\Config\Display;
use Mageplaza\RewardPoints\Model\Config\Earning;
use Mageplaza\RewardPoints\Model\Config\General;
use Mageplaza\RewardPoints\Model\Config\SaleEarning;
use Mageplaza\RewardPoints\Model\Config\Spending;

/**
 * Class ConfigRepository
 * @package Mageplaza\RewardPoints\Model
 */
class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @var Point
     */
    protected $pointHelper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigRepository constructor.
     *
     * @param Point $pointHelper
     * @param Config $config
     */
    public function __construct(
        Point $pointHelper,
        Config $config
    ) {
        $this->pointHelper = $pointHelper;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigs($storeId = null)
    {
        $configModule = $this->getStoreConfigs($storeId);
        $configModule['earning']['sales_earn'] = new SaleEarning($configModule['earning']['sales_earn']);

        $generalObject = new General($configModule['general']);
        $earningObject = new Earning($configModule['earning']);
        $spendingObject = new Spending($configModule['spending']);
        $displayObject = new Display($configModule['display']);

        $this->config->setGeneral($generalObject)
            ->setEarning($earningObject)
            ->setSpending($spendingObject)
            ->setDisplay($displayObject);

        return $this->config;
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function getStoreConfigs($storeId = null)
    {
        $configModule = $this->pointHelper->getConfigValue(Point::CONFIG_MODULE_PATH, $storeId);
        $configModule['general']['icon'] = $this->pointHelper->getIconUrl();
        if ($configModule['earning']['round_method'] === 'round') {
            $configModule['earning']['round_method'] = 'normal';
        }

        return $configModule;
    }
}
