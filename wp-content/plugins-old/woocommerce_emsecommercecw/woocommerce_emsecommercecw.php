<?php

/**
 * Plugin Name: WooCommerce EMSeCommerceCw
 * Plugin URI: http://www.customweb.ch
 * Description: This plugin adds the EMSeCommerceCw payment gateway to your WooCommerce.
 * Version: 2.0.193
 * Author: customweb GmbH
 * Author URI: http://www.customweb.ch
 * WC tested up to: 3.3.5
 */

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

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit();
}

// Load Language Files
load_plugin_textdomain('woocommerce_emsecommercecw', false, basename(dirname(__FILE__)) . '/translations');

require_once dirname(__FILE__) . '/lib/loader.php';
require_once 'classes/EMSeCommerceCw/Util.php';
require_once 'EMSeCommerceCw/TranslationResolver.php';

require_once 'EMSeCommerceCw/Util.php';
require_once 'Customweb/Util/Rand.php';
require_once 'Customweb/Payment/ExternalCheckout/IContext.php';
require_once 'EMSeCommerceCw/LoggingListener.php';
require_once 'EMSeCommerceCw/Cron.php';
require_once 'Customweb/Core/Exception/CastException.php';
require_once 'EMSeCommerceCw/ContextRequest.php';
require_once 'EMSeCommerceCw/ConfigurationAdapter.php';
require_once 'EMSeCommerceCw/Dispatcher.php';
require_once 'EMSeCommerceCw/Entity/ExternalCheckoutContext.php';
require_once 'Customweb/Payment/ExternalCheckout/IProviderService.php';
require_once 'Customweb/Core/Logger/Factory.php';



if (is_admin()) {
	// Get all admin functionality
	require_once EMSeCommerceCw_Util::getBasePath() . '/admin.php';
}
/**
 * Register plugin activation hook
 */
register_activation_hook(__FILE__, array(
	'EMSeCommerceCw_Util',
	'installPlugin' 
));

/**
 * Register plugin deactivation hook
 */
register_deactivation_hook(__FILE__, array(
	'EMSeCommerceCw_Util',
	'uninstallPlugin' 
));

/**
 * Add the payment methods with a filter
 */
add_filter('woocommerce_payment_gateways', array(
	'EMSeCommerceCw_Util',
	'addPaymentMethods' 
));

if (!is_admin()) {

	function woocommerce_emsecommercecw_add_frontend_css(){
		wp_register_style('woocommerce_emsecommercecw_frontend_styles', plugins_url('resources/css/frontend.css', __FILE__));
		wp_enqueue_style('woocommerce_emsecommercecw_frontend_styles');
		
		wp_register_script('emsecommercecw_frontend_script', plugins_url('resources/js/frontend.js', __FILE__), array(
			'jquery' 
		));
		wp_enqueue_script('emsecommercecw_frontend_script');
		wp_localize_script('emsecommercecw_frontend_script', 'woocommerce_emsecommercecw_ajax', 
				array(
					'ajax_url' => admin_url('admin-ajax.php') 
				));
	}
	add_action('wp_enqueue_scripts', 'woocommerce_emsecommercecw_add_frontend_css');
}

/**
 * Adds error message during checkout to the top of the page
 * WP action: wp_head
 */
function woocommerce_emsecommercecw_add_errors(){
	if (!function_exists('is_ajax') || is_ajax()) {
		return;
	}
	if (isset($_GET['emsecommercecwftid']) && isset($_GET['emsecommercecwftt'])) {
		$dbTransaction = EMSeCommerceCw_Util::getTransactionById($_GET['emsecommercecwftid']);
		$validateHash = EMSeCommerceCw_Util::computeTransactionValidateHash($dbTransaction);
		if ($validateHash == $_GET['emsecommercecwftt']) {
			woocommerce_emsecommercecw_add_message(current($dbTransaction->getTransactionObject()->getErrorMessages()));
		}
	}
	if (isset($_GET['emsecommercecwove'])) {
		woocommerce_emsecommercecw_add_message($_GET['emsecommercecwove']);
	}
	
	if (isset($_GET['old-context-id'])) {
		$context = EMSeCommerceCw_Entity_ExternalCheckoutContext::getContextById($_GET['old-context-id']);
		if ($context->getState() == Customweb_Payment_ExternalCheckout_IContext::STATE_FAILED && $context->getCookieKey() == $_GET['verifyKey']) {
			woocommerce_emsecommercecw_add_message($context->getFailedErrorMessage());
		}
	}
	
}
add_action('wp_head', 'woocommerce_emsecommercecw_add_errors');

/**
 * Calls the function to add error message, depending on shop plugin version
 *
 * @param string $errorMessage
 */
