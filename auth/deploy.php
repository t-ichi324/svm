<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";

$msg = "";
$success = "";
$ini;
$ibase = isset($_GET["i"]) ? $_GET["i"] : (isset($_POST["i"]) ? $_POST["i"] : "");
$op = isset($_POST["op"]) ? $_POST["op"] : "";
$i = empty($ibase) ? "" : base64_decode($ibase);

$pi = pathinfo($i);
$ini = parse_ini_file($i, true);

function arrVal($arr, $key, $nullVal = "", $callback_func = null){
    $d = "";
    if(isset($arr[$key])){ $d = $arr[$key];}
    if(!empty($d) && $callback_func !== null){
        try{ $d = $callback_func($d); } catch (Exception $ex) { $d = ""; }
    }
    if(empty($d)){
        return $nullVal;
    }
    return $d;
}
function create_deploy($i){
    if(empty($i)){ return; }
    global $msg;
    global $ini;
    global $pi;
    if($ini === false){ return; }
    
    //timezone
    arrVal($ini, "timezone", null, function($d){ date_default_timezone_set($d); });
    
    $n = "";
    $n .= arrVal($ini, "name", pathinfo($pi["dirname"])["basename"]);
    $n .= arrVal($ini, "timestamp", date("_YmdHis"), function($d){ return date($d); });
    $n .= ".".arrVal($ini, "extension", "zip");

    $cd = getTmpDir();
    $zipfile = $cd.DIRECTORY_SEPARATOR.$n;
    
    $bool = ZipUtil::toZip($pi["dirname"], $zipfile, false, function ($name, $zdir){
        global $ini;
        $name = ltrim($name, "/");
        $ret = false;
        if($name == "" || $name == "/"){
            $ret = true;
        }else{
            if(isset($ini["include"]) && !empty($ini["include"])){
                foreach ($ini["include"] as $v){
                    if(is_array($v) || empty($v)){ continue; }
                    if( fnmatch(ltrim($v,"/"), $name) || strpos(ltrim($v,"/"), $name) === 0){
                        $ret = true;
                        break;
                    }
                }
            }else{
                $ret = true;
            }
        }
        
        if(isset($ini["ignore"]) && !empty($ini["ignore"])){
            foreach ($ini["ignore"] as $v){
                if(is_array($v) || empty($v)){ continue; }
                if( fnmatch(ltrim($v,"/"), $name) ){
                    return false;
                }
            }
        }
        return $ret;
    });
    if($bool){
        fileDownload($zipfile, $n);
        die();
    }else{
        $msg .= "error";
    }
}
function do_deploy($i){
    global $msg;
    global $success;
    global $ini;
    global $pi;
    
    $msg = "";
    if(!isset($_FILES["file"])){ $msg .= "ERROR: not found Upload file"; }
    if(!empty($msg)) { return; }

    if($ini === false){ $msg .= "ERROR: ini file not loaded"; }
    if(!empty($msg)) { return; }
    
    $fname = $_FILES["file"]["name"];
    $fext = pathinfo($fname)["extension"];
    
    $iname = arrVal($ini, "name", pathinfo($pi["dirname"])["basename"]);
    $iext = arrVal($ini, "extension", "zip");
    
    if(strpos($fname, $iname) !== 0){ $msg .= "ERROR: 'name' not match.\tPlease check ini."; }
    if(!empty($msg)) { return; }
    if($fext != $iext){ $msg .= "ERROR: 'extension' not match.\tPlease check ini."; }
    if(!empty($msg)) { return; }
    
    $zipfile = getTmpDir().DIRECTORY_SEPARATOR.$fname;
    move_uploaded_file($_FILES['file']['tmp_name'], $zipfile);
    
    if(ZipUtil::unZip($zipfile, $pi["dirname"])){
        $success .= "SUCCESS: ".$pi["dirname"];
        if(isset($ini["log"])){
            $f = $pi["dirname"].DIRECTORY_SEPARATOR.$ini["log"];
            error_log("#".date("Y-m-d H:i:s")." - ".$fname."\n", 3,$f);
        }
        return;
    }
    $msg .= "ERROR:";
    return;
}

if($op == "create"){ create_deploy($i); }
if($op == "do"){ do_deploy($i); }

echoHead(htmlspecialchars("DEPLOY"));

$log = "";
if(isset($ini) && $ini !== false){
    if(isset($ini["log"])){
        $f = $pi["dirname"].DIRECTORY_SEPARATOR.$ini["log"];
        if(file_exists($f)){ $log = file_get_contents($f); }
    }
}

?>
<p style="color:#f00"><?= htmlspecialchars($msg); ?></p>
<p style="color:#00f"><?= htmlspecialchars($success); ?></p>
<p>INI：<a href="./file.php?i=<?= $ibase; ?>"><?= htmlspecialchars($i); ?></a></p>
<?php if(!empty($i)){ ?>
<hr>
<h2>Archive</h2>
<form method="post">
    <input type="hidden" name="i" value="<?= $ibase; ?>">
    <input type="hidden" name="op" value="create">
    <!--
    <textarea name="note" style="white-space: pre;border: 1px solid #888;overflow: auto;height: 200px;width: 80%;padding: 1em" placeholder="deploy note." required></textarea><br>
    -->
    <button type="submit">Download</button>
</form>
<hr>
<h2>Delivery</h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="i" value="<?= $ibase; ?>">
    <input type="hidden" name="op" value="do">
    <input type="file" name="file" required>
    <br>
    <br>
    <button type="submit">Upload</button>
</form>
<hr>
<pre style="white-space: pre;border: 1px solid #eee;overflow: auto;height: 200px;"><?php echo htmlspecialchars($log); ?></pre>
<?php } ?>
<?php echoFoot(); ?>