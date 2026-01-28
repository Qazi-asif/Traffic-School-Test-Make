<?php

namespace App\Helpers;

class StateHelper
{
    /**
     * Get the current state from request or session
     */
    public static function getCurrentState(): ?string
    {
        return request()->attributes->get('current_state') 
            ?? session('current_state') 
            ?? null;
    }
    
    /**
     * Get state configuration
     */
    public static function getStateConfig(?string $state = null): array
    {
        $state = $state ?? self::getCurrentState();
        
        if (!$state) {
            return [];
        }
        
        return config('app.current_state', []);
    }
    
    /**
     * Get state-specific model class
     */
    public static function getStateModel(string $baseModel, ?string $state = null): string
    {
        $state = $state ?? self::getCurrentState();
        
        if (!$state) {
            return $baseModel;
        }
        
        $stateClass = "App\\Models\\" . ucfirst($state) . "\\" . $baseModel;
        
        return class_exists($stateClass) ? $stateClass : $baseModel;
    }
    
    /**
     * Get state-specific table name
     */
    public static function getStateTable(string $baseTable, ?string $state = null): string
    {
        $state = $state ?? self::getCurrentState();
        
        if (!$state) {
            return $baseTable;
        }
        
        return $state . '_' . $baseTable;
    }
    
    /**
     * Check if state has specific feature
     */
    public static function hasFeature(string $feature, ?string $state = null): bool
    {
        $config = self::getStateConfig($state);
        
        return in_array($feature, $config['features'] ?? []);
    }
    
    /**
     * Get state branding colors
     */
    public static function getStateColors(?string $state = null): array
    {
        $colors = [
            'florida' => ['primary' => '#2c5aa0', 'secondary' => '#1e4080'],
            'missouri' => ['primary' => '#28a745', 'secondary' => '#1e7e34'],
            'texas' => ['primary' => '#ffc107', 'secondary' => '#e0a800'],
            'delaware' => ['primary' => '#17a2b8', 'secondary' => '#138496']
        ];
        
        $state = $state ?? self::getCurrentState();
        
        return $colors[$state] ?? ['primary' => '#6c757d', 'secondary' => '#495057'];
    }
}