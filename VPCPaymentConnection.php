<?php

require('PaymentCodesHelper.php');

class VPCPaymentConnection {
	
	// Define Variables
	// ----------------

	private $errorExists = false;             // Indicates if an error exists
	private $errorMessage;                    // The error message
	
	private $postData;                        // Data to be posted to the payment server
	
	private $responseMap;                     // Array of receipt data 
	
	private $secureHashSecret;                // Used for one way hashing in 3-party transactions
	private $hashInput;
	private $message;
	public function addDigitalOrderField($field, $value) {
		
		if (strlen($value) == 0) return false;      // Exit the function if no $value data is provided
		if (strlen($field) == 0) return false;      // Exit the function if no $value data is provided
		
		// Add the digital order information to the data to be posted to the Payment Server
		$this->postData .= (($this->postData=="") ? "" : "&") . urlencode($field) . "=" . urlencode($value);
		
		// Add the key's value to the MD5 hash input (only used for 3 party)
		$this->hashInput .= $field . "=" . $value . "&";
		
		return true;
		
	}

	
	public function sendMOTODigitalOrder($vpcURL, $proxyHostAndPort = "", $proxyUserPwd = "") {
		$message = "";
		// Generate and Send Digital Order (& receive DR)
		// *******************************************************

		
		// Exit if there is no data to send to the Virtual Payment Client
		if (strlen($this->postData) == 0) return false;
		
		
		// Get a HTTPS connection to VPC Gateway and do transaction
		// turn on output buffering to stop response going to browser
		ob_start();
		
		// initialise Client URL object
		$ch = curl_init();
		
		// set the URL of the VPC
		curl_setopt ($ch, CURLOPT_URL, $vpcURL);
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->postData);
		
		if (strlen($proxyHostAndPort) > 0) {
			if (strlen($proxyUserPwd) > 0) {
				// (optional) set the proxy IP address, port and proxy username and password
				curl_setopt ($ch, CURLOPT_PROXY, $proxyHostAndPort, CURLOPT_PROXYUSERPWD, $proxyUserPwd);
			}
			else {
			// (optional) set the proxy IP address and port without proxy authentication
			curl_setopt ($ch, CURLOPT_PROXY, $proxyHostAndPort);
			
		  }
		  
		}
		
		// (optional) certificate validation
		// trusted certificate file
		//curl_setopt($ch, CURLOPT_CAINFO, "c:/temp/ca-bundle.crt");
		
		//turn on/off cert validation
		// 0 = don't verify peer, 1 = do verify
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		// 0 = don't verify hostname, 1 = check for existence of hostame, 2 = verify
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
		
		// connect
		curl_exec ($ch);
		
		// get response
		$response = ob_get_contents();
		
		// turn output buffering off.
		ob_end_clean();
		
		// set up message paramter for error outputs
		$this->errorMessage = "";
		
		// serach if $response contains html error code
		if(strchr($response,"<HTML>") || strchr($response,"<html>")) {;
		    $this->errorMessage = $response;
		} else {
		    // check for errors from curl
		    if (curl_error($ch))
		          $this->errorMessage = "curl_errno=". curl_errno($ch) . " (" . curl_error($ch) . ")";
		}
		

		// close client URL
		curl_close ($ch);
		
		// Extract the available receipt fields from the VPC Response
		// If not present then let the value be equal to 'No Value Returned'
		$this->responseMap = array();
		
		// process response if no errors
		if (strlen($message) == 0) {
		    $pairArray = explode("&", $response);
		    foreach ($pairArray as $pair) {
		        $param = explode("=", $pair);
		        $this->responseMap[urldecode($param[0])] = urldecode($param[1]);
		    }
		    
		    return true;
		    
		} else {
			
				return false;
				
		}

	}
	
	
	public function getDigitalOrder($vpcURL) {
		
		$redirectURL = $vpcURL."?".$this->postData;

		return $redirectURL;

		
	}

	
	public function decryptDR($digitalReceipt) {
		
		// Decrypt Digital Receipt
		// ********************************


		if (!$this->socketCreated) return false;        // Exit function if an the socket connection hasn't been created
		if ($this->errorExists) return false;           // Exit function if an error exists



		// (This primary command to decrypt the Digital Receipt)
    $cmdResponse = $this->sendCommand("3,$digitalReceipt");
    
    if (substr($cmdResponse,0,1) != "1") {
        // Retrieve the Payment Client Error (There may be none to retrieve)
        $cmdResponse = $this->sendCommand("4,PaymentClient.Error");
				if (substr($cmdResponse,0,1) == "1") {$exception = substr($cmdResponse,2);}

        $this->errorMessage = "(11) Digital Order has not created correctly - decryptDR($digitalReceipt) failed - $exception";
        $this->errorExists = true;
        
        return false;
        
    }

		// Set the socket timeout value to normal
		$this->payClientTimeout = $this->SHORT_SOCKET_TIMEOUT;

		// Automatically call the nextResult function
		$this->nextResult();
		
		return true;



		
	}
	
	
	public function getResultField($field) {
		

		return $this->null2unknown($field);

    
    //return substr($cmdResponse,0,1) == "1" ? substr($cmdResponse,2) : "";
    
	}


	public function getErrorMessage() {
		return $this->errorMessage;
	}
	
	
	public function setSecureSecret($secret) {		
		$this->secureHashSecret = $secret;
	}
	
	
	public function hashAllFields() {
		$this->hashInput=rtrim($this->hashInput,"&");
		return strtoupper(hash_hmac('SHA256',$this->hashInput, pack("H*",$this->secureHashSecret)));
	}


	private function null2unknown($key) {

		// This subroutine takes a data String and returns a predefined value if empty
		// If data Sting is null, returns string "No Value Returned", else returns input
		   
		// @param $in String containing the data String
		
		// @return String containing the output String

		if (array_key_exists($key, $this->responseMap)) {
		    if (!is_null($this->responseMap[$key])) {
		        return $this->responseMap[$key];
		    }
		} 
		return "No Value Returned";
	}

	
}

?>