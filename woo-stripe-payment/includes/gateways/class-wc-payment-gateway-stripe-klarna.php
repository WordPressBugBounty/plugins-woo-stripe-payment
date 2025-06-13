<?php

defined( 'ABSPATH' ) || exit();

if ( ! class_exists( 'WC_Payment_Gateway_Stripe_Local_Payment' ) ) {
	return;
}

/**
 * Class WC_Payment_Gateway_Stripe_Klarna
 *
 */
class WC_Payment_Gateway_Stripe_Klarna extends WC_Payment_Gateway_Stripe_Local_Payment {

	use WC_Stripe_Local_Payment_Intent_Trait;

	use \PaymentPlugins\Stripe\Traits\BNPLPaymentGatewayTrait;

	protected $payment_method_type = 'klarna';

	private $supported_locales = array(
		'de-AT',
		'en-AT',
		'da-DK',
		'en-DK',
		'fi-FI',
		'sv-FI',
		'en-FI',
		'de-DE',
		'en-DE',
		'nl-NL',
		'en-NL',
		'nb-NO',
		'en-NO',
		'sv-SE',
		'en-SE',
		'en-GB',
		'en-US',
		'es-US',
		'nl-BE',
		'fr-BE',
		'en-BE',
		'es-ES',
		'en-ES',
		'it-IT',
		'en-IT',
		'fr-FR',
		'en-FR',
		'en-IE',
		'pl-PL'
	);

	/**
	 * European Economic Area countries
	 *
	 * @var string[]
	 */
	private $eea_countries = array(
		'AT', // Austria
		'BE', // Belgium
		'HR', // Croatia
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DK', // Denmark
		'EE', // Estonia
		'FI', // Finland
		'FR', // France
		'DE', // Germany
		'GR', // Greece
		'IE', // Ireland
		'IT', // Italy
		'LV', // Latvia
		'LT', // Lithuania
		'LU', // Luxembourg
		'MT', // Malta
		'NL', // Netherlands
		'NO', // Norway
		'PL', // Poland
		'PT', // Portugal
		'RO', // Romania
		'SK', // Slovakia
		'SI', // Slovenia
		'ES', // Spain
		'SE'  // Sweden
	);

	private $account_countries = array(
		'AU', // Australia
		'AT', // Austria
		'BE', // Belgium
		'CA', // Canada
		'HR', // Croatia
		'CY', // Cyprus
		'CZ', // Czech Republic
		'DK', // Denmark
		'EE', // Estonia
		'FI', // Finland
		'FR', // France
		'DE', // Germany
		'GR', // Greece
		'IE', // Ireland
		'IT', // Italy
		'LV', // Latvia
		'LT', // Lithuania
		'LU', // Luxembourg
		'MT', // Malta
		'NL', // Netherlands
		'NZ', // New Zealand
		'NO', // Norway
		'PL', // Poland
		'PT', // Portugal
		'RO', // Romania
		'SK', // Slovakia
		'SI', // Slovenia
		'ES', // Spain
		'SE', // Sweden
		'CH', // Switzerland
		'GB', // United Kingdom
		'US'  // United States
	);

	public function __construct() {
		$this->local_payment_type = 'klarna';
		$this->currencies         = array( 'AUD', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'NOK', 'NZD', 'PLN', 'RON', 'SEK', 'USD' );
		$this->countries          = $this->limited_countries = array( 'AT', 'AU', 'BE', 'CA', 'CH', 'CZ', 'DE', 'DK', 'ES', 'FI', 'FR', 'GB', 'GR', 'IE', 'IT', 'NL', 'NO', 'NZ', 'PL', 'PT', 'RO', 'SE', 'US' );
		$this->id                 = 'stripe_klarna';
		$this->tab_title          = __( 'Klarna', 'woo-stripe-payment' );
		$this->token_type         = 'Stripe_Local';
		$this->method_title       = __( 'Klarna (Stripe) by Payment Plugins', 'woo-stripe-payment' );
		$this->method_description = __( 'Klarna gateway that integrates with your Stripe account.', 'woo-stripe-payment' );
		parent::__construct();
		$this->icon = stripe_wc()->assets_url( 'img/' . $this->get_option( 'icon' ) . '.svg' );
		add_filter( 'woocommerce_gateway_icon', array( $this, 'get_woocommerce_gateway_icon' ), 10, 2 );
	}

