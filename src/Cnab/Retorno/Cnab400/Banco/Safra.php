<?php

namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\Banco;


use Eduardokum\LaravelBoleto\Util;
use Eduardokum\LaravelBoleto\Contracts\Cnab\RetornoCnab400;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Cnab400\AbstractRetorno;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;

class Safra extends AbstractRetorno implements RetornoCnab400
{

  /**
   * Código do banco
   *
   * @var string
   */
  protected $codigoBanco = BoletoContract::COD_BANCO_SAFRA;

  /**
   * Array com as ocorrencias do banco;
   *
   * @var array
   */
  private $ocorrencias = [
    '02' => 'Entrada confirmada',
    '03' => 'Entrada rejeitada',
    '04' => 'TRANSFERÊNCIA DE CARTEIRA (ENTRADA)',
    '05' => 'TRANSFERÊNCIA DE CARTEIRA (BAIXA)',
    '06' => 'Liquidação normal',
    '09' => 'Baixa simples',
    '10' => 'Baixa por ter sido liquidado',
    '11' => 'Em ser (só no retorno mensal)',
    '12' => 'Abatimento concedido',
    '13' => 'Abatimento cancelado',
    '14' => 'Vencimento alterado',
    '15' => 'LIQUIDAÇÃO EM CARTÓRIO',
    '19' => 'Confirma recebimento de instrução de protesto',
    '20' => 'Confirma recebimento de instrução de sustação de protesto /tarifa',
    '21' => 'TRANSFERÊNCIA DE BENEFICIÁRIO',
    '23' => 'Título enviado a cartório/tarifa',
    '40' => 'BAIXA DE TÍTULO PROTESTADO',
    '41' => 'LIQUIDAÇÃO DE TÍTULO BAIXADO',
    '42' => 'TÍTULO RETIRADO DO CARTÓRIO',
    '43' => 'DESPESA DE CARTÓRIO',
    '44' => 'ACEITE DO TÍTULO DDA PELO PAGADOR',
    '45' => 'NÃO ACEITE DO TÍTULO DDA PELO PAGADOR',
    '51' => 'VALOR DO TÍTULO ALTERADO',
    '52' => 'ACERTO DE DATA DE EMISSAO',
    '53' => 'ACERTO DE COD ESPECIE DOCTO',
    '54' => 'ALTERACAO DE SEU NUMERO',
    '56' => 'INSTRUÇÃO NEGATIVAÇÃO ACEITA',
    '57' => 'INSTRUÇÃO BAIXA DE NEGATIVAÇÃO ACEITA',
    '58' => 'INSTRUÇÃO NÃO NEGATIVAR ACEITA',
  ];

