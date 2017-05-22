<?php
ini_set('default_charset', 'UTF-8');

session_start();
$orgao = $_SESSION['org'];
$arq = $_POST['arq'];
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
}

//pega os turnos da escala geral
$caminhoB = "../../BD/" . $orgao . "/escalas/" . $arq . ".txt";
$base = file($caminhoB);

$base0 = explode("<>", $base[0]);
unset($base0[0]);
unset($base0[sizeof($base0)]);
foreach ($base0 as $b) {
    $bex = explode("+", $b);
    $escalas[$bex[0]] = $bex[1];
}

$base1 = explode("<>", $base[1]);
unset($base1[0]);
unset($base1[sizeof($base1)]);
foreach ($base1 as $b) {
    $bex = explode("+", $b);
    $turnos[$bex[0]][] = $bex[1];
}


$expedientes = array();
$afastamentos = array();

foreach ($escalas as $escLeg => $escN) {
    $caminho = "../../BD/" . $orgao . "/escalas/" . $arq . "_" . $escLeg . ".txt";
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
            if (in_array("A", $diaEx)) {
                $dia = "0$diaN";
                $dia = substr($dia, strlen($dia) - 2);
                $mes0 = "0$mes";
                $mes0 = substr($mes0, strlen($mes0) - 2);
                $inicio = "$dia/$mes0/$ano";

                $svcArmado[$escLeg][] = array('leg' => $lineex[1], 'opr' => $efetivo[$escLeg][$lineex[1]], 'ini' => $inicio);
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

                    $afastamentos[$escLeg][] = array('leg' => $lineex[1], 'opr' => $efetivo[$escLeg][$lineex[1]], 'ini' => $inicioAfastamento, 'ter' => $terminoAfastamento);

                    $afastamentoArray = array();
                }
            }

            if (in_array("E1", $diaEx) || in_array("E5", $diaEx) || in_array("ET", $diaEx)) {
                $expedienteArray[] = $diaN;
                //verifica o dia posterior
                $diaPosteriorEx = explode("/", $lineex[$coluna + 1]);
                if (!in_array("E1", $diaPosteriorEx) && !in_array("E5", $diaPosteriorEx) && !in_array("ET", $diaPosteriorEx)) {
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

                    $expedientes[$escLeg][] = array('leg' => $lineex[1], 'opr' => $efetivo[$escLeg][$lineex[1]], 'ini' => $inicioExpediente, 'ter' => $terminoExpediente);

                    $expedienteArray = array();
                }
            }
        }
    }
}



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

