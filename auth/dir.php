<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    $d = formVal("i","");
    if($d == ""){ $d = realpath(__DIR__."/.."); }else{ $d = base64_decode($d); }
    
    if(!file_exists($d)){
        echoHead(htmlspecialchars("404 NOT FOUND"));
        echo htmlspecialchars($d);
        echoFoot();
        die();
    }
    
    $my = pathinfo($d);
    if(isset($_GET["op"])){
        $op = $_GET["op"];
        //Download
        if($op == "zip"){
            $zipfile = toZip($d);
            if($zipfile !== null){
                header("Location: ./file.php?i=".base64_encode($zipfile));
            }
        }
    }
    
    $r = getDir($d);
    $dirs = $r["dirs"];
    $files = $r["files"];
    
    $name = $my["basename"];
    echoHead(htmlspecialchars($name));
    echoPathTbl($d);
    
?>
<p><a href="./zip.php?t=1&i=<?= base64_encode($d); ?>" onclick="return cnfZip()" >DOWNLOAD (Zip)</a></p>
<hr>
<table>
<tbody>
<?php foreach($dirs as $k => $v): ?>
<tr class="d">
<td class="c">Dir:</td>
<td class="n"><a href="./dir.php?i=<?= urlencode($k); ?>"><?= $v["name"]; ?></a>
<td class="c"></td>
<td class="ft"><?php echoTime($v["time"]); ?></td>
<td class="a"><?php if($v["name"] != ".."){ ?><a href="./zip.php?t=1&i=<?= $k; ?>" onclick="return cnfZip()" >Zip</a><?php } ?></td>
<tr>
<?php endforeach; ?>
<?php foreach($files as $k => $v): ?>
<tr class="f">
<td class="c">File:</td>
<td class="n"><a href="./file.php?i=<?= urlencode($k); ?>"><?= $v["name"]; ?></a>
<td class="c" style="text-align: right"><small><?= $v["size"] ?> byte</small></td>
<td class="ft"><?php echoTime($v["time"]); ?></td>
<td class="a">
    <?php if(preg_match("/^.+\.dep\.ini$/", $v["name"])){ ?><a href="./deploy.php?i=<?= $k; ?>">Deploy</a><?php } ?>
    <?php if($v["ext"] == "zip"){ ?><a href="./zip.php?i=<?= $k; ?>">UnZip</a><?php } ?>
</td>
<tr>
<?php endforeach; ?>
</tbody>
</table>

<?php echoFoot(); ?>