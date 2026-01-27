@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-ticket-alt me-2"></i>Support Tickets</h2>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('ticket-recipients.index') }}" class="btn btn-secondary">
                <i class="fas fa-envelope"></i> Manage Recipients
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Tickets List -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>All Tickets</h5>
                </div>
                <div class="card-body" id="ticketsList" style="max-height: 600px; overflow-y: auto;">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>

        <!-- Ticket Detail -->
        <div class="col-md-8">
            <div id="ticketDetail" style="display: none;">
                <!-- Ticket Info Card -->
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 id="detailSubject"></h5>
                        <button class="btn btn-sm btn-secondary" onclick="backToList()">Back</button>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>From:</strong> <span id="detailEmail"></span><br>
                                <strong>User:</strong> <span id="detailUser"></span><br>
                                <strong>Created:</strong> <span id="detailCreated"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> <span id="detailStatus" class="badge"></span><br>
                                <strong>Priority:</strong> <span id="detailPriority" class="badge"></span><br>
                                <strong>Category:</strong> <span id="detailCategory"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p id="detailDescription" style="background: var(--bg-secondary); padding: 10px; border-radius: 5px;"></p>
                        </div>
                    </div>
                </div>

                <!-- Conversation -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Conversation</h5>
                    </div>
                    <div class="card-body" id="detailReplies" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-muted">No replies yet</p>
                    </div>
                </div>

                <!-- Reply Form -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Add Reply</h5>
                    </div>
                    <div class="card-body">
                        <form id="adminReplyForm">
                            <div class="mb-3">
                                <textarea class="form-control" id="adminReplyMessage" rows="4" placeholder="Type your reply..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply me-2"></i>Send Reply
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5>Actions</h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-warning" onclick="updateTicketStatus('replied')">
                            <i class="fas fa-comment me-2"></i>Mark as Replied
                        </button>
                        <button class="btn btn-success" onclick="updateTicketStatus('resolved')">
                            <i class="fas fa-check me-2"></i>Mark as Resolved
                        </button>
                        <button class="btn btn-danger" onclick="updateTicketStatus('closed')">
                            <i class="fas fa-times me-2"></i>Close Ticket
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div id="emptyState" class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>Select a ticket to view details</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTicketId = null;

async function loadTickets() {
    try {
        const response = await fetch('/api/support/tickets', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        const tickets = result.data || result;
        const container = document.getElementById('ticketsList');
        
        if (!Array.isArray(tickets) || tickets.length === 0) {
            container.innerHTML = '<p class="text-muted">No tickets</p>';
            return;
        }
        
        container.innerHTML = tickets.map(ticket => `
            <div class="mb-2 p-2 border-bottom" style="cursor: pointer; background: var(--bg-secondary); border-radius: 5px; margin-bottom: 5px;" onclick="loadTicketDetail(${ticket.id})">
                <strong>#${ticket.id} - ${ticket.subject}</strong>
                <br>
                <small class="text-muted">${ticket.email}</small>
                <br>
                <small>
                    <span class="badge bg-${getStatusColor(ticket.status)}">${ticket.status}</span>
                    <span class="badge bg-${getPriorityColor(ticket.priority)}">${ticket.priority}</span>
                </small>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('ticketsList').innerHTML = '<p class="text-danger">Error loading tickets</p>';
    }
}

async function loadTicketDetail(ticketId) {
    try {
        const response = await fetch(`/api/support/tickets/${ticketId}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const ticket = await response.json();
        
        if (!response.ok) {
            alert('Error loading ticket');
            return;
        }
        
        currentTicketId = ticketId;
        
        // Show detail, hide empty state
        document.getElementById('ticketDetail').style.display = 'block';
        document.getElementById('emptyState').style.display = 'none';
        
        // Populate details
        document.getElementById('detailSubject').textContent = ticket.subject;
        document.getElementById('detailEmail').textContent = ticket.email;
        document.getElementById('detailUser').textContent = ticket.user?.name || 'Unknown';
        document.getElementById('detailCreated').textContent = new Date(ticket.created_at).toLocaleString();
        document.getElementById('detailStatus').textContent = ticket.status;
        document.getElementById('detailStatus').className = `badge bg-${getStatusColor(ticket.status)}`;
        document.getElementById('detailPriority').textContent = ticket.priority;
        document.getElementById('detailPriority').className = `badge bg-${getPriorityColor(ticket.priority)}`;
        document.getElementById('detailCategory').textContent = ticket.category || 'N/A';
        document.getElementById('detailDescription').textContent = ticket.description;
        
        // Load replies
        loadReplies(ticketId);
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load ticket');
    }
}

async function loadReplies(ticketId) {
    try {
        const response = await fetch(`/api/support/tickets/${ticketId}/replies`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const replies = await response.json();
        const container = document.getElementById('detailReplies');
        
        if (!Array.isArray(replies) || replies.length === 0) {
            container.innerHTML = '<p class="text-muted">No replies yet</p>';
            return;
        }
        
        container.innerHTML = replies.map(reply => `
            <div class="mb-3 p-2 border rounded" style="background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border);">
                <strong>${reply.is_staff_reply ? '👨‍💼 Admin' : '👤 ' + (reply.user?.name || 'User')}</strong>
                <br>
                <small style="color: var(--text-secondary);">${new Date(reply.created_at).toLocaleString()}</small>
                <p class="mt-2 mb-0">${reply.message}</p>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading replies:', error);
    }
}

document.getElementById('adminReplyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const message = document.getElementById('adminReplyMessage').value;
    
    try {
        const response = await fetch(`/api/support/tickets/${currentTicketId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ message })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            document.getElementById('adminReplyMessage').value = '';
            loadTicketDetail(currentTicketId);
        } else {
            alert('Error: ' + (result.error || 'Failed to send reply'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to send reply');
    }
});

async function updateTicketStatus(status) {
    if (!currentTicketId) {
        alert('Please select a ticket first');
        return;
    }
    
    try {
        const response = await fetch(`/api/support/tickets/${currentTicketId}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Ticket status updated to: ' + status);
            loadTickets();
            loadTicketDetail(currentTicketId);
        } else {
            alert('Error: ' + (result.error || 'Failed to update status'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to update status: ' + error.message);
    }
}

function backToList() {
    currentTicketId = null;
    document.getElementById('ticketDetail').style.display = 'none';
    document.getElementById('emptyState').style.display = 'block';
    loadTickets();
}

function getStatusColor(status) {
    const colors = {
        'open': 'primary',
        'replied': 'info',
        'resolved': 'success',
        'closed': 'secondary'
    };
    return colors[status] || 'secondary';
}

function getPriorityColor(priority) {
    const colors = {
        'low': 'success',
        'medium': 'warning',
        'high': 'danger',
        'critical': 'danger'
    };
    return colors[priority] || 'secondary';
}

loadTickets();
</script>
@endsection
