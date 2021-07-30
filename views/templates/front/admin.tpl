{*
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
*}


	
	{if $display_msg_information == '1'}
<div class="panel">
  <div class="alert_moneytigo_admin{$msg_information_class|escape:'htmlall':'UTF-8'}"> {$msg_information|escape:'htmlall':'UTF-8'} </div>
</div>
{/if}
  {if $display_msg_confirmation == '1'}
<div class="panel">
  <div class="{$msg_confirmation_class|escape:'htmlall':'UTF-8'}"> {$msg_confirmation} </div>
</div>
{/if}
<form method="post" action="{$actionForm|escape:'htmlall':'UTF-8'}" class="account-creation" id="formMoneyTigo">
  <fieldset>
    <legend>{l s='My API credentials' mod='moneytigo'}</legend>
    <h2>{l s='Technical settings' mod='moneytigo'}</h2>
    <div class="alert" style="width: 95%;">{l s='First of all, in order for the MoneyTigo payment module to appear on your shopping cart, you need to enter your API Key (MerchantKey) and your secret encryption key (SecretKey)' mod='moneytigo'}</div>
    <input type="hidden" id="ips_action" name="MONEYTIGO_ADMIN_ACTION" value="UPDATE">
    <label for="api_key" style="margin-top:5px;"> {$label_api_key|escape:'htmlall':'UTF-8'} </label>
    <div class="margin-form">
      <input type="text" id="api_key" name="MONEYTIGO_GATEWAY_API_KEY" value="{$value_api_key|escape:'htmlall':'UTF-8'}">
    </div>
    <label for="crypt_key" style="margin-top:5px;"> {$label_crypt_key|escape:'htmlall':'UTF-8'} </label>
    <div class="margin-form">
      <input type="text" id="crypt_key" name="MONEYTIGO_GATEWAY_CRYPT_KEY" value="{$value_crypt_key|escape:'htmlall':'UTF-8'}">
    </div>
    <div> {l s='No MoneyTigo account ?' mod='moneytigo'} <a href="https://app.moneytigo.com/">
      <input type="button" name="noaccountmtg" style="color: white; background: #127ac1; padding: 5px; border-radius: 10px; font-size: 10px; font-weight: bold;" value="{l s='Click here to open one' mod='moneytigo'}" id="noaccountmtg">
      </a></div>
  </fieldset>
  <hr>
  <fieldset>
    <legend>{l s='Payment method in 3 times' mod='moneytigo'}</legend>
    <h2>{l s='Setting of the payment in 3 times' mod='moneytigo'}</h2>
    <p>{l s='The payment in 3 times allows you to propose to your customer a facility of payment in order to settle his order in three monthly payments' mod='moneytigo'} **</p>
    <label for="p3f">{l s='Activate the payment in 3 times' mod='moneytigo'}</label>
    <div class="margin-form"> <span style="display:block;float:left;margin-top:3px;">
      <div class="margin-form">
        <label for="activer_p3f" style="color:#080;display:block;float:left;text-align:left;width:85px;">{l s='Enable' mod='moneytigo'}</label>
        <input type="radio" name="MONEYTIGO_GATEWAY_P3F" id="activer_p3f" style="vertical-align:middle;display:block;float:left;margin-top:2px;margin-right:3px;" value="on"{$p3f_on|escape:'htmlall':'UTF-8'}/>
      </div>
      </span> <span style="display:block;float:left;margin-top:3px;">
      <div class="margin-form">
        <label for="desactiver_p3f" style="color:#900;display:block;float:left;text-align:left;width:60px;">{l s='Disable' mod='moneytigo'}</label>
        <input type="radio" name="MONEYTIGO_GATEWAY_P3F" style="vertical-align:middle;display:block;float:left;margin-top:2px;margin-right:3px;" id="desactiver_p3f" value="off"{$p3f_off|escape:'htmlall':'UTF-8'}/>
      </div>
      </span> </div>
    <br>
    <fieldset id="settings_p3f" {if $p3f_off}style="display: none"{/if}>
      <legend>Paramétrage du paiement en 3 fois</legend>
      <label for="seuil_p3f" style="margin-top:5px;">{l s='Minimum triggering threshold (Min 50 €)' mod='moneytigo'} </label>
      <div class="margin-form">
        <input type="text" id="seuil_p3f" name="MONEYTIGO_TRIGGER_P3F" value="{$seuil_p3f|escape:'htmlall':'UTF-8'}" placeholder="50" ; >
        <div> {l s='If you set a threshold in this case this payment method will be displayed only when the customer cart total is at least equal to this threshold' mod='moneytigo'}<span class="badge badge-pill badge-warning" style="font-size: 10px;"> {l s='If not defined, 50€ will be the default threshold' mod='moneytigo'}</span></div>
      </div>
      <br>
      <label for="fee_p3f"  style="margin-top:5px;">{l s='Fees to be applied to this payment method' mod='moneytigo'} </label>
      <div class="margin-form">
        <input type="text" id="fee_p3f" name="MONEYTIGO_FEE_P3F" value="{$fee_p3f|escape:'htmlall':'UTF-8'}" placeholder="0" ; >
        <div class="alert" style="width: 95%;"> {l s='0 indicates no fees, however you can indicate fees that correspond to a percentage of the total amount of the cart, if you indicate 1 it will indicate 1%, be careful not to use a comma but only a point as a decimal separator.' mod='moneytigo'}</div>
      </div>
    </fieldset>
  </fieldset>
  <hr>
  <input type="button" name="submitMoneytigo" style="color: white; background: #127ac1; padding: 10px; border-radius: 10px; font-size: 15px; font-weight: bold;" value="{l s='Update configuration' mod='moneytigo'}" id="submitMoneytigo" onclick="MoneyTigoFX.validateFormMoneyTigo();">
</form>
<script>
    $("#csrf").val(makeid(20));
    function makeid(number) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < number; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }
</script>