<?php
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
<h2>Under Construction...</h2>
</html>