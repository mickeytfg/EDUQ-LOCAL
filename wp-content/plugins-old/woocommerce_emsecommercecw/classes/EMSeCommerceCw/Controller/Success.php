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
require_once 'Customweb/Core/Util/System.php';
require_once 'EMSeCommerceCw/ContextRequest.php';
require_once 'EMSeCommerceCw/Controller/Abstract.php';


/**
 *
 * @author Nico Eigenmann
 *
 */
class EMSeCommerceCw_Controller_Success extends EMSeCommerceCw_Controller_Abstract {

	public function indexAction() {
			
		$parameters = EMSeCommerceCw_ContextRequest::getInstance()->getParameters();
		$dbTransaction = null;
		try {
			$dbTransaction = $this->loadTransaction($parameters);
		}
		catch(Exception $e) {
			return $this->formatErrorMessage($e->getMessage());
		}
	
		$start = time();
		$maxExecutionTime = Customweb_Core_Util_System::getMaxExecutionTime() - 10;
			
		if ($maxExecutionTime > 30) {
			$maxExecutionTime = 30;
		}
	
	
		$order = $dbTransaction->getTransactionObject()->getTransactionContext()->getOrderContext()->getOrderObject();
		$method = EMSeCommerceCw_Util::getPaymentMehtodInstance($dbTransaction->getPaymentClass());
		if (method_exists($method, 'get_return_url')) {
			$successUrl = $method->get_return_url($order);
		}
		else {
			$option = EMSeCommerceCw_Util::getShopOption('woocommerce_thanks_page_id');
			$option = apply_filters( 'woocommerce_get_thanks_page_id', $option);
			$checkout_redirect = apply_filters( 'woocommerce_get_checkout_redirect_page_id', $option );
			$orderId = null;
			$orderKey = null;
			
			if(method_exists($order, 'get_id')){
				$orderId= $order->get_id();
				$orderKey = $order->get_order_key();
			}
			else{
				$orderId= $order->id;
				$orderKey = $order->order_key;
			}
 
			$successUrl = add_query_arg('key', $orderKey, add_query_arg('order', $orderId, get_permalink(EMSeCommerceCw_Util::getPermalinkIdModified($checkout_redirect))));
		}
		// We have to close the session here otherwise the transaction may not be updated by the notification
		// callback.
		session_write_close();
	
		// Wait as long as the notification is done in the background
		while (true) {
	
	
			$dbTransaction = EMSeCommerceCw_Util::getTransactionById($parameters['cwtid'], false);
			$transactionObject = $dbTransaction->getTransactionObject();
	
			$url = null;
			if ($transactionObject->isAuthorizationFailed()) {
	
				$url = EMSeCommerceCw_Util::getPluginUrl('failure', array('cwtid' => $parameters['cwtid'], 'cwtt' => $parameters['cwtt']));
			}
			else if ($transactionObject->isAuthorized()) {
				global $woocommerce;
				$url = $successUrl;
				if (isset($woocommerce)) {
					$woocommerce->cart->empty_cart();
				}
			}
	
			if ($url !== null) {
				header('Location: ' . $url);
				die();
			}
	
			if (time() - $start > $maxExecutionTime) {
				ob_start();
				$GLOBALS['woo_emsecommercecwTitle'] = __('Time Out' , 'woocommerce_emsecommercecw');
				EMSeCommerceCw_Util::includeTemplateFile('timeout', array('successUrl' => $successUrl));
				$content = ob_get_clean();
				return $content;
			}
			else {
				// Wait 2 seconds for the next try.
				sleep(2);
			}
		}
	}
	
	
}