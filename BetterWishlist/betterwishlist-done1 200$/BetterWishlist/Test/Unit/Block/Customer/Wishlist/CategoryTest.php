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

namespace Mageplaza\BetterWishlist\Test\Unit\Block\Customer\Wishlist;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Wishlist\Helper\Data as WishlistHelper;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Mageplaza\BetterWishlist\Block\Customer\Wishlist\Category;
use Mageplaza\BetterWishlist\Helper\Data;
use Mageplaza\BetterWishlist\Model\CategoryFactory;
use Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem\Collection;
use Mageplaza\BetterWishlist\Model\WishlistItem;
use Mageplaza\BetterWishlist\Model\WishlistItemFactory as MpWishlistItemFactory;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class CategoryTest
 *
 * @package Mageplaza\BetterWishlist\Test\Unit\Block\Customer\Wishlist
 */
class CategoryTest extends PHPUnit_Framework_TestCase
{
    private $object;

    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var CustomerSession|PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSession;

    /**
     * @var FormKey|PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKey;

    /**
     * @var WishlistHelper|PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistHelper;

    /**
     * @var Data|PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperData;

    /**
     * @var CategoryFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryFactory;

    /**
     * @var WishlistFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistFactory;

    /**
     * @var MpWishlistItemFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $mpWishlistItemFactory;

    /**
     * @var PostHelper|PHPUnit_Framework_MockObject_MockObject
     */
    protected $postDataHelper;

    protected function setUp()
    {
        $this->context               = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();
        $this->customerSession       = $this->getMockBuilder(CustomerSession::class)->setMethods(
            [
                'getIsMpReindex',
                'setIsMpReindex',
                'getData',
                'getCustomerId'
            ]
        )->disableOriginalConstructor()->getMock();
        $this->mpWishlistItemFactory = $this->getMockBuilder(MpWishlistItemFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->wishlistFactory       = $this->getMockBuilder(WishlistFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->formKey               = $this->getMockBuilder(FormKey::class)->disableOriginalConstructor()->getMock();
        $this->helperData            = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()->getMock();
        $this->categoryFactory       = $this->getMockBuilder(CategoryFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->wishlistHelper        = $this->getMockBuilder(WishlistHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->postDataHelper        = $this->getMockBuilder(PostHelper::class)
            ->disableOriginalConstructor()->getMock();
        $this->object                = new Category(
            $this->context,
            $this->customerSession,
            $this->wishlistFactory,
            $this->formKey,
            $this->wishlistHelper,
            $this->postDataHelper,
            $this->helperData,
            $this->categoryFactory,
            $this->mpWishlistItemFactory
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(Category::class, $this->object);
    }

    public function testReindex()
    {
        $this->customerSession->expects($this->once())->method('getIsMpReindex')->willReturn(null);
        $customerId = '1';
        $itemId     = '1';
        $this->customerSession->expects($this->once())->method('getCustomerId')->willReturn($customerId);

        $wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()->getMock();

        $this->wishlistFactory->expects($this->once())->method('create')->willReturn($wishlist);
        $wishlist->method('loadByCustomerId')->with($customerId)->willReturnSelf();

        $categoryIds = ['1', '2'];
        $this->helperData->expects($this->once())->method('getAllCategoryIds')->willReturn($categoryIds);

        $mpWishlistItemFirst = $this->getMockBuilder(WishlistItem::class)
            ->setMethods(['getResource'])
            ->disableOriginalConstructor()->getMock();
        $count               = 0;

        $this->mpWishlistItemFactory->expects($this->at($count++))->method('create')->willReturn($mpWishlistItemFirst);
        $mpWishlistItemResource = $this
            ->getMockBuilder(\Mageplaza\BetterWishlist\Model\ResourceModel\WishlistItem::class)
            ->disableOriginalConstructor()->getMock();

        $mpWishlistItemFirst->expects($this->once())->method('getResource')->willReturn($mpWishlistItemResource);
        $mpWishlistItemResource->expects($this->once())->method('clearItem')->with($categoryIds)->willReturnSelf();

        $item = $this->getMockBuilder(Item::class)
            ->setMethods(['getId', 'getQty'])
            ->disableOriginalConstructor()->getMock();
        $wishlist->method('getItemCollection')->willReturn([$item]);

        $itemQty = '3';
        $item->method('getQty')->willReturn($itemQty);
        $item->method('getId')->willReturn($itemId);
        $mpWishlistItem = $this->getMockBuilder(WishlistItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->mpWishlistItemFactory->expects($this->at($count++))->method('create')->willReturn($mpWishlistItem);

        $collection = $this
            ->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()->getMock();
        $mpWishlistItem->method('getCollection')->willReturn($collection);
        $collection->method('addFieldToFilter')->with('wishlist_item_id', $itemId)->willReturnSelf();

        $totalQty = '1';
        $mpWishlistItem->method('getTotalQty')->willReturn($totalQty);

        $defaultCategoryId = '1';

        $dataObject = $this->getMockBuilder(DataObject::class)
            ->setMethods(['getData', 'getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->helperData->method('getDefaultCategory')->willReturn($dataObject);
        $dataObject->method('getId')->willReturn($defaultCategoryId);

        $defaultWishlistItem = $this->getMockBuilder(WishlistItem::class)
            ->disableOriginalConstructor()->getMock();
        $this->mpWishlistItemFactory->expects($this->at($count++))->method('create')->willReturn($defaultWishlistItem);

        $defaultWishlistItem->method('loadItem')->with($itemId, $defaultCategoryId)->willReturnSelf();

        $defaultWishlistItem->method('getQty')->willReturn('1');
        $defaultWishlistItem->method('addData')
            ->with([
                'category_id'      => $defaultCategoryId,
                'wishlist_item_id' => $itemId,
                'qty'              => 3
            ])->willReturnSelf();
        $countSave = 0;

        $defaultWishlistItem
            ->expects($this->at($countSave++))
            ->method('save')->willReturnSelf();

        $this->customerSession->method('setIsMpReindex')->with(1);

        $this->object->reindex();
    }
}
