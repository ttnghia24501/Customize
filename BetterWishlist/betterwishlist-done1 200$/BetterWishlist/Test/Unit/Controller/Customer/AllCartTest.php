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

namespace Mageplaza\BetterWishlist\Test\Unit\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Wishlist\Controller\WishlistProviderInterface;
use Magento\Wishlist\Model\Wishlist;
use Mageplaza\BetterWishlist\Controller\Customer\AllCart;
use Mageplaza\BetterWishlist\Model\ItemCarrier;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

/**
 * Class AllCartTest
 * @package Mageplaza\BetterWishlist\Test\Unit\Controller\Customer
 */
class AllCartTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var AllCart
     */
    private $object;

    /**
     * @var Context|PHPUnit_Framework_MockObject_MockObject
     */
    private $context;

    /**
     * @var WishlistProviderInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $wishlistProvider;

    /**
     * @var Validator|PHPUnit_Framework_MockObject_MockObject
     */
    protected $formKeyValidator;

    /**
     * @var ItemCarrier|PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemCarrier;

    /**
     * @var ResultFactory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactory;

    /**
     * @var RequestInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_request;

    protected function setUp()
    {
        $this->context          = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()->getMock();
        $this->wishlistProvider = $this->getMockBuilder(WishlistProviderInterface::class)
            ->setMethods(['getWishlist'])
            ->disableOriginalConstructor()->getMock();
        $this->formKeyValidator = $this->getMockBuilder(Validator::class)
            ->setMethods(['validate'])
            ->disableOriginalConstructor()->getMock();
        $this->itemCarrier      = $this->getMockBuilder(ItemCarrier::class)
            ->setMethods([])
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory    = $this->getMockBuilder(ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()->getMock();
        $this->context->method('getResultFactory')->willReturn($this->resultFactory);

        $this->_request = $this->getMockBuilder(RequestInterface::class)
            ->getMockForAbstractClass();
        $this->context->method('getRequest')->willReturn($this->_request);

        $this->object = new AllCart(
            $this->context,
            $this->wishlistProvider,
            $this->formKeyValidator,
            $this->itemCarrier
        );
    }

    public function testAdminInstance()
    {
        $this->assertInstanceOf(AllCart::class, $this->object);
    }

    /**
     * @throws LocalizedException
     */
    public function testExecute()
    {
        $resultForward = $this->getMockBuilder(Forward::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->expects($this->at(0))->method('create')
            ->with(ResultFactory::TYPE_FORWARD)->willReturn($resultForward);
        $this->formKeyValidator->method('validate')->with($this->_request)->willReturn(true);

        $wishlist = $this->getMockBuilder(Wishlist::class)
            ->disableOriginalConstructor()->getMock();
        $this->wishlistProvider->method('getWishlist')->willReturn($wishlist);
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()->getMock();
        $this->resultFactory->expects($this->at(1))->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)->willReturn($resultRedirect);
        $redirectUrl = 'abc';
        $qty         = '3';
        $this->_request->method('getParam')->with('qty')->willReturn($qty);
        $this->itemCarrier->method('moveAllToCart')->with($wishlist, $qty)->willReturn($redirectUrl);

        $resultRedirect->method('setUrl')->with($redirectUrl);

        $this->assertEquals($resultRedirect, $this->object->execute());
    }
}
