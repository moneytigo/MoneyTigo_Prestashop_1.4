<?php
if ( !defined( '_PS_VERSION_' ) )
  exit;

function upgrade_module_1_1_1( $module ) {
  $apikey = Configuration::get( 'MONEYTIGO_GATEWAY_API_KEY' );
  $secretkey = Configuration::get( 'MONEYTIGO_GATEWAY_CRYPT_KEY' );
  Logger::addLog( "[START] - Update moneytigo module to version 1.1.1", 1 );
  include_once( _PS_MODULE_DIR_ . '/moneytigo/moneytigo_install.php' );
  $moneytigo_install = new MoneytigoInstall();
  $moneytigo_install->deleteConfiguration( 'moneytigo' );
  $moneytigo_install->createOrderState( 'moneytigo' );

  // Re-insertion of old merchant settings
  Configuration::updateValue( 'MONEYTIGO_TRIGGER_P3F', $trigerpnf3 );
  Configuration::updateValue( 'MONEYTIGO_GATEWAY_P3F', $pnf3active );
  Configuration::updateValue( 'MONEYTIGO_GATEWAY_API_KEY', $apikey );
  Configuration::updateValue( 'MONEYTIGO_GATEWAY_CRYPT_KEY', $secretkey );

  Logger::addLog( "Moneytigo Updating module" . uniqid(), 3 );
  Logger::addLog( "[SUCCESS] - Update moneytigo module to version 1.1.1", 1 );
  Logger::addLog( "[SUCCESS] - Clear Smarty CACHE", 1 );
  Tools::clearSmartyCache();
  Logger::addLog( "[SUCCESS] - Clear Xml CACHE", 1 );
  Tools::clearXMLCache();
  Logger::addLog( "[SUCCESS] - Regenerate INDEX", 1 );
  Tools::generateIndex();
  return true;
}
?>