  private $rejeicoes = [
    '001' => 'MOEDA INVÁLIDA',
    '002' => 'MOEDA INVÁLIDA PARA CARTEIRA',
    '007' => 'CEP NÃO CORRESPONDE UF',
    '008' => 'VALOR JUROS AO DIA MAIOR QUE 5% DO VALOR DO TÍTULO',
    '009' => 'USO EXCLUSIVO NÃO NUMÉRICO PARA COBRANCA EXPRESS',
    '010' => 'IMPOSSIBILIDADE DE REGISTRO => CONTATE O SEU GERENTE',
    '011' => 'NOSSO NÚMERO FORA DA FAIXA',
    '012' => 'CEP DE CIDADE INEXISTENTE',
    '013' => 'CEP FORA DE FAIXA DA CIDADE',
    '014' => 'UF INVÁLIDO PARA CEP DA CIDADE',
    '015' => 'CEP ZERADO',
    '016' => 'CEP NÃO CONSTA NA TABELA SAFRA',
    '017' => 'CEP NÃO CONSTA TABELA BANCO CORRESPONDENTE',
    '019' => 'PROTESTO IMPRATICÁVEL',
    '020' => 'PRIMEIRA INSTRUÇÃO DE COBRANÇA INVALIDA',
    '021' => 'SEGUNDA INSTRUÇÃO DE COBRANÇA INVÁLIDA', '023' => 'TERCEIRA INSTRUÇÃO DE COBRANÇA INVÁLIDA',
    '026' => 'CÓDIGO DE OPERAÇÃO/OCORRÊNCIA INVÁLIDO',
    '027' => 'OPERAÇÃO INVÁLIDA PARA O CLIENTE',
    '028' => 'NOSSO NÚMERO NÃO NUMÉRICO OU ZERADO',
    '029' => 'NOSSO NÚMERO COM DÍGITO DE CONTROLE ERRADO/INCONSISTENTE',
    '030' => 'VALOR DO ABATIMENTO NÃO NUMÉRICO OU ZERADO',
    '031' => 'SEU NÚMERO EM BRANCO',
    '032' => 'CÓDIGO DA CARTEIRA INVÁLIDO',
    '036' => 'DATA DE EMISSÃO INVÁLIDA',
    '037' => 'DATA DE VENCIMENTO INVÁLIDA',
    '038' => 'DEPOSITÁRIA INVÁLIDA',
    '039' => 'DEPOSITÁRIA INVÁLIDA PARA O CLIENTE',
    '040' => 'DEPOSITÁRIA NÃO CADASTRADA NO BANCO',
    '041' => 'CÓDIGO DE ACEITE INVÁLIDO',
    '042' => 'ESPÉCIE DE TÍTULO INVÁLIDO',
    '043' => 'INSTRUÇÃO DE COBRANÇA INVÁLIDA',
    '044' => 'VALOR DO TÍTULO NÃO NUMÉRICO OU ZERADO',
    '046' => 'VALOR DE JUROS NÃO NUMÉRICO OU ZERADO',
    '047' => 'DATA LIMITE PARA DESCONTO INVÁLIDA',
    '048' => 'VALOR DO DESCONTO INVÁLIDO',
    '049' => 'VALOR IOF. NÃO NUMÉRICO OU ZERADO (SEGUROS)',
    '051' => 'CÓDIGO DE INSCRIÇÃO DO SACADO INVÁLIDO',
    '053' => 'NÚMERO DE INSCRIÇÃO DO SACADO NÃO NÚMERICO OU DÍGITO ERRADO',
    '054' => 'NOME DO SACADO EM BRANCO',
    '055' => 'ENDEREÇO DO SACADO EM BRANCO',
    '056' => 'CLIENTE NÃO CADASTRADO',
    '058' => 'PROCESSO DE CARTÓRIO INVÁLIDO',
    '059' => 'ESTADO DO SACADO INVÁLIDO',
    '060' => 'CEP/ENDEREÇO DIVERGEM DO CORREIO',
    '061' => 'INSTRUÇÃO AGENDADA PARA AGÊNCIA',
    '062' => 'OPERAÇÃO INVÁLIDA PARA A CARTEIRA',
    '064' => 'TÍTULO INEXISTENTE (TFC)',
    '065' => 'OPERAÇÃO / TITULO JÁ EXISTENTE',
    '066' => 'TÍTULO JÁ EXISTE (TFC)',
    '067' => 'DATA DE VENCIMENTO INVÁLIDA PARA PROTESTO',
    '068' => 'CEP DO SACADO NÃO CONSTA NA TABELA',
    '069' => 'PRAÇA NÃO ATENDIDA PELO SERVIÇO CARTÓRIO',
    '070' => 'AGÊNCIA INVÁLIDA',
    '072' => 'TÍTULO JÁ EXISTE (COB)',
    '074' => 'TÍTULO FORA DE SEQÜÊNCIA',
    '078' => 'TÍTULO INEXISTENTE (COB)',
    '079' => 'OPERAÇÃO NÃO CONCLUÍDA',
    '080' => 'TÍTULO JÁ BAIXADO',
    '083' => 'PRORROGAÇÃO/ALTERAÇÃO DE VENCIMENTO INVÁLIDA',
    '085' => 'OPERAÇÃO INVÁLIDA PARA A CARTEIRA',
    '086' => 'ABATIMENTO MAIOR QUE VALOR DO TÍTULO',
    '088' => 'TÍTULO RECUSADO COMO GARANTIA (SACADO/NOVO/EXCLUSIVO/ALÇADACOMITÊ)',
    '089' => 'ALTERAÇÃO DE DATA DE PROTESTO INVÁLIDA',
    '094' => 'ENTRADA TÍTULO COBRANÇA DIRETA INVÁLIDA',
    '095' => 'BAIXA TÍTULO COBRANÇA DIRETA INVÁLIDA',
    '096' => 'VALOR DO TÍTULO INVÁLIDO',
    '098' => 'PCB DO TFC DIVERGEM DA PCB DO COB',
    '100' => 'INSTRUÇÃO NÃO PERMITIDA - TÍT COM PROTESTO (SE TÍTULO PROTESTADO NÃO PODE NEGATIVAR)',
    '101' => 'INSTRUÇÃO INCOMPATÍVEL - NÃO EXISTE INSTRUÇÃO DE NEGATIVAR PARA O TÍTULO',
    '102' => 'INSTRUÇÃO NÃO PERMITIDA - PRAZO INVÁLIDO PARA NEGATIVAÇÃO (MÍNIMO 2 DIAS CORRIDOS APÓS O VENCIMENTO)',
    '103' => 'INSTRUÇÃO NÃO PERMITIDA - TÍT INEXISTENTE'];

