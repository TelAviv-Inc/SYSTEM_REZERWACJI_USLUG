<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logowanie do Systemu Rezerwacji Usług</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6',
                        accent: '#f59e0b',
                        dark: '#1f2937',
                        light: '#f9fafb'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden max-w-md w-full border border-gray-200">
        <div class="bg-gradient-to-r from-primary to-secondary text-white p-6 text-center border-b border-gray-200">
            <h2 class="text-2xl font-bold mb-1"><i class="fas fa-user-lock mr-2"></i>System Rezerwacji Usług</h2>
            <p class="text-blue-100">Zaloguj się do swojego konta</p>
        </div>
        
        <div class="p-6">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                <div class="mb-5">
                    <label for="email" class="block text-gray-700 font-medium mb-2">Adres email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autocomplete="email" 
                           autofocus
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    @error('email')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="password" class="block text-gray-700 font-medium mb-2">Hasło</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           required 
                           autocomplete="current-password"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent transition duration-200">
                    @error('password')
                        <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-6 flex items-center">
                    <input type="checkbox" 
                           id="remember" 
                           name="remember" 
                           class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="remember" class="text-gray-700">Zapamiętaj mnie</label>
                </div>

                <div class="mb-6">
                    <button type="submit" class="w-full bg-gradient-to-r from-primary to-secondary text-white py-3 px-4 rounded-lg font-medium hover:from-primary hover:to-secondary transition duration-300 transform hover:-translate-y-0.5 shadow-md hover:shadow-lg">
                        Zaloguj się
                    </button>
                </div>

                @if (Route::has('password.request'))
                    <div class="text-center mb-4">
                        <a class="text-primary hover:text-blue-800 font-medium hover:underline transition duration-200" href="{{ route('password.request') }}">
                            Zapomniałeś hasła?
                        </a>
                    </div>
                @endif
            </form>

            <div class="flex items-center justify-center my-6">
                <div class="border-t border-gray-300 flex-grow"></div>
                <span class="px-4 text-gray-500 bg-white">lub</span>
                <div class="border-t border-gray-300 flex-grow"></div>
            </div>

            <div class="flex justify-center space-x-4 mb-6">
                <a href="#" class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white hover:bg-blue-700 transition duration-300 transform hover:-translate-y-0.5 shadow-md">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="w-12 h-12 rounded-full bg-red-500 flex items-center justify-center text-white hover:bg-red-600 transition duration-300 transform hover:-translate-y-0.5 shadow-md">
                    <i class="fab fa-google"></i>
                </a>
                <a href="#" class="w-12 h-12 rounded-full bg-blue-400 flex items-center justify-center text-white hover:bg-blue-500 transition duration-300 transform hover:-translate-y-0.5 shadow-md">
                    <i class="fab fa-twitter"></i>
                </a>
            </div>

            <div class="text-center">
                <p class="text-gray-600">
                    Nie masz konta? 
                    <a href="{{ route('register') }}" class="text-primary font-medium hover:text-blue-800 hover:underline transition duration-200">Zarejestruj się</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>