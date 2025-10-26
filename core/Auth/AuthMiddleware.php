<?php

namespace SysExperts\BusinessManager\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Authentication Middleware
 * Prüft ob Benutzer eingeloggt ist
 */
class AuthMiddleware
{
    private AuthService $authService;
    private SessionManager $sessionManager;

    public function __construct(AuthService $authService, SessionManager $sessionManager)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Middleware-Handler
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        // Session prüfen
        $user = $this->sessionManager->getCurrentUser($this->authService);

        if (!$user) {
            // Nicht eingeloggt → Redirect zu Login
            $response = new \Slim\Psr7\Response();
            return $response
                ->withHeader('Location', '/auth/login')
                ->withStatus(302);
        }

        // Benutzer ist eingeloggt → Request mit User-Objekt erweitern
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
