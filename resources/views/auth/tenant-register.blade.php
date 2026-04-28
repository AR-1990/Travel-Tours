<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Signup - {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-blue-50 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="max-w-2xl w-full bg-white rounded-2xl shadow-xl p-8 border border-indigo-100">
        <h2 class="text-3xl font-bold text-indigo-700 mb-2">Agent Signup</h2>
        <p class="text-gray-600 mb-6">Create your agency account. Super admin approval is required before login.</p>

        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="list-disc list-inside text-red-800 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('tenant.register') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent Name</label>
                    <input type="text" name="tenant_name" value="{{ old('tenant_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agent Email</label>
                    <input type="email" name="tenant_email" value="{{ old('tenant_email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Agent Phone</label>
                <input type="text" name="tenant_phone" value="{{ old('tenant_phone') }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg">
            </div>

            <h3 class="font-semibold text-gray-800 pt-2">Agent Admin Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full px-4 py-3 border border-gray-300 rounded-lg">
                </div>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white py-3 rounded-lg font-semibold">Submit Agent Signup</button>
        </form>
    </div>
</body>
</html>
