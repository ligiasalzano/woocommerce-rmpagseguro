<?php
/**
 * WooCommerce PagSeguro API class
 *
 * @package WooCommerce_PagSeguro/Classes/API
 * @version 2.12.0
 * Arquivo modificado por Ricardo Martins em 4 de Setembro de 2019 (GPLv2)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce PagSeguro API.
 */
class WC_PagSeguro_API {

	/**
	 * Gateway class.
	 *
	 * @var WC_PagSeguro_Gateway
	 */
	protected $gateway;

	/**
	 * Constructor.
	 *
	 * @param WC_PagSeguro_Gateway $gateway Payment Gateway instance.
	 */
	public function __construct( $gateway = null ) {
		$this->gateway = $gateway;
	}

	/**
	 * Get the API environment.
	 *
	 * @return string
	 */
	protected function get_environment() {
		return ( 'yes' == $this->gateway->sandbox ) ? 'sandbox.' : '';
	}

	/**
	 * Use stcpagseguro.ricardomartins.net.br instead of stc.pagseguro.uol.com.br (less stable)
	 * @return bool
	 */
	protected function use_static_mirror() {
		return ( 'yes' == $this->gateway->stcmirror );
	}

	/**
	 * Get the checkout URL.
	 *
	 * @return string.
	 */
	protected function get_checkout_url() {
		//modified by Ricardo Martins
		$appUrl = 'https://ws.ricardomartins.net.br/pspro/v7/wspagseguro/v2/checkout';

		if ( 'yes' == $this->gateway->sandbox ) {
			$appUrl = add_query_arg( array( 'isSandbox' => true ), $appUrl );
		}

		return $appUrl;
	}

	/**
	 * Get the sessions URL.
	 *
	 * @return string.
	 */
	protected function get_sessions_url() {
		//modified by Ricardo Martins
		$appUrl = 'https://ws.ricardomartins.net.br/pspro/v7/wspagseguro/v2/sessions';

		if ( 'yes' == $this->gateway->sandbox ) {
			$appUrl = add_query_arg( array( 'isSandbox' => true ), $appUrl );
		}

		return $appUrl;
	}

	/**
	 * Get the payment URL.
	 *
	 * @param  string $token Payment code.
	 *
	 * @return string.
	 */
	protected function get_payment_url( $token ) {
		//modified by Ricardo Martins
		return 'https://' . $this->get_environment() . 'pagseguro.uol.com.br/v2/checkout/payment.html?code=' . $token;
	}

	/**
	 * Get the transactions URL.
	 *
	 * @return string.
	 */
	protected function get_transactions_url() {
		//modified by Ricardo Martins
		$appUrl = 'https://ws.ricardomartins.net.br/pspro/v7/wspagseguro/v2/transactions';

		if ( 'yes' == $this->gateway->sandbox ) {
			$appUrl = add_query_arg( array( 'isSandbox' => true ), $appUrl );
		}

		return $appUrl;
	}

	/**
	 * Get the lightbox URL.
	 *
	 * @return string.
	 */
	public function get_lightbox_url() {
		if ($this->use_static_mirror()) {
			return 'yes' == $this->gateway->sandbox
				? 'https://stcpagsegurosandbox.ricardomartins.net.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js'
				: 'https://stcpagseguro.ricardomartins.net.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js';
		}

		return 'https://stc.' . $this->get_environment() . 'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.lightbox.js';
	}

	/**
	 * Get the direct payment URL.
	 *
	 * @return string.
	 */
	public function get_direct_payment_url() {
		if ($this->use_static_mirror()) {
			return 'yes' == $this->gateway->sandbox
				? 'https://stcpagsegurosandbox.ricardomartins.net.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js'
				: 'https://stcpagseguro.ricardomartins.net.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
		}

		return 'https://stc.' . $this->get_environment() . 'pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
	}

	/**
	 * Get the notification URL.
	 *
	 * @return string.
	 */
	protected function get_notification_url($notificationCode) {
		//modified by Ricardo Martins
		$appUrl = 'https://ws.ricardomartins.net.br/pspro/v7/wspagseguro/v3/transactions/notifications/' . esc_attr($notificationCode);

		if ( 'yes' == $this->gateway->sandbox ) {
			$appUrl = add_query_arg( array( 'isSandbox' => true ), $appUrl );
		}

		return $appUrl;
	}

	/**
	 * Check if is localhost.
	 *
	 * @return bool
	 */
	protected function is_localhost() {
		$url  = home_url( '/' );
		$home = untrailingslashit( str_replace( array( 'https://', 'http://' ), '', $url ) );

		return in_array( $home, array( 'localhost', '127.0.0.1' ) );
	}

	/**
	 * Money format.
	 *
	 * @param  int/float $value Value to fix.
	 *
	 * @return float            Fixed value.
	 */
	protected function money_format( $value ) {
		return number_format( $value, 2, '.', '' );
	}

	/**
	 * Sanitize the item description.
	 *
	 * @param  string $description Description to be sanitized.
	 *
	 * @return string
	 */
	protected function sanitize_description( $description ) {
		return sanitize_text_field( substr( $description, 0, 95 ) );
	}

