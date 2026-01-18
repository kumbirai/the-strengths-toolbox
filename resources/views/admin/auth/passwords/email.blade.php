<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Reset Password - {{ config('app.name') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Reset Password
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Enter your email to receive a password reset link
                </p>
            </div>
            
            <form class="mt-8 space-y-6" action="{{ route('admin.password.email') }}" method="POST">
                @csrf
                
                @if(session('status'))
                    <div class="rounded-md bg-green-50 p-4">
                        <p class="text-sm text-green-800">{{ session('status') }}</p>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="rounded-md bg-red-50 p-4">
                        <ul class="list-disc pl-5 space-y-1 text-sm text-red-700">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-primary-500 focus:border-primary-500 focus:z-10 sm:text-sm" 
                        placeholder="Email address"
                        value="{{ old('email') }}"
                    >
                </div>
                
                <div>
                    <button 
                        type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        Send Password Reset Link
                    </button>
                </div>
                
                <div class="text-center">
                    <a href="{{ route('admin.login') }}" class="text-sm text-primary-600 hover:text-primary-500">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
