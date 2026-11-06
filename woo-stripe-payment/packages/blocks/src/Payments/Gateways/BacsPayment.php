<?php


namespace PaymentPlugins\Stripe\Blocks\Payments\Gateways;


use PaymentPlugins\Stripe\Blocks\Payments\AbstractStripeLocalPayment;

/**
 * @since 4.0.20
 */
class BacsPayment extends AbstractStripeLocalPayment {

	protected $name = 'stripe_bacs';
}
