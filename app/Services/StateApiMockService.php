<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class StateApiMockService
{
    /**
     * Generate a mock response for a given state.
     */
    public static function getMockResponse(string $state, string $type = 'success'): array
    {
        $mockConfig = config("states.mocks.$state");
        
        if (!$mockConfig) {
            return self::getGenericMockResponse($state, $type);
        }

        $response = $mockConfig["{$type}_response"] ?? $mockConfig['success_response'];
        
        // Replace placeholders
        $response = self::replacePlaceholders($response);
        
        Log::info("Generated mock response for $state", [
            'state' => $state,
            'type' => $type,
            'response' => $response,
        ]);

        return [
            'success' => $type === 'success',
            'mock' => true,
            'response' => $response,
            'message' => "Mock response for $state ($type)",
        ];
    }

    /**
     * Generate a generic mock response when no specific config exists.
     */
    protected static function getGenericMockResponse(string $state, string $type): array
    {
        if ($type === 'success') {
            return [
                'success' => true,
                'mock' => true,
                'certificate_number' => strtoupper($state) . date('Y') . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT),
                'response_code' => 'MOCK_SUCCESS',
                'message' => "Mock successful submission to $state",
            ];
        } else {
            return [
                'success' => false,
                'mock' => true,
                'error_code' => 'MOCK_ERROR',
                'error_message' => "Mock error for $state testing",
            ];
        }
    }

    /**
     * Replace placeholders in mock responses.
     */
    protected static function replacePlaceholders($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'replacePlaceholders'], $data);
        }

        if (is_string($data)) {
            // Replace common placeholders
            $data = str_replace('{{RANDOM_6_DIGITS}}', str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT), $data);
            $data = str_replace('{{TIMESTAMP}}', now()->toISOString(), $data);
            $data = str_replace('{{DATE}}', now()->format('Y-m-d'), $data);
        }

        return $data;
    }

    /**
     * Simulate network delay for testing.
     */
    public static function simulateNetworkDelay(): void
    {
        if (config('states.development.simulate_network_delays')) {
            $delay = rand(1, 3); // 1-3 seconds
            sleep($delay);
            Log::debug("Simulated network delay: {$delay}s");
        }
    }

    /**
     * Check if mock mode is enabled for a state.
     */
    public static function isMockMode(string $state, string $service = null): bool
    {
        $configKey = $service ? "states.$state.$service.mode" : "states.$state.mode";
        $mode = config($configKey, 'live');
        
        return $mode === 'mock' || config('states.development.force_fallback_mode', false);
    }

    /**
     * Check if fallback mode is enabled for a state.
     */
    public static function isFallbackMode(string $state, string $service = null): bool
    {
        $configKey = $service ? "states.$state.$service.fallback.enabled" : "states.$state.fallback.enabled";
        return config($configKey, true);
    }
}