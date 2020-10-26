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

require_once 'Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Boolean.php';
/**
 * @XmlType(name="ResultInfoType", namespace="http://ipg-online.com/ipgapi/schemas/a1")
 */ 
class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ResultInfoType {
	/**
	 * @XmlValue(name="MoreResultsAvailable", simpleType=@XmlSimpleTypeDefinition(typeName='boolean', typeNamespace='http://www.w3.org/2001/XMLSchema', type='Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean'), namespace="http://ipg-online.com/ipgapi/schemas/a1")
	 * @var boolean
	 */
	private $moreResultsAvailable;
	
	public function __construct() {
	}
	
	/**
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ResultInfoType
	 */
	public static function _() {
		$i = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ResultInfoType();
		return $i;
	}
	/**
	 * Returns the value for the property moreResultsAvailable.
	 * 
	 * @return Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean
	 */
	public function getMoreResultsAvailable(){
		return $this->moreResultsAvailable;
	}
	
	/**
	 * Sets the value for the property moreResultsAvailable.
	 * 
	 * @param boolean $moreResultsAvailable
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_A1_ResultInfoType
	 */
	public function setMoreResultsAvailable($moreResultsAvailable){
		if ($moreResultsAvailable instanceof Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean) {
			$this->moreResultsAvailable = $moreResultsAvailable;
		}
		else {
			$this->moreResultsAvailable = Customweb_EMSeCommerce_Stubs_Org_W3_XMLSchema_Boolean::_()->set($moreResultsAvailable);
		}
		return $this;
	}
	
	
	
}