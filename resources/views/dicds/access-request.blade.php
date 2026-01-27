<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DICDS Access Request</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    DICDS Access Request
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Complete your access request to continue
                </p>
            </div>

            <form class="mt-8 space-y-6 bg-white p-8 rounded-lg shadow" method="POST" action="{{ route('dicds.access-request') }}">
                @csrf

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div>
                    <label for="desired_application" class="block text-sm font-medium text-gray-700">
                        Desired Application *
                    </label>
                    <select id="desired_application" name="desired_application" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Application</option>
                        <option value="DRS">Driver Record System (DRS)</option>
                        <option value="DICDS">DICDS</option>
                    </select>
                </div>

                <div>
                    <label for="desired_role" class="block text-sm font-medium text-gray-700">
                        Desired Role *
                    </label>
                    <select id="desired_role" name="desired_role" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">Select Role</option>
                        <option value="DRS_Provider_Admin">DRS Provider Admin</option>
                        <option value="DRS_Provider_User">DRS Provider User</option>
                        <option value="DRS_School_Admin">DRS School Admin</option>
                    </select>
                </div>

                <div>
                    <label for="user_group" class="block text-sm font-medium text-gray-700">
                        User Group *
                    </label>
                    <input type="text" id="user_group" name="user_group" required 
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit Access Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
