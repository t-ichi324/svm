<?php
/*
 * https://github.com/t-ichi324/svm
 * 
 * install php-zip
 * install php-mbstring
 * 
 * (opt)
 * install php-curl
*/

setlocale(LC_ALL, 'ja_JP.UTF-8');

const TMP_DIR = ".tmp";
const DIR_ROOT = "";
//const DIR_ROOT = __DIR__."/../../";

function formval($name, $defaultVal = null){ if(isset($_POST[$name])){ return trim($_POST[$name]); } if(isset($_GET[$name])){ return trim($_GET[$name]); } return $defaultVal; }
function isEmpty($str){ return (empty($str) && $str !== "0" && $str !== 0 && $str !== 0.0); }
function isNotEmpty($str){ return !(isEmpty($str)); }
function lineToArray($txt){ $ret = array(); foreach(explode("\n", $txt) as $line){ $v = trim($line); if($v != ""){ $ret[] = $v; } } return $ret; }
function isWindows(){ return (substr(PHP_OS,0,3) == 'WIN'); }

function h($string){ return htmlspecialchars($string, ENT_QUOTES, 'UTF-8'); }
function url64_encode($data){ return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); } 
function url64_decode($data){ return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); }

// ===================================================================
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
    if($htmlEnc){ return h($v); }
    return $v;
}
// ===================================================================
class Dat{
    public static $ini;
    public static $val;
}
class Message{
    public static $err = null;
    public static $info = null;
    public static function addError($msg){ self::$err[] = $msg; }
    public static function addInfo($msg){ self::$info[] = $msg; }
}
class HtmlEcho{
    public static function HEAD($title = "PAGE"){
        echo "<!DOCTYPE html><html lang='en'><head><title>".h($title)."</title><meta charset='UTF-8'>";
        echo "<link rel='stylesheet' href='./sys.css'/>";
        echo "</head><body>";
        echo "<div class='menu'>"
            . "<a href='./index.php'>Index</a>"
            . "<a href='./dir.php'>File Manager</a>"
            . "<a href='./net.php'>Network</a>"
            . "<a href='./sfiles.php'>Setting Files</a>"
            . "<a href='./sql.php'>MySql</a>"
            . "<a href='./dump.php'>Dump</a>"
            . "<a href='./basic.php'>Basic Auth</a>"
            . "<a href='./index.php?phpinfo'>PHP Info</a>";
        echo  "</div><div class='wrap'>";
        echo "<h1>".h($title)."</h1>";
        if(!empty(Message::$err)){
            echo "<ul style='color:#f00; margin: 1rem 0;'>";
            if(is_array(Message::$err)){
                foreach(Message::$err as $m){ echo "<li>".h($m)."</li>"; }
            }else{
                 echo "<li>".h(Message::$err)."</li>";
            }
            echo "</ul>";
        }
        if(!empty(Message::$info)){
            echo "<ul style='color:#00f; margin: 1rem 0;'>";
            if(is_array(Message::$info)){
                foreach(Message::$info as $m){ echo "<li>".h($m)."</li>"; }
            }else{
                 echo "<li>".h(Message::$info)."</li>";
            }
            echo "</ul>";
        }
    }
    public static function FOOT(){
        echo "</div>";
        echo "<script type='text/javascript' src='./sys.js'></script>";
        echo "</body></html>";
    }
    public static function NOT_FOUND(){
        self::HEAD("404 NOT FOUND");
        self::FOOT();
        die();
    }
    public static function tglPnl_radio($id, $txt, $run){
        echo "<label class='tgl-radio'><input type='radio' name='tgl_radio' onclick=". '"tglPnl('. "'". $id. "'". ');"';
        if($id == $run){ echo " checked"; }
        echo ">".h($txt)."</label>";
    }
    public static function tglPnl_attr($id, $run){
        echo " class='tgl-pnl' id='$id'";
        if($id == $run){
            echo " style='display:block;'";
        }else{
            echo " style='display:none;'";
        }
    }
    
