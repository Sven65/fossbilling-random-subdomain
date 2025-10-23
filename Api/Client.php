<?php
/**
 * Random Subdomain Client API
 */

namespace Box\Mod\Randomsubdomain\Api;

class Client extends \Api_Abstract
{
    /**
     * Generate a random subdomain for clients
     * 
     * @return array Generated subdomain
     */
    public function generate($data)
    {
        $config = $this->di['mod_service']('Extension')->getConfig('Randomsubdomain');
        $length = isset($config['subdomain_length']) ? $config['subdomain_length'] : 8;
        $domain = isset($config['default_domain']) ? $config['default_domain'] : 'example.com';
        
        $service = $this->getService();
        $subdomain = $service->generateUniqueSubdomain($domain);
        
        if (!$subdomain) {
            throw new \FOSSBilling\Exception('Unable to generate unique subdomain. Please try again.');
        }
        
        return [
            'subdomain' => $subdomain,
            'domain' => $domain,
            'full' => $subdomain . '.' . $domain
        ];
    }

    /**
     * Check if a subdomain is available
     * 
     * @return array Availability status
     */
    public function check_availability($data)
    {
        if (!isset($data['subdomain'])) {
            throw new \FOSSBilling\Exception('Subdomain is required');
        }
        
        $config = $this->di['mod_service']('Extension')->getConfig('Randomsubdomain');
        $domain = isset($data['domain']) ? $data['domain'] : $config['default_domain'];
        
        $service = $this->getService();
        $available = $service->isSubdomainAvailable($data['subdomain'], $domain);
        
        return [
            'available' => $available,
            'subdomain' => $data['subdomain'],
            'domain' => $domain
        ];
    }
}