<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DicdsNavigationController extends Controller
{
    public function mainMenu()
    {
        $userRole = auth()->user()->role->slug ?? 'user';

        $menu = [
            'schools' => [
                'title' => 'Schools',
                'items' => [
                    ['id' => 'new_school', 'title' => 'New School', 'description' => 'Add new contracted school'],
                    ['id' => 'maintain_school', 'title' => 'Maintain School', 'description' => 'Edit existing schools'],
                    ['id' => 'add_instructor', 'title' => 'Add Instructor', 'description' => 'Add approved instructors'],
                    ['id' => 'update_instructor', 'title' => 'Update Instructor', 'description' => 'Edit existing instructors'],
                ],
            ],
            'certificates' => [
                'title' => 'Certificates',
                'items' => [
                    ['id' => 'order_certificates', 'title' => 'Order Certificates', 'description' => 'Order from Florida DHSMV'],
                    ['id' => 'distribute_certificates', 'title' => 'Distribute Certificates', 'description' => 'Distribute to schools'],
                    ['id' => 'reclaim_certificates', 'title' => 'Reclaim Certificates', 'description' => 'Reclaim from schools'],
                    ['id' => 'maintain_certificates', 'title' => 'Maintain Certificates', 'description' => 'View order status'],
                ],
            ],
            'inquiry' => [
                'title' => 'Inquiry Menu',
                'items' => [
                    ['id' => 'web_service_info', 'title' => 'Web Service Info', 'description' => 'School and instructor reference'],
                    ['id' => 'school_certificates', 'title' => 'School\'s Certificates', 'description' => 'Certificate counts by school'],
                    ['id' => 'reports', 'title' => 'Reports', 'description' => 'Certificate lookup, school activity reports'],
                ],
            ],
        ];

        return response()->json([
            'menu' => $menu,
            'user_role' => $userRole,
        ]);
    }

    public function navigate(Request $request, $action)
    {
        return response()->json([
            'action' => $action,
            'redirect' => "/dicds/{$action}",
            'message' => "Navigating to {$action}",
        ]);
    }
}
