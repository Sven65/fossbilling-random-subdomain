<?php
/**
 * Random Subdomain Generator Module
 * Automatically generates random subdomains for hosting orders
 */

namespace Box\Mod\Randomsubdomain;

class Service implements \FOSSBilling\InjectionAwareInterface
{
    protected $di;

    public function setDi(\Pimple\Container|null $di): void
    {
        $this->di = $di;
    }

    public function getDi(): ?\Pimple\Container
    {
        return $this->di;
    }

    /**
     * Generate a random subdomain
     * 
     * @param int $length Length of the subdomain
     * @param string $prefix Optional prefix for the subdomain
     * @return string Generated subdomain
     */
    public function generateRandomSubdomain($length = 8, $prefix = '')
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $subdomain = $prefix;
        
        for ($i = 0; $i < $length; $i++) {
            $subdomain .= $characters[rand(0, strlen($characters) - 1)];
        }
        
        // Ensure it starts with a letter (not a number)
        if (empty($subdomain) || is_numeric($subdomain[0])) {
            $letters = 'abcdefghijklmnopqrstuvwxyz';
            $subdomain = $letters[rand(0, 25)] . substr($subdomain, 1);
        }
        
        return $subdomain;
    }

    /**
     * Check if a subdomain is available
     * 
     * @param string $subdomain Subdomain to check
     * @param string $domain Main domain
     * @return bool True if available
     */
    public function isSubdomainAvailable($subdomain, $domain)
    {
        // Query database to check if subdomain already exists
        $sql = "SELECT COUNT(*) as count 
                FROM service_hosting 
                WHERE sld = :subdomain 
                AND tld = :domain";
        
        try {
            $result = $this->di['db']->getRow($sql, [
                'subdomain' => $subdomain,
                'domain' => $domain
            ]);
            
            return ($result['count'] == 0);
        } catch (\Exception $e) {
            error_log('Error checking subdomain availability: ' . $e->getMessage());
            return true; // Assume available if error
        }
    }

    /**
     * Generate a unique subdomain
     * 
     * @param string $domain Main domain
     * @param int $maxAttempts Maximum attempts to find unique subdomain
     * @return string|null Unique subdomain or null if failed
     */
    public function generateUniqueSubdomain($domain, $maxAttempts = 10)
    {
        $attempts = 0;
        
        while ($attempts < $maxAttempts) {
            $subdomain = $this->generateRandomSubdomain();
            
            if ($this->isSubdomainAvailable($subdomain, $domain)) {
                return $subdomain;
            }
            
            $attempts++;
        }
        
        return null;
    }

    /**
     * Hook: Inject random subdomain into hosting orders
     */
    public static function onBeforeClientOrderCreate(\Box_Event $event)
    {
        $di = $event->getDi();
        $params = $event->getParameters();
        
        // Check if this is a hosting product
        if (isset($params['product_id'])) {
            try {
                $productService = $di['mod_service']('Product');
                $product = $productService->findOne($params['product_id']);
                
                // Check if it's a hosting product
                if ($product && isset($product->type) && strpos($product->type, 'hosting') !== false) {
                    $service = $di['mod_service']('Randomsubdomain');
                    
                    // Get config
                    $config = $di['mod_service']('Extension')->getConfig('Randomsubdomain');
                    
                    // Check if enabled
                    if (!isset($config['enabled']) || $config['enabled']) {
                        $defaultDomain = isset($config['default_domain']) ? $config['default_domain'] : 'example.com';
                        
                        // Generate unique subdomain if not provided
                        if (empty($params['hosting_sld'])) {
                            $randomSubdomain = $service->generateUniqueSubdomain($defaultDomain);
                            
                            if ($randomSubdomain) {
                                $params['hosting_sld'] = $randomSubdomain;
                                $params['hosting_tld'] = $defaultDomain;
                                
                                // Log the generated subdomain
                                error_log('Random subdomain generated: ' . $randomSubdomain . '.' . $defaultDomain);
                            }
                        }
                        
                        $event->setParameters($params);
                    }
                }
            } catch (\Exception $e) {
                error_log('Error in Randomsubdomain hook: ' . $e->getMessage());
            }
        }
    }

    /**
     * Install hook - called when module is installed
     */
    public function install()
    {
        // Create configuration if needed
        $extensionService = $this->di['mod_service']('Extension');
        
        $config = [
            'default_domain' => 'yourdomain.com',
            'subdomain_length' => 8,
            'enabled' => true
        ];
        
        $extensionService->setConfig(['ext' => 'Randomsubdomain', 'config' => $config]);
        
        return true;
    }

    /**
     * Uninstall hook - called when module is uninstalled
     */
    public function uninstall()
    {
        // Clean up if needed
        return true;
    }
}