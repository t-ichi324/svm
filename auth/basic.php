<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    $msg = "";
    
    //.htpasswd
    $f_pw = __DIR__.DIRECTORY_SEPARATOR.".htpasswd";
    $f_ac = __DIR__.DIRECTORY_SEPARATOR.".htaccess";
    $f_json = getTmpDir().DIRECTORY_SEPARATOR."basic.json";
    
    $run = formVal("run");
    $json = array();
    
    if($run == "reset"){
        if(file_exists($f_pw)){ unlink($f_pw); }
        file_put_contents($f_ac, "Allow from all\n");
        $msg = "reset basic-auth.";
        
    }elseif($run == "update" && hasPost("u","p")){
        $id = formVal("u","");
        $pw = formVal("p","");
        $c = formVal("c","");
        $ip = formVal("ip","");
        
        if($c == "plain"){
            $passwd =  $id.":".$pw;
        }else{
            $passwd =  $id.":".password_hash($pw, PASSWORD_BCRYPT);
        }
        file_put_contents($f_pw, $passwd);
        
        $ip_list = lineToArray($ip);
        
        //.htaccess
        $htac = "AuthType Basic\n".
                "AuthName 'auth check'\n".
                "AuthUserFile ".$f_pw."\n".
                "require valid-user\n";
        
                if(empty($ip_list)){
                    $htac.="Allow from all\n";
                }else{
                    $htac.="Deny from all\n";
                    foreach($ip_list as $line){ $htac.="Allow from ".$line."\n"; }
                }
                
        file_put_contents($f_ac, $htac);
        
        $msg = "update basic-auth.";
        
        $json = array("u"=>$id, "p"=>$pw, "c"=>$c, "ip"=>$ip);
        file_put_contents($f_json, json_encode($json));
    }else{
        if(file_exists($f_json)){
            $json = json_decode(file_get_contents($f_json), true);
        }
    }
    
    echoHead("Basic Auth");
?>
<hr>
<p style="color:#f00"><?= htmlspecialchars($msg); ?></p>
<form method="post" onsubmit="return confirm('Are you sure you want to update?')">
    <input type="hidden" name="run" value="update">
    <input type="text" name="u" required placeholder="auth user" value="<?= isset($json["u"]) ? htmlspecialchars($json["u"]) : ""; ?>">
    <input type="password" name="p" required placeholder="password" value="<?= isset($json["p"]) ? htmlspecialchars($json["p"]) : ""; ?>">
    <br>
    <select name="c" required>
        <option value="ph">password_hash</option>
        <option value="plain">plain-text</option>
    </select>
    <br>
    <br>
    <textarea name="ip" style="width: 20rem;height: 4rem;" placeholder="allow-ip(optional)"><?= isset($json["ip"]) ? htmlspecialchars($json["ip"]) : ""; ?></textarea>
    <p>YOUR-IP: <?= $_SERVER["REMOTE_ADDR"]; ?></p>
    <br>
    <button type="submit" >UPDATE</button>
</form>
<hr>
<form method="post" onsubmit="return confirm('Are you sure you want to reset?')">
    <input type="hidden" name="run" value="reset">
    <button type="submit">RESET</button>
</form>
<?php echoFoot(); ?>