<?php

namespace DynamicYield\Integration\Model\Event;

use DynamicYield\Integration\Model\Event;
use Magento\Sales\Model\Order;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use DynamicYield\Integration\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;


class PurchaseEvent extends Event
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var ProductRepository
     */
    protected $_productRepository;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * PurchaseEvent constructor
     *
     * @param Order $order
     * @param ProductRepository $productRepository
     * @param Data $data
     * @param PriceHelper $priceHelper
     */
    public function __construct(
        Order $order,
        ProductRepository $productRepository,
        Data $data,
        PriceHelper $priceHelper
    )
    {
        $this->_order = $order;
        $this->_productRepository = $productRepository;
        $this->_dataHelper = $data;
        $this->_priceHelper = $priceHelper;
    }

    /**
     * @return string
     */
    function getName()
    {
        return "Purchase";
    }

    /**
     * @return string
     */
    function getType()
    {
        return "purchase-v1";
    }

    /**
     * @return array
     */
    function getDefaultProperties()
    {
        return [
            'value' => null,
            'currency' => null,
            'cart' => []
        ];
    }

    /**
     * @return array
     */
    function generateProperties()
    {
        $items = [];

        foreach ($this->_order->getAllItems() as $item) {

            /**
             * Skip bundle and grouped products (out of scope)
             */
            if($item->getProductType() == Type::TYPE_BUNDLE || $item->getProductType() == Data::PRODUCT_GROUPED) {
                continue;
            }

            $product = $item->getProduct();


            if(!$product) {
                continue;
            }

            $variation = $this->_dataHelper->validateSku($product);

            /**
             * IF invalid variation and no parent item - skip (because we need parent values)
             * IF valid variation and does not have a parent - skip (because we need only variation values)
             */
            if(($variation == null && $item->getParentItemId() == null) || ($variation != null && $item->getParentItemId() == null && $product->getTypeId() != Type::TYPE_SIMPLE)){
                continue;
            }

            $items[] = [
                'productId' => $variation != null ? $variation->getSku() : ($this->_productRepository->getById($item->getParentItem()->getProductId())->getSku() ?: ""),
                'quantity' => round($item->getQtyOrdered(), 2),
                'itemPrice' =>  $variation ? round($this->_priceHelper->currency($variation->getData('price'),false,false),2) : round($this->_priceHelper->currency($product->getData('price'),false,false),2)
            ];
        }

        return [
            'value' => round($this->_order->getGrandTotal(),2),
            'currency' => $this->_order->getOrderCurrencyCode(),
            'cart' => $items
        ];
    }

    /**
     * @param $orderId
     * @return $this
     */
    public function setOrder($orderId)
    {
        $this->_order->loadByAttribute('entity_id', $orderId);

        return $this;
    }
}