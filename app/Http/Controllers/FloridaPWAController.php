<?php

namespace App\Http\Controllers;

class FloridaPWAController extends Controller
{
    public function manifest()
    {
        $manifest = [
            'name' => 'Florida Traffic School',
            'short_name' => 'FL Traffic School',
            'description' => 'Florida Online Traffic School Platform',
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
        ];

        return response()->json($manifest);
    }

    public function serviceWorker()
    {
        $serviceWorker = "
const CACHE_NAME = 'florida-traffic-school-v1';
const urlsToCache = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/courses',
    '/my-enrollments'
];

self.addEventListener('install', function(event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function(cache) {
                return cache.addAll(urlsToCache);
            })
    );
});

self.addEventListener('fetch', function(event) {
    event.respondWith(
        caches.match(event.request)
            .then(function(response) {
                if (response) {
                    return response;
                }
                return fetch(event.request);
            }
        )
    );
});
        ";

        return response($serviceWorker)
            ->header('Content-Type', 'application/javascript');
    }
}