	public function init_supports() {
		parent::init_supports();
		$this->supports[] = 'wc_stripe_cart_checkout';
		$this->supports[] = 'wc_stripe_product_checkout';
		$this->supports[] = 'wc_stripe_mini_cart_checkout';
	}

	public function get_required_parameters() {
		return apply_filters( 'wc_stripe_klarna_get_required_parameters', array(
			'AUD' => array( 'AU' ),
			'CAD' => array( 'CA' ),
			'CHF' => array( 'CH' ),
			'CZK' => array( 'CZ' ),
			'DKK' => array( 'DK' ),
			'EUR' => array( 'AT', 'BE', 'DE', 'ES', 'FI', 'FR', 'GR', 'IE', 'IT', 'NL', 'PT' ),
			'GBP' => array( 'GB' ),
			'NOK' => array( 'NO' ),
			'NZD' => array( 'NZ' ),
			'PLN' => array( 'PL' ),
			'RON' => array( 'RO' ),
			'SEK' => array( 'SE' ),
			'USD' => array( 'US' ),
		), $this );
	}

	/**
	 * @param string $currency
	 * @param string $billing_country
	 * @param float  $total
	 *
	 * @return bool
	 */
	public function validate_local_payment_available( $currency, $billing_country, $total ) {
		$result = false;
		/**
		 * https://docs.stripe.com/payments/klarna
		 * The rules for Klarna are as follows:
		 *  1.If the Stripe account is based in an EEA country, UK, or Switzerland, the account can offer
		 * Klarna to the customer, as long as the customer is in EEA, UK, or Switzerland and the store currency matches the currency of the customer
		 * 2. For all other countries, accounts can only transact with customers in the same country as long
		 * as the store currency matches the country's currency. So, if account is US based, customer billing_country must be
		 * US and currency USD.
		 */
		if ( $billing_country ) {
			$account_country = stripe_wc()->account_settings->get_account_country( wc_stripe_mode() );
			$params          = $this->get_required_parameters();
			if ( $this->is_eea( $account_country ) || in_array( $account_country, [ 'GB', 'CH' ] ) ) {
				if ( $this->is_eea( $billing_country ) || in_array( $billing_country, [ 'GB', 'CH' ] ) ) {
					if ( isset( $params[ $currency ] ) && in_array( $billing_country, $params[ $currency ] ) !== false ) {
						$result = true;
					}
				}
			} else {
				$result = $account_country === $billing_country
				          && ( isset( $params[ $currency ] )
				               && in_array( $billing_country, $params[ $currency ] ) !== false );
			}
		}

		return $result;
	}

	public function add_stripe_order_args( &$args, $order, $intent = null ) {
		$args['payment_method_options'] = array(
			'klarna' => array(
				'preferred_locale' => $this->get_formatted_locale_from_order( $order )
			)
		);
	}

	/**
	 * Returns a formatted locale based on the billing country for the order.
	 *
	 * @param WC_Order $order
	 *
	 * @return string
	 */
	private function get_formatted_locale_from_order( $order ) {
		$country = $order->get_billing_country();
		switch ( $country ) {
			case 'US':
				$locale = 'en-US';
				break;
			case 'GB':
				$locale = 'en-GB';
				break;
			case 'AT':
				$locale = 'de-AT';
				break;
			case 'BE':
				$locale = 'fr-BE';
				break;
			case 'DK':
				$locale = 'da-DK';
				break;
			case 'NO':
				$locale = 'nb-NO';
				break;
			case 'SE':
				$locale = 'sv-SE';
				break;
			case 'PL':
				$locale = 'pl-PL';
				break;
			default:
				$locale = strtolower( $country ) . '-' . strtoupper( $country );
		}
		if ( ! in_array( $locale, $this->supported_locales, true ) ) {
			$locale = 'en-US';
		}

		return $locale;
	}

