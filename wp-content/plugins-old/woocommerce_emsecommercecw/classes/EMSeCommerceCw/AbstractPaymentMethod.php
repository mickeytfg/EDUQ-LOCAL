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
require_once 'Customweb/Payment/Authorization/Widget/IAdapter.php';
require_once 'EMSeCommerceCw/Util.php';
require_once 'Customweb/Payment/Authorization/Iframe/IAdapter.php';
require_once 'EMSeCommerceCw/Entity/Transaction.php';
require_once 'Customweb/Payment/Authorization/Server/IAdapter.php';
require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';
require_once 'EMSeCommerceCw/CartOrderContext.php';
require_once 'EMSeCommerceCw/TransactionContext.php';
require_once 'Customweb/Form/Renderer.php';
require_once 'Customweb/Util/Html.php';
require_once 'EMSeCommerceCw/PaymentMethodWrapper.php';
require_once 'Customweb/Payment/Authorization/IPaymentMethod.php';
require_once 'Customweb/Payment/Authorization/Ajax/IAdapter.php';
require_once 'EMSeCommerceCw/OrderContext.php';
require_once 'EMSeCommerceCw/ConfigurationAdapter.php';
require_once 'EMSeCommerceCw/PaymentGatewayProxy.php';
require_once 'Customweb/Payment/Authorization/Hidden/IAdapter.php';



/**
 *        	  			  	 	   	
 * This class handlers the main payment interaction with the
 * EMSeCommerceCw server.
 */
