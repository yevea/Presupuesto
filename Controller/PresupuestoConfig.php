<?php
namespace FacturaScripts\Plugins\Presupuesto\Controller;

use FacturaScripts\Core\Base\Controller;
use FacturaScripts\Core\Base\ControllerPermissions;
use FacturaScripts\Core\Tools;
use Symfony\Component\HttpFoundation\Response;

class PresupuestoConfig extends Controller
{
    public $woocommerce_url = '';
    public $woocommerce_key = '';
    public $woocommerce_secret = '';
    
    public function getPageData(): array
    {
        $pageData = parent::getPageData();
        $pageData['title'] = 'WooCommerce Sync';
        $pageData['menu'] = 'admin';
        $pageData['icon'] = 'fas fa-sync-alt';
        $pageData['showonmenu'] = true;
        return $pageData;
    }

    public function privateCore(&$response, $user, $permissions): void
    {
        parent::privateCore($response, $user, $permissions);
        
        // Load settings
        $this->loadSettings();
        
        // Handle form submission
        if ($this->request->getMethod() === 'POST' && $this->request->request->get('action') === 'save') {
            $this->saveSettings();
        }
        
        // Handle test connection
        if ($this->request->get('action') === 'test') {
            $this->testConnection();
        }
    }
    
    private function loadSettings(): void
    {
        // Try POST first
        if ($this->request->getMethod() === 'POST') {
            $this->woocommerce_url = $this->request->request->get('woocommerce_url', '');
            $this->woocommerce_key = $this->request->request->get('woocommerce_key', '');
            $this->woocommerce_secret = $this->request->request->get('woocommerce_secret', '');
            
            if (!empty($this->woocommerce_url)) {
                return;
            }
        }
        
        // Load from database
        $this->woocommerce_url = Tools::settings('Presupuesto', 'woocommerce_url', '');
        $this->woocommerce_key = Tools::settings('Presupuesto', 'woocommerce_key', '');
        $this->woocommerce_secret = Tools::settings('Presupuesto', 'woocommerce_secret', '');
    }
    
    private function saveSettings(): void
    {
        $url = $this->request->request->get('woocommerce_url', '');
        $key = $this->request->request->get('woocommerce_key', '');
        $secret = $this->request->request->get('woocommerce_secret', '');
        
        if (empty($url) || empty($key) || empty($secret)) {
            return;
        }
        
        Tools::settingsSet('Presupuesto', 'woocommerce_url', $url);
        Tools::settingsSet('Presupuesto', 'woocommerce_key', $key);
        Tools::settingsSet('Presupuesto', 'woocommerce_secret', $secret);
        
        $this->woocommerce_url = $url;
        $this->woocommerce_key = $key;
        $this->woocommerce_secret = $secret;
        
        header('Location: ' . $this->url() . '?saved=1');
        exit();
    }
    
    private function testConnection(): void
    {
        if (empty($this->woocommerce_url) || empty($this->woocommerce_key) || empty($this->woocommerce_secret)) {
            header('Location: ' . $this->url() . '?error=Please save settings first');
            exit();
        }
        
        try {
            $wooApi = new \FacturaScripts\Plugins\Presupuesto\Lib\WooCommerceAPI();
            
            if ($wooApi->testConnection()) {
                header('Location: ' . $this->url() . '?success=Connection successful');
            } else {
                header('Location: ' . $this->url() . '?error=Connection failed');
            }
        } catch (\Exception $e) {
            header('Location: ' . $this->url() . '?error=' . urlencode($e->getMessage()));
        }
        exit();
    }
    
    protected function createViews(): void
    {
        // Empty
    }
}
