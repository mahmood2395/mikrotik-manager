<?php

namespace App\Services;

use App\Models\Router;
use RouterOS\Client;
use RouterOS\Query;
use Exception;

class MikrotikService
{
    protected Client $client;
    protected Router $router;

    /**
     * Connect to a router
     * Throws an exception if connection fails
     */
    public function connect(Router $router): self
    {
        $this->router = $router;  // already there ✅

        $this->client = new Client([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => $router->api_port,
        ]);

        // When connecting to a router
        Log::info('MikroTik connection attempt', [
            'router_id' => $router->id,
            'router_ip' => $router->ip_address,
        ]);

        // On success
        Log::info('MikroTik command executed', [
            'router_id'      => $router->id,
            'command'        => $command,
            'execution_time' => $executionTime,
        ]);

        // On failure
        Log::error('MikroTik connection failed', [
            'router_id' => $router->id,
            'router_ip' => $router->ip_address,
            'error'     => $e->getMessage(),
        ]);
        $router->update(['last_seen' => now()]);

        return $this;
    }

    /**
     * Run any RouterOS command and return the response
     * Example: $service->command('/ip/address/print')
     */
    public function command(string $command): array
    {
        $query = new Query($command);
        return $this->client->query($query)->read();
    }

    /**
     * Run a command with filters
     * Example: $service->commandWhere('/ip/firewall/filter/print', 'chain', 'input')
     */
    public function commandWhere(string $command, string $key, string $value): array
    {
        $query = (new Query($command))->where($key, $value);
        return $this->client->query($query)->read();
    }

    /**
     * Get all interfaces
     */
    public function getInterfaces(): array
    {
        return $this->command('/interface/print');
    }

    /**
     * Get IP addresses
     */
    public function getIpAddresses(): array
    {
        return $this->command('/ip/address/print');
    }

    /**
     * Get firewall filter rules
     */
    public function getFirewallRules(): array
    {
        return $this->command('/ip/firewall/filter/print');
    }

    /**
     * Get system resources (CPU, memory, uptime)
     */
    public function getSystemResources(): array
    {
        $result = $this->command('/system/resource/print');
        return $result[0] ?? [];
    }

    /**
     * Get system identity (router name)
     */
    public function getIdentity(): string
    {
        $result = $this->command('/system/identity/print');
        return $result[0]['name'] ?? 'Unknown';
    }

