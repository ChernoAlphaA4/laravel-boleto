<?php

namespace Eduardokum\LaravelBoleto\Boleto\Render;

use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Render\Pdf as PdfContract;
use Eduardokum\LaravelBoleto\Util;

class PdfCarne extends AbstractPdf implements PdfContract
{
  const OUTPUT_STANDARD = 'I';
  const OUTPUT_DOWNLOAD = 'D';
  const OUTPUT_SAVE = 'F';
  const OUTPUT_STRING = 'S';
  private $PadraoFont = 'Arial';
  /**
   * @var BoletoContract[]
   */
  private $boleto = [];
  /**
   * @var bool
   */
  private $print = false;
  private $desc = 3; // tamanho célula descrição
  private $cell = 4; // tamanho célula dado
  private $fdes = 6; // tamanho fonte descrição
  private $fcel = 8; // tamanho fonte célula
  private $small = 0.2; // tamanho barra fina
  private $totalBoletos = 0;

  public function __construct()
  {
    parent::__construct('P', 'mm', 'A4');
    $this->SetAutoPageBreak(false);
    $this->SetLeftMargin(20);
    $this->SetTopMargin(15);
    $this->SetRightMargin(20);
    $this->SetLineWidth($this->small);
  }

  /**
   * @param integer $i
   *
   * @return $this
   */
  protected function body($i)
  {
    $nomePagador = $this->boleto[$i]->getPagador()->getNome();
    $documentoPagador = $this->boleto[$i]->getPagador()->getDocumento();
    $nomeBeneficiario = $this->boleto[$i]->getBeneficiario()->getNome();
    $documentoBeneficiario = $this->boleto[$i]->getBeneficiario()->getDocumento();
    $nomeDocumentoPagador = substr($nomePagador, 0, 50) . ' - CPF/CNPJ: ' . $documentoPagador;
    $nomeDocumentoBeneficiario = substr($nomeBeneficiario, 0, 45) . ' - CNPJ: ' . $documentoBeneficiario;
    $enderecoPagador = substr($this->boleto[$i]->getPagador()->getEndereco(), 0, 50);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, $this->desc, $this->_('Nº Documento'), 'TLR');
    $this->Cell(3, $this->desc, $this->_('| '));
    $this->Image($this->boleto[$i]->getLogoBanco(), 43, ($this->GetY() - 2), 28);
    $this->Cell(29, 6, '', 'B');
    $this->SetFont($this->PadraoFont, 'B', 13);
    $this->Cell(15, 6, $this->boleto[$i]->getCodigoBancoComDv(), 'LBR', 0, 'C');
    $this->SetFont($this->PadraoFont, 'B', 10);
    $this->Cell(103, 6, $this->boleto[$i]->getLinhaDigitavel(), 'B', 1, 'R');
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_($this->boleto[$i]->getNumeroDocumento()), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc, $this->_('Local de Pagamento'), 'TLR');
    $this->Cell(30, $this->desc, $this->_('Vencimento'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('Vencimento'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('|  '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    $this->Cell(117, $this->cell, $this->_($this->boleto[$i]->getLocalPagamento()), 'LR');
    $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getDataVencimento()->format('d/m/Y')), 'R', 1, 'R');
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_($this->boleto[$i]->getDataVencimento()->format('d/m/Y')), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc, $this->_('Beneficiário'), 'TLR');
    $this->Cell(30, $this->desc, $this->_('Agência/Código Beneficiário'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('Ag./Cod. Cedente'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('| '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    $this->Cell(117, $this->cell, $this->_($nomeDocumentoBeneficiario), 'LR');
    $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getAgenciaCodigoBeneficiario()), 'R', 1, 'R');
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_($this->boleto[$i]->getAgenciaCodigoBeneficiario()), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(29, $this->desc, $this->_('Data do Documento'), 'TLR');
    $this->Cell(24, $this->desc, $this->_('Número do Documento'), 'TR');
    $this->Cell(14, $this->desc, $this->_('Espécie Doc.'), 'TR');
    $this->Cell(10, $this->desc, $this->_('Aceite'), 'TR');
    $this->Cell(40, $this->desc, $this->_('Data Processamento'), 'TR');
    $this->Cell(30, $this->desc, $this->_('Nosso Número'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('Nosso Número'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('| '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    $this->Cell(29, $this->cell, $this->_($this->boleto[$i]->getDataDocumento()->format('d/m/Y')), 'LR');
    $this->Cell(24, $this->cell, $this->_($this->boleto[$i]->getNumeroDocumento()), 'R');
    $this->Cell(14, $this->cell, $this->_($this->boleto[$i]->getEspecieDoc()), 'R');
    $this->Cell(10, $this->cell, $this->_($this->boleto[$i]->getAceite()), 'R');
    $this->Cell(40, $this->cell, $this->_($this->boleto[$i]->getDataProcessamento()->format('d/m/Y')), 'R');
    $this->Cell(30, $this->cell, $this->_($this->boleto[$i]->getNossoNumeroBoleto()), 'R', 1, 'R');
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_($this->boleto[$i]->getNossoNumeroBoleto()), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    if (isset($this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) && $this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) {
      $this->Cell(55, $this->desc, $this->_('Carteira'), 'TLR');
    } else {
      $cip = isset($this->boleto[$i]->variaveis_adicionais['mostra_cip']) && $this->boleto[$i]->variaveis_adicionais['mostra_cip'];
      $this->Cell(($cip ? 23 : 29), $this->desc, $this->_('Uso do Banco'), 'TLR');
      if ($cip) {
        $this->Cell(7, $this->desc, $this->_('CIP'), 'TLR');
      }
      $this->Cell(24, $this->desc, $this->_('Carteira'), 'TR');
    }
    $this->Cell(10, $this->desc, $this->_('Espécie'), 'TR');
    $this->Cell(14, $this->desc, $this->_('Quantidade'), 'TR');
    $this->Cell(40, $this->desc, $this->_('Valor'), 'TR');
    $this->Cell(30, $this->desc, $this->_('Valor Documento'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('Valor Documento'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('| '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    if (isset($this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) && $this->boleto[$i]->variaveis_adicionais['esconde_uso_banco']) {
      $this->TextFitCell(55, $this->cell, $this->_($this->boleto[$i]->getCarteiraNome()), 'LR', 0, 'L');
    } else {
      $cip = isset($this->boleto[$i]->variaveis_adicionais['mostra_cip']) && $this->boleto[$i]->variaveis_adicionais['mostra_cip'];
      $this->Cell(($cip ? 23 : 29), $this->cell, $this->_(''), 'LR');
      if ($cip) {
        $this->Cell(7, $this->cell, $this->_($this->boleto[$i]->getCip()), 'LR');
      }
      $this->Cell(24, $this->cell, $this->_(strtoupper($this->boleto[$i]->getCarteiraNome())), 'R');
    }
    $this->Cell(10, $this->cell, $this->_('R$'), 'R');
    $this->Cell(14, $this->cell, $this->_(''), 'R');
    $this->Cell(40, $this->cell, $this->_(''), 'R');
    $this->Cell(30, $this->cell, $this->_(Util::nReal($this->boleto[$i]->getValor())), 'R', 1, 'R');
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_(Util::nReal($this->boleto[$i]->getValor())), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc,
      $this->_("Instruções de responsabilidade do beneficiário. Qualquer dúvida sobre este boleto, contate o beneficiário"),
      'TLR');
    $this->Cell(30, $this->desc, $this->_('(-) Desconto / Abatimentos'), 'TR', 1);
    $xInstrucoes = $this->GetX();
    $yInstrucoes = $this->GetY();
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('(-) Desconto'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('|  '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, 2, $this->_(''), 'LR');
    $this->Cell(30, 2, $this->_(''), 'R', 1);
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_(''), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('   '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc, $this->_(''), 'LR');
    $this->Cell(30, $this->desc, $this->_('(+) Mora / Multa'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('(+) Mora / Multa'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('|  '));
    $this->Cell(117, 2, $this->_(''), 'LR');
    $this->Cell(30, 2, $this->_(''), 'R', 1);
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_(''), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('   '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc, $this->_(''), 'LR');
    $this->Cell(30, $this->desc, $this->_('(+) Outros Acréscimos'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('(+) Outros Acrés.'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('|  '));
    $this->Cell(117, 2, $this->_(''), 'LR');
    $this->Cell(30, 2, $this->_(''), 'R', 1);
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_(''), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('   '));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(117, $this->desc, $this->_(''), 'LR');
    $this->Cell(30, $this->desc, $this->_('(=) Valor Cobrado'), 'TR', 1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc, $this->_('(=) Valor Cobrado'), 'LR');
    $this->Cell(3, -$this->desc, $this->_('|  '));
    $this->Cell(117, 2, $this->_(''), 'BLR');
    $this->Cell(30, 2, $this->_(''), 'BR', 1);
    $this->SetFont($this->PadraoFont, 'B', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_(''), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('   '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    $this->Cell(147, $this->cell, $this->_('Pagador: ' . $nomeDocumentoPagador), 'LR',
      1);
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, -$this->desc - 1, $this->_('Sacado'), 'LR');
    $this->Cell(3, -$this->desc - 1, $this->_('|  '));
    $this->SetFont($this->PadraoFont, 'B', $this->fcel);
    $this->Cell(147, $this->cell,
      $this->_(trim('Endereço: ' . $enderecoPagador . ' - ' . substr($this->boleto[$i]->getPagador()->getBairro(), 0, 6) . ' ' . $this->boleto[$i]->getPagador()->getCepCidadeUf()),
        ' -'), 'BLR', 1);
    $sacado = $nomeDocumentoPagador;
    $sacado = explode(' ', $sacado);
    $sacado = $this->formataSacado($sacado);
    foreach ($sacado as $value) {
      $this->SetFont($this->PadraoFont, 'B', $this->fdes);
      $this->Cell(20, -$this->desc - 1, $this->_($value), 'LR');
      $this->Cell(3, -$this->desc - 1, $this->_('   '));
      $this->Ln(3);
    }
    $this->Ln(-3);
    $this->Cell(20, -$this->desc - 1, $this->_(''), 'TLR');
    $this->Cell(3, -$this->desc - 1, $this->_('   '));
    $this->Ln(-((count($sacado) * 3) - 3));
    $this->SetFont($this->PadraoFont, '', $this->fdes);
    $this->Cell(20, $this->desc, $this->_('                                       Sacador/Avalista'), 0);
    $this->Cell(75, $this->desc,
      $this->_($this->boleto[$i]->getSacadorAvalista() ? $this->boleto[$i]->getSacadorAvalista()->getNomeDocumento() : ''),
      0);
    $this->Cell(52, $this->desc,
      $this->_('                                              Autenticação mecânica - Ficha de Compensação'), 0,
      1);
    $xOriginal = $this->GetX();
    $yOriginal = $this->GetY();
    if (count($this->boleto[$i]->getInstrucoes()) > 0) {
      $this->SetXY($xInstrucoes, $yInstrucoes);
      $this->Ln(1);
      $this->SetFont($this->PadraoFont, '', $this->fcel);
      $this->listaLinhas($this->boleto[$i]->getInstrucoes(), 0);
      $this->SetXY($xOriginal, $yOriginal);
    }
    return $this;
  }

  /**
   * Formata o nome do sacado para exibir no "canhoto".
   *
   * @param $sacado
   * @return array
   */
  private function formataSacado($sacado)
  {
    $countNomePagador = count($sacado);
    for ($i = 1; $i < $countNomePagador; $i++) {
      if (!empty($sacado[$i])) {
        $sacadoConcatenado = $sacado[$i - 1] . ' ' . $sacado[$i];
        if (strlen($sacadoConcatenado) <= 13) {
          $sacado[$i - 1] = $sacadoConcatenado;
          unset($sacado[$i]);
          $sacado = array_values($sacado);
          $this->formataSacado($sacado);
        }
      }
    }
    return $sacado;
  }

  /**
   * @param integer $i
   */
  protected function codigoBarras($i)
  {
    $this->Ln(1);
    $this->Cell(0, 15, '', 0, 1, 'L');
    $this->i25(43, $this->GetY() - 15, $this->boleto[$i]->getCodigoBarras(), 0.8, 17);
    $this->Ln(7);
  }

  /**
   * Addiciona o boletos
   *
   * @param array $boletos
   *
   * @return $this
   */
  public function addBoletos(array $boletos)
  {
    foreach ($boletos as $boleto) {
      $this->addBoleto($boleto);
    }
    return $this;
  }

  /**
   * Addiciona o boleto
   *
   * @param BoletoContract $boleto
   *
   * @return $this
   */
  public function addBoleto(BoletoContract $boleto)
  {
    $this->totalBoletos += 1;
    $this->boleto[] = $boleto;
    return $this;
  }

  /**
   * função para gerar o boleto
   *
   * @param string $dest tipo de destino const BOLETOPDF_DEST_STANDARD | BOLETOPDF_DEST_DOWNLOAD | BOLETOPDF_DEST_SAVE | BOLETOPDF_DEST_STRING
   * @param null $save_path
   *
   * @return string
   * @throws \Exception
   */
  public function gerarBoleto($dest = self::OUTPUT_STANDARD, $save_path = null)
  {
    if ($this->totalBoletos == 0) {
      throw new \Exception('Nenhum Boleto adicionado');
    }
    for ($i = 0; $i < $this->totalBoletos; $i++) {
      $this->SetDrawColor('0', '0', '0');
      if (($i == 0) || (($i % 3) == 0)) {
        $this->AddPage();
      }
      $this->body($i)->codigoBarras($i);
    }
    if ($dest == self::OUTPUT_SAVE) {
      $this->Output($save_path, $dest, $this->print);
      return $save_path;
    }
    return $this->Output(str_random(32) . '.pdf', $dest, $this->print);
  }

  /**
   * @param $lista
   * @param integer $pulaLinha
   *
   * @return int
   */
  private function listaLinhas($lista, $pulaLinha)
  {
    foreach ($lista as $d) {
      $d = substr($d, 0, 57);
      $pulaLinha -= 2;
      $this->SetLeftMargin(43);
      $this->Cell(0, 4, $this->_(preg_replace('/(%)/', '%$1', $d)), 0, 1);
    }
    $this->SetLeftMargin(20);
    return $pulaLinha;
  }
}