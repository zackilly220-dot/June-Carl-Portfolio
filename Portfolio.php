<?php
function redirectToPortfolio($status)
{
    header('Location: index.html?contact=' . rawurlencode($status) . '#contact', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html', true, 303);
    exit;
}

$indexPage = file_get_contents(__DIR__ . '/index.html');
if (
    $indexPage === false ||
    preg_match('~<script type="application/json" id="profile-data">\s*(.*?)\s*</script>~s', $indexPage, $matches) !== 1
) {
    http_response_code(500);
    exit('Could not load portfolio profile data.');
}

$profile = json_decode($matches[1], true);
if (!is_array($profile) || !isset($profile['email']) || !is_string($profile['email'])) {
    http_response_code(500);
    exit('Portfolio profile email is missing or invalid.');
}
$recipientEmail = trim($profile['email']);

$name = $_POST['name'] ?? null;
$email = $_POST['email'] ?? null;
$message = $_POST['message'] ?? null;

if (
    !is_string($name) ||
    !is_string($email) ||
    !is_string($message)
) {
    redirectToPortfolio('invalid');
}

$name = trim($name);
$email = trim($email);
$message = trim($message);

if (
    $name === '' ||
    strlen($name) > 100 ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    strlen($message) === 0 ||
    strlen($message) > 5000
) {
    redirectToPortfolio('invalid');
}

if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL) || $recipientEmail === 'your.email@example.com') {
    redirectToPortfolio('not_configured');
}

$subject = 'Portfolio contact form message';
$body = "From: {$name}\nEmail: {$email}\n\n{$message}";
$headers = [
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

if (!mail($recipientEmail, $subject, $body, implode("\r\n", $headers))) {
    redirectToPortfolio('failed');
}

redirectToPortfolio('sent');