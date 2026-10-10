<?php

$config = file_get_contents('config/auth.php');
$config = str_replace(
    "'guards' => [",
    "'guards' => [\n        'api' => ['driver' => 'jwt', 'provider' => 'users'],",
    $config
);
file_put_contents('config/auth.php', $config);
echo "Auth config updated.";