	public function get_local_payment_settings() {
		return wp_parse_args(
			array(
				'charge_type'      => array(
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
				'order_status'     => array(
					'type'        => 'select',
					'title'       => __( 'Order Status', 'woo-stripe-payment' ),
					'default'     => 'default',
					'class'       => 'wc-enhanced-select',
					'options'     => array_merge( array( 'default' => __( 'Default', 'woo-stripe-payment' ) ), wc_get_order_statuses() ),
					'tool_tip'    => true,
					'description' => __( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.',
						'woo-stripe-payment' ),
				),
				'icon'             => array(
					'title'       => __( 'Icon', 'woo-stripe-payment' ),
					'type'        => 'select',
					'options'     => array(
						'klarna'      => __( 'Black text', 'woo-stripe-payment' ),
						'klarna_pink' => __( 'Pink background black text', 'woo-stripe-payment' )
					),
					'default'     => 'klarna_pink',
					'desc_tip'    => true,
					'description' => __( 'This is the icon style that appears next to the gateway on the checkout page.', 'woo-stripe-payment' ),
				),
				'payment_sections' => array(
					'type'        => 'multiselect',
					'title'       => __( 'Message Sections', 'woo-stripe-payment' ),
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'checkout' => __( 'Checkout page', 'woo-stripe-payment' ),
						'product'  => __( 'Product Page', 'woo-stripe-payment' ),
						'cart'     => __( 'Cart Page', 'woo-stripe-payment' ),
						'shop'     => __( 'Shop/Category Page', 'woo-stripe-payment' )
					),
					'default'     => array(),
					'desc_tip'    => true,
					'description' => __( 'These are the sections where the Klarna messaging will be enabled.',
						'woo-stripe-payment' ),
				),
				'cart_location'    => array(
					'title'       => __( 'Cart Message Location', 'woo-stripe-payment' ),
					'type'        => 'select',
					'default'     => 'below_total',
					'options'     => array(
						'below_total'           => __( 'Below Total', 'woo-stripe-payment' ),
						'below_checkout_button' => __( 'Below Checkout Button', 'woo-stripe-payment' )
					),
					'desc_tip'    => true,
					'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'woo-stripe-payment' )
				),
				'product_location' => array(
					'title'       => __( 'Product Message Location', 'woo-stripe-payment' ),
					'type'        => 'select',
					'default'     => 'below_price',
					'options'     => array(
						'above_price'       => __( 'Above Price', 'woo-stripe-payment' ),
						'below_price'       => __( 'Below Price', 'woo-stripe-payment' ),
						'below_add_to_cart' => __( 'Below Add to Cart', 'woo-stripe-payment' )
					),
					'desc_tip'    => true,
					'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'woo-stripe-payment' )
				),
				'shop_location'    => array(
					'title'       => __( 'Shop/Category Message Location', 'woo-stripe-payment' ),
					'type'        => 'select',
					'default'     => 'below_price',
					'options'     => array(
						'below_price'       => __( 'Below Price', 'woo-stripe-payment' ),
						'below_add_to_cart' => __( 'Below Add to Cart', 'woo-stripe-payment' )
					),
					'desc_tip'    => true,
					'description' => __( 'This option controls the location in which the messaging for the payment method will appear.', 'woo-stripe-payment' )
				)
			),
			parent::get_local_payment_settings()
		);
	}

	public function enqueue_checkout_scripts( $scripts ) {
		parent::enqueue_checkout_scripts( $scripts );
		$scripts->assets_api->register_script( 'wc-stripe-klarna-checkout', 'assets/build/klarna-message.js', array( 'wc-stripe-vendors', 'wc-stripe-local-payment' ) );
		wp_enqueue_script( 'wc-stripe-klarna-checkout' );
	}

	public function enqueue_product_scripts( $scripts ) {
		$scripts->assets_api->register_script( 'wc-stripe-klarna-product', 'assets/build/klarna-message.js', array( 'wc-stripe-vendors' ) );
		wp_enqueue_script( 'wc-stripe-klarna-product' );
		$scripts->localize_script( 'wc-stripe-klarna-product', $this->get_localized_params() );
	}

