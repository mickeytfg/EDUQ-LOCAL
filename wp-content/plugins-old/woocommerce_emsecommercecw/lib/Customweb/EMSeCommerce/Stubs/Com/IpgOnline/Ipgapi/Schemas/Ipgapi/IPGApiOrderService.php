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


require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/Amount.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/Currency.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/Money.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/OrderDetailItem.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/OrderDetailItemList.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/OrderDetailItemType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/OrderDetailText.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/OrderDetails.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/Clickandbuy/Api/Webservices/Pay/Quantity.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Action.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/DataStorageItem.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/DataStorageItem/Function.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Error.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Function.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetExternalConsumerInformation.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetExternalConsumerInformation/Country.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetExternalConsumerInformation/DataProvider.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetExternalTransactionStatus.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetExternalTransactionStatus/TDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetLastOrders.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetLastOrders/Count.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetLastTransactions.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetLastTransactions/Count.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/GetLastTransactions/TDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/InitiateClearing.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/InquiryOrder.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/InquiryRateType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Ip.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ManageProductStock.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ManageProductStock/Function.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ManageProducts.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ManageProducts/Function.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/OrderValueType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Product.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ProductChoice.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ProductStock.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPayment.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentInformation.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentInformation/InstallmentCount.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentInformation/InstallmentFrequency.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentInformation/InstallmentPeriod.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentInformation/MaximumFailures.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentValues.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RecurringPaymentValues/State.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RequestCardRateForDCC.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/RequestMerchantRateForDynamicPricing.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/ResultInfoType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/SendEMailNotification.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/SendEMailNotification/TDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/StoreHostedData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/StringDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/TransactionOrigin.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/TransactionValues.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/A1/Validate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVCardPresentResponse.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVCardPresent/Request.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData/IssuerAuthenticationData91.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData/IssuerAuthorizationResponseCode8A.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData/IssuerScriptTemplate171.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData/IssuerScriptTemplate272.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/EMVResponseData/MessageControlFieldDF4F.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/IPGApiActionRequest.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/IPGApiActionResponse.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/IPGApiOrderRequest.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/Ipgapi/IPGApiOrderResponse.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/AirlineDetails.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/AirlineDetails/ComputerizedReservationSystem.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/AmountValueType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/BINType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Basket.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Basket/Item.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Basket/Item/Option.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Basket/ProductStock.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Billing.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CarRental.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CarRental/ExtraCharges.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CardCodeValue.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CardFunctionType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClickandBuyData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClickandBuyTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClickandBuyTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClientLocale.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClientLocale/Country.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ClientLocale/Language.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCard3DSecure.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCard3DSecure/AuthenticationValue.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCard3DSecure/PayerAuthenticationResponse.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCard3DSecure/VerificationResponse.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCard3DSecure/XID.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardData/Brand.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardData/CardNumber.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardData/ExpMonth.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardData/ExpYear.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CreditCardTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/PINBlock.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/PINBlock/KSN.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/PINBlock/Value.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/SRED.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/SRED/KSN.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CryptData/SRED/Value.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CurrencyType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardData/CardNumber.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardData/ExpMonth.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardData/ExpYear.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/CustomerCardTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitData/AccountNumber.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitData/BIC.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitData/BankCode.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitData/IBAN.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/DE/DirectDebitTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVCardPresentRequest.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/AdditionalTerminalCapabilities9F40.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/AmountAuthorised9F02.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/AmountOther9F03.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationCryptogram9F26.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationIdentifier4F.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationIdentifierTerminal9F06.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationInterchangeProfile82.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationPANSequenceNumber5F34.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationTransactionCounter9F36.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationUsageControl9F07.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/ApplicationVersionNumber9F09.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/CVMResults9F34.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/CryptogramInformationData9F27.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/DedicatedFileName84.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/InterfaceDeviceSerialNumber9F1E.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/IssuerApplicationData9F10.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/IssuerAuthenticationData91.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/PointOfServiceEntryMode9F39.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TerminalApplicationVersionNumber9F09.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TerminalCapabilities9F33.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TerminalCountry9F1A.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TerminalType9F35.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TerminalVerificationResults95.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionCertificateHashValue98.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionDate9A.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionSequenceCounter9F41.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionStatusInformation9B.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionTime9F21.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/TransactionType9C.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/EMVRequestData/UnpredictableNumber9F37.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/GenderType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/HotelLodging.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/HotelLodging/ExtraCharges.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/InquiryRateId.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/InquiryRateReference.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Ip.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ItemAmountValueType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/KlarnaTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/KlarnaTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MCC6012Details.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MCC6012Details/AccountFirst6.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MCC6012Details/AccountLast4.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MNSP.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MSISDN.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MandateReference.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/MandateType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/OfflineApprovalType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Options.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PayPalTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PayPalTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Payment.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PaymentType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Payment/InstallmentsInterest.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Payment/NumberOfInstallments.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/PositiveNumeric14max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/ReferenceNumber.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Shipping.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SofortTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SofortTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SplitShipment.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/SplitShipment/SequenceCount.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String100max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String1024max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String10max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String128max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String1max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String20max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String24max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String2max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String30max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String32max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String3max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String4000max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String40max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String48max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String50max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String64max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String6max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String84max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/String96max.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/StringDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TDate.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TLVData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Terminal.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Terminal/TerminalID.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TopUpTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TopUpTxType/MPCharge.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TrackData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/Transaction.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TransactionDetails.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TransactionElement.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TransactionOrigin.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TravelRoute.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/TravelRoute/StopoverType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardData.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardData/CardNumber.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardData/ExpMonth.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardData/ExpYear.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardData/IssueNo.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardTxType.php');
require_once('Customweb/EMSeCommerce/Stubs/Com/IpgOnline/Ipgapi/Schemas/V1/UK/DebitCardTxType/Type.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/AnyType.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Boolean.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Date.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/DateTime.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Float.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/Integer.php');
require_once('Customweb/EMSeCommerce/Stubs/Org/W3/XMLSchema/String.php');
require_once 'Customweb/Soap/AbstractService.php';
/**
 */

class Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderService extends Customweb_Soap_AbstractService {

	/**
	 * @var Customweb_Soap_IClient
	 */
	private $soapClient;
		
	/**
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiActionRequest $iPGApiActionRequest
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiActionResponse
	 */ 
	public function iPGApiAction(Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiActionRequest $iPGApiActionRequest){
		$data = func_get_args();
		if (count($data) > 0) {;
			$data = current($data);
		} else {;
			 throw new InvalidArgumentException();
		};
		$call = $this->createSoapCall("IPGApiAction", $data, "Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiActionResponse", "");
		$call->setStyle(Customweb_Soap_ICall::STYLE_DOCUMENT);
		$call->setInputEncoding(Customweb_Soap_ICall::ENCODING_LITERAL);
		$call->setOutputEncoding(Customweb_Soap_ICall::ENCODING_LITERAL);
		$call->setSoapVersion(Customweb_Soap_ICall::SOAP_VERSION_11);
		$call->setLocationUrl($this->resolveLocation("https://test.ipg-online.com:443/ipgapi/services"));
		$result = $this->getClient()->invokeOperation($call);
		return $result;
		
	}
		
	/**
	 * @param Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderRequest $iPGApiOrderRequest
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderResponse
	 */ 
	public function iPGApiOrder(Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderRequest $iPGApiOrderRequest){
		$data = func_get_args();
		if (count($data) > 0) {;
			$data = current($data);
		} else {;
			 throw new InvalidArgumentException();
		};
		$call = $this->createSoapCall("IPGApiOrder", $data, "Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_IPGApiOrderResponse", "");
		$call->setStyle(Customweb_Soap_ICall::STYLE_DOCUMENT);
		$call->setInputEncoding(Customweb_Soap_ICall::ENCODING_LITERAL);
		$call->setOutputEncoding(Customweb_Soap_ICall::ENCODING_LITERAL);
		$call->setSoapVersion(Customweb_Soap_ICall::SOAP_VERSION_11);
		$call->setLocationUrl($this->resolveLocation("https://test.ipg-online.com:443/ipgapi/services"));
		$result = $this->getClient()->invokeOperation($call);
		return $result;
		
	}
		
	/**
	
	 * @return Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_EMVCardPresentResponse
	 */ 
	public function eMVCardPresent(){
		$data = new Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_EMVCardPresentResponse();
		$call = $this->createSoapCall("EMVCardPresent", $data, "Customweb_EMSeCommerce_Stubs_Com_IpgOnline_Ipgapi_Schemas_Ipgapi_EMVCardPresentResponse", "");
		$call->setStyle(Customweb_Soap_ICall::STYLE_DOCUMENT);
		$call->setInputEncoding(Customweb_Soap_ICall::ENCODING_ENCODED);
		$call->setOutputEncoding(Customweb_Soap_ICall::ENCODING_LITERAL);
		$call->setSoapVersion(Customweb_Soap_ICall::SOAP_VERSION_11);
		$call->setLocationUrl($this->resolveLocation("https://test.ipg-online.com:443/ipgapi/services"));
		$result = $this->getClient()->invokeOperation($call);
		return $result;
		
	}
	
}