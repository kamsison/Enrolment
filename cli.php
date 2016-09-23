<?php
// defines constant variables
define('CACHE_DIR',     dirname(__DIR__) . '/storage/cache');
define('MUNEE_CACHE',   CACHE_DIR . '/assets');
define('CONFIG_DIR',    dirname(__DIR__) . '/config');
define('ROUTES_DIR',    dirname(__DIR__) . '/routes');
define('VIEWS_DIR',     realpath(dirname(__DIR__) . '/templates'));
define('WEBROOT',       realpath(dirname(__DIR__) . '/public'));

require __DIR__.'/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Application;

$capsule = new Capsule;
$capsule->addConnection(array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'ims',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix'    => ''
));
$capsule->setAsGlobal();
$capsule->bootEloquent();
 
$application = new Application();
$application->add(new \um\commands\Seeder());
$application->run();