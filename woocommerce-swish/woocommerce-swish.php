<?php
/* Swish Payment Gateway Class */
class redlight_swish extends WC_Payment_Gateway {
 
    // Setup our Gateway's id, description and other values
    function __construct() {
 
        // The global ID for this Payment method
        $this->id = "redlight_swish";
 
        // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
        $this->method_title = __( "Swish", 'redlight-swish' );
 
        // The description for this Payment Gateway, shown on the actual Payment options page on the backend
        $this->method_description = __( "Swish Plug-in for WooCommerce", 'redlight-swish' );
 
        // The title to be used for the vertical tabs that can be ordered top to bottom
        $this->title = __( "Swish", 'redlight-swish' );
 
        // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
        $this->icon = plugins_url( 'assets/images/swish_logo.jpg', __FILE__ );
        $this->swishimglogo = plugins_url( 'assets/images/Swish-logo-image-vert.png', __FILE__ );
        $this->swishtextlogo = plugins_url( 'assets/images/Swish-logo-text-vert.png', __FILE__ );
		
		//Prepare Css
		wp_register_style( 'swish', plugins_url( 'assets/css/swish.css', __FILE__ ) );
 
        // Bool. Can be set to true if you want payment fields to show on the checkout 
        // if doing a direct integration, which we are doing in this case
        $this->has_fields = true;
 
        // This basically defines your settings which are then loaded with init_settings()
        $this->init_form_fields();
 
        // After init_settings() is called, you can get the settings and load them into variables, e.g:
        // $this->title = $this->get_option( 'title' );
        $this->init_settings();
         
		 
		 // Swish account fields shown on the thanks page and in emails
		$this->account_details = get_option( 'woocommerce_bacs_accounts',
			array(
				array(
					'swish_number' => $this->get_option( 'swish_number' ),
				)
			)
		);
        // Turn these settings into variables we can use
        foreach ( $this->settings as $setting_key => $value ) {
            $this->$setting_key = $value;
        }
        
		$this->instructions = $this->get_option( 'instructions', $this->description );
        // Actions
        //add_action( 'admin_notices', array( $this,  'do_ssl_check' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
    	add_action( 'woocommerce_thankyou_redlight_swish', array( $this, 'thankyou_page' ) ); 

    	// Customer Emails
    	add_action( 'woocommerce_email_before_order_table', array( $this, 'email_instructions' ), 10, 3 );
         
     
    } // End __construct()
 
