<?php

namespace App\Http\Controllers;

use App\Models\DicdsSystemMessage;
use Illuminate\Http\Request;

class DicdsWelcomeController extends Controller
{
    public function welcome()
    {
        try {
            $messages = DicdsSystemMessage::where('is_active', true)
                ->where(function ($query) {
                    $query->whereNull('start_date')
                        ->orWhere('start_date', '<=', now());
                })
                ->where(function ($query) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', now());
                })
                ->orderBy('message_type')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'messages' => $messages,
                'user' => auth()->user(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'messages' => [
                    [
                        'title' => 'Welcome to Florida DICDS',
                        'content' => 'Welcome to the Florida Driver Improvement Course Data System. Please click Continue to proceed.',
                        'message_type' => 'welcome',
                    ],
                ],
                'user' => auth()->user(),
            ]);
        }
    }

    public function continue(Request $request)
    {
        return response()->json([
            'redirect' => '/dicds/main-menu',
            'message' => 'Proceeding to main menu',
        ]);
    }
}
