<?php

/******************************************************************************
*	Module: Banamex Payment Gateway for Prestashop 
*	Author: Enrique Chavez
*	Author URI: http://tmeister.net
*	Version: 1.0
*	Description: Validates the response sent by the server, Banamex, and processes the order according to the response code.
******************************************************************************/

	/**************************************************************************
	*	Include core files
	**************************************************************************/
	include(dirname(__FILE__).'/../../config/config.inc.php');
	include(dirname(__FILE__).'/../../header.php');
	include(dirname(__FILE__).'/VPCPaymentConnection.php');
	include(dirname(__FILE__).'/banamexgateway.php');

	/**************************************************************************
	*	Get values from URL
	**************************************************************************/
	
	$id_module       = intval(Tools::getValue('id_module', 0));
	$id_cart         = intval(Tools::getValue('id_cart', 0));
	$secure_key      = isset($_GET['key']) ? $_GET['key'] : false;
	$payment_correct = 'no';
	
	/**************************************************************************
	*	If no module or secure_key redirect to history
	**************************************************************************/

	if (!$id_module OR !$secure_key OR empty($secure_key)){
		Tools::redirect('history.php');
	}

	/**************************************************************************
	*	Create a connection and banamexgategay instance
	**************************************************************************/

	$conn          = new VPCPaymentConnection();
	$banamex       = new banamexgateway();
	$config        = Configuration::getMultiple(array('BNMX_SECURE_SECRET'));
	$secure_secret = $config['BNMX_SECURE_SECRET'];
	
	$conn->setSecureSecret($secure_secret);

	foreach($_GET as $key => $value) {
		if (($key!="vpc_SecureHash") && ($key != "vpc_SecureHashType") && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
			$conn->addDigitalOrderField($key, $value);
		}
	}

	/**************************************************************************
	*	Get Secure Hash and validate against local Hash
	**************************************************************************/

	$serverSecureHash = array_key_exists("vpc_SecureHash", $_GET)	? $_GET["vpc_SecureHash"] : "";
	$secureHash       = $conn->hashAllFields();
	if( $secureHash != $serverSecureHash ){
		$payment_correct = 'bad_hash';	
	}
	
	/**************************************************************************
	*	Get all params sent by Banamex
	**************************************************************************/

	$vpc_Message         = Tools::getValue('vpc_Message', 0);
	$vpc_TransactionNo   = Tools::getValue('vpc_TransactionNo', 0);
	$vpc_TxnResponseCode = Tools::getValue('vpc_TxnResponseCode', 0);
	$vpc_Amount          = intval( Tools::getValue('vpc_Amount', 0) ) / 100;
	$txnResponseCodeDesc = getResultDescription($vpc_TxnResponseCode);

	/**************************************************************************
	*	if ResponseCode is 0 is a aprobbed payment
	**************************************************************************/	

	if( $vpc_TxnResponseCode === '0' ){
		$extra = array( 'vpc_TransactionNo' => $vpc_TransactionNo );
		$banamex->validateOrder($id_cart, 2, $vpc_Amount, 'Banamex' );
		$payment_correct = 'ok';	
	}

	/**************************************************************************
	*	Set values and load Smarty template
	**************************************************************************/		


	$smarty->assign(
		array(
			'status' => $payment_correct, 
			'this_path' => __PS_BASE_URI__, 
			'message' => $txnResponseCodeDesc
		)
	);

	if (is_file(_PS_THEME_DIR_.'modules/bbva/validate_bnmx_pay.tpl')){
		$smarty->display(_PS_THEME_DIR_.'modules/banamexgateway/validate_bnmx_pay.tpl');
	}else{
		$smarty->display(_PS_MODULE_DIR_.'banamexgateway/validate_bnmx_pay.tpl');
	}

	include(dirname(__FILE__).'/../../footer.php');

?>