function woocommerce_emsecommercecw_add_message($errorMessage){
	
	if (!function_exists('wc_add_notice')) {
		global $woocommerce;
		$woocommerce->add_error($errorMessage);
	}
	else {
		wc_add_notice($errorMessage, 'error');
	}
	

}

/**
 * Add new order status to shop system
 * Order status is WP Post Status
 * WP action: init
 * WP filter: wc_order_statuses
 */
function woocommerce_emsecommercecw_create_order_status(){
	$name = 'wc-pend-'.substr(hash('sha1', 'emsecommercecw'), 0 , 10); 

	register_post_status($name, 
			array(
				'label' => 'EMS eCommerce Gateway pending',
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('EMS eCommerce Gateway pending <span class="count">(%s)</span>', 
						'EMS eCommerce Gateway pending <span class="count">(%s)</span>') 
			));
//Keep old status, 
	$oldName = 'wc-'.substr(hash('sha1', 'emsecommercecw'), 0 , 10).'-pend'; 
	register_post_status($oldName, 
			array(
				'label' => 'EMS eCommerce Gateway pending (Legacy)',
				'public' => false,
				'exclude_from_search' => true,
				'show_in_admin_all_list' => false,
				'show_in_admin_status_list' => true,
				'label_count' => _n_noop('EMS eCommerce Gateway pending (Legacy) <span class="count">(%s)</span>', 
						'EMS eCommerce Gateway pending (Legacy)<span class="count">(%s)</span>') 
			));
}

// Add to list of WC Order statuses
function woocommerce_emsecommercecw_add_order_status( $order_statuses ) {
	$name = 'wc-pend-'.substr(hash('sha1', 'emsecommercecw'), 0 , 10); 
	$oldName = 'wc-'.substr(hash('sha1', 'emsecommercecw'), 0 , 10).'-pend';
	
	$order_statuses[$name] = 'EMS eCommerce Gateway pending';
	$order_statuses[$oldName] = 'EMS eCommerce Gateway pending (Legacy)';
	return $order_statuses; 
}

if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
	add_action('init', 'woocommerce_emsecommercecw_create_order_status');
	add_filter( 'wc_order_statuses', 'woocommerce_emsecommercecw_add_order_status' );
}


if(is_admin()){
	function woocommerce_emsecommercecw_orderstatus_css(){
		$orderStatusKey = 'pend-'.substr(hash('sha1', 'emsecommercecw'), 0 , 10);
		$orderStatusCss  = '.widefat .column-order_status mark.'.$orderStatusKey.':after {
			    content: "\e031";
	    		color: #ffba00;
				font-family: WooCommerce;
			    speak: none;
			    font-weight: 400;
			    font-variant: normal;
			    text-transform: none;
			    line-height: 1;
			    -webkit-font-smoothing: antialiased;
			    margin: 0;
			    text-indent: 0;
			    position: absolute;
			    top: 0;
			    left: 0;
			    width: 100%;
			    height: 100%;
			    text-align: center
		}';
		wp_add_inline_style('wp-admin', $orderStatusCss);
	}
	add_action('admin_enqueue_scripts', 'woocommerce_emsecommercecw_orderstatus_css');
}



/**
 * Add action to modify billing/shipping form during checkout
 */
add_action('woocommerce_before_checkout_billing_form', array(
	'EMSeCommerceCw_Util',
	'actionBeforeCheckoutBillingForm' 
));
add_action('woocommerce_before_checkout_shipping_form', array(
	'EMSeCommerceCw_Util',
	'actionBeforeCheckoutShippingForm' 
));

/**
 * Add Cron hooks and actions
 */
function createEMSeCommerceCwCronInterval($schedules){
	$schedules['EMSeCommerceCwCronInterval'] = array(
		'interval' => 120,
		'display' => __('EMSeCommerceCw Interval', 'woocommerce_emsecommercecw') 
	);
	return $schedules;
}

function createEMSeCommerceCwCron(){
	$timestamp = wp_next_scheduled('EMSeCommerceCwCron');
	if ($timestamp == false) {
		wp_schedule_event(time() + 120, 'EMSeCommerceCwCronInterval', 'EMSeCommerceCwCron');
	}
}

function deleteEMSeCommerceCwCron(){
	wp_clear_scheduled_hook('EMSeCommerceCwCron');
}

function runEMSeCommerceCwCron(){
	EMSeCommerceCw_Cron::run();
}

//Cron Functions to pull update
register_activation_hook(__FILE__, 'createEMSeCommerceCwCron');
register_deactivation_hook(__FILE__, 'deleteEMSeCommerceCwCron');

add_filter('cron_schedules', 'createEMSeCommerceCwCronInterval');
add_action('EMSeCommerceCwCron', 'runEMSeCommerceCwCron');

/**
 * Action to add payment information to order confirmation page, and email
 */
