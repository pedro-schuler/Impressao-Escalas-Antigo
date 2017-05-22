<?php

ini_set('default_charset', 'UTF-8');
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

set_time_limit(0);
session_start();
$orgao = $_SESSION['org'];
$arq = $_GET['arq'];
$escalasAndNomes = explode("_", $_GET['escalas']);
foreach ($escalasAndNomes as $e) {
    $eex = explode("|", $e);
    $escalasA[$eex[0]] = $eex[1];
}
$arqarray = explode('_', $arq);
$ano = $arqarray[0];
$mes = $arqarray[1];
$tipo = $arqarray[2];

$diasNoMes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

//pega os dados do efetivo no mes
$caminho = "../../BD/" . $orgao . "/efetivo/" . $mes . "_" . $ano . ".txt";
$esc = file($caminho);
foreach ($esc as $e) {
    $lineex = explode("<>", $e);
    $efetivo[$lineex[1]][$lineex[2]] = $lineex[3] . " BCT " . $lineex[4];
    if (array_key_exists($lineex[1], $escalasA)) {
        $efetivoLista[$lineex[2]] = $lineex[3] . " BCT " . $lineex[4];

        $saram = str_replace("-", "", $lineex[7]);
    }
    $efetivoSaramLegenda[$lineex[1]][$saram] = $lineex[2];
}
ksort($efetivoLista);

//print_r($efetivo);
//pega os turnos da escala geral
$caminhoB = "../../BD/" . $orgao . "/escalas/" . $arq . ".txt";
$base = file($caminhoB);

$base1 = explode("<>", $base[1]);
unset($base1[0]);
unset($base1[sizeof($base1)]);
foreach ($base1 as $b) {
    $bex = explode("+", $b);
    $turnos[$bex[0]][] = $bex[1];
    $turnos[$bex[0]][] = $bex[2];
    $turnos[$bex[0]][] = $bex[3];

    $iniEx = explode(":", $bex[2]);
    $iniP = $iniEx[0] + ($iniEx[1] / 60);
    $terEx = explode(":", $bex[3]);
    $terP = $terEx[0] + ($terEx[1] / 60);
    $duracao = $terP - $iniP;
    $duracao = $duracao <= 0 ? $duracao + 24 : $duracao;
    $turnos[$bex[0]][] = $duracao;
}
$expedientes = array();
$afastamentos = array();

//gera as legendas por dia e turno
foreach ($turnos as $t => $tn) {
    for ($i = 2; $i < 33; $i++) {
        $turnosLegendas[$t][$i - 1] = array();
        foreach ($escalasA as $esc => $escN) {
            $caminho = "../../BD/" . $orgao . "/escalas/" . $arq . "_" . $esc . ".txt";
            $e = file($caminho);

            unset($e[0]);
            foreach ($e as $el) {
                $lineex = explode("<>", $el);
                $combinacao = explode('/', $lineex[$i]);
                if (in_array($t, $combinacao)) {
                    $turnosLegendas[$t][$i - 1][] = $lineex[1];
                }
            }
        }
    }
}

foreach ($turnos as $leg => $inf) {
    if ($leg == "A" || $leg == "D") {
        unset($turnos[$leg]);
    }
}

$totalDiasComServicos = array();
$qtdDiasOperacionais = array();

