<?php 

class WC_Offline_Invoices_Request extends WC_Payment_Gateway{

	public function __construct(){
		$this->id = 'offline_invoices_request';
		$this->method_title = __('Offline Invoices Request','woocommerce-offline-invoices-request');
		$this->title = __('Offline Invoices Request','woocommerce-offline-invoices-request');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
	}
	public function init_form_fields(){
				$this->form_fields = array(
					'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'woocommerce-offline-invoices-request' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable Offline Invoices Request', 'woocommerce-offline-invoices-request' ),
					'default' 		=> 'yes'
					),
					'title' => array(
						'title' 		=> __( 'Method Title', 'woocommerce-offline-invoices-request' ),
						'type' 			=> 'text',
						'description' 	=> __( 'This controls the title', 'woocommerce-offline-invoices-request' ),
						'default'		=> __( 'Offline Invoices Request', 'woocommerce-offline-invoices-request' ),
						'desc_tip'		=> true,
					),
					'max_invoices' => array(
						'title' 		=> __( 'Max Number of Invoices', 'woocommerce-offline-invoices-request' ),
						'type' 			=> 'text',
						'description' 	=> __( 'This set the maximum invoices number that client can request', 'woocommerce-offline-invoices-request' ),
						'default'		=> __( '5', 'woocommerce-offline-invoices-request' ),
						'desc_tip'		=> true,
					),
					'invoice_fee' => array(
						'title' 		=> __( 'Invoice Fee', 'woocommerce-offline-invoices-request' ),
						'type' 			=> 'text',
						'description' 	=> __( 'Fee added on each requested invoice', 'woocommerce-offline-invoices-request' ),
						'default'		=> __( '0', 'woocommerce-offline-invoices-request' ),
						'desc_tip'		=> true,
					),
					'info_message' => array(
						'title' 		=> __( 'Info Message', 'woocommerce-offline-invoices-request' ),
						'type' 			=> 'textarea',
						'description' 	=> __( 'Message that client will see when select this payment method', 'woocommerce-offline-invoices-request' ),
						'default'		=> __( 'Invoices will be generated and sent to your e-mail within 2 business days.', 'woocommerce-offline-invoices-request' ),
						'desc_tip'		=> true,
					),
			 );
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'Offline Invoices Request Settings', 'woocommerce-offline-invoices-request' ); ?></h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="post-body-content">
						<table class="form-table">
							<?php $this->generate_settings_html();?>
						</table><!--/.form-table-->
					</div>
                    </div>
			</div>
			<div class="clear"></div>
				<?php
	}

	public function process_payment( $order_id ) {

		global $woocommerce;
		$order = new WC_Order( $order_id );

		// Mark as on-hold (we're awaiting the invoices)
		$order->update_status('on-hold', __( 'Awaiting payment', 'woocommerce-offline-invoices-request' ));

		// Reduce stock levels
		$order->reduce_order_stock();

		// Add admin note
		if(isset($_POST[ $this->id.'-admin-note']) && trim($_POST[ $this->id.'-admin-note'])!=''){
			$order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
		}

		// Remove cart
		$woocommerce->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' => 'success',
			'redirect' => $this->get_return_url( $order )
		);	

	}

	public function payment_fields(){

		global $woocommerce;

		// Util vars
		$total_cart_amount = floatval( $woocommerce->cart->total );
		$max_invoices = (int) $this->settings['max_invoices'];
		$invoice_fee = $this->settings['invoice_fee'];
		$currency_symbol = html_entity_decode(get_woocommerce_currency_symbol());
		?>
		<fieldset>
			<p class="form-row form-row-wide">
				<p><?= $this->settings['info_message'] ?></p>
				<select id="offline-invoices-request-number" name="<?= $this->id ?>-admin-note">
					<?php for ($i = 1; $i <= $max_invoices; $i++): ?>

						<?php 
							$invoice_amount = ($total_cart_amount / $i) + $invoice_fee;
							$invoice_amount = number_format($invoice_amount, 2, wc_get_price_decimal_separator(), wc_get_price_thousand_separator()); 
							$option_text = $i . ' x ' . $currency_symbol . ' ' . $invoice_amount;
						?>
						
						<option value="<?= $option_text ?>"><?= $option_text ?></option>

					<?php endfor; ?>
				</select>
			</p>						
		</fieldset>
		<?php
	}
}