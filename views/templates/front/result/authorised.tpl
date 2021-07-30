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
<p class="label_return_authorised">{l s='Your order on' mod='moneytigo'} <span class="bold">{$shop_name|escape:'htmlall':'UTF-8'}</span> {l s='is complete.' mod='moneytigo'}</p>
<p class="moneytigo_confirmp">{l s='We are pleased to confirm that we have taken into account your order n°' mod='moneytigo'} <b>{$reference_order}.</b></p>
<center>
  <table style="border: 1px solid #CDCCCC; width: 60%">
    <tr>
      <td class="moneytigo_td">{l s='Order No.' mod='moneytigo'}</td>
      <td class="moneytigo_tdlight">{$reference_order}</td>
    </tr>
    <tr>
      <td class="moneytigo_td">{l s='Amount' mod='moneytigo'}</td>
      <td class="moneytigo_tdlight">{$amount}</td>
    </tr>
    <tr>
      <td class="moneytigo_td">{l s='Method of payment' mod='moneytigo'}</td>
      <td class="moneytigo_tdlight">{$method}</td>
    </tr>
    <tr>
      <td colspan="2"><hr>
        <center>
          <button class="btn" onclick="window.print()">{l s='Print' mod='moneytigo'}</button>
        </center></td>
    </tr>
  </table>
</center>