  /**
   * Roda antes dos metodos de processar
   */
  protected function init()
  {
    $this->totais = [
      'liquidados' => 0,
      'entradas' => 0,
      'baixados' => 0,
      'protestados' => 0,
      'erros' => 0,
      'alterados' => 0,
    ];
  }

  /**
   * @param array $header
   *
   * @return boolean
   * @throws \Exception
   */
  protected function processarHeader(array $header)
  {
    $this->getHeader()
      ->setOperacaoCodigo($this->rem(2, 2, $header))
      ->setOperacao($this->rem(3, 9, $header))
      ->setServicoCodigo($this->rem(10, 11, $header))
      ->setServico($this->rem(12, 19, $header))
      ->setAgencia($this->rem(27, 31, $header))
      ->setConta($this->rem(32, 40, $header))
      ->setData($this->rem(95, 100, $header))
      ->setNumeroSequencialArquivo($this->rem(392, 394, $header));

    return true;
  }

  /**
   * @param array $detalhe
   *
   * @return boolean
   * @throws \Exception
   */
  protected function processarDetalhe(array $detalhe)
  {
    $d = $this->detalheAtual();

    $d
      ->setNossoNumero($this->rem(63, 71, $detalhe))//Pegando menos um caracter para remoção do digito verificador do nosso numero
      ->setCarteira($this->rem(108, 108, $detalhe))
      ->setOcorrencia($this->rem(109, 110, $detalhe))
      ->setDataOcorrencia($this->rem(111, 116, $detalhe))
      ->setNumeroDocumento($this->rem(117, 126, $detalhe))
      ->setNumeroControle($this->rem(38, 62, $detalhe))
      ->setOcorrenciaDescricao(array_get($this->ocorrencias, $d->getOcorrencia(), 'Desconhecida'))
      ->setDataVencimento($this->rem(147, 152, $detalhe))
      ->setDataCredito($this->rem(296, 301, $detalhe))
      ->setValor(Util::nFloat($this->rem(153, 165, $detalhe) / 100, 2, false))
      ->setValorTarifa(Util::nFloat($this->rem(176, 188, $detalhe) / 100, 2, false))
      ->setValorIOF(Util::nFloat($this->rem(215, 227, $detalhe) / 100, 2, false))
      ->setValorAbatimento(Util::nFloat($this->rem(228, 240, $detalhe) / 100, 2, false))
      ->setValorDesconto(Util::nFloat($this->rem(241, 253, $detalhe) / 100, 2, false))
      ->setValorRecebido(Util::nFloat($this->rem(254, 266, $detalhe) / 100, 2, false))
      ->setValorMora(Util::nFloat($this->rem(267, 279, $detalhe) / 100, 2, false))
      ->setValorMulta(Util::nFloat($this->rem(280, 292, $detalhe) / 100, 2, false));

    if ($d->hasOcorrencia('06', '07', '08', '10', '59')) {
      $this->totais['liquidados']++;
    } elseif ($d->hasOcorrencia('02', '64', '71', '73')) {
      $this->totais['entradas']++;
    } elseif ($d->hasOcorrencia('05', '09', '47', '72')) {
      $this->totais['baixados']++;
    } elseif ($d->hasOcorrencia('32')) {
      $this->totais['protestados']++;
    } elseif ($d->hasOcorrencia('14')) {
      $this->totais['alterados']++;
    } elseif ($d->hasOcorrencia('03')) {
      $this->totais['erros']++;
      $d->setError($this->rejeicoes[(string)$this->rem(105, 107, $detalhe)]);
    }

    return true;
  }

  /**
   * @param array $trailer
   *
   * @return boolean
   * @throws \Exception
   */
  protected function processarTrailer(array $trailer)
  {

    $qty_total = ($this->rem(18, 25, $trailer)) == 0 ? ($this->rem(98, 105, $trailer)) : ($this->rem(18, 25, $trailer));
    $valor_total = ($this->rem(26, 39, $trailer) / 100) == 0 ? ($this->rem(106, 119, $trailer) / 100) : ($this->rem(26, 39, $trailer) / 100);
    $this->getTrailer()
      ->setQuantidadeTitulos((int)$qty_total)
      ->setValorTitulos((float)Util::nFloat($valor_total, 2, false))
      ->setQuantidadeErros((int)$this->totais['erros'])
      ->setQuantidadeEntradas((int)$this->totais['entradas'])
      ->setQuantidadeLiquidados((int)$this->totais['liquidados'])
      ->setQuantidadeBaixados((int)$this->totais['baixados'])
      ->setQuantidadeAlterados((int)$this->totais['alterados'])
      ->setNumeroSequencialArquivo($this->rem(392, 394, $trailer));

    return true;
  }
}