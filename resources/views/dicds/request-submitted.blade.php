<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted - DICDS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4" style="background-color: #f4f6f0;">
                    <svg class="h-6 w-6 text-green-600" style="color: #516425;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Access Request Submitted
                </h2>

                <p class="text-gray-600 mb-6">
                    Your access request has been successfully submitted. You will receive an email notification once your request has been reviewed and approved by an administrator.
                </p>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <strong>What's Next?</strong><br>
                        • Your request will be reviewed by our team<br>
                        • You'll receive an email with the decision<br>
                        • Approval typically takes 1-2 business days
                    </p>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('dicds.login') }}" 
                        class="block w-full py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Return to Login
                    </a>
                    <a href="{{ route('dashboard') }}" 
                        class="block w-full py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
