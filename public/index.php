<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Jobdating\Core\Router;

$router = Router::getRouter();

$router->get('/', function() {
    echo "Welcome to Job Dating Application!";
});

$router->get('/jobs', function() {
    echo "List of all jobs";
});

$router->get('/jobs/{id}', function($id) {
    echo "Job details for ID: " . htmlspecialchars($id);
});

$router->post('/jobs', function() {
    echo "Create new job";
});

$router->get('/candidates', function() {
    echo "List of all candidates";
});

$router->get('/candidates/{id}', function($id) {
    echo "Candidate details for ID: " . htmlspecialchars($id);
});

$router->post('/candidates', function() {
    echo "Register new candidate";
});

$router->dispatch();