<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    </head>
    <body>
        <div class="min-h-screen bg-cover bg-center flex items-center justify-center relative">
            <div class="absolute inset-0 bg-gradient-to-t from-white to-transparent"></div>
            <div class="bg-gradient-to-b from-sky-200 to-white backdrop-blur-sm p-8 rounded-lg shadow-md w-96 relative z-10">
                <div class="flex flex-col items-center justify-center gap-4 mb-6">
                    <h1 class="text-2xl font-bold">Connexion</h1>
                    <p class="text-sm text-gray-600 text-center">
                        Connectez-vous avec votre email pour accéder à vos outils et
                        ressources.
                    </p>
                </div>

                <livewire:auth.login-form />
            </div>
        </div>
    </body>
</html>