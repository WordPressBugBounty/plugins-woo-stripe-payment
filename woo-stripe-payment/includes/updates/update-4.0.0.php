<?php
defined( 'ABSPATH' ) || exit();

// update Apple Pay "button_style" option to use new formats.
// Look at WC_Payment_Gateway_Stripe_ApplePay::get_button_theme

// For Google Pay, change button_style to button_type
// change button_color to button_theme

if ( function_exists( 'WC' ) ) {
	/**
	 * @var \WC_Payment_Gateway_Stripe $googlepay
	 */
	$googlepay = WC()->payment_gateways()->payment_gateways()['stripe_googlepay'] ?? null;
	/**
	 * @var \WC_Payment_Gateway_Stripe $applepay
	 */
	$applepay = WC()->payment_gateways()->payment_gateways()['stripe_applepay'] ?? null;
	/**
	 * @var \WC_Payment_Gateway_Stripe $payment_request_gateway
	 */
	$payment_request_gateway = WC()->payment_gateways()->payment_gateways()['stripe_payment_request'] ?? null;

	if ( $googlepay ) {
		$googlepay->settings['button_theme'] = $googlepay->get_option( 'button_color', 'black' );
		$googlepay->settings['button_type']  = $googlepay->get_option( 'button_style', 'buy' );
		update_option( $googlepay->get_option_key(), $googlepay->settings, 'yes' );
	}

	if ( $applepay ) {
		$theme = $applepay->get_option( 'button_style', 'black' );
		switch ( $theme ) {
			case 'apple-pay-button-white':
				$applepay->settings['button_theme'] = 'white';
				break;
			case 'apple-pay-button-white-with-line':
				$applepay->settings['button_theme'] = 'white-outline';
				break;
			default:
				$applepay->settings['button_theme'] = 'black';
				break;
		}
		update_option( $applepay->get_option_key(), $applepay->settings, 'yes' );
	}

	/**
	 * Replace the Payment Request Gateway with Google Pay. The Payment Request Gateway has been deprecated.
	 */
	if ( $payment_request_gateway ) {
		if ( $payment_request_gateway->enabled === 'yes' ) {
			if ( $googlepay ) {
				$googlepay->update_option( 'enabled', 'yes' );
			}
			$payment_request_gateway->update_option( 'enabled', 'no' );
		}
	}

	// 1. convert checkout_banner to express_checkout
	// 2. Enable the 'checkout' section since that's a default. Link Checkout however does not have 'checkout' as
	// a default.
	$gateways = [ 'stripe_applepay', 'stripe_googlepay', 'stripe_link_checkout', 'stripe_payment_request' ];
	foreach ( $gateways as $id ) {
		$gateway = WC()->payment_gateways()->payment_gateways()[ $id ] ?? null;
		/**
		 * @var \WC_Payment_Gateway_Stripe $gateway
		 */
		if ( $gateway ) {
			$sections = $gateway->get_option( 'payment_sections' );
			if ( is_array( $sections ) ) {
				if ( in_array( 'checkout_banner', $sections ) ) {
					$idx              = array_search( 'checkout_banner', $sections );
					$sections[ $idx ] = 'express_checkout';
				}
				if ( $gateway->id !== 'stripe_link_checkout' ) {
					// If the gateway is enabled, make sure the 'checkout' section is added.
					if ( $gateway->enabled === 'yes' && ! in_array( 'checkout', $sections, true ) ) {
						$sections[] = 'checkout';
					}
				}
				$gateway->update_option( 'payment_sections', $sections );
			}
		}
	}

	// enable bnpl messaging if payment_sections isn't empty
	$bnpl_gateways = [ 'stripe_affirm', 'stripe_afterpay', 'stripe_klarna' ];
	foreach ( $gateways as $id ) {
		$gateway = WC()->payment_gateways()->payment_gateways()[ $id ] ?? null;
		/**
		 * @var \WC_Payment_Gateway_Stripe $gateway
		 */
		if ( $gateway ) {
			$sections = $gateway->get_option( 'payment_sections', [] );
			if ( ! empty( $sections ) ) {
				$gateway->update_option( 'message_enabled', 'yes' );
			}
		}
	}

	// update the payment method configurations for UPM
	$upm = WC()->payment_gateways()->payment_gateways()['stripe_upm'] ?? null;
	if ( $upm ) {
		/**
		 * @var WC_Payment_Gateway_Stripe_UPM $upm
		 */
		$pmc_id = $upm->get_payment_method_configuration();
		if ( $pmc_id ) {
			if ( isset( $upm->client->paymentMethodConfigurations ) ) {
				$config = $upm->client->mode( wc_stripe_mode() )->paymentMethodConfigurations->retrieve( $pmc_id );
				if ( ! is_wp_error( $config ) ) {
					$upm->update_available_payment_methods(
						$upm->map_payment_config_to_payment_methods( $config ),
						wc_stripe_mode()
					);
				}
			}
		}
	}

	// Create the new icon_url
	$stripe_cc = WC()->payment_gateways()->payment_gateways()['stripe_cc'] ?? null;
	if ( $stripe_cc ) {
		$cards = $stripe_cc->get_option( 'cards', [] );
		if ( $cards ) {
			$stripe_cc->validate_card_icons_field( 'card_icons', $cards );
			$stripe_cc->update_option( 'card_icons', $cards );
			$stripe_cc->update_option( 'icon_url', $stripe_cc->settings['icon_url'] );
		}
	}

	/**
	 * @var \PaymentPlugins\Stripe\Payments\PaymentGatewaysController $payment_gateway_ctrl
	 */
	$payment_gateway_ctrl = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Payments\PaymentGatewaysController::class );
	if ( $applepay && $applepay->enabled === 'yes' ) {
		$applepay->set_post_data( [ 'woocommerce_stripe_applepay_enabled' => 'yes' ] );
		$payment_gateway_ctrl->process_payment_gateway_options( 'stripe_applepay' );
	}
	if ( $googlepay && $googlepay->enabled === 'yes' ) {
		$googlepay->set_post_data( [ 'woocommerce_stripe_googlepay_enabled' => 'yes' ] );
		$payment_gateway_ctrl->process_payment_gateway_options( 'stripe_googlepay' );
	}
}