foreach ($escalasA as $esc => $escN) {
    $caminho = "../../BD/" . $orgao . "/escalas/" . $arq . "_" . $esc . ".txt";
    $e = file($caminho);
    unset($e[0]);
    foreach ($e as $el) {
        $afastamentoArray = array();
        $expedienteArray = array();

        for ($i = 2; $i < 33; $i++) {
            $diaN = $i - 1;
            $coluna = $i;
            $lineex = explode("<>", $el);
            $diaEx = explode("/", $lineex[$i]);
            $diaAnterior = explode("/", $lineex[$i - 1]);
            if (in_array("A", $diaEx)) {
                $dia = "0$diaN";
                $dia = substr($dia, strlen($dia) - 2);
                $mes0 = "0$mes";
                $mes0 = substr($mes0, strlen($mes0) - 2);
                $inicio = "$dia/$mes0/$ano";

                $svcArmado[] = array('leg' => $lineex[1], 'opr' => $efetivo[$esc][$lineex[1]], 'ini' => $inicio);
            }
            if (in_array("--", $diaEx)) {
                $afastamentoArray[] = $diaN;
                //verifica o dia posterior
                $diaPosteriorEx = explode("/", $lineex[$coluna + 1]);
                if (!in_array("--", $diaPosteriorEx)) {
                    $inicioAfastamento = reset($afastamentoArray);
                    $terminoAfastamento = end($afastamentoArray);

                    $inicioAfastamento = "0" . $inicioAfastamento;
                    $inicioAfastamento = substr($inicioAfastamento, strlen($inicioAfastamento) - 2);
                    $mes0 = "0$mes";
                    $mes0 = substr($mes0, strlen($mes0) - 2);
                    $inicioAfastamento = "$inicioAfastamento/$mes0/$ano";
                    $terminoAfastamento = "0" . $terminoAfastamento;
                    $terminoAfastamento = substr($terminoAfastamento, strlen($terminoAfastamento) - 2);
                    $terminoAfastamento = "$terminoAfastamento/$mes0/$ano";

                    $afastamentos[] = array('leg' => $lineex[1], 'opr' => $efetivo[$esc][$lineex[1]], 'ini' => $inicioAfastamento, 'ter' => $terminoAfastamento);

                    $afastamentoArray = array();
                }
            }

            if (in_array("EA", $diaEx)) {
                $expedienteArray[] = $diaN;
                //verifica o dia posterior
                $diaPosteriorEx = explode("/", $lineex[$coluna + 1]);
                if (!in_array("EA", $diaPosteriorEx)) {
                    $inicioExpediente = reset($expedienteArray);
                    $terminoExpediente = end($expedienteArray);

                    $inicioExpediente = "0" . $inicioExpediente;
                    $inicioExpediente = substr($inicioExpediente, strlen($inicioExpediente) - 2);
                    $mes0 = "0$mes";
                    $mes0 = substr($mes0, strlen($mes0) - 2);
                    $inicioExpediente = "$inicioExpediente/$mes0/$ano";
                    $terminoExpediente = "0" . $terminoExpediente;
                    $terminoExpediente = substr($terminoExpediente, strlen($terminoExpediente) - 2);
                    $terminoExpediente = "$terminoExpediente/$mes0/$ano";

                    $expedientes[] = array('leg' => $lineex[1], 'opr' => $efetivo[$esc][$lineex[1]], 'ini' => $inicioExpediente, 'ter' => $terminoExpediente);

                    $expedienteArray = array();
                }
            }

            //checa se o militar esta de folga
            $temTurno = false;
            foreach ($turnos as $t => $tn) {
                if (in_array($t, $diaEx)) {
                    $temTurno = true;
                    $qtdTurnos[$lineex[1]][$t] ++;
                    $chOpr[$lineex[1]] += $tn[3];
                    $totalServicos[$lineex[1]] ++;
                    if (!$diasComTurnos[$lineex[1]][$diaN]){
                        $diasComTurnos[$lineex[1]][$diaN] = true; 
                    }
                  
                }
            }
            if (!$temTurno) {
                if (!in_array("EA", $diaEx) &&
                    !in_array("A", $diaEx) &&
                    !in_array("--", $diaEx) &&
                    !in_array("MO", $diaEx) &&
                    !in_array("PO", $diaEx) &&
                    !in_array("TO", $diaEx) &&
                    !in_array("E1", $diaEx) &&
                    !in_array("E5", $diaEx)) {
                    $folgas[$diaN][] = $lineex[1];
                    $totalFolgas[$lineex[1]] ++;
                }
            }

        }

        //Cálculo dos dias operacionais (folgas + turnos) para cada operador
        $totalDiasComServicos[$lineex[1]] += sizeof($diasComTurnos[$lineex[1]]);
        $qtdDiasOperacionais[$lineex[1]] += $totalDiasComServicos[$lineex[1]] + $totalFolgas[$lineex[1]];

    }
}

//qtd de efetivo///////////////////////////////
$qtdEfetivoTotal = sizeof($efetivoLista);
$qtdEfetivoEscala = sizeof($chOpr);

//media carga horaria

