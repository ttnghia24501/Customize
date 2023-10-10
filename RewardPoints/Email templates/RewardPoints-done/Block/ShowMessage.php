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

namespace Mageplaza\RewardPoints\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\RewardPoints\Helper\Data;

/**
 * Class ShowMessage
 * @package Mageplaza\RewardPoints\Block
 */
class ShowMessage extends Template
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */

    protected $messageManager;
    /**
     * @var Session
     */
    protected $customer;

    /**
     * ShowMessage constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param ManagerInterface $messageManager
     * @param Session $customer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        ManagerInterface $messageManager,
        Session $customer,
        array $data = []
    ) {
        $this->helper         = $helper;
        $this->messageManager = $messageManager;
        $this->customer       = $customer;
        parent::__construct($context, $data);
        if ($this->helper->isEnabled()
            && !$this->customer->getCustomerId()
            && $this->_request->getFullActionName() === 'checkout_cart_index') {
            $this->messageManager->addNoticeMessage(__($this->getMessageToGuest()));
        }
    }

    /**
     * @return bool
     */
    public function isCustomerLogin()
    {
        if ($this->customer->getCustomerId()) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getMessageToGuest()
    {
        return $this->helper->getMessageToGuest();
    }

    /**
     * @return mixed|string
     */
    public function isEnabledNoticeToGuest()
    {
        return $this->helper->isEnabledNoticeToGuest();
    }

}
