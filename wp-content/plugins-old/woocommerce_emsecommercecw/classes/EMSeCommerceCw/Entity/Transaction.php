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

require_once 'Customweb/Payment/Authorization/Recurring/ITransactionContext.php';
require_once 'EMSeCommerceCw/Util.php';
require_once 'Customweb/Payment/Authorization/ITransaction.php';
require_once 'Customweb/Payment/Entity/AbstractTransaction.php';
require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';

/**
 * This class represents a transaction.
 *
 * @author Thomas Hunziker
 *
 *
 * @Entity(tableName = 'woocommerce_emsecommercecw_transactions')
 * @Filter(name = 'loadByPostId', where = 'postId = >postId', orderBy = 'postId')
 */
class EMSeCommerceCw_Entity_Transaction extends Customweb_Payment_Entity_AbstractTransaction {
	private $paymentClass = null;
	private $postId = null;

	/**
	 * @Column(type = 'varchar')
	 */
	public function getPaymentClass(){
		return $this->paymentClass;
	}

	public function setPaymentClass($paymentClass){
		$this->paymentClass = $paymentClass;
		return $this;
	}
	
	/**
	 * @Column(type = 'varchar')
	 */
	public function getPostId(){
		return $this->postId;
	}
	
	public function setPostId($postId){
		$this->postId = $postId;
		return $this;
	}

	public function getOrder(){
		// We load the order object always fresh from the database, to make sure,
		// that no old status is shared between the different usages.
		$orderPostId = $this->getPostId();
		if(empty($orderPostId)) {
			return EMSeCommerceCw_Util::loadOrderObjectById($this->getOrderId());
		}
		return EMSeCommerceCw_Util::loadOrderObjectById($orderPostId);
		
	}

	public function onBeforeSave(Customweb_Database_Entity_IManager $entityManager){
		if($this->isSkipOnSafeMethods()){
			return;
		}
		$transactionObject = $this->getTransactionObject();
		// In case a order is associated with this transaction and the authorization failed, we have to cancel the order.
		if ($transactionObject !== null && $transactionObject instanceof Customweb_Payment_Authorization_ITransaction &&
				 $transactionObject->isAuthorizationFailed()) {
			$this->forceTransactionFailing();
		}
		return parent::onBeforeSave($entityManager);
	}

	protected function updateOrderStatus(Customweb_Database_Entity_IManager $entityManager, $orderStatus, $orderStatusSettingKey){
		
		if (class_exists('WC_Subscriptions') && version_compare(WC_Subscriptions::$version, '2.0') < 0) {
			if ($this->getTransactionObject()->getAuthorizationMethod() ==
					 Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
				return;
			}
		}
		
		
		//Switch language to the transaction language so emails are translated correctly
		$this->changeLanguage();		
		$GLOBALS['_woocommerce_emsecommercecw__status_change'] = true;
		$order = $this->getOrder();
		$paymentMethod = EMSeCommerceCw_Util::getPaymentMehtodInstance($this->getPaymentClass());
		if ($orderStatusSettingKey != 'status_authorized' || $paymentMethod->getPaymentMethodConfigurationValue('status_authorized') != 'use-default') {
			$order->update_status($orderStatus, __('Payment Notification', 'woocommerce_emsecommercecw'));
		}
		//Restore the original language
		$this->restoreLanguage();
		unset($GLOBALS['_woocommerce_emsecommercecw__status_change']);
	}