function mediaCargaHoraria($cargaHoraria, $qtdDiasOperacionais, $diasNoMes) {
    
    foreach ($qtdDiasOperacionais as $legenda => $qtdDias) {
        $chOperador[$legenda] = isset($cargaHoraria[$legenda]) ? $cargaHoraria[$legenda] : 0;
        $fatorConversaoOperador[$legenda] = ($qtdDias / $diasNoMes);
    }

    $somaCargaHorariaOperadores = array_sum($chOperador);
    $operadoresNaEscalaComConversao = array_sum($fatorConversaoOperador);

    return $somaCargaHorariaOperadores / $operadoresNaEscalaComConversao;

}

$mediaComConversao = ceil(mediaCargaHoraria($chOpr, $qtdDiasOperacionais, $diasNoMes));

$mediaReal = $mediaComConversao;
$mediaComTodos = ceil(array_sum($chOpr) / $qtdEfetivoTotal);
$chMinima = 0;
if ($orgao === "ACC-RE") {
    if ( array_key_exists("MNT", $escalasA) ) {
        $chMinima = 30;
    } else {
        $chMinima = 130;
    }
    
} else {
    $chMinima = $mediaComTodos;
}
//////////////////////////////////////////////
//PEGAR AS INFORMACOES QUE JA FORAM SALVAS
$caminhoI = "../../BD/" . $orgao . "/infoImpressao/$arq.txt";
$infos = array(
    'nome' => '',
    'localidade' => '',
    'adjunto' => '',
    'escalante' => '',
    'chefe' => '',
    'chefe_do' => '',
    'sva' => array(),
    'ch_instrucao' => array(),
    'detalhes_instrucao' => array(),
    'afa' => array(),
    'exp' => array(),
    'observacoes' => array()
);

if (file_exists($caminhoI)) {
    $inf = file($caminhoI);
    foreach ($inf as $i) {
        $l = explode("<>", $i);
        $nome = $l[0];
        switch ($nome) {
            case 'nome':
                $infos['nome'] = $l[1];
                break;
            case 'localidade':
                $infos['localidade'] = $l[1];
                break;
            case 'adjunto':
                $infos['adjunto'] = $l[1];
                break;
            case 'escalante':
                $infos['escalante'] = $l[1];
                break;
            case 'chefe':
                $infos['chefe'] = $l[1];
                break;
            case 'chefe_do':
                $infos['chefe_do'] = $l[1];
                break;
            case 'sva':
                $infos['sva'][$l[1]][] = array('leg' => $l[2], 'opr' => $l[3], 'dia' => $l[4], 'posto' => $l[5], 'postoTexto' => $l[6], 'obs' => $l[7]);
                break;
            case 'ch_instrucao':
                $infos['ch_instrucao'][$l[1]] = $l[2];
                break;
            case 'detalhes_instrucao':
                $infos['detalhes_instrucao'][$l[1]] = $l[2];
                break;
            case 'afa':
                $infos['afa'][$l[1]][] = array('leg' => $l[2], 'opr' => $l[3], 'dia' => $l[4], 'tipo' => $l[5], 'tipoTexto' => $l[6], 'obs' => $l[7]);
                break;
            case 'exp':
                $infos['exp'][$l[1]][] = array('leg' => $l[2], 'opr' => $l[3], 'dia' => $l[4], 'obs' => $l[5]);
                break;
            case 'observacoes':
                $infos['observacoes'][$l[1]] = $l[2];
                break;
        }
    }
}
switch ($tipo) {
    case 'p':
        $tipoN = 'PRÉVIA';
        break;
    case 'c':
        $tipoN = 'PREVISTA';
        break;
    case 'd':
        $tipoN = 'CUMPRIDA';
        break;
}

switch ($mes) {
    case 1:
        $mesN = 'JANEIRO';
        break;
    case 2:
        $mesN = 'FEVEREIRO';
        break;
    case 3:
        $mesN = 'MARÇO';
        break;
    case 4:
        $mesN = 'ABRIL';
        break;
    case 5:
        $mesN = 'MAIO';
        break;
    case 6:
        $mesN = 'JUNHO';
        break;
    case 7:
        $mesN = 'JULHO';
        break;
    case 8:
        $mesN = 'AGOSTO';
        break;
    case 9:
        $mesN = 'SETEMBRO';
        break;
    case 10:
        $mesN = 'OUTUBRO';
        break;
    case 11:
        $mesN = 'NOVEMBRO';
        break;
    case 12:
        $mesN = 'DEZEMBRO';
        break;
}
require '../../func/TCPDF/tcpdf.php';
// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
    require_once(dirname(__FILE__) . '/lang/eng.php');
    $pdf->setLanguageArray($l);
}

