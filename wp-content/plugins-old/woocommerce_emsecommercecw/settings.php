<?php

require_once 'EMSeCommerceCw/Util.php';
require_once 'EMSeCommerceCw/BackendFormRenderer.php';
require_once 'Customweb/Util/Url.php';
require_once 'Customweb/Payment/Authorization/DefaultInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
require_once 'Customweb/Form/Control/IEditableControl.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICancel.php';
require_once 'Customweb/IForm.php';
require_once 'Customweb/Form.php';
require_once 'Customweb/Core/Http/ContextRequest.php';
require_once 'Customweb/Form/Control/MultiControl.php';
require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/Payment/Authorization/IInvoiceItem.php';
require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
require_once 'Customweb/Licensing/EMSeCommerceCw/License.php';



// Make sure we don't expose any info if called directly        	  			  	 	   	
if (!function_exists('add_action')) {
	echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
	exit();
}

// Add some CSS and JS for admin        	  			  	 	   	
function woocommerce_emsecommercecw_admin_add_setting_styles_scripts(){
	wp_register_style('woocommerce_emsecommercecw_admin_styles', plugins_url('resources/css/settings.css', __FILE__));
	wp_enqueue_style('woocommerce_emsecommercecw_admin_styles');
	
	wp_register_script('woocommerce_emsecommercecw_admin_js', plugins_url('resources/js/settings.js', __FILE__));
	wp_enqueue_script('woocommerce_emsecommercecw_admin_js');
}
add_action('admin_init', 'woocommerce_emsecommercecw_admin_add_setting_styles_scripts');

function woocommerce_emsecommercecw_admin_notice_handler(){
	if (get_transient(get_current_user_id() . '_emsecommercecw_am') !== false) {
		
		foreach (get_transient(get_current_user_id() . '_emsecommercecw_am') as $message) {
			$cssClass = '';
			if (strtolower($message['type']) == 'error') {
				$cssClass = 'error';
			}
			else if (strtolower($message['type']) == 'info') {
				$cssClass = 'updated';
			}
			
			echo '<div class="' . $cssClass . '">';
			echo '<p>EMS eCommerce Gateway: ' . $message['message'] . '</p>';
			echo '</div>';
		}
		delete_transient(get_current_user_id() . '_emsecommercecw_am');
	}
}
add_action('admin_notices', 'woocommerce_emsecommercecw_admin_notice_handler');

function woocommerce_emsecommercecw_admin_show_message($message, $type){
	$existing = array();
	if (get_transient(get_current_user_id() . '_emsecommercecw_am') === false) {
		$existing = get_transient(get_current_user_id() . '_emsecommercecw_am');
	}
	$existing[] = array(
		'message' => $message,
		'type' => $type 
	);
	set_transient(get_current_user_id() . '_emsecommercecw_am', $existing);
}

/**
 * Add the configuration menu
 */
function woocommerce_emsecommercecw_menu(){
	add_menu_page('EMS eCommerce Gateway', __('EMS eCommerce Gateway', 'woocommerce_emsecommercecw'), 
			'manage_woocommerce', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_options');
	
	if (isset($_REQUEST['page']) && strpos($_REQUEST['page'], 'woocommerce-emsecommercecw') !== false) {
		$container = EMSeCommerceCw_Util::createContainer();
		if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
			$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
			foreach ($adapter->getForms() as $form) {
				add_submenu_page('woocommerce-emsecommercecw', 'EMS eCommerce Gateway ' . $form->getTitle(), $form->getTitle(), 
						'manage_woocommerce', 'woocommerce-emsecommercecw' . $form->getMachineName(), 
						'woocommerce_emsecommercecw_extended_options');
			}
		}
	}
	
	add_submenu_page(null, 'EMS eCommerce Gateway Capture', 'EMS eCommerce Gateway Capture', 'manage_woocommerce', 
			'woocommerce-emsecommercecw_capture', 'woocommerce_emsecommercecw_render_capture');
	add_submenu_page(null, 'EMS eCommerce Gateway Cancel', 'EMS eCommerce Gateway Cancel', 'manage_woocommerce', 
			'woocommerce-emsecommercecw_cancel', 'woocommerce_emsecommercecw_render_cancel');
	add_submenu_page(null, 'EMS eCommerce Gateway Refund', 'EMS eCommerce Gateway Refund', 'manage_woocommerce', 
			'woocommerce-emsecommercecw_refund', 'woocommerce_emsecommercecw_render_refund');
}
add_action('admin_menu', 'woocommerce_emsecommercecw_menu');

