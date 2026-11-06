<?php

defined( 'ABSPATH' ) || exit();

/**
 * @since   3.1.2
 * @package PaymentPlugins\Classes
 * @author  PaymentPlugins
 *
 */
class WC_Stripe_Product_Gateway_Option {

	/**
	 *
	 * @var string
	 */
	private $id;

	/**
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 *
	 * @var WC_Product
	 */
	private $product;

	/**
	 *
	 * @var WC_Payment_Gateway_Stripe
	 */
	private $payment_method;

	/**
	 *
	 * @param int|WC_Product                                           $product
	 * @param \PaymentPlugins\Stripe\Payments\Gateways\AbstractGateway $payment_method
	 */
	public function __construct( $product, $payment_method ) {
		if ( ! is_object( $product ) ) {
			$this->product = wc_get_product( $product );
		} else {
			$this->product = $product;
		}
		$this->payment_method = $payment_method;

		$this->init_settings();
	}

	/**
	 * Return the ID of this product option.
	 */
	public function get_id() {
		return '_' . $this->payment_method->id . '_options';
	}

	/**
	 * Save the settings
	 */
	public function save() {
		$this->product->update_meta_data( $this->get_id(), $this->settings );
		$this->product->save();
	}

	/**
	 * Initialize the settings.
	 */
	public function init_settings() {
		if ( ! $this->settings && $this->product ) {
			$this->settings = $this->product->get_meta( $this->get_id() );
			$this->settings = is_array( $this->settings ) ? $this->settings : array();
			// Before 4.0.20, BNPL gateways stored the message setting in "enabled". It's saved in the new format on the next save().
			if ( $this->payment_method->supports( 'stripe_bnpl_msg' )
			     && array_key_exists( 'enabled', $this->settings )
			     && ! array_key_exists( 'message_enabled', $this->settings ) ) {
				$this->settings['message_enabled'] = $this->settings['enabled'];
				unset( $this->settings['enabled'] );
			}
			$this->settings = wp_parse_args( $this->settings, $this->get_default_values() );
		}
	}

	/**
	 * Return default options build from the payment gateway's options.
	 *
	 * @return array
	 */
	public function get_default_values() {
		$defaults = array(
			'enabled'     => $this->payment_method->enabled === 'yes' && $this->payment_method->product_checkout_enabled(),
			'charge_type' => $this->payment_method->get_option( 'charge_type' ),
		);
		if ( $this->payment_method->supports( 'stripe_bnpl_msg' ) ) {
			$message_sections = (array) $this->payment_method->get_option( 'message_sections', [] );

			$defaults['message_enabled'] = \wc_string_to_bool( $this->payment_method->get_option( 'message_enabled', 'yes' ) )
			                               && in_array( 'product', $message_sections, true );
		}

		return $defaults;
	}

	/**
	 *
	 * @param string $key
	 * @param mixed  $default
	 */
	public function get_option( $key, $default = null ) {
		if ( ! isset( $this->settings[ $key ] ) && null !== $default ) {
			$this->settings[ $key ] = $default;
		}

		return $this->settings[ $key ] ?? $default;
	}

	public function set_option( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	/**
	 * Returns true if the payment button is enabled for the product.
	 *
	 * @return bool
	 */
	public function enabled() {
		return $this->get_option( 'enabled', false );
	}

	/**
	 * Returns true if the BNPL message is enabled for the product.
	 *
	 * @return bool
	 * @since 4.0.20
	 */
	public function message_enabled() {
		return $this->get_option( 'message_enabled', false );
	}

	/**
	 * @return bool
	 * @since 3.3.21
	 */
	public function has_product() {
		return ! ! $this->product;
	}

}
