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

require_once 'Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String20max.php';
/**
 * @XmlType(name="InitiateClearing", namespace="http://ipg-online.com/ipgapi/schemas/a1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_InitiateClearing {
	/**
	 * @XmlElement(name="StoreId", type="Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max", namespace="http://ipg-online.com/ipgapi/schemas/a1")
	 * @var Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max
	 */
	private $storeId;
	
	public function __construct() {
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_InitiateClearing
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_InitiateClearing();
		return $i;
	}
	/**
	 * Returns the value for the property storeId.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max
	 */
	public function getStoreId(){
		return $this->storeId;
	}
	
	/**
	 * Sets the value for the property storeId.
	 * 
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max $storeId
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_InitiateClearing
	 */
	public function setStoreId($storeId){
		if ($storeId instanceof Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max) {
			$this->storeId = $storeId;
		}
		else {
			throw new BadMethodCallException("Type of argument storeId must be Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_V1_String20max.");
		}
		return $this;
	}
	
	
	
}