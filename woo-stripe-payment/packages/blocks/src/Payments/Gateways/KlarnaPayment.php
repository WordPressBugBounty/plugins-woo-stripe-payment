<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

class KlarnaPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_klarna';

	/**
	 * @since 4.0.20
	 */
	public function get_payment_method_data() {
		return wp_parse_args( [
			'sections'   => (array) $this->payment_method->get_option( 'payment_sections', [] ),
			'button'     => [
				'height' => (int) $this->payment_method->get_option( 'button_height', 50 ),
				'radius' => $this->payment_method->get_option( 'button_radius', 4 ) . 'px',
			],
			'editorIcon' => $this->assets_api->assets_url( 'assets/img/klarna_button.svg' ),
		], parent::get_payment_method_data() );
	}

}