{if $status == 'ok'}
	{capture name=path}{l s='Pago realizado correctamente' mod='bnmx'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<h2>{l s='Confirmación del pedido' mod='bnmx'}</h2>
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}
	<h2>{l s='Pago realizado correctamente' mod='bnmx'}</h2>
	<table width="100%" border="0" cellspacing="8">
		<tr>
			<td><img src="{$this_path}modules/banamexgateway/pago_correcto.jpg" width="300" height="250" alt="" /></td>
		    <td>
		    	<h3>{l s='Su compra se ha realizado correctamente, ahora puede descargar su archivo desde su historial.' mod='bnmx'}</h3>
		    	<h3>{l s='Codigo de Autorización:'} <span style="color:#3C8534">{$transaction_id}<span></h3>
		    </td>
		</tr>
	</table>
	<ul class="footer_links">
		<li><a href="{$base_dir_ssl}history.php"><img src="{$img_dir}icon/order.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl}history.php">{l s='Historial y Detalles de sus Pedidos' mod='bnmx'}</a></li>
		<li><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /></a><a href="{$base_dir}">{l s='Inicio' mod='bnmx'}</a></li>
	</ul>
{else}
	{capture name=path}{l s='Pago no completado' mod='bnmx'}{/capture}
	{include file="$tpl_dir./breadcrumb.tpl"}
	<h2>{l s='Confirmación del pedido' mod='bnmx'}</h2>
	{assign var='current_step' value='payment'}
	{include file="$tpl_dir./order-steps.tpl"}
	<h2>{l s='Pago no completado' mod='bnmx'}</h2>
	<table width="100%" border="0" cellspacing="8">
		<tr>
			<td><img src="{$this_path}modules/banamexgateway/pago_error.jpg" width="300" height="250" alt="" /></td>
			<td>{l s='Lo sentimos, su pago no se ha completado.' mod='bnmx'} {l s='La razón que el banco dio es:' mod='bnmx'}<br><br><strong>{$message}</strong><br/><br/>{l s='Puede intentarlo de nuevo o escoger otro método de pago.' mod='bnmx'}</td>
		</tr>
	</table>
	<ul class="footer_links">
		<li><a href="{$base_dir_ssl}order.php?step=3"><img src="{$img_dir}icon/cart.gif" alt="" class="icon" /></a><a href="{$base_dir_ssl}order.php?step=3">{l s='Volver a elegir medio de pago' mod='bnmx'}</a></li>
		<li><a href="{$base_dir}"><img src="{$img_dir}icon/home.gif" alt="" class="icon" /></a><a href="{$base_dir}">{l s='Inicio' mod='bnmx'}</a></li>
	</ul>
{/if}