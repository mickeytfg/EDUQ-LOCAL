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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String50max.php';
require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ProductChoice.php';
require_once 'Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Integer.php';
/**
 * @XmlType(name="ProductStock", namespace="http://ipg-online.com/ipgapi/schemas/a1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock {
	/**
	 * @XmlElement(name="ProductID", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max", namespace="http://ipg-online.com/ipgapi/schemas/a1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max
	 */
	private $productID;
	
	/**
	 * @XmlList(name="Choice", type='Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice', namespace="http://ipg-online.com/ipgapi/schemas/a1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice[]
	 */
	private $choice;
	
	/**
	 * @XmlValue(name="Quantity", simpleType=@XmlSimpleTypeDefinition(typeName='nonNegativeInteger', typeNamespace='http://www.w3.org/2001/XMLSchema', type='Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Integer'), namespace="http://ipg-online.com/ipgapi/schemas/a1")
	 * @var integer
	 */
	private $quantity;
	
	public function __construct() {
		$this->choice = new ArrayObject();
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock();
		return $i;
	}
	/**
	 * Returns the value for the property productID.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max
	 */
	public function getProductID(){
		return $this->productID;
	}
	
	/**
	 * Sets the value for the property productID.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max $productID
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock
	 */
	public function setProductID($productID){
		if ($productID instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max) {
			$this->productID = $productID;
		}
		else {
			throw new BadMethodCallException("Type of argument productID must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String50max.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property choice.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice[]
	 */
	public function getChoice(){
		return $this->choice;
	}
	
	/**
	 * Sets the value for the property choice.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice $choice
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock
	 */
	public function setChoice($choice){
		if (is_array($choice)) {
			$choice = new ArrayObject($choice);
		}
		if ($choice instanceof ArrayObject) {
			$this->choice = $choice;
		}
		else {
			throw new BadMethodCallException("Type of argument choice must be ArrayObject.");
		}
		return $this;
	}
	
	/**
	 * Adds the given $item to the list of items of choice.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice $item
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock
	 */
	public function addChoice(Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductChoice $item) {
		if (!($this->choice instanceof ArrayObject)) {
			$this->choice = new ArrayObject();
		}
		$this->choice[] = $item;
		return $this;
	}
	
	/**
	 * Returns the value for the property quantity.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Integer
	 */
	public function getQuantity(){
		return $this->quantity;
	}
	
	/**
	 * Sets the value for the property quantity.
	 * 
	 * @param integer $quantity
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ProductStock
	 */
	public function setQuantity($quantity){
		if ($quantity instanceof Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Integer) {
			$this->quantity = $quantity;
		}
		else {
			$this->quantity = Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Integer::_()->set($quantity);
		}
		return $this;
	}
	
	
	
}