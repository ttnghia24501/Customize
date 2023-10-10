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

namespace Mageplaza\ProductFeed\Model\Config\Source;

use Mageplaza\ProductFeed\Model\Config\AbstractSource;

/**
 * Class Protocol
 * @package Mageplaza\ProductFeed\Model\Config\Source
 */
class Protocol extends AbstractSource
{
    const SFTP        = 'sftp';
    const FTP         = 'ftp';
    const HTTP_SERVER = 'http_server';
    const API         = 'api';
    const GRAPHQL     = 'graphql';

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::SFTP        => __('SFTP'),
            self::FTP         => __('FTP'),
            self::HTTP_SERVER => __('HTTP Server'),
            self::API         => __('API'),
            self::GRAPHQL     => __('GraphQL')
        ];
    }
}
