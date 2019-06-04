<?php
/**
 * Created by PhpStorm.
 * User: simetriatecnologia
 * Date: 28/05/2019
 * Time: 16:06
 */

namespace Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\Banco;

use Eduardokum\LaravelBoleto\Cnab\Remessa\Cnab400\AbstractRemessa;
use Eduardokum\LaravelBoleto\Contracts\Cnab\Remessa as RemessaContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Safra extends AbstractRemessa implements RemessaContract
{
  /**
   * Valor total dos titulos.
   *
   * @var float
   */
  private $total = 0;

  const
    REGISTRO = '01',
    BAIXA = '02';

  const
    ESPECIE_DUPLICATA = '01',
    ESPECIE_NOTA_PROMISSORIA = '02',
    ESPECIE_NOTA_SEGURO = '03',
    ESPECIE_RECIBO = '05',
    ESPECIE_DUPLICATA_SERVICO = '09';

  const INSTRUCAO_SEM = '00'
  , INSTRUCAO_NAO_RECEBER_PRINCIPAL_SEM_JUROS_DE_MORA = '01'
  , INSTRUCAO_DEVOLVER_APOS_15_DIAS = '02'
  , INSTRUCAO_DEVOLVER_APOS_30_DIAS = '03'
  , INSTRUCAO_NAO_PROTESTAR = '07'
  , INSTRUCAO_NAO_COBRAR_JUROS_DE_MORA = '08'
  , INSTRUCAO_MULTA = '16';

  /**
   * Define as carteiras disponíveis para este banco
   *
   * @var array
   */
  protected $carteiras = ['01', '02'];
  /**
   * Código do banco
   *
   * @var string
   */
  protected $codigoBanco = BoletoContract::COD_BANCO_SAFRA;

  /**
   * Função para gerar o cabeçalho do arquivo.
   *
   * @return mixed
   * @throws \Exception
   */
  protected function header()
  {
    $this->iniciaHeader();

    $this->add(1, 1, Util::formatCnab('9', '0', 1));
    $this->add(2, 2, Util::formatCnab('9', '1', 1));
    $this->add(3, 9, Util::formatCnab('X', 'REMESSA', 7));
    $this->add(10, 11, '01');
    $this->add(12, 19, Util::formatCnab('X', 'Cobrança', 8));
    $this->add(20, 26, '');
    $this->add(27, 40, Util::formatCnab('9L', $this->getAgencia() . $this->getAgenciaDv(), 5) . Util::formatCnab('9L', $this->getConta() . $this->getContaDv(), 9));
    $this->add(41, 46, '');
    $this->add(47, 76, Util::formatCnab('X', $this->getBeneficiario()->getNome(), 30));
    $this->add(77, 79, Util::formatCnab('9', $this->getCodigoBanco(), 3));
    $this->add(80, 90, Util::formatCnab('X', 'BANCO SAFRA', 11));
    $this->add(91, 94, '');
    $this->add(95, 100, $this->getDataRemessa('dmy'));
    $this->add(101, 391, '');
    $this->add(392, 394, Util::formatCnab('9', $this->getIdremessa(), 3));
    $this->add(395, 400, Util::formatCnab('9', '000001', 6));

    return $this;
  }

  /**
   * Função para adicionar detalhe ao arquivo.
   *
   * @param BoletoContract $boleto
   * @return mixed
   * @throws \Exception
   */
  public function addBoleto(BoletoContract $boleto)
  {
    $this->boletos[] = $boleto;
    $this->iniciaDetalhe();

    $this->total += $boleto->getValor();

    $this->add(1, 1, '1');
    $this->add(2, 3, Util::formatCnab('9', '02', 2));
    $this->add(4, 17, Util::formatCnab('9L', $this->getBeneficiario()->getDocumento(), 14));
    $this->add(18, 31, Util::formatCnab('9L', $this->getAgencia() . $this->getAgenciaDv(), 5) . Util::formatCnab('9L', $this->getConta() . $this->getContaDv(), 9));
    $this->add(32, 37, Util::formatCnab('X', '', 6));
    $this->add(38, 62, Util::formatCnab('X', '', 25));
    $this->add(63, 71, Util::formatCnab('9', $boleto->getNossoNumero(), 9));
    $this->add(72, 101, Util::formatCnab('X', '', 30));
    $this->add(102, 102, Util::formatCnab('9', '0', 1));
    $this->add(103, 104, Util::formatCnab('9', '00', 2));
    $this->add(105, 105, Util::formatCnab('9', '', 1));
    $this->add(106, 107, Util::formatCnab('9', '', 1));
    $this->add(108, 108, Util::formatCnab('9', '1', 1));
    $this->add(109, 110, Util::formatCnab('9', self::REGISTRO, 2));
    if ($boleto->getStatus() == $boleto::STATUS_BAIXA) {
      $this->add(109, 110, self::BAIXA); // BAIXA
    }
    $this->add(111, 120, Util::formatCnab('X', $boleto->getNumeroDocumento(), 10));
    $this->add(121, 126, Util::formatCnab('9', $boleto->getDataVencimento()->format('dmy'), 6));
    $this->add(127, 139, Util::formatCnab('9', $boleto->getValor(), 13, 2));
    $this->add(140, 142, Util::formatCnab('9', $this->getCodigoBanco(), 3));
    $this->add(143, 147, Util::formatCnab('X', '00000', 5));
    $this->add(148, 149, Util::formatCnab('9', $boleto->getEspecieDocCodigo(), 2));
    $this->add(150, 150, 'N');
    $this->add(151, 156, $boleto->getDataDocumento()->format('dmy'));
    $this->add(157, 158, self::INSTRUCAO_DEVOLVER_APOS_30_DIAS);
    $this->add(159, 160, self::INSTRUCAO_SEM);
    if ($boleto->getDiasProtesto() > 0) {
      $this->add(106, 107, Util::formatCnab('9', $boleto->getDiasProtesto(), 2));
      $this->add(159, 160, Util::formatCnab('9', 10, 2));
    }
    $this->add(161, 173, Util::formatCnab('9', $boleto->getMoraDia(), 13, 2));
    $this->add(174, 179, Util::formatCnab('9', $boleto->getDesconto() > 0 ? $boleto->getDataDesconto()->format('dmy') : '000000', 6));
    $this->add(180, 192, Util::formatCnab('9', $boleto->getDesconto(), 13, 2));
    $this->add(193, 205, Util::formatCnab('9', 0, 13, 2));
    $this->add(206, 218, Util::formatCnab('9', 0, 13, 2));
    $this->add(219, 220, strlen(Util::onlyNumbers($boleto->getPagador()->getDocumento())) == 14 ? '02' : '01');
    $this->add(221, 234, Util::formatCnab('9L', Util::onlyNumbers($boleto->getPagador()->getDocumento()), 14));
    $this->add(235, 274, Util::formatCnab('X', $boleto->getPagador()->getNome(), 40));
    $this->add(275, 314, Util::formatCnab('X', $boleto->getPagador()->getEndereco(), 40));
    $this->add(315, 324, Util::formatCnab('X', $boleto->getPagador()->getBairro(), 10));
    $this->add(325, 326, Util::formatCnab('X', '', 2));
    $this->add(327, 334, Util::formatCnab('9L', Util::onlyNumbers($boleto->getPagador()->getCep()), 8));
    $this->add(335, 349, Util::formatCnab('X', $boleto->getPagador()->getCidade(), 15));
    $this->add(350, 351, Util::formatCnab('X', $boleto->getPagador()->getUf(), 2));
    $this->add(352, 381, Util::formatCnab('X', $boleto->getSacadorAvalista() ? $boleto->getSacadorAvalista()->getNome() : '', 30));
    $this->add(382, 388, '');
    $this->add(389, 391, Util::formatCnab('9', $this->getCodigoBanco(), 3));
    $this->add(392, 394, Util::formatCnab('9', '060', 3));
    $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 1, 6));

    return $this;
  }

  /**
   * Função que gera o trailer (footer) do arquivo.
   *
   * @return mixed
   * @throws \Exception
   */
  protected function trailer()
  {
    $this->iniciaTrailer();

    $this->add(1, 1, '9');
    $this->add(2, 368, '');
    $this->add(369, 376, Util::formatCnab('9', $this->getCount() - 2, 8));
    $this->add(377, 391, Util::formatCnab('9', $this->total, 13, 2));
    $this->add(392, 394, '060');
    $this->add(395, 400, Util::formatCnab('9', $this->iRegistros + 2, 6));

    return $this;
  }
}