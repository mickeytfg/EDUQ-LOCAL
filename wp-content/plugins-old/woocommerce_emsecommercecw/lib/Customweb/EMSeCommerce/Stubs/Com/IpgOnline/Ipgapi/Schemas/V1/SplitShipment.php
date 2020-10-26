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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SplitShipment/SequenceCount.php';
require_once 'Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Boolean.php';
/**
 * @XmlType(name="SplitShipment", namespace="http://ipg-online.com/ipgapi/schemas/v1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment {
	/**
	 * @XmlElement(name="SequenceCount", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount", namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount
	 */
	private $sequenceCount;
	
	/**
	 * @XmlValue(name="FinalShipment", simpleType=@XmlSimpleTypeDefinition(typeName='boolean', typeNamespace='http://www.w3.org/2001/XMLSchema', type='Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean'), namespace="http://ipg-online.com/ipgapi/schemas/v1")
	 * @var boolean
	 */
	private $finalShipment;
	
	public function __construct() {
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment();
		return $i;
	}
	/**
	 * Returns the value for the property sequenceCount.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount
	 */
	public function getSequenceCount(){
		return $this->sequenceCount;
	}
	
	/**
	 * Sets the value for the property sequenceCount.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount $sequenceCount
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment
	 */
	public function setSequenceCount($sequenceCount){
		if ($sequenceCount instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount) {
			$this->sequenceCount = $sequenceCount;
		}
		else {
			throw new BadMethodCallException("Type of argument sequenceCount must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment_SequenceCount.");
		}
		return $this;
	}
	
	
	/**
	 * Returns the value for the property finalShipment.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean
	 */
	public function getFinalShipment(){
		return $this->finalShipment;
	}
	
	/**
	 * Sets the value for the property finalShipment.
	 * 
	 * @param boolean $finalShipment
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_SplitShipment
	 */
	public function setFinalShipment($finalShipment){
		if ($finalShipment instanceof Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean) {
			$this->finalShipment = $finalShipment;
		}
		else {
			$this->finalShipment = Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean::_()->set($finalShipment);
		}
		return $this;
	}
	
	
	
}