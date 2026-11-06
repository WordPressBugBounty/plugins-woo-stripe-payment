<?php

defined( 'ABSPATH' ) || exit();

/**
 *
 * @since   4.0.20
 * @package PaymentPlugins\PaymentTokens
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Token_Stripe_Bacs extends WC_Payment_Token_Stripe_Local {

	use WC_Payment_Token_Payment_Method_Trait;

	protected $type = 'Stripe_Bacs';

	protected $stripe_data = array(
		'sort_code' => '',
		'last4'     => ''
	);

	public function details_to_props( $details ) {
		if ( isset( $details['bacs_debit'] ) ) {
			$this->set_last4( $details['bacs_debit']['last4'] ?? '' );
			$this->set_sort_code( $details['bacs_debit']['sort_code'] ?? '' );
		}
	}

	public function set_last4( $value ) {
		$this->set_prop( 'last4', $value );
	}

	public function get_last4( $context = 'view' ) {
		return $this->get_prop( 'last4', $context );
	}

	public function set_sort_code( $value ) {
		$this->set_prop( 'sort_code', $value );
	}

	public function get_sort_code( $context = 'view' ) {
		return $this->get_prop( 'sort_code', $context );
	}

	public function get_brand( $context = 'view' ) {
		return __( 'Bacs Direct Debit', 'woo-stripe-payment' );
	}

	/**
	 * The brand is a label, not an icon file name.
	 *
	 * @return string
	 * @since 4.0.20
	 */
	public function get_icon_file() {
		return 'img/bacs.svg';
	}

	public function get_formats() {
		return wp_parse_args( array(
			'type_ending_last4' => array(
				'label'   => __( 'Gateway Title', 'woo-stripe-payment' ),
				'example' => 'Bacs Direct Debit ending in 2345',
				'format'  => __( '{brand} ending in {last4}', 'woo-stripe-payment' ),
			),
			'type_last4'        => array(
				'label'   => __( 'Type Last 4', 'woo-stripe-payment' ),
				'example' => 'Bacs Direct Debit 2345',
				'format'  => '{brand} {last4}',
			),
		), parent::get_formats() );
	}

}