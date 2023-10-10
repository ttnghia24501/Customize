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

namespace Mageplaza\RewardPoints\Model\Action;

use Magento\Authorization\Model\UserContextInterface;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Action;

/**
 * Class Admin
 * @package Mageplaza\RewardPoints\Model\TransactionAction
 */
class Admin extends Action
{
    const CODE = 'admin';

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * Admin constructor.
     *
     * @param Data $helper
     * @param UserContextInterface $userContext
     * @param null $customer
     * @param null $actionObject
     * @param array $data
     */
    public function __construct(
        Data $helper,
        UserContextInterface $userContext,
        $customer = null,
        $actionObject = null,
        array $data = []
    ) {
        $this->userContext = $userContext;

        parent::__construct($helper, $customer, $actionObject, $data);
    }

    /**
     * @inheritdoc
     */
    public function getActionLabel()
    {
        return __('Admin Updated');
    }

    /**
     * @inheritdoc
     */
    public function getTitle($transaction)
    {
        $extraContent = Data::jsonDecode($transaction->getData('extra_content'));
        if (isset($extraContent['comment'])) {
            return $extraContent['comment'];
        }

        return __('Updated by Admin');
    }

    /**
     * @return int|mixed
     */
    public function getActionType()
    {
        return Data::ACTION_TYPE_ADMIN;
    }

    /**
     * @inheritdoc
     */
    protected function getExpirationDate()
    {
        $expireAfter = $this->getActionObject()->getData('expire_after');
        if ($expireAfter) {
            return $this->helper->getExpirationDate($expireAfter);
        }

        return parent::getExpirationDate();
    }

    /**
     * @inheritdoc
     */
    protected function getExtraContent()
    {
        $extraContent = parent::getExtraContent();

        if ($comment = $this->getActionObject()->getData('comment')) {
            $extraContent['comment'] = $comment;
        }

        $extraContent['admin_id'] = $this->userContext->getUserId();

        return $extraContent;
    }
}
