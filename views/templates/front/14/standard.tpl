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

{if $MessageAnswer == "1"}
<p class="payment_module" style="background-color: red; color: white; font-weight: bold;">{l s='Your bank refused the payment!' mod='moneytigo'}</p>
{/if}
<p class="payment_module"> <a class="moneytigo-logo-link bankwire" href="{$LinkStandard|escape:'htmlall':'UTF-8'}" title="{l s='Pay by credit card with moneytigo' mod='moneytigo'}"> <img id="CB" class="moneytigo-logo" src="{$path_img|escape:'htmlall':'UTF-8'}/views/img/carte.png" alt="{l s='Pay by credit card with moneytigo' mod='moneytigo'}" width="140px"/> {l s='Pay with moneytigo' mod='moneytigo'} </a> </p>

{if $3Fav }
<p class="payment_module"> <a id="P3F" class="moneytigo-logo-link bankwire" href="{$Link3F|escape:'htmlall':'UTF-8'}" title="{l s='Pay in three time with moneytigo' mod='moneytigo'}"> <img id="CB" class="moneytigo-logo" src="{$path_img|escape:'htmlall':'UTF-8'}/views/img/carte.png" alt="{l s='Pay in three time with moneytigo' mod='moneytigo'}" width="140px"/> {l s='Pay in three time with moneytigo' mod='moneytigo'} 
  {if $3fees}
  ({l s='Fees' mod='moneytigo'} {$3fees}  )
  {/if} </a> </p>
{/if} 