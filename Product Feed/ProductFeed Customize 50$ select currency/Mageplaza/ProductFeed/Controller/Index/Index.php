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
 * @copyright   Copyright (c) Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Controller\Index;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Index
 * @package Mageplaza\ProductFeed\Controller\Index
 */
class Index extends Token
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        if ($this->session->getMpProductFeedErrorMessage()) {
            $errorMessage = $this->session->getMpProductFeedErrorMessage();
            if (is_string($errorMessage)) {
                printf('<b style="color:red">' . $errorMessage . '</b>');
            } else {
                printf('<b style="color:red">' . $this->session->getMpProductFeedErrorMessage()->getText() . '</b>');
            }
            $this->session->setMpProductFeedErrorMessage('');
        }

        if ($this->session->getMpProductFeedSuccessMessage()) {
            printf('<b style="color:green">' . $this->session->getMpProductFeedSuccessMessage()->getText() . '</b>');
            $this->session->setMpProductFeedSuccessMessage('');
        }
    }
}