// ---------------------------------------------------------
// set default font subsetting mode
$pdf->setFontSubsetting(true);
//
// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 14, '', true);
// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->SetMargins(10, 10, 10, true);
$pdf->AddPage('P', 'A4');

$nome = $infos['nome'];
$localidade = $infos['localidade'];
$escalante = $infos['escalante'];
$chefe = $infos['chefe'];
$chefeDo = $infos['chefe_do'];

$valores = array();
foreach ($escalasA as $leg => $n) {
    if (array_key_exists($leg, $infos['ch_instrucao'])) {
        if ($infos['ch_instrucao'][$leg] != "") {
            $valores[] = $infos['ch_instrucao'][$leg];
        }
    }
}
$horaInstrucao = array_key_exists(0, $valores) ? $valores[0] : 0;
// Set some content to print
$html = <<<EOD
    <table cellpadding="2" width="1130">
        <tr style="font-weight: bold; font-size: 9;">
            <td style="width:120;border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">SRPV/CINDACTA</td>
            <td style="width:145;border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">ESCALA</td>
            <td style="width:120;border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">MÊS/ANO</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">ESCALANTE</td>
        </tr>
        <tr align="center" style="font-weight: lighter;">
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">CINDACTA III</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$tipoN</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$mesN/$ano</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 6;">$escalante</td>
        </tr>
        <tr style="font-weight: bold; font-size: 9;">
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">LOCALIDADE</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">EFETIVO TOTAL</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">EFETIVO ESCALA</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">CHEFE DO ÓRGÃO</td>
        </tr>
        <tr align="center" style="font-weight: lighter;">
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$localidade</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$qtdEfetivoTotal</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$qtdEfetivoEscala</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 6;">$chefe</td>
        </tr>
        <tr style="font-weight: bold; font-size: 9;">
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">ÓRGÃO</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">MÉDIA HORA MENSAL</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">HORA INSTRUÇÃO</td>
            <td style="border-left:0px solid black;border-top:0px solid black;border-right:0px solid black;">CHEFE DA DIVISÃO DE OPERAÇÕES</td>
        </tr>
            <tr align="center" style="font-weight: lighter;">
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$nome</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$chMinima/$mediaReal</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 8;">$horaInstrucao</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 6;">$chefeDo</td>
        </tr>
    </table>
    <table cellpadding="2">
        <tr align="center" style="font-size: 9;">
            <td style="width:30;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>DIA</b></td>
            <td style="width:35;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>SEM</b></td>
EOD;
$larguraParaTurnos = 400;

$totalTurnos = sizeof($turnos);
$larguraPorTurno = $larguraParaTurnos / $totalTurnos;
foreach ($turnos as $leg => $inf) {
    $html .= <<<EOD
            <td style="width:$larguraPorTurno;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>$inf[0]</b><br><font size="7">$inf[1]/$inf[2]</font></td>
EOD;
}

$html .= <<<EOD
            <td style="width:168;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>FOLGA</b></td>
            <td style="width:35;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>OBS</b></td>
        </tr>
EOD;
for ($dia = 1; $dia <= $diasNoMes; $dia++) {
    $diaSemana = mb_strtoupper(utf8_encode(strftime('%a', strtotime("$mes/$dia/$ano"))), "UTF-8");
    //echo utf8_encode($diaSemana);
    $html .= <<<EOD
        <tr style="font-weight: lighter; font-size: 9;">
            <td align="center" style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>$dia</b></td>
            <td align="center" style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"><b>$diaSemana</b></td>
EOD;
    foreach ($turnos as $leg => $inf) {
        $cel = implode(" ", $turnosLegendas[$leg][$dia]);
        $html .= <<<EOD
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">$cel</td>
EOD;
    }
    $folgasI = implode(" ", $folgas[$dia]);
    $html .= <<<EOD
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">$folgasI</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;"></td>
        </tr>
EOD;
}
$html .= <<<EOD
    </table>
    <table align="center" cellpadding="2" width="668">
        <tr>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 10;"><b>LEGENDAS</b></td>
        </tr>
    </table>
    <table align="center" cellpadding="2" width="668">
        <tr>
            <td style="width:40;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;"><b>CÓD</b></td>
            <td style="width:182;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;"><b>OPERADOR</b></td>
            <td style="width:40;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;"><b>CÓD</b></td>
            <td style="width:183;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;"><b>OPERADOR</b></td>
            <td style="width:40;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;"><b>CÓD</b></td>
            <td style="width:183;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;"><b>OPERADOR</b></td>
        </tr>
