<?php
namespace FacturaScripts\Plugins\Presupuesto\Lib;

use FacturaScripts\Core\Tools;

class WooCommerceAPI
{
    private $url;
    private $consumerKey;
    private $consumerSecret;

    public function __construct()
    {
        $this->url = Tools::settings('Presupuesto', 'woocommerce_url', '');
        $this->consumerKey = Tools::settings('Presupuesto', 'woocommerce_key', '');
        $this->consumerSecret = Tools::settings('Presupuesto', 'woocommerce_secret', '');
    }

    public function testConnection(): bool
    {
        try {
            $response = $this->request('GET', '/wp-json/wc/v3/products', ['per_page' => 1]);
            return is_array($response);
        } catch (\Exception $e) {
            Tools::log()->error('WooCommerce API Error: ' . $e->getMessage());
            return false;
        }
    }

    public function getOrders(array $params = []): array
    {
        return $this->request('GET', '/wp-json/wc/v3/orders', $params);
    }

    public function getProducts(array $params = []): array
    {
        return $this->request('GET', '/wp-json/wc/v3/products', $params);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        if (empty($this->url) || empty($this->consumerKey) || empty($this->consumerSecret)) {
            throw new \Exception('WooCommerce API not configured');
        }

        $url = rtrim($this->url, '/') . $endpoint;
        
        $url .= '?' . http_build_query([
            'consumer_key' => $this->consumerKey,
            'consumer_secret' => $this->consumerSecret
        ]);
        
        if ($method === 'GET' && !empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }
        
        $data = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response');
        }
        
        return $data ?? [];
    }
}
