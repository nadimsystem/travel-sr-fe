<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: booking_travel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sutan Raya Business OS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        .bg-pattern {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-blue-800 to-slate-900 h-screen w-full flex items-center justify-center relative overflow-hidden bg-pattern">

    <!-- Decorative Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-500/20 blur-3xl"></div>
        <div class="absolute top-[40%] -right-[10%] w-[40%] h-[40%] rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute -bottom-[20%] left-[20%] w-[40%] h-[40%] rounded-full bg-cyan-500/20 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md p-6 relative z-10">
        <!-- Card -->
        <div class="glass-effect rounded-2xl shadow-2xl p-8 border border-white/20">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="mb-6 flex justify-center">
                    <img src="../image/logo.png" alt="Sutan Raya Logo" class="h-24 w-auto object-contain drop-shadow-xl hover:scale-105 transition-transform duration-300">
                </div>
                <h1 class="text-2xl font-bold text-slate-800">Sutan Raya</h1>
                <p class="text-sm font-medium text-slate-500 mt-1 uppercase tracking-wide">Business Operating System V12</p>
            </div>

            <!-- Form -->
            <form id="loginForm" class="space-y-5">
                <div>
                    <label for="username" class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-user text-slate-400 text-sm"></i>
                        </div>
                        <input type="text" id="username" name="username" required 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="Masukkan username">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-500 uppercase mb-1.5 ml-1">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-400 text-sm"></i>
                        </div>
                        <input type="password" id="password" name="password" required 
                            class="w-full pl-10 pr-12 py-3 bg-slate-50 border border-slate-200 text-slate-900 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all placeholder:text-slate-400 font-medium"
                            placeholder="••••••••">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center cursor-pointer text-slate-400 hover:text-slate-600 transition-colors focus:outline-none z-20">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-slate-500 cursor-pointer hover:text-slate-700">
                        <input type="checkbox" id="rememberMe" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mr-2">
                        Ingat saya
                    </label>
                    <a href="https://wa.me/6282282425862?text=lupa%20password%20login%20aplikasi%20booking%20admin%20sutan%20raya" target="_blank" class="text-blue-600 hover:text-blue-700 font-semibold transition-colors">Lupa password?</a>
                </div>

                <div id="errorMessage" class="hidden bg-red-50 text-red-600 text-sm p-4 rounded-xl border border-red-100 flex items-start gap-3">
                    <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                    <span id="errorText">Error message here</span>
                </div>

                <button type="submit" 
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-blue-500/25 active:scale-[0.98] flex items-center justify-center gap-2 group">
                    <span>Masuk ke Sistem</span>
                    <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </form>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-blue-200/60 text-xs font-medium tracking-widest uppercase hover:text-white/80 transition-colors cursor-default">
                sutanraya.com | 2026
            </p>
        </div>
    </div>

    <script>
        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            // Toggle between eye and eye-slash
            if (type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        // Remember Me Logic
        document.addEventListener('DOMContentLoaded', () => {
            const savedUsername = localStorage.getItem('saved_username');
            if (savedUsername) {
                document.getElementById('username').value = savedUsername;
                document.getElementById('rememberMe').checked = true;
            }
        });

        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const rememberMe = document.getElementById('rememberMe').checked;
            const errorDiv = document.getElementById('errorMessage');
            const errorText = document.getElementById('errorText');
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnContent = submitBtn.innerHTML;

            // Handle LocalStorage for Remember Me
            if (rememberMe) {
                localStorage.setItem('saved_username', username);
            } else {
                localStorage.removeItem('saved_username');
            }

            // Reset UI
            errorDiv.classList.add('hidden');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
            submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

            try {
                const response = await fetch('api.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'login',
                        username: username,
                        password: password,
                        remember: rememberMe
                    })
                });

                const data = await response.json();

                if (data.status === 'success') {
                    // Success animation or feedback could go here
                    submitBtn.innerHTML = '<i class="fa-solid fa-check"></i> Berhasil!';
                    submitBtn.classList.remove('from-blue-600', 'to-blue-700');
                    submitBtn.classList.add('from-green-500', 'to-green-600');
                    
                    setTimeout(() => {
                        window.location.href = 'booking_travel.php';
                    }, 500);
                } else {
                    errorText.textContent = data.message || 'Login gagal. Silakan cek username dan password.';
                    errorDiv.classList.remove('hidden');
                    // Shake animation for error
                    const card = document.querySelector('.glass-effect');
                    card.classList.add('animate-[shake_0.5s_ease-in-out]');
                    setTimeout(() => card.classList.remove('animate-[shake_0.5s_ease-in-out]'), 500);
                    
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnContent;
                    submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
                }
            } catch (error) {
                console.error('Error:', error);
                errorText.textContent = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
            }
        });
    </script>
    
    <!-- Tailwind Custom Config for Shake Animation -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    keyframes: {
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-4px)' },
                            '20%, 40%, 60%, 80%': { transform: 'translateX(4px)' },
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>