EOD;

$i = 0;
$linhaArray = array();
$linhasArray = array();
foreach ($efetivoLista as $leg => $el) {
    $linhaArray[] = array($leg, $el);
    $i++;
    if ($i == 3) {
        $linhasArray[] = $linhaArray;
        $linhaArray = array();
        $i = 0;
    }
}
if ($i != 0) {
    $linhasArray[] = $linhaArray;
}

foreach ($linhasArray as $l) {
    $cod1 = $l[0][0];
    $opr1 = $l[0][1];
    $cod2 = $l[1][0];
    $opr2 = $l[1][1];
    $cod3 = $l[2][0];
    $opr3 = $l[2][1];
    $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">$cod1</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">$opr1</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">$cod2</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">$opr2</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">$cod3</td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">$opr3</td>
        </tr>
EOD;
}
$html .= <<<EOD
    </table>
EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);
$pdf->AddPage('P', 'A4');
$adjunto = $infos['adjunto'];
$html = <<<EOD
    <table align="left" cellpadding="2" width="668">
        <tr>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">
                <b>ESPAÇO RESERVADO PARA OBSERVAÇÕES QUE SE FAÇAM NECESSÁRIAS</b>
            </td>
        </tr>
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>1.ADJUNTO</b>
            </td>
        </tr>
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $adjunto
            </td>
        </tr>
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>2.SERVIÇOS INDIVIDUAIS RISAER</b>
            </td>
        </tr>
EOD;

foreach ($infos['sva'] as $escLeg => $sva) {
    if (array_key_exists($escLeg, $escalasA)) {
        foreach ($sva as $s) {
            $opr = $s['opr'];
            $posto = $s['postoTexto'];
            $ini = $s['dia'];
            $iniEx = explode("/", $ini);
            $dia = $iniEx[0];
            $mes = $iniEx[1];
            $ano = $iniEx[2];
            $diaT = ($dia + 1) > $diasNoMes ? 1 : $dia + 1;
            $mesT = ($dia + 1) > $diasNoMes ? (($mes + 1) > 12 ? 1 : ($mes + 1)) : $mes;
            $anoT = ($dia + 1) > $diasNoMes ? (($mes + 1) > 12 ? ($ano + 1) : $ano) : $ano;
            $diaT = "0$diaT";
            $diaT = substr($diaT, strlen($diaT) - 2);
            $mes0T = "0$mesT";
            $mes0T = substr($mes0T, strlen($mes0T) - 2);
            $ter = "$diaT/$mes0T/$anoT";

            $obs = $s['obs'] == "" ? "" : "(" . $s['obs'] . ")";

            $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $opr - $posto de $ini até $ter $obs
            </td>
        </tr>
EOD;
        }
    }
}

$html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>3.INSTRUÇÃO NA OM</b>
            </td>
        </tr>
EOD;
foreach ($infos['detalhes_instrucao'] as $escLeg => $di) {
    if (array_key_exists($escLeg, $escalasA)) {
        $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                - $di
            </td>
        </tr>
EOD;
    }
}
$html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>4.AFASTAMENTOS DA ESCALA</b>
            </td>
        </tr>
EOD;
foreach ($infos['afa'] as $escLeg => $afa) {
    if (array_key_exists($escLeg, $escalasA)) {
        foreach ($afa as $s) {
            $opr = $s['opr'];
            $tipoT = $s['tipoTexto'];
            $periodo = $s['dia'];
            $obs = $s['obs'] == "" ? "" : "(" . $s['obs'] . ")";

            $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $opr - $tipoT de $periodo $obs
            </td>
        </tr>
EOD;
        }
    }
}
$html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>5.EXPEDIENTE ADMISTRATIVO</b>
            </td>
        </tr>
EOD;

