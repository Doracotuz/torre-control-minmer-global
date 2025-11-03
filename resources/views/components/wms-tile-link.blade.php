@props([
    'href',
    'icon',
    'color' => 'gray',
    'small' => false
])

@php
$colorClasses = [
    'gray' => [
        'bg' => 'bg-gray-100',
        'text' => 'text-gray-600',
        'hover_text' => 'hover:text-gray-900',
        'hover_border' => 'hover:border-gray-400',
    ],
    'green' => [
        'bg' => 'bg-green-100',
        'text' => 'text-green-600',
        'hover_text' => 'hover:text-green-800',
        'hover_border' => 'hover:border-green-400',
    ],
    'blue' => [
        'bg' => 'bg-blue-100',
        'text' => 'text-blue-600',
        'hover_text' => 'hover:text-blue-800',
        'hover_border' => 'hover:border-blue-400',
    ],
    'yellow' => [
        'bg' => 'bg-yellow-100',
        'text' => 'text-yellow-600',
        'hover_text' => 'hover:text-yellow-800',
        'hover_border' => 'hover:border-yellow-400',
    ],
    'purple' => [
        'bg' => 'bg-purple-100',
        'text' => 'text-purple-600',
        'hover_text' => 'hover:text-purple-800',
        'hover_border' => 'hover:border-purple-400',
    ],
    'red' => [
        'bg' => 'bg-red-100',
        'text' => 'text-red-600',
        'hover_text' => 'hover:text-red-800',
        'hover_border' => 'hover:border-red-400',
    ],
];

$selectedColor = $colorClasses[$color] ?? $colorClasses['gray'];
@endphp

<a href="{{ $href }}" 
   class="group flex items-center p-3 rounded-lg border border-gray-200 shadow-sm transition-all duration-300 transform {{ $selectedColor['hover_border'] }} hover:shadow-md hover:bg-gray-50">
    
    <span class="flex-shrink-0 p-2 rounded-full {{ $selectedColor['bg'] }}">
        <svg class="h-5 w-5 {{ $selectedColor['text'] }}" 
             xmlns="http://www.w3.org/2000/svg" 
             fill="none" 
             viewBox="0 0 24 24" 
             stroke-width="2" 
             stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
        </svg>
    </span>

    <span class="ml-3 font-semibold text-gray-700 {{ $selectedColor['hover_text'] }} transition-colors {{ $small ? 'text-sm' : 'text-base' }}">
        {{ $slot }}
    </span>

    <svg class="w-4 h-4 text-gray-400 ml-auto opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
</a>