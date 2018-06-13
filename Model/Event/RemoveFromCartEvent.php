<?php

namespace DynamicYield\Integration\Model\Event;

use Magento\Checkout\Model\Session as CheckoutSession;
use DynamicYield\Integration\Model\Event;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\Cart;
use DynamicYield\Integration\Helper\Data;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;


class RemoveFromCartEvent extends Event
{
    /**
     * @var int
     */
    protected $_cartItem;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * RemoveFromCartEvent constructor
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Cart $cart
     * @param Data $data
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager,
        Cart $cart,
        Data $data,
        PriceHelper $priceHelper
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
        $this->_cart = $cart;
        $this->_dataHelper = $data;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * @return string
     */
    function getName()
    {
        return "Remove from Cart";
    }

    /**
     * @return string
     */
    function getType()
    {
        return "remove-from-cart-v1";
    }

    /**
     * @return array
     */
    function getDefaultProperties()
    {
        return [
            'value' => 0,
            'currency' => null,
            'productId' => '',
            'quantity' => 0,
            'cart' => [],
        ];
    }

    /**
     * @return array
     */
    function generateProperties()
    {
        $quote = $this->_checkoutSession->getQuote();

        /** @var Item $item */
        $item = $quote->getItemById($this->_cartItem);
        $currency = $quote->getQuoteCurrencyCode();

        if (!$currency) {
            $currency = $quote->getStoreCurrencyCode() ?
                $quote->getStoreCurrencyCode() : $quote->getBaseCurrencyCode();
        }

        /** @var Store $store */
        $store = $this->_storeManager->getStore();
        $storeCurrency = $store->getCurrentCurrency();

        $product = $this->_dataHelper->validateSku($item->getProduct());

        return [
            'cart' => $this->getCartItems($this->_cart,$this->_dataHelper, $this->_priceHelper,[$item->getId()]),
            'value' => $product ? round($this->_priceHelper->currency($product->getData('price'),false,false),2) : round($this->_priceHelper->currency($item->getProduct()->getData('price'),false,false),2),
            'currency' => $currency ? $currency : $storeCurrency->getCode(),
            'productId' => $product ? $product->getSku() : $item->getProduct()->getData('sku'),
            'quantity' => round($item->getQty(), 2)
        ];
    }

    /**
     * @param $cartItem
     */
    public function setCartItem($cartItem)
    {
        $this->_cartItem = $cartItem;
    }
}