function woocommerce_emsecommercecw_render_cancel(){
	
	
	wp_redirect(get_option('siteurl') . '/wp-admin');
	
	
}

function woocommerce_emsecommercecw_render_capture(){
	
	
	wp_redirect(get_option('siteurl') . '/wp-admin');
	
	
}

function woocommerce_emsecommercecw_render_refund(){
	
	
	wp_redirect(get_option('siteurl') . '/wp-admin');
	
	
}

function woocommerce_emsecommercecw_extended_options(){
	$container = EMSeCommerceCw_Util::createContainer();
	$request = Customweb_Core_Http_ContextRequest::getInstance();
	$query = $request->getParsedQuery();
	$formName = substr($query['page'], strlen('woocommerce-emsecommercecw'));
	
	$renderer = new EMSeCommerceCw_BackendFormRenderer();
	
	if ($container->hasBean('Customweb_Payment_BackendOperation_Form_IAdapter')) {
		$adapter = $container->getBean('Customweb_Payment_BackendOperation_Form_IAdapter');
		
		foreach ($adapter->getForms() as $form) {
			if ($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}
		if ($currentForm === null) {
			if (isset($query['noheader'])) {
				require_once (ABSPATH . 'wp-admin/admin-header.php');
			}
			return;
		}
		
		if ($request->getMethod() == 'POST') {
			
			$pressedButton = null;
			$body = stripslashes_deep($request->getParsedBody());
			foreach ($form->getButtons() as $button) {
				
				if (array_key_exists($button->getMachineName(), $body['button'])) {
					$pressedButton = $button;
					break;
				}
			}
			$formData = array();
			foreach ($form->getElements() as $element) {
				$control = $element->getControl();
				if (!($control instanceof Customweb_Form_Control_IEditableControl)) {
					continue;
				}
				$dataValue = $control->getFormDataValue($body);
				if ($control instanceof Customweb_Form_Control_MultiControl) {
					foreach (woocommerce_emsecommercecw_array_flatten($dataValue) as $key => $value) {
						$formData[$key] = $value;
					}
				}
				else {
					$nameAsArray = $control->getControlNameAsArray();
					if (count($nameAsArray) > 1) {
						$tmpArray = array(
							$nameAsArray[count($nameAsArray) - 1] => $dataValue 
						);
						$iterator = count($nameAsArray) - 2;
						while ($iterator > 0) {
							$tmpArray = array(
								$nameAsArray[$iterator] => $tmpArray 
							);
							$iterator--;
						}
						if (isset($formData[$nameAsArray[0]])) {
							$formData[$nameAsArray[0]] = array_merge_recursive($formData[$nameAsArray[0]], $tmpArray);
						}
						else {
							$formData[$nameAsArray[0]] = $tmpArray;
						}
					}
					else {
						$formData[$control->getControlName()] = $dataValue;
					}
				}
			}
			$adapter->processForm($currentForm, $pressedButton, $formData);
			wp_redirect(Customweb_Util_Url::appendParameters($request->getUrl(), $request->getParsedQuery()));
			die();
		}
		
		if (isset($query['noheader'])) {
			require_once (ABSPATH . 'wp-admin/admin-header.php');
		}
		
		$currentForm = null;
		foreach ($adapter->getForms() as $form) {
			if ($form->getMachineName() == $formName) {
				$currentForm = $form;
				break;
			}
		}
		
		if ($currentForm->isProcessable()) {
			$currentForm = new Customweb_Form($currentForm);
			$currentForm->setRequestMethod(Customweb_IForm::REQUEST_METHOD_POST);
			$currentForm->setTargetUrl(
					Customweb_Util_Url::appendParameters($request->getUrl(), 
							array_merge($request->getParsedQuery(), array(
								'noheader' => 'true' 
							))));
		}
		echo '<div class="wrap">';
		echo $renderer->renderForm($currentForm);
		echo '</div>';
	}
}

function woocommerce_emsecommercecw_array_flatten($array){
	$return = array();
	foreach ($array as $key => $value) {
		if (is_array($value)) {
			$return = array_merge($return, woocommerce_emsecommercecw_array_flatten($value));
		}
		else {
			$return[$key] = $value;
		}
	}
	return $return;
}

/**
 * Setup the configuration page with the callbacks to the configuration API.
 */
function woocommerce_emsecommercecw_options(){
	if (!current_user_can('manage_woocommerce')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}
	require_once 'Customweb/Licensing/EMSeCommerceCw/License.php';
Customweb_Licensing_EMSeCommerceCw_License::run('m5j149a8rm4keqjq');
	echo '<div class="wrap">';
	
	echo '<form method="post" action="options.php" enctype="multipart/form-data">';
	settings_fields('woocommerce-emsecommercecw');
	do_settings_sections('woocommerce-emsecommercecw');
	
	echo '<p class="submit">';
	echo '<input type="submit" name="submit" id="submit" class="button-primary" value="' . __('Save Changes') . '" />';
	echo '</p>';
	
	echo '</form>';
	echo '</div>';
}



/**
 * Register Settings
 */
function woocommerce_emsecommercecw_admin_init(){
	add_settings_section('woocommerce_emsecommercecw', 'EMS eCommerce Gateway Basics', 
			'woocommerce_emsecommercecw_section_callback', 'woocommerce-emsecommercecw');
	add_settings_field('woocommerce_emsecommercecw_operation_mode', __("Operation Mode", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_operation_mode', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_operation_mode');
	
	add_settings_field('woocommerce_emsecommercecw_store_id', __("Store ID", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_store_id', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_store_id');
	
	add_settings_field('woocommerce_emsecommercecw_connect_shared_secret', __("Shared Secret Connect Interface provided by EMS eCommerce Gateway.", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_connect_shared_secret', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_connect_shared_secret');
	
	add_settings_field('woocommerce_emsecommercecw_order_id_schema', __("Order prefix", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_order_id_schema', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_order_id_schema');
	
	add_settings_field('woocommerce_emsecommercecw_three_d_secure_behavior', __("3d Secure Result", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_three_d_secure_behavior', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_three_d_secure_behavior');
	
	add_settings_field('woocommerce_emsecommercecw_pull_interval', __("Pull interval", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_pull_interval', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_pull_interval');
	
	add_settings_field('woocommerce_emsecommercecw_force_non_ssl', __("Force non SSL notifications", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_force_non_ssl', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_force_non_ssl');
	
	add_settings_field('woocommerce_emsecommercecw_page_mobile_detection', __("PaymentPage Mobile Detection", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_page_mobile_detection', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_page_mobile_detection');
	
	add_settings_field('woocommerce_emsecommercecw_test_store_id', __("Store ID (Test)", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_test_store_id', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_test_store_id');
	
	add_settings_field('woocommerce_emsecommercecw_test_connect_shared_secret', __("Shared Secret Connect Interface provided by EMS eCommerce Gateway (Test).", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_test_connect_shared_secret', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_test_connect_shared_secret');
	
	add_settings_field('woocommerce_emsecommercecw_review_input_form', __("Review Input Form", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_review_input_form', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_review_input_form');
	
	add_settings_field('woocommerce_emsecommercecw_order_identifier', __("Order Identifier", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_order_identifier', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_order_identifier');
	
	add_settings_field('woocommerce_emsecommercecw_external_checkout_placement', __("External Checkout: Widget Placement", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_external_checkout_placement', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_external_checkout_placement');
	
	add_settings_field('woocommerce_emsecommercecw_external_checkout_account_creation', __("External Checkout: Guest Checkout", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_external_checkout_account_creation', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_external_checkout_account_creation');
	
	add_settings_field('woocommerce_emsecommercecw_log_level', __("Log Level", 'woocommerce_emsecommercecw'), 'woocommerce_emsecommercecw_option_callback_log_level', 'woocommerce-emsecommercecw', 'woocommerce_emsecommercecw');
	register_setting('woocommerce-emsecommercecw', 'woocommerce_emsecommercecw_log_level');
	
	
}
add_action('admin_init', 'woocommerce_emsecommercecw_admin_init');

function woocommerce_emsecommercecw_section_callback(){}



function woocommerce_emsecommercecw_option_callback_operation_mode() {
	echo '<select name="woocommerce_emsecommercecw_operation_mode">';
		echo '<option value="test"';
		 if (get_option('woocommerce_emsecommercecw_operation_mode', "test") == "test"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Test Environment", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="live"';
		 if (get_option('woocommerce_emsecommercecw_operation_mode', "test") == "live"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Live Environment", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("You can switch between the different environments, by selecting the corresponding operation mode.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_store_id() {
	echo '<input type="text" name="woocommerce_emsecommercecw_store_id" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_store_id', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The Store ID will be provided by EMS eCommerce Gateway and usally consist of 11 digits.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_connect_shared_secret() {
	echo '<input type="text" name="woocommerce_emsecommercecw_connect_shared_secret" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_connect_shared_secret', ''),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The shared secret for the connect interface", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_order_id_schema() {
	echo '<input type="text" name="woocommerce_emsecommercecw_order_id_schema" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_order_id_schema', '{id}'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("Here you can insert an order prefix. The prefix allows you to change the order number that is transmitted to EMS eCommerce Gateway . The prefix must contain the tag {id}. It will then be replaced by the order number (e.g. name-{id}). Make sure that you do not use '_'.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_three_d_secure_behavior() {
	$currentValues = get_option('woocommerce_emsecommercecw_three_d_secure_behavior', array(
		0 => 'dirserver',
 		1 => 'authserver',
 		2 => 'attempted',
 	));
	echo '<select name="woocommerce_emsecommercecw_three_d_secure_behavior[]" multiple="multiple" >';
		echo '<option value="dirserver"';
		 if(!is_array($currentValues)){ 
			 $currentValues = array();
		}if (in_array("dirserver", $currentValues)){
			echo ' selected="selected" ';
		}
	echo '>' . __("Directory Server not responding", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="authserver"';
		 if(!is_array($currentValues)){ 
			 $currentValues = array();
		}if (in_array("authserver", $currentValues)){
			echo ' selected="selected" ';
		}
	echo '>' . __("Access Control Server not responding", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="notenrolled"';
		 if(!is_array($currentValues)){ 
			 $currentValues = array();
		}if (in_array("notenrolled", $currentValues)){
			echo ' selected="selected" ';
		}
	echo '>' . __("Cardholder not enrolled", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="attempted"';
		 if(!is_array($currentValues)){ 
			 $currentValues = array();
		}if (in_array("attempted", $currentValues)){
			echo ' selected="selected" ';
		}
	echo '>' . __("3D secure authorization attempted", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("During the authorization of the payment a 3D secure check may be done. The selected outcomes are treated as uncertain transactions.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_pull_interval() {
	echo '<input type="text" name="woocommerce_emsecommercecw_pull_interval" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_pull_interval', '15'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The interval in minutes between pulling updates from EMS eCommerce Gateway.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_force_non_ssl() {
	echo '<select name="woocommerce_emsecommercecw_force_non_ssl">';
		echo '<option value="false"';
		 if (get_option('woocommerce_emsecommercecw_force_non_ssl', "false") == "false"){
			echo ' selected="selected" ';
		}
	echo '>' . __("No", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="true"';
		 if (get_option('woocommerce_emsecommercecw_force_non_ssl', "false") == "true"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Yes", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Force EMS eCommerce Gateway to send the payment notification on a non SSL secured connection", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_page_mobile_detection() {
	echo '<select name="woocommerce_emsecommercecw_page_mobile_detection">';
		echo '<option value="responsive"';
		 if (get_option('woocommerce_emsecommercecw_page_mobile_detection', "responsive") == "responsive"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Responsive", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="mobile"';
		 if (get_option('woocommerce_emsecommercecw_page_mobile_detection', "responsive") == "mobile"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Detect Mobile", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("With this setting you can control how mobile devices shoulg be treated. If you use select 'Detect Mobile' the mobile optimized payment page is used, if the user is on a mobile device and the responsive otherwise. With the 'Responsive' option the responsive payment page is used for all users.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_test_store_id() {
	echo '<input type="text" name="woocommerce_emsecommercecw_test_store_id" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_test_store_id', '2320540003'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The Store ID will be provided by EMS eCommerce Gateway and usally consist of 11 digits. (Test)", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_test_connect_shared_secret() {
	echo '<input type="text" name="woocommerce_emsecommercecw_test_connect_shared_secret" value="' . htmlspecialchars(get_option('woocommerce_emsecommercecw_test_connect_shared_secret', 'M2E9hUFuwt'),ENT_QUOTES) . '" />';
	
	echo '<br />';
	echo __("The shared secret for the connect interface (Test)", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_review_input_form() {
	echo '<select name="woocommerce_emsecommercecw_review_input_form">';
		echo '<option value="active"';
		 if (get_option('woocommerce_emsecommercecw_review_input_form', "active") == "active"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Activate input form in review pane.", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="deactivate"';
		 if (get_option('woocommerce_emsecommercecw_review_input_form', "active") == "deactivate"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Deactivate input form in review pane.", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Should the input form for credit card data rendered in the review pane? To work the user must have JavaScript activated. In case the browser does not support JavaScript a fallback is provided. This feature is not supported by all payment methods.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_order_identifier() {
	echo '<select name="woocommerce_emsecommercecw_order_identifier">';
		echo '<option value="postid"';
		 if (get_option('woocommerce_emsecommercecw_order_identifier', "ordernumber") == "postid"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Post ID of the order", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="ordernumber"';
		 if (get_option('woocommerce_emsecommercecw_order_identifier', "ordernumber") == "ordernumber"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Order number", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Set which identifier should be sent to the payment service provider. If a plugin modifies the order number and can not guarantee it's uniqueness, select Post Id.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_external_checkout_placement() {
	echo '<select name="woocommerce_emsecommercecw_external_checkout_placement">';
		echo '<option value="both"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_placement', "both") == "both"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Cart and Checkout page", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="cart"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_placement', "both") == "cart"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Cart Page only", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="checkout"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_placement', "both") == "checkout"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Checkout Page only", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="custom"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_placement', "both") == "custom"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Custom Action", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Should the external checkout widgets be displayed on the cart page, checkout page, both, or placed with a custom action. If you use the Custom Action, you can display the widgets with through executing the action 'woocommerce_customweb_checkout_widgets' in your theme.", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_external_checkout_account_creation() {
	echo '<select name="woocommerce_emsecommercecw_external_checkout_account_creation">';
		echo '<option value="force_selection"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_account_creation', "skip_selection") == "force_selection"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Force Account Selection", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="skip_selection"';
		 if (get_option('woocommerce_emsecommercecw_external_checkout_account_creation', "skip_selection") == "skip_selection"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Create Guest Account when possible", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("When an external checkout is active the customer may need to authenticate. If the e-mail address does not exist in the database, should the customer be forced to select how he or she should create the account or should automatically an guest account be created?", 'woocommerce_emsecommercecw');
}

function woocommerce_emsecommercecw_option_callback_log_level() {
	echo '<select name="woocommerce_emsecommercecw_log_level">';
		echo '<option value="error"';
		 if (get_option('woocommerce_emsecommercecw_log_level', "error") == "error"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Error", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="info"';
		 if (get_option('woocommerce_emsecommercecw_log_level', "error") == "info"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Info", 'woocommerce_emsecommercecw'). '</option>';
	echo '<option value="debug"';
		 if (get_option('woocommerce_emsecommercecw_log_level', "error") == "debug"){
			echo ' selected="selected" ';
		}
	echo '>' . __("Debug", 'woocommerce_emsecommercecw'). '</option>';
	echo '</select>';
	echo '<br />';
	echo __("Messages of this or a higher level will be logged.", 'woocommerce_emsecommercecw');
}

