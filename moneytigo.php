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

class Moneytigo extends PaymentModule
{
  protected $_html = '';
  protected $_postErrors = array();

  public $details;
  public $owner;
  public $address;
  public $extra_mail_vars;

  public function __construct()
  {
    $this->name = 'moneytigo';
    $this->tab = 'payments_gateways';
    $this->version = '1.1.1';
    $this->author = 'IPS INTERNATIONNAL SAS';
    $this->ps_versions_compliancy = array('min' => '1.4', 'max' => '1.5.6.3');
    $this->currencies = true;
    $this->currencies_mode = 'checkbox';
    parent::__construct();
    $this->displayName = $this->l('MoneyTigo');
    $this->description = $this->l('Accept credit card payments in minutes');
    $this->confirmUninstall = $this->l('Do you really want to uninstall MoneyTigo ?');
    if (!count(Currency::checkPaymentCurrencies($this->id))) {
      $this->warning = $this->l('No currency has been set for this module.');
    }
    $this->BaseUriApi = 'https://payment.moneytigo.com';
    $this->BaseWebPaymentStandard = 'https://checkout.moneytigo.com/pay/standard/token/';
    $this->BaseWebPaymentInstallments = 'https://checkout.moneytigo.com/pay/installment/token/';
    $this->ApiInitPayment = $this->BaseUriApi . '/init_transactions/';
    $this->ApiGetPayment = $this->BaseUriApi . '/transactions/';
    $this->ApiGetById = $this->BaseUriApi . '/transactions_by_merchantid/';
    include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/inc/function_core.php');
    require(_PS_MODULE_DIR_ . $this->name . '/backward_compatibility/backward.php');
  }

  /**
   * Module installation
   */
  public function install()
  {
    include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/moneytigo_install.php');
    $moneytigo_install = new MoneytigoInstall(); //sent version
    $moneytigo_install->createOrderState($this->name);
    $moneytigo_install->createDatabaseTables();
    return parent::install() &&
      $this->registerHook('payment') &&
      $this->registerHook('orderConfirmation') &&
      $this->registerHook('backOfficeHeader');
  }

  /**
   * Uninstalling the module
   */

  public function uninstall()
  {

    include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/moneytigo_install.php');
    $moneytigo_install = new MoneytigoInstall();

    if (
      !$this->unregisterHook('payment') ||
      !$this->unregisterHook('orderConfirmation') ||
      !$this->unregisterHook('backOfficeHeader')
    ) {
      Logger::addLog('Moneytigo module: unregisterHook failed', 4);
      return false;
    }

    if (!parent::uninstall()) {
      Logger::addLog('MoneyTigo module: uninstall failed', 4);
      return false;
    }
    return true;
  }

  public function hookPayment($params, $ListPnfMethod = array(), $SmartyParams = array())
  {

    global $smarty, $cookie;
    if (!$this->isAvailable()) {
      return;
    }
	  
	 
	  
    $moneytigoCore = new moneytigoCore();
    $cart = $params["cart"];
    $PnfActive = $moneytigoCore::getleaseactive($cart->getordertotal(true));

    if (isset($PnfActive)) {
      foreach ($PnfActive as $key => $value) {
        $RequestPnf = $moneytigoCore::moneytigo_constructPayment($params, $value);
        if (isset($RequestPnf['success']) && $RequestPnf['success'] == true) {
          $ListPnfMethod['pnf' . $value] = array('links' => $RequestPnf['UriToRedirect'], 'fees' => $RequestPnf['fees'], 'status' => $RequestPnf['success']);
          if ($value == "3") {
            $Canp3f = true;
          }
        } else {
          if ($value == "3") {
            $Canp3f = false;
          }
        }
      }
    } else {
      $Canp3f = false;
    }
    $TokenIs = $moneytigoCore::moneytigo_constructPayment($params);
    // generated SmartyAssignator
    $SmartyParams = array();
    if (isset($TokenIs['success']) && $TokenIs['success'] == true) {
      $SmartyParams['Token'] = $TokenIs['Token'];
      $SmartyParams['LinkStandard'] = $TokenIs['UriToRedirect'];
    } else {
	  $ErrorMoneyTigo = $TokenIs['error_message'];
		return("<p style=' color: red; font-weight: bold;'>MoneyTigo Fatal Error: $ErrorMoneyTigo</p>");
      return false;
    }
    if (isset($Canp3f) && $Canp3f == true) {
      $SmartyParams['Link3F'] = $ListPnfMethod['pnf3']['links'];
      $SmartyParams['3Fav'] = $Canp3f;
      $SmartyParams['3fees'] = $ListPnfMethod['pnf3']['fees'];
    } else {
      $SmartyParams['3Fav'] = $Canp3f;
    }
    $SmartyParams['path_img'] = $this->_path;
    $SmartyParams['MessageAnswer'] = Tools::getValue('ips_failed');
    $this->context->smarty->assign($SmartyParams);
    return $this->display(__FILE__, '/views/templates/front/14/standard.tpl');
  }


