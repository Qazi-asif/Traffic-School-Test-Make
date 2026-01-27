@extends('layouts.app')

@section('title', 'Florida DICDS - Welcome')

@section('content')
<div class="container-fluid" style="padding: 2rem;">
    <div class="row">
        <div class="col-12">
            <div class="dicds-welcome-screen">
                <div class="welcome-header text-center mb-4">
                    <h2>Florida Driver Improvement Course Data System (DICDS)</h2>
                    <h3>Welcome Screen</h3>
                </div>

                <div class="system-messages mb-4" id="systemMessages">
                    <div class="alert alert-info">
                        <h4>Welcome to Florida DICDS</h4>
                        <p>Welcome to the Florida Driver Improvement Course Data System. Please review any system messages and click Continue to proceed to the main menu.</p>
                    </div>
                </div>

                <div class="welcome-content text-center mb-4">
                    <p class="lead">Please click Continue to proceed to the main menu.</p>
                </div>

                <div class="welcome-actions text-center">
                    <button onclick="continueToMenu()" class="btn btn-primary btn-lg">
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadWelcomeData();
});

async function loadWelcomeData() {
    try {
        const response = await fetch('/api/dicds/welcome');
        const data = await response.json();
        
        if (data.messages && data.messages.length > 0) {
            const messagesHtml = data.messages.map(message => `
                <div class="alert alert-${getMessageClass(message.message_type)}">
                    <h4>${message.title}</h4>
                    <div>${message.content}</div>
                </div>
            `).join('');
            
            document.getElementById('systemMessages').innerHTML = messagesHtml;
        }
    } catch (error) {
        console.error('Error loading welcome data:', error);
    }
}

function getMessageClass(type) {
    const classes = {
        welcome: 'info',
        alert: 'danger',
        maintenance: 'warning',
        update: 'primary'
    };
    return classes[type] || 'info';
}

async function continueToMenu() {
    try {
        await fetch('/api/dicds/welcome/continue', { method: 'POST' });
        window.location.href = '/dicds/main-menu';
    } catch (error) {
        console.error('Error continuing:', error);
        window.location.href = '/dicds/main-menu';
    }
}
</script>

<style>
.dicds-welcome-screen {
    max-width: 800px;
    margin: 0 auto;
}

.welcome-header {
    padding-bottom: 1rem;
    border-bottom: 2px solid #007bff;
}

.btn-lg {
    padding: 1rem 3rem;
    font-size: 1.2rem;
    min-height: 44px;
}
</style>
@endsection
