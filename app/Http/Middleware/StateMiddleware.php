<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StateMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $state): Response
    {
        // Set the current state in the request
        $request->attributes->set('current_state', $state);
        
        // Add state to session for persistence
        session(['current_state' => $state]);
        
        // Set state-specific configuration
        $this->setStateConfiguration($state);
        
        // Add state context to view data
        view()->share('currentState', $state);
        view()->share('stateConfig', $this->getStateConfig($state));
        
        return $next($request);
    }
    
    /**
     * Set state-specific configuration
     */
    private function setStateConfiguration(string $state): void
    {
        $configs = [
            'florida' => [
                'name' => 'Florida Traffic School',
                'abbreviation' => 'FL',
                'color' => '#2c5aa0',
                'compliance_authority' => 'FLHSMV',
                'required_hours' => 8,
                'passing_score' => 80,
                'certificate_fee' => 25.00,
                'features' => ['dicds_integration', 'state_submission', 'timer_enforcement']
            ],
            'missouri' => [
                'name' => 'Missouri Traffic School',
                'abbreviation' => 'MO',
                'color' => '#28a745',
                'compliance_authority' => 'Missouri DOR',
                'required_hours' => 8,
                'passing_score' => 70,
                'certificate_fee' => 20.00,
                'features' => ['form_4444', 'state_submission']
            ],
            'texas' => [
                'name' => 'Texas Traffic School',
                'abbreviation' => 'TX',
                'color' => '#ffc107',
                'compliance_authority' => 'Texas DPS',
                'required_hours' => 6,
                'passing_score' => 70,
                'certificate_fee' => 25.00,
                'features' => ['state_submission']
            ],
            'delaware' => [
                'name' => 'Delaware Traffic School',
                'abbreviation' => 'DE',
                'color' => '#17a2b8',
                'compliance_authority' => 'Delaware DMV',
                'required_hours' => 8,
                'passing_score' => 80,
                'certificate_fee' => 30.00,
                'features' => ['state_submission']
            ]
        ];
        
        config(['app.current_state' => $configs[$state] ?? null]);
    }
    
    /**
     * Get state configuration
     */
    private function getStateConfig(string $state): array
    {
        return config('app.current_state', []);
    }
}