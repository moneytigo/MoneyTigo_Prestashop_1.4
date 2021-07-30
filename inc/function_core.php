<?php

/**
 * NOTICE OF LICENSE
 *
 * This file is create by Ips
 * For the installation of the software in your application
 * You accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    Ips
 *  @copyright 2018-2021 IPS INTERNATIONNAL SAS
 *  @license   moneytigo.com
 */

if (!defined('_PS_VERSION_')) {
  exit;
}
class moneytigoCore extends Moneytigo
{


  public function __construct()
  {
    parent::__construct();
  }
  /* Management of the activation and deactivation of the payment in several times */
  public function getleaseactive($amount)
  {
    $seuil3 = Configuration::get('MONEYTIGO_TRIGGER_P3F');
    if (!$seuil3 || $seuil3 < 50) {
      $seuil3 = "50";
    }
    $pnf3 = Configuration::get('MONEYTIGO_GATEWAY_P3F');
    $pnflist = array();
    if ($pnf3 == "on" && $amount >= $seuil3) {
      $pnflist[] = '3';
    }
    return $pnflist;
  }

  /* Builder of payment initiation requests sent to MoneyTigo */
  public function moneytigo_constructPayment($params, $Lease = NULL, $feefordisplay = NULL)
  {
	  
    $Mtd = new MoneyTigo();
    $cart = $params["cart"];
    $CustomerIs = new Customer($params["cart"]->id_customer);
    $urlIPN = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http');
    $urlIPN .= '://' . $_SERVER['HTTP_HOST'] . __PS_BASE_URI__ . 'modules/' . $Mtd->name . '/ipn.php';
    //Need Get Token //for simple payment
    $params = array(
      "MerchantKey" => Configuration::get('MONEYTIGO_GATEWAY_API_KEY'),
      "RefOrder" => (int)$cart->id,
      'Customer_Name' => $CustomerIs->{'lastname'},
      'Customer_FirstName' => $CustomerIs->{'firstname'},
      'Customer_Email' => $CustomerIs->{'email'},
      'extension' => 'prestashop-' . _PS_VERSION_ . '-Module1.0',
      'urlIPN' => $urlIPN
    );
    $params['urlOK'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'order-confirmation.php?key=' . $CustomerIs->secure_key . '&id_cart=' . (int)$cart->id . '&id_module=' . (int)$this->id . '&id_order=' . (int)$this->currentOrder;
    $params['urlKO'] = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__ . 'order.php?step=3&ips_failed=1';

    /* Set up payments in installments if available and apply fee preferences if defined */
    if ($Lease == 3) {
      $params['Lease'] = $Lease;
      $fee3 = Configuration::get('MONEYTIGO_FEE_P3F');
      if ($Lease == 3) {
        if (isset($fee3) && $fee3 > 0) {
          $fee3_preset = $cart->getordertotal(true) * $fee3 / 100;
          $feefordisplay = ' ' . number_format($fee3_preset, 2, ',', '.') . ' €';
          $params['amount'] = number_format($cart->getordertotal(true) + $fee3_preset, 2, '.', ' ');
        } else {
          $params['amount'] = $cart->getordertotal(true); /* No fee, original amount applies */
        }
      }
      $PaymentLinks = $Mtd->BaseWebPaymentInstallments;
    } else {
      /* Standard payment the normal amount of the order is applied */
      $params['amount'] = $cart->getordertotal(true);
      $PaymentLinks = $Mtd->BaseWebPaymentStandard;
    }
    $TokenIs = moneytigoCore::moneytigo_getToken($params);
    if (isset($TokenIs['Code']) && $TokenIs['Code'] == 200) {
      $PaymentReturn = array();
      $PaymentReturn['success'] = true;
      $PaymentReturn['Links'] = $PaymentLinks;
      $PaymentReturn['Token'] = $TokenIs['SACS'];
      $PaymentReturn['UriToRedirect'] = $TokenIs['DirectLinkIs'];
      $PaymentReturn['fees'] = $feefordisplay;

      return $PaymentReturn;
    } else {
      $PaymentReturn = array();
      $PaymentReturn['success'] = false;
      $PaymentReturn['error_message'] = json_encode($TokenIs);
      return $PaymentReturn;
    }
  }

  /* @Curl query executor */
  private function moneytigo_getToken($args)
  {
    $Mtd = new MoneyTigo();
    $tokencurl = curl_init();
    curl_setopt($tokencurl, CURLOPT_URL, $Mtd->ApiInitPayment);
    curl_setopt($tokencurl, CURLOPT_POST, 1);
    curl_setopt($tokencurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($tokencurl, CURLOPT_POSTFIELDS, http_build_query(moneytigoCore::moneytigo_signRequest($args)));
    $tokenheaders = array();
    $tokenheaders[] = 'Content-Type: application/x-www-form-urlencoded';
    $tokenheaders[] = 'Content-Type: application/json';
    curl_setopt($tokencurl, CURLOPT_HTTPHEADER, $tokenheaders);
    $resultoken = json_decode(curl_exec($tokencurl), true);
    return ($resultoken);
  }

  /* MoneyTigo Payment Initiation Signature Calculation */
  private function moneytigo_signRequest($params, $beforesign = "")
  {
    $ShaKey = Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY');
    foreach ($params as $key => $value) {
      $beforesign .= $value . "!";
    }
    $beforesign .= $ShaKey;
    $sign = hash("sha512", base64_encode($beforesign . "|" . $ShaKey));
    $params['SHA'] = $sign;
    return $params;
  }
  /* Constructeur de requête pour l'interrogation de l'api MoneyTigo @transactions */
  public function checkingTransaction($transactionBankID)
  {
    $RequestContruct = array(
      "TransID" => $transactionBankID,
      "ApiKey" => Configuration::get('MONEYTIGO_GATEWAY_API_KEY'),
      "SHA" => $this->signRequest(Configuration::get('MONEYTIGO_GATEWAY_API_KEY'), Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY'), $transactionBankID)
    );
    $TransactionSinfo = $this->getTransactionInfo($RequestContruct);

    if (isset($TransactionSinfo->{'ErrorCode'})) {
      Logger::addLog("IPN - MoneyTigo : " . $TransactionSinfo->{'ErrorCode'} . " - " . $TransactionSinfo->{'ErrorDescription'} . " for", 4);
      $answerIs = json_encode(
        array(
          "success" => "false",
          "error" => $TransactionSinfo->{'ErrorCode'},
          "error_description" => $TransactionSinfo->{'ErrorDescription'}
        )
      );
      header("Status: 401 Authorization failed or transaction not found", false, 401);
      exit($answerIs);
    } else {

      $this->confirmOrder($TransactionSinfo);
    }
  }

  /* Executeur @curl MoneyTigo pour @transactions */
  private function getTransactionInfo($request)
  {
    $UriRequest = "" . $this->ApiGetPayment . "?";
    foreach ($request as $key => $value) {
      $UriRequest .= $key . "=" . $value . "&";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $UriRequest);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
      Logger::addLog("IPN - MoneyTigo : " . curl_error($ch) . " for", 4);
      $answerIs = json_encode(array(
        "success" => "false",
        "error" => "internal",
        "error_description" => curl_error($ch)
      ));
      header("Status: 500 Internal fatal error with curl request GetTransactionInfo", false, 500);
      exit($answerIs);
    } else {
      return json_decode($result);
    }
    curl_close($ch);
  }
  /* Enregistrement et confirmateur de commande */

  private function confirmOrder($transactiondetails)
  {

    // know type of payment
    if ($transactiondetails->{'Type'}->{'type'} === "installment") {
      //payment pnf
      if ($transactiondetails->{'Type'}->{'condition'} === "3") {
        //3n
        $ApprovedState = Configuration::get('MONEYTIGO_OS_ACCEPTED_P3F');
        $typetransaction = "pnf3";
      }
    } else {
      $ApprovedState = Configuration::get('MONEYTIGO_OS_ACCEPTED');
      $typetransaction = "standard";
    }

    $CartingID = $transactiondetails->{'Merchant_Order_Id'};
    $cart = new Cart($CartingID);
    $order = new Order();
    $orderId = $order->getOrderByCartId($cart->id);
    if ($orderId) {
      //This cart has already been transformed into an order, we check if the payment is processed, if necessary we record it.
      if ($transactiondetails->{'Transaction_Status'}->{'State'} == 2) { //Beginning of the procedure only if the transaction is accepted.
        $order = new Order($orderId);
        if ($order->getCurrentState() == $ApprovedState) {
          //Order already processed and confirmed
          Logger::addLog("IPN - MoneyTigo : Order ID #" . $orderId . " - is already processed and confirmed in your prestashop", 1);
          $answerIs = json_encode(array("success" => "true", "OrderID" => $orderId)); //success cause already processed and confirmed
          exit($answerIs); //Immediate stop of the process, action completed and response received.
        } else {
          //Order confirmation not processed, we start processing.
          $orderHistory = new OrderHistory();
          $orderHistory->id_order = $orderId;
          $orderHistory->changeIdOrderState((int)$ApprovedState, $orderId);
          $orderHistory->addWithemail();
          $orderHistory->save();
          $this->insertDataBase($orderId, $transactiondetails->{'Merchant_Order_Id'}, $typetransaction, $transactiondetails);
          if (_PS_VERSION_ > '1.5' && _PS_VERSION_ < '1.5.2') {
            $order->current_state = $orderHistory->id_order_state;
            $order->update();
          }
          Logger::addLog("IPN - MoneyTigo : Order # " . $orderId . " to was processed successfully!", 1);
          $answerIs = json_encode(array("success" => "true", "OrderID" => $orderId)); //success cause already processed and confirmed
          exit($answerIs);
        }
      } else {
        Logger::addLog("IPN - MoneyTigo : Order # " . $order->id . " at this moment the payment seems not to be accepted yet !", 2);
        exit();
      }
    } else {
      if ($cart->id) {
        if ($transactiondetails->{'Transaction_Status'}->{'State'} == 2) {
          $customer = new Customer((int)$cart->id_customer);
          $message = "Process processing for the transaction " . $transactiondetails->{'Merchant_Order_Id'};
          $this->validateOrder(
            $cart->id,
            $ApprovedState,
            (float)$cart->getOrderTotal(true, Cart::BOTH),
            $this->displayName,
            $message,
            array(),
            (int)$cart->id_currency,
            false,
            $customer->secure_key
          );
          $order = new Order($this->currentOrder);
          $this->insertDataBase($order->id, $transactiondetails->{'Merchant_Order_Id'}, $typetransaction, $transactiondetails);
          $answerIs = json_encode(array("success" => "true", "OrderID" => $order->id));
          Logger::addLog("IPN - MoneyTigo : Order # " . $order->id . " to was processed successfully!", 1);
          exit($answerIs);
        } else {
          Logger::addLog("IPN - MoneyTigo : Cart # " . $cart->id . " at this moment the payment seems not to be accepted yet !", 2);
          exit();
        }
      } else {
        Logger::addLog("IPN - MoneyTigo : Payment validation error , CART $CartingID not exist!", 4);
        header('HTTP/1.0 403 Forbidden');
        exit();
      }
    }
  }

  /* Signature de requête @get transactions */

  private function signRequest($key, $secret, $txid)
  {
    $BeforeSign = $key . "!" . $txid . "!" . $secret;
    return hash("sha512", base64_encode($BeforeSign . "|" . $secret));
  }

  /**
   * Changer Id Order State
   */
  public function changeIdOrderState($transactionId, $stateId)
  {
    if ($transactionId == "") {
      return false;
    }
    $orderHistory = new OrderHistory();
    $orderHistory->id_order = $transactionId;
    $orderHistory->changeIdOrderState($stateId, $transactionId);
    $orderHistory->addWithemail();
    $orderHistory->save();
    return true;
  }

  /**
   * Insert transaction in database
   */


  public function insertDataBase($orderId, $transactionId, $type_tr, $ipsanswer = null)
  {
    //Adding transaction information to a separate table
    if (!empty($orderId) && !empty($transactionId) && ($type_tr == "standard" || $type_tr == "pnf3")) {
      $now = date("Y-m-d H:i:s");
      $db = Db::getInstance();
      $requestSql = 'INSERT INTO `'
        . _DB_PREFIX_
        . 'moneytigo_transactiondata`
                (`order_id`, `transaction_id`, `datetime`, `type_tr`, `IPS_Return_Responses`) VALUES("' .
        (int)$orderId . '", "' .
        pSQL($transactionId) . '", "' .
        $now . '", "' .
        pSQL($type_tr) . '", "' .
        pSQL(json_encode($ipsanswer)) . '")';
      try {
        $db->execute($requestSql);
      } catch (Exception $exception) {
        Logger::addLog("IPN - MoneyTigo : Failure while adding the transaction to the database !  OrderID : " . $orderId, 3);
      }
    } else {
      return false;
    }
  }
}
