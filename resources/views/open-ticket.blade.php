@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2><i class="fas fa-ticket-alt me-2"></i>Support Tickets</h2>
    
    <div class="row mt-4">
        <!-- Create Ticket Form -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Open a New Support Ticket</h5>
                </div>
                <div class="card-body">
                    <form id="ticketForm">
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" id="category" required>
                                <option value="">Select Category</option>
                                <option value="technical">Technical Issue</option>
                                <option value="billing">Billing Question</option>
                                <option value="course">Course Content</option>
                                <option value="certificate">Certificate Issue</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select" id="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane me-2"></i>Submit Ticket
                        </button>
                    </form>
                </div>
            </div>

            <!-- Ticket Detail View -->
            <div id="ticketDetail" style="display: none;">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 id="ticketSubject"></h5>
                        <button class="btn btn-sm btn-secondary" onclick="backToList()">Back to List</button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Status:</strong> <span id="ticketStatus" class="badge"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Priority:</strong> <span id="ticketPriority" class="badge"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Created:</strong> <span id="ticketCreated"></span>
                        </div>
                        <div class="mb-3">
                            <strong>Description:</strong>
                            <p id="ticketDescription"></p>
                        </div>
                    </div>
                </div>

                <!-- Replies Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Conversation</h5>
                    </div>
                    <div class="card-body" id="repliesContainer" style="max-height: 400px; overflow-y: auto;">
                        <p class="text-muted">No replies yet</p>
                    </div>
                </div>

                <!-- Reply Form -->
                <div class="card">
                    <div class="card-header">
                        <h5>Add Reply</h5>
                    </div>
                    <div class="card-body">
                        <form id="replyForm">
                            <div class="mb-3">
                                <textarea class="form-control" id="replyMessage" rows="4" placeholder="Type your reply..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply me-2"></i>Send Reply
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- My Tickets List -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>My Tickets</h5>
                </div>
                <div class="card-body" id="myTickets" style="max-height: 600px; overflow-y: auto;">
                    <p class="text-muted">Loading...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentTicketId = null;

document.getElementById('ticketForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const data = {
        subject: document.getElementById('subject').value,
        category: document.getElementById('category').value,
        priority: document.getElementById('priority').value,
        description: document.getElementById('description').value
    };
    
    try {
        const response = await fetch('/api/support/tickets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('Ticket submitted successfully!');
            document.getElementById('ticketForm').reset();
            loadMyTickets();
        } else {
            alert('Error: ' + (result.message || result.error || 'Failed to submit ticket'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to submit ticket');
    }
});

document.getElementById('replyForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const message = document.getElementById('replyMessage').value;
    
    try {
        const response = await fetch(`/api/support/tickets/${currentTicketId}/reply`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'same-origin',
            body: JSON.stringify({ message })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            document.getElementById('replyMessage').value = '';
            loadTicketDetail(currentTicketId);
        } else {
            alert('Error: ' + (result.message || result.error || 'Failed to send reply'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to send reply');
    }
});

async function loadMyTickets() {
    try {
        const response = await fetch('/api/support/tickets', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        
        const result = await response.json();
        const container = document.getElementById('myTickets');
        
        const tickets = result.data || result;
        
        if (!Array.isArray(tickets) || tickets.length === 0) {
            container.innerHTML = '<p class="text-muted">No tickets yet</p>';
            return;
        }
        
        container.innerHTML = tickets.map(ticket => `
            <div class="mb-2 p-2 border-bottom" style="cursor: pointer;" onclick="loadTicketDetail(${ticket.id})">
                <strong>${ticket.subject}</strong>
                <br>
                <small class="text-muted">${ticket.status}</small>
                <br>
                <small class="text-muted">${new Date(ticket.created_at).toLocaleDateString()}</small>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading tickets:', error);
        document.getElementById('myTickets').innerHTML = '<p class="text-danger">Error loading tickets</p>';
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
        
        // Hide form, show detail
        document.getElementById('ticketForm').parentElement.parentElement.style.display = 'none';
        document.getElementById('ticketDetail').style.display = 'block';
        
        // Populate ticket details
        document.getElementById('ticketSubject').textContent = ticket.subject;
        document.getElementById('ticketStatus').textContent = ticket.status;
        document.getElementById('ticketStatus').className = `badge bg-${getStatusColor(ticket.status)}`;
        document.getElementById('ticketPriority').textContent = ticket.priority;
        document.getElementById('ticketPriority').className = `badge bg-${getPriorityColor(ticket.priority)}`;
        document.getElementById('ticketCreated').textContent = new Date(ticket.created_at).toLocaleString();
        document.getElementById('ticketDescription').textContent = ticket.description;
        
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
        const container = document.getElementById('repliesContainer');
        
        if (!Array.isArray(replies) || replies.length === 0) {
            container.innerHTML = '<p class="text-muted">No replies yet</p>';
            return;
        }
        
        container.innerHTML = replies.map(reply => `
            <div class="mb-3 p-2 border rounded" style="background: var(--bg-secondary); color: var(--text-primary); border-color: var(--border);">
                <strong>${reply.is_staff_reply ? '👨‍💼 Support Team' : 'You'}</strong>
                <br>
                <small style="color: var(--text-secondary);">${new Date(reply.created_at).toLocaleString()}</small>
                <p class="mt-2 mb-0">${reply.message}</p>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading replies:', error);
    }
}

function backToList() {
    currentTicketId = null;
    document.getElementById('ticketForm').parentElement.parentElement.style.display = 'block';
    document.getElementById('ticketDetail').style.display = 'none';
    loadMyTickets();
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

loadMyTickets();
</script>
@endsection
