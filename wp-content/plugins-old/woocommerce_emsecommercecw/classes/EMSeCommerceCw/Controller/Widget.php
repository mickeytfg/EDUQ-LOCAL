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
require_once 'Customweb/Payment/Authorization/Widget/IAdapter.php';
require_once 'EMSeCommerceCw/Util.php';
require_once 'EMSeCommerceCw/ContextRequest.php';
require_once 'EMSeCommerceCw/OrderContext.php';
require_once 'EMSeCommerceCw/Controller/Abstract.php';
require_once 'EMSeCommerceCw/PaymentMethodWrapper.php';


/**
 *
 * @author Nico Eigenmann
 *
 */
class EMSeCommerceCw_Controller_Widget extends EMSeCommerceCw_Controller_Abstract {

	
	public function indexAction() {
				
		$parameters = EMSeCommerceCw_ContextRequest::getInstance()->getParameters();
		if(!isset($parameters['cwsubmit'])|| $parameters['cwsubmit'] != 'true') {
			return;
		}
		try {
			$order = $this->loadOrder($parameters);
		}
		catch(Exception $e) {
			return $this->formatErrorMessage($e->getMessage());
		}
		
		$paymentModule = $this->getPaymentMethodModule($order);
	
		if ($paymentModule === NULL) {
			return $this->formatErrorMessage(__('Could not load payment module.', 'woocommerce_emsecommercecw'));
		}
	
		$orderContext = new EMSeCommerceCw_OrderContext($order, new EMSeCommerceCw_PaymentMethodWrapper($paymentModule));
		
		$authorizationAdapter = EMSeCommerceCw_Util::getAuthorizationAdapterByContext($orderContext);
		
		if (!($authorizationAdapter instanceof Customweb_Payment_Authorization_Widget_IAdapter)) {
			return $this->formatErrorMessage(__('Wrong authorization type.', 'woocommerce_emsecommercecw'));
		}
		
		$this->validateTransaction($orderContext, $authorizationAdapter, $parameters);
		
		$aliasTransaction = $this->getAlias($parameters, $orderContext->getCustomerId());
		$failedTransaction = $this->getFailed($parameters);
		
		$dbTransaction = $paymentModule->prepare($orderContext, $aliasTransaction, $failedTransaction);
	
		$variables = array(
			'widget_html' => $authorizationAdapter->getWidgetHTML($dbTransaction->getTransactionObject(), $parameters),
		);
		EMSeCommerceCw_Util::getEntityManager()->persist($dbTransaction);
		
		if($dbTransaction->getTransactionObject()->isAuthorizationFailed()){
			$url = EMSeCommerceCw_Util::getPluginUrl('failure', array('cwtid' => $dbTransaction->getTransactionId(), 'cwtt' => EMSeCommerceCw_Util::computeTransactionValidateHash($dbTransaction)));
			header('Location: ' . $url);
			die();
		}
		
		ob_start();
		EMSeCommerceCw_Util::includeTemplateFile('payment_widget', $variables);
		$content = ob_get_clean();
		return $content;
	}
	
	
}