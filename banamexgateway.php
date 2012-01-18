<?php 

/******************************************************************************
*	Module: Banamex Payment Gateway for Prestashop 
*	Author: Enrique Chavez
*	Author URI: http://tmeister.net
*	Version: 1.0
*	Class Name: banamexgateway
*	Description: Add a option to pay via Banamex, add the options tab in the administrator and generates the option on the front-end for the user.
******************************************************************************/

if ( !defined( '_PS_VERSION_' ) )
	exit;

class banamexgateway extends PaymentModule
{

	private	$_html = '';
	private $_postErrors = array();


	var $bnmx_access_code;
	var $bnmx_merchant_id;
	var $bnmx_terminal_id;
	var $bnmx_merchant_category_code;
	var $bnmx_url;
		

	function __construct(){
		$this->name = 'banamexgateway';
		$this->tab = 'payments_gateways';
		$this->version = "1.0";
		$this->author = 'Enrique Chavez';
		$this->currencies = true;
		$this->currencies_mode = 'checkbox';

		$config = Configuration::getMultiple(array('BNMX_ACCESSCODE', 'BNMX_MERCHANTID', 'BNMX_URL', 'BNMX_TERMINAL_ID','BNMX_MERCHANT_CATEGORY_CODE', 'BNMX_SECURE_SECRET'));

		// Establecer propiedades según los datos de configuración
		if (isset($config['BNMX_ACCESSCODE'])){
			$this->bnmx_access_code = $config['BNMX_ACCESSCODE'];
		}
		
		if (isset($config['BNMX_MERCHANTID'])){
			$this->bnmx_merchant_id = $config['BNMX_MERCHANTID'];
		}

		if (isset($config['BNMX_URL'])) {
			$this->bnmx_url = $config['BNMX_URL'];
		}

		if (isset($config['BNMX_TERMINAL_ID'])) {
			$this->bnmx_terminal_id = $config['BNMX_TERMINAL_ID'];
		}

		if (isset($config['BNMX_MERCHANT_CATEGORY_CODE'])) {
			$this->bnmx_merchant_category_code = $config['BNMX_MERCHANT_CATEGORY_CODE'];
		}

		if (isset($config['BNMX_SECURE_SECRET'])){
			$this->bnmx_secure_secret = $config['BNMX_SECURE_SECRET'];
		}


		parent::__construct(); 
		
		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Banamex TNS');
		$this->description = $this->l('Implementación de pago mediante el banco Banamex');

		if (	!isset($this->bnmx_access_code) OR 
				!isset($this->bnmx_merchant_id) OR 
				!isset($this->bnmx_url) OR
				!isset($this->bnmx_terminal_id) OR
				!isset($this->bnmx_merchant_category_code) OR
				!isset($this->bnmx_secure_secret)
				)
			$this->warning = $this->l('Te faltan datos a configurar el módulo de Banamex.');
	}

	public function install(){
		if (!parent::install() OR !$this->registerHook('payment'))
			return false;
	}

	public function hookPayment($params){
		global $smarty, $cookie;

		$config = Configuration::getMultiple(array('BNMX_ACCESSCODE', 'BNMX_MERCHANTID', 'BNMX_URL', 'BNMX_TERMINAL_ID','BNMX_MERCHANT_CATEGORY_CODE', 'BNMX_SECURE_SECRET'));

		require_once( dirname(__FILE__) . "/VPCPaymentConnection.php" );
		
		$conn          = new VPCPaymentConnection();
		
		$secureHash    = $config['BNMX_SECURE_SECRET'];
		$base_url_bnmx = Tools::getHttpHost(true, true)._MODULE_DIR_.$this->name.'/';
		
		$customer      = new Customer(intval($params['cart']->id_customer));
		
		$currency      = new Currency(intval($cookie->id_currency));
		$total         = $params['cart']->getOrderTotal(true, 3) * 100;
		#Order ID: El número de pedido es  los 8 ultimos digitos del ID del carrito + el tiempo MMSS.
		$order_id = str_pad($params['cart']->id, 8, "0", STR_PAD_LEFT) . date('is');


		$urlback = $base_url_bnmx.'validate_bnmx_pay.php?key='.$customer->secure_key.'&id_cart='.intval($params['cart']->id).'&id_module='.intval($this->id);

		$conn->setSecureSecret($secureHash);
		$conn->addDigitalOrderField("vpc_AccessCode", $config['BNMX_ACCESSCODE'] );
		$conn->addDigitalOrderField("vpc_Amount", $total);
		$conn->addDigitalOrderField("vpc_Command", "pay");
		$conn->addDigitalOrderField("vpc_Currency", "MXN");
		$conn->addDigitalOrderField("vpc_Locale", "es_MX");
		$conn->addDigitalOrderField("vpc_MerchTxnRef", $order_id);
		$conn->addDigitalOrderField("vpc_Merchant", $config['BNMX_MERCHANTID']);
		$conn->addDigitalOrderField("vpc_OrderInfo", $order_id);
		$conn->addDigitalOrderField("vpc_ReturnURL", $urlback);
		$conn->addDigitalOrderField("vpc_Version", "1");

		$secureHash = $conn->hashAllFields();
		$conn->addDigitalOrderField("Title", $title);
		$conn->addDigitalOrderField("vpc_SecureHash", $secureHash);
		$conn->addDigitalOrderField("vpc_SecureHashType", "SHA256");

		$vpcURL = $conn->getDigitalOrder( $config['BNMX_URL'] );


		$smarty->assign(array(
			'vpcURL' => $vpcURL,
			'random' => rand()
		));

		return $this->display(__FILE__, 'banamexgateway.tpl');
	}

