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

namespace Mageplaza\RewardPoints\Block\Adminhtml\Transaction\View;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\RewardPoints\Helper\Data;
use Mageplaza\RewardPoints\Model\Transaction;

/**
 * Class Form
 * @package Mageplaza\RewardPoints\Block\Adminhtml\Transaction\View
 */
class Form extends Generic
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Form constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return Generic
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Transaction $transaction */
        $transaction = $this->_coreRegistry->registry('transaction');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'     => 'edit_form',
                    'action' => $this->getUrl('*/*/save'),
                    'method' => 'post',
                ],
            ]
        );
        $form->setFieldNameSuffix('transaction');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Transaction Information')]);

        if ($transaction->getId()) {
            $customerId = $transaction->getCustomerId();
            $customer   = $this->helper->getAccountHelper()->getCustomerById($customerId);
            $url        = $this->getUrl('customer/index/edit', ['id' => $customerId]);
            $fieldset->addField('customer_email', 'note', [
                'label' => __('Customer'),
                'text'  => '<a target="_blank" href="' . $url . '">' . $customer->getName()
                    . ' &lt;' . $customer->getEmail() . '&gt;</a>'
            ]);

            $fieldset->addField('status', 'note', [
                'label' => __('Status'),
                'text'  => $transaction->getStatusLabel()
            ]);

            $fieldset->addField('point_amount', 'note', [
                'label' => __('Point Amount'),
                'text'  => '<strong>'
                    . $this->helper->getPointHelper()->format($transaction->getPointAmount(), false)
                    . '</strong>',
            ]);

            $fieldset->addField('store_id', 'note', [
                'label' => __('Store View'),
                'text'  => $this->_storeManager->getStore($transaction->getStoreId())->getName()
            ]);

            $fieldset->addField('comment', 'note', [
                'label' => __('Comment'),
                'text'  => $transaction->getTitle()
            ]);

            $fieldset->addField('created_at', 'note', [
                'label' => __('Created At'),
                'text'  => $this->formatDate($transaction->getCreatedAt(), IntlDateFormatter::MEDIUM, true)
            ]);

            if ($transaction->getExpirationDate()) {
                $fieldset->addField('expiration_date', 'note', [
                    'label' => __('Expire On'),
                    'text'  => $this->formatDate($transaction->getExpirationDate(), IntlDateFormatter::MEDIUM, true)
                ]);
            }
        } else {
            $fieldset->addField('customer_id_form', 'hidden', [
                'name'  => 'customer_id_form',
                'label' => __('Customer Id'),
                'title' => __('Customer Id'),
            ]);

            $fieldset->addField('customer_email', 'text', [
                'name'     => 'customer_email',
                'label'    => __('Customer'),
                'title'    => __('Customer'),
                'required' => true,
                'readonly' => true,
                'text'     => 'abcd'
            ])->setAfterElementHtml(
                '<div id="customer-grid" style="display:none"></div>
                <script type="text/x-magento-init">
                    {
                        "#customer_email": {
                            "Mageplaza_RewardPoints/js/view/transaction":{
                                "url": "' . $this->getAjaxUrl() . '"
                            }
                        }
                    }
                </script>'
            );
            $fieldset->addField('point_amount', 'text', [
                'name'     => 'point_amount',
                'label'    => __('Point(s)'),
                'title'    => __('Point(s)'),
                'class'    => 'validate-number',
                'required' => true
            ]);
            $fieldset->addField('comment', 'textarea', [
                'name'  => 'comment',
                'label' => __('Comment'),
                'title' => __('Comment'),
                'note'  => __('This comment will not be translated.')
            ]);
            $fieldset->addField('expire_after', 'text', [
                'name'  => 'expire_after',
                'label' => __('Points expire after'),
                'title' => __('Points expire after'),
                'class' => 'validate-number',
                'note'  => __('day(s) since the transaction date. If empty or 0, there is no expiration.'),
            ]);
        }
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Get transaction grid url
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mpreward/transaction/customergrid');
    }
}
