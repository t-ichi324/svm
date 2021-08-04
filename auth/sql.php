<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    HtmlEcho::HEAD("MySql - Sql");
    
    $sql = formval("sql");
    
    $usr = formval("usr");
    $pw = formval("pw");
    $host = formval("host");
    $dbname = formval("dbname");
    $bin = formval("bin");

    //save
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
?>
<style>
    .result-wrap{
        width: 100%;
        height: 500px;
        
        overflow: auto;
        background-color: #fafafa;
    }
    .result-tbl th, .result-tbl td{
        border: 1px solid #eee;
    }
</style>
<form method="post">
    <table>
        <tr>
            <td>DB_NAME</td>
            <td><input type="text" name="dbname" placeholder="dbname" value="<?= h($dbname) ?>" ></td>
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
            <td><input type="text" name="host" placeholder="host" value="<?= h($host) ?>" ></td>
        </tr>
    </table>
    <input type="hidden" name="bin" value="<?= h($bin) ?>">
    <hr>
    sql:<br>
    <textarea name="sql" style="min-height: 5rem; min-width: 50%"><?= h($sql); ?></textarea><br>
    <button>run</button>
</form>
<hr>
<?php
    if($_POST && !empty($sql) && !empty($usr)){
        try{
            echo "<p>Result:</p>";
            $dns = "mysql:";
            if(!empty($dbname)){ $dns.="dbname=".$dbname.";"; }
            $dns.="host=" . (empty($host) ? "localhost" : $host);
            echo "<p>[DNS] ".$dns."</p>";            
            
            $dbh = new PDO($dns, $usr, $pw);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->exec("SET NAMES utf8");
            $sth = $dbh->prepare($sql);
            $sth->execute();
            $rows = $sth->fetchAll(PDO::FETCH_NAMED);
            $cnt = count($rows);
            echo "<p>[ROW] ".$cnt."</p>";
            echo "<div class='result-wrap'>";
            echo "<table class='result-tbl'>";
            if($cnt > 0){
                $r0 = $rows[0];
                echo "<thead><tr>";
                foreach ($r0 as $k => $v){
                    echo "<th>". h($k)."</th>";
                }
                echo "</tr></thead>";
            }
            echo "<tbody>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach($row as $col){
                    echo "<td>".h($col)."</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        }catch (Exception $e){
            echo "<p style='color:#f00'>Error: ".h($e->getMessage())."</p>";
        }
    }
?>

<?php HtmlEcho::FOOT(); ?>