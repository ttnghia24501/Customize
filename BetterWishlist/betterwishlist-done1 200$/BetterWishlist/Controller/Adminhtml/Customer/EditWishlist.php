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
 * @category  Mageplaza
 * @package   Mageplaza_BetterWishlist
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BetterWishlist\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class EditWishlist
 * @package Mageplaza\BetterWishlist\Controller\Adminhtml\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EditWishlist extends Action
{
    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * EditWishlist constructor.
     *
     * @param Context $context
     * @param RawFactory $resultRawFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultRawFactory  = $resultRawFactory;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return Raw
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $result     = $resultPage->getLayout()->renderElement('content');

        return $this->resultRawFactory->create()->setContents($result);
    }
}
