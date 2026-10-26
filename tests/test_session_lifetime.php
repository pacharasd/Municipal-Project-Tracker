<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SecurityHeaders.php';
require_once __DIR__ . '/../app/Core/Session.php';

use App\Core\Session;

putenv('SESSION_LIFETIME=120');

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

Session::start();
check(session_name() === 'MPTSESSID', 'The app must use its own session cookie');
check((int)ini_get('session.gc_maxlifetime') === 7200, 'Session files must survive the configured lifetime');
check((int)session_get_cookie_params()['lifetime'] === 7200, 'Cookie lifetime must match the session lifetime');
check(str_ends_with(str_replace('\\', '/', session_save_path()), '/storage/sessions'), 'Session files must use the app directory');

Session::set('user_id', 123);
$activeId = session_id();
session_write_close();

Session::start();
check(Session::get('user_id') === 123, 'An active session must survive a new request');
Session::set('_last_activity', time() - 7201);
session_write_close();

Session::start();
check(!Session::has('user_id'), 'An inactive session must expire');
check(session_id() !== $activeId, 'An expired session must receive a new ID');
Session::destroy();

echo "Session lifetime checks passed\n";