$listaExpediente = array();
$listaExpedienteNome = array();
foreach ($infos['exp'] as $escLeg => $exp) {
    if (array_key_exists($escLeg, $escalasA)) {
        foreach ($exp as $s) {
            $obs = $s['obs'] == "" ? "" : "(" . $s['obs'] . ")";
            $listaExpedienteDias[$s['leg']][] = "de " . $s['dia'] . " " . $obs;
            $listaExpedienteNome[$s['leg']] = $s['opr'] . " -";
        }
    }
}
foreach ($listaExpedienteNome as $leg => $d) {
    $dados = implode(", ", $listaExpedienteDias[$leg]);
    $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $d $dados
            </td>
        </tr>
EOD;
}
$html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 7;">
                <b>6.OBSERVAÇÔES</b>
            </td>
        </tr>
EOD;
$listaObs = array();
foreach ($infos['observacoes'] as $escLeg => $obs) {
    if (array_key_exists($escLeg, $escalasA)) {
        $obsEsp = str_replace(" ", "", $obs);
        if ($obsEsp != "") {
            $listaObs[] = $obs;
        }
    }
}
if (empty($listaObs)) {
    $html .= <<<EOD
        <tr>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                Não previsto.
            </td>
        </tr>
EOD;
    $linhasUsadas++;
} else {
    foreach ($listaObs as $d) {
        $dex = explode("<br>", $d);
        foreach ($dex as $de) {
            $html .= <<<EOD
            <tr>
                <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                    $de
                </td>
            </tr>
EOD;
        }
    }
}
$escalante = $infos['escalante'];
$html .= <<<EOD
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;"></td>
        </tr>
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $escalante
            </td>
        </tr>
EOD;
$html .= <<<EOD
    </table>
EOD;
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);

$modificacoes = array();
if ($tipo == "d") {
    $i = 0;
    $linhaArray = array();

    //remanejamentos, escalacoes e dispensas
    $caminhoRED = "../../BD/" . $orgao . "/RED/" . $arq . "_RED.txt";
    if (file_exists($caminhoRED)) {
        $red = file($caminhoRED);
        foreach ($red as $r) {
            $l = explode("<>", $r);
            if (array_key_exists($l[3], $escalasA)) {
                if ($l[1] == 'disp') {
                    $linhaArray[] = $l[2] . " por no dia " . $l[4] . " Motivo: " . $l[6];
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                } else if ($l[1] == 'esc') {
                    $linhaArray[] = " por " . $l[2] . " no dia " . $l[4] . " Motivo: " . $l[6];
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                } else if ($l[1] == 'rem') {
                    $linhaArray[] = $l[2] . " por no dia " . $l[4] . " Motivo: " . $l[8];
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }

                    $linhaArray[] = " por " . $l[2] . " no dia " . $l[6] . " Motivo: " . $l[8];
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                }
            }
        }
    }
//    print_r($efetivoSaramLegenda);
    //trocas
    $caminhoTrocas = "../../BD/" . $orgao . "/TROCAS/EFETUADAS/" . $arq . ".txt";
    if (file_exists($caminhoTrocas)) {
        $trocas = file($caminhoTrocas);
        foreach ($trocas as $t) {
            $l = explode("<>", $t);

            $legendaOpr1 = $efetivoSaramLegenda[$l[2]][$l[1]];
            $dia1 = $l[3];
            $legendaOpr2 = $efetivoSaramLegenda[$l[6]][$l[5]];
            $dia2 = $l[7];

            $escala1 = $l[2];
            $escala2 = $l[6];
            if ($escala1 == $escala2) {
                if (array_key_exists($escala1, $escalasA)) {
                    $linhaArray[] = "$legendaOpr1 por $legendaOpr2 no dia $dia1 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }

                    $linhaArray[] = "$legendaOpr2 por $legendaOpr1 no dia $dia2 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                }
            } else {
                if (array_key_exists($escala1, $escalasA) && array_key_exists($escala2, $escalasA)) {
                    $linhaArray[] = "$legendaOpr1 por $legendaOpr2 no dia $dia1 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }

                    $linhaArray[] = "$legendaOpr2 por $legendaOpr1 no dia $dia2 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                } else if (array_key_exists($escala1, $escalasA)) {
                    $linhaArray[] = "$legendaOpr1 por no dia $dia1 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }

                    $linhaArray[] = "por $legendaOpr1 no dia $dia2 Motivo: Particular de $legendaOpr1";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                } else if (array_key_exists($escala2, $escalasA)) {
                    $linhaArray[] = "$legendaOpr2 por no dia $dia2 Motivo: Particular de $legendaOpr2";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }

                    $linhaArray[] = "por $legendaOpr2 no dia $dia1 Motivo: Particular de $legendaOpr2";
                    $i++;
                    if ($i == 2) {
                        $modificacoes[] = $linhaArray;
                        $linhaArray = array();
                        $i = 0;
                    }
                }
            }
        }
    }
    if ($i != 0) {
        $modificacoes[] = $linhaArray;
    }
}

