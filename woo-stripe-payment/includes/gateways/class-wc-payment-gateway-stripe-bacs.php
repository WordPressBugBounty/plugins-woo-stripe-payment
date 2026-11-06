<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 *
 * @since   4.0.20
 * @package PaymentPlugins\Gateways
 * @author  PaymentPlugins
 *
 */
class WC_Payment_Gateway_Stripe_Bacs extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_bacs';

	protected $payment_method_type = 'bacs_debit';

	public $token_type = 'Stripe_Bacs';

	//protected $supports_save_payment_method = true;

	private $account_countries = array(
		'AT', // Austria
		'AU', // Australia
		'BE', // Belgium
		'BG', // Bulgaria
		'CA', // Canada
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DE', // Germany
		'DK', // Denmark
		'EE', // Estonia
		'ES', // Spain
		'FI', // Finland
		'FR', // France
		'GB', // United Kingdom
		'GR', // Greece
		'HR', // Croatia
		'HU', // Hungary
		'IE', // Ireland
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
		'SG', // Singapore
		'SI', // Slovenia
		'SK', // Slovakia
		'US', // United States
	);

	public function __construct( ...$args ) {
		$this->synchronous        = false;
		$this->currencies         = array( 'GBP' );
		$this->countries          = $this->limited_countries = array( 'GB' );
		$this->tab_title          = __( 'Bacs Direct Debit', 'woo-stripe-payment' );
		$this->method_title       = __( 'Bacs Direct Debit (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Bacs Direct Debit gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon                          = $this->assets->assets_url( 'img/bacs.svg' );
		$this->settings['save_card_enabled'] = 'yes';
		$this->new_payment_method_label      = __( 'New Account', 'woo-stripe-payment' );
		$this->saved_payment_methods_label   = __( 'Saved Accounts', 'woo-stripe-payment' );
	}

	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		// Bacs Direct Debit is only available to customers with a UK bank account.
		if ( ! in_array( $billing_country, $this->limited_countries, true ) ) {
			return false;
		}

		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
		if ( ! in_array( $account_country, $this->account_countries, true ) ) {
			return false;
		}

		// The Payment Element can't create Bacs SetupIntents, which save a payment method without a payment.
		$container = wc_stripe_get_container();
		$packages  = $container->get( \PaymentPlugins\Stripe\Packages\PackageRegistry::class );

		$subscriptions = $packages->get( 'woocommerce_subscriptions' );
		if ( $subscriptions && $subscriptions->is_active()
		     && $container->has( \PaymentPlugins\Stripe\WooCommerceSubscriptions\FrontendRequests::class ) ) {
			/**
			 * @var \PaymentPlugins\Stripe\WooCommerceSubscriptions\FrontendRequests $requests
			 */
			$requests = $container->get( \PaymentPlugins\Stripe\WooCommerceSubscriptions\FrontendRequests::class );
			if ( $requests->is_change_payment_method()
			     || $requests->is_checkout_with_free_trial()
			     || $requests->is_checkout_with_free_coupon()
			     || $requests->is_order_pay_with_free_trial() ) {
				return false;
			}
		}

		$preorders = $packages->get( 'preorders' );
		if ( $preorders && $preorders->is_active()
		     && $container->has( \PaymentPlugins\Stripe\WooCommercePreOrders\FrontendRequests::class ) ) {
			/**
			 * @var \PaymentPlugins\Stripe\WooCommercePreOrders\FrontendRequests $requests
			 */
			$requests = $container->get( \PaymentPlugins\Stripe\WooCommercePreOrders\FrontendRequests::class );
			if ( $requests->is_checkout_with_preorder_requires_tokenization()
			     || $requests->is_order_pay_with_preorder_requires_tokenization() ) {
				return false;
			}
		}

		return true;
	}

	public function get_local_payment_settings() {
		return array_merge(
			parent::get_local_payment_settings(),
			array(
				'method_format' => array(
					'title'       => __( 'Payment Method Display', 'woo-stripe-payment' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
					'default'     => 'type_ending_last4',
					'desc_tip'    => true,
					'description' => __( 'This option allows you to customize how the payment method will display for your customers on orders, subscriptions, etc.', 'woo-stripe-payment' ),
				),
			)
		);
	}

}