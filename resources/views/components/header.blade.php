<header class="bg-base-100 border-b border-gray-200 px-6 py-4">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
                    <i class="fas fa-wrench text-white text-sm"></i>
                </div>
                <h1 class="text-xl font-bold text-black">AutoCare Pro</h1>
            </div>
            <span class="text-gray-500">|</span>
            <p class="text-gray-700">Garage Management System</p>
        </div>
        <div class="flex items-center space-x-4">
            <div class="relative">
                <input type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-black"></i>
            </div>
            <button class="btn btn-ghost btn-square relative">
                <i class="fas fa-bell w-5 h-5 text-black"></i>
                <span class="absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full"></span>
            </button>
            <div class="dropdown dropdown-end">
                <label tabindex="0" class="btn btn-ghost flex items-center gap-2">
                    <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=32&h=32&fit=crop&crop=face" alt="Profile" class="w-8 h-8 rounded-full">
                    <span class="text-black font-medium">John Smith</span>
                    <i class="fas fa-chevron-down text-black text-xs"></i>
                </label>
                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-md bg-base-100 rounded-lg w-52 border border-gray-200">
                    <li><a class="text-black"><i class="fas fa-user w-5 h-5 text-black"></i> Profile</a></li>
                    <li><a class="text-black"><i class="fas fa-cog w-5 h-5 text-black"></i> Settings</a></li>
                    <li><a class="text-black"><i class="fas fa-sign-out-alt w-5 h-5 text-black"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
