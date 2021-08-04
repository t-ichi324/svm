<?php include_once __DIR__.DIRECTORY_SEPARATOR.".func.php";
    FM::read();

    //to zip download
    if(FM::isDirectory()){
        if(formval("op") == "dl"){
            $di = FM::getDirectoryInfo();
            try{
                $zipfile = Path::tmp( $di->name()."_".date("Ymd_his").".zip" );
                if(ZipUtil::toZip($di->fullName(), $zipfile) === true){
                    Response::fileDownload($zipfile); die();
                }
            } catch (Exception $ex) { Message::$err = $ex->getMessage(); }
        }
        HtmlEcho::NOT_FOUND();
        die();
    }
    
    //Un zip
    $fi = FM::getFileInfo();
    $k = url64_encode($fi->fullName());
    $to = $fi->baseDirectory().DIRECTORY_SEPARATOR.$fi->name(false);
    
    if(isNotEmpty(formval("to"))){
        $to = formval("to");
        $di = new DirectoryInfo($to);
        if(ZipUtil::unZip($fi->fullName(), $di->fullName()) === true){
            Response::redirect("./dir.php?i=".FM::toId($di->fullName()));
            die();
        }
        Message::$err = "ERR: undefined error. [ ".$i." ]";
    }
    
    HtmlEcho::HEAD("UN ZIP");
    FM::echo_breadcrumb($fi->fullName());
?>
<hr>
<form method="get">
<table>
<tbody>
<tr>
    <td>UnZip To:</td>
    <td style="min-width: 40em"><input type="text" style="width: 100%; padding: 5px 0;" name="to" value="<?= h($to); ?>"></td>
</tr>
<tr>
    <td colspan="2" style="text-align: center">
        <input type="hidden" name="i" value="<?=FM::$id;?>">
        <button type="submit" style="margin-top: 20px; width: 100%; padding: 0.5em;">UN-ZIP</button>
    </td>
</tr>
</tbody>
</table>
</form>

<?php HtmlEcho::FOOT(); ?>
