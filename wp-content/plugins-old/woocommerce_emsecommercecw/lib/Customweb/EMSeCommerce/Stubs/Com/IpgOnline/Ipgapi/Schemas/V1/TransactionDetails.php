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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/AirlineDetails.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CarRental.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/HotelLodging.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String1024max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String48max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String100max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String128max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String40max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PositiveNumeric14max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/OfflineApprovalType.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Ip.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ReferenceNumber.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SplitShipment.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TDate.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TransactionOrigin.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Terminal.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/InquiryRateReference.php';
require_once 'Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/String.php';
/**
 * @XmlType(name="TransactionDetails", namespace="http://ipg-online.com/ipgapi/schemas/v1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails {
	/**
	 * @XmlElement(name="AirlineDetails", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails
	 */
	private $airlineDetails;
	
	/**
	 * @XmlElement(name="CarRental", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental
	 */
	private $carRental;
	
	/**
	 * @XmlElement(name="HotelLodging", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging
	 */
	private $hotelLodging;
	
	/**
	 * @XmlElement(name="Comments", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max
	 */
	private $comments;
	
	/**
	 * @XmlElement(name="InvoiceNumber", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max
	 */
	private $invoiceNumber;
	
	/**
	 * @XmlElement(name="DynamicMerchantName", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max
	 */
	private $dynamicMerchantName;
	
	/**
	 * @XmlElement(name="PONumber", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max
	 */
	private $pONumber;
	
	/**
	 * @XmlElement(name="OrderId", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max
	 */
	private $orderId;
	
	/**
	 * @XmlElement(name="MerchantTransactionId", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max
	 */
	private $merchantTransactionId;
	
	/**
	 * @XmlElement(name="ReferencedMerchantTransactionId", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max
	 */
	private $referencedMerchantTransactionId;
	
	/**
	 * @XmlElement(name="IpgTransactionId", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max
	 */
	private $ipgTransactionId;
	
	/**
	 * @XmlElement(name="OfflineApprovalType", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType
	 */
	private $offlineApprovalType;
	
	/**
	 * @XmlElement(name="Ip", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip
	 */
	private $ip;
	
	/**
	 * @XmlElement(name="ReferenceNumber", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber
	 */
	private $referenceNumber;
	
	/**
	 * @XmlElement(name="SplitShipment", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment
	 */
	private $splitShipment;
	
	/**
	 * @XmlElement(name="TDate", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate
	 */
	private $tDate;
	
	/**
	 * @XmlElement(name="TransactionOrigin", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin
	 */
	private $transactionOrigin = 'ECI';
	
	/**
	 * @XmlElement(name="Terminal", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal
	 */
	private $terminal;
	
	/**
	 * @XmlElement(name="InquiryRateReference", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference
	 */
	private $inquiryRateReference;
	
	/**
	 * @XmlValue(name="Signature", simpleType=@XmlSimpleTypeDefinition(typeName='base64Binary', typeNamespace='http://www.w3.org/2001/XMLSchema', type='Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_String'), namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var string
	 */
	private $signature;
	
	public function __construct() {
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails();
		return $i;
	}
	/**
	 * Returns the value for the property airlineDetails.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails
	 */
	public function getAirlineDetails(){
		return $this->airlineDetails;
	}
	
	/**
	 * Sets the value for the property airlineDetails.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails $airlineDetails
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setAirlineDetails($airlineDetails){
		if ($airlineDetails instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails) {
			$this->airlineDetails = $airlineDetails;
		}
		else {
			throw new BadMethodCallException("Type of argument airlineDetails must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_AirlineDetails.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property carRental.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental
	 */
	public function getCarRental(){
		return $this->carRental;
	}
	
	/**
	 * Sets the value for the property carRental.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental $carRental
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setCarRental($carRental){
		if ($carRental instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental) {
			$this->carRental = $carRental;
		}
		else {
			throw new BadMethodCallException("Type of argument carRental must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_CarRental.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property hotelLodging.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging
	 */
	public function getHotelLodging(){
		return $this->hotelLodging;
	}
	
	/**
	 * Sets the value for the property hotelLodging.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging $hotelLodging
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setHotelLodging($hotelLodging){
		if ($hotelLodging instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging) {
			$this->hotelLodging = $hotelLodging;
		}
		else {
			throw new BadMethodCallException("Type of argument hotelLodging must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_HotelLodging.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property comments.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max
	 */
	public function getComments(){
		return $this->comments;
	}
	
	/**
	 * Sets the value for the property comments.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max $comments
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setComments($comments){
		if ($comments instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max) {
			$this->comments = $comments;
		}
		else {
			throw new BadMethodCallException("Type of argument comments must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String1024max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property invoiceNumber.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max
	 */
	public function getInvoiceNumber(){
		return $this->invoiceNumber;
	}
	
	/**
	 * Sets the value for the property invoiceNumber.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max $invoiceNumber
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setInvoiceNumber($invoiceNumber){
		if ($invoiceNumber instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max) {
			$this->invoiceNumber = $invoiceNumber;
		}
		else {
			throw new BadMethodCallException("Type of argument invoiceNumber must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String48max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property dynamicMerchantName.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max
	 */
	public function getDynamicMerchantName(){
		return $this->dynamicMerchantName;
	}
	
	/**
	 * Sets the value for the property dynamicMerchantName.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max $dynamicMerchantName
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setDynamicMerchantName($dynamicMerchantName){
		if ($dynamicMerchantName instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max) {
			$this->dynamicMerchantName = $dynamicMerchantName;
		}
		else {
			throw new BadMethodCallException("Type of argument dynamicMerchantName must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property pONumber.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max
	 */
	public function getPONumber(){
		return $this->pONumber;
	}
	
	/**
	 * Sets the value for the property pONumber.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max $pONumber
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setPONumber($pONumber){
		if ($pONumber instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max) {
			$this->pONumber = $pONumber;
		}
		else {
			throw new BadMethodCallException("Type of argument pONumber must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String128max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property orderId.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max
	 */
	public function getOrderId(){
		return $this->orderId;
	}
	
	/**
	 * Sets the value for the property orderId.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max $orderId
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setOrderId($orderId){
		if ($orderId instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max) {
			$this->orderId = $orderId;
		}
		else {
			throw new BadMethodCallException("Type of argument orderId must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String100max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property merchantTransactionId.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max
	 */
	public function getMerchantTransactionId(){
		return $this->merchantTransactionId;
	}
	
	/**
	 * Sets the value for the property merchantTransactionId.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max $merchantTransactionId
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setMerchantTransactionId($merchantTransactionId){
		if ($merchantTransactionId instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max) {
			$this->merchantTransactionId = $merchantTransactionId;
		}
		else {
			throw new BadMethodCallException("Type of argument merchantTransactionId must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property referencedMerchantTransactionId.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max
	 */
	public function getReferencedMerchantTransactionId(){
		return $this->referencedMerchantTransactionId;
	}
	
	/**
	 * Sets the value for the property referencedMerchantTransactionId.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max $referencedMerchantTransactionId
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setReferencedMerchantTransactionId($referencedMerchantTransactionId){
		if ($referencedMerchantTransactionId instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max) {
			$this->referencedMerchantTransactionId = $referencedMerchantTransactionId;
		}
		else {
			throw new BadMethodCallException("Type of argument referencedMerchantTransactionId must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String40max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property ipgTransactionId.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max
	 */
	public function getIpgTransactionId(){
		return $this->ipgTransactionId;
	}
	
	/**
	 * Sets the value for the property ipgTransactionId.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max $ipgTransactionId
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setIpgTransactionId($ipgTransactionId){
		if ($ipgTransactionId instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max) {
			$this->ipgTransactionId = $ipgTransactionId;
		}
		else {
			throw new BadMethodCallException("Type of argument ipgTransactionId must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_PositiveNumeric14max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property offlineApprovalType.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType
	 */
	public function getOfflineApprovalType(){
		return $this->offlineApprovalType;
	}
	
	/**
	 * Sets the value for the property offlineApprovalType.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType $offlineApprovalType
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setOfflineApprovalType($offlineApprovalType){
		if ($offlineApprovalType instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType) {
			$this->offlineApprovalType = $offlineApprovalType;
		}
		else {
			throw new BadMethodCallException("Type of argument offlineApprovalType must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_OfflineApprovalType.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property ip.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip
	 */
	public function getIp(){
		return $this->ip;
	}
	
	/**
	 * Sets the value for the property ip.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip $ip
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setIp($ip){
		if ($ip instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip) {
			$this->ip = $ip;
		}
		else {
			throw new BadMethodCallException("Type of argument ip must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Ip.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property referenceNumber.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber
	 */
	public function getReferenceNumber(){
		return $this->referenceNumber;
	}
	
	/**
	 * Sets the value for the property referenceNumber.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber $referenceNumber
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setReferenceNumber($referenceNumber){
		if ($referenceNumber instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber) {
			$this->referenceNumber = $referenceNumber;
		}
		else {
			throw new BadMethodCallException("Type of argument referenceNumber must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_ReferenceNumber.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property splitShipment.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment
	 */
	public function getSplitShipment(){
		return $this->splitShipment;
	}
	
	/**
	 * Sets the value for the property splitShipment.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment $splitShipment
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setSplitShipment($splitShipment){
		if ($splitShipment instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment) {
			$this->splitShipment = $splitShipment;
		}
		else {
			throw new BadMethodCallException("Type of argument splitShipment must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property tDate.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate
	 */
	public function getTDate(){
		return $this->tDate;
	}
	
	/**
	 * Sets the value for the property tDate.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate $tDate
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setTDate($tDate){
		if ($tDate instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate) {
			$this->tDate = $tDate;
		}
		else {
			throw new BadMethodCallException("Type of argument tDate must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TDate.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property transactionOrigin.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin
	 */
	public function getTransactionOrigin(){
		return $this->transactionOrigin;
	}
	
	/**
	 * Sets the value for the property transactionOrigin.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin $transactionOrigin
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setTransactionOrigin($transactionOrigin){
		if ($transactionOrigin instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin) {
			$this->transactionOrigin = $transactionOrigin;
		}
		else {
			throw new BadMethodCallException("Type of argument transactionOrigin must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionOrigin.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property terminal.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal
	 */
	public function getTerminal(){
		return $this->terminal;
	}
	
	/**
	 * Sets the value for the property terminal.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal $terminal
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setTerminal($terminal){
		if ($terminal instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal) {
			$this->terminal = $terminal;
		}
		else {
			throw new BadMethodCallException("Type of argument terminal must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_Terminal.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property inquiryRateReference.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference
	 */
	public function getInquiryRateReference(){
		return $this->inquiryRateReference;
	}
	
	/**
	 * Sets the value for the property inquiryRateReference.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference $inquiryRateReference
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setInquiryRateReference($inquiryRateReference){
		if ($inquiryRateReference instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference) {
			$this->inquiryRateReference = $inquiryRateReference;
		}
		else {
			throw new BadMethodCallException("Type of argument inquiryRateReference must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_InquiryRateReference.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property signature.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_String
	 */
	public function getSignature(){
		return $this->signature;
	}
	
	/**
	 * Sets the value for the property signature.
	 * 
	 * @param string $signature
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_TransactionDetails
	 */
	public function setSignature($signature){
		if ($signature instanceof Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_String) {
			$this->signature = $signature;
		}
		else {
			$this->signature = Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_String::_()->set($signature);
		}
		return $this;
	}
	
	
	
}