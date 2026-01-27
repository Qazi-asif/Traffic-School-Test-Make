<?php

namespace App\Http\Controllers;

use App\Mail\TicketMail;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\TicketRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        try {
            \Log::info('=== Support Tickets Index START ===');
            \Log::info('User authenticated: '.(auth()->check() ? 'yes' : 'no'));
            \Log::info('User ID: '.(auth()->id() ?? 'null'));

            $query = SupportTicket::with(['user']);

            if (auth()->check() && auth()->user()->role_id != 1) {
                \Log::info('Filtering tickets for user: '.auth()->id());
                $query->where('user_id', auth()->id());
            } else {
                \Log::info('Loading all tickets (admin or not authenticated)');
            }

            $tickets = $query->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            })
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            \Log::info('Tickets loaded: '.$tickets->count());
            \Log::info('=== Support Tickets Index END ===');

            // For web view
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return view('admin.support.tickets');
            }

            return response()->json($tickets);
        } catch (\Exception $e) {
            \Log::error('=== Support Tickets Index ERROR ===');
            \Log::error('Error: '.$e->getMessage());
            \Log::error('File: '.$e->getFile().':'.$e->getLine());
            \Log::error('Stack: '.$e->getTraceAsString());

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            \Log::info('=== Support Ticket Store START ===');
            \Log::info('Request data: ', $request->all());

            $request->validate([
                'subject' => 'required|string|max:255',
                'category' => 'nullable|string',
                'priority' => 'nullable|in:low,medium,high,critical',
                'description' => 'required|string',
            ]);

            \Log::info('Validation passed');

            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'description' => $request->description,
                'email' => auth()->user()->email,
                'priority' => $request->priority ?? 'medium',
                'status' => 'open',
            ]);

            \Log::info('Ticket created: '.$ticket->id);

            // Send email to all active recipients
            try {
                $recipients = TicketRecipient::where('is_active', true)->get();
                \Log::info('Found '.$recipients->count().' active recipients');

                foreach ($recipients as $recipient) {
                    try {
                        Mail::to($recipient->email)->send(new TicketMail($ticket));
                        \Log::info('Email sent to: '.$recipient->email);
                    } catch (\Exception $e) {
                        \Log::error('Failed to send email to '.$recipient->email.': '.$e->getMessage());
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error sending emails: '.$e->getMessage());
            }

            \Log::info('=== Support Ticket Store END ===');

            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Exception $e) {
            \Log::error('=== Support Ticket Store ERROR ===');
            \Log::error('Error: '.$e->getMessage());
            \Log::error('File: '.$e->getFile().':'.$e->getLine());
            \Log::error('Stack: '.$e->getTraceAsString());

            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['user'])->findOrFail($id);

        if (auth()->user()->role_id != 1 && $ticket->user_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($ticket);
    }

    public function reply(Request $request, $id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);

            $request->validate([
                'message' => 'required|string',
            ]);

            // Check if user is admin/super-admin by checking role slug
            $isStaffReply = auth()->user()->role && in_array(auth()->user()->role->slug, ['super-admin', 'admin']);

            $reply = SupportTicketReply::create([
                'support_ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'is_staff_reply' => $isStaffReply,
            ]);

            $ticket->update(['status' => 'replied']);

            return response()->json(['success' => true, 'reply' => $reply]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Reply error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);

            $request->validate([
                'status' => 'required|in:open,replied,resolved,closed',
            ]);

            $ticket->update([
                'status' => $request->status,
                'resolved_at' => $request->status === 'resolved' ? now() : null,
            ]);

            return response()->json(['success' => true, 'ticket' => $ticket]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Update status error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function getReplies($id)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);

            if (auth()->user()->role && !in_array(auth()->user()->role->slug, ['super-admin', 'admin']) && $ticket->user_id != auth()->id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $replies = SupportTicketReply::where('support_ticket_id', $id)
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($replies);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Ticket not found'], 404);
        } catch (\Exception $e) {
            \Log::error('Get replies error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
