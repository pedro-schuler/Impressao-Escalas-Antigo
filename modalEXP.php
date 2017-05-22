<?php
ini_set('default_charset', 'UTF-8');

$opr = $_POST['opr'];
$dia = $_POST['dia'];
$obs = $_POST['obs'];
$leg = $_POST['leg'];
$escLeg = $_POST['escLeg'];
$n = $_POST['n'];
?>
<div class="modal-header">
    <button id='fechar_modal' type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h4 class="modal-title" id="myModalLabel">EXPEDIENTE</h4>
    <h4 class="modal-title" id="myModalLabel">
        <?php echo $opr . " - " . $dia; ?>
    </h4>
</div>
<div class="modal-body">
    <label>OBS:</label>
    <br>
    <input id='obsEXP' class="form-control" placeholder="OBSERVAÇÕES" <?php echo $obs != "" ? "value='$obs'" : ""; ?>>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success" onclick="inserirEXPLinha('<?php echo $leg; ?>', '<?php echo $escLeg; ?>', '<?php echo $n; ?>')">INSERIR</button>
</div>
<script>
    function inserirEXPLinha(leg, escLeg, n) {
        obs = $("#obsEXP").val();
        $("#exp_obs_" + leg + "_" + escLeg + "_" + n).html(obs);
        $("#fechar_modal").click();
    }
</script>