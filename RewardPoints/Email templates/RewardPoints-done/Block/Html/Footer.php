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

namespace Mageplaza\RewardPoints\Block\Html;

use Exception;
use Magento\Cms\Helper\Page as HelperCmsPage;
use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Source\Page;

/**
 * Class Footer
 * @package Mageplaza\RewardPoints\Block\Html
 */
class Footer extends Link
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var HelperCmsPage
     */
    protected $helperCmsPage;

    /**
     * Footer constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param HelperCmsPage $helperCmsPage
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        HelperCmsPage $helperCmsPage,
        array $data = []
    ) {
        $this->helper        = $helper;
        $this->helperCmsPage = $helperCmsPage;

        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isShowLandingPage()
    {
        try {
            return $this->helper->getLandingPageConfig(
                'landing_page_in_footer',
                $this->helper->getStore()->getId()
            );
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @return array|bool
     */
    public function isEnabled()
    {
        return $this->helper->isEnabled();
    }

    /**
     * @return string
     */
    public function getUrlLandingPage()
    {
        try {
            $landingPage = $this->helper->getLandingPageConfig(
                'choose_landing_page',
                $this->helper->getStore()->getId()
            );

            switch ($landingPage) {
                case Page::LANDING_PAGE:
                    $url = $this->getUrl('customer/rewards/landingpage');
                    break;
                default:
                    $url = $this->helperCmsPage->getPageUrl($landingPage);
                    if (!$url) {
                        $url = $this->getUrl('customer/rewards/landingpage');
                    }
            }

            return $url;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getLandingPageLabel()
    {
        try {
            $storeId = $this->helper->getStore()->getId();
        } catch (Exception $exception) {
            $storeId = null;
        }

        return $this->helper->getLandingPageConfig(
            'landing_page_label',
            $storeId
        );
    }
}
