<?php

namespace App\Services\Legacy;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LegacyRuntimeService
{
    private function verboseLog(string $message): void
    {
        if ((bool) config('piprapay.legacy_runtime.verbose_logs', false)) {
            \Illuminate\Support\Facades\Log::info($message);
        }
    }

    public function dispatch(Request $request, ?string $overridePage = null): Response
    {
        $legacyIndex = base_path('resources/legacy-index.php');

        if (!file_exists($legacyIndex)) {

            return response(view('404'), 404);
        }

        $path = $overridePage;

        if ($path === null) {
            $requestPath = trim($request->path(), '/');
            $path = ($requestPath === '' || $requestPath === '.') ? '' : $requestPath;
        }

        $originalGet = $_GET;
        $originalPost = $_POST;
        $originalRequest = $_REQUEST;
        $originalCookie = $_COOKIE;
        $originalServer = $_SERVER;

        $_GET = array_merge($request->query->all(), ['page' => $path]);
        $_POST = $request->request->all();
        $_REQUEST = array_merge($_GET, $_POST, $request->cookies->all());
        $_COOKIE = $request->cookies->all();

        $_SERVER['REQUEST_METHOD'] = $request->method();
        $_SERVER['REQUEST_URI'] = $request->getRequestUri();
        $_SERVER['HTTP_HOST'] = $request->getHost();
        $_SERVER['SERVER_PORT'] = (string) $request->getPort();
        $_SERVER['HTTPS'] = $request->isSecure() ? 'on' : 'off';
        $_SERVER['REMOTE_ADDR'] = (string) ($request->ip() ?? '127.0.0.1');

        $this->verboseLog('LegacyRuntimeService - session_status before: ' . session_status());
        $this->verboseLog('LegacyRuntimeService - headers_sent: ' . (headers_sent() ? 'true' : 'false'));

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
            $this->verboseLog('LegacyRuntimeService - Started session');
        }

        if (function_exists('csrf_token') && csrf_token()) {
            $_SESSION['csrf_token'] = csrf_token();
            $this->verboseLog('LegacyRuntimeService - Injected CSRF token: ' . csrf_token());
            $this->verboseLog('LegacyRuntimeService - Session CSRF is now: ' . $_SESSION['csrf_token']);
        }

        $site_url = rtrim(config('app.url'), '/') . '/';
        $GLOBALS['site_url']  = $site_url;
        $GLOBALS['db_prefix'] = env('DB_PREFIX', 'pp_');

        $headersBefore = headers_list();
        $statusBefore = http_response_code() ?: 200;

        ob_start();
        try {
            require $legacyIndex;
        } catch (\Throwable $e) {
            ob_end_clean();
            $_GET = $originalGet;
            $_POST = $originalPost;
            $_REQUEST = $originalRequest;
            $_COOKIE = $originalCookie;
            $_SERVER = $originalServer;
            throw $e;
        }
        $content = (string) ob_get_clean();

        $_GET = $originalGet;
        $_POST = $originalPost;
        $_REQUEST = $originalRequest;
        $_COOKIE = $originalCookie;
        $_SERVER = $originalServer;

        $statusAfter = http_response_code() ?: $statusBefore;
        $response = response($content, $statusAfter);

        $headersAfter = headers_list();
        $newHeaders = array_values(array_diff($headersAfter, $headersBefore));

        foreach ($newHeaders as $headerLine) {
            $parts = explode(':', $headerLine, 2);
            if (count($parts) === 2) {
                $headerName = trim($parts[0]);
                $response->headers->set($headerName, trim($parts[1]), false);
                header_remove($headerName);
            }
        }

        return $response;
    }
}
