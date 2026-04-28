@extends('layouts.main')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl w-full space-y-8">
        <!-- Header -->
        <div class="text-center">
            <h1 class="text-5xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-blue-600 mb-4">
                Travel Tours
            </h1>
            <p class="text-xl text-gray-600">Travel management platform for super admin, agents, sub-agents, and users</p>
            <div class="mt-6">
                <a href="{{ route('blogs.index') }}" class="inline-flex items-center px-5 py-2.5 rounded-lg bg-white border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                    <i class="fas fa-blog mr-2"></i> Blogs
                </a>
            </div>
        </div>
        
        <!-- Features Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-12">
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-indigo-100">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Admin Panel</h3>
                </div>
                <p class="text-gray-600">Manage agents, sub-agents, users, and platform settings</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-indigo-100">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">Roles & Permissions</h3>
                </div>
                <p class="text-gray-600">Control access by role with tenant-aware permissions</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-indigo-100">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">User Authentication</h3>
                </div>
                <p class="text-gray-600">Dedicated secure login portals for each account type</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow border border-indigo-100">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-green-600 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">API Structure</h3>
                </div>
                <p class="text-gray-600">RESTful API foundation for bookings and travel operations</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-12">
            <a href="{{ route('login.form') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Users
            </a>
            <a href="{{ route('admin.login') }}" class="inline-flex items-center px-6 py-3 border-2 border-indigo-600 text-base font-medium rounded-lg text-indigo-600 bg-white hover:bg-indigo-50 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Super Admin
            </a>
            <a href="{{ route('agent.login') }}" class="inline-flex items-center px-6 py-3 border-2 border-purple-600 text-base font-medium rounded-lg text-purple-700 bg-white hover:bg-purple-50 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"></path>
                </svg>
                Agent
            </a>
            <a href="{{ route('subagent.login') }}" class="inline-flex items-center px-6 py-3 border-2 border-cyan-600 text-base font-medium rounded-lg text-cyan-700 bg-white hover:bg-cyan-50 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 100 8 4 4 0 000-8zm0 10c-3.314 0-6 2.239-6 5v1h12v-1c0-2.761-2.686-5-6-5z"></path>
                </svg>
                Sub-Agent
            </a>
        </div>
    </div>
</div>
@endsection
