<?php
/**
 * * You are allowed to use this API in your web application.
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
require_once 'Customweb/Core/Stream/Input/File.php';
require_once 'Customweb/Payment/IConfigurationAdapter.php';


/**
 *
 */
abstract class EMSeCommerceCw_AbstractConfigurationAdapter implements Customweb_Payment_IConfigurationAdapter
{
	
	protected $settingsMap=array(
		'operation_mode' => array(
			'id' => 'telecash-operation-mode-setting',
 			'machineName' => 'operation_mode',
 			'type' => 'select',
 			'label' => 'Operation Mode',
 			'description' => 'You can switch between the different environments, by selecting the corresponding operation mode.',
 			'defaultValue' => 'test',
 			'allowedFileExtensions' => array(
			),
 		),
 		'store_id' => array(
			'id' => 'telecash-store-id-setting',
 			'machineName' => 'store_id',
 			'type' => 'textfield',
 			'label' => 'Store ID',
 			'description' => 'The Store ID will be provided by EMS eCommerce Gateway and usally consist of 11 digits.',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'connect_shared_secret' => array(
			'id' => 'telecash-connect-shared-secret-setting',
 			'machineName' => 'connect_shared_secret',
 			'type' => 'textfield',
 			'label' => 'Shared Secret Connect Interface provided by
				EMS eCommerce Gateway.
			',
 			'description' => 'The shared secret for the connect interface',
 			'defaultValue' => '',
 			'allowedFileExtensions' => array(
			),
 		),
 		'order_id_schema' => array(
			'id' => 'telecash-order-id-schema-setting',
 			'machineName' => 'order_id_schema',
 			'type' => 'textfield',
 			'label' => 'Order prefix',
 			'description' => 'Here you can insert an order prefix. The prefix allows you to change the order number that is transmitted to EMS eCommerce Gateway . The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name-{id}). Make sure that you do not use \'_\'.',
 			'defaultValue' => '{id}',
 			'allowedFileExtensions' => array(
			),
 		),
 		'three_d_secure_behavior' => array(
			'id' => 'telecash-3dsecure-result-behavior-setting',
 			'machineName' => 'three_d_secure_behavior',
 			'type' => 'multiselect',
 			'label' => '3d Secure Result',
 			'description' => 'During the authorization of the payment a 3D secure check may be done. The selected outcomes are treated as uncertain transactions.',
 			'defaultValue' => array(
				0 => 'dirserver',
 				1 => 'authserver',
 				2 => 'attempted',
 			),
 			'allowedFileExtensions' => array(
			),
 		),
 		'pull_interval' => array(
			'id' => 'telecash-pull-interval-setting',
 			'machineName' => 'pull_interval',
 			'type' => 'textfield',
 			'label' => 'Pull interval',
 			'description' => 'The interval in minutes between pulling updates from EMS eCommerce Gateway.',
 			'defaultValue' => '15',
 			'allowedFileExtensions' => array(
			),
 		),
 		'force_non_ssl' => array(
			'id' => 'telecash-force-non-ssl-notification',
 			'machineName' => 'force_non_ssl',
 			'type' => 'select',
 			'label' => 'Force non SSL notifications',
 			'description' => 'Force EMS eCommerce Gateway to send the payment notification on a non SSL secured connection',
 			'defaultValue' => 'false',
 			'allowedFileExtensions' => array(
			),
 		),
 		'page_mobile_detection' => array(
			'id' => 'telecash-paypage-style-setting',
 			'machineName' => 'page_mobile_detection',
 			'type' => 'select',
 			'label' => 'PaymentPage Mobile Detection',
 			'description' => 'With this setting you can control how mobile devices shoulg be treated. If you use select \'Detect Mobile\' the mobile optimized payment page is used, if the user is on a mobile device and the responsive otherwise. With the \'Responsive\' option the responsive payment page is used for all users.',
 			'defaultValue' => 'responsive',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_store_id' => array(
			'id' => 'telecash-test-store-id-setting',
 			'machineName' => 'test_store_id',
 			'type' => 'textfield',
 			'label' => 'Store ID (Test)',
 			'description' => 'The Store ID will be provided by EMS eCommerce Gateway and usally consist of 11 digits. (Test)',
 			'defaultValue' => '2320540003',
 			'allowedFileExtensions' => array(
			),
 		),
 		'test_connect_shared_secret' => array(
			'id' => 'telecash-test-connect-shared-secret-setting',
 			'machineName' => 'test_connect_shared_secret',
 			'type' => 'textfield',
 			'label' => 'Shared Secret Connect Interface provided by
				EMS eCommerce Gateway (Test).
			',
 			'description' => 'The shared secret for the connect interface (Test)',
 			'defaultValue' => 'M2E9hUFuwt',
 			'allowedFileExtensions' => array(
			),
 		),
 		'review_input_form' => array(
			'id' => 'woocommerce-input-form-in-review-pane-setting',
 			'machineName' => 'review_input_form',
 			'type' => 'select',
 			'label' => 'Review Input Form',
 			'description' => 'Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.',
 			'defaultValue' => 'active',
 			'allowedFileExtensions' => array(
			),
 		),
 		'order_identifier' => array(
			'id' => 'woocommerce-order-number-setting',
 			'machineName' => 'order_identifier',
 			'type' => 'select',
 			'label' => 'Order Identifier',
 			'description' => 'Set which identifier should be sent to the payment service provider. If a plugin modifies the order number and can not guarantee it\'s uniqueness, select Post Id.',
 			'defaultValue' => 'ordernumber',
 			'allowedFileExtensions' => array(
			),
 		),
 		'external_checkout_placement' => array(
			'id' => 'external-checkout-setting',
 			'machineName' => 'external_checkout_placement',
 			'type' => 'select',
 			'label' => 'External Checkout: Widget Placement',
 			'description' => 'Should the external checkout widgets be displayed on the cart page, checkout page, both, or placed with a custom action. If you use the Custom Action, you can display the widgets with through executing the action \'woocommerce_customweb_checkout_widgets\' in your theme.',
 			'defaultValue' => 'both',
 			'allowedFileExtensions' => array(
			),
 		),
 		'external_checkout_account_creation' => array(
			'id' => '',
 			'machineName' => 'external_checkout_account_creation',
 			'type' => 'select',
 			'label' => 'External Checkout: Guest Checkout',
 			'description' => 'When an external checkout is active the customer may need to authenticate. If the e-mail address does not exist in the database, should the customer be forced to select how he or she should create the account or should automatically an guest account be created?',
 			'defaultValue' => 'skip_selection',
 			'allowedFileExtensions' => array(
			),
 		),
 		'log_level' => array(
			'id' => '',
 			'machineName' => 'log_level',
 			'type' => 'select',
 			'label' => 'Log Level',
 			'description' => 'Messages of this or a higher level will be logged.',
 			'defaultValue' => 'error',
 			'allowedFileExtensions' => array(
			),
 		),
 	);

	
	/**
	 * (non-PHPdoc)
	 * @see Customweb_Payment_IConfigurationAdapter::getConfigurationValue()
	 */
	public function getConfigurationValue($key, $languageCode = null) {

		$setting = $this->settingsMap[$key];
		$value =  get_option('woocommerce_emsecommercecw_' . $key, $setting['defaultValue']);
		
		if($setting['type'] == 'file') {
			if(isset($value['path']) && file_exists($value['path'])) {
				return new Customweb_Core_Stream_Input_File($value['path']);
			}
			else {
				$resolver = EMSeCommerceCw_Util::getAssetResolver();
				return $resolver->resolveAssetStream($setting['defaultValue']);
			}
		}
		else if($setting['type'] == 'multiselect') {
			if(empty($value)){
				return array();
			}
		}
		return $value;
	}
		
	public function existsConfiguration($key, $languageCode = null) {
		if ($languageCode !== null) {
			$languageCode = (string)$languageCode;
		}
		$value = get_option('woocommerce_emsecommercecw_' . $key, null);
		if ($value === null) {
			return false;
		}
		else {
			return true;
		}
	}
	
	
}