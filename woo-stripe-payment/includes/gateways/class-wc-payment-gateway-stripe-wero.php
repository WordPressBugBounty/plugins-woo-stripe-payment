<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_Wero extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	public $id = 'stripe_wero';

	protected $payment_method_type = 'wero';

	private $account_countries = array(
		'AT', // Austria
		'BE', // Belgium
		'BG', // Bulgaria
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DE', // Germany
		'DK', // Denmark
		'EE', // Estonia
		'ES', // Spain
		'FI', // Finland
		'FR', // France
		'GR', // Greece
		'HR', // Croatia
		'HU', // Hungary
		'IE', // Ireland
		'IS', // Iceland
		'IT', // Italy
		'LI', // Liechtenstein
		'LT', // Lithuania
		'LU', // Luxembourg
		'LV', // Latvia
		'MT', // Malta
		'NL', // Netherlands
		'NO', // Norway
		'PL', // Poland
		'PT', // Portugal
		'RO', // Romania
		'SE', // Sweden
		'SI', // Slovenia
		'SK', // Slovakia
	);

	public function __construct( ...$args ) {
		$this->currencies         = array( 'EUR' );
		$this->countries          = $this->limited_countries = array( 'BE', 'DE', 'FR', 'LU' );
		$this->tab_title          = __( 'Wero', 'woo-stripe-payment' );
		$this->method_title       = __( 'Wero (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Wero gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/' . $this->get_option( 'icon', 'wero' ) . '.svg' );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		// Wero is only available to customers in the supported customer locations.
		if ( ! in_array( $billing_country, $this->limited_countries, true ) ) {
			return false;
		}
		
		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		return in_array( $account_country, $this->account_countries, true );
	}

	public function get_local_payment_settings() {
		return array_merge(
			parent::get_local_payment_settings(),
			array(
				'charge_type' => array(
					'type'        => 'select',
					'title'       => __( 'Charge Type', 'woo-stripe-payment' ),
					'default'     => 'capture',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'capture'   => __( 'Capture', 'woo-stripe-payment' ),
						'authorize' => __( 'Authorize', 'woo-stripe-payment' ),
					),
					'desc_tip'    => true,
					'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.',
						'woo-stripe-payment' ),
				),
				'icon'        => array(
					'title'       => __( 'Icon', 'woo-stripe-payment' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'wero'       => __( 'Wero', 'woo-stripe-payment' ),
						'wero_black' => __( 'Wero black', 'woo-stripe-payment' ),
					),
					'default'     => 'wero',
					'desc_tip'    => true,
					'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'woo-stripe-payment' ),
				),
			)
		);
	}

}