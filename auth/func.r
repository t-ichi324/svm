<?php
writeLog();

function echoHead($title = "PAGE"){
echo "<!DOCTYPE html><html lang='en'><head><title>{$title}</title><meta charset='UTF-8'>";
echo "<style>
body{font-family:Arial; font-size: 1rem; font-weight: 400; line-height: 1.5; color: #212529; }
h1{ font-size: 25px; }
h2{ font-size: 18px; margin: 2em 0 1em; border-left: 5px solid #eee; padding-left: 0.75rem; }
.menu{ padding: 10px;  border-bottom: 1px solid #eee; }
.menu a{display:inline-block; margin-right: 10px; padding: 0.25rem 0.5em; border: 1px solid #666;  }
.wrap{box-sizing: border-box; margin: 20px;}
td{padding:5px 10px; border-bottom: 1px solid #eee;}
tr.p td{background-color: #f0f8ff; }
tr.d td{background-color: #f5f5dc; }
tr.f td{background-color: #e9ecef; }
td.c{ min-width:4em; }
td.n{ min-width:12em; }
td.a{ min-width:2em; text-align:center; font-size:0.8em; }
td.ft{ font-size:0.8rem; text-align: right;}
form table { margin-bottom:1.5rem; }
form button[type='submit'] { margin-bottom: 2rem; }
.pi{ padding-left: 5px; margin: 10px 0; }
pre{border:1px solid #eee; padding: 1em; overflow-y: auto; height: 50vh; }
.no-pre {border:1px solid #eee; padding: 1em; height: 50vh; background-color:#fafafa; text-align:center; }
.tgl-nav{box-sizing: border-box; width:100%; margin: 0; padding:0.5rem; background-color: #fafafa; border: 1px solid #eee; }
.tgl-radio{margin:.5rem; }
.tgl-pnl{box-sizing: border-box; withd:100%; }
.tips{box-sizing: border-box; font-size: 0.8rem; margin-top:1rem; background: #fafafa; padding: .5rem;border:1px solid #eee;}
</style></head><body>";
echo "<div class='menu'>"
. "<a href='./index.php'>Index</a>"
. "<a href='./dir.php'>File Manager</a>"
. "<a href='./net.php'>Network</a>"
. "<a href='./sfiles.php'>Setting Files</a>"
. "<a href='./sql.php'>MySql</a>"
. "<a href='./dump.php'>Dump</a>"
. "<a href='./basic.php'>Basic Auth</a>"
. "<a href='./index.php?phpinfo'>PHP Info</a>"
. "</div><div class='wrap'><h1>{$title}</h1>";
}
function echoFoot(){
echo "</div><script>"
. "function cnfZip(){ return confirm('Do you want to download this folder as Zip?'); }"
. "function cnfFile(){ return confirm('Do you want to download this file?'); }"
. "function tglPnl(showId, pnlClass){ var cls = (pnlClass) ? pnlClass : 'tgl-pnl'; var matches = document.getElementsByClassName(cls); for(var i=0; i<matches.length; i++){ var ele = matches[i]; ele.style.display = 'none'; } document.getElementById(showId).style.display = 'block'; }"
. "</script></body></html>";
}
function formVal($name, $defaultVal = null){
    if(isset($_POST[$name])){
        return trim($_POST[$name]);
    }
    if(isset($_GET[$name])){
        return trim($_GET[$name]);
    }
    return $defaultVal;
}
function hasPost(... $names){
    if(empty($names)){ return false; }
    foreach($names as $n){
        if(!isset($_POST[$n])){
            return false;
        }
    }
    return true;
}
function toUtf8($str, $htmlEnc = false){
    $enc = mb_detect_encoding($str, ['ASCII', 'ISO-2022-JP', 'UTF-8', 'EUC-JP', 'SJIS'], true);
    if ($enc === false){ $enc = 'SJIS'; }
    $v = mb_convert_encoding($str, 'UTF-8', $enc);
    if($htmlEnc){
        return htmlspecialchars($v);
    }
    return $v;
}
function tglPnlRadio($id, $txt, $run){
    echo "<label class='tgl-radio'><input type='radio' name='tgl_radio' onclick=". '"tglPnl('. "'". $id. "'". ');"';
    if($id == $run){ echo " checked"; }
    echo ">".htmlspecialchars($txt)."</label>";
}
function tglPnlAttr($id, $run){
    echo " class='tgl-pnl' id='$id'";
    if($id == $run){
        echo " style='display:block;'";
    }else{
        echo " style='display:none;'";
    }
}
function lineToArray($txt){
    $ret = array();
    foreach(explode("\n", $txt) as $line){
        $v = trim($line);
        if($v != ""){ $ret[] = $v; }
    }
    return $ret;
}
function textShow($txt){
    header("Content-type: text/plain; charset=UTF-8");
    if(is_array($txt)){
        foreach($txt as $line){
            echo $line."\n";
        }
    }else{
        echo $txt;
    }
    die();
}
function textDownload($txt, $dlname = null){
    header('Content-Type: application/octet-stream');
    header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($dlname));
    if(is_array($txt)){
        foreach($txt as $line){
            echo $line."\n";
        }
    }else{
        echo $txt;
    }
    die();
}
function fileDownload($path, $dlname = null){
    if(is_file($path)){
        if($dlname == null){ $dlname = pathinfo($path)["basename"];  }
        
        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename*=UTF-8\'\''.rawurlencode($dlname));
        header('Content-Length: '.filesize($path));
        
        while(ob_get_level()){ ob_end_clean(); }
        ob_start();
        if($fp = fopen($path, 'rb')) {
            try{
                while(!feof($fp) and (connection_status() === 0)){
                    echo fread($fp, 1024); ob_flush(); flush();
                }
            } catch (Exception $ex) { }
            ob_flush();
            fclose($fp);
        }
        ob_end_clean();
    }else{
        http_response_code(404);
    }
    die();
}
function echoPathTbl($file){
    $pi = pathinfo($file);
    $parent = $pi["dirname"];
    $name = $pi["basename"];
    
    echo "<div class='pi'><small>".htmlspecialchars($file)."</small></div>";
    echo "<table><tbody><tr class='p'>";
    
    $arr = explode(DIRECTORY_SEPARATOR, $parent);
    $path = ""; $col = 0;
    foreach ($arr as $i){
        if($col > 0){ $path .= DIRECTORY_SEPARATOR; }
        $col++;
        $path.= $i;
        if(empty($i)) { continue; }
        echo '<td><a href="./dir.php?i='.base64_encode($path).'">'.toUtf8($i, true)."</a></td>";
    }
    echo "<td><strong>".toUtf8($name, true)."</strong></td></tr></tbody></table>";
}

function getDir($d){
    setlocale(LC_ALL, 'ja_JP.UTF-8');
    $files = array();
    $dirs = array();
    try{
        setlocale(LC_ALL, 'ja_JP.UTF-8');
        foreach(scandir($d) as $name){
            if($name == "." || $name == ".."){ continue; }
            $path = realpath($d.DIRECTORY_SEPARATOR.$name);
            
            if(is_dir($path)){
                $dirs[base64_encode($path)] = array("name"=>toUtf8($name), "path"=>$path, "ext"=>null, "size"=>null, "time"=>filemtime($path));
            }else{
                $inf = pathinfo($path);
                $ext = isset($inf["extension"]) ? $inf["extension"] : null;
                $files[base64_encode($path)] = array("name"=>toUtf8($name), "path"=>$path, "ext"=>$ext, "size"=>filesize($path), "time"=>filemtime($path));
            }
        }
    } catch (Exception $ex) {
    }
    return array("files"=>$files, "dirs"=>$dirs);
}

function getTmpDir(){
    $cd = __DIR__.DIRECTORY_SEPARATOR.".tmp";
    if(!is_dir($cd)){ mkdir($cd, 0777);}
    return $cd;
}

function echoTime($time, $show_full = false){
    if($time !== false && !is_int($time)){ echo "---"; return; }
    $td = date("Y-m-d");
    $full = date("Y-m-d h:i:s", $time);
    if($show_full){ echo $full; return; }
    echo "<span title='".$full."'>";
    $day = date("Y-m-d",$time);
    if($td == $day){
        echo date("h:i:s", $time);
    }else{
        echo $day;
    }
    echo "</span>";
}

/** copy from lib */
//include_once "./../lib/ZipUtil.php";
class ZipUtil {
    public static function unZip($zipfile, $dir){
        if(!is_file($zipfile)){ return false; }
        if(empty($dir)) { return false; }
        if(!is_dir($dir)){ mkdir($dir, 0777); }
        
        set_time_limit(0);
        $zip = new ZipArchive();
        if( $zip->open($zipfile) === true){
            $zip->extractTo($dir);
            $zip->close();
            return true;
        }
        return false;
    }
    
    public static function toZip($dir, $zipfile, $containsDirName = false, $callback_func = null){
        if(!is_dir($dir)){ return false; }
        if(empty($zipfile)) { return false; }
        
        set_time_limit(0);
        
        $result = array();
        $zdir = "";
        if($containsDirName && is_dir($dir)){ $zdir = pathinfo($dir)["basename"]; }
        self::preZip($result, $dir, $zdir, $callback_func);
        
        if(!empty($result)){
            $zip = new ZipArchive();
            if($zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true ){
                self::addZip($zip, $result);
                $zip->close();
                return true;
            }
        }
        return false;
    }
    
    private static function preZip(array &$result, $dir, $zdir, $callback_func = null){
        if(!file_exists($dir)){ return false; }
        $pi = pathinfo($dir);
        $base = $pi["basename"];
        
        if($callback_func !== null){
            try{
                if(is_dir($dir)){
                    if($callback_func($zdir."/", $zdir, $dir) === false){ return false; }
                }else{
                    if($callback_func($zdir."/".$base, $zdir) === false){ return false; }
                }
            } catch (Exception $ex) {
                return false;
            }
        }
        
        if(is_dir($dir)){
            $fs = scandir($dir);
            foreach($fs as $f){
                if(empty($f) || $f == "." || $f == ".."){ continue; }
                $path = $dir.DIRECTORY_SEPARATOR.$f;
                if(!file_exists($path)) { continue; }
                $key = (($zdir === "") ? "" : $zdir."/").$f;                
                if(is_file($path)){
                    self::preZip($result, $path, $zdir, $callback_func);
                    //$result[$key] = $path;
                }elseif(is_dir($path)){
                    $result[$key] = array();
                    if(self::preZip($result[$key], $path, $key, $callback_func) === false){
                        unset($result[$key]);
                    }
                }
            }
        }else{
            if(is_file($dir)){
                $key = (($zdir === "") ? "" : $zdir."/").$base; 
                $result[$key] = $dir;
            }
        }
        return true;
    }
    private static function addZip(ZipArchive &$zip, array $result){
        if(empty($result)){ return; }
        foreach($result as $k => $v){
            if(is_array($v)){
                $zip->addEmptyDir($k);
                self::addZip($zip, $v);
            }else{
                $zip->addFile($v, $k);
            }
        }
    }
}

function writeLog(){
    try{
        $s = date("Y-m-d H:i:s"). "\t"
        . $_SERVER["REMOTE_ADDR"]. "\t"
        . $_SERVER["REQUEST_METHOD"]. "\t"
        . $_SERVER["REQUEST_URI"]. "\t";
        if(isset($_POST)){
            $s.=json_encode($_POST);
        }elseif(isset($_GET)){
            $s.=json_encode($_GET);
        }
        $s.="\t";
        $s.=(isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : ""). "\t";
        error_log($s."\n", 3, getTmpDir().DIRECTORY_SEPARATOR."access.log");
    } catch (Exception $ex) {
    }
}

function isWindows(){ return (substr(PHP_OS,0,3) == 'WIN'); }
function sysCmd_echo(... $lines){
    $cmd = "";
    echo "<div class='cmd' style='font-family:monospace;background-color:#000; color:#fff; padding:.5rem; margin-bottom:.5rem;'>";
    if(is_array($lines)){
        foreach($lines as $v){
            echo htmlspecialchars($v)."<br>";
            if($cmd !== "") { $cmd.="\n"; }
            $cmd .= $v;
        }
    }else{
        $cmd = $lines;
        echo htmlspecialchars($cmd)."<br>";
    }
    echo "</div>";
    $opt = null;
    exec($cmd, $opt);
    echo "<div class='cmd' style='font-family:monospace;background-color:#333; color:#fff; padding:.5rem; margin-bottom:.5rem; margin-left: 1rem;'>";
    if(!empty($opt)){
        foreach($opt as $o){
            $line = toUtf8($o);
            if(trim($line) == "") { continue; }
            echo htmlspecialchars($line)."<br>";
        }
    }
    echo "</div>";
}

?>