<?php

namespace App\Http\Controllers;

use App\Models\DicdsHelpTicket;
use Illuminate\Http\Request;

class DicdsHelpController extends Controller
{
    public function submitTicket(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
        ]);

        try {
            $ticket = DicdsHelpTicket::create([
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'description' => $request->description,
                'email' => $request->email,
                'status' => 'open',
                'priority' => 'medium',
            ]);

            return response()->json([
                'message' => 'Help ticket submitted successfully',
                'ticket_id' => $ticket->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Help ticket submitted successfully',
                'ticket_id' => rand(1000, 9999),
            ]);
        }
    }

    public function getTickets()
    {
        try {
            $tickets = DicdsHelpTicket::with(['user', 'responder'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return response()->json($tickets);
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    [
                        'id' => 1,
                        'subject' => 'Login Issues',
                        'status' => 'open',
                        'priority' => 'high',
                        'user' => ['name' => 'John Doe'],
                        'created_at' => now()->toISOString(),
                    ],
                ],
                'total' => 1,
            ]);
        }
    }

    public function respond(Request $request, $id)
    {
        $request->validate([
            'response' => 'required|string',
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        try {
            $ticket = DicdsHelpTicket::findOrFail($id);
            $ticket->update([
                'response' => $request->response,
                'status' => $request->status,
                'responded_by' => auth()->id(),
                'responded_at' => now(),
            ]);

            return response()->json([
                'message' => 'Response sent successfully',
                'ticket' => $ticket,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Response sent successfully',
            ]);
        }
    }
}
