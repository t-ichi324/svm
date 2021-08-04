<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    HtmlEcho::HEAD("Network");
    
    $run = formval("run", "dns");
    //dns
    $dns = formval("dns");
    //curl
    $curl = formval("curl");
    $ua = formval("ua", $_SERVER['HTTP_USER_AGENT']);
    $method = formval("method", "GET");
    $ssl = formval("ssl", "1");
    //port
    $host = formval("host");
    $port = formval("port", 80);
    $protocol = formval("protocol");
    
    function sysCmd_echo(... $lines){
        $cmd = "";
        echo "<div class='cmd' style='font-family:monospace;background-color:#000; color:#fff; padding:.5rem; margin-bottom:.5rem;'>";
        if(is_array($lines)){
            foreach($lines as $v){
                echo h($v)."<br>";
                if($cmd !== "") { $cmd.="\n"; }
                $cmd .= $v;
            }
        }else{
            $cmd = $lines;
            echo h($cmd)."<br>";
        }
        echo "</div>";
        $opt = null;
        exec($cmd, $opt);
        echo "<div class='cmd' style='font-family:monospace;background-color:#333; color:#fff; padding:.5rem; margin-bottom:.5rem; margin-left: 1rem;'>";
        if(!empty($opt)){
            foreach($opt as $o){
                $line = toUtf8($o);
                if(trim($line) == "") { continue; }
                echo h($line)."<br>";
            }
        }
        echo "</div>";
    }
?>
<div class="tgl-nav">
<?php 
    HtmlEcho::tglPnl_radio("dns", "DNS", $run);
    HtmlEcho::tglPnl_radio("curl", "cURL", $run);
    HtmlEcho::tglPnl_radio("port", "Port", $run);
    HtmlEcho::tglPnl_radio("trac", "Trace", $run);
?>
</div>
<div <?php HtmlEcho::tglPnl_attr("dns", $run); ?>>
    <h2>DNS Checker</h2>
    <form method="post">
        <input type="hidden" name="run" value="dns">
        <table>
            <tr>
                <td>domain or ip:</td>
                <td><input type="text" name="dns" value="<?= h($dns); ?>" placeholder="domain or ip"></td>
            </tr>
        </table>
        <button type="submit">CHECK</button>
    </form>
    <?php
        if($run == "dns" && $dns !== null){
            $ignore = ["host","ttl","class","type"];
            try{
                $dns_ar = dns_get_record($dns);
            } catch (Exception $ex) {
                $dns_ar = null;
            }
            if(empty($dns_ar)){
                echo "<p>not found dns record.</p>";
            }else{
                echo "<p>Result:</p>";
                echo "<table><thead><tr>"
                . "<th>host</th>"
                . "<th>ttl</th>"
                . "<th>claass</th>"
                . "<th>type</th>"
                . "<th></th>"
                . "</tr></thead><tbody>";
                foreach($dns_ar as $k=>$v){
                    $type = isset($v["type"]) ? $v["type"] : "";
                    $target = isset($v["target"]) ? $v["target"] : "";
                    $val = isset($v["ip"]) ? $v["ip"] : "";
                    echo "<tr>";
                    echo "<td>".$v["host"]."</td>";
                    echo "<td>".$v["ttl"]."</td>";
                    echo "<td>".$v["class"]."</td>";
                    echo "<td>".$type."</td>";
                    $val = "";
                    foreach($v as $k2=>$v2){
                        if(in_array($k2, $ignore)){ continue; }
                        if(is_array($v2)){
                            $val .= $k2."=".json_encode($v2)."\t";
                        }else{
                            $val .= $k2."=".$v2."\t";
                        }
                    }
                    echo "<td>".h($val)."</td>";
                    echo "</tr>";
                }
                echo "</table></tbody>";?>
                <hr><?php
            }
        }
    ?>
    <div class="tips">
        <table>
            <tr>
                <td>NS</td>
                <td>nameserver record</td>
                <td>Host name</td>
            </tr>
            <tr>
                <td>A</td>
                <td>address record</td>
                <td>IPv4</td>
            </tr>
            <tr>
                <td>AAAA</td>
                <td>address record</td>
                <td>IPv6</td>
            </tr>
            <tr>
                <td>MX</td>
                <td>email exchange record</td>
                <td>A or AAAA or Host name</td>
            </tr>
            <tr>
                <td>CNAME</td>
                <td>canonical name record</td>
                <td>Alias name</td>
            </tr>
            <tr>
                <td>TXT</td>
                <td>text record</td>
                <td>(any-text)</td>
            </tr>
        </table>
    </div>
