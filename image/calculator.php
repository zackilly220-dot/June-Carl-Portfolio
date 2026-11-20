<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function respond($statusCode, $payload)
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['error' => 'Use POST to calculate a result.']);
}

$contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
if ($contentType !== 'application/json') {
    respond(415, ['error' => 'Send calculator values as JSON.']);
}

$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($input)) {
    respond(400, ['error' => 'The calculator request was not valid JSON.']);
}

$left = $input['left'] ?? null;
$right = $input['right'] ?? null;
$operation = $input['operation'] ?? null;

if (!is_numeric($left) || !is_numeric($right) || !is_string($operation)) {
    respond(422, ['error' => 'Enter two numbers and a supported operation.']);
}

$left = (float) $left;
$right = (float) $right;
if (!is_finite($left) || !is_finite($right)) {
    respond(422, ['error' => 'Calculator values must be finite numbers.']);
}

switch ($operation) {
    case 'add':
        $result = $left + $right;
        break;
    case 'subtract':
        $result = $left - $right;
        break;
    case 'multiply':
        $result = $left * $right;
        break;
    case 'divide':
        if ($right == 0.0) {
            respond(422, ['error' => 'Cannot divide by zero.']);
        }
        $result = $left / $right;
        break;
    default:
        respond(422, ['error' => 'That operation is not supported.']);
}

if (!is_finite($result)) {
    respond(422, ['error' => 'The result is outside the calculator’s number range.']);
}

respond(200, ['result' => $result]);
