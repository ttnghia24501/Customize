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

namespace Mageplaza\ProductFeed\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\ProductFeed\Helper\Data as HelperData;

/**
 * Class AccessToken
 * @package Mageplaza\ProductFeed\Block\Adminhtml\System\Config
 */
class AccessToken extends Field
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_ProductFeed::system/config/access_token.phtml';

    /**
     * AccessToken constructor.
     *
     * @param Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        /** @var Button $button */
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            [
                'id'    => 'access_token_button',
                'label' => __('Get Access Token'),
                'class' => 'primary',
            ]
        );

        return $button->toHtml();
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAccessTokenUrl()
    {
        $url = 'https://accounts.google.com/o/oauth2/v2/auth?';
        $url .= 'scope=https://www.googleapis.com/auth/content';
        $url .= '&client_id=' . $this->helperData->getClientId();
        $url .= '&response_type=code&access_type=offline&include_granted_scopes=true';
        $url .= '&state=state_parameter_passthrough_value&prompt=consent';
        $url .= '&redirect_uri=' . $this->getAuthorizedRedirectURIs();

        return $url;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getAuthorizedRedirectURIs()
    {
        return $this->helperData->getAuthorizedRedirectURIs();
    }
}
