<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    $run = formval("run", null);
    
    $usr = formval("usr");
    $pw = formval("pw");
    $host = formval("host");
    $dbname = formval("dbname");
    $bin = formval("bin");
    
    $f_json = Path::tmp("-save-dbcon.json");
    $json = array();
    if($_POST){
        $json["usr"] = $usr;
        $json["pw"] = $pw;
        $json["host"] = $host;
        $json["dbname"] = $dbname;
        $json["bin"] = $bin;
        file_put_contents($f_json, json_encode($json));
    }else{
        if(file_exists($f_json)){
            $json = json_decode(file_get_contents($f_json), true);
            $usr = isset($json["usr"]) ? $json["usr"] : "";
            $pw = isset($json["pw"]) ? $json["pw"] : "";
            $host = isset($json["host"]) ? $json["host"] : "";
            $dbname = isset($json["dbname"]) ? $json["dbname"] : "";
            $bin = isset($json["bin"]) ? $json["bin"] : "";
        }
    }
    
    
    function createDump($filename, $usr, $pw, $host, $dbname, $bin){
        $cmd = $bin;
        if(!empty($cmd)){ $cmd .= DIRECTORY_SEPARATOR; }
        $cmd .= "mysqldump -u{$usr} -p{$pw}";
        
        if($host !== null) { $cmd.= " -h{$host}"; }
        if($dbname !== null) { $cmd.= " {$dbname}"; }
        $cmd .= ' > ' . $filename;
        $ret = system($cmd);
        return $ret;
    }
    function importDump($filename, $usr, $pw, $host, $dbname){
        if(!file_exists($filename)){
            Message::$err =  "ERROR: file notfound.";
        }
        try{
            $sql = file_get_contents($filename);
            $byte = strlen($sql) + 1024; //1024 buffer
            $pdo = new PDO('mysql:host='.$host.'; dbname='.$dbname, $usr, $pw,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "set global max_allowed_packet=".$byte));
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $pdo->exec($sql);
            Message::$info =  "SUCCESS (".$byte." byte).";
        } catch (Exception $ex) {
            Message::$err = "ERROR:\n".$ex->getMessage();
        }
    }
    if($run == "exp"){
        if(empty($usr) || empty($pw) || empty($dbname) || empty($host)){
            $msg = "Please input [host][dbname][user][password].";
        }else{
            $n = $dbname."_".date("Ymdhis")."".".dump";
            $filename = Path::tmp($n);
            createDump($filename, $usr, $pw, $host, $dbname, $bin);
            Response::fileDownload($filename, $n);
            die();
        }
    }
    if($run == "imp"){
        if(!isset($_FILES["file"])){
            $msg .= "ERROR: not found Upload file";
        }else{
            if(empty($usr) || empty($pw) || empty($dbname) || empty($host)){
                $msg = "Please input [host][dbname][user][password].";
            }else{
                $n = $dbname."_".date("Ymdhis")."_imp".".dump";
                $filename = Path::tmp($n);
                move_uploaded_file($_FILES['file']['tmp_name'], $filename);
                importDump($filename, $usr, $pw, $host, $dbname);
            }
        }
    }
    
    $run = formval("run", "exp");
    
    HtmlEcho::HEAD("MySql - Dump");
?>
<div class="tgl-nav">
<?php 
    HtmlEcho::tglPnl_radio("exp", "Export", $run);
    HtmlEcho::tglPnl_radio("imp", "Import", $run);
?>
</div>
<div <?php HtmlEcho::tglPnl_attr("exp", $run); ?>>
    <h2>Export</h2>
    <form method="post" onsubmit="return confirm('Are you sure you want to create dump?')">
        <input type="hidden" name="run" value="exp">
        <table>
            <tr>
                <td>DB_NAME</td>
                <td><input type="text" name="dbname" required placeholder="dbname" value="<?= h($dbname) ?>" ></td>
            </tr>
            <tr>
                <td>DB_USER</td>
                <td><input type="text" name="usr" required placeholder="user" value="<?= h($usr) ?>" ></td>
            </tr>
            <tr>
                <td>DB_PASSWORD</td>
                <td><input type="password" name="pw" required placeholder="password" value="<?= h($pw) ?>" ></td>
            </tr>
            <tr>
                <td>DB_HOST</td>
                <td><input type="text" name="host" required placeholder="host" value="<?= h($host) ?>" ></td>
            </tr>
            <tr>
                <td>Bin (Service)</td>
                <td><input type="text" name="bin" placeholder="mysql path(optional)" value="<?= h($bin) ?>" ></td>
            </tr>
        </table>
        <button type="submit" >Export</button>
    </form>
</div>
<div <?php HtmlEcho::tglPnl_attr("imp", $run); ?>>
    <h2>Import</h2>
    <form method="post" onsubmit="return confirm('Are you sure you want to import dump?')" enctype="multipart/form-data">
        <input type="hidden" name="run" value="imp">
        <table>
            <tr>
                <td>Dump (SqlFile)</td>
                <td><input type="file" name="file" required></td>
            </tr>
            <tr>
                <td>DB_NAME</td>
                <td><input type="text" name="dbname" required placeholder="dbname" value="<?= h($dbname) ?>" ></td>
            </tr>
            <tr>
                <td>DB_USER</td>
                <td><input type="text" name="usr" required placeholder="user" value="<?= h($usr) ?>" ></td>
            </tr>
            <tr>
                <td>DB_PASSWORD</td>
                <td><input type="password" name="pw" required placeholder="password" value="<?= h($pw) ?>" ></td>
            </tr>
            <tr>
                <td>DB_HOST</td>
                <td><input type="text" name="host" required placeholder="host" value="<?= h($host) ?>" ></td>
            </tr>
        </table>
        <input type="hidden" name="bin" value="<?= h($bin) ?>">
        <button type="submit">Import</button>
    </form>
</div>

<?php HtmlEcho::FOOT(); ?>