<?php
ini_set('default_charset', 'UTF-8');

$opr = $_POST['opr'];
$dia = $_POST['dia'];
$posto = $_POST['posto'];
$obs = $_POST['obs'];
$leg = $_POST['leg'];
$escLeg = $_POST['escLeg'];
$n = $_POST['n'];
?>
<div class="modal-header">
    <button id='fechar_modal' type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">RISAER</h4>
    <h4 class="modal-title" id="myModalLabel">
        <?php echo $opr . " - " . $dia; ?>
    </h4>
</div>
<div class="modal-body">
    <label>POSTO:</label>
    <br>
    <select id='postoSVASel' class="selectpicker">
        <option value="0" <?php echo $posto == 0 ? 'selected' : ''; ?>>OFICIAL DE DIA</option>
        <option value="1" <?php echo $posto == 1 ? 'selected' : ''; ?>>ADJUNTO AO OFICIAL DE DIA</option>
        <option value="2" <?php echo $posto == 2 ? 'selected' : ''; ?>>ADJUNTO AO OFICIAL DE OPERAÇÕES</option>
        <option value="3" <?php echo $posto == 3 ? 'selected' : ''; ?>>SARGENTO DE DIA</option>
        <option value="4" <?php echo $posto == 4 ? 'selected' : ''; ?>>COMANDANTE DA GUARDA</option>
    </select>
    <br>
    <label>OBS:</label>
    <br>
    <input id='obsSVA' class="form-control" placeholder="OBSERVAÇÕES SOBRE O SERVIÇO" <?php echo $obs != "" ? "value='$obs'" : ""; ?>>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" onclick="inserirSVALinha('<?php echo $leg; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>')">INSERIR</button>
</div>
<script>
    function inserirSVALinha(leg, escLeg, n) {
        posto = $("#postoSVASel").val();
        obs = $("#obsSVA").val();
        switch (posto) {
            case '0':
                postoTexto = "OFICIAL DE DIA";
                break;
            case '1':
                postoTexto = "ADJUNTO AO OFICIAL DE DIA";
                break;
            case '2':
                postoTexto = "ADJUNTO AO OFICIAL DE OPERAÇÕES";
                break;
            case '3':
                postoTexto = "SARGENTO DE DIA";
                break;
            case '4':
                postoTexto = "COMANDANTE DA GUARDA";
                break;
        }
        $("#posto_" + leg + "_" + escLeg + "_" + n).attr('valor', posto);
        $("#posto_" + leg + "_" + escLeg + "_" + n).html(postoTexto);
        $("#obs_" + leg + "_" + escLeg + "_" + n).html(obs);
        $("#fechar_modal").click();
    }
</script>