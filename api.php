<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\auth\JwtFilter;
use App\core\ApiException;
use App\auth\AuthRouter;
use App\interns\InternRouter;
use App\users\UserRouter;
use App\documents\DocumentRouter;
use App\activity_reports\ActivityReportRouter;
use App\meetings\MeetingRouter;
use App\messaging\MessageRouter;

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$statusCode = 200;
$response = [
    'success' => false,
    'message' => 'URL not found.',
    'data' => null
];

try {
    JwtFilter::handle();

    $resource = $_GET['resource'] ?? null;
    $router = null;

    switch ($resource) {
        case 'auth':
            $router = new AuthRouter();
            break;
        case 'interns':
            $router = new InternRouter();
            break;
        case 'users':
            $router = new UserRouter();
            break;
        case 'documents':
            $router = new DocumentRouter();
            break;
        case 'activity-reports':
            $router = new ActivityReportRouter();
            break;
        case 'meetings':
            $router = new MeetingRouter();
            break;
        case 'messaging':
            $router = new MessageRouter();
    }

    if ($router) {
        $response = $router->handleRequest();
        $statusCode = http_response_code();
    } else {
        $statusCode = 404;
    }

} catch (ApiException $ex) {
    $statusCode = $ex->getCode();
    $response['success'] = false;
    $response['message'] = $ex->getMessage();
} catch (Exception $ex) {
    $statusCode = 500;
    $response['success'] = false;
    $response['message'] = 'Internal Server Error';
}

http_response_code($statusCode);
echo json_encode($response);
exit();