<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class AmazonPayPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_amazonpay';

	public function get_payment_method_data() {
		return wp_parse_args( [
			'accountCountry'     => stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ),
			'accountCountries'   => $this->payment_method->get_account_countries(),
			'acceptedCurrencies' => $this->payment_method->get_accepted_currencies(),
			'sections'           => (array) $this->payment_method->get_option( 'payment_sections', [] ),
			'button'             => [
				'height' => (int) $this->payment_method->get_option( 'button_height', 50 ),
				'radius' => $this->payment_method->get_option( 'button_radius', 4 ) . 'px',
			],
			'editorIcon'         => $this->assets_api->assets_url( 'assets/img/amazonpay_button.svg' ),
		], parent::get_payment_method_data() );
	}

}