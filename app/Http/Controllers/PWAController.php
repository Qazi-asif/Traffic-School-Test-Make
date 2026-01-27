<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PWAController extends Controller
{
    public function manifest(): JsonResponse
    {
        return response()->json([
            'name' => 'Traffic School Platform',
            'short_name' => 'TrafficSchool',
            'description' => 'Online Traffic School Learning Platform',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#007bff',
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => '/images/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
                [
                    'src' => '/images/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                ],
            ],
        ]);
    }

    public function serviceWorker(): Response
    {
        $serviceWorker = "
const CACHE_NAME = 'traffic-school-v1';
const urlsToCache = [
    '/',
    '/dashboard',
    '/courses',
    '/css/app.css',
    '/js/app.js',
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => {
                if (response) {
                    return response;
                }
                return fetch(event.request);
            })
    );
});
        ";

        return response($serviceWorker)
            ->header('Content-Type', 'application/javascript');
    }
}
