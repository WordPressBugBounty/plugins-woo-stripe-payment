<?php
defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe' ) ) {
	return;
}

/**
 * This gateway is provided so merchants can accept Chrome Payments, Microsoft Pay, etc.
 *
 * @author  PaymentPlugins
 * @package Stripe/Gateways
 *
 */
class WC_Payment_Gateway_Stripe_Payment_Request extends WC_Payment_Gateway_Stripe {

	use WC_Stripe_Payment_Intent_Trait;

	use WC_Stripe_Express_Payment_Trait;

	protected $payment_method_type = 'card';

	private $supported_locales = [
		'ar',
		'bg',
		'cs',
		'da',
		'de',
		'el',
		'en',
		'en-GB',
		'es',
		'es-419',
		'et',
		'fi',
		'fil',
		'fr',
		'fr-CA',
		'he',
		'hr',
		'hu',
		'id',
		'it',
		'ja',
		'ko',
		'lt',
		'lv',
		'ms',
		'mt',
		'nb',
		'nl',
		'pl',
		'pt-BR',
		'pt',
		'ro',
		'ru',
		'sk',
		'sl',
		'sv',
		'th',
		'tr',
		'vi',
		'zh',
		'zh-HK',
		'zh-TW'
	];

	public function __construct() {
		$this->id                 = 'stripe_payment_request';
		$this->tab_title          = __( 'PaymentRequest Gateway', 'woo-stripe-payment' );
		$this->template_name      = 'payment-request.php';
		$this->token_type         = 'Stripe_CC';
		$this->method_title       = __( 'Payment Request (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Gateway that renders based on the user\'s browser. Chrome payment methods, Microsoft pay, etc.', 'woo-stripe-payment' );
		$this->has_digital_wallet = true;
		$this->icon               = stripe_wc()->assets_url( 'img/googlepay_round_outline.svg' );
		parent::__construct();
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'wc_stripe_cart_checkout';
		$this->supports[] = 'wc_stripe_product_checkout';
		$this->supports[] = 'wc_stripe_banner_checkout';
		$this->supports[] = 'wc_stripe_mini_cart_checkout';
	}

	public function enqueue_product_scripts( $scripts ) {
		$this->enqueue_checkout_scripts( $scripts );
	}

	public function enqueue_cart_scripts( $scripts ) {
		$this->enqueue_checkout_scripts( $scripts );
	}

	public function enqueue_checkout_scripts( $scripts ) {
		$scripts->enqueue_script(
			'payment-request',
			$scripts->assets_url( 'js/frontend/payment-request.js' ),
			array(
				$scripts->get_handle( 'wc-stripe' ),
				$scripts->get_handle( 'external' ),
			),
			stripe_wc()->version(),
			true
		);

		$scripts->localize_script( 'payment-request', $this->get_localized_params() );
	}

	public function get_localized_params() {
		return array_merge_recursive(
			parent::get_localized_params(),
			array(
				'button'   => array(
					'type'   => $this->get_option( 'button_type' ),
					'theme'  => $this->get_option( 'button_theme' ),
					'height' => $this->get_button_height(),
				),
				'icons'    => array( 'chrome' => stripe_wc()->assets_url( 'img/chrome.svg' ) ),
				'messages' => array(
					'invalid_amount' => __( 'Please update you product quantity before paying.', 'woo-stripe-payment' ),
					'add_to_cart'    => __( 'Adding to cart...', 'woo-stripe-payment' ),
					'choose_product' => __( 'Please select a product option before updating quantity.', 'woo-stripe-payment' ),
				)
			)
		);
	}

	public function get_button_height() {
		$value = $this->get_option( 'button_height' );
		$value .= strpos( $value, 'px' ) === false ? 'px' : '';

		return $value;
	}

	public function has_enqueued_scripts( $scripts ) {
		return wp_script_is( $scripts->get_handle( 'payment-request' ) );
	}

	protected function get_element_options_locale() {
		$locale = wc_stripe_get_site_locale();

		if ( $locale === 'auto' ) {
			return $locale;
		}

		if ( in_array( $locale, $this->supported_locales ) ) {
			return $locale;
		}

		$formatted_locale = substr( $locale, 0, 2 );

		if ( in_array( $formatted_locale, $this->supported_locales ) ) {
			$locale = $formatted_locale;
		}

		return $locale;
	}

}