add_action('woocommerce_thankyou', array(
	'EMSeCommerceCw_Util',
	'thankYouPageHtml' 
));
add_action('woocommerce_view_order', array(
	'EMSeCommerceCw_Util',
	'thankYouPageHtml'
));
add_action('woocommerce_email_before_order_table', array(
	'EMSeCommerceCw_Util',
	'orderEmailHtml' 
), 10, 3);


/**
 * Updates the payment fields of the payment methods
 * WP action: wp_ajax_woocommerce_emsecommercecw_update_payment_form
 * WP action: wp_ajax_nopriv_woocommerce_emsecommercecw_update_payment_form
 */
function woocommerce_emsecommercecw_ajax_update_payment_form(){
	if (!isset($_POST['payment_method'])) {
		die();
	}
	$length = strlen('EMSeCommerceCw');
	if (substr($_POST['payment_method'], 0, $length) != 'EMSeCommerceCw') {
		die();
	}
	try {
		$paymentMethod = EMSeCommerceCw_Util::getPaymentMehtodInstance($_POST['payment_method']);
		$paymentMethod->payment_fields();
		die();
	}
	catch (Exception $e) {
		die();
	}
}
add_action('wp_ajax_woocommerce_emsecommercecw_update_payment_form', 'woocommerce_emsecommercecw_ajax_update_payment_form');
add_action('wp_ajax_nopriv_woocommerce_emsecommercecw_update_payment_form', 'woocommerce_emsecommercecw_ajax_update_payment_form');

/**
 * Form fields validation through ajax call -> prevents creating an order if validation fails
 * WP action: wp_ajax_woocommerce_emsecommercecw_validate_payment_form
 * WP action: wp_ajax_nopriv_woocommerce_emsecommercecw_validate_payment_form
 */
function woocommerce_emsecommercecw_validate_payment_form(){
	$result = array(
		'result' => 'failure',
		'message' => '<ul class="woocommerce-error"><li>' . __('Invalid Request', 'woocommerceemsecommercecw') .
		'</li></ul>'
	);
	if (!isset($_POST['payment_method'])) {
		echo json_encode($result);
		die();
	}
	$length = strlen('EMSeCommerceCw');
	if (substr($_POST['payment_method'], 0, $length) != 'EMSeCommerceCw') {
		echo json_encode($result);
		die();
	}
	try {
		if ( !defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}
		$paymentMethod = EMSeCommerceCw_Util::getPaymentMehtodInstance($_POST['payment_method']);
		$paymentMethod->validate(EMSeCommerceCw_ContextRequest::getInstance()->getParameters());
		$result = array(
			'result' => 'success');
		echo json_encode($result);
		die();
	}
	catch (Exception $e) {
		$result = array(
			'result' => 'failure',
			'message' => '<ul class="woocommerce-error"><li>' . $e->getMessage() .
			'</li></ul>'
		);
		echo json_encode($result);
		die();
	}
}
add_action('wp_ajax_woocommerce_emsecommercecw_validate_payment_form', 'woocommerce_emsecommercecw_validate_payment_form');
add_action('wp_ajax_nopriv_woocommerce_emsecommercecw_validate_payment_form', 'woocommerce_emsecommercecw_validate_payment_form');

//Fix to avoid multiple cart calculations
function woocommerce_emsecommercecw_before_calculate_totals($cart){
	$cart->disableValidationCw = true;
	return;
}
add_action('woocommerce_before_calculate_totals', 'woocommerce_emsecommercecw_before_calculate_totals');


function woocommerce_emsecommercecw_after_calculate_totals($cart){
	
	if (defined( 'WOOCOMMERCE_CHECKOUT' ) || defined( 'WOOCOMMERCE_CART' )||  is_checkout() || is_cart()) {
		//Fix to avoid multiple cart calculations, only if total really was computed
		$cart->totalCalculatedCw = true;
	}
	$cart->disableValidationCw = false;
	return;
}
add_action('woocommerce_after_calculate_totals', 'woocommerce_emsecommercecw_after_calculate_totals');




/**
 * Returns the html required to display all external checkout widgets.
 */
function woocommerce_emsecommercecw_checkout_widgets_html(){
	if (!isset($GLOBALS['cwWooCommerceExternalCheckoutWidgetsHtml'])) {
		$output = '';
		if (!empty($GLOBALS['cwWooCommerceExternalCheckouts'])) {
			$output = '<div class="cw-external-checkouts">';
			foreach ($GLOBALS['cwWooCommerceExternalCheckouts'] as $checkout) {
				$output .= $checkout['widget'];
			}
			$output .= '</div>';
		}
		$GLOBALS['cwWooCommerceExternalCheckoutWidgetsHtml'] = $output;
	}
	return $GLOBALS['cwWooCommerceExternalCheckoutWidgetsHtml'];
}

