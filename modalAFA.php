<?php
ini_set('default_charset', 'UTF-8');

$opr = $_POST['opr'];
$dia = $_POST['dia'];
$tipo = $_POST['tipo'];
$obs = $_POST['obs'];
$leg = $_POST['leg'];
$escLeg = $_POST['escLeg'];
$n = $_POST['n'];
?>
<div class="modal-header">
    <button id='fechar_modal' type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">AFASTAMENTO</h4>
    <h4 class="modal-title" id="myModalLabel">
        <?php echo $opr . " - " . $dia; ?>
    </h4>
</div>
<div class="modal-body">
    <label>TIPO:</label>
    <br>
    <select id='tipoAFASel' class="selectpicker">
        <optgroup label="FÉRIAS">
            <option value="0" <?php echo $tipo == 0 ? 'selected' : ''; ?>>FÉRIAS</option>
            <option value="1" <?php echo $tipo == 1 ? 'selected' : ''; ?>>DESCONTO EM FÉRIAS</option>
        </optgroup>
        <optgroup label="LICENÇAS">
            <option value="2" <?php echo $tipo == 2 ? 'selected' : ''; ?>>ESPECIAL</option>
            <option value="3" <?php echo $tipo == 3 ? 'selected' : ''; ?>>PARA TRATAR DE INTERESSE PARTICULAR</option>
            <option value="4" <?php echo $tipo == 4 ? 'selected' : ''; ?>>PARA TRATAMENTO DE SAÚDE PRÓPRIA</option>
            <option value="5" <?php echo $tipo == 5 ? 'selected' : ''; ?>>PARA TRATAMENTO DE SAÚDE DE DEPENDENTES</option>
            <option value="6" <?php echo $tipo == 6 ? 'selected' : ''; ?>>PATERNIDADE</option>
            <option value="7" <?php echo $tipo == 7 ? 'selected' : ''; ?>>MATERNIDADE</option>
        </optgroup>
        <optgroup label="DISPENSAS">
            <option value="8" <?php echo $tipo == 8 ? 'selected' : ''; ?>>COMO RECOMPENSA</option>
            <option value="9" <?php echo $tipo == 9 ? 'selected' : ''; ?>>EM DECORRÊNCIA DE PRESCRIÇÃO MÉDICA</option>
            <option value="10" <?php echo $tipo == 10 ? 'selected' : ''; ?>>POR MOTIVO DE FORÇA MAIOR</option>
            <option value="11" <?php echo $tipo == 11 ? 'selected' : ''; ?>>POR ORDEM SUPERIOR</option>
        </optgroup>
        <optgroup label="OUTROS">
            <option value="12" <?php echo $tipo == 12 ? 'selected' : ''; ?>>NÚPCIAS</option>
            <option value="13" <?php echo $tipo == 13 ? 'selected' : ''; ?>>LUTO</option>
            <option value="14" <?php echo $tipo == 14 ? 'selected' : ''; ?>>INSTALAÇÃO</option>
            <option value="15" <?php echo $tipo == 15 ? 'selected' : ''; ?>>CURSO</option>
            <option value="16" <?php echo $tipo == 16 ? 'selected' : ''; ?>>INSPEÇÃO DE SAÚDE (FORA DE SEDE)</option>
            <option value="17" <?php echo $tipo == 17 ? 'selected' : ''; ?>>CUMPRIMENTO DE ORDEM DE SERVIÇO</option>
            <option value="18" <?php echo $tipo == 18 ? 'selected' : ''; ?>>MILITAR DE OUTRA OM PRESTANDO SERVIÇO</option>
            <option value="19" <?php echo $tipo == 19 ? 'selected' : ''; ?>>MUDANÇA DE ESCALA</option>
            <option value="20" <?php echo $tipo == 20 ? 'selected' : ''; ?>>TRANSFERIDO</option>
            <option value="21" <?php echo $tipo == 21 ? 'selected' : ''; ?>>JUNTA ESPECIAL DE SAÚDE</option>
            <option value="22" <?php echo $tipo == 22 ? 'selected' : ''; ?>>CONCURSO PÚBLICO</option>
            <option value="23" <?php echo $tipo == 23 ? 'selected' : ''; ?>>SITUAÇÕES ESPECIAIS</option>
            <option value="24" <?php echo $tipo == 24 ? 'selected' : ''; ?>>EXPEDIENTE ADMINISTRATIVO</option>
        </optgroup>
    </select>
    <br>
    <label>OBS:</label>
    <br>
    <input id='obsAFA' class="form-control" placeholder="OBSERVAÇÕES SOBRE O AFASTAMENTO" <?php echo $obs != "" ? "value='$obs'" : ""; ?>>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" onclick="inserirAFALinha('<?php echo $leg; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>')">INSERIR</button>
