<?php
//
/*
  Plugin Name: WooCommerce Eupago MBWAY
  Plugin URI: http://www.eupago.pt
  Description: Plugin de integra&ccedil;&atilde;o com Woocommerce 2.0
  Version: 1.0
  Author: euPago
  Author URI: http://www.eupago.pt
 */
register_activation_hook(__FILE__, 'epmbw_install');
add_action('plugins_loaded', 'woocommerce_eupago_mbway_init', 0);

function woocommerce_eupago_mbway_init() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    class WC_Eupago_Mbway extends WC_Payment_Gateway {

        public function __construct() {

            $this->id = 'eupago_mbway';
            $this->method_title = __('Eupago-Mbway', 'woocommerce');
            $this->icon = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/imagens/eupagombway.png';
            $this->encomendado = false;
            $this->init_form_fields();

            $this->init_settings();

            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');

            $this->chave_api_mbw = $this->get_option('chave_api_mbw');

            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_eupago_mbway', array(&$this, 'thankyou_page'));


            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 2);
            add_action('woocommerce_api_wc_eupago_mbway', array(&$this, 'callback'));

        }

        function callback() {
            //url Tipo: http://url.com/wp/wc-api/WC_eupago_mbway/?chave_api=xxxx-xxx-xxx&entidade=xxxxx&referencia=xxxxxxxxx&valor=xx&identificador=xx

            @ob_clean();
            global $wpdb;

            $chave_api_mbw = "";
            $chave_api_mb = "";
            $chave_api_ps = "";
            $nota = "";
            $nome_tabela_mbw = $wpdb->prefix . "eupago_mbway";

            if (class_exists(strtolower(esc_attr("Wc_eupago_multibanco")))) {
                $clas_mb = new Wc_eupago_multibanco();
                $chave_api_mb = $clas_mb->get_option('chave_api_mb');
            }

            if (class_exists(strtolower(esc_attr("Wc_eupago_mbway")))) {
                $clas_mbw = new Wc_eupago_mbway();
                $chave_api_mbw = $clas_mbw->get_option('chave_api_mbw');
            }
            if (class_exists(strtolower(esc_attr("Wc_eupago_payshop")))) {
                $clas_ps = new Wc_eupago_payshop();
                $chave_api_ps = $clas_ps->get_option('chave_api_ps');
            }


            $chave_api = $_GET['chave_api'];
            $valor = $_GET['valor'];
            $entidade = $_GET['entidade'];
            $referencia = $_GET['referencia'];
            $order_id = $_GET['identificador'];


            if (($chave_api_mb == $chave_api || $chave_api_ps == $chave_api || $chave_api_mbw == $chave_api) && $chave_api != "") {
                $order = new WC_Order($order_id);
                $results = $wpdb->get_results("select * from $nome_tabela_mbw where order_id='$order_id' and referencia='$referencia' and valor_total='$valor' and entidade='$entidade'", OBJECT);
                if ($results['0']->order_id != "") {
                    $sql = "update $nome_tabela_mbw set estado='pago' where order_id=$order_id and estado='pendente' and referencia='$referencia' and valor_total='$valor' and entidade='$entidade'";
                    mysql_query($sql);
                    $nota = "Pagamento por <b>Mbway</b>.";

                } else {
                    wp_die('Erro: Valores inv&aacute;lidos ou encomenda j&aacute; paga!', 'WC_Eupago_Mbway', array('response' => 200)); //Sends 200
                }

                $nota = $nota . " Ref.: " . $_GET['referencia'] . "<br>";
                $order->update_status('completed', $nota);
                wp_die('Sucesso!', 'WC_Eupago_Mbway', array('response' => 200)); //Sends 200

            } else {
                wp_die('Error: chave inv&aacute;lida', 'WC_Eupago_Mbway', array('response' => 200)); //Sends 200
            }
        }

        function init_form_fields() {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woocommerce'),
                    'type' => 'checkbox',
                    'label' => __('Ativar Pagamento por MB Way', 'woocommerce'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Controla o t&iacute;tulo que utilizador vai visualizar durante o checkout.', 'woocommerce'),
                    'default' => __('Pagamento por MB Way', 'woocommerce')
                ),
                'description' => array(
                    'title' => __('Mensagem para o Cliente', 'woocommerce'),
                    'type' => 'textarea',
                    'description' => __('Deixe uma mensagem ao seu cliente para ele saber que este meio de pagamento &eacute; mais comodo e seguro para ele.', 'woocommerce'),
                    'default' => __('Maior facilidade e simplicidade de pagamento podendo o mesmo ser efetuado em qualquer Smartphone.', 'woocommerce')
                ),
                'chave_api_mbw' => array(
                    'title' => __('Chave API', 'woocommerce'),
                    'type' => 'text',
                    'description' => __('Chave fornecida pelo euPago', 'woocommerce'),
                    'default' => __('', 'woocommerce')
                ));
        }



        public function admin_options() {
            ?>
            <img src="<?php echo 'https://seguro.eupago.pt/repositorio/imagens/eupagombway.png' ?>" />
            <h3><?php _e('Pagamento por MB Way', 'woothemes'); ?></h3>
            <p><?php _e('Permite a ativação do serviço MB Way na sua loja online.', 'woothemes'); ?></p>
            <table class="form-table">
                <?php
                // Generate the HTML For the settings form.
                $this->generate_settings_html();
                ?>
            </table>
            <?php
        }


        function thankyou_page($order_id) {

            global $woocommerce;

            $eupago_mbw_referencia = $woocommerce->session->eupago_mbw_referencia;

            $order = new WC_Order($order_id);

            echo '
				<div align="center"><table cellpadding="0" cellspacing="0" border="0" style="width: 350px; color:#666; background-color: #f1f1f1; height: 50px; margin-top: 10px; border: 0px solid #d70510;">
					<tr style=" font-size:small; border:0;">
						<td style="border: 0; padding:5px; margin:0; background-color: #d70510; color: #f1f1f1; text-align: center; font-size:small;" colspan="3" >Pagamento por MB Way</td>
					</tr>
					<tr style=" font-size:small; border:0;">
						<td rowspan="3" style=" font-size:small;padding: 0px 15px;vertical-align: middle; border:0; "><img src="https://seguro.eupago.pt/repositorio/imagens/MBWay.png" style="margin-bottom: 0px; margin-right: 5px;"/></td>

					</tr>
					<tr style=" font-size:small; border:0;">
						<td style="border:0; padding:10px 8px 0 8px;">N. Pedido:</td>
						<td style="border:0; padding:10px 8px 0 8px;">' . $eupago_mbw_referencia . '</td>
					</tr>
					<tr style=" font-size:small; border:0;">
						<td style="border:0; padding:8px ; ">Valor:</td>
						<td style="border:0; padding:8px ;">' . $order->order_total . ' &euro;</td>
					</tr>
					<tr>
						<td style="text-align:center;padding-top:5px; font-size: xx-small;border:0; border-top: 0px solid #ddd; background-color: #fff; color: #666" colspan="3">Foi enviado um pedido de pagamento para o seu smartphone.</td>
					</tr>
				</table></div>';

        }


        function email_instructions($order, $sent_to_admin) {

            global $woocommerce;

            if ($order->payment_method == 'eupago_mbway'){
                ?>

                <div align="center">
                    <table cellpadding="3" cellspacing="0" style="width: 350px; height: 50px; margin-top: 10px;border: 1px solid #fff">
                        <tr>
                            <td style=" border-bottom: 1px solid #333; background-color: #d70510; color: #fff; text-align: center;  font-size:small;" colspan="3">Pagamento por MB Way</td>
                        </tr>
                        <tr style=" font-size:small;">
                            <td rowspan="3" style="padding: 0px 0px 0px 10px;vertical-align: middle;"><img src="https://seguro.eupago.pt/repositorio/imagens/MBWay.png" style="margin-bottom: 0px; margin-right: 0px;"/></td>
                            <td></td>
                            <td style="font-weight:bold;"></td>
                        </tr>
                        <tr style=" font-size:small;">
                            <td>N. Pedido:</td>
                            <td style="font-weight:bold;"><?php echo $woocommerce->session->eupago_mbw_referencia; ?></td>
                        </tr>
                        <tr style=" font-size:small;">
                            <td>Valor:</td>
                            <td style="font-weight:bold;"><?php echo $order->order_total; ?> &euro;</td>
                        </tr>
                        <tr>
                            <td style="font-size: xx-small;border-top: 1px solid #333; background-color: #45829F; color: #d70510" colspan="3">Foi enviado um pedido de pagamento para o seu smartphone.</td>
                        </tr>
                    </table><br/>
                </div>
            <?php }
        }

        public function payment_fields(){

            echo "
			<script>
			jQuery('#payment').find('input').on('click', function(){
				if(jQuery('#payment_method_eupago_mbway').is(':checked')){
					jQuery('#telefone').prop('required', true);
					jQuery('.payment_method_eupago_mbway').css({'display':'block !important'});
					jQuery('.payment_method_eupago_mbway').show();
				}else{
					jQuery('#telefone').prop('required', false);

				}
			});
			</script>";
            echo '<label>Insira o número de telemóvel associado ao MB Way</label>';
            echo '<input type="text" id="telefone" name="telefone" class="input_text" data-o_class="form-row form-row form-row-wide validate-required" />';
            global $woocommerce;
        }




        function process_payment($order_id) {

            global $woocommerce;

            $order = wc_get_order( $order_id );

            if ($order->payment_method !== 'eupago_mbway')
                return;

            if (!isset($woocommerce->session->eupago_mb_entidade) || $woocommerce->session->eupago_mb_order_id != $order->id) {
                if($this->encomendado == false){
                    $eupago = $this->GenerateMbRef($order->id, $order->order_total);
                    $woocommerce->session->eupago_mbw_referencia = $eupago->referencia;
                    $woocommerce->session->eupago_mbw_order_id = $order->id;
                    $woocommerce->session->eupago_mbw_resposta = $eupago->resposta;
                }else{
                    // pode dar erro ou entao podemos retirar o else
                }
            }

            if($eupago->estado == 0){
                $order->update_status('on-hold', __('Aguardar Pagamento por MB Way', 'woothemes'));
                $woocommerce->cart->empty_cart();
                $order->reduce_order_stock();
                unset($_SESSION['order_awaiting_payment']);
                //$order->payment_complete();
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            }else{
                if($eupago->resposta == "Alias inexistente"){
                    $eupago->resposta = "Número de telemóvel não associado ao serviço Mb Way";
                }
                wc_add_notice( __('Payment error:', 'woothemes') . " ".$eupago->resposta, 'error' );
                return false;
            }

        }

        //INICIO REF MULTIBANCO

        function GenerateMbRef($order_id, $order_value) {

            $chave_api_mbw = $this->chave_api_mbw;
            global $wpdb;
            $nome_tabela = $wpdb->prefix . "eupago_mbway";
            $results = $wpdb->get_results("select * from $nome_tabela where order_id='$order_id'", OBJECT);

            if ($results['order_id'] == "") {

                $demo = explode("-", $chave_api_mbw);
                if ($demo['0'] == 'demo') {
                    $url = 'https://replica.eupago.pt/replica.eupagov3.wsdl';
                }
                else{
                    $url = 'https://seguro.eupago.pt/eupagov3.wsdl';
                }

                $billing_phone = get_post_meta($order_id,'_billing_phone',true);
                $mbw_phone = $_POST['telefone'];
                $client = @new SoapClient($url, array('cache_wsdl' => WSDL_CACHE_NONE)); // chamada do serviço SOAP
                $arraydados = array("chave" => $chave_api_mbw, "valor" => $order_value, "id" => $order_id, "alias"=> $mbw_phone); //cada canal tem a sua chave

                $result = $client->pedidoMBW($arraydados);

                // verifica erros na execução do serviço e exibe o resultado
                if (is_soap_fault($result)) {
                    //trigger_error("SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faulstring})", E_ERROR);
                } else {
                    if ($result->estado == 0) { //estados possiveis: 0 sucesso. -10 Chave invalida. -9 Valores incorretcos
                        $this->encomendado = true;
                        $nome_tabela = $wpdb->prefix . "eupago_mbway";
                        $wpdb->insert($nome_tabela, array('id' => NULL, 'order_id' => $order_id, 'entidade' => 000, 'referencia' => $result->referencia, 'valor_total' => $result->valor, 'estado' => 'pendente'));
                        return $result; // retorna 3 valores: entidade, referência e valor
                    }
                }

                return $result;
            }
        }

    }

    function add_eupago_mbway_gateway($methods) {
        $methods[] = 'WC_Eupago_Mbway';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_eupago_mbway_gateway');

}

function epmbw_install() {

    global $wpdb;

    $nome_tabela = $wpdb->prefix . "eupago_mbway";

    $sql = "CREATE TABLE " . $nome_tabela . "  (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `order_id` int(11) NOT NULL,
		  `entidade` int(11) NOT NULL,
		  `referencia` int(11) NOT NULL,
		  `valor_total`  DECIMAL(11,5) NOT NULL,
		  `estado` enum('pendente','pago') NOT NULL,
		  PRIMARY KEY (`id`)
		);";


    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);
}
?>