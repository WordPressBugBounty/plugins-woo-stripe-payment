<?php
defined( 'ABSPATH' ) || exit();

if ( function_exists( 'WC' ) ) {
	// Adaptive Pricing now defaults to enabled for new installs. Existing UPM settings saved before the
	// field existed would otherwise inherit that new default, so persist the previous default instead.
	// Runs before the gateways are loaded so the UPM gateway reads the stored value.
	$upm_settings = get_option( 'woocommerce_stripe_upm_settings', null );
	if ( is_array( $upm_settings ) && ! isset( $upm_settings['adaptive_pricing_config'] ) ) {
		$upm_settings['adaptive_pricing_config'] = [
			'enabled'                    => 'no',
			'currency_selector_position' => 'above_payment_methods',
		];
		update_option( 'woocommerce_stripe_upm_settings', $upm_settings );
	}

	// Before 4.0.20 Klarna always showed on checkout; make sure payment_sections includes it.
	$klarna = WC()->payment_gateways()->payment_gateways()['stripe_klarna'] ?? null;
	if ( $klarna ) {
		$sections = $klarna->get_option( 'payment_sections' );
		$sections = is_array( $sections ) ? $sections : [];
		if ( ! in_array( 'checkout', $sections, true ) ) {
			$sections[] = 'checkout';
			$klarna->update_option( 'payment_sections', $sections );
		}
	}

	// Express Elements now load the plugin's payment method configuration explicitly, so store its ID
	// per mode and make sure enabled express payment methods are turned on in it. Caught so a failure
	// can't block the version bump - express Elements fall back to the default configuration and the
	// ID is stored on the next gateway save.
	try {
		$payment_gateways_ctrl = wc_stripe_get_container()->get( \PaymentPlugins\Stripe\Payments\PaymentGatewaysController::class );
		$payment_gateways_ctrl->update_payment_method_configurations( $payment_gateways_ctrl->get_enabled_express_gateways() );
	} catch ( \Throwable $e ) {
		wc_stripe_log_error( sprintf( '4.0.20 update: error syncing the payment method configuration. %s', $e->getMessage() ) );
	}
}