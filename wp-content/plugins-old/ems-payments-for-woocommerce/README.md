# EMS payments for WooCommmerce
Accept payments in WooCommerce with the official EMS e-Commerce gateway plugin.

## Description
This plugin will add support for the following EMS payments methods to your WooCommerce webshop:

* Credit card (Visa, Mastercard, Diner's club)
* PayPal
* iDEAL
* MasterPass
* Klarna
* Sofort
* Maestro, Maestro UK

## Provisioning

### Are you already a customer ?
If you are already registered as an EMS merchant then please enter the credentials and settings below.

For new customers please follow the link below to acquire an EMS merchant account.

### Becoming an EMS customer
Get a merchant account by sending an email with your request to integrations@emspay.eu

### Contact EMS Support
Visit the FAQ:
https://developer.emspay.eu/faq

Contact information:
https://developer.emspay.eu/contact

## Features
* Support for all available EMS payment methods
* Enable / disable payment methods
* Able to configure each payment method
* Toggle 3D secure transactions for the credit card payment method
* Switch between integration and production modes
* Select the pay mode of your preference (payonly, payplus, fullpay)
* Toggle payment method icons
* Transaction logs / notes in order
* IPN handling

## Frequently Asked Questions

### I can't install the plugin
Please temporarily enable the [WordPress Debug Mode](https://codex.wordpress.org/Debugging_in_WordPress). Edit your `wp-config.php` and set the constants `WP_DEBUG` and `WP_DEBUG_LOG` to `true` and try
it again. When the plugin triggers an error, WordPress will log the error to the log file `/wp-content/debug.log`. Please check this file for errors. When done, don't forget to turn off
the WordPress debug mode by setting the two constants `WP_DEBUG` and `WP_DEBUG_LOG` back to `false`.

### I get a white screen
Most of the time a white screen means a PHP error. Because PHP won't show error messages on default for security reasons, the page is white. Please turn on the WordPress Debug Mode to turn on PHP error messages (see previous answer).

### I have a different question
Please contact us via the above "support" tab and add a ticket: please describe your problem as detailed as possible. Include screenshots where appropriate.
Where possible, also include the log file. You can find the log files in `/wp-content/uploads/wc-logs/` or `/wp-content/plugin/woocommerce/logs`.

## Installation

### Minimum Requirements
* PHP version 5.6 or greater
* PHP extensions enabled: cURL, JSON
* WordPress 4.4 or greater
* WooCommerce 2.2.0 or greater

### Automatic installation
1. Install the plugin via Plugins -> New plugin. Search for 'EMS Payments for WooCommerce'
2. Activate the 'EMS Payments for WooCommerce' plugin through the 'Plugins' menu in WordPress
3. Set your EMS shop name and secret at WooCommerce -> Settings -> Integration
4. You're done, the active payment methods should be visible in the checkout of your webshop

### Manual installation
1. Unpack the download package
2. Upload the directory 'ems-payments-for-woocommerce' to the `/wp-content/plugins/` directory
3. Activate the 'EMS Payments for WooCommerce' plugin through the 'Plugins' menu in WordPress
4. Set your EMS shop name and secret at WooCommerce -> Settings -> Integration
5. You're done, the active payment methods should be visible in the checkout of your webshop

### Updating
Automatic updates should work flawlessly; as always though, ensure you backup your site just in case.
