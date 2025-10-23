<?php
/**
 * Random Subdomain Admin Controller
 */

class Controller_Admin
{
    protected $di;

    public function setDi($di)
    {
        $this->di = $di;
    }

    public function getDi()
    {
        return $this->di;
    }

    /**
     * Default admin page
     */
    public function index()
    {
        $this->di['is_admin_logged'];
        return $this->di['view']->render('mod_Randomsubdomain_index');
    }

    /**
     * Settings page
     */
    public function settings()
    {
        $this->di['is_admin_logged'];
        return $this->di['view']->render('mod_Randomsubdomain_settings');
    }
}