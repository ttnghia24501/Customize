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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab\Renderer\File;
use Mageplaza\ProductFeed\Model\Config\Source\Protocol;
use Mageplaza\ProductFeed\Model\Feed;

/**
 * Class Delivery
 * @package Mageplaza\ProductFeed\Block\Adminhtml\Feed\Edit\Tab
 */
class Delivery extends Generic implements TabInterface
{
    /**
     * @var Enabledisable
     */
    protected $enabledisable;

    /**
     * @var Yesno
     */
    protected $yesno;

    /**
     * @var Protocol
     */
    protected $protocol;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Delivery constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Enabledisable $enabledisable
     * @param EncryptorInterface $encryptor
     * @param Yesno $yesno
     * @param Protocol $protocol
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Enabledisable $enabledisable,
        EncryptorInterface $encryptor,
        Yesno $yesno,
        Protocol $protocol,
        array $data = []
    ) {
        $this->enabledisable = $enabledisable;
        $this->yesno         = $yesno;
        $this->protocol      = $protocol;
        $this->encryptor     = $encryptor;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var Feed $feed */
        $feed = $this->_coreRegistry->registry('mageplaza_productfeed_feed');

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('feed_');
        $form->setFieldNameSuffix('feed');

        $deliveryFieldset = $form->addFieldset('delivery_base_fieldset', [
            'legend' => __('Delivery Config'),
            'class'  => 'fieldset-wide'
        ]);

        $deliveryFieldset->addField('delivery_enable', 'select', [
            'name'   => 'delivery_enable',
            'label'  => __('Delivery'),
            'title'  => __('Delivery'),
            'values' => $this->enabledisable->toOptionArray()
        ]);

        $deliveryFieldset->addField('protocol', 'select', [
            'name'   => 'protocol',
            'label'  => __('Protocol'),
            'title'  => __('Protocol'),
            'values' => $this->protocol->toOptionArray()
        ]);

        $deliveryFieldset->addField('passive_mode', 'select', [
            'name'   => 'passive_mode',
            'label'  => __('Passive Mode'),
            'title'  => __('Passive Mode'),
            'values' => $this->yesno->toOptionArray()
        ]);

        $deliveryFieldset->addField('host_name', 'text', [
            'name'  => 'host_name',
            'label' => __('Host Name'),
            'title' => __('Host Name'),
            'note'  => __('It can be IP address or host name. You can add port at the end of host name. E.g: ftp.domain.com:22'),
        ]);

        $deliveryFieldset->addField('user_name', 'text', [
            'name'  => 'user_name',
            'label' => __('User Name'),
            'title' => __('User Name'),
        ]);

        $deliveryFieldset->addField('password', 'password', [
            'name'  => 'password',
            'label' => __('Password'),
            'title' => __('Password'),
        ]);

        $deliveryFieldset->addField('is_encryptor', 'hidden', [
            'name'  => 'is_encryptor',
            'label' => __('Password'),
            'title' => __('Password')
        ]);

        $deliveryFieldset->addField('private_key_path', File::class, [
            'name'  => 'private_key_path',
            'label' => __('Private Key File'),
            'title' => __('Private Key File')
        ]);

        $deliveryFieldset->addField('directory_path', 'text', [
            'name'  => 'directory_path',
            'label' => __('Directory Path'),
            'title' => __('Directory Path'),
            'note'  => __('Full path of a directory. E.g: /var/www/path/to/your-folder/'),
        ]);

        $deliveryFieldset->addField('request_url', 'text', [
            'name'  => 'request_url',
            'label' => __('Request URL'),
            'title' => __('Request URL'),
            'class' => 'validate-url',
            'note'  => __('Enter the url to send to the HTTP Server, API, GraphQL by sending a request POST, GET,.....<br>E.g : {{magento_base_url}}graphql, ....')
        ]);

        $deliveryFieldset->addField('headers', 'textarea', [
            'name'  => 'headers',
            'label' => __('Headers'),
            'title' => __('Headers'),
            'note'  => __('Rows separated by line breaks, Keys and Values separated by colon ":". For example: <br>
            Content-Type: application/json <br>
            Content_length: 200')
        ]);

        $deliveryFieldset->addField('test_connect', 'button', [
            'name'               => 'test_connect',
            'value'              => __('Test Connection'),
            'after_element_html' => '<div class="test-connect-message"></div>'
        ]);

        $data = $feed->getData();
        if ($data && $pass = $feed->getData('password')) {
            $data['password']     = $this->encryptor->encrypt($pass);
            $data['is_encryptor'] = 1;
        }

        $form->addValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Delivery');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