    /**
     * Test connection — returns true if reachable, false if not
     */
    public static function testConnection(Router $router): bool
    {
        try {
            (new self())->connect($router)->getIdentity();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Execute a RouterOS script on the router
     * 
     * Strategy:
     * 1. Check if a script with this name already exists
     * 2. If yes — update its source
     * 3. If no — create it
     * 4. Run it
     * 5. Clean up (remove it)
     */
    public function executeScript(string $scriptName, string $scriptContent): array
    {
        // Check if script already exists
        $existing = $this->client
            ->query((new Query('/system/script/print'))
                ->where('name', $scriptName))
            ->read();

        if (!empty($existing)) {
            // Update existing script
            $this->client->query(
                (new Query('/system/script/set'))
                    ->equal('.id', $existing[0]['.id'])
                    ->equal('source', $scriptContent)
            )->read();
        } else {
            // Create new script
            $this->client->query(
                (new Query('/system/script/add'))
                    ->equal('name', $scriptName)
                    ->equal('source', $scriptContent)
            )->read();
        }

        // Run the script
        $result = $this->client->query(
            (new Query('/system/script/run'))
                ->equal('name', $scriptName)
        )->read();

        // Clean up — remove the script after execution
        $this->client->query(
            (new Query('/system/script/remove'))
                ->equal('numbers', $scriptName)
        )->read();

        return $result;
    }

    /**
     * Get all scripts from router — explicitly request source field
     */
    public function getRouterScripts(): array
    {
        return $this->getRouterScriptsRest($this->router);
    }

    /**
     * Get full script source using file workaround
     */
    public function getRouterScript(string $name): ?array
    {
        return $this->getRouterScriptRest($this->router, $name);
    }

    /**
     * Push a script to the router permanently (not run, just save)
     * Updates if exists, creates if not
     */
    public function pushScript(string $name, string $content): void
    {
        $existing = $this->client
            ->query((new Query('/system/script/print'))
                ->where('name', $name))
            ->read();

        if (!empty($existing)) {
            $this->client->query(
                (new Query('/system/script/set'))
                    ->equal('.id', $existing[0]['.id'])
                    ->equal('source', $content)
            )->read();
        } else {
            $this->client->query(
                (new Query('/system/script/add'))
                    ->equal('name', $name)
                    ->equal('source', $content)
            )->read();
        }
    }

    /**
     * Import all scripts from a router
     * Returns array of ['name' => ..., 'source' => ...]
     */
public function importScripts(): array
{
    return $this->getRouterScriptsRest($this->router);
}

/**
 * Make a REST API call to RouterOS 7
 * REST API runs on port 80 (HTTP) or 443 (HTTPS)
 * No truncation issues unlike the binary API on port 8728
 */
    protected function restGet(Router $router, string $endpoint): array
    {
        // Use HTTPS if rest_port is 443, otherwise HTTP
        $protocol = ($router->rest_port ?? 80) == 443 ? 'https' : 'http';
        $port     = $router->rest_port ?? 80;
        $url      = "{$protocol}://{$router->ip_address}:{$port}/rest{$endpoint}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => "{$router->username}:{$router->password}",
            CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("REST API error: {$error}");
        }

        if ($httpCode !== 200) {
            throw new \Exception("REST API returned HTTP {$httpCode}");
        }

        return json_decode($response, true) ?? [];
    }

/**
 * Get full script source via REST API — no truncation
 */
public function getRouterScriptRest(Router $router, string $name): ?array
{
    $scripts = $this->restGet($router, '/system/script');

    $script = collect($scripts)->firstWhere('name', $name);

    if (!$script) {
        return null;
    }

    return [
        'name'    => $script['name'],
        'source'  => $script['source'] ?? '',
        'comment' => $script['comment'] ?? '',
    ];
}

/**
 * Get all scripts with full source via REST API
 */
public function getRouterScriptsRest(Router $router): array
{
    $scripts = $this->restGet($router, '/system/script');

    return array_map(fn($s) => [
        'name'    => $s['name'] ?? '',
        'source'  => $s['source'] ?? '',
        'comment' => $s['comment'] ?? '',
    ], $scripts);
}

/**
 * Get system resources via REST — CPU, memory, uptime etc
 */
public function getSystemResourcesRest(): array
{
    $result = $this->restGet($this->router, '/system/resource');
    return $result[0] ?? $result ?? [];
}

/**
 * Get all interfaces with full stats via REST
 */
public function getInterfacesRest(): array
{
    return $this->restGet($this->router, '/interface');
}

/**
 * Get interface traffic stats — rx/tx bytes and packets
 */
public function getInterfaceStats(): array
{
    return $this->restGet($this->router, '/interface/monitor-traffic?interface=all&once=');
}

/**
 * Get all IP addresses via REST
 */
public function getIpAddressesRest(): array
{
    return $this->restGet($this->router, '/ip/address');
}

/**
 * Get system identity via REST
 */
public function getIdentityRest(): string
{
    $result = $this->restGet($this->router, '/system/identity');
    return $result['name'] ?? $result[0]['name'] ?? 'Unknown';
}

/**
 * Get all monitoring data in one call
 */
public function getMonitoringData(): array
{
    return [
        'identity'  => $this->getIdentityRest(),
        'resources' => $this->getSystemResourcesRest(),
        'interfaces' => $this->getInterfacesRest(),
        'addresses' => $this->getIpAddressesRest(),
    ];
}

/**
 * Get firewall filter rules via REST
 */
public function getFirewallFilterRest(): array
{
    return $this->restGet($this->router, '/ip/firewall/filter');
}

/**
 * Get NAT rules via REST
 */
public function getFirewallNatRest(): array
{
    return $this->restGet($this->router, '/ip/firewall/nat');
}

/**
 * Get mangle rules via REST
 */
public function getFirewallMangleRest(): array
{
    return $this->restGet($this->router, '/ip/firewall/mangle');
}

/**
 * Enable a firewall rule by .id
 */
public function enableFirewallRule(string $chain, string $id): void
{
    $this->client->query(
        (new Query("/ip/firewall/{$chain}/enable"))
            ->equal('numbers', $id)
    )->read();
}

/**
 * Disable a firewall rule by .id
 */
public function disableFirewallRule(string $chain, string $id): void
{
    $this->client->query(
        (new Query("/ip/firewall/{$chain}/disable"))
            ->equal('numbers', $id)
    )->read();
}

/**
 * Add a firewall filter rule
 */
public function addFirewallRule(array $params): void
{
    $query = new Query('/ip/firewall/filter/add');
    foreach ($params as $key => $value) {
        $query->equal($key, $value);
    }
    $this->client->query($query)->read();
}

}