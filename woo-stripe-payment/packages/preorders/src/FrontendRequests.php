<?php

namespace PaymentPlugins\Stripe\WooCommercePreOrders;

use PaymentPlugins\Stripe\ContextHandler;
use PaymentPlugins\Stripe\Utilities\ProductUtils;

/**
 * @package PaymentPlugins\WooCommercePreOrders\Stripe
 */
class FrontendRequests {

	/**
	 * @var ContextHandler
	 */
	private $context_handler;

	/**
	 * @param ContextHandler $context_handler
	 *
	 * @since 4.0.20
	 */
	public function __construct( ContextHandler $context_handler ) {
		$this->context_handler = $context_handler;
	}

	/**
	 * @return bool
	 */
	public function is_checkout_with_preorder_requires_tokenization() {
		return ( $this->context_handler->is_checkout() || $this->context_handler->is_cart() )
		       && WC()->cart
		       && \WC_Pre_Orders_Cart::cart_contains_pre_order()
		       && \WC_Pre_Orders_Product::product_is_charged_upon_release( \WC_Pre_Orders_Cart::get_pre_order_product() );
	}

	/**
	 * @return bool
	 */
	public function is_order_pay_with_preorder_requires_tokenization() {
		if ( $this->context_handler->is_order_pay() ) {
			$order = $this->context_handler->get_order_from_query();

			return $order instanceof \WC_Order
			       && \WC_Pre_Orders_Order::order_contains_pre_order( $order )
			       && \WC_Pre_Orders_Order::order_will_be_charged_upon_release( $order );
		}

		return false;
	}

	/**
	 * True when viewing a product page for a pre-order product that's charged upon release.
	 *
	 * @return bool
	 * @since 4.0.20
	 */
	public function is_product_with_preorder_requires_tokenization() {
		if ( ! $this->context_handler->is_product() ) {
			return false;
		}
		$product = ProductUtils::get_queried_product();

		return $product instanceof \WC_Product
		       && \WC_Pre_Orders_Product::product_can_be_pre_ordered( $product )
		       && \WC_Pre_Orders_Product::product_is_charged_upon_release( $product );
	}

}