<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    echoHead("MySql - Sql");
    
    $usr = formVal("usr");
    $pw = formVal("pw");
    $host = formVal("host");
    $dbname = formVal("dbname");
    $sql = formval("sql");
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
            <td><input type="text" name="dbname" placeholder="dbname" value="<?= htmlspecialchars($dbname) ?>" ></td>
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
            <td><input type="text" name="host" placeholder="host" value="<?= htmlspecialchars($host) ?>" ></td>
        </tr>
    </table>
    <hr>
    sql:<br>
    <textarea name="sql" style="min-height: 5rem; min-width: 50%"><?= htmlspecialchars($sql); ?></textarea><br>
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
                    echo "<th>". htmlspecialchars($k)."</th>";
                }
                echo "</tr></thead>";
            }
            echo "<tbody>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach($row as $col){
                    echo "<td>".htmlspecialchars($col)."</td>";
                }
                echo "</tr>";
            }
            echo "</tbody></table>";
            echo "</div>";
        }catch (Exception $e){
            echo "<p style='color:#f00'>Error: ".htmlspecialchars($e->getMessage())."</p>";
        }
    }
?>
<?php echoFoot(); ?>