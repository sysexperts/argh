<?php
/**
 * Web Routes
 * 
 * @package SysExperts\BusinessManager
 */

declare(strict_types=1);

use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Home / Dashboard
$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('
        <!DOCTYPE html>
        <html lang="de">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Business Manager - sys-experts.de</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: white;
                }
                .container {
                    text-align: center;
                    padding: 2rem;
                }
                h1 { font-size: 3rem; margin-bottom: 1rem; }
                p { font-size: 1.2rem; opacity: 0.9; margin-bottom: 2rem; }
                .status {
                    background: rgba(255,255,255,0.1);
                    backdrop-filter: blur(10px);
                    border-radius: 12px;
                    padding: 2rem;
                    margin-top: 2rem;
                }
                .status-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 0.5rem 0;
                    border-bottom: 1px solid rgba(255,255,255,0.1);
                }
                .status-item:last-child { border-bottom: none; }
                .badge {
                    background: #10b981;
                    padding: 0.25rem 0.75rem;
                    border-radius: 6px;
                    font-size: 0.875rem;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>🚀 Business Manager</h1>
                <p>Modulare mandantenfähige Business-Software</p>
                <p style="font-size: 1rem; opacity: 0.7;">von sys-experts.de</p>
                
                <div class="status">
                    <div class="status-item">
                        <span>System Status</span>
                        <span class="badge">✓ Online</span>
                    </div>
                    <div class="status-item">
                        <span>PHP Version</span>
                        <span class="badge">' . PHP_VERSION . '</span>
                    </div>
                    <div class="status-item">
                        <span>Framework</span>
                        <span class="badge">Slim 4</span>
                    </div>
                    <div class="status-item">
                        <span>Datenbank</span>
                        <span class="badge">SQLite</span>
                    </div>
                </div>
            </div>
        </body>
        </html>
    ');
    return $response;
});

// API Health Check
$app->get('/api/health', function (Request $request, Response $response) {
    $data = [
        'status' => 'ok',
        'timestamp' => date('Y-m-d H:i:s'),
        'version' => '0.1.0',
    ];
    
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json');
});

// Auth Routes (später)
$app->group('/auth', function (RouteCollectorProxy $group) {
    $group->get('/login', function (Request $request, Response $response) {
        $response->getBody()->write('Login Page - Coming Soon');
        return $response;
    });
    
    $group->post('/login', function (Request $request, Response $response) {
        $response->getBody()->write('Login Handler - Coming Soon');
        return $response;
    });
    
    $group->get('/logout', function (Request $request, Response $response) {
        $response->getBody()->write('Logout Handler - Coming Soon');
        return $response;
    });
});
