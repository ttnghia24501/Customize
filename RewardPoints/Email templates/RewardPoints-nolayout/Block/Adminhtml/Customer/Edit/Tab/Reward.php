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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Controller\RegistryConstants;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Block\Adminhtml\Customer\Edit\Tab\Grid\History;
use Mageplaza\RewardPoints\Block\Adminhtml\Render\Customer\History as BlockHistory;
use Mageplaza\RewardPoints\Helper\Data as HelperData;
use Mageplaza\RewardPoints\Model\Account;

/**
 * Class Reward
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Customer\Edit\Tab
 */
class Reward extends Generic implements TabInterface
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var ManagerInterface
     */
    protected $managerEvent;

    /**
     * Reward constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ManagerInterface $managerEvent
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ManagerInterface $managerEvent,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helper       = $helperData;
        $this->managerEvent = $managerEvent;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @param Account $rewardAccount
     *
     * @return bool
     */
    public function isChecked($rewardAccount)
    {
        return $rewardAccount->getIsActive() || $rewardAccount->getIsActive() === null;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var Account $rewardAccount */
        $rewardAccount = $this->helper->getAccountHelper()
            ->getByCustomerId($this->getCustomerId());

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('mpreward');

        $isActiveFieldset = $form->addFieldset('is_reward_active', ['legend' => __('Reward Account Enable')]);
        $isActiveFieldset->addField('is_reward_account_active', 'checkbox', [
            'label'          => __('Enable Reward Account'),
            'name'           => 'mpreward[is_active]',
            'id'             => 'is_reward_account_active',
            'value'          => 1,
            'checked'        => $this->isChecked($rewardAccount),
            'data-form-part' => $this->getData('target_form')
        ]);

        $this->_eventManager->dispatch(
            'mageplaza_reward_point_customer_form',
            ['form' => $form, 'account' => $rewardAccount]
        );

        $balanceFieldset = $form->addFieldset('reward_balance', ['legend' => __('Balance Information')]);
        $balanceFieldset->addField('point_balance', 'note', [
            'label' => __('Current Balance:'),
            'text'  => '<strong>' . $rewardAccount->getBalanceFormatted() . '</strong>',
        ]);
        $balanceFieldset->addField('point_earning', 'note', [
            'label' => __('Total Earning Points:'),
            'text'  => '<strong>' . $rewardAccount->getTotalEarningPoints(true) . '</strong>',
        ]);
        $balanceFieldset->addField('point_spending', 'note', [
            'label' => __('Total Spending Points:'),
            'text'  => '<strong>' . $rewardAccount->getTotalSpendingPoints(true) . '</strong>',
        ]);

        $updateFieldset = $form->addFieldset('reward_update', ['legend' => __('Update Balance')]);
        $updateFieldset->addField('point_amount', 'text', [
            'label'          => __('Update Points'),
            'name'           => 'mpreward[point_amount]',
            'data-form-part' => $this->getData('target_form'),
            'class'          => 'validate-number'
        ]);
        $updateFieldset->addField('comment', 'textarea', [
            'label'          => __('Comment'),
            'name'           => 'mpreward[comment]',
            'data-form-part' => $this->getData('target_form')
        ]);
        $updateFieldset->addField('expire_after', 'text', [
            'label'          => __('Points expire after'),
            'title'          => __('Points expire after'),
            'name'           => 'mpreward[expire_after]',
            'note'           => __('Day(s) since the transaction date. If empty or 0, there is no expiration'),
            'class'          => 'validate-digits',
            'data-form-part' => $this->getData('target_form')
        ]);

        $noticeFieldset = $form->addFieldset('notification_fieldset', ['legend' => __('Email Notification')]);
        $noticeFieldset->addField('notification_update', 'checkbox', [
            'label'          => __('Subscribe to balance update'),
            'name'           => 'mpreward[notification_update]',
            'id'             => 'notification_update',
            'value'          => 1,
            'checked'        => (bool) intval($rewardAccount->getData('notification_update')),
            'data-form-part' => $this->getData('target_form')
        ]);
        $noticeFieldset->addField('notification_expire', 'checkbox', [
            'label'          => __('Subscribe to points expiration notification'),
            'name'           => 'mpreward[notification_expire]',
            'id'             => 'notification_expire',
            'value'          => 1,
            'checked'        => (bool) intval($rewardAccount->getData('notification_expire')),
            'data-form-part' => $this->getData('target_form')
        ]);

        $historyFieldset = $form->addFieldset('reward_history', ['legend' => __('Transactions')]);
        $historyFieldset->addField(
            'reward_transaction',
            BlockHistory::class,
            [
                'transaction_history' => $this->getLayout()
                    ->createBlock(History::class)->toHtml()
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return mixed
     */
    protected function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return tab label
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Reward Points');
    }

    /**
     * Return tab title
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Reward Points');
    }

    /**
     * Check if can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getCustomerId() && $this->helper->isEnabled();
    }

    /**
     * Check if tab hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab URL getter
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getTabClass()
    {
        return '';
    }
}
