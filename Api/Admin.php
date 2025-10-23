<?php
/**
 * Random Subdomain Admin API
 */

class Api_Admin extends Api_Abstract
{
    /**
     * Generate a random subdomain
     * 
     * @return array Generated subdomain
     */
    public function generate($data)
    {
        $length = isset($data['length']) ? (int)$data['length'] : 8;
        $prefix = isset($data['prefix']) ? $data['prefix'] : '';
        
        $service = $this->getService();
        $subdomain = $service->generateRandomSubdomain($length, $prefix);
        
        return [
            'subdomain' => $subdomain
        ];
    }

    /**
     * Check if subdomain is available
     * 
     * @return array Availability status
     */
    public function check_availability($data)
    {
        $required = ['subdomain', 'domain'];
        $this->di['validator']->checkRequiredParamsForArray($required, $data);
        
        $service = $this->getService();
        $available = $service->isSubdomainAvailable($data['subdomain'], $data['domain']);
        
        return [
            'available' => $available,
            'subdomain' => $data['subdomain'],
            'domain' => $data['domain']
        ];
    }

    /**
     * Update module configuration
     * 
     * @return bool Success status
     */
    public function update_config($data)
    {
        $config = [];
        
        if (isset($data['default_domain'])) {
            $config['default_domain'] = $data['default_domain'];
        }
        
        if (isset($data['subdomain_length'])) {
            $config['subdomain_length'] = (int)$data['subdomain_length'];
        }
        
        if (isset($data['enabled'])) {
            $config['enabled'] = (bool)$data['enabled'];
        }
        
        $extensionService = $this->di['mod_service']('Extension');
        $extensionService->setConfig(['ext' => 'Randomsubdomain', 'config' => $config]);
        
        return true;
    }

    /**
     * Get module configuration
     * 
     * @return array Configuration
     */
    public function get_config($data)
    {
        $extensionService = $this->di['mod_service']('Extension');
        $config = $extensionService->getConfig('Randomsubdomain');
        
        return $config ?: [
            'default_domain' => 'yourdomain.com',
            'subdomain_length' => 8,
            'enabled' => true
        ];
    }
}