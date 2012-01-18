<form action="{$vpcURL}" method="post" id="bnmx_form" class="hidden">
	<input type="hidden" name="random" value="{$random}" />
</form>
<p class="payment_module">
	<a href="javascript:$('#bnmx_form').submit();" title="{l s='Pago en linea via Banamex' mod='bnmx'}">
		<img src="{$module_dir}banamex_logo.png" alt="{l s='Pago en linea via Banamex' mod='bnmx'}" />
		{l s='Pago con tarjeta de crédito o débito mediante Banamex' mod='bnmx'}
	</a>
</p>