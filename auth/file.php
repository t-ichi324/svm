<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    FM::read();
    $fi = FM::getFileInfo();
    
    if(formval("op") === "dl"){ Response::fileDownload($fi->fullName(), $fi->name()); die(); }
    
    $txtExts = ["txt","csv","xml","log","json","htm","html","php","js","css","htaccess","ini","cnf","config","md"];
    $imgExts = ["icon","png","jpg","gif","tif"];
    
    HtmlEcho::HEAD($fi->name());
    FM::echo_breadcrumb();
    
    $k = url64_encode($fi->fullName());
    $size = filesize($fi->fullName());
    $ext = $fi->extension(false);
?>
<p><a href="./file.php?i=<?=FM::$id;?>&op=dl" onclick="return cnfFile()">DOWNLOAD (<?= HtmlEcho::fileSize($size); ?>)</a></p>
<hr>
<?php if(in_array($ext, $txtExts)){ ?>
<div class="pre"><pre><?= h(file_get_contents($fi->fullName())); ?></pre></div>
<?php }elseif(in_array($ext, $imgExts)){ ?>
<div class="no-pre"><img style="max-width: 100%;max-height:100%;" src="data:image/<?= h($ext); ?>;base64,<?= base64_encode(file_get_contents($fi->fullName())); ?>" /></div>
<?php }else{ ?>
<div class="no-pre"><p style="color:#888;">NO SUPPORT PREVIEW</p></div>
<?php } ?>
<ul style="color: #888; font-size: 0.8rem;">
    <li><?= HtmlEcho::fileSize($size, false); ?></li>
    <li><?php HtmlEcho::fileTime($fi->mTime(), true); ?></li>
</ul>
<?php HtmlEcho::FOOT(); ?>