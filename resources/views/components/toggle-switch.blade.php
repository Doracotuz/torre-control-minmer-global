@props(['name', 'label', 'checked' => false, 'color' => 'bg-[#ff9c00]'])

<div class="flex items-center justify-between p-4 bg-white rounded-2xl border border-gray-100 hover:border-gray-300 hover:shadow-md transition-all duration-300 group">
    <span class="text-sm font-bold text-gray-700 group-hover:text-[#2c3856] transition-colors">{{ $label }}</span>
    
    <div class="relative inline-block w-12 mr-2 align-middle select-none transition duration-200 ease-in">
        <input type="checkbox" name="{{ $name }}" id="{{ $name }}" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer transition-all duration-300 ease-in-out" style="top: 2px; left: 2px;" {{ $checked ? 'checked' : '' }}/>
        <label for="{{ $name }}" class="toggle-label block overflow-hidden h-7 rounded-full bg-gray-200 cursor-pointer transition-colors duration-300 ease-in-out"></label>
    </div>
    
    <style>
        #{{ $name }}:checked + .toggle-label {
            background-color: {{ $color === 'bg-[#ff9c00]' ? '#ff9c00' : ($color === 'bg-sky-500' ? '#0ea5e9' : '#a855f7') }};
        }
        #{{ $name }}:checked {
            right: 0;
            border-color: white;
            transform: translateX(100%);
            left: auto; 
        }
        #{{ $name }} {
            right: auto;
            left: 2px;
            border-color: white;
        }
    </style>
</div>