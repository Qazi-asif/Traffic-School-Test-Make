<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/css/themes.css" rel="stylesheet">
</head>
<body>
    <x-theme-switcher />
    <x-navbar />
    
    <div class="container-fluid mt-4" style="margin-left: 280px; max-width: calc(100% - 300px);">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                        </div>

                        <h1 class="mb-4">Thank You!</h1>
                        <p class="lead mb-4">Your feedback has been submitted successfully. We appreciate you taking the time to complete this survey.</p>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You will be redirected to your certificate in <strong><span id="countdown">5</span></strong> seconds...
                        </div>

                        <a href="/generate-certificate/{{ $enrollment->id }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-certificate"></i> Proceed to Certificate
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let seconds = 5;
        const countdownElement = document.getElementById('countdown');
        const interval = setInterval(() => {
            seconds--;
            countdownElement.textContent = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.href = "/generate-certificate/{{ $enrollment->id }}";
            }
        }, 1000);
    </script>
</body>
</html>
