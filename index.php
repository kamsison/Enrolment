<?php
// defines constant variables
define('CACHE_DIR',     dirname(__DIR__) . '/Enrolment/storage/cache');
define('MUNEE_CACHE',   CACHE_DIR . '/Enrolment/assets');
define('CONFIG_DIR',    dirname(__DIR__) . '/Enrolment/config');
define('ROUTES_DIR',    dirname(__DIR__) . '/Enrolment/routes');
define('VIEWS_DIR',     realpath(dirname(__DIR__) . '/Enrolment/templates'));
define('IMPORT_DIR',    dirname(__DIR__) . '/Enrolment/storage/import');
define('IMAGE_DIR',     '/Enrolment/storage/import');
define('VENDOR_DIR',    dirname(__DIR__) . '/Enrolment/vendor');


require 'vendor/autoload.php';

// prepares app
$app = new \Slim\Slim([
    'debug' => true
]);

// sets timezone
date_default_timezone_set('Asia/Manila');
ini_set('date.timezone', 'Asia/Manila');

// sets environment
switch($_SERVER['HTTP_HOST']) {
    case 'ccms.ringcentral.com':
    case 'www.ccms.ringcentral.com':
        $app->config('mode', 'production');
        $app->config('debug', false);
        break;
    default:
        $app->config('mode', 'development');
}

// sets security
$slim_security = [
    "path" => "/",
    "realm" => "UM Authentication",
    "users" => [
        "admin" => "umadmin",
        "hr"    => "hradmin"
    ]
];

if($app->config('mode') == "development"){
	$slim_security['secure'] = false;
}
// adds basic security
$app->add(new \Slim\Middleware\HttpBasicAuthentication($slim_security));

// includes config files
require CONFIG_DIR . '/auth.php';
require CONFIG_DIR . '/db.php';
require CONFIG_DIR . '/views.php';
require CONFIG_DIR . '/logger.php';
require CONFIG_DIR . '/sessions.php';
require CONFIG_DIR . '/mailer.php';

// checks defined session
$app->hook('slim.before', function () use ($app) {
    if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
        $app->view()->set('loggedInUser', $_SESSION['user']);
    }

    // gets count of new suspected files
    $suspectedFileTotal = \um\models\File::getNewFileTotalByStatus(2);
    $app->view()->set('suspectedFileTotal', $suspectedFileTotal);

    // gets count of new positive files
    $positiveFileTotal = \um\models\File::getNewFileTotalByStatus(4);
    $app->view()->set('positiveFileTotal', $positiveFileTotal);

    // gets count of new WIP files
    $wipFileTotal = \um\models\File::getNewFileTotalByStatus(3);
    $app->view()->set('wipFileTotal', $wipFileTotal);

    // gets count of DPF to-do by user type
    $dpfTodoTotal = \um\models\Dpf::getDpfTodoTotal();
    $app->view()->set('dpfTodoTotal', $dpfTodoTotal);

    if ($dpfTodoTotal->count()) {
        $lastDpfTodo = \Carbon\Carbon::parse($dpfTodoTotal[0]['dpf_created_at']);
        $dpf_created_at = $lastDpfTodo->diffInMinutes(\Carbon\Carbon::now('Asia/Manila'));
        $app->view()->set('dpf_created_at', $dpf_created_at);
    }

    // gets count of CAF to-do by user type
    $cafTodoTotal = \um\models\Caf::getCafTodoTotal();
    $app->view()->set('cafTodoTotal', $cafTodoTotal);

    if ($cafTodoTotal->count()) {
        $lastCafTodo = \Carbon\Carbon::parse($cafTodoTotal[0]['caf_created_at']);
        $caf_created_at = $lastCafTodo->diffInMinutes(\Carbon\Carbon::now('Asia/Manila'));
        $app->view()->set('caf_created_at', $caf_created_at);
    }

    // gets count of CAF For Secure Deletion
    $cafMarkSecurelyDeletedTotal = \um\models\Caf::getCafMarkSecurelyDeletedTotal();
    $app->view()->set('cafMarkSecurelyDeletedTotal', $cafMarkSecurelyDeletedTotal);

    if ($cafMarkSecurelyDeletedTotal->count()) {
        $lastMarkSecurelyDeleted = \Carbon\Carbon::parse($cafMarkSecurelyDeletedTotal[0]['created_at']);
        $created_at = $lastMarkSecurelyDeleted->diffInMinutes(\Carbon\Carbon::now('Asia/Manila'));
        $app->view()->set('created_at', $created_at);
    }
}, 5);

// defines routes
require ROUTES_DIR . '/users/users.php';
require ROUTES_DIR . '/users/dashboard.php';
require ROUTES_DIR . '/users/import.php';
require ROUTES_DIR . '/users/dpf.php';
require ROUTES_DIR . '/users/caf.php';
require ROUTES_DIR . '/users/settings.php';
require ROUTES_DIR . '/payments/payment.php';
require ROUTES_DIR . '/assessments/assessment.php';
require ROUTES_DIR . '/assets.php';
require ROUTES_DIR . '/payments/payment.php';
require ROUTES_DIR . '/students/students.php';
require ROUTES_DIR . '/assessments/assessment.php';


// renders default page
$app->get('/', function () use ($app) {
    $app->render('index.twig');
});

// runs app
$app->run();