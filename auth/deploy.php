<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    FM::read();
    $fi = FM::getFileInfo();
    Dat::$ini = parse_ini_file($fi->fullName(), true);
    $op = formval("op");
    if($op == "dl"){ create_deploy($fi); }
    if($op == "up"){ do_deploy($fi); }

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
    function create_deploy(FileInfo $fi){
        if(empty(Dat::$ini) || Dat::$ini === false){  Message::$err = "ERROR: ini file not loaded"; return; }

        //timezone
        arrVal(Dat::$ini, "timezone", null, function($d){ date_default_timezone_set($d); });

        $n = "";
        $n .= arrVal(Dat::$ini, "name", $fi->name(false));
        $n .= arrVal(Dat::$ini, "timestamp", date("_YmdHis"), function($d){ return date($d); });
        $n .= ".".arrVal(Dat::$ini, "extension", "zip");

        $zipfile = Path::tmp($n);

        $bool = ZipUtil::toZip($fi->baseDirectory(), $zipfile, false, function ($name, $zdir){
            $name = ltrim($name, "/");
            $ret = false;
            if($name == "" || $name == "/"){
                $ret = true;
            }else{
                if(isset(Dat::$ini["include"]) && !empty(Dat::$ini["include"])){
                    foreach (Dat::$ini["include"] as $v){
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
            if(isset(Dat::$ini["ignore"]) && !empty(Dat::$ini["ignore"])){
                foreach (Dat::$ini["ignore"] as $v){
                    if(is_array($v) || empty($v)){ continue; }
                    if( fnmatch(ltrim($v,"/"), $name) ){
                        return false;
                    }
                }
            }
            return $ret;
        });
        if($bool){
            Response::fileDownload($zipfile, $n);
            die();
        }else{
            Message::$err = "error : to zip";
        }
    }
    function do_deploy(FileInfo $fi){
        
        if(empty(Dat::$ini) || Dat::$ini === false){ Message::$err = "ERROR: ini file not loaded"; return; }
        if(!isset($_FILES["file"])){ Message::$err = "ERROR: not found Upload file"; return; }

        $fname = $_FILES["file"]["name"];
        $fext = pathinfo($fname)["extension"];

        $iname = arrVal(Dat::$ini, "name", $fi->name(false));
        $iext = arrVal(Dat::$ini, "extension", "zip");

        if(strpos($fname, $iname) !== 0){ Message::$err = "ERROR: 'name' not match.\tPlease check ini."; return; }
        if($fext != $iext){ Message::$err = "ERROR: 'extension' not match.\tPlease check ini."; return; }

        $zipfile = Path::tmp($fname);
        move_uploaded_file($_FILES['file']['tmp_name'], $zipfile);

        if(ZipUtil::unZip($zipfile, $fi->baseDirectory())){
            Message::$info = "SUCCESS: ".$fi->baseDirectory();
            if(isset(Dat::$ini["log"])){
                $f = $fi->baseDirectory().DIRECTORY_SEPARATOR.Dat::$ini["log"];
                error_log("#".date("Y-m-d H:i:s")." - ".$fname."\n", 3,$f);
            }
            return;
        }
        Message::$err = "ERROR:";
        return;
    }

    $log = "";
    if(!empty(Dat::$ini) && Dat::$ini !== false){
        if(isset(Dat::$ini["log"])){
            $f = $fi->baseDirectory().DIRECTORY_SEPARATOR.Dat::$ini["log"];
            if(file_exists($f)){ $log = file_get_contents($f); }
        }
    }
    
    HtmlEcho::HEAD("DEPLOY");
    FM::echo_breadcrumb();
?>
<p>dep.ini：<a href="./file.php?i=<?= FM::$id; ?>">Show</a></p>
<hr>
<h2>Archive</h2>
<form method="post">
    <input type="hidden" name="i" value="<?=FM::$id;?>">
    <input type="hidden" name="op" value="dl">
    <br>
    <button type="submit">Download</button>
</form>
<hr>
<h2>Delivery</h2>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="i" value="<?=FM::$id;?>">
    <input type="hidden" name="op" value="up">
    <input type="file" name="file" required>
    <br>
    <br>
    <button type="submit">Upload</button>
</form>
<hr>
<pre style="white-space: pre;border: 1px solid #eee;overflow: auto;height: 200px;"><?php echo h($log); ?></pre>

<?php HtmlEcho::FOOT(); ?>