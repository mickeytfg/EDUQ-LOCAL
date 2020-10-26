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

require_once 'Customweb/Payment/Authorization/Recurring/ITransactionContext.php';
require_once 'EMSeCommerceCw/Util.php';
require_once 'EMSeCommerceCw/TransactionContext.php';


class EMSeCommerceCw_RecurringTransactionContext extends EMSeCommerceCw_TransactionContext implements Customweb_Payment_Authorization_Recurring_ITransactionContext
{
	protected $initialTransactionId;
	
	private $initialTransaction;
	
	public function __construct(EMSeCommerceCw_Entity_Transaction $transaction, EMSeCommerceCw_AbstractRecurringOrderContext $orderContext) {
		parent::__construct($transaction, $orderContext);
		$initialTransactionId = $orderContext->getInitialTransactionId(); 
		if(empty($initialTransactionId)){
			$initialTransaction = EMSeCommerceCw_Util::getAuthorizedTransactionByPostId($orderContext->getInitialOrderId());
			if(empty($initialTransaction)){
				$initialTransaction = EMSeCommerceCw_Util::getAuthorizedTransactionByOrderId($orderContext->getInitialOrderId());
			}
			if ($initialTransaction === NULL) {
				throw new Exception(sprintf("No initial transaction found for order %s.", $orderContext->getInitialOrderId()));
			}
			if(!$initialTransaction->getTransactionObject()->isAuthorized()){
				throw new Exception("The intial transaction was never authorized.");
			}
			$initialTransactionId = $initialTransaction->getTransactionId();
		}		
		$this->initialTransactionId = $initialTransactionId;
		
	}
	
	public function __sleep() {
		$fields = parent::__sleep();
		$fields[] = 'initialTransactionId';
		return $fields;
	}
	
	public function getInitialTransaction() {
		if ($this->initialTransaction === NULL) {
			$this->initialTransaction = EMSeCommerceCw_Util::getTransactionById($this->initialTransactionId);
		}
		return $this->initialTransaction->getTransactionObject();
	}
}