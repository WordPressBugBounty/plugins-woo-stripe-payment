<?php

namespace PaymentPlugins\Stripe\Controllers;

use PaymentPlugins\Stripe\ContextHandler;

class PaymentIntentController {

	/**
	 * @var \WC_Stripe_Gateway
	 */
	private $client;

	/**
	 * @var array The list of payment methods ID's that are compatible
	 */
	private $payment_method_ids;

	private $retrys = 0;

	private $max_retries = 1;

	private $intent_exists;

	/**
	 * @var ContextHandler
	 */
	private $context_handler;

	private $element_options;

	private static $instance;

	/**
	 * @param ContextHandler $context_handler
	 */
	public function __construct( ContextHandler $context_handler ) {
		$this->context_handler = $context_handler;
		self::$instance        = $this;
	}

	public static function instance() {
		return self::$instance;
	}

	public function initialize() {
		add_action( 'woocommerce_before_pay_action', function () {
			$this->set_order_pay_constants();
		} );
	}

	/**
	 * @param $context
	 *
	 * @deprecated 4.0.20 Set the context on ContextHandler instead.
	 */
	public function set_request_context( $context ) {
	}

	/**
	 * @return ContextHandler
	 * @deprecated 4.0.20 Use ContextHandler instead.
	 */
	public function get_request_context() {
		return $this->context_handler;
	}

	public function get_element_options() {
		if ( ! $this->element_options ) {
			$element_options = array(
				'mode'                  => 'payment',
				'paymentMethodCreation' => 'manual'
			);
			if ( $this->is_setup_intent_needed() ) {
				$element_options['mode'] = 'setup';
			} elseif ( $this->is_subscription_mode() ) {
				$element_options['mode'] = 'subscription';
			}
			$this->element_options = $element_options;
		}

		return $this->element_options;
	}

	private function is_setup_intent_needed() {
		return $this->context_handler->is_add_payment_method()
		       || apply_filters( 'wc_stripe_create_setup_intent', false, $this->context_handler );
	}

	private function is_subscription_mode() {
		return apply_filters( 'wc_stripe_deferred_intent_subscription_mode', false, $this->context_handler );
	}

	private function set_order_pay_constants() {
		wc_maybe_define_constant( \WC_Stripe_Constants::WOOCOMMERCE_STRIPE_ORDER_PAY, true );
	}

}