	/**
	 * Get payment name by type.
	 *
	 * @param  int $value Payment Type number.
	 *
	 * @return string
	 */
	public function get_payment_name_by_type( $value ) {
		$types = array(
			1 => __( 'Credit Card', 'woo-pagseguro-rm' ),
			2 => __( 'Billet', 'woo-pagseguro-rm' ),
			3 => __( 'Bank Transfer', 'woo-pagseguro-rm' ),
			4 => __( 'PagSeguro credit', 'woo-pagseguro-rm' ),
			5 => __( 'Oi Paggo', 'woo-pagseguro-rm' ),
			7 => __( 'Account deposit', 'woo-pagseguro-rm' ),
            8 => __( 'Emergential Card Caixa (Debit)'),
            11 => __('PIX')
		);

		return isset( $types[ $value ] ) ? $types[ $value ] : __( 'Unknown', 'woo-pagseguro-rm' );
	}

	/**
	 * Get payment method name.
	 *
	 * @param  int $value Payment method number.
	 *
	 * @return string
	 */
	public function get_payment_method_name( $value ) {
		$value = (int)$value;
		$credit = __( 'Credit Card %s', 'woo-pagseguro-rm' );
		$ticket = __( 'Billet %s', 'woo-pagseguro-rm' );
		$debit  = __( 'Bank Transfer %s', 'woo-pagseguro-rm' );

		$methods = array(
			101 => sprintf( $credit, 'Visa' ),
			102 => sprintf( $credit, 'MasterCard' ),
			103 => sprintf( $credit, 'American Express' ),
			104 => sprintf( $credit, 'Diners' ),
			105 => sprintf( $credit, 'Hipercard' ),
			106 => sprintf( $credit, 'Aura' ),
			107 => sprintf( $credit, 'Elo' ),
			108 => sprintf( $credit, 'PLENOCard' ),
			109 => sprintf( $credit, 'PersonalCard' ),
			110 => sprintf( $credit, 'JCB' ),
			111 => sprintf( $credit, 'Discover' ),
			112 => sprintf( $credit, 'BrasilCard' ),
			113 => sprintf( $credit, 'FORTBRASIL' ),
			114 => sprintf( $credit, 'CARDBAN' ),
			115 => sprintf( $credit, 'VALECARD' ),
			116 => sprintf( $credit, 'Cabal' ),
			117 => sprintf( $credit, 'Mais!' ),
			118 => sprintf( $credit, 'Avista' ),
			119 => sprintf( $credit, 'GRANDCARD' ),
			201 => sprintf( $ticket, 'Bradesco' ),
			202 => sprintf( $ticket, 'Santander' ),
			301 => sprintf( $debit, 'Bradesco' ),
			302 => sprintf( $debit, 'Itaú' ),
			303 => sprintf( $debit, 'Unibanco' ),
			304 => sprintf( $debit, 'Banco do Brasil' ),
			305 => sprintf( $debit, 'Real' ),
			306 => sprintf( $debit, 'Banrisul' ),
			307 => sprintf( $debit, 'HSBC' ),
			401 => __( 'PagSeguro credit', 'woo-pagseguro-rm' ),
			501 => __( 'Oi Paggo', 'woo-pagseguro-rm' ),
			701 => __( 'Account deposit', 'woo-pagseguro-rm' ),
		);

		return isset( $methods[ $value ] ) ? $methods[ $value ] : __( 'Unknown', 'woo-pagseguro-rm' );
	}

	/**
	 * Get the paymet method.
	 *
	 * @param  string $method Payment method.
	 *
	 * @return string
	 */
	public function get_payment_method( $method ) {
		$methods = array(
			'credit-card'    => 'creditCard',
			'banking-ticket' => 'boleto',
			'bank-transfer'  => 'eft',
		);

		return isset( $methods[ $method ] ) ? $methods[ $method ] : '';
	}

