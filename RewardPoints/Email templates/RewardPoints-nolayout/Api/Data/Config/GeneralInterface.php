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

namespace Mageplaza\RewardPoints\Api\Data\Config;

/**
 * Interface GeneralInterface
 * @package Mageplaza\RewardPoints\Api\Data
 */
interface GeneralInterface
{
    const ENABLED                  = 'enabled';
    const ACCOUNT_NAVIGATION_LABEL = 'account_navigation_label';
    const POINT_LABEL              = 'point_label';
    const PLURAL_POINT_LABEL       = 'plural_point_label';
    const DISPLAY_POINT_LABEL      = 'display_point_label';
    const ZERO_AMOUNT              = 'zero_amount';
    const SHOW_POINT_ICON          = 'show_point_icon';
    const ICON                     = 'icon';
    const MAXIMUM_POINT            = 'maximum_point';

    /**
     * @return boolean
     */
    public function getEnabled();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setEnabled($value);

    /**
     * @return string
     */
    public function getAccountNavigationLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setAccountNavigationLabel($value);

    /**
     * @return string
     */
    public function getPointLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPointLabel($value);

    /**
     * @return string
     */
    public function getPluralPointLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPluralPointLabel($value);

    /**
     * @return string
     */
    public function getDisplayPointLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDisplayPointLabel($value);

    /**
     * @return string
     */
    public function getZeroAmount();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setZeroAmount($value);

    /**
     * @return boolean
     */
    public function getShowPointIcon();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShowPointIcon($value);

    /**
     * @return string
     */
    public function getIcon();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setIcon($value);

    /**
     * @return int
     */
    public function getMaximumPoint();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMaximumPoint($value);
}
