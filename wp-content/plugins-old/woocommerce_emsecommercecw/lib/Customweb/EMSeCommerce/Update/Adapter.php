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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/InquiryOrder.php';
require_once 'Customweb/Date/DateTime.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String100max.php';
require_once 'Customweb/Payment/Update/IAdapter.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/IPGApiActionRequest.php';
require_once 'Customweb/EMSeCommerce/SoapService.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Action.php';
require_once 'Customweb/EMSeCommerce/AbstractAdapter.php';
require_once 'Customweb/EMSeCommerce/IConstants.php';
require_once 'Customweb/EMSeCommerce/Util.php';



/**
 *
 * @author nicoeigenmann
 * @Bean
 */
class Customweb_EMSeCommerce_Update_Adapter extends Customweb_EMSeCommerce_AbstractAdapter implements Customweb_Payment_Update_IAdapter {
	
	const TRANSACTION_TIMEOUT = 172800;
	
	const TRANSACTION_UPDATE_INTERVAL = 600;
	
	/**
	 *
	 * @var Customweb_Payment_BackendOperation_Adapter_Shop_ICapture
	 */
	private $backendCaptureShopAdapter;
	
	/**
	 *
	 * @var Customweb_Payment_BackendOperation_Adapter_Shop_ICancel
	 */
	private $backendCancelShopAdapter;

	public function preprocessTransactionUpdate(){
		return array();
	}

	/**
	 * @Inject
	 */
	public function setBackendCaptureShopAdapter(Customweb_Payment_BackendOperation_Adapter_Shop_ICapture $adapter){
		$this->backendCaptureShopAdapter = $adapter;
	}

	public function getBackendCaptureShopAdapter(){
		return $this->backendCaptureShopAdapter;
	}

	public function updateTransaction(Customweb_Payment_Authorization_ITransaction $transaction){
		/* @var $transaction Customweb_EMSeCommerce_Authorization_Transaction */
		$config = $this->getConfiguration();
		
		$oid = $transaction->getPaymentId();
		$isMoto = $transaction->isMoto();
		
		$ipgAction = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_Action();
		$ipgAction->setInquiryOrder(
				Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_InquiryOrder::_()->setOrderId(
						Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max::_()->set($oid)));
		$actionRequest = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiActionRequest();
		$actionRequest->setAction($ipgAction);
		
		$service = new Customweb_EMSeCommerce_SoapService($this->getAPIUser($isMoto), $this->getAPIPassword($isMoto), $this->getAPIUrl());
		$service->setClientCertificate(Customweb_EMSeCommerce_Util::getCertificate($this->getConfiguration(), $isMoto));
		$service->setClientCertificatePassphrase($this->getCertificatePassphrase($isMoto));
		
		//Send XML handle response
		$errorMessage = null;
		try {
			$response = $service->iPGApiAction($actionRequest);
		}
		catch (Customweb_Soap_Exception_SoapFaultException $e) {
			
			if ($e->getFaultCode() == 'SOAP-ENV:Server') {
				$errorMessage = 'Server Error! Please contact EMS eCommerce Gateway';
			}
			elseif ($e->getFaultCode() == 'SOAP-ENV:Client') {
				$errorMessage = $e->getFaultDetail();
			}
			else {
				$errorMessage = 'Unkown Error';
			}
		}
		if (empty($errorMessage) && $response->getSuccessfully()->__toString() == "false") {
			$errorMessage = 'Error executing request';
		}
		
		if (!empty($errorMessage)) {
			$diff = time() - $transaction->getCreatedOn()->getTimestamp();
			if ($diff > self::TRANSACTION_TIMEOUT) {
				$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The customer does not finish the payment with in the timeout."));
			}
			else {
				$transaction->setUpdateExecutionDate(Customweb_Date_DateTime::_()->addSeconds(self::TRANSACTION_UPDATE_INTERVAL));
			}
			return;
		}
		if($response->getTransactionValues() === null){
			$transaction->setUpdateExecutionDate(Customweb_Date_DateTime::_()->addSeconds(self::TRANSACTION_UPDATE_INTERVAL));
			return;
		}
		
		$approvalCode = $response->getTransactionValues()->getIPGApiOrderResponse()->getApprovalCode();
		if ($approvalCode == null || substr($approvalCode->__toString(), 0, 1) == '?') {
			$transaction->setUpdateExecutionDate(Customweb_Date_DateTime::_()->addSeconds(self::TRANSACTION_UPDATE_INTERVAL));
			return;
		}
		$parameters = $transaction->getAuthorizationParameters();
		if (substr($approvalCode->__toString(), 0, 1) == 'Y') {
			//Authorization successful
			if (!$transaction->isAuthorized()) {
				$transaction->authorize();
			}
			$transaction->setAuthorizationUncertain(false);
			
			
			$paymentMethod = $this->getPaymentMethodFactory()->getPaymentMethod($transaction->getPaymentMethod(), 
					$transaction->getAuthorizationMethod());
			
			$threeDSecure = $transaction->getThreeDSecureState();
			
			if ($threeDSecure != null && in_array($threeDSecure, $config->getUncertain3dstates())) {
				$transaction->setAuthorizationUncertain();
			}
			$parameters['approval_code'] = $approvalCode->__toString();
			//Set Alias if requested
			if (isset($parameters['hosteddataid']) &&
					 (($config->isAliasManagerActive() && $paymentMethod->isAliasManagerSupported()) ||
					 $transaction->getTransactionContext()->createRecurringAlias())) {
				$transaction->setAlias($parameters['hosteddataid']);
				if (isset($parameters['cardnumber'])) {
					$transaction->setAliasForDisplay($parameters['cardnumber']);
				}
				elseif (isset($parameters['accountnumber'])) {
					$transaction->setAliasForDisplay('xx' . $parameters['accountnumber']);
				}
			}
			//Capture if necessary
			if ($parameters['txntype'] == Customweb_EMSeCommerce_IConstants::OPERATION_SALE) {
				if($transaction->isCapturePossible()) {
					$transaction->capture();
					try {
						$this->getBackendCaptureShopAdapter()->capture($transaction);
					}
					catch(Exception $e){
						$transaction->createHistoryItem(Customweb_I18n_Translation::__('Capture in Shopsystem failed.'));
					}
				}
			
				
			}
			$transaction->setUpdateExecutionDate(null);
		}
		if (substr($approvalCode->__toString(), 0, 1) == 'N') {
				$parameters['approval_code'] = $approvalCode->__toString();
				//Authorization failed, cancel
				if($transaction->isAuthorized()) {
					$transaction->setUncertainTransactionFinallyDeclined();
				}
				else {
					$transaction->setAuthorizationFailed();
				}
				$transaction->setUpdateExecutionDate(null);
		}
		$transaction->setAuthorizationParameters($parameters);
	}
}