</div>
<div <?php HtmlEcho::tglPnl_attr("curl", $run); ?>>
    <h2>cURL Checker</h2>
    <form method="post">
        <input type="hidden" name="run" value="curl">
        <table>
            <tr>
                <td>Url:</td>
                <td><input type="url" name="curl" value="<?= h($curl); ?>" required placeholder="url"></td>
            </tr>
            <tr>
                <td>Method:</td>
                <td>
                    <select name="method">
                        <option value="GET" <?= ( $method == "GET" ? "selected" : ""); ?>>GET</option>
                        <option value="POST" <?= ( $method == "POST" ? "selected" : ""); ?>>POST</option>
                        <option value="HEAD" <?= ( $method == "HEAD" ? "selected" : ""); ?>>HEAD</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>UserAgent:</td>
                <td><input type="text" name="ua" value="<?= h($ua); ?>" placeholder="(opt) user-agent"></td>
            </tr>
            <tr>
                <td colspan="2">
                    <label>
                        <input type="checkbox" name="ssl" value="1" <?= ( $ssl == "1" ? "checked" : ""); ?>>
                        SSL valid
                    </label>
                </td>
            </tr>
        </table>
        <button type="submit">CHECK</button>
        <?php
        if($run == "curl" && $curl !== null){
            $info = null;
            if(!extension_loaded("curl")){
                echo "<p style='color:#f00;'>ERROR : Not installed 'cURL'.</p?";
            }else{
                $ch = curl_init($curl);
                try{
                    curl_setopt($ch, CURLOPT_HEADER, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    curl_setopt($ch, CURLOPT_USERAGENT, $ua);

                    if($ssl == "1"){ curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); }
                    if($method == "POST"){
                        curl_setopt($ch, CURLOPT_POST, TRUE); 
                    }
                    $r = curl_exec($ch);
                    $info = curl_getinfo($ch);
                    $result["header"] = substr ($r, 0, $info["header_size"]);
                    $result["body"] = substr ($r, $info["header_size"]);
                    curl_close($ch);
                } catch (Exception $ex) {
                    curl_close($ch);
                    $result["msg"] = $ex->getMessage();
                }
            }
            
            if($info !== null){ ?>
            <p>Header:</p>
            <pre style="height: 5rem;"><?= h($result["header"]); ?></pre>
            <p>Body:</p>
            <pre><?= h($result["body"]); ?></pre>
            <p>Info:</p>
            <table>
                <tbody>
                    <?php foreach($info as $k => $v){ if(!is_array($v)){ ?>
                    <tr>
                        <td><?= h($k); ?></td>
                        <td><?= h($v); ?></td>
                    </tr>
                    <?php }} ?>
                </tbody>
            </table>
            <hr>
            <?php }
        }
        ?>
    </form>
</div>
<div <?php HtmlEcho::tglPnl_attr("port", $run); ?>>
    <h2>Port Checker</h2>
    <form method="get">
        <input type="hidden" name="run" value="port">
        <table>
            <tr>
                <td>Host</td>
                <td><input type="text" name="host" value="<?= h($host); ?>" required placeholder="domain or ip"></td>
            </tr>
            <tr>
                <td>Port</td>
                <td><input type="number" name="port" value="<?= h($port); ?>" required placeholder="port number"></td>
            </tr>
            <tr>
                <td>Protocol</td>
                <td>
                    <select name="protocol">
                        <option value="tcp">tcp</option>
                        <option value="udp">udp</option>
                    </select>
                </td>
            </tr>
        </table>
        <button type="submit">CHECK</button>
    </form>
    <?php
        if($run == "port" && $host !== null){
            $hn = ($protocol != null) ? $protocol."://" : "";
            $hn.= $host;
            $errstr = "";
            $errno = -1;
            $status = "<span style='color:#f00;'>ERROR</span>";
            $cmd = h($hn).":".$port."<br>";
            try{
                $fp = @fsockopen($hn, $port, $errno, $errstr, 1);
                if(!$fp){
                }else{
                    $status = "<span style='color:#00f;'>OPEN</span>";
                }
                if($fp){ fclose($fp); }
            } catch (Exception $ex) {
                if($fp){ fclose($fp); }
            }?>
            <p>Result:</p>
            <table>
                <tr>
                    <td>Cmd</td>
                    <td><?= $cmd ?></td>
                </tr>
                <tr>
                    <td>Status</td>
                    <td><?= $status ?></td>
                </tr>
            </table>
            <hr>
        <?php
        }
    ?>
    <div class="tips">
        <table>
            <tbody>
                <tr><td>HTTP</td><td>TCP</td><td>80</td></tr>
                <tr><td>HTTPS</td><td>TCP</td><td>443</td></tr>
                <tr><td>SSH</td><td>TCP</td><td>22</td></tr>
                <tr><td>RDS</td><td>TCP</td><td>3389</td></tr>
                <tr><td>FTP</td><td>TCP</td><td>20, 21</td></tr>
                <tr><td>SMTP(mail)</td><td>TCP</td><td>25, <strike>465</strike>, 587, 2525</td></tr>
            </tbody>
        </table>
    </div>
</div>
<div <?php HtmlEcho::tglPnl_attr("trac", $run); ?>>
    <h2>Traceroute Checker</h2>
    <form method="post">
        <input type="hidden" name="run" value="trac">
        <table>
            <tr>
                <td>domain or ip:</td>
                <td><input type="text" name="dns" value="<?= h($dns); ?>" placeholder="domain or ip"></td>
            </tr>
        </table>
        <button type="submit">CHECK</button>
    </form>
    <?php
        if($run == "trac" && $dns !== null){
            if(isWindows()){
                sysCmd_echo("tracert ".$dns);
            }else{
                sysCmd_echo("traceroute ".$dns);
            }
        }
    ?>
</div>

<?php HtmlEcho::FOOT(); ?>