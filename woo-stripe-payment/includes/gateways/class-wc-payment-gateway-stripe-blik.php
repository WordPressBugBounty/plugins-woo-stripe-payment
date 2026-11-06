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
class WC_Payment_Gateway_Stripe_BLIK extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_blik';

	protected $payment_method_type = 'blik';

	protected $max_off_session_amount = array( 'PLN' => 2000 );

	public function __construct( ...$args ) {
		$this->currencies         = array( 'PLN' );
		$this->countries          = array( 'PL' );
		$this->tab_title          = __( 'BLIK', 'woo-stripe-payment' );
		$this->method_title       = __( 'BLIK (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'BLIK gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/blik.svg' );
	}

}
