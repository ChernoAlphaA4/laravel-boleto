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
    'RC' => '05',
    'DS' => '09',
  ];


  /**
   * Retorna o campo Agência/Beneficiário do boleto
   *
   * @return string
   */
  public function getAgenciaCodigoBeneficiario()
  {
    $agencia = $this->getAgenciaDv() !== null ? $this->getAgencia() . $this->getAgenciaDv() : $this->getAgencia();
    $conta = $this->getContaDv() !== null ? $this->getConta() . $this->getContaDv() : $this->getConta();

    return $agencia . ' / ' . $conta;
  }

  /**
   * Retorna o campo aceite
   *
   * @return string
   */
  public function getAceite()
  {
    return is_numeric($this->aceite) ? ($this->aceite ? 'Sim' : 'Não') : $this->aceite;
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
   * Gera o Nosso Número.
   *
   * @return string
   * @throws \Exception
   */
  protected function gerarNossoNumero()
  {
    return Util::numberFormatGeral($this->getNumero(), 9);
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

    $codigoCalcDAC = Util::numberFormatGeral($this->getCodigoBanco(), 3)
      . $this->getMoeda()
      . Util::fatorVencimento($this->getDataVencimento())
      . Util::numberFormatGeral($this->getValor(), 10)
      . Util::numberFormatGeral($this->getAgencia() . $this->getAgenciaDv(), 5)
      . Util::numberFormatGeral($this->getConta() . $this->getContaDv(), 9)
      . Util::numberFormatGeral($this->getNossoNumero(), 9)
      . '2';

    $resto = (int)Util::modulo11($codigoCalcDAC, 2, 9, 0);
    $dac = (in_array($resto, [0, 10, 1])) ? 1 : abs(11 - $resto);

    return $this->campoCodigoBarras = substr($codigo, 0, 4) . $dac . substr($codigo, 4);
  }

  /**
   * Retorna a linha digitável do boleto
   *
   * @return string
   * @throws \Exception
   */
  public function getLinhaDigitavel()
  {
    if (!empty($this->campoLinhaDigitavel)) {
      return $this->campoLinhaDigitavel;
    }

    $codigo = $this->getCodigoBarras();


    $s1 = substr($codigo, 0, 4) . substr($codigo, 19, 1) . substr($codigo, 21, 4);

    $s1 = $s1 . Util::modulo10($s1);
    $s1 = substr_replace($s1, '.', 5, 0);
    $s2 = substr($codigo, 24, 10);

    $s2 = $s2 . Util::modulo10($s2);
    $s2 = substr_replace($s2, '.', 5, 0);

    $s3 = substr($codigo, 34, 10);
    $s3 = $s3 . Util::modulo10($s3);
    $s3 = substr_replace($s3, '.', 5, 0);

    $s4 = substr($codigo, 4, 1);

    $s5 = substr($codigo, 5, 14);

    return $this->campoLinhaDigitavel = sprintf('%s %s %s %s %s', $s1, $s2, $s3, $s4, $s5);
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
    $campoLivre .= Util::numberFormatGeral($this->getAgencia() . $this->getAgenciaDv(), 5);
    $campoLivre .= Util::numberFormatGeral($this->getConta() . $this->getContaDv(), 9);
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