/**
 * Adds the external checkouts to the cart page
 * WP Action: woocommerce_proceed_to_checkout
 */
function woocommerce_emsecommercecw_proceed_to_checkout(){
	if (EMSeCommerceCw_ConfigurationAdapter::getExternalCheckoutPlacement() == 'both' ||
			 EMSeCommerceCw_ConfigurationAdapter::getExternalCheckoutPlacement() == 'cart') {
		if (!isset($GLOBALS['cwWooCommerceExternalCheckoutDisplay'])) {
			$GLOBALS['cwWooCommerceExternalCheckoutDisplay'] = true;
			echo woocommerce_emsecommercecw_checkout_widgets_html();
		}
	}
}
add_action('woocommerce_proceed_to_checkout', 'woocommerce_emsecommercecw_proceed_to_checkout', 10);

/**
 * Adds the external checkouts to the checkout page
 * WP Action: woocommerce_before_checkout_form
 */
function woocommerce_emsecommercecw_before_checkout_form(){
	if (EMSeCommerceCw_ConfigurationAdapter::getExternalCheckoutPlacement() == 'both' ||
			 EMSeCommerceCw_ConfigurationAdapter::getExternalCheckoutPlacement() == 'checkout') {
		if (!isset($GLOBALS['cwWooCommerceExternalCheckoutDisplay'])) {
			$GLOBALS['cwWooCommerceExternalCheckoutDisplay'] = true;
			echo woocommerce_emsecommercecw_checkout_widgets_html();
		}
	}
}
add_action('woocommerce_before_checkout_form', 'woocommerce_emsecommercecw_before_checkout_form', 20);

/**
 * Adds the external checkouts to the checkout page
 * WP Action: woocommerce_emsecommercecw_checkout_widgets
 */
function woocommerce_emsecommercecw_checkout_widgets_custom(){
	if (EMSeCommerceCw_ConfigurationAdapter::getExternalCheckoutPlacement() == 'custom') {
		if (!isset($GLOBALS['cwWooCommerceExternalCheckoutDisplay'])) {
			$GLOBALS['cwWooCommerceExternalCheckoutDisplay'] = true;
			echo woocommerce_emsecommercecw_checkout_widgets_html();
		}
	}
}
add_action('woocommerce_customweb_checkout_widgets', 'woocommerce_emsecommercecw_checkout_widgets_custom', 20);

/**
 * Clears the external checkout widget flag, so the action can be executed multiple times
 */
function woocommerce_emsecommercecw_checkout_widgets_clear(){
	unset($GLOBALS['cwWooCommerceExternalCheckoutDisplay']);
}
add_action('woocommerce_customweb_checkout_widgets', 'woocommerce_emsecommercecw_checkout_widgets_clear', 1000);
add_action('woocommerce_proceed_to_checkout', 'woocommerce_emsecommercecw_checkout_widgets_clear', 1000);
add_action('woocommerce_before_checkout_form', 'woocommerce_emsecommercecw_checkout_widgets_clear', 1000);

/**
 * collects all external checkout form different modules with filter cw_woocommerce_external_checkout_collection
 * WP action: get_header
 */
function woocommerce_emsecommercecw_create_checkouts(){
	if (is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART')) {
		if (!isset($GLOBALS['cwWooCommerceExternalCheckouts'])) {
			if (class_exists('WC_Subscriptions_Cart') && WC_Subscriptions_Cart::cart_contains_subscription()) {
				$GLOBALS['cwWooCommerceExternalCheckouts'] = array();
				return;
			}
			$checkouts = apply_filters('cw_woocommerce_external_checkout_collection', array());
			usort($checkouts, "woocommerce_emsecommercecw_sort_checkouts");
			$GLOBALS['cwWooCommerceExternalCheckouts'] = $checkouts;
		}
	}
}
add_action('get_header', 'woocommerce_emsecommercecw_create_checkouts');

/**
 * generates the external checkouts html code for the checkout of this modules
 * WP filter: cw_woocommerce_external_checkout_collection
 *
 * @param array $checkoutList
 * @throws Customweb_Core_Exception_CastException
 * @return array
 */
