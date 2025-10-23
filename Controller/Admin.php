<?php
/**
 * Random Subdomain Admin Controller
 */

namespace Box\Mod\Randomsubdomain\Controller;

class Admin implements \FOSSBilling\InjectionAwareInterface
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
     * Default admin page
     */
    public function register(\Box_App &$app)
    {
        $app->get('/randomsubdomain', 'get_index', [], static::class);
        $app->get('/randomsubdomain/settings', 'get_settings', [], static::class);
    }

    public function get_index(\Box_App $app)
    {
        $this->di['is_admin_logged'];
        return $app->render('mod_Randomsubdomain_index');
    }

    public function get_settings(\Box_App $app)
    {
        $this->di['is_admin_logged'];
        return $app->render('mod_Randomsubdomain_settings');
    }
}