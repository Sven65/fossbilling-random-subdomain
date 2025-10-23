<?php
declare(strict_types=1);

namespace FOSSBilling\Module\ServiceRandomSubdomain;

class Module
{
	// Module configuration
	public function getConfig(): array
	{
		return [
			'base_domain' => [
				'type' => 'text',
				'label' => 'Base domain',
				'description' => 'Base domain to append to generated labels',
				'default' => 'example.com'
			]
		];
	}

	// Create DB table on install
	public function install(): void
	{
		$db = $this->di['db'];
		$db->exec("
			CREATE TABLE IF NOT EXISTS reserved_subdomains (
				id INT AUTO_INCREMENT PRIMARY KEY,
				subdomain VARCHAR(63) NOT NULL UNIQUE,
				session_id VARCHAR(128) NOT NULL,
				created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
			)
		");
	}

	// Optional cleanup on uninstall
	public function uninstall(): void
	{
		$db = $this->di['db'];
		$db->exec("DROP TABLE IF EXISTS reserved_subdomains");
	}

	// Display subdomain in client order form
	public static function onBeforeClientOrderFormRender(\Box_Event $event): void
	{
		$di = $event->getParameters()['di'] ?? $GLOBALS['di'] ?? null;
		if (!$di) return;

		$sessionId = session_id();
		$svc = new ServiceProvision($di);
		$subdomain = $svc->generateAndReserveSubdomain($sessionId);

		$event->setTemplateVar('random_subdomain', $subdomain);
	}

	// Assign reserved subdomain on order creation
	public static function onAfterClientOrderCreate(\Box_Event $event): void
	{
		$di = $event->getParameters()['di'] ?? $GLOBALS['di'] ?? null;
		$orderId = $event->getParameters()['order_id'] ?? null;
		if (!$di || !$orderId) return;

		$sessionId = session_id();
		$svc = new ServiceProvision($di);

		$subdomain = $svc->claimReservedSubdomain($sessionId);
		if ($subdomain) {
			$svc->assignSubdomainToOrder((int)$orderId, $subdomain);
		}
	}
}
