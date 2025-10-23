<?php

namespace Box\Mod\Randomsubdomain;

class Service {
    protected $di;

    public function setDi(\Pimple\Container|null $di): void {
        $this->di = $di;
    }

    public function install(): bool {
        // Execute SQL script if needed
        $db = $this->di['db'];
        $db->exec('SELECT NOW()');

        // throw new InformationException("Throw exception to terminate module installation process with a message", array(), 123);
        return true;
    }
}