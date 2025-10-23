<?php
declare(strict_types=1);

namespace FOSSBilling\Module\ServiceRandomSubdomain;

class ServiceProvision
{
	private \Pimple\Container $di;
	private int $maxAttempts = 6;

	public function __construct(\Pimple\Container $di)
	{
		$this->di = $di;
	}

	public function generateAndReserveSubdomain(string $sessionId): string
	{
		$baseDomain = $this->getConfigValue('base_domain', 'example.com');

		for ($i = 0; $i < $this->maxAttempts; $i++) {
			$label = 'u' . bin2hex(random_bytes(3));
			$label = substr(preg_replace('/[^a-z0-9-]/', '', strtolower($label)), 0, 63);
			$fullDomain = $label . '.' . $baseDomain;

			if (!$this->labelExists($label) && !$this->isReserved($label)) {
				$this->di['db']->exec(
					'INSERT INTO reserved_subdomains (subdomain, session_id) VALUES (:subdomain, :session_id)',
					['subdomain' => $fullDomain, 'session_id' => $sessionId]
				);
				return $fullDomain;
			}
		}

		throw new \RuntimeException('Unable to generate unique subdomain');
	}

	private function isReserved(string $label): bool
	{
		$rows = $this->di['db']->getAll(
			'SELECT subdomain FROM reserved_subdomains WHERE subdomain LIKE :like',
			['like' => '%' . $this->di['db']->escape($label) . '%']
		);
		return count($rows) > 0;
	}

	private function labelExists(string $label): bool
	{
		$rows = $this->di['db']->getAll(
			'SELECT params FROM client_order WHERE params LIKE :like',
			['like' => '%' . $this->di['db']->escape($label) . '%']
		);
		foreach ($rows as $r) {
			$p = json_decode($r['params'] ?? '{}', true);
			if (!empty($p['assigned_subdomain']) && str_starts_with($p['assigned_subdomain'], $label . '.')) {
				return true;
			}
		}
		return false;
	}

	public function claimReservedSubdomain(string $sessionId): ?string
	{
		$row = $this->di['db']->getRow(
			'SELECT subdomain FROM reserved_subdomains WHERE session_id = :session_id ORDER BY created_at DESC LIMIT 1',
			['session_id' => $sessionId]
		);
		if (!$row) return null;

		$this->di['db']->exec(
			'DELETE FROM reserved_subdomains WHERE subdomain = :subdomain',
			['subdomain' => $row['subdomain']]
		);

		return $row['subdomain'];
	}

	public function assignSubdomainToOrder(int $orderId, string $subdomain): void
	{
		$order = $this->di['db']->getRow('SELECT * FROM client_order WHERE id = :id', ['id' => $orderId]);
		if (!$order) throw new \RuntimeException('Order not found');

		$params = json_decode($order['params'] ?? '{}', true);
		if (!is_array($params)) $params = [];
		$params['assigned_subdomain'] = $subdomain;

		$this->di['db']->exec(
			'UPDATE client_order SET params = :params WHERE id = :id',
			['params' => json_encode($params), 'id' => $orderId]
		);

		$this->di['db']->exec(
			'UPDATE service SET domain = :domain WHERE order_id = :id',
			['domain' => $subdomain, 'id' => $orderId]
		);
	}

	private function getConfigValue(string $key, $default = null)
	{
		if (!isset($this->di['mod_service'])) return $default;
		try {
			$moduleConfig = $this->di['mod_service']('extension')->getConfig('ServiceRandomSubdomain');
			return $moduleConfig[$key] ?? $default;
		} catch (\Throwable) {
			return $default;
		}
	}
}
