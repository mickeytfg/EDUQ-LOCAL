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

require_once dirname(dirname(__FILE__)) . '/classes/EMSeCommerceCw/PaymentMethod.php'; 

class EMSeCommerceCw_CreditCard extends EMSeCommerceCw_PaymentMethod
{
	public $machineName = 'creditcard';
	public $admin_title = 'Credit Card';
	public $title = 'Credit Card';
	
	protected function getMethodSettings(){
		return array(
			'credit_card_brands' => array(
				'title' => __("Credit Card Brands", 'woocommerce_emsecommercecw'),
 				'default' => array(
					0 => 'visa',
 					1 => 'mastercard',
 					2 => 'americanexpress',
 					3 => 'diners',
 					4 => 'jcb',
 					5 => 'laser',
 					6 => 'maestro',
 				),
 				'description' => __("The brand of the credit card is detected by the card number if hidden authorization is used. The allowed credit card brands can be restricted by this setting. If payment page is used the customer will select the payment method at ____paymetnServiceProviderName____ and this setting is ignored.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'multiselect',
 				'type' => 'multiselect',
 				'options' => array(
					'visa' => __("VISA", 'woocommerce_emsecommercecw'),
 					'mastercard' => __("MasterCard", 'woocommerce_emsecommercecw'),
 					'americanexpress' => __("American Express", 'woocommerce_emsecommercecw'),
 					'diners' => __("Diners Club", 'woocommerce_emsecommercecw'),
 					'jcb' => __("JCB", 'woocommerce_emsecommercecw'),
 					'laser' => __("Laser", 'woocommerce_emsecommercecw'),
 					'maestro' => __("Maestro", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'brand_selection' => array(
				'title' => __("Image Brand Selection", 'woocommerce_emsecommercecw'),
 				'default' => 'active',
 				'description' => __("The card brand selection is automatically done depending on the entered card number. If no Javascript is active in the browser, a drop down is shown. In case JavaScript a the brand logos can be shown. Should the brand selection using images? (Only for Hidden Authorization)", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'active' => __("Yes, use images for the brand selection.", 'woocommerce_emsecommercecw'),
 					'inactive' => __("No, use the dropdown.", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'status_authorized' => array(
				'title' => __("Authorized Status", 'woocommerce_emsecommercecw'),
 				'default' => (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) ? 'wc-processing' : 'processing',
 				'description' => __("This status is set, when the payment was successfull and it is authorized.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'use-default' => __("Use WooCommerce rules", 'woocommerce_emsecommercecw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_uncertain' => array(
				'title' => __("Uncertain Status", 'woocommerce_emsecommercecw'),
 				'default' => (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) ? 'wc-on-hold' : 'on-hold',
 				'description' => __("You can specify the order status for new orders that have an uncertain authorisation status.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
				),
 				'is_order_status' => true,
 			),
 			'status_captured' => array(
				'title' => __("Captured Status", 'woocommerce_emsecommercecw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are captured either directly after the order or manually in the backend.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_emsecommercecw'),
 				),
 				'is_order_status' => true,
 			),
 			'capturing' => array(
				'title' => __("Capturing", 'woocommerce_emsecommercecw'),
 				'default' => 'direct',
 				'description' => __("Should the amount be captured automatically after the order (direct) or should the amount only be reserved (deferred)?", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'direct' => __("Directly after order", 'woocommerce_emsecommercecw'),
 					'deferred' => __("Deferred", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'solvency_service' => array(
				'title' => __("Solvency Information", 'woocommerce_emsecommercecw'),
 				'default' => 'deactivated',
 				'description' => __("Gather solvency information through the service from Bürgel", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'deactivated' => __("Deactivated", 'woocommerce_emsecommercecw'),
 					'active' => __("Active", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'threeD_active' => array(
				'title' => __("3d Secure Active", 'woocommerce_emsecommercecw'),
 				'default' => 'active',
 				'description' => __("Disable 3d Secure for this payment method.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'active' => __("Active", 'woocommerce_emsecommercecw'),
 					'deactivated' => __("Deactivated", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'authorizationMethod' => array(
				'title' => __("Authorization Method", 'woocommerce_emsecommercecw'),
 				'default' => 'PaymentPage',
 				'description' => __("Select the authorization method to use for processing this payment method.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'PaymentPage' => __("Payment Page", 'woocommerce_emsecommercecw'),
 				),
 			),
 			'alias_manager' => array(
				'title' => __("Alias Manager", 'woocommerce_emsecommercecw'),
 				'default' => 'inactive',
 				'description' => __("The alias manager allows the customer to select from a credit card previously stored. The sensitive data is stored by EMS eCommerce Gateway.", 'woocommerce_emsecommercecw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'active' => __("Active", 'woocommerce_emsecommercecw'),
 					'inactive' => __("Inactive", 'woocommerce_emsecommercecw'),
 				),
 			),
 		); 
	}
	
	public function __construct() {
		$this->icon = apply_filters(
			'woocommerce_emsecommercecw_creditcard_icon', 
			EMSeCommerceCw_Util::getResourcesUrl('icons/creditcard.png')
		);
		parent::__construct();
	}
	
	public function createMethodFormFields() {
		$formFields = parent::createMethodFormFields();
		
		return array_merge(
			$formFields,
			$this->getMethodSettings()
		);
	}

}