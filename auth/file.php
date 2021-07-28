<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    $f = formVal("i","");
    if($f == ""){ $f = __FILE__; }else{ $f = base64_decode($f); }
    
    if(!file_exists($f)){
        echoHead(htmlspecialchars("404 NOT FOUND"));
        echo htmlspecialchars($f);
        echoFoot();
        die();
    }
    $my = pathinfo($f);
    $name = $my["basename"];
    $ext = isset($my["extension"]) ? $my["extension"] : null;
    $txtExts = ["txt","csv","xml","log","json","htm","html","php","js","css","htaccess","ini","cnf","config"];
    $imgExts = ["icon","png","jpg","gif","tif"];
    $d = $my["dirname"];
    
    if(isset($_GET["op"])){
        $op = $_GET["op"];
        //Download
        if($op == "dl"){
            fileDownload($f, $name);
            die();
        }
    }
    
    $run = "view";
    
    $fileSize = filesize($f);
    echoHead(htmlspecialchars($name));
    echoPathTbl($f);
?>
<p><a href="./file.php?op=dl&i=<?= base64_encode($f); ?>" onclick="return cnfFile()">DOWNLOAD (<?= filesize($f); ?> byte)</a></p>
<hr>
<?php if(in_array($ext, $txtExts)){ ?>
<div <?php tglPnlAttr("view", $run); ?>>
<pre><?= htmlspecialchars(file_get_contents($f)); ?></pre>
</div>
<!--
<div <?php tglPnlAttr("edit", $run); ?>>
    <form method="post">
        <input type="hidden" name="i" value="<?= htmlspecialchars(formVal("i","")); ?>">
        <textarea name="data" style="display: inline-block; box-sizing: border-box; width: 100%; height: 40vh; padding: 1em;"><?= htmlspecialchars(file_get_contents($f)); ?></textarea>
        <br>
        <button type="submit">save</button>
    </form>
</div>
<div class="tgl-nav"><?php tglPnlRadio("view", "view", $run); tglPnlRadio("edit", "edit", $run);?></div>
-->
<?php }elseif(in_array($ext, $imgExts)){ ?>
<div class="no-pre"><img style="max-width: 100%;max-height:100%;" src="data:image/<?= $ext; ?>;base64,<?= base64_encode(file_get_contents($f)); ?>" /></div>
<?php }else{ ?>
<div class="no-pre"><p style="color:#888;">NO SUPPORT PREVIEW</p></div>
<?php } ?>
<ul style="color: #888; font-size: 0.8rem;">
    <li><?= filesize($f); ?> byte</li>
    <li><?php echoTime(filemtime($f), true); ?></li>
</ul>
<?php echoFoot(); ?>