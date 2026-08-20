<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#dfc91b] border border-transparent rounded-md font-semibold text-xs text-[#1a1000] uppercase tracking-widest hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-[#0ca3a6] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