function woocommerce_emsecommercecw_checkout_collection($checkoutList = array()){
	$context = EMSeCommerceCw_Entity_ExternalCheckoutContext::getReusableContextFromCookie();
	if ($context === null) {
		$context = new EMSeCommerceCw_Entity_ExternalCheckoutContext();
		$context->setCookieKey(Customweb_Util_Rand::getUuid());
		$context = EMSeCommerceCw_Util::getEntityManager()->persist($context);
		setcookie('emsecommercecw-woocommerce-context-id', $context->getContextId());
		setcookie('emsecommercecw-woocommerce-context-key', $context->getCookieKey());

	}
	
	$context->updateFromCart(WC()->cart);
	$context->setState(Customweb_Payment_ExternalCheckout_IContext::STATE_PENDING);
	
	//Logged In
	if (is_user_logged_in()) {
		$user = wp_get_current_user();
		$context->setCustomerId($user->ID);
	}
	
	$providerService = EMSeCommerceCw_Util::createContainer()->getBean('Customweb_Payment_ExternalCheckout_IProviderService');
	if (!($providerService instanceof Customweb_Payment_ExternalCheckout_IProviderService)) {
		throw new Customweb_Core_Exception_CastException('Customweb_Payment_ExternalCheckout_IProviderService');
	}
	
	$checkouts = $providerService->getCheckouts($context);
	
	foreach ($checkouts as $checkout) {
		ob_start();
		EMSeCommerceCw_Util::includeTemplateFile('cart_checkout', 
				array(
					'widgetCode' => $providerService->getWidgetHtml($checkout, $context),
					'checkoutName' => $checkout->getMachineName() 
				));
		$widgetHtml = ob_get_clean();
		$checkoutList['emsecommercecw_' . $checkout->getMachineName()] = array(
			'sortOrder' => $checkout->getSortOrder(),
			'widget' => $widgetHtml 
		);
	}
	EMSeCommerceCw_Util::getEntityManager()->persist($context);
	return $checkoutList;
}
add_filter('cw_woocommerce_external_checkout_collection', 'woocommerce_emsecommercecw_checkout_collection');

/**
 * Sorts the available external checkouts
 */