//$modificacoesI = implode(" ", $modificacoes);
//print_r($modificacoesI);
$chefe = $infos['chefe'];
$html = <<<EOD
    <table align="left" cellpadding="2" width="668">
        <tr>
            <td colspan="2" style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">
                <b>ALTERAÇÕES NA ESCALA</b>
            </td>
        </tr>
EOD;
//print_r($modificacoes);
foreach ($modificacoes as $m) {
    $m1 = $m[0];
    $m2 = $m[1];
    $html .= <<<EOD
        <tr align="left">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">$m1</td>
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">$m2</td>
        </tr>
EOD;
}
$html .= <<<EOD
        <tr align="right">
            <td colspan="2" style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $chefe
            </td>
        </tr>
    </table>
EOD;
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);

$html = <<<EOD
    <table align="left" cellpadding="2" width="668">
        <tr>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;">
                <b>OBSERVAÇOES DO CHEFE DA DIVISÃO DE OPERAÇÕES</b>
            </td>
        </tr>
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;"></td>
        </tr>
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;"></td>
        </tr>
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;"></td>
        </tr>
        <tr align="right">
            <td style="border-left:0px solid black;border-right:0px solid black;font-size: 6;">
                $chefeDo
            </td>
        </tr>
    </table>
EOD;
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);
$dimensoesPaginas = $pdf->getPageDimensions();
$alturaRestatante = ($dimensoesPaginas['hk'] - $pdf->GetY() - $dimensoesPaginas['bm']) * 3.53;

$html = <<<EOD
    <table align="left" cellpadding="2" width="668">
        <tr>
            <td height="$alturaRestatante" style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;font-size: 7;"></td>
        </tr>
    </table>
EOD;
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);

$pdf->AddPage('P', 'A4');
$html = <<<EOD
    <table align="center" cellpadding="2" width="668">
        <tr>
            <td colspan="2" style="width:200;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">
                <b>LEGENDA/NOME</b>
            </td>
            <td style="width:50;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">
                <b>TOTAL</b>
            </td>
EOD;
$larguraColuna = 368 / (sizeof($turnos));
foreach ($turnos as $t => $tn) {
    $duracao = $tn[3];
    $html .= <<<EOD
            <td style="width:$larguraColuna;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">
                <b>$duracao</b>
            </td>
EOD;
}
$html .= <<<EOD
        <td style="width:50;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 7;background-color: #bbb;">
                <b>HORAS</b>
            </td>
        </tr>
EOD;
$nl = 0;
foreach ($efetivoLista as $leg => $nome) {
    $nl++;
    $corDaLinha = $nl % 2 == 0 ? 'style="background-color: #f2f2f2;"' : "";

    $total = $totalServicos[$leg];
    if ($total === null) {
        $total = 0;
    }
    $ch = $chOpr[$leg];
    if ($ch === null) {
        $ch = 0;
    }
    $html .= <<<EOD
        <tr align="center" $corDaLinha>
            <td style="width:25;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">
                $leg
            </td>
            <td style="width:175;border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">
                $nome
            </td>
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">
                $total
            </td>
EOD;
    foreach ($turnos as $t => $tn) {
        $qtdT = $qtdTurnos[$leg][$t];
        if ($qtdT === null) {
            $qtdT = 0;
        }
        $html .= <<<EOD
        <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">
                $qtdT
            </td>
EOD;
    }
    $html .= <<<EOD
            <td style="border-left:0px solid black;border-bottom:0px solid black;border-right:0px solid black;border-top:0px solid black;font-size: 6;">
                $ch
            </td>
        </tr>
EOD;
}
$html .= <<<EOD
    </table>
EOD;
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', false);


// ---------------------------------------------------------
// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');
//============================================================+
// END OF FILE
//============================================================+



    