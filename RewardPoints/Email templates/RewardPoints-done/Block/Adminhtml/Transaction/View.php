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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Transaction;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Class View
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Transaction
 */
class View extends Container
{
    /**
     * Core registry
     * @var Registry
     */
    public $registry;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_mode = 'view';
        $this->_blockGroup = 'Mageplaza_RewardPoints';
        $this->_controller = 'adminhtml_transaction';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');

        $transaction = $this->getTransaction();
        if ($transaction->getTransactionId()) {
            $this->removeButton('save');
            if ($transaction->getPointAmount() <= 0) {
                return;
            }

            if ($transaction->canExpire()) {
                $this->addButton('expire_transaction', [
                    'label' => __('Expire'),
                    'onclick' => $this->getConfirmSetLocation('expire'),
                    'class' => 'expire',
                ]);
            }

            if ($transaction->canCancel()) {
                $this->addButton('cancel_transaction', [
                    'label' => __('Cancel'),
                    'onclick' => $this->getConfirmSetLocation('cancel'),
                    'class' => 'primary',
                ]);
            }
        } else {
            $this->addButton(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => [
                                'event' => 'saveAndContinueEdit',
                                'target' => '#edit_form'
                            ]
                        ]
                    ]
                ],
                -100
            );
        }

        $this->_eventManager->dispatch('mageplaza_reward_transaction_view_construct_after', ['subject' => $this]);
    }

    /**
     * Get confirm set location
     *
     * @param $action
     *
     * @return string
     */
    public function getConfirmSetLocation($action)
    {
        $message = __('This action can not be restored. Are you sure?');
        $url = $this->getUrl('*/*/' . $action, ['id' => $this->getTransaction()->getId()]);

        return "confirmSetLocation('{$message}', '{$url}')";
    }

    /**
     * Get Transaction
     * @return mixed
     */
    public function getTransaction()
    {
        return $this->registry->registry('transaction');
    }
}
