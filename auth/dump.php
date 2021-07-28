<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    $run = formVal("run", null);

    $msg = "";
    $usr = formVal("usr");
    $pw = formVal("pw");
    $host = formVal("host");
    $dbname = formVal("dbname");
    $bin = formVal("bin");
    
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
            return "ERROR: file notfound.";
        }
        try{
            $sql = file_get_contents($filename);
            $byte = strlen($sql) + 1024; //1024 buffer
            $pdo = new PDO('mysql:host='.$host.'; dbname='.$dbname, $usr, $pw,
                    array(PDO::MYSQL_ATTR_INIT_COMMAND => "set global max_allowed_packet=".$byte));
            $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $pdo->exec($sql);
            return "SUCCESS (".$byte." byte).";
        } catch (Exception $ex) {
            return "ERROR:\n".$ex->getMessage();
        }
    }
    if($run == "exp"){
        if(empty($usr) || empty($pw) || empty($dbname) || empty($host)){
            $msg = "Please input [host][dbname][user][password].";
        }else{
            $n = $dbname."_".date("Ymdhis")."".".dump";
            $cd = getTmpDir();
            $filename = $cd.DIRECTORY_SEPARATOR.$n;
            createDump($filename, $usr, $pw, $host, $dbname, $bin);
            fileDownload($filename, $n);
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
                $cd = getTmpDir();
                $filename = $cd.DIRECTORY_SEPARATOR.$n;
                move_uploaded_file($_FILES['file']['tmp_name'], $filename);
                print_r($_FILES);
                
                $msg = importDump($filename, $usr, $pw, $host, $dbname);
            }
        }
    }
    
    $run = formVal("run", "exp");
    echoHead("MySql - Dump");
?>
<div class="tgl-nav">
<?php 
    tglPnlRadio("exp", "Export", $run);
    tglPnlRadio("imp", "Import", $run);
?>
</div>
<p style="color:#f00"><?= htmlspecialchars($msg); ?></p>
<div <?php tglPnlAttr("exp", $run); ?>>
    <h2>Export</h2>
    <form method="post" onsubmit="return confirm('Are you sure you want to create dump?')">
        <input type="hidden" name="run" value="exp">
        <table>
            <tr>
                <td>DB_NAME</td>
                <td><input type="text" name="dbname" required placeholder="dbname" value="<?= htmlspecialchars($dbname) ?>" ></td>
            </tr>
            <tr>
                <td>DB_USER</td>
                <td><input type="text" name="usr" required placeholder="user" value="<?= htmlspecialchars($usr) ?>" ></td>
            </tr>
            <tr>
                <td>DB_PASSWORD</td>
                <td><input type="password" name="pw" required placeholder="password" value="<?= htmlspecialchars($pw) ?>" ></td>
            </tr>
            <tr>
                <td>DB_HOST</td>
                <td><input type="text" name="host" required placeholder="host" value="<?= htmlspecialchars($host) ?>" ></td>
            </tr>
            <tr>
                <td>Bin (Service)</td>
                <td><input type="text" name="bin" placeholder="mysql path(optional)" value="<?= htmlspecialchars($bin) ?>" ></td>
            </tr>
        </table>
        <button type="submit" >Export</button>
    </form>
</div>
<div <?php tglPnlAttr("imp", $run); ?>>
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
                <td><input type="text" name="dbname" required placeholder="dbname" value="<?= htmlspecialchars($dbname) ?>" ></td>
            </tr>
            <tr>
                <td>DB_USER</td>
                <td><input type="text" name="usr" required placeholder="user" value="<?= htmlspecialchars($usr) ?>" ></td>
            </tr>
            <tr>
                <td>DB_PASSWORD</td>
                <td><input type="password" name="pw" required placeholder="password" value="<?= htmlspecialchars($pw) ?>" ></td>
            </tr>
            <tr>
                <td>DB_HOST</td>
                <td><input type="text" name="host" required placeholder="host" value="<?= htmlspecialchars($host) ?>" ></td>
            </tr>
        </table>
        <button type="submit">Import</button>
    </form>
</div>
<?php echoFoot(); ?>