	protected function authorize(Customweb_Database_Entity_IManager $entityManager){
		if ($this->getTransactionObject()->isAuthorized()) {
			$GLOBALS['_woocommerce_emsecommercecw__status_change'] = true;
			$orderPostId = $this->getPostId();
			if(empty($orderPostId)) {
				$orderPostId = $this->getOrderId();
			}
			
			//Switch language to the transaction language so emails are translated correctly
			$this->changeLanguage();
			delete_post_meta( $orderPostId, '_emsecommercecw_pending_state' );
			
			// Ensure that the mail is send to the administrator
						
			if (class_exists('WC_Subscriptions') && version_compare(WC_Subscriptions::$version, '2.0') >= 0) {
				if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
						$this->getOrder()->update_status('wc-pending');
					}
					else {
						$this->getOrder()->update_status('pending');
					}
			
			}
			if($this->getTransactionObject()->getAuthorizationMethod() != Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
				if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '2.2.0') >= 0) {
					$this->getOrder()->update_status('wc-pending');
				}
				else {
					$this->getOrder()->update_status('pending');
				}
			}
			
			
			
			
			
			if (class_exists('WC_Subscriptions') && version_compare(WC_Subscriptions::$version, '2.0') >= 0) {
				apply_filters('woocommerce_payment_successful_result', array(
					'result' => 'success' 
				), $orderPostId);
				if($this->getOrder() instanceof WC_Order){
					$this->getOrder()->payment_complete($this->getTransactionObject()->getPaymentId());
				}
				else{
					$this->getOrder()->payment_complete();
				}
				if (wcs_order_contains_subscription($this->getOrder())) {
					if ($this->getTransactionObject()->getTransactionContext()->createRecurringAlias()) {
						$subscriptions = wcs_get_subscriptions(array(
							'order_id' => $orderPostId 
						));
						$subscriptions = wcs_get_subscriptions_for_order( $orderPostId, array( 'order_type' => array( 'parent', 'renewal' )));
						foreach ($subscriptions as $subscription) {
							update_post_meta($subscription->id, 'cwInitialTransactionRecurring', $this->getTransactionId());
						}
					}
				}
			}
			else {
				if (class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($orderPostId)) {
					$subscriptionKey = WC_Subscriptions_Manager::get_subscription_key($orderPostId);
					$subscription = WC_Subscriptions_Manager::get_subscription($subscriptionKey);
					if ($subscription['status'] != 'active') {
						WC_Subscriptions_Manager::update_subscription($subscriptionKey, array(
							'status' => 'pending' 
						));
					}
				}
				if ($this->getTransactionObject()->getAuthorizationMethod() !=
						 Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
					// Mark the order as completed
					apply_filters('woocommerce_payment_successful_result', array(
						'result' => 'success' 
					), $orderPostId);
					 if($this->getOrder() instanceof WC_Order){
						$this->getOrder()->payment_complete($this->getTransactionObject()->getPaymentId());
					}
					else{
						$this->getOrder()->payment_complete();
					}
				}
				if (class_exists('WC_Subscriptions_Order') && WC_Subscriptions_Order::order_contains_subscription($orderPostId)) {
					if($this->getTransactionObject()->getTransactionContext()->createRecurringAlias()){
						update_post_meta($orderPostId, 'cwInitialTransactionRecurring', $this->getTransactionId());
					}
					$transactionContext = $this->getTransactionObject()->getTransactionContext();
					if ($transactionContext instanceof Customweb_Payment_Authorization_Recurring_ITransactionContext) {
						update_post_meta($orderPostId, 'cwInitialTransactionRecurring', $transactionContext->getInitialTransaction()->getTransactionId());
					}
					WC_Subscriptions_Manager::activate_subscriptions_for_order($this->getOrder());
				}
			}
			
			
			$this->restoreLanguage();
			unset($GLOBALS['_woocommerce_emsecommercecw__status_change']);
		}
	}

	protected function forceTransactionFailing(){
		$GLOBALS['_woocommerce_emsecommercecw__status_change'] = true;
		$message = current($this->getTransactionObject()->getErrorMessages());
		
		if(EMSeCommerceCw_Util::getAuthorizedTransactionByPostId($this->getPostId()) !== null){
			//Another Transaction has already sucessfuly authorized this transaction, do not mark the order as cancelled
			return;
		}
		$this->getOrder()->add_order_note(__('Error Message: ', 'woocommerce_emsecommercecw') . $message);
		$orderId = null;
		if(method_exists($this->getOrder(), 'get_id')){
			$orderId= $this->getOrder()->get_id();
		}
		else{
			$orderId= $this->getOrder()->id;
		}
		delete_post_meta( $orderId, '_emsecommercecw_pending_state' );
		
		if (class_exists('WC_Subscriptions') && version_compare(WC_Subscriptions::$version, '2.0') >= 0) {
			if ($this->getAuthorizationType() != Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
				if($this->getTransactionObject()->getTransactionContext()->createRecurringAlias()) {
					//Activate hook to remove subscription cancel email, if the initial transaction failed
					add_action( 'woocommerce_email', 'woocommerce_emsecommercecw_unhook_subscription_cancel' );
				}
				$this->cancelOrderChecked();
			}
			else {
				$this->getOrder()->update_status('cancelled');
			}
			
		}
		else {
			if ($this->getAuthorizationType() != Customweb_Payment_Authorization_Recurring_IAdapter::AUTHORIZATION_METHOD_NAME) {
				//If it is not a recurring transaction we cancel the order
				$this->cancelOrderChecked();
			}
		}
			
		
		unset($GLOBALS['_woocommerce_emsecommercecw__status_change']);
	}

	
	private function changeLanguage(){
		global $sitepress, $originalWpmlCw;
		if (isset($sitepress)) {
			$originalWpmlCw = $sitepress->get_current_language();
			$sitepress->switch_lang($this->getTransactionObject()->getTransactionContext()->getOrderContext()->getLanguage()->getIso2LetterCode(), false);
		}
		$GLOBALS['woo_emsecommercecwAuthorizeLanguage'] = $this->getTransactionObject()->getTransactionContext()->getOrderContext()->getLanguage()->getIetfCode();
		add_filter('locale', 'woocommerce_emsecommercecw_locale');
	}
	
	private function restoreLanguage(){
		global $sitepress, $originalWpmlCw;
		if (isset($sitepress) && !empty($originalWpml)) {
			$sitepress->switch_lang($originalWpml, false);
		}
		remove_filter('locale', 'woocommerce_emsecommercecw_locale');
	}
	
	private function cancelOrderChecked(){
				
		if (defined('WOOCOMMERCE_VERSION') && version_compare(WOOCOMMERCE_VERSION, '3.0') >= 0) {
			$this->getOrder()->update_status('cancelled');
		}
		else{
			if(WC()->session == null){
				$session_class  = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );
				WC()->session  = new $session_class();
			}
			$this->getOrder()->cancel_order();
		}
		
	}
}

