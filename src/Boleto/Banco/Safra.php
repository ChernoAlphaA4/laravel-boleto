<?php

namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Safra extends AbstractBoleto implements BoletoContract
{
  /**
   * Código do banco
   *
   * @var string
   */
  protected $codigoBanco = self::COD_BANCO_SAFRA;
  /**
   * Variáveis adicionais.
   *
   * @var array
   */
  public $variaveis_adicionais = [
    'carteira_nome' => '',
  ];
  /**
   * Define as carteiras disponíveis para este banco
   *
   * @var array
   */
  protected $carteiras = ['01', '02'];
  /**
   * Espécie do documento, coódigo para remessa
   *
   * @var string
   */
  protected $especiesCodigo = [
    'DM' => '01',
    'NP' => '02',
    'NS' => '03',
    'ME' => '04',
    'REC' => '05',
    'CT' => '06',
    'CS' => '07',
    'DS' => '08',
    'LC' => '09',
    'ND' => '13',
    'CDA' => '15',
    'EC' => '16',
    'CPS' => '17',
  ];

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
   * Gera o Nosso Número.
   *
   * @return string
   * @throws \Exception
   */
  protected function gerarNossoNumero()
  {
    return $this->getNumero();
  }

  /**
   * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
   *
   * @return string
   */
  public function getNossoNumeroBoleto()
  {
    return $this->getNossoNumero();
  }

  /**
   * Codigo de barras do safra
   * @return mixed|string
   * @throws \Exception
   */
  public function getCodigoBarras()
  {
    if (!empty($this->campoCodigoBarras)) {
      return $this->campoCodigoBarras;
    }

    if (!$this->isValid($messages)) {
      throw new \Exception('Campos requeridos pelo banco, aparentam estar ausentes ' . $messages);
    }

    $codigo = Util::numberFormatGeral($this->getCodigoBanco(), 3)
      . $this->getMoeda()
      . Util::fatorVencimento($this->getDataVencimento())
      . Util::numberFormatGeral($this->getValor(), 10)
      . $this->getCampoLivre();

    $resto = Util::modulo11($codigo, 2, 9, 0);
    $dv = (in_array($resto, [0, 10, 1])) ? 1 : $resto;

    return $this->campoCodigoBarras = substr($codigo, 0, 4) . $dv . substr($codigo, 4);
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

    $campoLivre = Util::numberFormatGeral(7, 1);
    $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 5);
    $campoLivre .= Util::numberFormatGeral($this->getConta(), 9);
    $campoLivre .= Util::numberFormatGeral($this->getNossoNumero(), 9);
    $campoLivre .= '2';

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
      'agenciaDv' => null,
      'codigoCliente' => null,
      'carteira' => null,
      'nossoNumero' => substr($campoLivre, 16, 25),
      'nossoNumeroDv' => null,
      'nossoNumeroFull' => substr($campoLivre, 16, 25),
      'agencia' => substr($campoLivre, 2, 6),
      'contaCorrente' => substr($campoLivre, 7, 15),
      'contaCorrenteDv' => null,
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
