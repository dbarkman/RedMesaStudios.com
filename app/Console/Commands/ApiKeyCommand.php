<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ApiClient;

class ApiKeyCommand extends Command
{
    protected $signature = 'api:key 
                            {--generate : Generate a new API key for a client}
                            {--list : List all API clients and their keys}
                            {--status : Update the status of an API client}
                            {--client= : The description of the client (for --generate)}
                            {--token= : The token prefix to identify the client (for --status)}
                            {--set= : The new status [active|blocked] (for --status)}';
    protected $description = 'Manage API keys for clients';

    public function handle()
    {
        if ($this->option('generate')) {
            $this->generateKey();
        } elseif ($this->option('list')) {
            $this->listKeys();
        } elseif ($this->option('status')) {
            $this->updateStatus();
        } else {
            $this->info('Please specify an action: --generate, --list, or --status');
        }
    }

    protected function generateKey()
    {
        $clientDescription = $this->option('client');
        if (!$clientDescription) {
            $this->error('Please provide a client description with --client="<description>"');
            return;
        }

        $client = ApiClient::create(['description' => $clientDescription]);
        $token = $client->createToken($clientDescription)->plainTextToken;

        $this->info("API Key generated for client: {$clientDescription}");
        $this->info("Token: {$token}");
    }

    protected function listKeys()
    {
        $clients = ApiClient::with('tokens')->get();
        $this->table(
            ['ID', 'Description', 'Status', 'Token Prefix', 'Created At'],
            $clients->map(function ($client) {
                $token = $client->tokens->first();
                return [
                    $client->id,
                    $client->description,
                    $client->status,
                    $token ? substr($token->token, 0, 8) . '...' : 'N/A',
                    $client->created_at->toDateTimeString(),
                ];
            })
        );
    }

    protected function updateStatus()
    {
        $tokenPrefix = $this->option('token');
        $status = $this->option('set');

        if (!$tokenPrefix || !$status) {
            $this->error('Please provide --token="<prefix>" and --set="<active|blocked>"');
            return;
        }

        if (!in_array($status, ['active', 'blocked'])) {
            $this->error('Invalid status. Please use "active" or "blocked".');
            return;
        }

        // Find the token by its prefix
        $token = \Laravel\Sanctum\PersonalAccessToken::where('token', 'like', $tokenPrefix . '%')->first();

        if (!$token) {
            $this->error("No token found with that prefix.");
            return;
        }
        
        $client = $token->tokenable;

        if ($client) {
            $client->status = $status;
            $client->save();
            $this->info("Client '{$client->description}' has been set to '{$status}'.");
        } else {
            $this->error("Could not find the associated API client for that token.");
        }
    }
}