	public function enqueue_cart_scripts( $scripts ) {
		$scripts->assets_api->register_script( 'wc-stripe-klarna-cart', 'assets/build/klarna-message.js', array( 'wc-stripe-vendors' ) );
		wp_enqueue_script( 'wc-stripe-klarna-cart' );
		$this->enqueue_payment_method_styles();
		$scripts->localize_script( 'wc-stripe-klarna-cart', $this->get_localized_params() );
	}

	/**
	 * @param \PaymentPlugins\Stripe\Assets\AssetsApi    $assets_api
	 * @param \PaymentPlugins\Stripe\Assets\AssetDataApi $asset_data
	 *
	 * @return void
	 */
	public function enqueue_category_scripts( $assets_api, $asset_data ) {
		$assets_api->register_script( 'wc-stripe-klarna-category', 'assets/build/klarna-message.js', array( 'wc-stripe-vendors' ) );
		$asset_data->add( $this->id, array(
			'messageOptions' => array(
				'countryCode'        => stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ),
				'paymentMethodTypes' => array( 'klarna' )
			)
		) );
		wp_enqueue_script( 'wc-stripe-klarna-category' );
	}

	public function get_localized_params() {
		return array_merge( parent::get_localized_params(), array(
			'messageOptions' => array(
				'countryCode'        => stripe_wc()->account_settings->get_account_country( wc_stripe_mode() ),
				'paymentMethodTypes' => array( 'klarna' )
			)
		) );
	}

	public function cart_fields() {
		$this->enqueue_frontend_scripts( 'cart' );
		$this->output_display_items( 'cart' );
	}

	public function product_fields() {
		$this->enqueue_frontend_scripts( 'product' );
		$this->output_display_items( 'product' );
	}

	/**
	 * Returns true if the provided country is part of the European Economic Area (EEA)
	 *
	 * @since 3.3.81
	 * @return bool
	 */
	private function is_eea( $country ) {
		return \in_array( $country, $this->eea_countries, true );
	}

	/**
	 * @since 3.3.81
	 * @return string[]
	 */
	public function get_eea_countries() {
		return $this->eea_countries;
	}

	public function get_payment_description() {
		ob_start();
		?>
        <span><?php esc_html_e( 'The rules for Klarna are as follows:', 'woo-stripe-payment' ) ?></span>
        <a href="https://docs.stripe.com/payments/klarna" target="_blank"><?php esc_html_e( 'Learn more', 'woo-stripe-payment' ) ?></a>

        <div class="klarna-rules">
            <h4><?php esc_html_e( 'For EEA, UK, and Switzerland accounts:', 'woo-stripe-payment' ) ?></h4>
            <div class="klarna-section">
				<?php esc_html_e( 'You can offer Klarna if:', 'woo-stripe-payment' ) ?>
                <ol>
                    <li><?php esc_html_e( 'Your Stripe account is based in EEA, UK, or Switzerland', 'woo-stripe-payment' ) ?></li>
                    <li><?php esc_html_e( 'Your customer is located in EEA, UK, or Switzerland', 'woo-stripe-payment' ) ?></li>
                    <li><?php esc_html_e( 'Your store currency matches the customer\'s local currency', 'woo-stripe-payment' ) ?></li>
                </ol>
            </div>

            <h4><?php esc_html_e( 'For all other countries:', 'woo-stripe-payment' ) ?></h4>
            <div class="klarna-section">
				<?php esc_html_e( 'Transactions are only allowed when:', 'woo-stripe-payment' ) ?>
                <ol>
                    <li><?php esc_html_e( 'Your customer is in the same country as your Stripe account', 'woo-stripe-payment' ) ?></li>
                    <li><?php esc_html_e( 'Your store currency matches your country\'s currency', 'woo-stripe-payment' ) ?></li>
                </ol>
            </div>

            <p class="klarna-example">
				<?php esc_html_e( 'Example: If your Stripe account is US-based, your customer must be in the US and your store must use USD.', 'woo-stripe-payment' ) ?>
            </p>
        </div>
		<?php
		return ob_get_clean();
	}

}
