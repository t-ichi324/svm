<?php include_once __DIR__.DIRECTORY_SEPARATOR."func.r";
    $run = formVal("run", "hta");
    $txt = formVal("txt");
    
    
    if($run == "hta" && $txt == "1"){
        $rewriteOn = false;
        $hta = "";
        $domain_red = formVal("domain_red", "");
        $ssl_red = formVal("ssl_red", "");
        $www_red = formVal("www_red", "");
        $indexes = formVal("indexes", "");
        
        $ip_deny = lineToArray(formVal("ip_deny",""));
        $ip_allow = lineToArray(formVal("ip_allow",""));
        
        if($domain_red !== ""){
            $hta.="# Domain Redirect\n";
            $hta.= "Redirect permanent / ".trim($domain_red, "/")."/"."\n";
            $hta.= "\n";
        }
        if($www_red !== ""){
            if(!$rewriteOn){ $hta.="RewriteEngine On\n"; $rewriteOn = true; }
            $hta.="# WWW Redirect\n";
            if($www_red == "www"){
                $hta.= "RewriteCond %{HTTP_HOST} !^www.(.*)$ [NC]"."\n";
                $hta.= "RewriteRule ^(.*)$ http://www.%{HTTP_HOST}/$1"."\n";
            }else{
                $hta.= "RewriteCond %{HTTP_HOST} ^www.(.*)$ [NC]"."\n";
                $hta.= "RewriteRule ^(.*)$ http://%1/$1 [R=301,L]"."\n";
            }
            $hta.= "\n";
        }
        
        if($ssl_red !== ""){
            if(!$rewriteOn){ $hta.="RewriteEngine On\n"; $rewriteOn = true; }
            $hta.="# SSL Redirect\n";
            $hta.= "RewriteCond %{HTTPS} off"."\n";
            $hta.= "RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]"."\n";
            $hta.= "\n";
        }
        
        if($indexes !== ""){
            $hta.= "# Options Indexes (file list)"."\n";
            $hta.= "Options ".$indexes."\n";
            $hta.= "\n";
        }
        
        if(!empty($ip_allow) || !empty($ip_deny)){
            $hta.="# Ip Filter\n";
            if(empty($ip_allow)){
                $hta.= "Order allow,deny"."\n";
                $hta.= "Allow from all"."\n"; 
            }elseif(empty($ip_deny)){ 
                $hta.= "Order deny,allow"."\n";
                $hta.= "Deny from all"."\n"; 
            }else{
                $hta.= "Order allow,deny"."\n";
            }
            
            foreach($ip_allow as $ip){  $hta.= "allow from ".$ip."\n"; }
            foreach($ip_deny as $ip){  $hta.= "deny from ".$ip."\n"; }
            $hta.= "\n";
        }
        
        if(formVal("wp_def","") !== ""){
            $wp_dir = formVal("wp_dir");
            if($wp_dir != null){ $wp_dir = trim($wp_dir,"/")."/"; }
            $hta.="# BEGIN WordPress"."\n";
            $hta.="<IfModule mod_rewrite.c>"."\n";
            $hta.="RewriteEngine On"."\n";
            $hta.="RewriteBase /".$wp_dir."\n";
            $hta.="RewriteRule ^index\.php$ - [L]"."\n";
            $hta.="RewriteCond %{REQUEST_FILENAME} !-f"."\n";
            $hta.="RewriteCond %{REQUEST_FILENAME} !-d"."\n";
            $hta.="RewriteRule . /".$wp_dir."index.php [L]"."\n";
            $hta.="</IfModule>"."\n";
            $hta.="# END WordPress"."\n";
            $hta.= "\n";
        }
        if(formVal("ssm_def","") !== ""){
            $hta.="# BEGIN Sesame"."\n";
            $hta.="<IfModule mod_rewrite.c>"."\n";
            $hta.="RewriteEngine on"."\n";
            $hta.="RewriteCond %{REQUEST_FILENAME} !-f"."\n";
            $hta.="RewriteCond %{REQUEST_FILENAME} !-d"."\n";
            $hta.="RewriteRule ^(.*)$ index.php/$1 [QSA,L] !-d"."\n";
            $hta.="</IfModule>"."\n";
            $hta.="# END Sesame"."\n";
        }
        
        //textShow($hta);
        textDownload($hta, "---.htaccess");
    }
    
    if($run == "userini" && $txt == "1"){
        $ini = "";
        $nms = array("post_max_size", "upload_max_filesize", "memory_limit", "max_execution_time", "error_reporting", "display_errors");
        foreach($nms as $n){
            $v = formVal($n);
            if($v != null){
                $ini .=  $n."=".$v."\n";
            }
        }
        //textShow($ini);
        textDownload($ini, "---.user.ini");
    }
    
    if($run == "robots" && $txt == "1"){
        $txt = "User-Agent:*\n";
        $dis = lineToArray(formVal("rbt_disallow"));
        $alw = lineToArray(formVal("rbt_allow"));
        $smp = lineToArray(formVal("rbt_sitemap"));
        
        foreach($dis as $v){ $txt .=  "Disallow:".$v."\n"; }
        foreach($alw as $v){ $txt .=  "Allow:".$v."\n"; }
        foreach($smp as $v){ $txt .=  "Sitemap:".$v."\n"; }
        //textShow($txt);
        textDownload($txt, "robots.txt");
    }

    if($run == "sitemap" && $txt == "1"){
        $type = formVal("smp_type");
        if($type == "index"){
            $root = "sitemapindex";
            $tag = "sitemap";
        }else{
            $root = "urlset";
            $tag = "url";
        }
        $txt = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $txt.= '<'.$root.' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
        $url = lineToArray(formVal("smp_url"));
        foreach($url as $v){
            $txt .= "  <".$tag."><loc>".$v."</loc></".$tag.">\n";
        }
        $txt .= "</".$root.">";
        //textShow($txt);
        textDownload($txt, "sitemap.xml");
    }
    echoHead("Setting Files");
