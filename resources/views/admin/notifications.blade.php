<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Push Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />

    <div class="container mt-4" style="margin-left: 300px; max-width: calc(100% - 320px);">
        <h2><i class="fas fa-bell"></i> Push Notification System</h2>

        <!-- Send Notification Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5>Send Real-Time Notification</h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Recipients</label>
                                <div class="mb-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipientType" id="recipientSingle" value="single" checked onchange="toggleRecipientType()">
                                        <label class="form-check-label" for="recipientSingle">
                                            Single User
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipientType" id="recipientMultiple" value="multiple" onchange="toggleRecipientType()">
                                        <label class="form-check-label" for="recipientMultiple">
                                            Multiple Users
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipientType" id="recipientAll" value="all" onchange="toggleRecipientType()">
                                        <label class="form-check-label" for="recipientAll">
                                            All Users
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="singleUserSelect">
                                    <select class="form-control" id="userEmail">
                                        <option value="">Select User</option>
                                    </select>
                                </div>
                                
                                <div id="multipleUserSelect" style="display: none;">
                                    <div class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()">
                                            <label class="form-check-label fw-bold" for="selectAllUsers">
                                                Select All
                                            </label>
                                        </div>
                                        <hr class="my-2">
                                        <div id="userCheckboxList"></div>
                                    </div>
                                    <small class="text-muted">Selected: <span id="selectedCount">0</span> user(s)</small>
                                </div>
                                
                                <div id="allUsersMessage" style="display: none;">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-users"></i> Notification will be sent to <strong>all users</strong> in the system.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Notification Type</label>
                                <select class="form-control" id="notificationType">
                                    <option value="info">Info</option>
                                    <option value="success">Success</option>
                                    <option value="warning">Warning</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" id="notifTitle" placeholder="Notification title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Message</label>
                        <textarea class="form-control" id="notifMessage" rows="3" placeholder="Notification message" required></textarea>
                    </div>
                    
                    <button type="button" class="btn btn-primary" onclick="sendPushNotification()">
                        <i class="fas fa-paper-plane"></i> Send Push Notification
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification History -->
        <div class="card">
            <div class="card-header">
                <h5>Notification History</h5>
            </div>
            <div class="card-body">
                <div id="notificationHistory">
                    <p>No notifications sent yet.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allUsers = [];

        function toggleRecipientType() {
            const type = document.querySelector('input[name="recipientType"]:checked').value;
            
            document.getElementById('singleUserSelect').style.display = type === 'single' ? 'block' : 'none';
            document.getElementById('multipleUserSelect').style.display = type === 'multiple' ? 'block' : 'none';
            document.getElementById('allUsersMessage').style.display = type === 'all' ? 'block' : 'none';
        }

        function toggleAllUsers() {
            const selectAll = document.getElementById('selectAllUsers').checked;
            const checkboxes = document.querySelectorAll('.user-checkbox');
            
            checkboxes.forEach(cb => {
                cb.checked = selectAll;
            });
            
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.user-checkbox:checked').length;
            document.getElementById('selectedCount').textContent = checked;
        }

        async function loadUsers() {
            try {
                const response = await fetch('/web/users', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });
                
                if (response.ok) {
                    const data = await response.json();
                    allUsers = data.data || data;
                    const userSelect = document.getElementById('userEmail');
                    const checkboxList = document.getElementById('userCheckboxList');
                    
                    // Populate single select dropdown
                    userSelect.innerHTML = '<option value="">Select User</option>';
                    
                    // Populate checkbox list
                    checkboxList.innerHTML = '';
                    
                    allUsers.forEach((user, index) => {
                        // Single select option
                        const option = document.createElement('option');
                        option.value = user.email;
                        option.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
                        userSelect.appendChild(option);
                        
                        // Checkbox option
                        const checkboxDiv = document.createElement('div');
                        checkboxDiv.className = 'form-check';
                        checkboxDiv.innerHTML = `
                            <input class="form-check-input user-checkbox" type="checkbox" value="${user.email}" id="user${index}" onchange="updateSelectedCount()">
                            <label class="form-check-label" for="user${index}">
                                ${user.first_name} ${user.last_name} (${user.email})
                            </label>
                        `;
                        checkboxList.appendChild(checkboxDiv);
                    });
                }
            } catch (error) {
                console.error('Error loading users:', error);
            }
        }

        async function sendPushNotification() {
            const recipientType = document.querySelector('input[name="recipientType"]:checked').value;
            const typeEl = document.getElementById('notificationType');
            const titleEl = document.getElementById('notifTitle');
            const messageEl = document.getElementById('notifMessage');

            if (!typeEl || !titleEl || !messageEl) {
                alert('Form elements not found');
                return;
            }

            const type = typeEl.value ? typeEl.value.trim() : '';
            const title = titleEl.value ? titleEl.value.trim() : '';
            const message = messageEl.value ? messageEl.value.trim() : '';

            if (!title) {
                alert('Please enter notification title');
                return;
            }
            if (!message) {
                alert('Please enter notification message');
                return;
            }

            let emails = [];

            // Get recipient emails based on type
            if (recipientType === 'single') {
                const email = document.getElementById('userEmail').value.trim();
                if (!email) {
                    alert('Please select a user');
                    return;
                }
                emails = [email];
            } else if (recipientType === 'multiple') {
                const checkboxes = document.querySelectorAll('.user-checkbox:checked');
                if (checkboxes.length === 0) {
                    alert('Please select at least one user');
                    return;
                }
                emails = Array.from(checkboxes).map(cb => cb.value);
            } else if (recipientType === 'all') {
                emails = allUsers.map(user => user.email);
                if (emails.length === 0) {
                    alert('No users found in the system');
                    return;
                }
            }

            console.log('Sending notification to:', emails);

            // Show progress
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

            let successCount = 0;
            let failCount = 0;

            try {
                // Send notifications to all selected users
                for (const email of emails) {
                    try {
                        const response = await fetch('/api/push-notification', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                email: email,
                                type: type,
                                title: title,
                                message: message
                            })
                        });

                        if (response.ok) {
                            successCount++;
                        } else {
                            failCount++;
                        }
                    } catch (error) {
                        console.error('Error sending to', email, error);
                        failCount++;
                    }
                }

                // Show result
                if (failCount === 0) {
                    alert(`✓ Notification sent successfully to ${successCount} user(s)!`);
                    document.getElementById('notificationForm').reset();
                    toggleRecipientType();
                    addToHistory(emails.join(', '), type, title, message, successCount);
                } else {
                    alert(`Sent to ${successCount} user(s), failed for ${failCount} user(s)`);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error sending notifications: ' + error.message);
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function addToHistory(recipients, type, title, message, count) {
            const history = document.getElementById('notificationHistory');
            const now = new Date().toLocaleString();
            
            const recipientDisplay = count > 3 
                ? `${count} users` 
                : recipients;
            
            const notificationHtml = `
                <div class="alert alert-${type} mb-2">
                    <div class="d-flex justify-content-between">
                        <div style="flex: 1;">
                            <strong>${title}</strong><br>
                            <small><i class="fas fa-users"></i> To: ${recipientDisplay}</small><br>
                            <span>${message}</span>
                        </div>
                        <small class="text-muted text-nowrap ms-3">${now}</small>
                    </div>
                </div>
            `;
            
            if (history.innerHTML.includes('No notifications sent yet')) {
                history.innerHTML = notificationHtml;
            } else {
                history.insertAdjacentHTML('afterbegin', notificationHtml);
            }
        }
        
        // Load users when page loads
        document.addEventListener('DOMContentLoaded', loadUsers);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <x-footer />
</body>
</html>
