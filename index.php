<?php include __DIR__.DIRECTORY_SEPARATOR."auth".DIRECTORY_SEPARATOR.".func.php";

http_response_code(404);
$title = htmlspecialchars($_SERVER["SERVER_NAME"]);
?>
<!DOCTYPE html>
<html lang="en">
    <head><title><?=$title;?></title>
    <meta charset="UTF-8" />
    <meta name="robots" content="noindex,nofollow,noarchive" />
</head>
<body style="text-align:center;">
<h1><?=$title;?></h1>
<hr>
<h2><a href="./auth">AUTH</a></h2>
</html>