    public static function fileTime($time, $show_full = false){
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
    public static function fileSize($size, $show_full = false){
        $uni = " B";
        $title = number_format($size)." byte";
        if($size > 1024){ $size = ($size / 1024); $uni = " KB"; }
        if($size > 1024){ $size = ($size / 1024); $uni = " MB"; }
        if($size > 1024){ $size = ($size / 1024); $uni = " GB"; }
        echo "<span title='". h($title)."'>".h(number_format(ceil($size)).$uni)."</span>";
    }
}

// ===================================================================
class Response{
    public static function redirect($url){
        header("Location: ".$url);
        die();
    }
    public static function textShow($txt){
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
    public static function textDownload($txt, $dlname = null){
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
    public static function fileDownload($path, $dlname = null){
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
}

class Path{
    public static function normalize($path){
        $hie = array();
        $trm = trim(str_replace("\\", "/", $path));
        $pre = "";
        for($i = 0; $i < strlen($trm); $i++) { if($trm[$i] !== "/"){ break; } $pre .= "/"; }
        $trm = trim($trm, "/");
        $arr = explode("/", $trm);
        foreach($arr as $p){
            if($p === "" || $p === "."){ continue; }
            if($p === ".."){ array_pop($hie); continue; }
            $hie[] = $p;
        }
        return $pre . implode(DIRECTORY_SEPARATOR, $hie);
    }
    
    public static function tmp(... $appends){
        $cd = __DIR__.DIRECTORY_SEPARATOR.TMP_DIR.DIRECTORY_SEPARATOR;
        if(!file_exists($cd)){ mkdir($cd, 0777); file_put_contents($cd.".htaccess", "Deny from all\n"); }
        if(!empty($appends)){ foreach($appends as $p){ $cd .= DIRECTORY_SEPARATOR.$p; } }
        return $cd;
    }
}

class FM{
    private static $droot = null;
    public static $id;
    public static $fullname;
    
    private static function _init_droot(){
        if(self::$droot === null){
            self::$droot = "";
            if(isNotEmpty(DIR_ROOT)){
                self::$droot = Path::normalize(DIR_ROOT);
                if(! file_exists(self::$droot) ){ self::$droot = "";}
            }
        }
    }
    public static function toPath($id){
        self::_init_droot();
        
        if(isEmpty($id)){ return null; }
        $relative = url64_decode($id);
        if(isEmpty($relative)){ return null; }
        if(isNotEmpty(self::$droot)){
            $fullpath = Path::normalize(self::$droot.DIRECTORY_SEPARATOR.$relative);
        }else{
            $fullpath = Path::normalize($relative);
        }
        if(isEmpty($fullpath)){ return null; }
        if(isNotEmpty(self::$droot) && strpos($fullpath, self::$droot) !== 0){ return null; }
        return $fullpath;
    }
    public static function toId($path){
        self::_init_droot();
        
        $fullpath = Path::normalize($path);
        if(isNotEmpty(self::$droot) && strpos($fullpath, self::$droot) !== 0){ return null; }
        $relative = substr($fullpath, strlen(self::$droot));
        return url64_encode($relative);
    }
    
    
    public static function read($default = null){
        self::_init_droot();
        
        self::$id = formval("i");
        if(isNotEmpty(self::$id)){
            self::$fullname = self::toPath(self::$id);
        }else{
            self::$fullname = Path::normalize($default);
        }
        if(self::$fullname === null || !file_exists(self::$fullname)){
             HtmlEcho::NOT_FOUND();
        }
    }

    public static function isFile(){ return is_file(self::$fullname); }
    public static function isDirectory(){ return is_dir(self::$fullname); }
    
    public static function getFileInfo(){ return new FileInfo(self::$fullname); }
    public static function getDirectoryInfo(){ return new DirectoryInfo(self::$fullname); }

    public static function echo_breadcrumb(){
        self::_init_droot();
        
        $full = rtrim(trim(self::$fullname), DIRECTORY_SEPARATOR);
        $arr = explode(DIRECTORY_SEPARATOR, $full);
        $max = count($arr);
        $path = "";
        echo "<div class='breadcrumb-path'>".h($full)."</div>";
        echo "<table class='breadcrumb-tbl'><tbody><tr>";
        for($i = 0; $i<$max-1; $i++){
            if($i > 0){ $path .= DIRECTORY_SEPARATOR; }
            $c = $arr[$i];
            if($path === "" && $c === ""){
                echo '<td><a href="./dir.php?i='.self::toId("/").'">'.h("/")."</a></td>";
            }else{
                $path .= $c;
                if(isNotEmpty(self::$droot) && strpos($path, self::$droot) !== 0){ continue; }
                echo '<td><a href="./dir.php?i='.self::toId($path).'">'.h($c)."</a></td>";
            }
        }
        echo "<td><strong>".h($arr[$max-1])."</strong></td></tr></tbody></table>";
    }
}

// ===================================================================

/** IO??????????????? */
abstract class __IO_Info{
    protected $full = null;
    protected $info = null;
    protected static function __IsExists($path, $isFile){
        $r = isNotEmpty($path) && file_exists($path);
        if($isFile){ return $r; }
        return $r && is_dir($path);
    }
    public function __construct($path) {
        $this->full = $path;
        $this->info = pathinfo($this->full);
    }
    protected function gi($n, $nv = ""){
        if($this->info === null){ return $nv; }
        if(isset($this->info[$n])){ return $this->info[$n]; }
        return $nv;
    }
    /** <p>????????????</p> */
    public abstract function exists();
    /** <p>????????????</p> */
    public function notExists(){ return !$this->exists(); }
    /** <p>????????????????????????</p> */
    public function fullName(){ return $this->full;  }
    /** <p>?????????????????????????????????</p> */
    public function baseDirectory(){ return $this->gi("dirname"); }
    /** <p>?????????????????????????????????</p> */
    public function baseDirectoryInfo(){ return new DirectoryInfo($this->baseDirectory()); }
    
    /** <p>????????????</p> */
    public function rename($newName){if($this->exists() && !file_exists($newName)){ rename($this->full, $newName); if(file_exists($newName)){ $this->full = $newName; return true;}} return false; }
    /** <p>????????????????????????(Unix timestamp)???????????????????????????null</p> */
    public function aTime(){ if($this->exists()){ return fileatime($this->full); } return null; }
    /** <p>????????????(Unix timestamp)???????????????????????????null</p> */
    public function mTime(){ if($this->exists()){ return filemtime($this->full); } return null; }
}

/** ??????????????????IO */
class DirectoryInfo extends __IO_Info{
    /** <p>????????????</p> */
    public function exists(){ return self::__IsExists($this->full, FALSE);}
    /** <p>??????????????????????????????</p> */
    public function name(){ return $this->gi("basename"); }
    /** <p>????????????Path???????????????</p> */
    public function getFilePaths(... $ptrns){
        $ret = array();
        if(!$this->exists()){ return $ret; }
        if(isEmpty($ptrns)){
            foreach(glob($this->full.DIRECTORY_SEPARATOR."{*,.[!.]*,..?*}", GLOB_BRACE) as $f){ if(is_file($f)){ $ret[] = $f;} }
        }else{
            foreach($ptrns as $p){
                foreach(glob($this->full.DIRECTORY_SEPARATOR.$p, GLOB_BRACE) as $f){ if(is_file($f)){ $ret[] = $f;} }
            }
        }
        sort($ret);
        return $ret;
    }
    /** <p>???????????????(path???????????????)??????????????????</p> */
    public function getFileNames(... $ptrns){
        $ret = array();
        foreach($this->getFilePaths(... $ptrns) as $v){
            $p = pathinfo($v);
            $ret[] = $p["basename"];
        }
        return $ret;
    }
    /** <p>????????????Info??????????????????</p> */
    public function getFileInfos(... $ptrns){
        $ret = array();
        foreach($this->getFilePaths(... $ptrns) as $v){ $ret[] = new FileInfo($v); }
        return $ret;
    }
    /** <p>????????????Path?????????</p> */
    public function getFilePath($childName){
        return Path::combine($this->full, $childName);
    }
    /** <p>????????????Info?????????</p> */
    public function getFileInfo($childName){
        return new FileInfo($this->getFilePath($childName));
    }
    /** <p>????????????????????????????????????</p> */
    public function getDirectoryPaths(... $ptrns){
        $ret = array();
        if(!$this->exists()){ return $ret; }
        if(isEmpty($ptrns)){
            foreach(glob($this->full.DIRECTORY_SEPARATOR."{*,.[!.]*,..?*}", GLOB_BRACE | GLOB_ONLYDIR) as $f){ if(is_dir($f)){ $ret[] = $f;} }
        }else{
            foreach($ptrns as $p){
                foreach(glob($this->full.DIRECTORY_SEPARATOR.$p, GLOB_BRACE | GLOB_ONLYDIR) as $f){ if(is_dir($f)){ $ret[] = $f;} }
            }
        }
        sort($ret);
        return $ret;
    }
    /** <p>??????????????????Info??????????????????</p> */
    public function getDirectoryInfos(... $ptrns){
        $ret = array();
        foreach($this->getDirectoryPaths(... $ptrns) as $v){ $ret[] = new DirectoryInfo($v); }
        return $ret;
    }
    /** <p>??????????????????????????????Path?????????</p> */
    public function getDirectoryPath($childName){ return $this->full.DIRECTORY_SEPARATOR.$childName; }
    /** <p>????????????????????????Info?????????</p> */
    public function getDirectoryInfo($childName){ return new DirectoryInfo(Path::combine($this->full, $childName)); }
    
    /** <p>?????????????????????????????????????????????????????????</p> */
    public function make($mode = 0777){
        if(empty($this->full) || $this->exists()){ return; }
        mkdir($this->full, $mode, true);
    }
    /** <p>????????????????????????????????????</p> */
    public function delete($delete_files = false){
        if(!$this->exists()){ return; }
        if($delete_files){
            foreach ($this->getDirectoryInfos() as $i){ $i->delete(true); }
            foreach ($this->getFileInfos() as $i){ $i->delete(true); }
        }
        rmdir($this->full);
    }
}

/** ????????????IO */
class FileInfo extends __IO_Info {
    /** <p>????????????</p> */
    public function exists(){ return self::__IsExists($this->full, TRUE);}
    
    /** <p>????????????????????????</p> */
    public function name($needExtention = true){ 
        if($needExtention){ return $this->gi("basename"); }
        return basename($this->full, $this->extension(TRUE));
    }
    /** <p>??????????????????</p> */
    public function extension($needDot = true){ return ($needDot ? "." : "") . $this->gi("extension"); }
    
    /** <p>?????????????????????????????????????????????</p> */
    public function read($nullVal = null){
        if($this->notExists()){ return $nullVal; }
        return file_get_contents($this->full);
    }
    /** <p>?????????????????????htmlspecialchars????????????????????????????????????????????????</p> */
    public function readH($nullVal = null){
        if($this->notExists()){ return h($nullVal); }
        return h(file_get_contents($this->full));
    }

    /** <p>sha1????????????????????????????????????????????????NULL?????????</p> */
    public function hash(){ if($this->notExists()){ return null; } return sha1_file($this->full); }
    
    /** <p>??????????????????????????????????????????</p> */
    public function save($data, $lock = true){
        if(empty($this->full)){ return; }
        $bs = $this->baseDirectoryInfo();
        if($bs->notExists()){ $bs->make($mode); }
        $opt = (($lock === true) ? LOCK_EX : 0);
        file_put_contents($this->full, $data, $opt);
    }
    /** <p>??????????????????????????????</p> */
    public function delete(){
        if($this->notExists()){ return; }
        unlink($this->full);
    }
    
    /** <p>?????????????????????????????????????????????????????????</p> */
    public function makeDirectory($mode = 0777){
        $this->baseDirectoryInfo()->make($mode);
    }
}

// ===================================================================

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

// ===================================================================

// AccessLog
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
    error_log($s."\n", 3, Path::tmp("access.log"));
} catch (Exception $ex) {
}
?>