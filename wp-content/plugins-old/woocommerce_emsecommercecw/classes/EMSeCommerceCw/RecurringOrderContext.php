<?php 
/**
  * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 */
require_once 'EMSeCommerceCw/Util.php';
require_once 'EMSeCommerceCw/AbstractRecurringOrderContext.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'EMSeCommerceCw/CartUtil.php';
require_once 'Customweb/Util/Invoice.php';


class EMSeCommerceCw_RecurringOrderContext extends EMSeCommerceCw_AbstractRecurringOrderContext
{
	protected $productId;
	
	public function __construct($order, $paymentMethod, $amountToCharge, $productId) {
		parent::__construct($order, $paymentMethod, $amountToCharge);
		$this->productId = $productId;
		$orderId = null;
		if(method_exists($order, 'get_id')){
			$orderId= $order->get_id();
		}
		else{
			$orderId= $order->id;
		}
		
		$initialTransactionId = get_post_meta($orderId, 'cwInitialTransactionRecurring', true);
		$initialTransaction = null;
		if(!empty($initialTransactionId)){
			$this->setInitialTransactionId($initialTransactionId);
			$initialTransaction = EMSeCommerceCw_Util::getTransactionById($initialTransactionId);
		}
		else{
			$this->setInitialOrderId($orderId);
			$initialTransaction = EMSeCommerceCw_Util::getAuthorizedTransactionByPostId($this->getInitialOrderId());
			if(empty($initialTransaction)){
				$initialTransaction = EMSeCommerceCw_Util::getAuthorizedTransactionByOrderId($this->getInitialOrderId());
			}
		}
		if ($initialTransaction === NULL) {
			throw new Exception(sprintf("No initial transaction found for order %s.", $orderId));
		}
		
		$this->currencyCode = $initialTransaction->getTransactionObject()->getCurrencyCode();
		$this->userId = $initialTransaction->getCustomerId();
		
	}
	
	public function getInvoiceItems() {
		$items = array();
		$amountIncludingTax = $this->orderAmount;
		
		$taxRate = 0;
		
		// Recurring Itmes
		foreach (WC_Subscriptions_Order::get_recurring_items($this->getOrderObject()) as $reccuringItem) {
			$product = WC_Subscriptions::get_product($reccuringItem['product_id']);
			$amountExcludingTax = $reccuringItem['recurring_line_subtotal'];
			$tax = $reccuringItem['recurring_line_subtotal_tax'];
			$taxRate = $tax / $amountExcludingTax * 100;
			$amountIncludingTax = $amountExcludingTax + $tax;
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($product->get_sku(), $product->get_title(), $taxRate, $amountIncludingTax, 1);
			$items[] = $item;
		}
		
		// Apply Cart discounts
		$cartDiscount = WC_Subscriptions_Order::get_recurring_discount_cart($this->getOrderObject());
		if ($cartDiscount > 0) {
			$items = EMSeCommerceCw_CartUtil::applyCartDiscounts($cartDiscount, $items);
		}
		
		// Apply Order discounts
		$orderDiscount = WC_Subscriptions_Order::get_recurring_discount_total($this->getOrderObject());
		if ($orderDiscount > 0) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'order-discount',
				__('Discount', 'woocommerce_emsecommercecw'),
				$taxRate,
				$orderDiscount,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		// Shipping
		$shipping = WC_Subscriptions_Order::get_recurring_shipping_total($this->getOrderObject());
		if ($shipping > 0) {
			$shippingExclTax = $shipping;
			$shippingTax = WC_Subscriptions_Order::get_recurring_shipping_tax_total($this->getOrderObject());
			$taxRate = $shippingTax / $shippingExclTax * 100;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'shipping',
				$this->getShippingMethod(),
				$taxRate,
				$shippingExclTax + $shippingTax,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_SHIPPING
			);
		}
		
		// Calculate the difference to the amountToCharge. This can happen, when some outstanding payments are added to this one.
		$total = $this->getLineTotalsWithTax($items);
		$difference = $this->orderAmount - $total;
		if ($difference > 0) {
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'outstanding-payments',
				__('Outstanding Payments'),
				$taxRate,
				$difference,
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_PRODUCT
			);
		}
		else if($difference < 0){
			$taxRate = 0;
			$items[] = new Customweb_Payment_Authorization_DefaultInvoiceItem(
				'other-discount',
				__('Other Discount', 'woocommerce_emsecommercecw'),
				$taxRate,
				abs($difference),
				1,
				Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT
			);
		}
		
		return Customweb_Util_Invoice::cleanupLineItems($items, $this->getOrderAmountInDecimals(), $this->getCurrencyCode());
	}
	
}