<?php
/**
 * Customer Controller
 * 
 * @package SysExperts\BusinessManager\Customers
 */

declare(strict_types=1);

namespace SysExperts\BusinessManager\Customers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use SysExperts\BusinessManager\Database\Database;
use SysExperts\BusinessManager\Auth\SessionService;
use SysExperts\BusinessManager\Navigation\NavigationService;

class CustomerController
{
    private Database $db;
    private SessionService $session;

    public function __construct(Database $db, SessionService $session)
    {
        $this->db = $db;
        $this->session = $session;
    }

    /**
     * Liste aller Kunden
     */
    public function index(Request $request, Response $response): Response
    {
        if (!$this->session->isAuthenticated()) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $user = $this->session->getUser();
        
        $customers = $this->db->fetchAll("
            SELECT * FROM bm_customers
            WHERE is_active = 1
            ORDER BY company_name
        ");

        $navService = new NavigationService($this->db);
        $navigation = $navService->getNavigation($user['id'], '/customers');

        ob_start();
        require __DIR__ . '/../../resources/views/customers/index.php';
        $html = ob_get_clean();

        $response->getBody()->write($html);
        return $response;
    }

    /**
     * Kunde erstellen
     */
    public function store(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();

        // Generiere Kundennummer
        $year = date('Y');
        $lastCustomer = $this->db->fetchOne("
            SELECT customer_number FROM bm_customers 
            WHERE customer_number LIKE ? 
            ORDER BY customer_number DESC LIMIT 1
        ", ["KD-$year-%"]);
        
        $nextNumber = 1;
        if ($lastCustomer) {
            preg_match('/KD-\d{4}-(\d+)/', $lastCustomer['customer_number'], $matches);
            $nextNumber = isset($matches[1]) ? (int)$matches[1] + 1 : 1;
        }
        $customerNumber = sprintf('KD-%s-%04d', $year, $nextNumber);

        $this->db->insert('bm_customers', [
            'tenant_id' => 1,
            'customer_number' => $customerNumber,
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'vat_id' => $data['vat_id'] ?? null,
            'street' => $data['street'] ?? null,
            'zip' => $data['zip'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? 'Deutschland',
            'notes' => $data['notes'] ?? null,
        ]);

        $_SESSION['success'] = 'Kunde erfolgreich erstellt';
        return $response->withHeader('Location', '/customers')->withStatus(302);
    }

    /**
     * Kunde bearbeiten
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        $customerId = (int) $args['id'];
        $data = $request->getParsedBody();

        $this->db->update('bm_customers', [
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'website' => $data['website'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'vat_id' => $data['vat_id'] ?? null,
            'street' => $data['street'] ?? null,
            'zip' => $data['zip'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? 'Deutschland',
            'notes' => $data['notes'] ?? null,
        ], 'id = ?', [$customerId]);

        $_SESSION['success'] = 'Kunde aktualisiert';
        return $response->withHeader('Location', '/customers')->withStatus(302);
    }

    /**
     * Kunde deaktivieren
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $customerId = (int) $args['id'];
        
        $this->db->update('bm_customers', ['is_active' => 0], 'id = ?', [$customerId]);

        $_SESSION['success'] = 'Kunde deaktiviert';
        return $response->withHeader('Location', '/customers')->withStatus(302);
    }
}
