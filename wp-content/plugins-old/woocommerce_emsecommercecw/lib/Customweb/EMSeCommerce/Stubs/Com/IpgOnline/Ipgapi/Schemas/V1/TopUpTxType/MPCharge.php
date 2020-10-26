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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MNSP.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MSISDN.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PaymentType.php';
/**
 * @XmlType(name="MPCharge", namespace="http://ipg-online.com/ipgapi/schemas/v1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge {
	/**
	 * @XmlElement(name="MNSP", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP
	 */
	private $mNSP;
	
	/**
	 * @XmlElement(name="MSISDN", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN
	 */
	private $mSISDN;
	
	/**
	 * @XmlElement(name="PaymentType", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType
	 */
	private $paymentType;
	
	public function __construct() {
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge();
		return $i;
	}
	/**
	 * Returns the value for the property mNSP.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP
	 */
	public function getMNSP(){
		return $this->mNSP;
	}
	
	/**
	 * Sets the value for the property mNSP.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP $mNSP
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge
	 */
	public function setMNSP($mNSP){
		if ($mNSP instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP) {
			$this->mNSP = $mNSP;
		}
		else {
			throw new BadMethodCallException("Type of argument mNSP must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MNSP.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property mSISDN.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN
	 */
	public function getMSISDN(){
		return $this->mSISDN;
	}
	
	/**
	 * Sets the value for the property mSISDN.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN $mSISDN
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge
	 */
	public function setMSISDN($mSISDN){
		if ($mSISDN instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN) {
			$this->mSISDN = $mSISDN;
		}
		else {
			throw new BadMethodCallException("Type of argument mSISDN must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_MSISDN.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property paymentType.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType
	 */
	public function getPaymentType(){
		return $this->paymentType;
	}
	
	/**
	 * Sets the value for the property paymentType.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType $paymentType
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TopUpTxType_MPCharge
	 */
	public function setPaymentType($paymentType){
		if ($paymentType instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType) {
			$this->paymentType = $paymentType;
		}
		else {
			throw new BadMethodCallException("Type of argument paymentType must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PaymentType.");
		}
		return $this;
	}
	
	
	
}