<?php

ini_set('default_charset', 'UTF-8');

session_start();
$orgao = $_SESSION['org'];
$arq = $_POST['arq'];
//print_r($_POST);
$linha[] = "nome<>".$_POST['nome']."<>";
$linha[] = "\r\nlocalidade<>".$_POST['localidade']."<>";
$linha[] = "\r\nadjunto<>".$_POST['adjunto']."<>";
$linha[] = "\r\nescalante<>".$_POST['escalante']."<>";
$linha[] = "\r\nchefe<>".$_POST['chefe']."<>";
$linha[] = "\r\nchefe_do<>".$_POST['chefeDO']."<>";
foreach ($_POST['sva'] as $sva){
    $linha[] = "\r\nsva<>".$sva['escLeg']."<>".$sva['leg']."<>".$sva['opr']."<>".$sva['dia']."<>".$sva['posto']."<>".$sva['postoTexto']."<>".$sva['obs']."<>";
}
foreach ($_POST['ch_instrucao'] as $ch) {
    $linha[] = "\r\nch_instrucao<>".$ch['escLeg']."<>".$ch['valor']."<>";
}
foreach ($_POST['detalhes_instrucao'] as $d) {
    $linha[] = "\r\ndetalhes_instrucao<>".$d['escLeg']."<>".$d['valor']."<>";
}

foreach ($_POST['afa'] as $afa){
    $linha[] = "\r\nafa<>".$afa['escLeg']."<>".$afa['leg']."<>".$afa['opr']."<>".$afa['dia']."<>".$afa['tipo']."<>".$afa['tipoTexto']."<>".$afa['obs']."<>";
}
foreach ($_POST['exp'] as $exp){
    $linha[] = "\r\nexp<>".$exp['escLeg']."<>".$exp['leg']."<>".$exp['opr']."<>".$exp['dia']."<>".$exp['obs']."<>";
}
foreach ($_POST['observacoes'] as $d) {
    $linha[] = "\r\nobservacoes<>".$d['escLeg']."<>".str_replace("\n", '<br>', $d['valor'])."<>";
}

$caminho = "../../BD/".$orgao."/infoImpressao/$arq.txt";
file_put_contents($caminho, $linha);