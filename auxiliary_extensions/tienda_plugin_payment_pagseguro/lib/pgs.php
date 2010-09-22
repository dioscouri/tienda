<?php

class pgs {
  var $_itens = array();
  var $_config = array ();
  var $_cliente = array ();
  /**
   * pgs
   *
   * Função de inicialização
   * você pode passar os parâmetros padrão alterando as informações padrão como o tipo de moeda ou
   * o tipo de carrinho (próprio ou do pagseguro)
   *
   * Ex:
   * <code>
   * array (
   *   'email_cobranca' => 'raposa@vermelha.com.br',
   *   'tipo'           => 'CBR',
   *   'ref_transacao'  => 'A36',
   *   'tipo_frete'     => 'PAC',
   * )
   * </code>
   *
   * @access public
   * @param array $args    Array associativo contendo as configurações que você deseja alterar
   * @return               void
   */
  function pgs($args = array()) {
    if ('array'!=gettype($args)) $args=array();
    $default = array(
      'email_cobranca'  => '',
      'tipo'            => 'CP',
      'moeda'           => 'BRL',
    );
    $this->_config = $args+$default;
  }
  /**
   * error
   *
   * Retorna a mensagem de erro
   *
   * @access public
   * @return string
   */
  function error($msg){
    trigger_error($msg);
    return $this;
  }
  /**
   * adicionar
   *
   * Adiciona um item ao carrinho
   *
   * O elemento adicionado deve ser um array associativo com as seguintes chaves
   * id         => string com até 100 caracteres
   * descricao  => string com até 100 caracteres
   * quantidade => integer
   * valor      => integer ou float
   * peso       => integer (opcional) coloque o peso (em gramas) do produto, caso seja um peso único para todos os
   *               produtos é preferivel inplantá-lo no new pgs(array('item_peso_1' => 1300))
   * frete      => integer ou float (opcional) coloque o valor do frete, caso seja um frete único
   *               para todos os produtos é preferivel inplantá-lo no new pgs(array('item_frete_1' => 30))
   *
   * @access public
   * @param array $item O elemento que será adicionado
   * @return object pgs O próprio objeto para que possa ser concatenado a outro comando dele mesmo
   */
  function adicionar($item) {

    if ('array' !== gettype($item))
      return $this->error("Item precisa ser um array.");
    if(isset($item[0]) && 'array' === gettype($item[0])){
      foreach ($item as $elm) {
        if('array' === gettype($elm)) {
          $this->adicionar($elm);
        }
      }
      return $this;
    }

    $tipos=array(
      "id" =>         array(1,"string",                '@\w@'         ),
      "quantidade" => array(1,"string,integer",        '@^\d+$@'      ),
      "valor" =>      array(1,"double,string,integer", '@^\d*\.?\d+$@'),
      "descricao" =>  array(1,"string",                '@\w@'         ),
      "frete" =>      array(0,"string,integer",        '@^\d+$@'      ),
      "peso" =>       array(0,"string,integer",        '@^\d+$@'      ),
    );

    foreach($tipos as $elm=>$valor){
      list($obrigatorio,$validos,$regexp)=$valor;
      if(isset($item[$elm])){
        if(strpos($validos,gettype($item[$elm])) === false ||
          (gettype($item[$elm]) === "string" && !preg_match($regexp,$item[$elm]))){
          return $this->error("Valor invalido passado para $elm.");
        }
      }elseif($obrigatorio){
        return $this->error("O item adicionado precisa conter $elm");
      }
    }

    $this->_itens[] = $item;
    return $this;
  }
  /**
   * cliente
   *
   * Define o cliente a ser inserido no sistema.
   * Recebe como parametro um array associativo contendo os dados do cliente.
   *
   * Ex:
   * <code>
   * array (
   *   'nome'   => 'José de Arruda',
   *   'cep'    => '12345678',
   *   'end'    => 'Rua dos Tupiniquins',
   *   'num'    => 37,
   *   'compl'  => 'apto 507',
   *   'bairro' => 'Sto Amaro',
   *   'cidade' => 'São Camilo',
   *   'uf'     => 'SC',
   *   'pais'   => 'Brasil',
   *   'ddd'    => '48',
   *   'tel'    => '55554877',
   *   'email'  => 'josearruda@teste.com',
   * )
   * </code>
   *
   * @access public
   * @param array $args Dados sobre o cliente, se não forem passados os dados corretos,
   * o pagseguro se encarrega de perguntar os dados ao cliente
   * @return void
   */
  function cliente($args=array()) {
    if ('array'!==gettype($args)) return;
    $this->_cliente = $args;
  }
  /**
   *
   * mostra
   *
   * Mostra o formulário de envio de post do PagSeguro
   *
   * Após configurar o objeto, você pode usar este método para mostrando assim o
   * formulário com todos os inputs necessários para enviar ao pagseguro.
   *
   * <code>
   * array (
   *   'print'       => false,        // Cancelará o evento de imprimir na tela, retornando o formulário
   *   'open_form'   => false,        // Não demonstra a tag <form target="pagseguro" ... >
   *   'close_form'  => false,        // Não demonstra a tag </form>
   *   'show_submit' => false,        // Não mostra o botão de submit (imagem ou um dos 5 do pagseguro)
   *   'img_button'  => 'imagem.jpg', // Usa a imagem (url) para formar o botão de submit
   *   'btn_submit'  => 1,            // Mostra um dos 5 botões do pagseguro no botão de submit
   * )
   * </code>
   *
   * @access public
   * @param array $args Array associativo contendo as configurações que você deseja alterar
   */
  function mostra ($args=array()) {
    $default = array (
      'print'       => true,
      'open_form'   => true,
      'close_form'  => true,
      'show_submit' => true,
      'img_button'  => false,
      'bnt_submit'  => false,
    );
    $args = $args+$default;
    $_input = '  <input type="hidden" name="%s" value="%s"  />';
    $_form = array();
    if ($args['open_form'])
      $_form[] = '<form target="pagseguro" action="https://pagseguro.uol.com.br/security/webpagamentos/webpagto.aspx" method="post">';
    foreach ($this->_config as $key=>$value)
      $_form[] = sprintf ($_input, $key, $value);
    foreach ($this->_cliente as $key=>$value)
      $_form[] = sprintf ($_input, "cliente_$key", $value);

    $assoc = array (
      'id' => 'item_id',
      'descricao' => 'item_descr',
      'quantidade' => 'item_quant',
    );
    $i=1;
    foreach ($this->_itens as $item) {
      foreach ($assoc as $key => $value) {
        $sufixo=($this->_config['tipo']=="CBR")?'':'_'.$i;
        $_form[] = sprintf ($_input, $value.$sufixo, $item[$key]);
        unset($item[$key]);
      }
      $_form[] = str_replace ('.', '', sprintf ('  <input type="hidden" name="%s" value="%.2f"  />', "item_valor$sufixo", $item['valor']));
      unset($item['valor']);

      foreach ($item as $key=>$value)
        $_form[] = sprintf ($_input, "item_{$key}{$sufixo}", $value);

      $i++;
    }
    if ($args['show_submit']) {
      if ($args['img_button']) {
        $_form[] = sprintf('  <input type="image" src="%s" name="submit" alt="Pague com o PagSeguro - &eacute; r&aacute;pido, gr&aacute;tis e seguro!"  />', $args['img_button']);
      } elseif ($args['btn_submit']) {
        switch ($args['btn_submit']) {
          case 1:  $btn = 'btnComprarBR.jpg'; break;
          case 2:  $btn = 'btnPagarBR.jpg'; break;
          case 3:  $btn = 'btnPagueComBR.jpg'; break;
          case 4:  $btn = 'btnComprar.jpg'; break;
          case 5:  $btn = 'btnPagar.jpg'; break;
          default: $btn = 'btnComprarBR.jpg';
        }
        $_form[] = sprintf ('  <input type="image" src="https://pagseguro.uol.com.br/Security/Imagens/%s"  name="submit" alt="Pague com o PagSeguro - &eacute; r&aacute;pido, gr&aacute;tis e seguro!" />', $btn);
      } else {
        $_form[] = '  <input type="submit" value="Pague com o PagSeguro"  />';
      }
    }
    if($args['close_form']) $_form[] = '</form>';
    $return = implode("\n", $_form);
    if ($args['print']) print ($return);
    return $return;
  }
}

?>
