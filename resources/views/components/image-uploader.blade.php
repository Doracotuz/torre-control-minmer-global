@props(['name', 'label', 'current' => null])

<div x-data="{ 
        preview: '{{ $current }}', 
        handleFile(e) {
            const file = e.target.files[0];
            if(file) {
                const reader = new FileReader();
                reader.onload = (e) => { this.preview = e.target.result };
                reader.readAsDataURL(file);
            }
        }
     }" class="relative">
    
    <label class="block text-xs font-bold text-gray-500 mb-2">{{ $label }}</label>
    
    <div class="img-upload-box w-full aspect-square rounded-2xl flex flex-col items-center justify-center cursor-pointer relative overflow-hidden bg-gray-50 group"
         @click="$refs.input.click()">
        
        <template x-if="preview">
            <img :src="preview" class="absolute inset-0 w-full h-full object-cover">
        </template>

        <div x-show="!preview" class="text-center p-4">
            <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2 text-gray-400 group-hover:bg-[#ff9c00] group-hover:text-white transition-colors">
                <i class="fas fa-cloud-upload-alt text-xl"></i>
            </div>
            <span class="text-xs font-semibold text-gray-400 group-hover:text-[#ff9c00]">Subir Imagen</span>
        </div>

        <div x-show="preview" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
            <span class="text-white text-xs font-bold px-3 py-1 rounded-full border border-white">Cambiar</span>
        </div>

        <input type="file" name="{{ $name }}" x-ref="input" class="hidden" accept="image/*" @change="handleFile">
    </div>
</div>