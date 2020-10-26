<?php

/**
 *  * You are allowed to use this API in your web application.
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
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Core/Util/Rand.php';
require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
require_once 'Customweb/Util/Invoice.php';
require_once 'Customweb/Core/String.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'EMSeCommerceCw/AbstractOrderContext.php';

class EMSeCommerceCw_OrderContext extends EMSeCommerceCw_AbstractOrderContext {

	public function __construct($order, Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		parent::__construct($order, $paymentMethod);
		
		global $woocommerce;
		
		$sessionHandler = $woocommerce->session;
		if ($sessionHandler != null) {
			if (method_exists($sessionHandler, 'get')) {
				$checkoutId = $sessionHandler->get('EMSeCommerceCwCheckoutId', null);
				if ($checkoutId === null) {
					$checkoutId = Customweb_Core_Util_Rand::getUuid();
					$sessionHandler->set('EMSeCommerceCwCheckoutId', $checkoutId);
				}
			}
			else {
				$checkoutId = $sessionHandler->EMSeCommerceCwCheckoutId;
				if ($checkoutId === null) {
					$checkoutId = Customweb_Core_Util_Rand::getUuid();
					$sessionHandler->EMSeCommerceCwCheckoutId = $checkoutId;
				}
			}
		}
		else {
			//if a recurring payment is activated manually from the backend, the session handler is not avaiable
			//we do not need to store checkout id in this case, we just need to generate one
			$checkoutId = Customweb_Core_Util_Rand::getUuid();
		}
		
		$this->checkoutId = $checkoutId;
	}

	protected function getInvoiceItemsInternal(){
		$items = array();
		$wooCommerceItems = $this->order->get_items(array(
			'line_item' 
		));
		$itemsMap = array();
		foreach ($wooCommerceItems as $wooItem) {
			
			$product = $this->order->get_product_from_item($wooItem);
			if (is_object($product)) {
				$sku = $product->get_sku();
			}
			if (empty($sku)) {
				$sku = Customweb_Core_String::_($wooItem['name'])->replace(" ", "")->replace("\t", "")->convertTo('ASCII')->toLowerCase()->toString();
			}
			
			$name = $wooItem['name'];
			if (isset($wooItem['line_subtotal']) && isset($wooItem['qty']) && isset($wooItem['line_subtotal_tax'])) {
				$amountExclTax = $wooItem['line_subtotal'];
				$amountIncludingTax = $wooItem['line_subtotal'] + $wooItem['line_subtotal_tax'];
				$taxRate = 0;
				if ($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
				$quantity = $wooItem['qty'];
			}
			else {
				$quantity = 1;
				$amountExclTax = $wooItem['line_total'];
				$amountIncludingTax = $wooItem['line_total'] + $wooItem['line_tax'];
				$taxRate = 0;
				if ($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
			}
			
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity);
			$items[] = $item;
			$discountAmount = $item->getAmountIncludingTax() - $this->order->get_line_total($wooItem, true);
			if (Customweb_Util_Currency::compareAmount($discountAmount, 0, $this->getCurrencyCode()) > 0) {
				$discountItem = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku . '-discount',
						__("Discount", "woocommerce_emsecommercecw") . ' ' . $wooItem['name'], $taxRate, $discountAmount, $quantity,
						Customweb_Payment_Authorization_IInvoiceItem::TYPE_DISCOUNT);
				$items[] = $discountItem;
			}
		}
		
		$wooCommerceFees = $this->order->get_items(array(
			'fee' 
		));
		foreach ($wooCommerceFees as $fees) {
			$name = $fees['name'];
			$sku = Customweb_Core_String::_($name)->replace(" ", "")->replace("\t", "")->convertTo('ASCII')->toString();
			if (empty($sku)) {
				$sku = "fee" . rand();
			}
			
			if (isset($fees['line_subtotal']) && isset($fees['qty']) && isset($fees['line_subtotal_tax'])) {
				$amountExclTax = $fees['line_subtotal'];
				$amountIncludingTax = $fees['line_subtotal'] + $fees['line_subtotal_tax'];
				$taxRate = 0;
				if ($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
				$quantity = $fees['qty'];
			}
			else {
				$quantity = 1;
				$amountExclTax = $fees['line_total'];
				$amountIncludingTax = $fees['line_total'] + $fees['line_tax'];
				$taxRate = 0;
				if ($amountExclTax != 0) {
					$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
				}
			}
			
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity,
					Customweb_Payment_Authorization_IInvoiceItem::TYPE_FEE);
			$items[] = $item;
		}
		
		$wooCommerceShipping = $this->order->get_items(array(
			'shipping' 
		));
		foreach ($wooCommerceShipping as $shipping) {
			$name = $shipping['name'];
			
			$sku = Customweb_Core_String::_($name)->replace(" ", "")->replace("\t", "")->convertTo('ASCII')->toString();
			if(empty($sku)){
				$sku = $shipping['method_id'];
			}
			
			$quantity = 1;
			$amountExclTax = $shipping['cost'];
			$taxAmount = 0;
			$taxesString = $shipping['taxes'];
			if (is_string($taxesString)) {
				$taxesArray = unserialize($taxesString);
				if ($taxesArray !== false) {
					$taxAmount = end($taxesArray);
				}
			}
			elseif (is_array($taxesString) && isset($taxesString['total'])) {
				$taxAmount = end($taxesString['total']);
				if (is_array($taxAmount) && empty($taxAmount)) {
					$taxAmount = 0;
				}
				elseif (is_array($taxAmount)) {
					$taxAmount = end($taxAmount);
				}
			}
			
			$amountIncludingTax = $amountExclTax + $taxAmount;
			$taxRate = 0;
			if ($amountExclTax != 0) {
				$taxRate = ($amountIncludingTax - $amountExclTax) / $amountExclTax * 100;
			}
			$item = new Customweb_Payment_Authorization_DefaultInvoiceItem($sku, $name, $taxRate, $amountIncludingTax, $quantity,
					Customweb_Payment_Authorization_IInvoiceItem::TYPE_SHIPPING);
			$items[] = $item;
		}
		return $items;
	}

	public function getInvoiceItems(){
		$items = $this->getInvoiceItemsInternal();
		return Customweb_Util_Invoice::cleanupLineItems($items, $this->getOrderAmountInDecimals(), $this->getCurrencyCode());
	}

	public function getShippingMethod(){
		if (method_exists($this->order, 'get_shipping_method')) {
			return $this->order->get_shipping_method();
		}
		if (property_exists($this->order, 'shipping_method_title') && isset($this->order->shipping_method_title) &&
				 $this->order->shipping_method_title != '') {
			return $this->order->shipping_method_title;
		}
		$wooCommerceShipping = $this->order->get_items(array(
			'shipping' 
		));
		if (count($wooCommerceShipping) == 0) {
			return __("Free Shipping", "woocommerce_emsecommercecw");
		}
		if (count($wooCommerceShipping) > 1) {
			return __("Multiple Shipping Methods", "woocommerce_emsecommercecw");
		}
		$shipping = end($wooCommerceShipping);
		return $shipping['name'];
	}

	public function isNewSubscription(){
		$result = false;
		
		if (class_exists('WC_Subscriptions') && version_compare(WC_Subscriptions::$version, '2.0') >= 0) {
			if (wcs_order_contains_subscription($this->getOrderObject()) &&
					 ('yes' != get_option(WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no'))) {
				try {
					$adapter = EMSeCommerceCw_Util::getAuthorizationAdapter(
							Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
					if ($adapter->isPaymentMethodSupportingRecurring($this->getPaymentMethod())) {
						$result = true;
					}
				}
				catch (Customweb_Payment_Authorization_Method_PaymentMethodResolutionException $e) {
				}
			}
		}
		else {
			if (class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($this->getOrderObject()->id) &&
					 ('yes' != get_option(WC_Subscriptions_Admin::$option_prefix . '_turn_off_automatic_payments', 'no'))) {
				try {
					$adapter = EMSeCommerceCw_Util::getAuthorizationAdapter(
							Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME);
					if ($adapter->isPaymentMethodSupportingRecurring($this->getPaymentMethod())) {
						$result = true;
					}
				}
				catch (Customweb_Payment_Authorization_Method_PaymentMethodResolutionException $e) {
				}
			}
		}
		
		
		return $result;
	}
}