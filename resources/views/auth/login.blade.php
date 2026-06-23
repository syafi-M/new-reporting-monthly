<x-guest-layout>
    
    <div class="flex min-h-screen">
        
        <!-- Left Side - Illustration -->
        <div class="relative items-center justify-center hidden p-12 overflow-hidden lg:flex lg:w-1/2 illustration">
            <div class="relative z-10 max-w-lg text-center">
                <h1 class="mb-4 text-4xl font-bold text-white">Welcome Back</h1>
                <p class="mb-8 text-lg text-indigo-100">Sign in to your account and continue</p>
                
                <!-- Illustration SVG placeholder - you can replace with actual image -->
                <div class="relative">
                    <div class="max-w-md p-8 mx-auto bg-white/10 backdrop-blur-sm rounded-3xl">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="flex items-center justify-center p-6 bg-white/20 rounded-2xl">
                                <i class="text-5xl text-white ri-mail-line"></i>
                            </div>
                            <div class="flex items-center justify-center p-6 bg-white/20 rounded-2xl">
                                <i class="text-5xl text-white ri-notification-3-line"></i>
                            </div>
                            <div class="flex items-center justify-center p-6 bg-white/20 rounded-2xl">
                                <i class="text-5xl text-white ri-calendar-line"></i>
                            </div>
                            <div class="flex items-center justify-center p-6 bg-white/20 rounded-2xl">
                                <i class="text-5xl text-white ri-settings-3-line"></i>
                            </div>
                        </div>
                        <div class="flex items-center justify-center space-x-4">
                            <div class="flex items-center justify-center w-16 h-16 rounded-full bg-white/20">
                                <i class="text-2xl text-white ri-user-line"></i>
                            </div>
                            <div class="text-left">
                                <div class="w-24 h-3 mb-2 rounded bg-white/30"></div>
                                <div class="w-16 h-2 rounded bg-white/20"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Decorative circles -->
            <div class="absolute w-32 h-32 rounded-full top-10 left-10 bg-white/10 blur-2xl"></div>
            <div class="absolute w-40 h-40 rounded-full bottom-10 right-10 bg-white/10 blur-3xl"></div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="flex items-center justify-center w-full p-8 lg:w-1/2">
            <div class="w-full max-w-md">
                
                <!-- Logo -->
                <div class="mb-8">
                    <div class="flex items-center mb-2 space-x-2">
                        <div class="flex items-center justify-center w-10 h-10 bg-indigo-600 rounded-lg">
                            <i class="text-xl text-white ri-lock-password-line"></i>
                        </div>
                        <span class="text-2xl font-bold text-gray-800">{{ config('app.name', 'SILAB') }}</span>
                    </div>
                </div>

                <!-- Form Header -->
                <div class="mb-8">
                    <h2 class="mb-2 text-3xl font-bold text-gray-900">Log In</h2>
                    <p class="text-gray-600">Masukkan Akun Absensi Untuk Masuk.</p>
                </div>

                <div class="mb-6">
                    <div class="rounded-xl border-[3px] border-dashed border-indigo-600/70 bg-indigo-50/70 p-3.5">
                        <p class="flex flex-wrap items-center gap-2 text-sm text-indigo-900">
                            <i class="text-base ri-chat-smile-2-line"></i>
                            <span>Ingin memberikan ulasan pekerjaan?</span>
                            <a href="{{ route('rating-pekerjaan.create') }}"
                                class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 font-semibold text-indigo-600 transition hover:bg-indigo-600 hover:text-indigo-400">
                                Klik di sini
                                <i class="text-sm ri-arrow-right-line"></i>
                            </a>
                        </p>
                    </div>
                </div>

                <!-- Error Messages -->
                @if ($errors->any())
                <div class="mb-6 alert alert-error">
                    <i class="text-xl ri-error-warning-line"></i>
                    <div>
                        <ul class="text-sm list-none">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Username Field -->
                    <div class="form-control">
                        <label class="pb-2 label">
                            <span class="font-medium text-gray-700 label-text">Masukkan Nama / Username</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            value="{{ old('name') }}"
                            placeholder="Username" 
                            class="input input-bordered w-full bg-white border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 @error('name') input-error @enderror" 
                            required 
                            autofocus
                        />
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="form-control">
                        <label class="pb-2 label">
                            <span class="font-medium text-gray-700 label-text">Masukkan Password</span>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            placeholder="Password" 
                            class="input input-bordered w-full bg-white border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 @error('password') input-error @enderror" 
                            required
                        />
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="text-right">
                        @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                            Lupa Password ?
                        </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full h-12 text-base font-medium text-white bg-indigo-600 border-none btn btn-primary hover:bg-indigo-700">
                        Masuk
                    </button>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-center pt-2">
                        <label class="gap-2 cursor-pointer label">
                            <input type="checkbox" name="remember" class="border-2 checkbox checkbox-primary checkbox-sm" />
                            <span class="text-gray-600 label-text">Biarkan saya tetap masuk</span>
                        </label>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>