<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";

if(isset($_GET["phpinfo"])){
    echo phpinfo();
    die();
}

$tmp_dir = getTmpDir().DIRECTORY_SEPARATOR;
echoHead("SERVER MANAGER");

if(isset($_POST["note"])){
    file_put_contents($tmp_dir."note.txt", $_POST["note"]);
    echo "<script>alert('Save Note');</script>";
}
?>
<form method="post"><textarea name="note" style="border: none; background-color: #eee; padding: .5em; width: 90%; height: 8em;resize: none;" placeholder="Free Note"><?php
if(file_exists($tmp_dir."note.txt")){
    echo htmlspecialchars(file_get_contents($tmp_dir."note.txt")); 
}
?></textarea>
<br><button type="submit">SAVE</button></form>
<hr>
<ul>
    <li><a href="./dir.php">File Manager</a></li>
    <li><a href="./file.php?i=<?= htmlspecialchars(base64_encode(php_ini_loaded_file())); ?>">PHP.ini</a></li>
</ul>
<hr>
<table>
<tbody>
<tr><td>PHP</td><td><?= phpversion(); ?></td></tr>
<tr><td>TIMEZONE</td><td><?= date_default_timezone_get(); ?></td></tr>
<tr><td>LOCALE</td><td><?php print_r(setlocale(LC_ALL, '')); ?></td></tr>
<tr><td>SERVER IP</td><td><?= $_SERVER["SERVER_ADDR"]; ?> : <?= $_SERVER["SERVER_PORT"]; ?></td></tr>
<tr><td>USER IP</td><td><?= $_SERVER["REMOTE_ADDR"]; ?> : <?= $_SERVER["REMOTE_PORT"]; ?></td></tr>
</tbody>
</table>

<hr>
<table>
<tbody>
<tr><td>OS</td><td><?= php_uname(); ?></td></tr>
<tr><td>USER</td><td><?= get_current_user(); ?></td></tr>
<tr><td>DISK</td><td><?= number_format(disk_free_space("/")); ?> byte / <?= number_format(disk_total_space("/")); ?> byte</td></tr>
<tr><td>MEM</td><td><?= number_format(memory_get_usage()); ?> byte / <?= number_format(memory_get_usage(true)); ?> byte</td></tr>
<tr><td>MEM(PEAK)</td><td><?= number_format(memory_get_peak_usage()); ?> byte / <?= number_format(memory_get_peak_usage(true)); ?> byte</td></tr>
</tbody>
</table>

<hr>
<table>
<tbody>
<?php foreach($_SERVER as $k => $v){ ?>
<tr><td><?= htmlspecialchars($k); ?></td><td><?= htmlspecialchars($v); ?></td></tr>
<?php } ?>
</tbody>
</table>


<?php echoFoot(); ?>