abstract class EMSeCommerceCw_AbstractPaymentMethod extends EMSeCommerceCw_PaymentGatewayProxy implements 
		Customweb_Payment_Authorization_IPaymentMethod {
	public $class_name;
	public $id;
	public $title;
	public $chosen;
	public $has_fields = FALSE;
	public $countries;
	public $availability;
	public $enabled = 'no';
	public $icon;
	public $description;
	private $isCartTotalCalculated = FALSE;

	public function __construct(){
		$this->class_name = substr(get_class($this), 0, 39);
		
		$this->id = $this->class_name;
		$this->method_title = $this->admin_title;
		
		parent::__construct();
		
		$title = $this->getPaymentMethodConfigurationValue('title');
		if (!empty($title)) {
			$this->title = $title;
		}
		
		$this->description = $this->getPaymentMethodConfigurationValue('description');
	}

	public function getPaymentMethodName(){
		return $this->machineName;
	}

	public function getPaymentMethodDisplayName(){
		return $this->title;
	}

	public function receipt_page($order){}

	public function getBackendDescription(){
		return __('The configuration values for EMS eCommerce Gateway can be set under:', 'woocommerce_emsecommercecw') .
				 ' <a href="options-general.php?page=woocommerce-emsecommercecw">' .
				 __('EMS eCommerce Gateway Settings', 'woocommerce_emsecommercecw') . '</a>';
	}

	public function isAliasManagerActive(){
		$result = false;
		
		$result = ($this->getPaymentMethodConfigurationValue('alias_manager') == 'active');
		
		return $result;
	}

	public function getCurrentSelectedAlias(){
		$aliasTransactionId = null;
		
		if (isset($_REQUEST[$this->getAliasHTMLFieldName()])) {
			$aliasTransactionId = $_REQUEST[$this->getAliasHTMLFieldName()];
		}
		else if (isset($_POST['post_data'])) {
			parse_str($_POST['post_data'], $data);
			if (isset($data[$this->getAliasHTMLFieldName()])) {
				$aliasTransactionId = $data[$this->getAliasHTMLFieldName()];
			}
		}
		
		return $aliasTransactionId;
	}

	protected function showError($errorMessage){
		echo '<div class="woocommerce-error">' . $errorMessage . '</div>';
		die();
	}
	
	public function processShopPayment($orderPostId, $aliasTransactionId = NULL, $failedTransactionId = NULL, $failedValidate = null){
		require_once 'Customweb/Licensing/EMSeCommerceCw/License.php';
		$arguments = array(
			'orderPostId' => $orderPostId,
 			'failedTransactionId' => $failedTransactionId,
 			'aliasTransactionId' => $aliasTransactionId,
 			'failedValidate' => $failedValidate,
 		);
		return Customweb_Licensing_EMSeCommerceCw_License::run('fuobo9ud59iuf878', $this, $arguments);
	}

	final public function call_grsvovcvh6em07qs() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	
	
	public function processTransaction($orderPostId, $aliasTransactionId = NULL){
		require_once 'Customweb/Licensing/EMSeCommerceCw/License.php';
		$arguments = array(
			'orderPostId' => $orderPostId,
 			'aliasTransactionId' => $aliasTransactionId,
 		);
		return Customweb_Licensing_EMSeCommerceCw_License::run('qb9f5lai34r8ih15', $this, $arguments);
	}

	final public function call_b350qafobrutse4o() {
		$arguments = func_get_args();
		$method = $arguments[0];
		$call = $arguments[1];
		$parameters = array_slice($arguments, 2);
		if ($call == 's') {
			return call_user_func_array(array(get_class($this), $method), $parameters);
		}
		else {
			return call_user_func_array(array($this, $method), $parameters);
		}
		
		
	}
	

	
	/**
	 *
	 * @return EMSeCommerceCw_CartOrderContext
	 */
	protected function getCartOrderContext(){
		if (!isset($_POST['post_data'])) {
			return null;
		}
		
		parse_str($_POST['post_data'], $data);
		
		return new EMSeCommerceCw_CartOrderContext($data, new EMSeCommerceCw_PaymentMethodWrapper($this));
	}

	public function payment_fields(){
		parent::payment_fields();
		
		
		if ($this->isAliasManagerActive()) {
			$userId = get_current_user_id();
			$aliases = EMSeCommerceCw_Util::getAliasTransactions($userId, $this->getPaymentMethodName());
			
			if (count($aliases) > 0) {
				$selectedAlias = $this->getCurrentSelectedAlias();
				
				echo '<div class="emsecommercecw-alias-input-box"><div class="alias-field-description">' .
						 __('You can choose a previous used card:', 'woocommerce_emsecommercecw') . '</div>';
				echo '<select name="' . $this->getAliasHTMLFieldName() . '">';
				echo '<option value="new"> -' . __('Select card', 'woocommerce_emsecommercecw') . '- </option>';
				foreach ($aliases as $aliasTransaction) {
					echo '<option value="' . $aliasTransaction->getTransactionId() . '"';
					if ($selectedAlias == $aliasTransaction->getTransactionId()) {
						echo ' selected="selected" ';
					}
					echo '>' . $aliasTransaction->getAliasForDisplay() . '</option>';
				}
				echo '</select></div>';
			}
			else {
				echo '<div class="emsecommercecw-alias-hidden-new"><input type="hidden" name="' . $this->getAliasHTMLFieldName() .
						 '" value="new" /></div>';
			}
		}
		
		

		$orderContext = $this->getCartOrderContext();
		if ($orderContext !== null) {
			$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
			$aliasTransactionObject = null;
			
			if ($this->isAliasManagerActive()) {
				$aliasTransactionObject = "new";
				$selectedAlias = $this->getCurrentSelectedAlias();
				if ($selectedAlias !== null) {
					$aliasTransaction = EMSeCommerceCw_Util::getTransactionById($selectedAlias);
					if ($aliasTransaction !== null && $aliasTransaction->getCustomerId() == get_current_user_id()) {
						$aliasTransactionObject = $aliasTransaction->getTransactionObject();
					}
				}
			}
			
			
			echo $this->getReviewFormFields($orderContext, $aliasTransactionObject);
		}
	}
	
	
	public function getAliasHTMLFieldName(){
		return 'emsecommercecw_alias_' . $this->getPaymentMethodName();
	}
	
	public function has_fields(){
		$fields = parent::has_fields();
		
		if ($this->isAliasManagerActive()) {
			$userId = get_current_user_id();
			$aliases = EMSeCommerceCw_Util::getAliasTransactions($userId, $this->getPaymentMethodName());
			
			if (count($aliases) > 0) {
				return true;
			}
		}
		
		$orderContext = $this->getCartOrderContext();
		if ($orderContext !== null) {
			$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
			$aliasTransactionObject = null;
			
			if ($this->isAliasManagerActive()) {
				$aliasTransactionObject = "new";
				$selectedAlias = $this->getCurrentSelectedAlias();
				if ($selectedAlias !== null) {
					$aliasTransaction = EMSeCommerceCw_Util::getTransactionById($selectedAlias);
					if ($aliasTransaction !== null && $aliasTransaction->getCustomerId() == get_current_user_id()) {
						$aliasTransactionObject = $aliasTransaction->getTransactionObject();
					}
				}
			}
			
			$generated = $this->getReviewFormFields($orderContext, $aliasTransactionObject);
			if (!empty($generated)) {
				return true;
			}
		}
		return $fields;
	}

	/**
	 * This function creates a new Transaction
	 *
	 * @param EMSeCommerceCw_OrderContext $order
	 * @return EMSeCommerceCw_Entity_Transaction
	 */
	public function newDatabaseTransaction(EMSeCommerceCw_OrderContext $orderContext){
		$dbTransaction = new EMSeCommerceCw_Entity_Transaction();
		
		$this->destroyCheckoutId();
		
		$dbTransaction->setPostId($orderContext->getOrderPostId())->setOrderId($orderContext->getOrderNumber())->setCustomerId($orderContext->getCustomerId())->setPaymentClass(get_class($this))->setPaymentMachineName(
				$this->getPaymentMethodName());
		EMSeCommerceCw_Util::getEntityManager()->persist($dbTransaction);
		return $dbTransaction;
	}

	/**
	 * This function creates a new Transaction and transaction object and persists them in the DB
	 *
	 * @param EMSeCommerceCw_OrderContext $orderContext
	 * @param Customweb_Payment_Authorization_ITransactionContext | null $aliasTransaction
	 * @param Customweb_Payment_Authorization_ITransactionContext |null $failedTransaction
	 * @return EMSeCommerceCw_Entity_Transaction
	 */
	public function prepare(EMSeCommerceCw_OrderContext $orderContext, $aliasTransaction = null, $failedTransaction = null){
		$dbTransaction = $this->newDatabaseTransaction($orderContext);
		$transactionContext = $this->newTransactionContext($dbTransaction, $orderContext, $aliasTransaction);
		$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
		$transaction = $adapter->createTransaction($transactionContext, $failedTransaction);
		$dbTransaction->setTransactionObject($transaction);
		return EMSeCommerceCw_Util::getEntityManager()->persist($dbTransaction);
	}

	public function newTransactionContext(EMSeCommerceCw_Entity_Transaction $dbTransaction, $orderContext, $aliasTransaction = null){
		return new EMSeCommerceCw_TransactionContext($dbTransaction, $orderContext, $aliasTransaction);
	}

	/**
	 * This method generates a HTML form for each payment method.
	 */
	public function createMethodFormFields(){
		return array(
			'enabled' => array(
				'title' => __('Enable/Disable', 'woocommerce_emsecommercecw'),
				'type' => 'checkbox',
				'label' => sprintf(__('Enable %s', 'woocommerce_emsecommercecw'), $this->admin_title),
				'default' => 'no' 
			),
			'title' => array(
				'title' => __('Title', 'woocommerce_emsecommercecw'),
				'type' => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'woocommerce_emsecommercecw'),
				'default' => __($this->title, 'woocommerce_emsecommercecw') 
			),
			'description' => array(
				'title' => __('Description', 'woocommerce_emsecommercecw'),
				'type' => 'textarea',
				'description' => __('This controls the description which the user sees during checkout.', 'woocommerce_emsecommercecw'),
				'default' => sprintf(
						__("Pay with %s over the interface of EMS eCommerce Gateway.", 'woocommerce_emsecommercecw'), 
						$this->title) 
			),
			'min_total' => array(
				'title' => __('Minimal Order Total', 'woocommerce_emsecommercecw'),
				'type' => 'text',
				'description' => __(
						'Set here the minimal order total for which this payment method is available. If it is set to zero, it is always available.', 
						'woocommerce_emsecommercecw'),
				'default' => 0 
			),
			'max_total' => array(
				'title' => __('Maximal Order Total', 'woocommerce_emsecommercecw'),
				'type' => 'text',
				'description' => __(
						'Set here the maximal order total for which this payment method is available. If it is set to zero, it is always available.', 
						'woocommerce_emsecommercecw'),
				'default' => 0 
			) 
		);
	}

	protected function getOrderStatusOptions($statuses = array()){
		$terms = get_terms('shop_order_status', array(
			'hide_empty' => 0,
			'orderby' => 'id' 
		));
		
		foreach ($statuses as $k => $value) {
			$statuses[$k] = __($value, 'woocommerce_emsecommercecw');
		}
		
		foreach ($terms as $term) {
			$statuses[$term->slug] = $term->name;
		}
		return $statuses;
	}

	protected function getReviewFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction){
		if (EMSeCommerceCw_ConfigurationAdapter::isReviewFormInputActive()) {
			$paymentContext = EMSeCommerceCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
			$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
			$fields = array();
			if (method_exists($adapter, 'getVisibleFormFields')) {
				$fields = $adapter->getVisibleFormFields($orderContext, $aliasTransaction, null, $paymentContext);
			}
			EMSeCommerceCw_Util::persistPaymentCustomerContext($paymentContext);
			
			$result = '<div class="emsecommercecw-preview-fields';
			if (!($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter ||
					 $adapter instanceof Customweb_Payment_Authorization_Hidden_IAdapter)) {
				$result .= ' emsecommercecw-validate';
			}
			$result .= '">';
			
			$result .= $this->getCompatibilityFormFields();
			
			if ($fields !== null && count($fields) > 0) {
				$renderer = new Customweb_Form_Renderer();
				$renderer->setRenderOnLoadJs(false);
				$renderer->setNameSpacePrefix('emsecommercecw_' . $orderContext->getPaymentMethod()->getPaymentMethodName());
				$renderer->setCssClassPrefix('emsecommercecw-');
				
				$result .= $renderer->renderElements($fields) . '</div>';
			}
			else {
				$result .= '</div>';
			}
			return $result;
		}
		
		return '';
	}

	public function getFormActionUrl(EMSeCommerceCw_OrderContext $orderContext){
		$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
		$identifiers = array(
			'cwoid' => $orderContext->getOrderPostId(),
			'cwot' => EMSeCommerceCw_Util::computeOrderValidationHash($orderContext->getOrderPostId()) 
		);
		if ($adapter instanceof Customweb_Payment_Authorization_Iframe_IAdapter) {
			return EMSeCommerceCw_Util::getPluginUrl('iframe', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_Widget_IAdapter) {
			return EMSeCommerceCw_Util::getPluginUrl('widget', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_PaymentPage_IAdapter) {
			return EMSeCommerceCw_Util::getPluginUrl('redirection', $identifiers);
		}
		if ($adapter instanceof Customweb_Payment_Authorization_Server_IAdapter) {
			return EMSeCommerceCw_Util::getPluginUrl('authorize', $identifiers);
		}
	}

	protected function getCheckoutFormVaiables(EMSeCommerceCw_OrderContext $orderContext, $aliasTransaction, $failedTransaction){
		$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
		
		$visibleFormFields = array();
		if (method_exists($adapter, 'getVisibleFormFields')) {
			
			$customerContext = EMSeCommerceCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
			$visibleFormFields = $adapter->getVisibleFormFields($orderContext, $aliasTransaction, $failedTransaction, $customerContext);
			EMSeCommerceCw_Util::persistPaymentCustomerContext($customerContext);
		}
		
		$html = '';
		if ($visibleFormFields !== null && count($visibleFormFields) > 0) {
			$renderer = new Customweb_Form_Renderer();
			$renderer->setCssClassPrefix('emsecommercecw-');
			$html = $renderer->renderElements($visibleFormFields);
		}
		
		if ($adapter instanceof Customweb_Payment_Authorization_Ajax_IAdapter) {
			$dbTransaction = $this->prepare($orderContext, $aliasTransaction, $failedTransaction);
			$ajaxScriptUrl = $adapter->getAjaxFileUrl($dbTransaction->getTransactionObject());
			$callbackFunction = $adapter->getJavaScriptCallbackFunction($dbTransaction->getTransactionObject());
			EMSeCommerceCw_Util::getEntityManager()->persist($dbTransaction);
			return array(
				'visible_fields' => $html,
				'template_file' => 'payment_confirmation_ajax',
				'ajaxScriptUrl' => (string) $ajaxScriptUrl,
				'submitCallbackFunction' => $callbackFunction 
			);
		}
		
		if ($adapter instanceof Customweb_Payment_Authorization_Hidden_IAdapter) {
			$dbTransaction = $this->prepare($orderContext, $aliasTransaction, $failedTransaction);
			$formActionUrl = $adapter->getFormActionUrl($dbTransaction->getTransactionObject());
			$hiddenFields = Customweb_Util_Html::buildHiddenInputFields($adapter->getHiddenFormFields($dbTransaction->getTransactionObject()));
			EMSeCommerceCw_Util::getEntityManager()->persist($dbTransaction);
			return array(
				'form_target_url' => $formActionUrl,
				'hidden_fields' => $hiddenFields,
				'visible_fields' => $html,
				'template_file' => 'payment_confirmation' 
			);
		}
		
		return array(
			'form_target_url' => $this->getFormActionUrl($orderContext),
			'visible_fields' => $html,
			'template_file' => 'payment_confirmation' 
		);
	}

	public function validate(array $formData){
		$orderContext = new EMSeCommerceCw_CartOrderContext($formData, new EMSeCommerceCw_PaymentMethodWrapper($this));
		$paymentContext = EMSeCommerceCw_Util::getPaymentCustomerContext($orderContext->getCustomerId());
		$adapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
		
		// Validate transaction
		$errorMessage = null;
		try {
			if (EMSeCommerceCw_ConfigurationAdapter::isReviewFormInputActive() && isset($_REQUEST['emsecommercecw-preview-fields'])) {
				$adapter->validate($orderContext, $paymentContext, $formData);
			}
		}
		catch (Exception $e) {
			$errorMessage = $e->getMessage();
		}
		EMSeCommerceCw_Util::persistPaymentCustomerContext($paymentContext);
		
		if ($errorMessage !== null) {
			throw new Exception($errorMessage);
		}
	}

	protected abstract function getCompatibilityFormFields();
	
	
	protected abstract function destroyCheckoutId();
}