?>
<div class="tgl-nav">
<?php 
    tglPnlRadio("hta", ".htaccess", $run);
    tglPnlRadio("userini", ".user.ini", $run);
    tglPnlRadio("robots", "robots.txt", $run);
    tglPnlRadio("sitemap", "sitemap.xml", $run);
?>
</div>
<div <?php tglPnlAttr("hta", $run); ?>>
    <h2>.httaccess @ Apache</h2>
    <form method="post">
        <input type="hidden" name="run" value="hta">
        <input type="hidden" name="txt" value="1">
        
        <p>Redirect : Domain (http://old-domain.com/ -> https://new-domain.com/)</p>
        <input type="url" name="domain_red" value="" placeholder="ex) https://new-domain.com">

        <p>Redirect : www (http://example.com/ -> https://www.example.com/)</p>
        <select name="www_red">
            <option value=""></option>
            <option value="www">"www" Required</option>
            <option value="none">"www" None</option>
        </select>
        <p>Redirect : SSL (http://example.com -> https://example.com)</p>
        <select name="ssl_red">
            <option value=""></option>
            <option value="1">YES</option>
        </select>

        <p>Prevent directory listing (Indexes)</p>
        <select name="indexes">
            <option value=""></option>
            <option value="-Indexes">NO</option>
            <option value="Indexes">YES</option>
        </select>
        <p>IP Filter</p>
        <table>
            <tr>
                <td style="padding: 0">Allow<br><textarea style="min-width: 12rem;min-height: 3rem;" name="ip_allow" placeholder="ex)&#13;192.168.1.1&#13;192.168.1.2"></textarea></td>
                <td style="padding: 0">Deny<br><textarea style="min-width: 12rem;min-height: 3rem;" name="ip_deny" placeholder="ex)&#13;192.168.1.1&#13;192.168.1.2"></textarea></td>
            </tr>
        </table>
        <div>YOUR-IP: <?= $_SERVER["REMOTE_ADDR"]; ?></div>
        <hr>
        <p>[FW] Wordpress Default</p>
        <select name="wp_def">
            <option value=""></option>
            <option value="1">YES</option>
        </select>
        <input type="text" name="wp_dir" placeholder="ex) wordpress/">

        <p>[FW] Sesame Default</p>
        <select name="ssm_def">
            <option value=""></option>
            <option value="1">YES</option>
        </select>

        <hr>
        <button type="submit">Create</button>
    </form>
    <p>Template</p>
    
    <p># Deny All</p>
    <div class="tips">
        <div classs="border:1px solid #666; padding: 1rem;">
            Deny from all<br>
        </div>
    </div>
    <p># Allow All</p>
    <div class="tips">
        <div classs="border:1px solid #666; padding: 1rem;">
            Allow from all<br>
        </div>
    </div>
</div>
<div <?php tglPnlAttr("userini", $run); ?>>
    <h2>.user.ini @ PHP</h2>
    <form method="post">
        <input type="hidden" name="run" value="userini">
        <input type="hidden" name="txt" value="1">
        <table>
            <tr>
                <td>memory_limit</td>
                <td><input type="text" name="memory_limit" placeholder="ex) 128M"> Byte</td>
            </tr>
            <tr>
                <td>post_max_size</td>
                <td><input type="text" name="post_max_size"  placeholder="ex) 20M"> Byte</td>
            </tr>
            <tr>
                <td>upload_max_filesize</td>
                <td><input type="text" name="upload_max_filesize" placeholder="ex) 20M"> Byte</td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>(Please Note)</strong> memory_limit > post_max_size > upload_max_filesize
                </td>
            </tr>
            <tr>
                <td>max_execution_time</td>
                <td><input type="number" name="max_execution_time" placeholder="ex) 30"> Sec</td>
            </tr>
            <tr>
                <td>error_reporting</td>
                <td>
                    <select name="error_reporting">
                        <option></option>
                        <option value="0">0 (Off)</option>
                        <option value="E_ALL">E_ALL</option>
                        <option value="E_ALL & ~E_NOTICE & ~E_DEPRECATED">E_ALL & ~E_NOTICE & ~E_DEPRECATED</option>
                        <option value="E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_NOTICE">E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_NOTICE</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>display_errors</td>
                <td>
                    <select name="display_errors">
                        <option></option>
                        <option value="On">On</option>
                        <option value="Off">Off</option>
                    </select>
                </td>
            </tr>
        </table>
        <button type="submit">Create</button>
    </form>
</div>

<div <?php tglPnlAttr("robots", $run); ?>>
    <h2>robots.txt @ SEO</h2>
    <form method="post">
        <input type="hidden" name="run" value="robots">
        <input type="hidden" name="txt" value="1">
        <table>
            <tr>
                <td>Disallow</td>
                <td><textarea style="width: 20rem; min-height: 5rem;" name="rbt_disallow" placeholder="ex)&#13;/wp-admin/*&#13;/api/*"></textarea></td>
            </tr>
            <tr>
                <td>Allow</td>
                <td><textarea style="width: 20rem; min-height: 5rem;" name="rbt_allow" placeholder="ex)&#13;/"></textarea></td>
            </tr>
            <tr>
                <td>Sitemap</td>
                <td><textarea style="width: 20rem; min-height: 5rem;" name="rbt_sitemap" placeholder="ex)&#13;https://example.com/sitemap.xml"></textarea></td>
            </tr>
        </table>
        <button type="submit">Create</button>
    </form>
    <hr>
    <p>* TestSite or NoIndex's sample</p>
    <div class="tips">
        <div classs="border:1px solid #666; padding: 1rem;">
            User-Agent:*<br>
            Disallow:/<br>
        </div>
    </div>
    <hr>
    <ul>
        <li><a href="https://search.google.com/search-console/" target="_blank">Google Search Console</a></li>
        <li><a href="https://analytics.google.com/" target="_blank">Google Analytics</a></li>
    </ul>
</div>
<div <?php tglPnlAttr("sitemap", $run); ?>>
    <h2>sitemap.xml@ SEO</h2>
    <form method="post">
        <input type="hidden" name="run" value="sitemap">
        <input type="hidden" name="txt" value="1">
        <table>
            <tr>
                <td>Type</td>
                <td>
                    <select name="smp_type">
                        <option value="">url set</option>
                        <option value="index">sitemap index</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>Loc</td>
                <td><textarea style="width: 20rem; min-height: 5rem;" name="smp_url" placeholder="ex)&#13;https://example.com/&#13;https://example.com/about/"></textarea></td>
            </tr>
        </table>
        <button type="submit">Create</button>
    </form>
    
    <hr>
    <ul>
        <li><a href="https://search.google.com/search-console/" target="_blank">Google Search Console</a></li>
        <li><a href="https://analytics.google.com/" target="_blank">Google Analytics</a></li>
    </ul>
</div>
<?php echoFoot(); ?>