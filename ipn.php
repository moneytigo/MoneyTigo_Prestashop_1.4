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
 *  @copyright 2018-2020 IPS INTERNATIONNAL SAS
 *  @license   ips-payment.com
 */

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/moneytigo.php');
require_once(dirname(__FILE__) . '/inc/function_core.php');

$moneytigo = new moneytigoCore();
$trxgtwId =  Tools::getValue("TransId");
if ($trxgtwId) {
    $moneytigo->checkingTransaction($trxgtwId);
} else {
    header('HTTP/1.0 403 Forbidden');
    exit();
}