</div>
<script>
    function inserirAFALinha(leg, escLeg, n) {
        tipo = $("#tipoAFASel").val();
        obs = $("#obsAFA").val();
        switch (tipo) {
            case '0':
                tipoTexto = "FÉRIAS";
                break;
            case '1':
                tipoTexto = "DESCONTO EM FÉRIAS";
                break;
            case '2':
                tipoTexto = "LICENÇA ESPECIAL";
                break;
            case '3':
                tipoTexto = "LICENÇA PARA TRATAR DE INTERESSE PARTICULAR";
                break;
            case '4':
                tipoTexto = "LICENÇA PARA TRATAMENTO DE SAÚDE PRÓPRIA";
                break;
            case '5':
                tipoTexto = "LICENÇA PARA TRATAMENTO DE SAÚDE DE DEPENDENTES";
                break;
            case '6':
                tipoTexto = "LICENÇA PATERNIDADE";
                break;
            case '7':
                tipoTexto = "LICENÇA MATERNIDADE";
                break;
            case '8':
                tipoTexto = "DISPENSA COMO RECOMPENSA";
                break;
            case '9':
                tipoTexto = "DISPENSA EM DECORRÊNCIA DE PRESCRIÇÃO MÉDICA";
                break;
            case '10':
                tipoTexto = "DISPENSA POR MOTIVO DE FORÇA MAIOR";
                break;
            case '11':
                tipoTexto = "DISPENSA POR ORDEM SUPERIOR";
                break;
            case '12':
                tipoTexto = "NÚPCIAS";
                break;
            case '13':
                tipoTexto = "LUTO";
                break;
            case '14':
                tipoTexto = "INSTALAÇÃO";
                break;
            case '15':
                tipoTexto = "CURSO";
                break;
            case '16':
                tipoTexto = "INSPEÇÃO DE SAÚDE (FORA DE SEDE)";
                break;
            case '17':
                tipoTexto = "CUMPRIMENTO DE ORDEM DE SERVIÇO";
                break;
            case '18':
                tipoTexto = "MILITAR DE OUTRA OM PRESTANDO SERVIÇO";
                break;
            case '19':
                tipoTexto = "MUDANÇA DE ESCALA";
                break;
            case '20':
                tipoTexto = "TRANSFERIDO";
                break;
            case '21':
                tipoTexto = "JUNTA ESPECIAL DE SAÚDE";
                break;
            case '22':
                tipoTexto = "CONCURSO PÚBLICO";
                break;
            case '23':
                tipoTexto = "SITUAÇÕES ESPECIAIS";
                break;
            case '24':
                tipoTexto = "EXPEDIENTE ADMINISTRATIVO";
                break;
        }
        $("#afa_tipo_" + leg + "_" + escLeg + "_" + n).attr('valor', tipo);
        $("#afa_tipo_" + leg + "_" + escLeg + "_" + n).html(tipoTexto);
        $("#afa_obs_" + leg + "_" + escLeg + "_" + n).html(obs);
        $("#fechar_modal").click();
    }
</script>