function woocommerce_emsecommercecw_sort_checkouts($a, $b){
	if (isset($a['sortOrder']) && isset($b['sortOrder'])) {
		if ($a['sortOrder'] < $b['sortOrder']) {
			return -1;
		}
		else if ($a['sortOrder'] > $b['sortOrder']) {
			return 1;
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

/**
 * Returns the formatted cost from the give shipping method,
 * Uses shop settings to match the format
 *
 * @param unknown $method
 * @return string
 */
function woocommerce_emsecommercecw_format_shipping_amount_like_shop($method){
	$amount;
	if ($method->cost > 0) {
		if (WC()->cart->tax_display_cart == 'excl') {
			$amount = wc_price($method->cost);
			if ($method->get_shipping_tax() > 0 && WC()->cart->prices_include_tax) {
				$amount .= ' <small>' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
		}
		else {
			$amount = wc_price($method->cost + $method->get_shipping_tax());
			if ($method->get_shipping_tax() > 0 && !WC()->cart->prices_include_tax) {
				$amount .= ' <small>' . WC()->countries->inc_tax_or_vat() . '</small>';
			}
		}
	}
	elseif ($method->id == 'free_shipping') {
		$amount = '(' . __('Free', 'woocommerce_emsecommercecw') . ')';
	}
	else {
		$amount = wc_price($method->cost);
	}
	return $amount;
}




//Fix to not send cancel subscription mail (if initial paymet fails)
function woocommerce_emsecommercecw_unhook_subscription_cancel($email){
	remove_action('cancelled_subscription_notification', array(
		$email->emails['WCS_Email_Cancelled_Subscription'],
		'trigger' 
	));
}



/**
 * Email hooks
 * This hooks ensure the on_hold/processing/completed email are only sent once.
 * If we move the state to uncertain and back.
 */
function woocommerce_emsecommercecw_on_hold_email($enabled, $order){
	return woocommerce_emsecommercecw_check_email($enabled, $order, 'woocommerce_emsecommercecw_on_hold_email');
}
add_filter('woocommerce_email_enabled_customer_on_hold_order', 'woocommerce_emsecommercecw_on_hold_email', 5000, 2);

function woocommerce_emsecommercecw_processing_email($enabled, $order){
	return woocommerce_emsecommercecw_check_email($enabled, $order, 'woocommerce_emsecommercecw_processing_email');
}
add_filter('woocommerce_email_enabled_customer_processing_order', 'woocommerce_emsecommercecw_processing_email', 5000, 2);


function woocommerce_emsecommercecw_completed_email($enabled, $order){
	return woocommerce_emsecommercecw_check_email($enabled, $order, 'woocommerce_emsecommercecw_completed_email');
}
add_filter('woocommerce_email_enabled_customer_completed_order', 'woocommerce_emsecommercecw_completed_email', 5000, 2);


function woocommerce_emsecommercecw_check_email($enabled, $order, $metaKey){
	if (!($order instanceof WC_Order)) {
		return $enabled;
	}
	if (isset($GLOBALS['_woocommerce_emsecommercecw__resend_email']) && $GLOBALS['_woocommerce_emsecommercecw__resend_email']) {
		return $enabled;
	}
	if(!isset($GLOBALS['_woocommerce_emsecommercecw__status_change']) ||  !$GLOBALS['_woocommerce_emsecommercecw__status_change']){
		return $enabled;
	}
	$orderId = null;
	if (method_exists($order, 'get_id')) {
		$orderId= $order->get_id();
	}
	else{
		$orderId= $order->id;
	}
	$alreadySent = get_post_meta($orderId, $metaKey, true);
	if(!empty($alreadySent)){
		return false;
	}
	if($enabled){
		update_post_meta($orderId, $metaKey, true);
	}
	return $enabled;
}

function woocommerce_emsecommercecw_before_resend_email($order){
	$GLOBALS['_woocommerce_emsecommercecw__resend_email'] = true;
}

function woocommerce_emsecommercecw_after_resend_email($order, $email){
	unset($GLOBALS['_woocommerce_emsecommercecw__resend_email']);
}
add_filter('woocommerce_before_resend_order_emails', 'woocommerce_emsecommercecw_before_resend_email', 10, 1);
add_filter('woocommerce_after_resend_order_emails', 'woocommerce_emsecommercecw_after_resend_email', 10, 2);
	


/**
 * Avoid redirects if our page is called, fixes some problem introduced by other plugins
 * WP filter: redirect_canonical
 *
 * @param string $redirectUrl
 * @param string $requestUrl
 * @return false|string
 */
function woocommerce_emsecommercecw_redirect_canonical($redirectUrl, $requestUrl){
	if (woocommerce_emsecommercecw_is_plugin_page()) {
		return false;
	}
	return $redirectUrl;
}
add_filter('redirect_canonical', 'woocommerce_emsecommercecw_redirect_canonical', 10, 2);

/**
 * Removes our page/post, from appearing in breadcrumbs or navigation
 * WP filter: get_pages
 *
 * @param array $pages
 * @return array
 */
function woocommerce_emsecommercecw_get_pages($pages){
	$pageFound = -1;
	$pageId = get_option('woocommerce_emsecommercecw_page');
	
	foreach ($pages as $key => $post) {
		$postId = $post->ID;
		if ($postId == $pageId) {
			$pageFound = $key;
			break;
		}
	}
	if ($pageFound != -1) {
		unset($pages[$pageFound]);
	}
	return $pages;
}
add_filter('get_pages', 'woocommerce_emsecommercecw_get_pages', 10, 2);

/**
 * Replaces our shortcode string with the actual content
 * WP shortcode: woocommerce_emsecommercecw
 */
function woocommerce_emsecommercecw_shortcode_handling(){
	if (isset($GLOBALS['woo_emsecommercecwContent'])) {
		return $GLOBALS['woo_emsecommercecwContent'];
	}
}
add_shortcode('woocommerce_emsecommercecw', 'woocommerce_emsecommercecw_shortcode_handling');

/**
 * Initialies our context request, before wordpress messes up the parameters with it's magic quotes functions
 * WP action: plugins_loaded
 */
function woocommerce_emsecommercecw_loaded(){
	EMSeCommerceCw_ContextRequest::getInstance();
	Customweb_Core_Logger_Factory::addListener(new EMSeCommerceCw_LoggingListener());
}
//We need to execute this early as other plugins modify the $_GET and $_POST variables in this step.
add_action('plugins_loaded', 'woocommerce_emsecommercecw_loaded', -5);

/**
 * Filter for the get_locale function, this is activated before authorizing a transaction.
 * 
 * 
 * WP Filter : locale
 */
function woocommerce_emsecommercecw_locale($locale){
	if(isset($GLOBALS['woo_emsecommercecwAuthorizeLanguage'])){
		
		$languages = get_available_languages();
		foreach($languages as $language){
			if(strncmp($language, $GLOBALS['woo_emsecommercecwAuthorizeLanguage'], strlen($GLOBALS['woo_emsecommercecwAuthorizeLanguage'])) === 0){
				return $language;
			}
		}		
		return $GLOBALS['woo_emsecommercecwAuthorizeLanguage'];
	}
	return $locale;
}

/**
 * Generates our content, handles request to our enpoint,
 * writes possible content to $GLOBALS['woo_emsecommercecwContent']
 * sets default title for our pages in $GLOBALS['woo_emsecommercecwTitle']
 * WP action: wp_loaded -> most of wordpress is loaded and headers are not yet sent
 */
function woocommerce_emsecommercecw_init(){
	if (woocommerce_emsecommercecw_is_plugin_page()) {
		
		//If we have WPML language parameter, force Wordpress language
		global $sitepress;
		if (isset($sitepress) && isset($_REQUEST['wpml-lang'])) {
			$sitepress->switch_lang($_REQUEST['wpml-lang'], false);
		}
		$dispatcher = new EMSeCommerceCw_Dispatcher();
		$GLOBALS['woo_emsecommercecwTitle'] = __('Payment', 'woocommerce_emsecommercecw');
		try {
			$result = $dispatcher->dispatch();
		}
		catch (Exception $e) {
			$result = '<strong>' . $e->getMessage() . '</strong> <br />';
		}
		$GLOBALS['woo_emsecommercecwContent'] = $result;
	}
}
add_action('wp_loaded', 'woocommerce_emsecommercecw_init', 50);

/**
 * Echos additional JS and CSS file urls during the html head generation
 * WP action: wp_head -> is triggered while wordpress is echoing the html head
 */
function woocommerce_emsecommercecw_additional_files_header(){
	if (isset($GLOBALS['woo_emsecommercecwCSS'])) {
		echo $GLOBALS['woo_emsecommercecwCSS'];
	}
	if (isset($GLOBALS['woo_emsecommercecwJS'])) {
		echo $GLOBALS['woo_emsecommercecwJS'];
	}
}
add_action('wp_head', 'woocommerce_emsecommercecw_additional_files_header');

/**
 * Replaces the title of our page, if it is set in $GLOBALS['woo_emsecommercecwTitle']
 * WP filter: the_title
 *
 * @param string $title
 * @param int $id
 * @return string
 */
function woocommerce_emsecommercecw_get_page_title($title, $id = null){
	if(woocommerce_emsecommercecw_check_pageid($id)){
		if (isset($GLOBALS['woo_emsecommercecwTitle'])) {
			return $GLOBALS['woo_emsecommercecwTitle'];
		}
	}
	return $title;
}
add_filter('the_title', 'woocommerce_emsecommercecw_get_page_title', 10, 2);

/**
 * Never do unforce SSL redirect on our page
 * WP Filter : woocommerce_unforce_ssl_checkout
 */
function woocommerce_emsecommercecw_unforce_ssl_checkout($unforce){
	if (woocommerce_emsecommercecw_is_plugin_page()) {
		return false;
	}
	return $unforce;
}
add_filter('woocommerce_unforce_ssl_checkout', 'woocommerce_emsecommercecw_unforce_ssl_checkout', 10, 2);

/**
 * Remove get variables to avoid wordpress redirecting to 404,
 * if our page is called and
 * WP Filter : request
 */
function woocommerce_emsecommercecw_alter_the_query($request){
	if (woocommerce_emsecommercecw_is_plugin_page()) {
		unset($request['year']);
		unset($request['day']);
		unset($request['w']);
		unset($request['m']);
		unset($request['name']);
		unset($request['hour']);
		unset($request['minute']);
		unset($request['second']);
		unset($request['order']);
		unset($request['term']);
		unset($request['error']);
	}
	return $request;
}
add_filter('request', 'woocommerce_emsecommercecw_alter_the_query');


/**
 * We define our sites as checkout, so we are not unforced from SSL
 *
 * @param boolean $isCheckout
 * @return boolean
 */
function woocommerce_emsecommercecw_is_checkout($isCheckout){
	
	// We need to be in checkout, to calculate the complete order total
	if (isset($GLOBALS['cwExternalCheckoutOrderTotal']) && $GLOBALS['cwExternalCheckoutOrderTotal']) {
		return true;
	}
	
	if (woocommerce_emsecommercecw_is_plugin_page()) {
		return true;
	}
	return $isCheckout;
}
add_filter('woocommerce_is_checkout', 'woocommerce_emsecommercecw_is_checkout', 10, 2);

/**
 * This function returns true if the page id ($pid) belongs to the plugin.
 *
 * @param integer|null $pid
 * @return boolean
 */
function woocommerce_emsecommercecw_check_pageid($pid){
	if ($pid == get_option('woocommerce_emsecommercecw_page')) {
		return true;
	}
	if (defined('ICL_SITEPRESS_VERSION')) {
		$meta = get_post_meta($pid, '_icl_lang_duplicate_of', true);
		if ($meta != '' && $meta == get_option('woocommerce_emsecommercecw_page')) {
			return true;
		}
	}
	return false;
}

/**
 * This function returns true if the page id ($pid) belongs to the plugin page endpoint.
 * If no page id is provided, the function determines it with the
 * woocommerce_emsecommercecw_get_page_id function
 *
 * @param integer|null $pid
 * @return boolean
 */
function woocommerce_emsecommercecw_is_plugin_page(){
	if (is_admin()) {
		return false;
	}
	if(function_exists('wp_doing_ajax') && wp_doing_ajax()){
		return false;
	}
	elseif(defined( 'DOING_AJAX' ) && DOING_AJAX){
		return false;		
	}
	if ( function_exists( 'ux_builder_is_active' ) && ux_builder_is_active() ) {
		//UX Builder compatibility
		return false;
	}
	if(defined('FACETWP_VERSION')){
		//WPFacet compatibility
		$getKeys = array_keys($_GET);
		foreach($getKeys as $key){
			if(strncmp($key, 'fwp_', strlen('fwp_')) === 0){
				return false;
			}
		}
	}
	$pid = woocommerce_emsecommercecw_get_page_id();
	if ($pid == get_option('woocommerce_emsecommercecw_page')) {
		return true;
	}
	if (defined('ICL_SITEPRESS_VERSION')) {
		$meta = get_post_meta($pid, '_icl_lang_duplicate_of', true);
		if ($meta != '' && $meta == get_option('woocommerce_emsecommercecw_page')) {
			return true;
		}
	}
	return false;
}

/**
 * Returns the current page id,
 * Uses the wordpress function url_to_postid
 *
 * @return number
 */
function woocommerce_emsecommercecw_get_page_id(){
	/*
	 * WPML (Version < 3.3) has problems with calling ur_to_postid during this stage.
	 * It looks like Version 3.5 introduced the issue again.
	 * We remove their filter for our call and re add it afterwards.
	 * We need to backup and restore the registred filters. (WPML Versions 3.2)
	 *
	 * WPML adds an filter to 'option_rewrite_rules', when calling wp_rewrite_rules inside of
	 * url_to_postid. With this filter the following calls to wp_rewrite_rules return a wrong result. This leads to Page not found errors, when
	 * permalink setting for product base is a custom value.
	 * Therefore we restore the filters after we call url_to_post_id at this point. (older WPML Versions)
	 */
	$pid = 0;
	if (defined('ICL_SITEPRESS_VERSION')) {
		if (version_compare(ICL_SITEPRESS_VERSION, '3.2') < 0) {
			$backup = $GLOBALS['wp_filter'];
			$pid = url_to_postid($_SERVER['REQUEST_URI']);
			$GLOBALS['wp_filter'] = $backup;
		}
		elseif (version_compare(ICL_SITEPRESS_VERSION, '3.3') < 0 || version_compare(ICL_SITEPRESS_VERSION, '3.5') > 0) {
			global $sitepress;
			$removedFilter = false;
			if (isset($sitepress) && has_filter('url_to_postid', array(
				$sitepress,
				'url_to_postid' 
			))) {
				remove_filter('url_to_postid', array(
					$sitepress,
					'url_to_postid' 
				));
				$removedFilter = true;
			}
			$pid = url_to_postid($_SERVER['REQUEST_URI']);
			if ($removedFilter) {
				add_filter('url_to_postid', array(
					$sitepress,
					'url_to_postid' 
				));
			}
		}
		else {
			$pid = url_to_postid($_SERVER['REQUEST_URI']);
		}
	}
	else {
		$pid = url_to_postid($_SERVER['REQUEST_URI']);
	}
	return $pid;
}

/**
 * Disable product state check for subscription during the processing of the transaction
 * 
 * @param boolean $state
 * @param Object $product
 * @return boolean
 */
function woocommerce_emsecommercecw_is_subscription_purchasable($state, $product){
	if(woocommerce_emsecommercecw_is_plugin_page()){
		return true;
	}
	return $state;	
}

add_filter('woocommerce_subscription_is_purchasable', 'woocommerce_emsecommercecw_is_subscription_purchasable', 10, 2);


/**
 * Check if the order is already in processing of the module, if so we disable the option to pay the order again.
 * 
 * @param boolean $needed
 * @param Object $order
 * @return boolean
 */
function woocommerce_emsecommercecw_needs_payment($needed, $order, $validStates){

	$order_id =null;
	if(method_exists($order, 'get_id')){
		$order_id = $order->get_id();
	}
	else{
		$order_id = $order->id;
	}
	
	if ( 'yes' == get_post_meta( $order_id, '_emsecommercecw_pending_state', true ) ) {
			return false;
	}
	return $needed;	
}
add_filter('woocommerce_order_needs_payment', 'woocommerce_emsecommercecw_needs_payment', 10, 3);

/**
 * Check if the order is already in processing of the module, if so we disable the cancel for the order.
 * 
 * @param array $validStates
 * @param Object $order
 * @return boolean
 */
function woocommerce_emsecommercecw_valid_cancel( $validStates, $order = null){

	if($order === null){
		return $validStates;
	}
	$order_id =null;
	if(method_exists($order, 'get_id')){
		$order_id = $order->get_id();
	}
	else{
		$order_id = $order->id;
	}
	if ( 'yes' == get_post_meta( $order_id, '_emsecommercecw_pending_state', true ) ) {
			return array();
	}
	return $validStates;	
}
add_filter('woocommerce_valid_order_statuses_for_cancel', 'woocommerce_emsecommercecw_valid_cancel', 10, 2);