	/**
	 * Get error message.
	 *
	 * @param  int $code Error code.
	 *
	 * @return string
	 */
	public function get_error_message( $code ) {
		$code = (string) $code;

		$messages = array(
			'11013' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'11014' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'53018' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'53019' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'53020' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'53021' => __( 'Please enter with a valid phone number with DDD. Example: (11) 5555-5555.', 'woo-pagseguro-rm' ),
			'11017' => __( 'Please enter with a valid zip code number.', 'woo-pagseguro-rm' ),
			'53022' => __( 'Please enter with a valid zip code number.', 'woo-pagseguro-rm' ),
			'53023' => __( 'Please enter with a valid zip code number.', 'woo-pagseguro-rm' ),
			'53053' => __( 'Please enter with a valid zip code number.', 'woo-pagseguro-rm' ),
			'53054' => __( 'Please enter with a valid zip code number.', 'woo-pagseguro-rm' ),
			'11164' => __( 'Please enter with a valid CPF number.', 'woo-pagseguro-rm' ),
			'53110' => '',
			'53111' => __( 'Please select a bank to make payment by bank transfer.', 'woo-pagseguro-rm' ),
			'53045' => __( 'Credit card holder CPF is required.', 'woo-pagseguro-rm' ),
			'53047' => __( 'Credit card holder birthdate is required.', 'woo-pagseguro-rm' ),
			'53042' => __( 'Credit card holder name is required.', 'woo-pagseguro-rm' ),
			'53049' => __( 'Credit card holder phone is required.', 'woo-pagseguro-rm' ),
			'53051' => __( 'Credit card holder phone is required.', 'woo-pagseguro-rm' ),
			'11020' => __( 'The address complement is too long, it cannot be more than 40 characters.', 'woo-pagseguro-rm' ),
			'53028' => __( 'The address complement is too long, it cannot be more than 40 characters.', 'woo-pagseguro-rm' ),
			'53029' => __( '<strong>Neighborhood</strong> is a required field.', 'woo-pagseguro-rm' ),
			'53046' => __( 'Credit card holder CPF invalid.', 'woo-pagseguro-rm' ),
			'53122' => __( 'Invalid email domain. You must use an email @sandbox.pagseguro.com.br while you are using the PagSeguro Sandbox.', 'woo-pagseguro-rm' ),
			'53081' => __( 'The customer email can not be the same as the PagSeguro account owner.', 'woo-pagseguro-rm' ),
		);

		if ( isset( $messages[ $code ] ) ) {
			return $messages[ $code ];
		}

		return __( 'An error has occurred while processing your payment, please review your data and try again. Or contact us for assistance.', 'woo-pagseguro-rm' );
	}

	/**
	 * Get the available payment methods.
	 *
	 * @return array
	 */
	protected function get_available_payment_methods() {
		$methods = array();

		if ( 'yes' == $this->gateway->tc_credit ) {
			$methods[] = 'credit-card';
		}

		if ( 'yes' == $this->gateway->tc_transfer ) {
			$methods[] = 'bank-transfer';
		}

		if ( 'yes' == $this->gateway->tc_ticket ) {
			$methods[] = 'banking-ticket';
		}

		return $methods;
	}

	/**
	 * Do requests in the PagSeguro API.
	 *
	 * @param  string $url      URL.
	 * @param  string $method   Request method.
	 * @param  array  $data     Request data.
	 * @param  array  $headers  Request headers.
	 *
	 * @return array            Request response.
	 */
	protected function do_request( $url, $method = 'POST', $data = array(), $headers = array() ) {
		$params = array(
			'method'  => $method,
			'timeout' => 60,
		);

		if ( 'POST' == $method && ! empty( $data ) ) {
			$params['body'] = $data;
		}

		if ( ! empty( $headers ) ) {
			$params['headers'] = $headers;
		}

		return wp_safe_remote_post( $url, $params );
	}

	/**
	 * Safe load XML.
	 *
	 * @param  string $source  XML source.
	 * @param  int    $options DOMDpocment options.
	 *
	 * @return SimpleXMLElement|bool
	 */
	protected function safe_load_xml( $source, $options = 0 ) {
		$old = null;

		if ( '<' !== substr( $source, 0, 1 ) ) {
			return false;
		}

		if ( function_exists( 'libxml_disable_entity_loader' ) && version_compare(phpversion(), '8.0.0', '<')) {
			$old = libxml_disable_entity_loader( true );
		}

		$dom    = new DOMDocument();
		$return = $dom->loadXML( $source, $options );

		if ( ! is_null( $old ) && version_compare(phpversion(), '8.0.0', '<')) {
			libxml_disable_entity_loader( $old );
		}

		if ( ! $return ) {
			return false;
		}

		if ( isset( $dom->doctype ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Unsafe DOCTYPE Detected while XML parsing' );
			}

			return false;
		}

		return simplexml_import_dom( $dom );
	}

