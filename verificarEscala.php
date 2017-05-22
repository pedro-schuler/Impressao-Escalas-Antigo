<?php
ini_set('default_charset','UTF-8');

session_start();
$orgao = $_SESSION['org'];
$ano = $_POST['ano'];
$mes = $_POST['mes'];
$tipo = $_POST['tipo'];


$caminho = "../../BD/".$orgao."/escalas/".$ano."_".$mes."_".$tipo.".txt";
if(!file_exists($caminho)){
    echo "0";
}else{ 
    
    //pega quais escalas fazem parte deste grupo
    $base = file($caminho);
    $linha0 = explode("<>", $base[0]);
    unset($linha0[0]);
    unset($linha0[sizeof($linha0)]);
    foreach($linha0 as $l){
        $lineex = explode("+", $l);
        $escalas[$lineex[0]] = $lineex[1];
    }
    //cria um select para determindar quais escalas q ele deseja criar a planilha
    ?>
    <div class="col-md-12">
        <label for="selecao">Escalas:</label>
        <select id='selecao' class='selectpicker form-control' data-width='auto' data-size='false' multiple arq='<?php echo $ano."_".$mes."_".$tipo;?>'><?php
            foreach($escalas as $e=>$n){?>
            <option value="<?php echo "$e|$n"?>"><?php echo $e;?></option>
        <?php }?>
        </select>
    </div>
    
<?php }
       