  /**
   * Processing the return page after payment
   * https://developer.moneytigo.com/prestashop_14/order-confirmation.php?id_cart=4&id_module=13&id_order=4&key=8ca3c26391e991cdd3f39392e048c516
   */
  public function hookOrderConfirmation()
  {

	$orderID = Order::getOrderByCartId(Tools::getValue('id_cart'));
    if (!$this->isAvailable())
      return;
    $order = new Order($orderID); 
    $this->context->smarty->assign(
      array(
        'reference_order' => $orderID,
        'method' => 'Payment by credit card with MoneyTigo',
        'amount' => Tools::displayPrice($order->{'total_paid'})
      )
    );
    return $this->display(__FILE__, 'views/templates/front/result/authorised.tpl');
  }
  /**
   * Display the methods only if the module is correctly configured!
   */
  public function isAvailable() //work
  {
    if (!$this->active) {
      return false;
    }
    if ((Configuration::get('MONEYTIGO_GATEWAY_API_KEY') != "") && (Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY') != "") && (Configuration::get('MONEYTIGO_GATEWAY_API_KEY') != "PRESTASHOP") && (Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY') != "00000000000000000000000000000")) {
      return true;
    }
    Logger::addLog("MoneyTigo : (" . date('Y-m-d H:i:s') . ") Mode not displayed because not active or ApiKey and SecretKey not defined !", 1);
    return false;
  }

  public function hookBackOfficeHeader($params)
  {
    return ("
		<link rel='stylesheet' href='" . $this->_path . "views/css/moneytigo_back.css'> 
		<script src='" . $this->_path . "views/js/validateConfiguration.js' type='text/javascript'>
		");
  }

  public function getContent()
  {
    if (!isset($this->_html) || empty($this->_html)) {
      $this->_html = '';
    }

    $msg_confirmation = '';
    $msg_confirmation_class = '';
    $display_msg_confirmation = '0';
    $msg_information = '';
    $msg_information_class = '';
    $display_msg_information = '0';

    if (Tools::getValue('MONEYTIGO_ADMIN_ACTION')) {
      Configuration::updateValue('MONEYTIGO_GATEWAY_API_KEY', Tools::getValue('MONEYTIGO_GATEWAY_API_KEY')); //Update ApiKey
      Configuration::updateValue('MONEYTIGO_GATEWAY_CRYPT_KEY', Tools::getValue('MONEYTIGO_GATEWAY_CRYPT_KEY')); //Update CrypKey or Secret Key
      Configuration::updateValue('MONEYTIGO_GATEWAY_P3F', Tools::getValue('MONEYTIGO_GATEWAY_P3F')); //Enable or not split payment 3steps
      $error = '<h3>' . $this->l('Change not registered') . '</h3>';
      $nberror = 0;
      if (Tools::getValue('MONEYTIGO_TRIGGER_P3F') && ((int)Tools::getValue('MONEYTIGO_TRIGGER_P3F') < 50 && Tools::getValue('MONEYTIGO_GATEWAY_P3F') == "on")) {
        $nberror = $nberror + 1;
        $error .= "<b>P3F</b> - " . $this->l('The activation of the payment in three times is not possible because you defined a threshold lower than the minimum authorized 50€') . "<br>";
      } else {
        Configuration::updateValue('MONEYTIGO_TRIGGER_P3F', Tools::getValue('MONEYTIGO_TRIGGER_P3F'));
      }
      if (!is_numeric(Tools::getValue('MONEYTIGO_FEE_P3F')) && Tools::getValue('MONEYTIGO_FEE_P3F')) {
        $nberror = $nberror + 1;
        $error .= "<b>P3F</b> - $this->l('Non-numeric value for the three times payment processing fee. (e.g. 1.00) for 1%.')<br>";
      } else {
        Configuration::updateValue('MONEYTIGO_FEE_P3F', Tools::getValue('MONEYTIGO_FEE_P3F'));
      }
      if ($nberror === 0) {
        $msg_confirmation_class = 'alert mtg-alert-success';
        $msg_confirmation = $this->l('Saved change'); //Update is saved and split payment activated
        $display_msg_confirmation = '1';
      } else {
        $msg_confirmation = $error;
        $msg_confirmation_class = ' alert';
        $display_msg_confirmation = '1';
      }
    }
    if (Tools::getValue('MONEYTIGO_ADMIN_ACTION')) {
      $activeTab_1 = ' active';
      $activeTab_2 = '';
      $activeTabList_1 = 'active';
      $activeTabList_2 = '';
      $apiKeyNumber = Tools::safeOutput(
        Tools::getValue('MONEYTIGO_GATEWAY_API_KEY', Configuration::get('MONEYTIGO_GATEWAY_API_KEY'))
      );
      $cryptKeyNumber = Tools::safeOutput(
        Tools::getValue('MONEYTIGO_GATEWAY_CRYPT_KEY', Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY'))
      );
    } else {
      $apiKeyNumber = Tools::getValue(
        'MONEYTIGO_GATEWAY_API_KEY',
        Configuration::get('MONEYTIGO_GATEWAY_API_KEY')
      );
      $cryptKeyNumber = Tools::getValue(
        'MONEYTIGO_GATEWAY_CRYPT_KEY',
        Configuration::get('MONEYTIGO_GATEWAY_CRYPT_KEY')
      );
    }
    $seuil_p3f = Tools::safeOutput(
      Tools::getValue('MONEYTIGO_TRIGGER_P3F', Configuration::get('MONEYTIGO_TRIGGER_P3F'))
    );
    $fee_p3f = Tools::safeOutput(
      Tools::getValue('MONEYTIGO_FEE_P3F', Configuration::get('MONEYTIGO_FEE_P3F'))
    );
    if (($apiKeyNumber == false) || ($apiKeyNumber == "")) {
      $apiKeyNumber = 'PRESTASHOP';
    }
    if (($cryptKeyNumber == false) || ($cryptKeyNumber == "")) {
      $cryptKeyNumber = '00000000000000000000000000000';
    }
    if (Tools::getValue('MONEYTIGO_GATEWAY_P3F', Configuration::get('MONEYTIGO_GATEWAY_P3F')) == "on") {
      $p3f_on = " checked=\"checked\"";
      $p3f_off = "";
    } else {
      $p3f_on = "";
      $p3f_off = " checked=\"checked\"";
    }
    if (Tools::getValue('MONEYTIGO_ADMIN_ACTION')) {
      $activeTab_1 = 'active';
      $activeTab_2 = '';
      $activeTabList_1 = 'active';
      $activeTabList_2 = '';
    } else {
      $activeTab_1 = 'active';
      $activeTab_2 = '';
      $activeTabList_1 = 'active';
      $activeTabList_2 = '';
    }
    if ($this->context->language->iso_code == 'fr') {
      $imageName = "moneytigo_header_admin.jpg";
      $imageNameBottom = "moneytigo_header_admin_bottom.jpg";
    } else {
      $imageName = "moneytigo_header_admin_en.jpg";
      $imageNameBottom = "moneytigo_header_admin_bottom_en.jpg";
    }
    $this->context->smarty->assign(
      array(
        'image_header' => "../modules/" . $this->name . "/views/img/" . $imageName,
        'image_header_bottom' => "../modules/" . $this->name . "/views/img/" . $imageNameBottom,
        'activeTabList_1' => $activeTabList_1,
        'activeTabList_2' => $activeTabList_2,
        'activeTab_1' => $activeTab_1,
        'actionForm' => '',
        'label_api_key' => $this->l('Your API Key (MerchantKey)'),
        'value_api_key' => $apiKeyNumber,
        'label_crypt_key' => $this->l('Your encryption key (SecretKey)'),
        'value_crypt_key' => $cryptKeyNumber,
        'p3f_on' => $p3f_on,
        'p3f_off' => $p3f_off,
        'seuil_p3f' => $seuil_p3f,
        'fee_p3f' => $fee_p3f,
        'activeTab_2' => $activeTab_2,
        'msg_information' => $msg_information,
        'msg_information_class' => $msg_information_class,
        'display_msg_information' => $display_msg_information,
        'msg_confirmation' => $msg_confirmation,
        'msg_confirmation_class' => $msg_confirmation_class,
        'display_msg_confirmation' => $display_msg_confirmation
      )
    );
    return $this->display(__FILE__, '/views/templates/front/admin.tpl');
  }
}
