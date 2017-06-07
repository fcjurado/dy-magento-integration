<?php

namespace DynamicYield\Integration\Model\Event;

use DynamicYield\Integration\Model\Event;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class AddToCartEvent extends Event
{
    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var integer
     */
    protected $_qty;

    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * AddToCartEvent constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        StoreManagerInterface $storeManager
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return string
     */
    function getName()
    {
        return "Add to Cart";
    }

    /**
     * @return string
     */
    function getType()
    {
        return "add-to-cart-v1";
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
        ];
    }

    /**
     * @return array
     */
    function generateProperties()
    {
        $product = $this->_product;
        $quote = $this->_checkoutSession->getQuote();

        $item = $quote->getItemByProduct($product)->getProduct();
        $currency = $quote->getQuoteCurrencyCode();
        $price = $item->getPrice();

        if (!$currency) {
            $currency = $quote->getStoreCurrencyCode() ?
                $quote->getStoreCurrencyCode() : $quote->getBaseCurrencyCode();
        }

        /** @var Store $store */
        $store = $this->_storeManager->getStore();
        $storeCurrency = $store->getCurrentCurrency();

        return [
            'value' => $price,
            'currency' => $currency ? $currency : $storeCurrency->getCode(),
            'productId' => $product->getData('sku'),
            'quantity' => round($this->_qty, 2)
        ];
    }

    /**
     * @param Product $product
     * @param $qty
     */
    public function setProduct(Product $product, $qty)
    {
        $this->_product = $product;
        $this->_qty = $qty;
    }
}