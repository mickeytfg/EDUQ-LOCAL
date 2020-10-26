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

require_once 'Customweb/Xml/Binding/DateHandler/Date.php';
require_once 'Customweb/EMSeCommerce/BirthDateFormat.php';


/**
 *
 * @author Nico Eigenmann
 *
 */
final class Customweb_EMSeCommerce_BirthDateFormat extends Customweb_Xml_Binding_DateHandler_Date{
	
	public static function _($time = null, $timezone = null) {
		return new Customweb_EMSeCommerce_BirthDateFormat($time, $timezone);
	}
	
	public function formatForXml(){
		return $this->format('Y-m-dP');
	}
 
}