	/**
	 * Get order items.
	 *
	 * @param  WC_Order $order Order data.
	 *
	 * @return array           Items list, extra amount and shipping cost.
	 */
	protected function get_order_items( $order ) {
		$items         = array();
		$extra_amount  = 0;
		$shipping_cost = 0;

		// Force only one item.
		if ( 'yes' == $this->gateway->send_only_total ) {
			$items[] = array(
				'description' => $this->sanitize_description( sprintf( __( 'Order %s', 'woo-pagseguro-rm' ), $order->get_order_number() ) ),
				'amount'      => $this->money_format( $order->get_total() ),
				'quantity'    => 1,
			);
		} else {

			// Products.
			if ( 0 < count( $order->get_items() ) ) {
				foreach ( $order->get_items() as $order_item ) {
					if ( $order_item['qty'] ) {
						$item_total = $order->get_item_total( $order_item, false );
						if ( 0 >= (float) $item_total ) {
							continue;
						}

						$item_name = $order_item['name'];

						if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '3.0', '<' ) ) {
							if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.4.0', '<' ) ) {
								$item_meta = new WC_Order_Item_Meta( $order_item['item_meta'] );
							} else {
								$item_meta = new WC_Order_Item_Meta( $order_item );
							}

							if ( $meta = $item_meta->display( true, true ) ) {
								$item_name .= ' - ' . $meta;
							}
						}

						$items[] = array(
							'description' => $this->sanitize_description( str_replace( '&ndash;', '-', $item_name ) ),
							'amount'      => $this->money_format( $item_total ),
							'quantity'    => $order_item['qty'],
						);
					}
				}
			}

			// Fees.
			if ( 0 < count( $order->get_fees() ) ) {
				foreach ( $order->get_fees() as $fee ) {
					if ( 0 >= (float) $fee['line_total'] ) {
						continue;
					}

					$items[] = array(
						'description' => $this->sanitize_description( $fee['name'] ),
						'amount'      => $this->money_format( $fee['line_total'] ),
						'quantity'    => 1,
					);
				}
			}

			// Taxes.
			if ( 0 < count( $order->get_taxes() ) ) {
				foreach ( $order->get_taxes() as $tax ) {
					$tax_total = $tax['tax_amount'] + $tax['shipping_tax_amount'];
					if ( 0 >= (float) $tax_total ) {
						continue;
					}

					$items[] = array(
						'description' => $this->sanitize_description( $tax['label'] ),
						'amount'      => $this->money_format( $tax_total ),
						'quantity'    => 1,
					);
				}
			}

			// Shipping Cost.
			if ( 0 < $order->get_total_shipping() ) {
				$shipping_cost = $this->money_format( $order->get_total_shipping() );
			}

			// Discount.
			if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.3', '<' ) ) {
				if ( 0 < $order->get_order_discount() ) {
					$extra_amount = '-' . $this->money_format( $order->get_order_discount() );
				}
			}
		}

		return array(
			'items'         => $items,
			'extra_amount'  => $extra_amount,
			'shipping_cost' => $shipping_cost,
		);
	}

	/**
	 * Get the checkout xml.
	 *
	 * @param WC_Order $order Order data.
	 * @param array    $posted Posted data.
	 *
	 * @return string
	 */
	protected function get_checkout_xml( $order, $posted, $render = true ) {
		$data    = $this->get_order_items( $order );
		$ship_to = isset( $posted['ship_to_different_address'] ) ? true : false;

		// Creates the checkout xml.
		$xml = new WC_PagSeguro_XML( '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><checkout></checkout>' );
		$xml->add_currency( get_woocommerce_currency() );

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_id' ) ) {
			$xml->add_reference( $this->gateway->invoice_prefix . $order->get_id() );
			$xml->add_sender_data( $order );
			$xml->add_shipping_data( $order, $ship_to, $data['shipping_cost'] );
		} else {
			// @codingStandardsIgnoreStart
			$xml->add_reference( $this->gateway->invoice_prefix . $order->id );
			// @codingStandardsIgnoreEnd
			$xml->add_legacy_sender_data( $order );
			$xml->add_legacy_shipping_data( $order, $ship_to, $data['shipping_cost'] );
		}

		$xml->add_items( $data['items'] );
		$xml->add_extra_amount( $data['extra_amount'] );

		// Checks if is localhost... PagSeguro not accept localhost urls!
		if ( ! in_array( $this->is_localhost(), array( 'localhost', '127.0.0.1' ) ) ) {
			$xml->add_redirect_url( $this->gateway->get_return_url( $order ) );
			$xml->add_notification_url( WC()->api_request_url( 'WC_PagSeguro_Gateway' ) );
		}

		$xml->add_max_uses( 1 );
		$xml->add_max_age( 120 );

		// Filter the XML.
		$xml = apply_filters( 'woocommerce_pagseguro_checkout_xml', $xml, $order );

		if (!$render){
			return $xml;
		}
		return $xml->render();
	}

	protected function get_checkout_post($order, $posted) {
		$xml = $this->get_checkout_xml($order, $posted, false);

		$post = array(
			'currency' => (string)$xml->currency,
			'maxUses' => (string)$xml->maxUses,
			'maxAge' => (string)$xml->maxAge,
			'notificationURL' => (string)$xml->notificationURL,
			'reference' => (string)$xml->reference,
			'senderAreaCode' => (string)$xml->sender->phone->areaCode,
			'public_key' => (string)$this->gateway->settings['public_key'],
			'senderPhone' => (string)$xml->sender->phone->number,
			'senderName' => (string)$xml->sender->name,
			'shippingType' => (string)$xml->shipping->type,
			'shippingCost' => (string)$xml->shipping->cost,
			'shippingAddressCountry' => (string)$xml->shipping->address->country,
			'shippingAddressStreet' => (string)$xml->shipping->address->street,
			'shippingAddressNumber' => (string)$xml->shipping->address->number,
			'shippingAddressComplement' => (string)$xml->shipping->address->complement,
			'shippingAddressDistrict' => (string)$xml->shipping->address->district,
			'shippingAddressCity' => (string)$xml->shipping->address->city,
			'shippingAddressPostalCode' => (string)$xml->shipping->address->postalCode,
			'shippingAddressState' => (string)$xml->shipping->address->state,
		);

		$senderIp = $this->get_sender_ip();
		if ($senderIp) {
			$post['senderIp'] = $senderIp;
		}

		if (isset($xml->redirectURL)) {
			$post['redirectURL'] = (string)$xml->redirectURL;
		}

		foreach($xml->items->item as $item) {
			$post['itemId' . (string)$item->id] = (string)$item->id;
			$post['itemDescription' . (string)$item->id] = (string)$item->description;
			$post['itemAmount' . (string)$item->id] = (string)$item->amount;
			$post['itemQuantity' . (string)$item->id] = (string)$item->quantity;
		}

		switch ((string)$xml->sender->documents->document->type){
			case 'CNPJ':
				$post['senderCNPJ'] = (string)$xml->sender->documents->document->value;
				break;
			case 'CPF':
			default:
				$post['senderCPF'] = (string)$xml->sender->documents->document->value;
		}
		$post = $this->convert_encoding($post);
		return $post;

	}
	/**
	 * Get the direct payment xml.
	 *
	 * @param WC_Order $order Order data.
	 * @param array    $posted Posted data.
	 *
	 * @return string|WC_PagSeguro_XML
	 */
	protected function get_payment_xml( $order, $posted, $render=true ) {
		$data    = $this->get_order_items( $order );
		$ship_to = isset( $posted['ship_to_different_address'] ) ? true : false;
		$method  = isset( $posted['pagseguro_payment_method'] ) ? $this->get_payment_method( $posted['pagseguro_payment_method'] ) : '';
		$hash    = isset( $posted['pagseguro_sender_hash'] ) ? sanitize_text_field( $posted['pagseguro_sender_hash'] ) : '';

		// Creates the payment xml.
		$xml = new WC_PagSeguro_XML( '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><payment></payment>' );
		$xml->add_mode( 'default' );
		$xml->add_method( $method );
		$xml->add_currency( get_woocommerce_currency() );
		if ( ! in_array( $this->is_localhost(), array( 'localhost', '127.0.0.1' ) ) ) {
			$xml->add_notification_url( WC()->api_request_url( 'WC_PagSeguro_Gateway' ) );
		}
		$xml->add_items( $data['items'] );
		$xml->add_extra_amount( $data['extra_amount'] );

		// WooCommerce 3.0 or later.
		if ( method_exists( $order, 'get_id' ) ) {
			$xml->add_reference( $this->gateway->invoice_prefix . $order->get_id() );
			$xml->add_sender_data( $order, $hash );
			$xml->add_shipping_data( $order, $ship_to, $data['shipping_cost'] );
		} else {
			// @codingStandardsIgnoreStart
			$xml->add_reference( $this->gateway->invoice_prefix . $order->id );
			// @codingStandardsIgnoreEnd
			$xml->add_legacy_sender_data( $order, $hash );
			$xml->add_legacy_shipping_data( $order, $ship_to, $data['shipping_cost'] );
		}

		// Items related to the payment method.
		if ( 'creditCard' == $method ) {
			$credit_card_token = isset( $posted['pagseguro_credit_card_hash'] ) ? sanitize_text_field( $posted['pagseguro_credit_card_hash'] ) : '';
			$installment       = array(
				'quantity' => isset( $posted['pagseguro_card_installments'] ) ? absint( $posted['pagseguro_card_installments'] ) : '',
				'value'    => isset( $posted['pagseguro_installment_value'] ) ? $this->money_format( $posted['pagseguro_installment_value'] ) : '',
			);
			$holder_data       = array(
				'name'       => isset( $posted['pagseguro_card_holder_name'] ) ? sanitize_text_field( $posted['pagseguro_card_holder_name'] ) : '',
				'cpf'        => isset( $posted['pagseguro_card_holder_cpf'] ) ? sanitize_text_field( $posted['pagseguro_card_holder_cpf'] ) : '',
				'birth_date' => isset( $posted['pagseguro_card_holder_birth_date'] ) ? sanitize_text_field( $posted['pagseguro_card_holder_birth_date'] ) : '',
				'phone'      => isset( $posted['pagseguro_card_holder_phone'] ) ? sanitize_text_field( $posted['pagseguro_card_holder_phone'] ) : '',
			);

			// WooCommerce 3.0 or later.
			if ( method_exists( $order, 'get_id' ) ) {
				$xml->add_credit_card_data( $order, $credit_card_token, $installment, $holder_data );
			} else {
				$xml->add_legacy_credit_card_data( $order, $credit_card_token, $installment, $holder_data );
			}
		} elseif ( 'eft' == $method ) {
			$bank_name = isset( $posted['pagseguro_bank_transfer'] ) ? sanitize_text_field( $posted['pagseguro_bank_transfer'] ) : '';
			$xml->add_bank_data( $bank_name );
		}

		// Filter the XML.
		$xml = apply_filters( 'woocommerce_pagseguro_payment_xml', $xml, $order );

		if (!$render){
			return $xml;
		}
		return $xml->render();
	}

	/**
	 * Get the direct payment POST params.
	 *
	 * @author Ricardo Martins <pagseguro-transparente@ricardomartins.net.br>
	 * @param WC_Order $order Order data.
	 * @param array    $posted Posted data.
	 *
	 * @return array
	 */
	protected function get_payment_post( $order, $posted ) {
		$xml = $this->get_payment_xml($order, $posted, false);

		$post = array(
			'currency' => (string)$xml->currency,
			'paymentMethod' => (string)$xml->method,
			'paymentMode' => (string)$xml->mode,
			'reference' => (string)$xml->reference,
			'notificationURL' => (string)$xml->notificationURL,
			'senderEmail' => (string)$xml->sender->email,
			'senderHash' => (string)$xml->sender->hash,
			'senderAreaCode' => (string)$xml->sender->phone->areaCode,
			'public_key' => (string)$this->gateway->settings['public_key'],
			'senderPhone' => (string)$xml->sender->phone->number,
			'senderName' => (string)$xml->sender->name,
			'shippingType' => (string)$xml->shipping->type,
			'shippingCost' => (string)$xml->shipping->cost,
			'shippingAddressCountry' => (string)$xml->shipping->address->country,
			'shippingAddressStreet' => (string)$xml->shipping->address->street,
			'shippingAddressNumber' => (string)$xml->shipping->address->number,
			'shippingAddressComplement' => (string)$xml->shipping->address->complement,
			'shippingAddressDistrict' => (string)$xml->shipping->address->district,
			'shippingAddressCity' => (string)$xml->shipping->address->city,
			'shippingAddressPostalCode' => (string)$xml->shipping->address->postalCode,
			'shippingAddressState' => (string)$xml->shipping->address->state,
		);

		$senderIp = $this->get_sender_ip();
		if ($senderIp) {
			$post['senderIp'] = $senderIp;
		}

		foreach($xml->items->item as $item) {
			$post['itemId' . (string)$item->id] = (string)$item->id;
			$post['itemDescription' . (string)$item->id] = (string)$item->description;
			$post['itemAmount' . (string)$item->id] = (string)$item->amount;
			$post['itemQuantity' . (string)$item->id] = (string)$item->quantity;
		}

		switch ((string)$xml->sender->documents->document->type){
			case 'CNPJ':
				$post['senderCNPJ'] = (string)$xml->sender->documents->document->value;
				break;
			case 'CPF':
			default:
				$post['senderCPF'] = (string)$xml->sender->documents->document->value;
		}

		if (isset($xml->bank)) {
			$post['bankName'] = (string)$xml->bank->name;
		}

		if (isset($xml->creditCard)) {
			$post['installmentQuantity'] = (string)$xml->creditCard->installment->quantity;
			$post['installmentValue'] = (string)$xml->creditCard->installment->value;
			$post['creditCardToken'] = (string)$xml->creditCard->token;
			$post['creditCardHolderName'] = (string)$xml->creditCard->holder->name;
			$post['creditCardHolderCPF'] = (string)$xml->creditCard->holder->documents->document->value;
			$post['creditCardHolderBirthDate'] = (string)$xml->creditCard->holder->birthDate;
			$post['creditCardHolderAreaCode'] = (string)$xml->creditCard->holder->phone->areaCode;
			$post['creditCardHolderPhone'] = (string)$xml->creditCard->holder->phone->number;

			// parcels without interest setting
			if ( $posted['no_interest_installments_min_value'] ) {
				$noInterestInstallmentsMaxParcels = floor( $order->get_total() / $posted['no_interest_installments_min_value'] );

                //prevents internal server error from PagSeguro when receiving a value > 18
                $noInterestInstallmentsMaxParcels = min($noInterestInstallmentsMaxParcels, 18);
                
                //prevents 0 or 1
				if ( $noInterestInstallmentsMaxParcels > 1 ) {
					$post['noInterestInstallmentQuantity'] = $noInterestInstallmentsMaxParcels;
				}
			}

			//billing address
			$post['billingAddressStreet'] = (string)$xml->creditCard->billingAddress->street;
			$post['billingAddressNumber'] = (string)$xml->creditCard->billingAddress->number;
			$post['billingAddressComplement'] = (string)$xml->creditCard->billingAddress->complement;
			$post['billingAddressDistrict'] = (string)$xml->creditCard->billingAddress->district;
			$post['billingAddressCity'] = (string)$xml->creditCard->billingAddress->city;
			$post['billingAddressState'] = (string)$xml->creditCard->billingAddress->state;
			$post['billingAddressCountry'] = (string)$xml->creditCard->billingAddress->country;
			$post['billingAddressPostalCode'] = (string)$xml->creditCard->billingAddress->postalCode;

		}
		$post = $this->convert_encoding($post);
		return $post;
	}

	/**
	 * Do checkout request.
	 *
	 * @param  WC_Order $order  Order data.
	 * @param  array    $posted Posted data.
	 *
	 * @return array
	 */
	public function do_checkout_request( $order, $posted ) {
		// Sets the xml.
		$xml = $this->get_checkout_post( $order, $posted );

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting token for order ' . $order->get_order_number() . ' with the following data: ' . var_export($xml, true) );
		}

		$url      = add_query_arg( array( 'email' => $this->gateway->get_email(), 'public_key' => $this->gateway->get_public_key() ), $this->get_checkout_url() );
		$response = $this->do_request( $url, 'POST', $xml, $this->get_custom_headers() );

		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error in generate payment token: ' . $response->get_error_message() );
			}
		} else if ( 401 === $response['response']['code'] ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Invalid public key and/or email settings!' );
			}

			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( __( 'Invalid e-mail or public key. Please check your PagSeguro configuration.', 'woo-pagseguro-rm' ) ),
			);
		} else {
			try {
				libxml_disable_entity_loader( true );
				$body = $this->safe_load_xml( $response['body'], LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				$body = '';

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Error while parsing the PagSeguro response: ' . print_r( $e->getMessage(), true ) );
				}
			}

			if ( isset( $body->code ) ) {
				$token = (string) $body->code;

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'PagSeguro Payment Token created with success! The Token is: ' . $token );
				}

				return array(
					'url'   => $this->get_payment_url( $token ),
					'token' => $token,
					'error' => '',
				);
			}

			if ( isset( $body->error ) ) {
				$errors = array();

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Failed to generate the PagSeguro Payment Token: ' . print_r( $response, true ) );
				}

				foreach ( $body->error as $error_key => $error ) {
					if ( $message = $this->get_error_message( $error->code ) ) {
                        //if generic message, add the original one
                        $message .= (strpos($message, 'Um erro ocorreu') !== false) ? ' (' . $error->message . ')' : '';
						$errors[] = '<strong>' . __( 'PagSeguro', 'woo-pagseguro-rm' ) . '</strong>: ' . $message;
					}
				}

				return array(
					'url'   => '',
					'token' => '',
					'error' => $errors,
				);
			}
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Error generating the PagSeguro payment token: ' . print_r( $response, true ) );
		}

		// Return error message.
		return array(
			'url'   => '',
			'token' => '',
			'error' => array( '<strong>' . __( 'PagSeguro', 'woo-pagseguro-rm' ) . '</strong>: ' . __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'woo-pagseguro-rm' ) ),
		);
	}

	/**
	 * Do payment request.
	 *
	 * @param  WC_Order $order  Order data.
	 * @param  array    $posted Posted data.
	 *
	 * @return array
	 */
	public function do_payment_request( $order, $posted ) {
		$payment_method = isset($posted['pagseguro_payment_method']) ? sanitize_text_field(
			$posted['pagseguro_payment_method']
		) : '';

		/**
		 * Validate if has selected a payment method.
		 */
		if ( ! in_array( $payment_method, $this->get_available_payment_methods() ) ) {
			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( '<strong>' . __( 'PagSeguro', 'woo-pagseguro-rm' ) . '</strong>: ' .  __( 'Please, select a payment method.', 'woo-pagseguro-rm' ) ),
			);
		}

		// Sets the xml.
		$posted['public_key'] = $this->gateway->settings['public_key'];
		$xml = $this->get_payment_post( $order, $posted );

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting direct payment for order ' . $order->get_order_number() . ' with the following data: ' . var_export($xml, true) );
		}

		$url      = add_query_arg( array( 'email' => $this->gateway->get_email(), 'public_key' => $this->gateway->get_public_key() ), $this->get_transactions_url() );
		$response = $this->do_request( $url, 'POST', $xml, $this->get_custom_headers() );

		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error in requesting the direct payment: ' . $response->get_error_message() );
			}
		} else if ( 401 === $response['response']['code'] ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'The user does not have permissions to use the PagSeguro Transparent Checkout!' );
			}

			return array(
				'url'   => '',
				'data'  => '',
				'error' => array( __( 'You are not allowed to use the PagSeguro Transparent Checkout. Please check the installation instructions.', 'woo-pagseguro-rm' ) ),
			);
		} else {
			try {
				$data = $this->safe_load_xml( $response['body'], LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				$data = '';

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Error while parsing the PagSeguro response: ' . print_r( $e->getMessage(), true ) );
				}
			}

			if ( isset( $data->code ) ) {
				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'PagSeguro direct payment created successfully!' );
				}

				return array(
					'url'   => $this->gateway->get_return_url( $order ),
					'data'  => $data,
					'error' => '',
				);
			}

			if ( isset( $data->error ) ) {
				$errors = array();

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'An error occurred while generating the PagSeguro direct payment: ' . print_r( $response, true ) );
				}

				foreach ( $data->error as $error_key => $error ) {
					if ( $message = $this->get_error_message( $error->code ) ) {
						$errors[] = '<strong>' . __( 'PagSeguro', 'woo-pagseguro-rm' ) . '</strong>: ' . $message;
					}
				}

				return array(
					'url'   => '',
					'data'  => '',
					'error' => $errors,
				);
			}
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'An error occurred while generating the PagSeguro direct payment: ' . print_r( $response, true ) );
		}

		// Return error message.
		return array(
			'url'   => '',
			'data'  => '',
			'error' => array( '<strong>' . __( 'PagSeguro', 'woo-pagseguro-rm' ) . '</strong>: ' . __( 'An error has occurred while processing your payment, please try again. Or contact us for assistance.', 'woo-pagseguro-rm' ) ),
		);
	}

	/**
	 * Process the IPN.
	 *
	 * @param  array $data IPN data.
	 *
	 * @return bool|SimpleXMLElement
	 */
	public function process_ipn_request( $data ) {

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Checking IPN request...' );
		}

		// Valid the post data.
		if ( ! isset( $data['notificationCode'] ) && ! isset( $data['notificationType'] ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Invalid IPN request: ' . print_r( $data, true ) );
			}

			return false;
		}

		// Checks the notificationType.
		if ( 'transaction' != $data['notificationType'] ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'Invalid IPN request, invalid "notificationType": ' . print_r( $data, true ) );
			}

			return false;
		}

		// Gets the PagSeguro response.
		$url      = add_query_arg( array( 'public_key' => $this->gateway->get_public_key() ), $this->get_notification_url( $data['notificationCode'] ) );
		$response = $this->do_request( $url, 'GET' );

		// Check to see if the request was valid.
		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error in IPN: ' . $response->get_error_message() );
			}
		} else {
			try {
				$body = $this->safe_load_xml( $response['body'], LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				$body = '';

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Error while parsing the PagSeguro IPN response: ' . print_r( $e->getMessage(), true ) );
				}
			}

			if ( isset( $body->code ) ) {
				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'PagSeguro IPN is valid! The return is: ' . print_r( $body, true ) );
				}

				return $body;
			}
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'IPN Response: ' . print_r( $response, true ) );
		}

		return false;
	}

	/**
	 * Get session ID.
	 *
	 * @return string
	 */
	public function get_session_id() {

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Requesting session ID...' );
		}

		$url      = add_query_arg( array( 'public_key' => $this->gateway->get_public_key() ), $this->get_sessions_url() );
		$response = $this->do_request( $url, 'POST' );

		// Check to see if the request was valid.
		if ( is_wp_error( $response ) ) {
			if ( 'yes' == $this->gateway->debug ) {
				$this->gateway->log->add( $this->gateway->id, 'WP_Error requesting session ID: ' . $response->get_error_message() );
			}
		} else {
			try {
				$session = $this->safe_load_xml( $response['body'], LIBXML_NOCDATA );
			} catch ( Exception $e ) {
				$session = '';

				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'Error while parsing the PagSeguro session response: ' . print_r( $e->getMessage(), true ) );
				}
			}

			if ( isset( $session->id ) ) {
				if ( 'yes' == $this->gateway->debug ) {
					$this->gateway->log->add( $this->gateway->id, 'PagSeguro session is valid! The return is: ' . print_r( $session, true ) );
				}

				return (string) $session->id;
			}
		}

		if ( 'yes' == $this->gateway->debug ) {
			$this->gateway->log->add( $this->gateway->id, 'Session Response: ' . print_r( $response, true ) );
		}

		return false;
	}

	/**
	 * Adds custom headers with platform information
	 * @return string
	 */
	public function get_custom_headers()
	{
		$headers  =  'Content-Type: application/x-www-form-urlencoded; charset=ISO-8859-1';
		$headers .= PHP_EOL . 'Platform: WooCommerce';
		$headers .= PHP_EOL . 'Platform-Version: ' . get_bloginfo('version');
		$headers .= PHP_EOL . 'Module-Version:' . WC_PAGSEGURO_VERSION;

		if ($wpVersion = $this->wpbo_get_woo_version_number()) {
			$headers .= PHP_EOL . 'Extra-Version:' . $wpVersion;
		}
		return $headers;

	}

	/**
	 * Get WooCommerce version
	 * @return bool | string
	 */
	public function wpbo_get_woo_version_number() {
		// If get_plugins() isn't available, require it
		if ( ! function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		// Create the plugins folder and file variables
		$plugin_folder = get_plugins( '/' . 'woocommerce' );
		$plugin_file = 'woocommerce.php';

		// If the plugin version number is set, return it
		if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
			return $plugin_folder[$plugin_file]['Version'];

		} else {
			// Otherwise return false
			return false;
		}
	}

	/**
	 * Convert data sent to PagSeguro to ISO-8859-1
	 * @param $data
	 *
	 * @return mixed
	 */
	public function convert_encoding($data)
	{
		foreach ($data as $k => $v) {
			$data[$k] = utf8_decode($v);
		}
		return $data;
	}

	/**
	 * Return Customer's IP v4 or '' if unsuccessful
	 * @return string
	 */
	public function get_sender_ip()
	{
		$senderIp = '';

		// In order of preference, with the best ones for this purpose first.
		$address_headers = array(
			'HTTP_CF_CONNECTING_IP', //Cloudflare
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR',
		);

		foreach ( $address_headers as $header ) {
			if ( array_key_exists( $header, $_SERVER ) ) {
				/*
				 * HTTP_X_FORWARDED_FOR can contain a chain of comma-separated
				 * addresses. The first one is the original client. It can't be
				 * trusted for authenticity, but we don't need to for this purpose.
				 */
				$address_chain = explode( ',', $_SERVER[ $header ] );
				$senderIp     = trim( $address_chain[0] );

				break;
			}
		}

		if ( ! $senderIp  || false === filter_var($senderIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return '';
		}

		return $senderIp;
	}
}
