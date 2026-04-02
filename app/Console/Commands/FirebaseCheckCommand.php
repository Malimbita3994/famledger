<?php

namespace App\Console\Commands;

use App\Services\FirebaseClient;
use Illuminate\Console\Command;
use Throwable;

class FirebaseCheckCommand extends Command
{
    protected $signature = 'firebase:check';

    protected $description = 'Verify Firebase credentials and a live Auth API connection.';

    public function handle(FirebaseClient $firebase): int
    {
        $pathRaw = config('firebase.credentials.path');
        $jsonRaw = config('firebase.credentials.json');
        $projectId = config('firebase.project_id');

        $resolvedPath = $this->resolveCredentialsPath($pathRaw);
        $jsonDecoded = $this->decodeCredentialsJson($jsonRaw);

        if ($resolvedPath === null && $jsonDecoded === null) {
            $this->error('No Firebase credentials configured.');
            $this->line('Set FIREBASE_CREDENTIALS (path to service account JSON) or FIREBASE_CREDENTIALS_JSON in `.env`.');

            return self::FAILURE;
        }

        if (is_string($pathRaw) && $pathRaw !== '' && $resolvedPath === null) {
            $this->error('FIREBASE_CREDENTIALS file not found: '.$pathRaw);

            return self::FAILURE;
        }

        if (is_string($jsonRaw) && $jsonRaw !== '' && $jsonDecoded === null) {
            $this->error('FIREBASE_CREDENTIALS_JSON is not valid JSON: '.json_last_error_msg());

            return self::FAILURE;
        }

        $projectFromCreds = null;
        if (is_array($jsonDecoded) && isset($jsonDecoded['project_id'])) {
            $projectFromCreds = (string) $jsonDecoded['project_id'];
        } elseif ($resolvedPath !== null) {
            $contents = @file_get_contents($resolvedPath);
            if ($contents !== false) {
                $data = json_decode($contents, true);
                if (is_array($data) && isset($data['project_id'])) {
                    $projectFromCreds = (string) $data['project_id'];
                }
            }
        }

        $this->table(
            ['Setting', 'Value'],
            [
                ['Credential source', $resolvedPath !== null ? 'FIREBASE_CREDENTIALS file' : 'FIREBASE_CREDENTIALS_JSON'],
                ['Credentials path', $resolvedPath ?? '—'],
                ['FIREBASE_PROJECT_ID (config)', is_string($projectId) && $projectId !== '' ? $projectId : '—'],
                ['project_id (service account)', $projectFromCreds ?? '—'],
            ]
        );

        if (is_string($projectId) && $projectId !== '' && $projectFromCreds !== null && $projectId !== $projectFromCreds) {
            $this->warn('FIREBASE_PROJECT_ID does not match project_id in the service account JSON. The JSON value usually wins; align them to avoid confusion.');
        }

        try {
            $auth = $firebase->auth();
            foreach ($auth->listUsers(1) as $_) {
                break;
            }
        } catch (Throwable $e) {
            $this->error('Firebase Auth request failed: '.$e->getMessage());
            if ($this->output->isVerbose()) {
                $this->line($e->getTraceAsString());
            }

            return self::FAILURE;
        }

        $this->info('Firebase connection OK (Auth API reachable).');

        return self::SUCCESS;
    }

    private function resolveCredentialsPath(mixed $pathRaw): ?string
    {
        if (! is_string($pathRaw) || $pathRaw === '') {
            return null;
        }
        if (is_file($pathRaw)) {
            return $pathRaw;
        }
        $candidate = base_path($pathRaw);
        if (is_file($candidate)) {
            return $candidate;
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeCredentialsJson(mixed $jsonRaw): ?array
    {
        if (! is_string($jsonRaw) || $jsonRaw === '') {
            return null;
        }
        $decoded = json_decode($jsonRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
