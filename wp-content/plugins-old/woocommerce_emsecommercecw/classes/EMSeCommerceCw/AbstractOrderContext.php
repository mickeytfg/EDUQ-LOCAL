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

require_once 'Customweb/Payment/Authorization/OrderContext/AbstractDeprecated.php';
require_once 'EMSeCommerceCw/Util.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Core/Language.php';
require_once 'EMSeCommerceCw/ConfigurationAdapter.php';

abstract class EMSeCommerceCw_AbstractOrderContext extends Customweb_Payment_Authorization_OrderContext_AbstractDeprecated {
	protected $order;
	protected $orderAmount;
	protected $currencyCode;
	protected $paymentMethod;
	protected $language;
	protected $userId;
	protected $checkoutId;

	public function __construct($order, Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		if ($order == null) {
			throw new Exception("The order parameter cannot be null.");
		}
		
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$this->currencyCode = $order->get_currency();
		}
		elseif (method_exists($order, 'get_order_currency')) {
			$this->currencyCode = $order->get_order_currency();
		}
		elseif (function_exists('get_woocommerce_currency')) {
			$this->currencyCode = get_woocommerce_currency();
		}
		else {
			$this->currencyCode = EMSeCommerceCw_Util::getShopOption('woocommerce_currency');
		}
		$this->order = $order;
		$this->paymentMethod = $paymentMethod;
		
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$this->orderAmount = $order->get_total();
		}
		elseif (method_exists($order, 'get_order_total')) {
			$this->orderAmount = $order->get_order_total();
		}
		else {
			$this->orderAmount = $order->order_total;
		}
		
		$this->language = get_bloginfo('language');
		
		if (method_exists($order, 'get_customer_id')) {
			$userId = $order->get_customer_id();
		}
		elseif (isset($order->customer_user)) {
			$userId = $order->customer_user;
		}
		else {
			$userId = $order->user_id;
		}
		
		if ($userId === null) {
			$this->userId = get_current_user_id();
		}
		else {
			$this->userId = $userId;
		}
		
		if ($this->userId === null) {
			$this->userId = 0;
		}
	}

	public function getCustomerId(){
		return $this->userId;
	}

	public function isNewCustomer(){
		return 'unkown';
	}

	public function getCustomerRegistrationDate(){
		return null;
	}

	public function getOrderObject(){
		return $this->order;
	}

	public function getOrderAmountInDecimals(){
		return $this->orderAmount;
	}

	public function getCurrencyCode(){
		return $this->currencyCode;
	}

	public function getPaymentMethod(){
		return $this->paymentMethod;
	}

	public function getLanguage(){
		return new Customweb_Core_Language($this->language);
	}

	public function getCustomerEMailAddress(){
		return $this->getBillingEMailAddress();
	}

	public function getBillingEMailAddress(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_email();
		}
		return $this->order->billing_email;
	}

	public function getBillingGender(){
		$billingCompany = trim($this->getBillingCompanyName());
		if (!empty($billingCompany)) {
			return 'company';
		}
		else {
			return null;
		}
	}

	public function getBillingSalutation(){
		return null;
	}

	public function getBillingFirstName(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_first_name();
		}
		return $this->order->billing_first_name;
	}

	public function getBillingLastName(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_last_name();
		}
		return $this->order->billing_last_name;
	}

	public function getBillingStreet(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$second = $this->order->get_billing_address_2();
			if (empty($second)) {
				return $this->order->get_billing_address_1();
			}
			return $this->order->get_billing_address_1() . " " . $second;
		}
		
		$second = $this->order->billing_address_2;
		if (empty($second)) {
			return $this->order->billing_address_1;
		}
		return $this->order->billing_address_1 . " " . $second;
	}

	public function getBillingCity(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_city();
		}
		return $this->order->billing_city;
	}

	public function getBillingPostCode(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_postcode();
		}
		return $this->order->billing_postcode;
	}

	public function getBillingState(){
		$state = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$state = $this->order->get_billing_state();
		}
		elseif (isset($this->order->billing_state)) {
			$state = $this->order->billing_state;
		}
		return EMSeCommerceCw_Util::cleanUpStateField($state, $this->getBillingCountryIsoCode());

	}

	public function getBillingCountryIsoCode(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_country();
		}
		return $this->order->billing_country;
	}

	public function getBillingPhoneNumber(){
		$phoneNumber = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$phoneNumber = $this->order->get_billing_phone();
		}
		else {
			$phoneNumber = $this->order->billing_phone;
		}
		$phoneNumber = trim($phoneNumber);
		if (!empty($phoneNumber)) {
			return $phoneNumber;
		}
		return null;
	}

	public function getBillingMobilePhoneNumber(){
		return null;
	}

	public function getBillingDateOfBirth(){
		return null;
	}

	public function getBillingCompanyName(){
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			return $this->order->get_billing_company();
		}
		return $this->order->billing_company;
	}

	public function getBillingCommercialRegisterNumber(){
		return null;
	}

	public function getBillingSalesTaxNumber(){
		return null;
	}

	public function getBillingSocialSecurityNumber(){
		return null;
	}

	public function getShippingEMailAddress(){
		return $this->getBillingEMailAddress();
	}

	public function getShippingGender(){
		$company = trim($this->getShippingCompanyName());
		if (!empty($company)) {
			return 'company';
		}
		else {
			return null;
		}
	}

	public function getShippingSalutation(){
		return null;
	}

	public function getShippingFirstName(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result = $this->order->get_shipping_first_name();
		}
		else{
			$result = $this->order->shipping_first_name;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingFirstName();
	}

	public function getShippingLastName(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result  = $this->order->get_shipping_last_name();
		}
		else{
			$result = $this->order->shipping_last_name;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingLastName();
	}

	public function getShippingStreet(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$second = $this->order->get_shipping_address_2();
			if (empty($second)) {
				$result = $this->order->get_shipping_address_1();
			}
			else{
				$result = $this->order->get_shipping_address_1() . " " . $second;
			}
		}
		else{
			$second = $this->order->shipping_address_2;
			if (empty($second)) {
				$result = $this->order->shipping_address_1;
			}
			else{
				$result =$this->order->shipping_address_1 . " " . $second;
			}
		}

		if(!empty($result)){
			return $result;
		}
		return $this->getBillingStreet();
	}

	public function getShippingCity(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result = $this->order->get_shipping_city();
		}
		else{
			$result = $this->order->shipping_city;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingCity();
	}

	public function getShippingPostCode(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result =  $this->order->get_shipping_postcode();
		}
		else {
			$result = $this->order->shipping_postcode;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingPostCode();
	}

	public function getShippingState(){
		$state = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$state = $this->order->get_shipping_state();
		}
		elseif (isset($this->order->shipping_state)) {
			$state = $this->order->shipping_state;
		}
		
		if (!empty($state)) {
			return EMSeCommerceCw_Util::cleanUpStateField($state, $this->getShippingCountryIsoCode());
		}
		return $this->getBillingState();
	}

	public function getShippingCountryIsoCode(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result = $this->order->get_shipping_country();
		}
		else {
			$result = $this->order->shipping_country;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingCountryIsoCode();
	}

	public function getShippingPhoneNumber(){
		return $this->getBillingPhoneNumber();
	}

	public function getShippingMobilePhoneNumber(){
		return null;
	}

	public function getShippingDateOfBirth(){
		return null;
	}

	public function getShippingCompanyName(){
		$result = null;
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0.0') >= 0) {
			$result = $this->order->get_shipping_company();
		}
		else{
			$result =  $this->order->shipping_company;
		}
		if(!empty($result)){
			return $result;
		}
		return $this->getBillingCompanyName();
	}

	public function getShippingCommercialRegisterNumber(){
		return null;
	}

	public function getShippingSalesTaxNumber(){
		return null;
	}

	public function getShippingSocialSecurityNumber(){
		return null;
	}

	public function getOrderParameters(){
		return array();
	}

	protected function getLineTotalsWithoutTax(array $lines){
		$total = 0;
		
		foreach ($lines as $line) {
			if ($line->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$total -= $line->getAmountExcludingTax();
			}
			else {
				$total += $line->getAmountExcludingTax();
			}
		}
		
		return $total;
	}

	protected function getLineTotalsWithTax(array $lines){
		$total = 0;
		
		foreach ($lines as $line) {
			if ($line->getType() == Customweb_Payment_Authorization_DefaultInvoiceItem::TYPE_DISCOUNT) {
				$total -= $line->getAmountIncludingTax();
			}
			else {
				$total += $line->getAmountIncludingTax();
			}
		}
		
		return $total;
	}

	public function getCheckoutId(){
		return $this->checkoutId;
	}

	public function getOrderPostId(){
		if (method_exists($this->order, 'get_id')) {
			return $this->order->get_id();
		}
		return $this->order->id;
	}

	public function getOrderNumber(){
		$orderNumber = null;
		if (EMSeCommerceCw_ConfigurationAdapter::getOrderNumberIdentifier() == 'ordernumber') {
			$orderNumber = preg_replace('/[^a-zA-Z\d]/', '', $this->order->get_order_number());
		}
		else {
			if (method_exists($this->order, 'get_id')) {
				return $this->order->get_id();
			}
			$orderNumber = $this->order->id;
		}
		$existing = EMSeCommerceCw_Util::getTransactionsByOrderId($orderNumber);
		if (count($existing) > 0) {
			$number = count(EMSeCommerceCw_Util::getTransactionsByPostId($this->getOrderPostId()));
			$orderNumber = $orderNumber . '_' . $number;
		}
		return $orderNumber;
	}
}