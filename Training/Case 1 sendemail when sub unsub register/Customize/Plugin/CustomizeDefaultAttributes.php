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
 * @package     Mageplaza_Customize
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Customize\Plugin;

use Magento\Eav\Model\Entity\AbstractEntity;

/**
 * Class CustomizeDefaultAttributes
 * @package Mageplaza\Customize\Plugin
 */
class CustomizeDefaultAttributes
{
    /**
     * @param AbstractEntity $subject
     * @param $result
     *
     * @return array
     */
    public function afterGetDefaultAttributes(AbstractEntity $subject, $result)
    {
        $customizeEntity = [
            'mp_email_register_success_sent'
        ];

        return array_merge($result, $customizeEntity);
    }
}
