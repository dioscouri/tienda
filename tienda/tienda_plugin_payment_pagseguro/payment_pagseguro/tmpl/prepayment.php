<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php echo JText::_( "Tienda Paypal Payment Stadard Preparation Message" ); ?>

<form target="pagseguro" method="post"
action="https://pagseguro.uol.com.br/checkout/checkout.jhtml">

<!-- STORE INFO -->
<input type="hidden" name="email_cobranca" value="<?php echo $vars->merchant_email; ?>">
<input type="hidden" name="tipo" value="CP">
<input type="hidden" name="moeda" value='<?php echo $vars->currency_code; ?>> <!-- should be BRL -->

<!-- CLIENT INFO -->
<input type="hidden" name="cliente_nome" value='<?php echo @$vars->first_name; ?>'>
<input type="hidden" name="cliente_cep" value='<?php echo @$vars->postal_code; ?>'>
<input type="hidden" name="cliente_end" value='<?php echo @$vars->address_1; ?>'>
<input type="hidden" name="cliente_num" value="12">
<input type="hidden" name="cliente_compl" value="Sala 109">
<input type="hidden" name="cliente_bairro" value="Bairro do cliente">
<input type="hidden" name="cliente_cidade" value='<?php echo @$vars->city; ?>'>
<input type="hidden" name="cliente_uf" value="ES">
<input type="hidden" name="cliente_pais" value='<?php echo @$vars->country; ?>'>
<input type="hidden" name="cliente_ddd" value="27">
<input type="hidden" name="cliente_tel" value="22345678">
<input type="hidden" name="cliente_email" value='<?php echo @$vars->email; ?>'>

<!--CART INFO -->
<!-- TODO must loop for each item -->
<input type="hidden" name="item_id_1" value="12345">
<input type="hidden" name="item_descr_1"
value="Descrição do item a ser vendido">
<input type="hidden" name="item_quant_1" value="1">
<input type="hidden" name="item_valor_1" value="100">
<input type="hidden" name="item_frete_1" value="0">
<input type="hidden" name="item_peso_1" value="0">

<input type="hidden" name="item_id_2" value="67890">
<input type="hidden" name="item_descr_2"
value="Descrição do item 2 a ser vendido">
<input type="hidden" name="item_quant_2" value="1">
<input type="hidden" name="item_valor_2" value="199">
<input type="hidden" name="item_frete_2" value="0">
<input type="hidden" name="item_peso_2" value="0">
<input type="hidden" name="tipo_frete" value="EN">

<!-- BUTTON -->
<?php echo JText::_('Click The Paypal Button to Complete Your Order'); ?>
<input type="image"
src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/carrinhoproprio/btnFinalizaBR.jpg"
name="submit" alt="Pague com PagSeguro - é rápido, grátis e seguro!">
</form>
