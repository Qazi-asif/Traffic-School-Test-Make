<?php

namespace App\Http\Controllers;

use App\Models\TicketRecipient;
use Illuminate\Http\Request;

class TicketRecipientController extends Controller
{
    public function index()
    {
        $recipients = TicketRecipient::orderBy('created_at', 'desc')->paginate(20);

        return view('admin.support.recipients', compact('recipients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:ticket_recipients,email',
            'name' => 'required|string|max:255',
        ]);

        TicketRecipient::create($request->only(['email', 'name']));

        return redirect()->back()->with('success', 'Recipient added successfully');
    }

    public function destroy(TicketRecipient $recipient)
    {
        $recipient->delete();

        return redirect()->back()->with('success', 'Recipient deleted successfully');
    }

    public function toggle(TicketRecipient $recipient)
    {
        $recipient->update(['is_active' => ! $recipient->is_active]);

        return redirect()->back()->with('success', 'Recipient status updated');
    }
}
