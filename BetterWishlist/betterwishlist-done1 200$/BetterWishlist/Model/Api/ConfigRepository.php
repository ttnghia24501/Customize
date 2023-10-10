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
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Model\Api;

use Mageplaza\BetterWishlist\Api\ConfigRepositoryInterface;
use Mageplaza\BetterWishlist\Helper\Data;
use Magento\Framework\Exception\InputException;
use Mageplaza\BetterWishlist\Model\Config;
use Mageplaza\BetterWishlist\Model\Config\General;

/**
 * Class ConfigRepository
 * @package Mageplaza\BetterWishlist\Model\Api
 */
class ConfigRepository implements ConfigRepositoryInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigRepository constructor.
     *
     * @param Data $helperData
     * @param Config $config
     */
    public function __construct(
        Data $helperData,
        Config $config
    ) {
        $this->_helperData = $helperData;
        $this->config = $config;
    }

    /**
     * @inheritDoc
     */
    public function getConfigs($storeId = null)
    {
        if (!$this->_helperData->isEnabled()) {
            throw new InputException(__('Module Better Wishlist extension is disabled'));
        }

        $configData = $this->_helperData->getConfigValue(Data::CONFIG_MODULE_PATH, $storeId);
        $general    = new General($configData['general']);
        $this->config->setGeneral($general);

        return $this->config;
    }
}
