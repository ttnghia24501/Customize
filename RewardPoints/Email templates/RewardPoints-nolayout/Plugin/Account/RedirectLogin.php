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

namespace Mageplaza\RewardPoints\Plugin\Account;

use Magento\Customer\Controller\Account\LoginPost;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlInterface;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class RedirectLogin
 * @package Mageplaza\RewardPoints\Plugin\Account
 */
class RedirectLogin
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var RedirectFactory
     */
    protected $redirectFactory;

    /**
     * Redirect constructor.
     *
     * @param Data $helperData
     * @param UrlInterface $url
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        Data $helperData,
        UrlInterface $url,
        RedirectFactory $redirectFactory
    ) {
        $this->helperData      = $helperData;
        $this->url             = $url;
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param LoginPost $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterExecute(LoginPost $subject, $result)
    {
        if ($this->helperData->getConfigGeneral('redirect_after_login') && ($this->helperData->isEnabled())) {
            $redirect = $this->redirectFactory->create();
            $redirect->setUrl($this->url->getUrl('customer/rewards/'));

            return $redirect;
        }

        return $result;
    }
}
