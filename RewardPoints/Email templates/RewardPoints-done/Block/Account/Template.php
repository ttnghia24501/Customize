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

namespace Mageplaza\RewardPoints\Block\Account;

use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template as AbstractTemplate;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class Template
 * @package Mageplaza\RewardPoints\Block\Account
 */
class Template extends AbstractTemplate
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * Template constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param ThemeProviderInterface $themeProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        ThemeProviderInterface $themeProvider,
        array $data = []
    ) {
        $this->helperData    = $helperData;
        $this->themeProvider = $themeProvider;

        AbstractTemplate::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        if ($this->getCurrentTheme() === 'Smartwave/porto') {
            return '';
        }

        return parent::getBlockTitle();
    }

    /**
     * @return string
     */
    public function getBlockCss()
    {
        if ($this->getCurrentTheme() === 'Smartwave/porto') {
            return 'account-nav';
        }

        return parent::getBlockCss();
    }

    /**
     * @return string
     */
    public function getCurrentTheme()
    {
        $themeId = $this->helperData->getConfigValue(DesignInterface::XML_PATH_THEME_ID);

        /**
         * @var $theme ThemeInterface
         */
        $theme = $this->themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}
