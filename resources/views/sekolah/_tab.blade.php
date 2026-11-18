<div class="border-b border-gray-200">
    <nav class="-mb-px flex space-x-6">
        <a href="{{ route('sekolah.edit', $sekolah) }}" class="inline-flex items-center py-3 px-1 border-b-2 text-sm font-medium transition {{ request()->routeIs('sekolah.edit') ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Data Sekolah
        </a>
        <a href="{{ route('sekolah.profil.edit', $sekolah) }}" class="inline-flex items-center py-3 px-1 border-b-2 text-sm font-medium transition {{ request()->routeIs('sekolah.profil.*') ? 'border-indigo-600 text-indigo-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profil Sekolah
        </a>
    </nav>
</div>
