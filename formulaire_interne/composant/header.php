<header>   
    <div class="fixed top-0 left-0 right-0 z-50 px-3 py-2 overflow-hidden text-white transition-all duration-300 shadow-2xl lg:p-4 lg:mb-10 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-b-xl">
        <!-- Effet de fond décoratif -->
        <!-- <div class="absolute inset-0 bg-black bg-opacity-10"></div>
        <div class="absolute top-0 right-0 w-32 h-32 translate-x-16 -translate-y-16 bg-gray-100 rounded-full bg-opacity-10"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 -translate-x-12 translate-y-12 bg-gray-100 rounded-full bg-opacity-5"></div> -->
        
        <!-- Contenu principal -->
        <div class="relative z-10">
            <!-- En-tête de bienvenue -->
            <div class="flex items-center justify-between mb-1">
                <div>
                    <h1 class="mb-2 font-bold text-transparent lg:text-4xl bg-gradient-to-r from-white to-blue-100 bg-clip-text">
                        Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?> !
                    </h1>
                </div>
                <div class="flex space-x-2 lg:space-x-8">
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'administrateur'): ?>
                        <a href="manage_users.php" class="px-2 font-semibold text-black transition-all duration-300 transform bg-white border-2 border-gray-600 border-opacity-100 rounded-lg lg:text-lg lg:py-3 lg:px-3 group hover:bg-gray-300 hover:-translate-y-1">
                            Gérer les utilisateurs
                        </a>
                    <?php endif; ?>
                    <!-- Bouton déconnexion -->
                    <a href="./logout.php" 
                    class="flex items-center justify-center px-1 font-semibold text-black transition-all duration-300 transform bg-white border-2 border-gray-600 border-opacity-100 rounded-lg lg:py-3 lg:px-6 group hover:bg-gray-300 hover:-translate-y-1">
                        <svg class="w-5 h-5 transition-transform duration-300 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Déconnexion
                    </a>
                    <!-- Icône décorative -->
                    <!-- <div class="hidden md:block">
                        <div class="flex items-center justify-center w-16 h-16 bg-white rounded-full bg-opacity-20">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </div>
</header>