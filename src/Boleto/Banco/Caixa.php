<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Caixa extends AbstractBoleto implements BoletoContract
{
  public function __construct(array $params = [])
  {
    parent::__construct($params);
    $this->setCamposObrigatorios('numero', 'agencia', 'carteira', 'codigoCliente');
  }

  /**
   * Código do banco
   *
   * @var string
   */
  protected $codigoBanco = self::COD_BANCO_CEF;
  /**
   * Define as carteiras disponíveis para este banco
   *
   * @var array
   */
  protected $carteiras = ['RG', 'SR'];
  /**
   * Espécie do documento, coódigo para remessa
   *
   * @var string
   */
  protected $especiesCodigo = [
    'DM' => '01',
    'NP' => '02',
    'DS' => '03',
    'NS' => '05',
    'LC' => '06',
  ];

  /**
   * Codigo do desconto 3
   */
  protected $codigoDesconto3 = 0;

  /**
   * Valor/Percentual do desconto 3
   */
  protected $desconto3;

  /**
   * Data do desconto 3
   */
  protected $dataDesconto3;

  /**
   * Codigo do cliente junto ao banco.
   * @var string
   */
  protected $codigoCliente;

  /**
   * Define o codigo do desconto para o segmento R
   *
   * Código adotado pela FEBRABAN para identificação do tipo de desconto que deverá ser concedido.
   * Ao se optar por valor, o desconto deve ser expresso em valor. Idem ao se optar por percentual, o desconto deve ser expresso em percentual.
   * ‘0’ = Sem Desconto
   * ‘1’ = Valor Fixo até a data informada
   * ‘2’ = Percentual até a data informada
   * Para os códigos ‘1’ e ‘2’ será obrigatório a informação da Data.
   * @param $d1 - Valor
   * @return mixed
   */
  public function setCodigoDesconto3($d1)
  {
    $this->codigoDesconto3 = $d1;
    return $this;
  }

  /**
   * Retorna o codigo do desconto para o segmento R
   * @return mixed
   */
  public function getCodigoDesconto3()
  {
    return $this->codigoDesconto;
  }

  /**
   * Retorna a data de limite de desconto
   *
   * @return Carbon
   */
  public function getDataDesconto3()
  {
    return $this->dataDesconto;
  }

  /**
   * Define a data de limite de desconto
   *
   * @param  Carbon $dataDesconto3
   *
   * @return AbstractBoleto
   */
  public function setDataDesconto3(Carbon $dataDesconto3)
  {
    $this->dataDesconto3 = $dataDesconto3;

    return $this;
  }

  /**
   * Seta o codigo do cliente.
   *
   * @param mixed $codigoCliente
   *
   * @return $this
   */
  public function setCodigoCliente($codigoCliente)
  {
    $this->codigoCliente = $codigoCliente;

    return $this;
  }

  /**
   * Retorna o codigo do cliente.
   *
   * @return string
   */
  public function getCodigoCliente()
  {
    return $this->codigoCliente;
  }

  /**
   * Retorna o codigo do cliente como se fosse a conta
   * ja que a caixa não faz uso da conta para nada.
   *
   * @return string
   */
  public function getConta()
  {
    return $this->getCodigoCliente();
  }

  /**
   * @return mixed
   */
  public function getDesconto3()
  {
    return $this->desconto3;
  }

  /**
   * @param mixed $desconto3
   * @return $this
   */
  public function setDesconto3($desconto3)
  {
    $this->desconto3 = $desconto3;
    return $this;
  }

  /**
   * Gera o Nosso Número.
   *
   * @throws \Exception
   * @return string
   */
  protected function gerarNossoNumero()
  {
    $numero_boleto = Util::numberFormatGeral($this->getNumero(), 15);
    $composicao = '1';
    if ($this->getCarteira() == 'SR') {
      $composicao = '2';
    }

    $carteira = $composicao . '4';
    // As 15 próximas posições no nosso número são a critério do beneficiário, utilizando o sequencial
    // Depois, calcula-se o código verificador por módulo 11
    $numero = $carteira . Util::numberFormatGeral($numero_boleto, 15);
    return $numero;
  }

  /**
   * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
   *
   * @return string
   */
  public function getNossoNumeroBoleto()
  {
    return $this->getNossoNumero() . '-' . CalculoDV::cefNossoNumero($this->getNossoNumero());
  }

  /**
   * Seta dias para baixa automática
   *
   * @param int $baixaAutomatica
   *
   * @return $this
   * @throws \Exception
   */
  public function setDiasBaixaAutomatica($baixaAutomatica)
  {
    if ($this->getDiasProtesto() > 0) {
      throw new \Exception('Você deve usar dias de protesto ou dias de baixa, nunca os 2');
    }
    $baixaAutomatica = (int)$baixaAutomatica;
    $this->diasBaixaAutomatica = $baixaAutomatica > 0 ? $baixaAutomatica : 0;
    return $this;
  }

  /**
   * Método para gerar o código da posição de 20 a 44
   *
   * @return string
   * @throws \Exception
   */
  protected function getCampoLivre()
  {
    if ($this->campoLivre) {
      return $this->campoLivre;
    }

    $nossoNumero = Util::numberFormatGeral($this->gerarNossoNumero(), 17);
    $beneficiario = Util::numberFormatGeral($this->getCodigoCliente(), (int)$this->getCodigoCliente() > 1100000 ? 7 : 6);

    $campoLivre = (int)$beneficiario > 1100000 ? $beneficiario : ($beneficiario . Util::modulo11($beneficiario));
    $campoLivre .= substr($nossoNumero, 2, 3);
    $campoLivre .= substr($nossoNumero, 0, 1);
    $campoLivre .= substr($nossoNumero, 5, 3);
    $campoLivre .= substr($nossoNumero, 1, 1);
    $campoLivre .= substr($nossoNumero, 8, 9);
    $campoLivre .= Util::modulo11($campoLivre);
    return $this->campoLivre = $campoLivre;
  }

  /**
   * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
   *
   * @param $campoLivre
   *
   * @return array
   */
  public static function parseCampoLivre($campoLivre)
  {
    return [
      'convenio' => null,
      'agencia' => null,
      'agenciaDv' => null,
      'contaCorrente' => null,
      'contaCorrenteDv' => null,
      'codigoCliente' => substr($campoLivre, 0, 6),
      'carteira' => substr($campoLivre, 10, 1),
      'nossoNumero' => substr($campoLivre, 7, 3) . substr($campoLivre, 11, 3) . substr($campoLivre, 15, 8),
      'nossoNumeroDv' => substr($campoLivre, 23, 1),
      'nossoNumeroFull' => substr($campoLivre, 7, 3) . substr($campoLivre, 11, 3) . substr($campoLivre, 15, 8),
    ];
  }

  /**
   * @return mixed
   */
  public function alterarDataDeVencimento()
  {
    // TODO: Implement alterarDataDeVencimento() method.
  }

  /**
   * @param $instrucao
   *
   * @return mixed
   */
  public function comandarInstrucao($instrucao)
  {
    // TODO: Implement comandarInstrucao() method.
  }

  /**
   * @return mixed
   */
  public function getComando()
  {
    // TODO: Implement getComando() method.
  }
}
