<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    if(isEmpty(DIR_ROOT)){
        FM::read(__DIR__);
    }else{
        FM::read(DIR_ROOT);
    }

    $di = FM::getDirectoryInfo();
    $dirs = $di->getDirectoryInfos();
    $files = $di->getFileInfos();
    
    HtmlEcho::HEAD($di->name());
    FM::echo_breadcrumb();
?>
<p><a href="./zip.php?i=<?= FM::$id; ?>&op=dl" onclick="return cnfZip()">DOWNLOAD (Zip)</a></p>
<hr>
<table>
<tbody>
<?php foreach($dirs as $sd): $i = FM::toId($sd->fullName()); ?>
<tr class="d">
<td class="n"><i class="icon icon-dir margin-r"></i> <a href="./dir.php?i=<?=$i;?>"><?= h($sd->name()); ?></a>
<td class="ft"><?php HtmlEcho::fileTime($sd->mTime()); ?></td>
<td class="ft"></td>
<td class="a"><a href="./zip.php?i=<?=$i;?>&op=dl" onclick="return cnfZip()">Zip</a></td>
<tr>
<?php endforeach; ?>
<?php foreach($files as $sf): $i = FM::toId($sf->fullName()); ?>
<tr class="f">
<td class="n"><i class="icon icon-file margin-r"></i> <a href="./file.php?i=<?=$i;?>"><?= h($sf->name()); ?></a>
<td class="ft"><?php HtmlEcho::fileTime($sf->mTime()); ?></td>
<td class="ft"><?= HtmlEcho::fileSize(filesize($sf->fullName())); ?></td>
<td class="a">
    <?php if(preg_match("/^.+\.dep\.ini$/", $sf->name())){ ?><a href="./deploy.php?i=<?=$i;?>">Deploy</a><?php } ?>
    <?php if($sf->extension(false) == "zip"){ ?><a href="./zip.php?i=<?=$i;?>">UnZip</a><?php } ?>
</td>
<tr>
<?php endforeach; ?>
</tbody>
</table>
</script>

<?php HtmlEcho::FOOT(); ?>