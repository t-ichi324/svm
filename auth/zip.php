<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";


    function toZip($dir, $zipfile = null){
        if(!is_dir($dir)){ return null; }
        $inf = pathinfo($dir);
        $nm = $inf["basename"];
        if(empty($zipfile)){
            $cd = __DIR__.DIRECTORY_SEPARATOR.".tmp";
            if(!is_dir($cd)){ mkdir($cd, 0777);}
            $zipfile = $cd.DIRECTORY_SEPARATOR.$nm."_".date("Ymd_his").".zip";
        }
        if(ZipUtil::toZip($dir, $zipfile) === true){
            return $zipfile;
        }
        return null;
    }
    function unZip($zipfile, $dir = null){
        if(!is_file($zipfile)){ return null; }

        $inf = pathinfo($zipfile);
        $nm = $inf["filename"];

        if(empty($dir)){
            $dir = $inf["dirname"].DIRECTORY_SEPARATOR.$nm;
            if(!is_dir($dir)){ mkdir($dir, 0777, true);}
        }
        if(ZipUtil::unZip($zipfile, $dir) === true){
            return $dir;
        }
        return null;
    }


    $i = isset($_GET["i"]) ? base64_decode($_GET["i"]) : "";
    if(!file_exists($i)){
        echoHead(htmlspecialchars("404 NOT FOUND"));
        echo htmlspecialchars($i);
        echoFoot();
        die();
    }
    
    $msg = "";
    $title = "UN ZIP";
    
    if(isset($_GET["t"])){
        $title = "TO ZIP";
        if(is_dir($i)){
            if($_GET["t"] == "1"){
                //create zip
                try{
                    $zipfile = toZip($i);
                    //$zipfile = toDeployZip($i, array("/^\.db$/", "/^\.db$/", "/^\.tmp$/", "/\.log$/"));
                    if($zipfile !== null){
                        fileDownload($zipfile);
                        die();
                    }
                } catch (Exception $ex) {
                    $msg .= "ERR: ".$ex->getMessage();
                }
            }
            $msg .= "ERR: can not create zip. [ ".$i." ]";
        }else{
            $msg .= "ERR: can not found directory. [ ".$i." ]";
        }
    }
    
    if(isset($_GET["u"])){
        $u = $_GET["u"];
        if(is_file($i)){
            $re = unZip($i, $u);
            if(!empty($re)){
                header("Location: ./dir.php?i=".base64_encode($re));
                die();
            }
            $msg .= "ERR: undefined error. [ ".$i." ]";
        }else{
            $msg .= "ERR: can not found file. [ ".$i." ]";
        }
    }
    
    $inf = pathinfo($i);
    $dir = $inf["dirname"].DIRECTORY_SEPARATOR.$inf["filename"];
    echoHead($title);
    
    echoPathTbl($i);
?>
<hr>
<p style="color:#f00"><?= htmlspecialchars($msg); ?></p>
<?php if($title == "UN ZIP"){ ?>
<form method="get" action="./zip.php">
<table >
<tbody>
<tr>
<td>UnZip To:</td>
<td style="min-width: 40em"><input type="text" style="width: 100%; padding: 5px 0;" name="u" value="<?= htmlspecialchars($dir); ?>"></td>
</tr>
<tr>
<td colspan="2" style="text-align: center">
<input type="hidden" name="i" value="<?= base64_encode($i); ?>">
<button type="submit" style="margin-top: 20px; width: 100%; padding: 0.5em;">UN-ZIP</button>
</td>
</tr>
</tbody>
</table>
</form>
<?php } ?>
<?php echoFoot(); ?>