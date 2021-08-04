<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";

// phpinfo
if(isset($_GET["info"])){ echo phpinfo(); die(); }

if(isset($_GET["ini"])){ Response::textShow(file_get_contents(php_ini_loaded_file())); die(); }


$fnote = Path::tmp("-save-note.txt");
if(isset($_POST["note"])){ file_put_contents($fnote, $_POST["note"]); Message::$err = "note saved."; }

$txt = ""; $line = 6;
if(file_exists($fnote)){ $txt = file_get_contents($fnote); $c = count(explode("\n", $txt)); if($line < $c) {$line=$c;} }

HtmlEcho::HEAD("SERVER MANAGER");

?>
<form method="post"><textarea name="note" class="freenote" placeholder="Free Note" style="height: <?=$line;?>em;"><?= h($txt); ?></textarea>
<br><button type="submit">SAVE</button></form>

<hr>
<ul>
<li><a href="./dir.php">File Manager</a></li>
<li><a href="./file.php?i=<?= FM::toId(Path::tmp("access.log")); ?>">access.log</a></li>
</ul>

<hr>
<table><tbody>
<tr><td>PHP</td><td><?= h(phpversion()); ?></td></tr>
<tr><td>TIMEZONE</td><td><?= h(date_default_timezone_get()); ?></td></tr>
<tr><td>SERVER IP</td><td><?= $_SERVER["SERVER_ADDR"] . " : " . $_SERVER["SERVER_PORT"]; ?></td></tr>
<tr><td>USER IP</td><td><?= $_SERVER["REMOTE_ADDR"] . " : " . $_SERVER["REMOTE_PORT"]; ?></td></tr>
</tbody></table>

<hr>
<table><tbody>
<tr><td>OS</td><td><?= h(php_uname()); ?></td></tr>
<tr><td>USER</td><td><?= h(get_current_user()); ?></td></tr>
<tr><td>DISK</td><td><?= number_format(disk_free_space("/")); ?> byte / <?= number_format(disk_total_space("/")); ?> byte</td></tr>
<tr><td>MEM</td><td><?= number_format(memory_get_usage()); ?> byte / <?= number_format(memory_get_usage(true)); ?> byte</td></tr>
</tbody></table>

<hr>
<table><tbody>
<tr><td>php.ini</td><td><a href="./index.php?ini" target="_blank"><?= h(php_ini_loaded_file()); ?></a></td></tr>
<tr><td>MEM_LIMIT</td><td><?= h(ini_get("memory_limit")) ?></td></tr>
<tr><td>POST_MAX</td><td><?= h(ini_get("post_max_size")) ?></td></tr>
<tr><td>UPLOAD_MAX</td><td><?= h(ini_get("upload_max_filesize")) ?></td></tr>
</tbody></table>

<hr>
<table><tbody><?php foreach($_SERVER as $k => $v){ ?><tr><td><?= h($k); ?></td><td><?= h($v); ?></td></tr><?php } ?></tbody></table>

<?php HtmlEcho::FOOT(); ?>