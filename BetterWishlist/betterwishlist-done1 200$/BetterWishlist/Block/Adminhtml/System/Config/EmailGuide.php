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

namespace Mageplaza\BetterWishlist\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\BetterWishlist\Helper\Data;

/**
 * Class EmailGuide
 * @package Mageplaza\BetterWishlist\Block\Adminhtml\System\Config
 */
class EmailGuide extends Field
{
    /**
     * @var string
     */
    protected $_template = 'system/config/default-category.phtml';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var
     */
    protected $_element;

    /**
     * EmailGuide constructor.
     *
     * @param Context $context
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    ) {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $url            = $this->_urlBuilder->getUrl('adminhtml/system_config/edit/section/wishlist');
        $this->_element = $element;
        $html           = '<div style="margin-left: 15%">
                    <div>1. Please follow <strong>Stores > Settings > Configuration > Customers > Wish List > 
                        <a href="' . $url . '" >Share Options</a></strong> to set up sharing Wish List via email.
                    </div>
                    <div>2. <a target="_blank" 
                    href="https://www.mageplaza.com/kb/how-to-customize-email-template-transactional-email-magento-2.html?utm_source=mageplaza&utm_medium=mageplaza&utm_content=SMTP">
                    How to customize Email Template, Transactional Email in Magento 2</a>
                    </div>
                    <div>3. To avoid sending emails to spam box install 
                    <a href="https://www.mageplaza.com/magento-2-smtp/?utm_source=mageplaza&utm_medium=mageplaza&utm_campaign=mageplaza-review&utm_content=SMTP" target="_blank">Mageplaza SMTP</a>.
                    </div>
                 </div>';

        return $html;
    }
}
