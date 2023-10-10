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
 * @package     Mageplaza_BetterWishlist
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

var config = {
    config: {
        mixins: {
            'mage/redirect-url': {
                'Mageplaza_BetterWishlist/js/redirect-url-mixins': true
            },
            'Magento_Wishlist/js/add-to-wishlist': {
                'Mageplaza_BetterWishlist/js/add-to-wishlist-mixins': true
            }
        }
    }
};
