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
class WC_Payment_Gateway_Stripe_AmazonPay extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;
	use \PaymentPlugins\Stripe\Traits\TokenizationTrait;
	use \PaymentPlugins\Stripe\Traits\ExpressCheckoutTrait;
	use \PaymentPlugins\Stripe\WooCommercePreOrders\Traits\PreOrdersTrait;
	use \PaymentPlugins\Stripe\WooCommerceSubscriptions\Traits\WooCommerceSubscriptionsTrait;

	public $id = 'stripe_amazonpay';

	protected $payment_method_type = 'amazon_pay';

	private $account_countries = array(
		'AT',
		'BE',
		'CY',
		'DK',
		'FR',
		'DE',
		'HU',
		'IE',
		'IT',
		'LU',
		'NL',
		'PT',
		'ES',
		'SE',
		'CH',
		'GB',
		'US'
	);

	private $accepted_currencies = array(
		'US' => array( 'USD' )
	);

	public function __construct( ...$args ) {
		$this->currencies = array(
			'AUD',
			'CHF',
			'DKK',
			'EUR',
			'GBP',
			'HKD',
			'JPY',
			'NOK',
			'NZD',
			'SEK',
			'USD',
			'ZAR'
		);
		//$this->countries          = array( 'US' );
		$this->tab_title          = __( 'Amazon Pay', 'woo-stripe-payment' );
		$this->method_title       = __( 'Amazon Pay (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Amazon Pay gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct( ...$args );
		$this->icon = $this->assets->assets_url( 'img/amazon_pay.svg' );
	}

	public function get_local_payment_settings() {
		return array_merge(
			parent::get_local_payment_settings(),
			array(
				'payment_sections' => array(
					'type'        => 'multiselect',
					'title'       => __( 'Payment Sections', 'woo-stripe-payment' ),
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'checkout'         => __( 'Checkout', 'woo-stripe-payment' ),
						'product'          => __( 'Product Page', 'woo-stripe-payment' ),
						'cart'             => __( 'Cart Page', 'woo-stripe-payment' ),
						'express_checkout' => __( 'Express Checkout', 'woo-stripe-payment' )
					),
					'default'     => array( 'checkout' ),
					'desc_tip'    => true,
					'description' => __( 'Checkout shows Amazon Pay in the list of payment methods. Product Page, Cart Page and Express Checkout show the Amazon Pay button on the product page, cart page and at the top of the checkout page.', 'woo-stripe-payment' ),
				),
				'button_height'    => array(
					'title'             => __( 'Button Height', 'woo-stripe-payment' ),
					'type'              => 'number',
					'default'           => 50,
					'custom_attributes' => array(
						'min'  => 40,
						'max'  => 55,
						'step' => 1,
					),
					'desc_tip'          => true,
					'description'       => __( 'Height of the Amazon Pay express button. The button height must be between 40px and 55px.', 'woo-stripe-payment' ),
					'sanitize_callback' => function ( $value ) {
						if ( ! is_numeric( $value ) ) {
							$value = 40;
						}

						return max( 40, min( 55, absint( $value ) ) );
					}
				),
				'button_radius'    => array(
					'title'             => __( 'Button Radius', 'woo-stripe-payment' ),
					'type'              => 'number',
					'default'           => '4',
					'desc_tip'          => true,
					'description'       => __( 'The border radius of the Amazon Pay express button.', 'woo-stripe-payment' ),
					'sanitize_callback' => function ( $value ) {
						if ( ! preg_match( '/^[\d]+$/', $value ) ) {
							$value = 0;
						}

						return absint( $value );
					}
				),
			)
		);
	}

	/**
	 * @since 4.0.20
	 */
	public function get_payment_method_data() {
		return array_merge(
			parent::get_payment_method_data(),
			[
				'button' => [
					'height' => (int) $this->get_option( 'button_height', 50 ),
					'radius' => $this->get_option( 'button_radius', 4 ) . 'px',
				]
			]
		);
	}

	/**
	 * @since 4.0.20
	 */
	public function get_express_checkout_script_handles() {
		$this->assets->register_script( 'wc-stripe-amazonpay-express-checkout', 'build/amazonpay-express-checkout.js' );

		return [ 'wc-stripe-amazonpay-express-checkout' ];
	}

	/**
	 * @since 4.0.20
	 */
	public function get_product_script_handles() {
		$this->assets->register_script( 'wc-stripe-amazonpay-product', 'build/amazonpay-product.js' );

		return [ 'wc-stripe-amazonpay-product' ];
	}

	/**
	 * @since 4.0.20
	 */
	public function get_cart_script_handles() {
		$this->assets->register_script( 'wc-stripe-amazonpay-cart', 'build/amazonpay-cart.js' );

		return [ 'wc-stripe-amazonpay-cart' ];
	}

	/**
	 * @since 4.0.20
	 */
	public function product_fields() {
		?>
        <div id="wc-<?php echo esc_attr( $this->id ) ?>-product-button"
             class="wc-<?php echo esc_attr( $this->id ) ?>-product-button"></div>
		<?php
	}

	/**
	 * @since 4.0.20
	 */
	public function cart_fields() {
		?>
        <div id="wc-<?php echo esc_attr( $this->id ) ?>-cart-button"
             class="wc-<?php echo esc_attr( $this->id ) ?>-cart-button"></div>
		<?php
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 * @param float  $total
	 *
	 * @return bool
	 */
	protected function validate_local_payment_available( $currency, $billing_country, $total ) {
		$result = false;

		$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );

		// Check if account_country exists in the account_countries array
		if ( in_array( $account_country, $this->account_countries ) ) {
			// if there is an accepted_currencies list, verify.
			if ( isset( $this->accepted_currencies[ $account_country ] ) ) {
				$result = in_array( $currency, $this->accepted_currencies[ $account_country ], true );
			} else {
				$result = true;
			}
		}

		return $result;
	}

	/**
	 * @return string[]
	 * @since 3.3.85
	 */
	public function get_account_countries() {
		return $this->account_countries;
	}

	/**
	 * @return mixed
	 * @since 3.3.85
	 */
	public function get_accepted_currencies() {
		return $this->accepted_currencies;
	}

}
