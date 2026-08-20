@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#0ca3a6] focus:ring-[#0ca3a6] rounded-md shadow-sm']) }}>
