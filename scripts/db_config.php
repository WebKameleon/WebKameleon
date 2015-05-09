<?php




$config = parse_ini_file(__DIR__ . '/../application/configs/application.ini', true);
$config = $config['production'];

if (file_exists(__DIR__ . '/../application/configs/local.ini')) {
    $localConfig = parse_ini_file(__DIR__ . '/../application/configs/local.ini');
    $config = array_merge($config, $localConfig);
}

$config['db.adapter'] = str_replace('pdo_', '', $config['db.adapter']);
return $config;