    // Build the administration fields for this specific Gateway
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'     => __( 'Enable / Disable', 'redlight-swish' ),
                'label'     => __( 'Enable this payment gateway', 'redlight-swish' ),
                'type'      => 'checkbox',
                'default'   => 'no',
            ),
            'title' => array(
                'title'     => __( 'Title', 'redlight-swish' ),
                'type'      => 'text',
                'desc_tip'  => __( 'Ange det namn du vill ska visas för användaren.', 'redlight-swish' ),
                'default'   => __( 'Swish', 'redlight-swish' ),
            ),
            'description' => array(
                'title'     => __( 'Description', 'redlight-swish' ),
                'type'      => 'textarea',
                'desc_tip'  => __( 'Betala ditt köp direkt via din smartphone med Swish betalapp som finns för både iPhone och Android.
									Observera att du måste ha din mobiltelefon och inloggningskod redo för att kunna genomföra köpet.<br>Betalningen registreras direkt och pengarna reserveras från ditt konto med betalningsreferens “Partykungen.se”.Ingen extra avgift tas ut.',
				'redlight-swish' ),
                'default'   => __( 'Swish fungerar mellan  Danske Bank,  Handelsbanken,  ICA Banken,  Länsförsäkringar,  Nordea,  SEB,  Skandia,  Sparbanken Syd,  Sparbanken Öresund samt Swedbank och Sparbankerna.', 'redlight-swish' ),
				'desc_tip'    => true,
            ),
			'message' => array(
				'title'       => __( 'Meddelande', 'redlight-swish' ),
				'type'        => 'textarea',
				'description' => __( 'Instruktioner som kommer att visas på "Tack för din order" och i e-postmeddelande.', 'redlight-swish' ),
				'default'     => 'Eftersom betalningar gjorda via Swish måste granskas manuellt och matchas mot din order så kan det ta upp till 24 timmar innan det är gjort. Du får ett mail när din order är behandlad.',
				'desc_tip'    => true,
			),
			'swish_number' => array(
				'title'     => __( 'Ditt swish nummer', 'redlight-swish' ),
                'type'      => 'text',
                'description' => __( 'Ange numret som du fick när du anslöt till swish.', 'redlight-swish' ),
			),
			'show_desc' => array(
                'title'     => __( 'Visa / Dölj beskrivning', 'redlight-swish' ),
                'label'     => __( 'Visa beskrvning', 'redlight-swish' ),
                'type'      => 'checkbox',
                'default'   => 'no',
            ),
			'swish_number_desc' => array(
				'title'       => __( 'Beskrivning av Swishkonto', 'redlight-swish' ),
				'type'        => 'textarea',
				'description' => __( 'Exempel: Aktiebolag AB', 'redlight-swish' ),
				'default'     => '',
				'desc_tip'    => true,
			),
			'swish_number_two' => array(
				'title'     => __( 'Ditt swish nummer', 'redlight-swish' ),
                'type'      => 'text',
                'description' => __( 'Om du har fler än ett swishnummer, ange det andra här. Om inte, lämna tomt', 'redlight-swish' ),
			),
			'show_desc_two' => array(
                'title'     => __( 'Visa / Dölj beskrivning', 'redlight-swish' ),
                'label'     => __( 'Visa beskrvning', 'redlight-swish' ),
                'type'      => 'checkbox',
                'default'   => 'no',
            ),
			'swish_number_desc_two' => array(
				'title'       => __( 'Beskrivning av Swishkonto', 'redlight-swish' ),
				'type'        => 'textarea',
				'description' => __( 'Exempel: Dotterbolag AB', 'redlight-swish' ),
				'default'     => '',
				'desc_tip'    => true,
			),
        );      
    }

	
	/**
     * Output for the order received page.
     */
    public function thankyou_page( $order_id ) {
		if ( $this->instructions ) {
        	//echo wpautop( wptexturize( wp_kses_post( $this->instructions ) ) );
        }
        $this->swish_details( $order_id );
    }

    /**
     * Add content to the WC emails.
     *
     * @access public
     * @param WC_Order $order
     * @param bool $sent_to_admin
     * @param bool $plain_text
     * @return void
     */
    public function email_instructions( $order, $sent_to_admin, $plain_text = false ) {
    	if ( ! $sent_to_admin && 'swish' === $order->payment_method && $order->has_status( 'on-hold' ) ) {
			if ( $this->instructions ) {
				echo wpautop( wptexturize( $this->instructions ) ) . PHP_EOL;
			}
			$this->swish_details( $order->id );
		}
    }


    /**
     * Get bank details and place into a list format
     */
    private function swish_details( $order_id = '' ) {
    	if ( empty( $this->swish_number ) ) {
    		return;
    	}

    	$swish_account = apply_filters( 'woocommerce_swish_account', $this->swish_number );

    	if ( ! empty( $swish_account ) ) {
	    		$swish_account = (object) $swish_account;
				$order = new WC_Order($order_id);
				//Load Swish CSS
				wp_enqueue_style('swish');
				?>
			<div class="logo centered">
					<img class="rotate centered" src="<?php echo $this->swishimglogo;?>" />
					<img src="<?php echo $this->swishtextlogo;?>" />
				</div>
				<div class="messages centered"><?php
						echo '<h2>' . __( 'Att betala med Swish', 'redlight-swish' ) . '</h2>' . PHP_EOL;
						echo '<p>Vänligen betala din order genom att swisha <strong>'.$order->order_total.' '.$order->order_currency.'</strong> till ';
						if($this->show_desc == 'yes'){echo $this->swish_number_desc . ', <strong>';}else{echo 'nummer <strong>';}
						echo $this->swish_number .'</strong>.';
						if(isset($this->swish_number_two) && $this->swish_number_two !== ''){
							echo '<br>Alternativt betalar du till ';
							if($this->show_desc_two == 'yes'){
								echo $this->swish_number_desc_two . ', <strong>';
							}
							else{echo 'nummer <strong>';}
						echo $this->swish_number_two .'</strong>.';
							}
							echo '<br>Ange <strong>'. $order_id . '</strong> som meddelande i din Swish-app.</p>'.
						wpautop( wptexturize( $this->message ) ).'</div>';
						
				
	    		echo '<ul class="order_details swish_details">' . PHP_EOL;

	    		// Swish account fields shown on the thanks page and in emails
				$account_fields = apply_filters( 'woocommerce_swish_account_fields', $this->swish_number, $order_id );



	    		echo '</ul>';
	    	
	    }
    }

    /**
     * Process the payment and return the result
     *
     * @param int $order_id
     * @return array
     */
    public function process_payment( $order_id ) {

		$order = wc_get_order( $order_id );

		// Mark as on-hold (we're awaiting the payment)
		$order->update_status( 'on-hold', __( 'Awaiting swish payment', 'woocommerce' ) );

		// Reduce stock levels
		$order->reduce_order_stock();

		// Remove cart
		WC()->cart->empty_cart();

		// Return thankyou redirect
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->get_return_url( $order )
		);
    }
	
	// Check if we are forcing SSL on checkout pages
    // Custom function not required by the Gateway
    public function do_ssl_check() {
        if( $this->enabled == "yes" ) {
            if( get_option( 'woocommerce_force_ssl_checkout' ) == "no" ) {
                echo "<div class=\"error\"><p>". sprintf( __( "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>" ), $this->method_title, admin_url( 'admin.php?page=wc-settings&tab=checkout' ) ) ."</p></div>";   
            }
        }       
    }
 
} // End of redlight_swish