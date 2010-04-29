<?php defined('_JEXEC') or die('Restricted access'); ?>

<?php echo $vars->message; ?>

<?php
// account token
// TODO must get from xml
define('TOKEN','0123456789abcdef0123456789abcdef');

// Include the return class
include('../../lib/retorno.php');

// function that captures return data
function retorno_automatico ( $VendedorEmail, $TransacaoID,
  $Referencia, $TipoFrete, $ValorFrete, $Anotacao, $DataTransacao,
  $TipoPagamento, $StatusTransacao, $CliNome, $CliEmail,
  $CliEndereco, $CliNumero, $CliComplemento, $CliBairro, $CliCidade,
  $CliEstado, $CliCEP, $CliTelefone, $produtos, $NumItens) {

  // Here you got the data from pagseguro, already verified.
  // Check the list of products and values with what you have in the database,
  // and if it's all correct, update the order status


}

// From here on, just html output ("we got your order", "waiting for payment confirmation",...)
?>