	public function getContent(){
		$this->_html = '<h2>'.$this->displayName.'</h2>';

		if (!empty($_POST)){
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
				foreach ($this->_postErrors AS $err)
					$this->_html .= '<div class="alert error">'. $err .'</div>';
		}else{
			$this->_html .= '<br />';
		}

		$this->_displayForm();
		return $this->_html;
	}

	private function _displayForm(){
	    // Mostar formulario
		$this->_html .=
		'<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="../img/admin/contact.gif" />'.$this->l('Configuración Banamex TNS').'</legend>
				<table border="0" width="800" cellpadding="0" cellspacing="0" id="form">
					<tr>
						<td colspan="2">'
							.$this->l('Por favor completa la información requerida que te proporcionará Banamex').'.<br /><br />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('Identificación del comercio').
						'</td>
						<td>
							<input type="text" name="bnmx_merchant_id" value="'.Tools::getValue('bnmx_merchant_id', $this->bnmx_merchant_id).'" style="width: 140px;" />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('Clave de acceso').'
						</td>
						<td>
							<input type="text" name="bnmx_access_code" value="'.Tools::getValue('bnmx_access_code', $this->bnmx_access_code).'" style="width: 140px;" />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('Identificación de terminal').
						'</td>
						<td>
							<input type="text" name="bnmx_terminal_id" value="'.Tools::getValue('bnmx_terminal_id', $this->bnmx_terminal_id).'" style="width: 140px;" />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('Código de categoría del comercio').
						'</td>
						<td>
							<input type="text" name="bnmx_merchant_category_code" value="'.Tools::getValue('bnmx_merchant_category_code', $this->bnmx_merchant_category_code).'" style="width: 140px;" />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('VPC URL').'
						</td>
						<td>
							<input type="text" name="bnmx_url" value="'.Tools::getValue('bnmx_url', $this->bnmx_url).'" style="width: 430px;" />
						</td>
					</tr>
					<tr>
						<td width="215" style="height: 35px;">'
							.$this->l('Hash Secreto').'
						</td>
						<td>
							<input type="text" name="bnmx_secure_secret" value="'.Tools::getValue('bnmx_secure_secret', $this->bnmx_secure_secret).'" style="width: 430px;" />
						</td>
					</tr>


				</table>
			</fieldset>
			<br/>
			<input class="button" name="btnSubmit" value="'.$this->l('Guardar configuración').'" type="submit" />
		</form>';
	}

	private function _postValidation(){

	    // Si al enviar los datos del formulario de configuración hay campos vacios, mostrar errores.
		if (isset($_POST['btnSubmit']))
		{
			if (empty($_POST['bnmx_merchant_id']))
				$this->_postErrors[] = $this->l('Falta el código de comercio.');
			if (empty($_POST['bnmx_access_code']))
				$this->_postErrors[] = $this->l('Falta el çódigo de acceso.');
			if (empty($_POST['bnmx_terminal_id']))
				$this->_postErrors[] = $this->l('Falta la identificación de terminal.');
			if (empty($_POST['bnmx_merchant_category_code']))
				$this->_postErrors[] = $this->l('Falta el código de la categoría del comercio.');
			if (empty($_POST['bnmx_url']))
				$this->_postErrors[] = $this->l('Falta la URL de llamada al VPC.');
			if (empty($_POST['bnmx_secure_secret']))
				$this->_postErrors[] = $this->l('Falta la llave Hash.');
			
		}
	}
	private function _postProcess(){
		if (isset($_POST['btnSubmit']))
		{
			Configuration::updateValue('BNMX_ACCESSCODE', $_POST['bnmx_access_code']);
			Configuration::updateValue('BNMX_MERCHANTID', $_POST['bnmx_merchant_id']);
			Configuration::updateValue('BNMX_URL', $_POST['bnmx_url']);
			Configuration::updateValue('BNMX_TERMINAL_ID', $_POST['bnmx_terminal_id']);
			Configuration::updateValue('BNMX_MERCHANT_CATEGORY_CODE', $_POST['bnmx_merchant_category_code']);
			Configuration::updateValue('BNMX_SECURE_SECRET', $_POST['bnmx_secure_secret']);
		}

		$this->_html .= '<div class="conf confirm">
			<img src="../img/admin/ok.gif" alt="'.$this->l('ok').'" /> '
				.$this->l('Configuración actualizada').'
			</div>';
	}

}

?>