if ($tipo == 'd') {
    if (!file_exists($caminhoI)) {
        $caminhoIP = "../../BD/" . $orgao . "/infoImpressao/" . $ano . "_" . $mes . "_c.txt";
        if (file_exists($caminhoIP)) {
            $a = file($caminhoIP);
            file_put_contents($caminhoI, $a); //copia os dados da prevista para a cumprida
        }
    }
}

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
                $infos['observacoes'][$l[1]] = str_replace('<br>', '&#10;', $l[2]);
                break;
        }
    }
}
?>
<div class="row clearfix" id="informacoes" arq="<?php echo $arq; ?>">
    <center>
        <strong>ESCALA  
            <?php
            if ($mes < 10) {
                $mes = '0' . $mes;
            }
            switch ($tipo) {
                case 'p':
                    $tipo = 'PRÉVIA';
                    break;
                case 'c':
                    $tipo = 'PREVISTA';
                    break;
                case 'd':
                    $tipo = 'CUMPRIDA';
                    break;
            }
            echo $tipo . ' DO ' . $orgao . ' REFERENTE A ' . $mes . '/' . $ano . ':<br>';
            ?>
        </strong>        
    </center>
    <div class="row">
        <div class="col-md-6" align ="left">
            <label>ÓRGÃO/NOME DA ESCALA</label>
            <?php $valor = $infos['nome']; ?>
            <input class="form-control" type="text" id="nome" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
            <br>
            <label>LOCALIDADE</label>
            <?php $valor = $infos['localidade']; ?>
            <input class="form-control" type="text" id="localidade" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
            <br>
            <label>ADJUNTO</label>
            <?php $valor = $infos['adjunto']; ?>
            <input class="form-control" type="text" id="adjunto" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
            <br>
            <label>ESCALANTE</label>
            <?php $valor = $infos['escalante']; ?>
            <input class="form-control" type="text" id="escalante" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
            <br>
            <label>CHEFE DO ORGÃO</label>
            <?php $valor = $infos['chefe']; ?>
            <input class="form-control" type="text"  id="chefe" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
            <label>CHEFE DA DO</label>
            <?php $valor = $infos['chefe_do']; ?>
            <input class="form-control" type="text"  id="chefe_do" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
        </div>
    </div>
    <br>
    <?php
    $n1 = 0;
    $n2 = 0;
    $n3 = 0;
    foreach ($escalas as $escLeg => $escN) {
        ?>
        <div class="row">
            <div class="jumbotron well escalaInfo" escala="<?php echo $escLeg; ?>">
                <h4><?php echo $escN; ?></h4>
                <div class="row">
                    <h4 align="left">SERVIÇOS INDIVIDUAIS RISAER</h4>
                    <div class="col-md-12" align ="left">
                        <table class="table table-condensed table-hover">
                            <tr class="bg-primary">
                                <th>OPERADOR</th>
                                <th>DIA SVC</th>
                                <th>POSTO</th>
                                <th>OBS</th>
                                <th></th>
                            </tr>
                            <?php
                            if (array_key_exists($escLeg, $svcArmado)) {
                                $svcArmadoEsc = $svcArmado[$escLeg];
                            } else {
                                $svcArmadoEsc = array();
                            }
                            foreach ($svcArmadoEsc as $sva) {
                                $n = $n1;
                                ?>
                                <tr class="sva" legenda="<?php echo $sva['leg']; ?>" escala="<?php echo $escLeg; ?>">
                                    <td id="opr_<?php echo $sva['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $sva['opr']; ?></td>
                                    <td id="dia_<?php echo $sva['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $sva['ini']; ?></td>
                                    <?php
                                    $posto = "";
                                    $texto = "";
                                    $obs = "";
                                    if (array_key_exists($escLeg, $infos['sva'])) {
                                        foreach ($infos['sva'][$escLeg] as $i) {
                                            if ($sva['leg'] == $i['leg'] && $sva['ini'] == $i['dia']) {
                                                $posto = $i['posto'];
                                                $texto = $i['postoTexto'];
                                                $obs = $i['obs'];
                                            }
                                        }
                                    }
                                    ?>
                                    <td id="posto_<?php echo $sva['leg'] . "_" . $escLeg . "_$n"; ?>" valor="<?php echo $posto != '' ? $posto : ''; ?>"><?php echo $texto; ?></td>
                                    <td id="obs_<?php echo $sva['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $obs; ?></td>
                                    <td><button class="btn-sm btn-warning pull-right" onclick="editarSVA('<?php echo $sva['leg']; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>');"><span class="glyphicon glyphicon-pencil"></span></button></td>
                                </tr>
                                <?php
                                $n1++;
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>INSTRUÇÃO NA OM</h4>
                        <div class="col-md-3">
                            <label>CHARGA HORÁRIA</label>
                            <?php
                            if (array_key_exists($escLeg, $infos['ch_instrucao'])) {
                                $valor = $infos['ch_instrucao'][$escLeg];
                            } else {
                                $valor = "";
                            }
                            if (array_key_exists($escLeg, $infos['detalhes_instrucao'])) {
                                $det = $infos['detalhes_instrucao'][$escLeg];
                            } else {
                                $det = "";
                            }
                            ?>
                            <input id='ch_instrucao_<?php echo $escLeg; ?>' class="form-control soNumero" placeholder="INSERIR CARGA HORÁRIA" <?php echo $valor != "" ? "value='$valor'" : ""; ?>>
                        </div>
                        <div class="col-md-12">
                            <label>DETALHES</label>
                            <textarea id='texto_instrucao_<?php echo $escLeg; ?>' class="form-control" rows="2" placeholder="INSERIR DESCRIÇÃO"><?php echo $det; ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <h4 align="left">AFASTAMENTOS DA ESCALA</h4>
                    <div class="col-md-12" align ="left">
                        <table class="table table-condensed table-hover">
                            <tr class="bg-primary">
                                <th>OPERADOR</th>
                                <th>PERÍODO</th>
                                <th>TIPO</th>
                                <th>OBS</th>
                                <th></th>
                            </tr>
                            <?php
                            if (array_key_exists($escLeg, $afastamentos)) {
                                $afastamentosEsc = $afastamentos[$escLeg];
                            } else {
                                $afastamentosEsc = array();
                            }
                            foreach ($afastamentosEsc as $n => $af) {
                                $n = $n2;
                                ?>
                                <tr class="afa" legenda="<?php echo $af['leg']; ?>" escala="<?php echo $escLeg; ?>">
                                    <td id="afa_opr_<?php echo $af['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $af['opr']; ?></td>
                                    <td id="afa_dia_<?php echo $af['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $af['ini'] . " até " . $af['ter']; ?></td>
                                    <?php
                                    $tipo = "";
                                    $texto = "";
                                    $obs = "";
                                    $comp = $af['ini'] . " até " . $af['ter'];
                                    if (array_key_exists($escLeg, $infos['afa'])) {
                                        foreach ($infos['afa'][$escLeg] as $i) {
                                            if ($af['leg'] == $i['leg'] && $comp == $i['dia']) {
                                                $tipo = $i['tipo'];
                                                $texto = $i['tipoTexto'];
                                                $obs = $i['obs'];
                                            }
                                        }
                                    }
                                    ?>
                                    <td id="afa_tipo_<?php echo $af['leg'] . "_" . $escLeg . "_$n"; ?>" valor="<?php echo $tipo != '' ? $tipo : ''; ?>"><?php echo $texto; ?></td>
                                    <td id="afa_obs_<?php echo $af['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $obs; ?></td>
                                    <td><button class="btn-sm btn-warning pull-right" onclick="editarAFA('<?php echo $af['leg']; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>');"><span class="glyphicon glyphicon-pencil"></span></button></td>
                                </tr>
                                <?php
                                $n2++;
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <h4 align="left">EXPEDIENTE ADMINISTRATIVO</h4>
                    <div class="col-md-12" align ="left">
                        <table class="table table-condensed table-hover">
                            <tr class="bg-primary">
                                <th>OPERADOR</th>
                                <th>PERÍODO</th>
                                <th>OBS</th>
                                <th></th>
                            </tr>
                            <?php
                            if (array_key_exists($escLeg, $expedientes)) {
                                $expedientesEsc = $expedientes[$escLeg];
                            } else {
                                $expedientesEsc = array();
                            }
                            foreach ($expedientesEsc as $n => $ex) {
                                $n = $n3;
                                ?>
                                <tr class="exp" legenda="<?php echo $ex['leg']; ?>" escala="<?php echo $escLeg; ?>">
                                    <td id="exp_opr_<?php echo $ex['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $ex['opr']; ?></td>
                                    <td id="exp_dia_<?php echo $ex['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $ex['ini'] . " até " . $ex['ter']; ?></td>
                                    <?php
                                    $obs = "";
                                    $comp = $ex['ini'] . " até " . $ex['ter'];
                                    if (array_key_exists($escLeg, $infos['exp'])) {
                                        foreach ($infos['exp'][$escLeg] as $i) {
                                            if ($ex['leg'] == $i['leg'] && $comp == $i['dia']) {
                                                $obs = $i['obs'];
                                            }
                                        }
                                    }
                                    ?>
                                    <td id="exp_obs_<?php echo $ex['leg'] . "_" . $escLeg . "_$n"; ?>"><?php echo $obs; ?></td>
                                    <td><button class="btn-sm btn-warning pull-right" onclick="editarEXP('<?php echo $ex['leg']; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>');"><span class="glyphicon glyphicon-pencil"></span></button></td>
                                </tr>
                                <?php
                                $n3++;
                            }
                            ?>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>OBSERVAÇÕES</h4>
                        <?php
                        if (array_key_exists($escLeg, $infos['observacoes'])) {
                            $det = $infos['observacoes'][$escLeg];
                        } else {
                            $det = "";
                        }
                        ?>
                        <textarea id='texto_observacoes_<?php echo $escLeg; ?>' class="form-control" rows="2" placeholder="INSERIR DESCRIÇÃO"><?php echo $det; ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <?php
    }
    ?>
</div>
<br>
<div class="row clearfix">
    <div class="col-md-6" align ="left">
        <button class="btn btn-success" onclick="salvarDados();">SALVAR</button>
    </div>
    <div class="col-md-6" align ="right">
        <div class="col-md-8 column" align="right" id="select"></div>
        <div class="col-md-4" align="left">
            <button class="btn btn-primary" onclick="gerarPDF();">Imprimir</button>
        </div>
    </div>
</div>
<script>
    function editarSVA(leg, escLeg, n) {
        opr = $("#opr_" + leg + "_" + escLeg + "_" + n).html();
        dia = $("#dia_" + leg + "_" + escLeg + "_" + n).html();
        posto = $("#posto_" + leg + "_" + escLeg + "_" + n).attr('valor');
        obs = $("#obs_" + leg + "_" + escLeg + "_" + n).html();

        $.ajax({
            type: "POST",
            data: {opr: opr, dia: dia, posto: posto, obs: obs, leg: leg, escLeg: escLeg, n: n},
            url: 'funcBD/escalaImpressao/modalSVA.php',
            success: function (data) { //O HTML é retornado em 'data'
                $("#conteudo_modal_ed").html(data);
                $("#botao_modal").click();
                reativar();
            }
        });
    }
    function editarAFA(leg, escLeg, n) {
        opr = $("#afa_opr_" + leg + "_" + escLeg + "_" + n).html();
        dia = $("#afa_dia_" + leg + "_" + escLeg + "_" + n).html();
        tipo = $("#afa_tipo_" + leg + "_" + escLeg + "_" + n).attr('valor');
        obs = $("#afa_obs_" + leg + "_" + escLeg + "_" + n).html();

        $.ajax({
            type: "POST",
            data: {opr: opr, dia: dia, tipo: tipo, obs: obs, leg: leg, escLeg: escLeg, n: n},
            url: 'funcBD/escalaImpressao/modalAFA.php',
            success: function (data) { //O HTML é retornado em 'data'
                $("#conteudo_modal_ed").html(data);
                $("#botao_modal").click();
                reativar();
            }
        });
    }

    function editarEXP(leg, escLeg, n) {
        opr = $("#exp_opr_" + leg + "_" + escLeg + "_" + n).html();
        dia = $("#exp_dia_" + leg + "_" + escLeg + "_" + n).html();
        obs = $("#exp_obs_" + leg + "_" + escLeg + "_" + n).html();

        $.ajax({
            type: "POST",
            data: {opr: opr, dia: dia, obs: obs, leg: leg, escLeg: escLeg, n: n},
            url: 'funcBD/escalaImpressao/modalEXP.php',
            success: function (data) { //O HTML é retornado em 'data'
                $("#conteudo_modal_ed").html(data);
                $("#botao_modal").click();
                reativar();
            }
        });
    }
    function salvarDados() {
        arq = $("#informacoes").attr('arq');
        nome = $("#nome").val();
        localidade = $("#localidade").val();
        adjunto = $("#adjunto").val();
        escalante = $("#escalante").val();
        chefe = $("#chefe").val();
        chefeDO = $("#chefe_do").val();

        sva = [];
        afa = [];
        exp = [];
        ch_instrucao = [];
        detalhes_instrucao = [];
        observacoes = [];
        $(".sva").each(function (n) {
            leg = $(this).attr('legenda');
            escLeg = $(this).attr('escala');
            opr = $("#opr_" + leg + "_" + escLeg + "_" + n).html();
            dia = $("#dia_" + leg + "_" + escLeg + "_" + n).html();
            posto = $("#posto_" + leg + "_" + escLeg + "_" + n).attr('valor');
            postoTexto = $("#posto_" + leg + "_" + escLeg + "_" + n).html();
            obs = $("#obs_" + leg + "_" + escLeg + "_" + n).html();
            sva.push({escLeg: escLeg, leg: leg, opr: opr, dia: dia, posto: posto, postoTexto: postoTexto, obs: obs});
        });
        $(".afa").each(function (n) {
            leg = $(this).attr('legenda');
            escLeg = $(this).attr('escala');
            opr = $("#afa_opr_" + leg + "_" + escLeg + "_" + n).html();
            dia = $("#afa_dia_" + leg + "_" + escLeg + "_" + n).html();
            tipo = $("#afa_tipo_" + leg + "_" + escLeg + "_" + n).attr('valor');
            tipoTexto = $("#afa_tipo_" + leg + "_" + escLeg + "_" + n).html();
            obs = $("#afa_obs_" + leg + "_" + escLeg + "_" + n).html();
            afa.push({escLeg: escLeg, leg: leg, opr: opr, dia: dia, tipo: tipo, tipoTexto: tipoTexto, obs: obs});
        });
        $(".exp").each(function (n) {
            leg = $(this).attr('legenda');
            escLeg = $(this).attr('escala');
            opr = $("#exp_opr_" + leg + "_" + escLeg + "_" + n).html();
            dia = $("#exp_dia_" + leg + "_" + escLeg + "_" + n).html();
            obs = $("#exp_obs_" + leg + "_" + escLeg + "_" + n).html();
            exp.push({escLeg: escLeg, leg: leg, opr: opr, dia: dia, obs: obs});
        });
        $(".escalaInfo").each(function () {
            escLeg = $(this).attr('escala');
            ch_instrucao.push({escLeg: escLeg, valor: $("#ch_instrucao_" + escLeg).val()});
            detalhes_instrucao.push({escLeg: escLeg, valor: $("#texto_instrucao_" + escLeg).val()});
            observacoes.push({escLeg: escLeg, valor: $("#texto_observacoes_" + escLeg).val()});
        });
        enviar = {
            arq: arq,
            nome: nome,
            localidade: localidade,
            adjunto: adjunto,
            escalante: escalante,
            chefe: chefe,
            chefeDO: chefeDO,
            sva: sva,
            ch_instrucao: ch_instrucao,
            detalhes_instrucao: detalhes_instrucao,
            afa: afa,
            exp: exp,
            observacoes: observacoes
        };

        $.ajax({
            async: false,
            type: "POST",
            data: enviar,
            url: 'funcBD/escalaImpressao/salvarDados.php',
            success: function (data) { //O HTML é retornado em 'data'

            }
        });
    }

    $(function () {//impede que seja digitado numero no input
        $(".soNumero").bind('keydown', function (e) {
            //teclas permitidas (tab,delete,backsapace,setas)
            keyCodesPermitidos = new Array(8, 9, 37, 39, 46);
            //numero de 0 a 9 do teclado alfanumerico
            for (x = 48; x <= 57; x++) {
                keyCodesPermitidos.push(x);
            }
            //numero de 0 a 9 do teclado numerico
            for (x = 96; x <= 105; x++) {
                keyCodesPermitidos.push(x);
            }
            //pega a tecladigitada
            keyCode = e.which;
            //verifica se a tecla digitada é permitida
            if ($.inArray(keyCode, keyCodesPermitidos) != -1) {
                return true;
            }
            return false;
        });
    });
</script>