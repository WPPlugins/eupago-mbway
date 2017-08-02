=== euPago - MBWAY ===

Contributors: eu_pago
Donate link: https://wordpress.org/plugins/eupago-mbway
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 4.4
Stable tag: 4.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Payment method that allows your costumers to pay by MBWAY (Portuguese payment method).

== Description ==

English below

(PORTUGUÊS) Método de pagamento que permite efetuar pagamentos por MBWAY. Permite gerar referências MBWAY na sua loja online, ao efetuar o pagamento da encomenda o comprador é notificado para fazer o pagamento no seu smartphone o pagamento é processado de forma automática e em tempo real, após pagamento, altera os estados das encomendas para pagos no preciso momento em que o cliente paga a referência, bem como actualiza automaticamente o stock dos produtos. Desta forma, com este plugin, fornece ao seus clientes o pagamento por MBWAY de uma forma celere e segura. O plugin é produtivo somente após fazer o acordo com euPago. Para mais informações consulte https://www.eupago.pt/.

Algumas notas importantes:

*   Este plugin requer o plugin do woocommerce previamente instalado
*   Este plugin é um método de pagamento feito para woocommerce
*   Para usar este métudo de pagamento terá de criar previamente uma conta em https://www.eupago.pt
*   Para receber as atualizações automáticas do estado do pagamento terá de configurar o campo de url_callback no backoffice da sua conta eupago
*   Tipo de callback ->  url_do_site/wp/wc-api/WC_eupago_mbway


(ENGLISH) Payment method that allows your costumers to pay by MBWAY (Portuguese payment method). This plugin, automatically and in real time, changes the status of orders to paid in the right moment of the payment made by the customer. At the same time it's changes the stock of products. This plugin is productive only after making the agreement with euPago in https://www.eupago.pt/. For more information about us and this payment method please check our website https://www.eupago.pt/.

A few notes about the sections above:

*   this plugin require woocommerce plugin
*   this plugin is one payment method for woocommerce stores
*   for work with this gateway you will need create one account in https://www.eupago.pt
*   for receive callbacks you need to configure url_calback field in your euPago account.  
*   callback url estructure ->  your_website/wp/wc-api/WC_eupago_mbway

== Installation ==

English below

(PORTUGUÊS) 
*   1. Ir a "Plugins" - > "Adicionar Novo" e procurar por eupago-mbway. 
*   2. Ative o plugin. 
*   3. Ir a "Woocommerce"->"Configurações" escolhe a aba "Finalizar compras" clicar na hiperligação "Eupago-Mbway" e coloque a chave fornecida pela euPago.pt.

(ENGLISH) 
*   1. Go to "Plugins" - > "Add New" and search by euPago.pt. 
*   2. Activate the plugin through the 'Plugins' menu in WordPress. 
*   3. Go to "Woocommerce" -> "Settings" tab and choose the "Checkout". Click the link "Eupago-Mbway" and enter the key provided by euPago.pt.


== Frequently Asked Questions ==

English below

(PORTUGUÊS)

= Como é que obtenho a chave de activação? =
Tem que ir a https://www.eupago.pt e registar-se, e após aceder ao backoffice, obter a chave de ativação.

= Qual o URL de callback? =
callback url ->  url_do_site/wp/wc-api/WC_eupago_mbway

(ENGLISH)

= How do I get the key? =
You must go to https://www.eupago.pt, register, and after access to the backoffice, get the activation key.

= What is the callback url? =
callback url estructure ->  your_website/wp/wc-api/WC_eupago_mbway

== Screenshots ==

1. `/assets/banner-772x250.jpg`


== Changelog ==

= 1.0 =
* Plugin released
* Auto-update order status (callback)