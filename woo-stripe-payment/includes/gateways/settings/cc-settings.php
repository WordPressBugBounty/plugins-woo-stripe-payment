<?php

return array(
	'enabled'           => array(
		'title'       => __( 'Enabled', 'woo-stripe-payment' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, your site can accept credit card payments through Stripe.', 'woo-stripe-payment' ),
	),
	'desc1'             => array(
		'type'        => 'description',
		'description' => sprintf( __( '%1$sTest cards%2$s', 'woo-stripe-payment' ), '<a target="_blank" href="https://stripe.com/docs/testing#cards">', '</a>' ),
	),
	'general_settings'  => array(
		'type'  => 'title',
		'title' => __( 'General Settings', 'woo-stripe-payment' ),
	),
	'title_text'        => array(
		'type'        => 'text',
		'title'       => __( 'Title', 'woo-stripe-payment' ),
		'default'     => __( 'Credit/Debit Cards', 'woo-stripe-payment' ),
		'desc_tip'    => true,
		'description' => __( 'Title of the credit card gateway' ),
	),
	'description'       => array(
		'title'       => __( 'Description', 'woo-stripe-payment' ),
		'type'        => 'text',
		'default'     => '',
		'description' => __( 'Leave blank if you don\'t want a description to show for the gateway.', 'woo-stripe-payment' ),
		'desc_tip'    => true,
	),
	'method_format'     => array(
		'title'       => __( 'Credit Card Display', 'woo-stripe-payment' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'options'     => wp_list_pluck( $this->get_payment_method_formats(), 'example' ),
		'value'       => '',
		'default'     => 'type_ending_in',
		'desc_tip'    => true,
		'description' => __( 'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.' ),
	),
	'charge_type'       => array(
		'type'        => 'select',
		'title'       => __( 'Charge Type', 'woo-stripe-payment' ),
		'default'     => 'capture',
		'class'       => 'wc-enhanced-select',
		'options'     => array(
			'capture'   => __( 'Capture', 'woo-stripe-payment' ),
			'authorize' => __( 'Authorize', 'woo-stripe-payment' ),
		),
		'desc_tip'    => true,
		'description' => __( 'This option determines whether the customer\'s funds are captured immediately or authorized and can be captured at a later date.', 'woo-stripe-payment' ),
	),
	'order_status'      => array(
		'type'        => 'select',
		'title'       => __( 'Order Status', 'woo-stripe-payment' ),
		'default'     => 'default',
		'class'       => 'wc-enhanced-select',
		'options'     => array_merge( array( 'default' => __( 'Default', 'woo-stripe-payment' ) ), wc_get_order_statuses() ),
		'tool_tip'    => true,
		'description' => __( 'This is the status of the order once payment is complete. If <b>Default</b> is selected, then WooCommerce will set the order status automatically based on internal logic which states if a product is virtual and downloadable then status is set to complete. Products that require shipping are set to Processing. Default is the recommended setting as it allows standard WooCommerce code to process the order status.',
			'woo-stripe-payment' ),
	),
	'save_card_enabled' => array(
		'type'        => 'checkbox',
		'value'       => 'yes',
		'default'     => 'yes',
		'title'       => __( 'Allow Credit Card Save', 'woo-stripe-payment' ),
		'desc_tip'    => false,
		'description' => __( 'If enabled, a checkbox will be available on the checkout page allowing your customers to save their credit card. The payment methods are stored securely in Stripe\'s vault and never touch your server. Note: if the cart contains a subscription, there will be no checkbox because the payment method will be saved automatically.',
			'woo-stripe-payment' ),
	),
	'force_3d_secure'   => array(
		'title'       => __( 'Force 3D Secure', 'woo-stripe-payment' ),
		'type'        => 'checkbox',
		'value'       => 'yes',
		'default'     => 'no',
		'desc_tip'    => false,
		'description' => sprintf( __( 'Stripe internally determines when 3D secure should be presented based on their SCA engine. If <strong>Force 3D Secure</strong> is enabled, 3D Secure will be forced for ALL credit card transactions. In test mode 3D secure only shows for %1$s3DS Test Cards%2$s regardless of this setting.',
			'woo-stripe-payment' ), '<a target="_blank" href="https://stripe.com/docs/testing#regulatory-cards">', '</a>' ),
	),
	'generic_error'     => array(
		'title'       => __( 'Generic Errors', 'woo-stripe-payment' ),
		'type'        => 'checkbox',
		'default'     => 'no',
		'value'       => 'yes',
		'desc_tip'    => true,
		'description' => __( 'If enabled, credit card errors will be generic when presented to the customer. Merchants may prefer to not provide details on why a card was not accepted for security purposes.', 'woo-stripe-payment' ),
	),
	'cards'             => array(
		'type'        => 'multiselect',
		'title'       => __( 'Credit Card Icons', 'woo-stripe-payment' ),
		'class'       => 'wc-enhanced-select stripe-accepted-cards',
		'default'     => array( 'amex', 'discover', 'visa', 'mastercard' ),
		'options'     => array(
			'visa'            => __( 'Visa', 'woo-stripe-payment' ),
			'amex'            => __( 'Amex', 'woo-stripe-payment' ),
			'discover'        => __( 'Discover', 'woo-stripe-payment' ),
			'mastercard'      => __( 'MasterCard', 'woo-stripe-payment' ),
			'jcb'             => __( 'JCB', 'woo-stripe-payment' ),
			'maestro'         => __( 'Maestro', 'woo-stripe-payment' ),
			'diners'          => __( 'Diners Club', 'woo-stripe-payment' ),
			'china_union_pay' => __( 'Union Pay', 'woo-stripe-payment' ),
		),
		'desc_tip'    => true,
		'description' => __( 'The selected icons will show customers which credit card brands you accept.', 'woo-stripe-payment' ),
	),
	'link_title'        => array(
		'type'  => 'title',
		'title' => __( 'Link Options', 'woo-stripe-payment' )
	),
	'link_enabled'      => array(
		'title'       => __( 'Faster Checkout With Link', 'woo-stripe-payment' ),
		'type'        => 'checkbox',
		'default'     => 'yes',
		'value'       => 'yes',
		'description' => __( 'Enable Stripe Link to offer customers a faster checkout experience. With Link, returning customers can pay with one click using their saved payment information, while new customers can create a 
		Link account to speed up future purchases. Link appears integrated within the credit card payment form.', 'woo-stripe-payment' )
	),
	'form_title'        => array(
		'type'  => 'title',
		'title' => __( 'Credit Card Form', 'woo-stripe-payment' ),
	),
	'form_type'         => array(
		'title'       => __( 'Card Form', 'woo-stripe-payment' ),
		'type'        => 'select',
		'options'     => array(
			'payment' => __( 'Stripe payment form', 'woo-stripe-payment' ),
			'inline'  => __( 'Stripe inline form', 'woo-stripe-payment' ),
			'custom'  => __( 'Custom form', 'woo-stripe-payment' ),
		),
		'default'     => 'payment',
		'desc_tip'    => true,
		'description' => __( 'The card form design that displays on payment pages.', 'woo-stripe-payment' ),
	),
	'theme'             => array(
		'title'             => __( 'Theme', 'woo-stripe-payment' ),
		'type'              => 'select',
		'default'           => 'stripe',
		'options'           => array(
			'stripe' => __( 'Default', 'woo-stripe-payment' ),
			'night'  => __( 'Night', 'woo-stripe-payment' ),
			'flat'   => __( 'Flat', 'woo-stripe-payment' )
		),
		'desc_tip'          => true,
		'description'       => __( 'The theme option controls how the Stripe payment form looks.', 'woo-stripe-payment' ),
		'custom_attributes' => array( 'data-show-if' => array( 'form_type' => 'payment' ) ),
	),
	'custom_form'       => array(
		'title'             => __( 'Custom Form', 'woo-stripe-payment' ),
		'type'              => 'select',
		'options'           => wp_list_pluck( wc_stripe_get_custom_forms(), 'label' ),
		'default'           => 'bootstrap',
		'description'       => __( 'The design of the credit card form.', 'woo-stripe-payment' ),
		'desc_tip'          => true,
		'custom_attributes' => array( 'data-show-if' => array( 'form_type' => 'custom' ) ),
	),
	'postal_enabled'    => array(
		'title'             => __( 'Postal Code', 'woo-stripe-payment' ),
		'type'              => 'checkbox',
		'default'           => 'no',
		'description'       => __( 'If enabled, the CC form will show the postal code on the checkout page. If disabled, the billing field\'s postal code will be used. The postal code will show on the Add Payment Method page for security reasons.', 'woo-stripe-payment' ),
		'desc_tip'          => true,
		'custom_attributes' => array( 'data-show-if' => array( 'form_type' => 'custom' ) ),
	),
	'notice_location'   => array(
		'title'       => __( 'Notices Location', 'woo-stripe-payment' ),
		'type'        => 'select',
		'default'     => 'acf',
		'options'     => array(
			'acf'    => __( 'Above card form', 'woo-stripe-payment' ),
			'bcf'    => __( 'Below card form', 'woo-stripe-payment' ),
			'toc'    => __( 'Top of checkout page', 'woo-stripe-payment' ),
			'custom' => __( 'Custom css selector', 'woo-stripe-payment' )
		),
		'desc_tip'    => true,
		'description' => __( 'This option allows you to control the location of credit card form validation errors. If you select custom, then you can provide a custom css selector for where the notices appear.', 'woo-stripe-payment' )
	),
	'notice_selector'   => array(
		'title'             => __( 'Notices Selector', 'woo-stripe-payment' ),
		'type'              => 'text',
		'default'           => 'div.payment_method_stripe_cc',
		'desc_tip'          => true,
		'description'       => __( 'This is the css selector where the card validation notices will be prepended to.', 'woo-stripe-payment' ),
		'custom_attributes' => array( 'data-show-if' => array( 'notice_location' => 'custom' ) ),
	)
);
