<x-app-layout>
    <x-slot name="header"></x-slot>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Raleway:wght@700;800;900&display=swap');
        [x-cloak] { display: none !important; }
        .font-raleway { font-family: 'Raleway', sans-serif; }
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .perspective-container { perspective: 1000px; }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px 0 rgba(44, 56, 86, 0.1);
        }
        
        .input-group:focus-within label { color: #ff9c00; transform: translateY(-1.5rem) scale(0.8); }
        .input-group label { transition: all 0.3s ease; transform-origin: left top; }
        .input-group input:not(:placeholder-shown) + label { transform: translateY(-1.5rem) scale(0.8); }
        
        .carousel-enter-active, .carousel-leave-active { transition: all 0.6s cubic-bezier(0.23, 1, 0.32, 1); }
        .carousel-enter-from { opacity: 0; transform: translateX(100px) scale(0.95); }
        .carousel-leave-to { opacity: 0; transform: translateX(-100px) scale(0.95); }
        
        .radio-tile:checked + div { border-color: #ff9c00; background: linear-gradient(145deg, #ffffff, #fff8f0); box-shadow: 0 10px 20px -5px rgba(255, 156, 0, 0.3); transform: translateY(-4px); }
        .radio-tile:checked + div .indicator { background-color: #ff9c00; border-color: #ff9c00; }
        .radio-tile:checked + div .indicator svg { opacity: 1; }
        
        .folder-check:checked + div { background-color: #2c3856; border-color: #2c3856; }
        .folder-check:checked + div svg { opacity: 1; }
        
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #ff9c00; }
        
        .blob { position: absolute; filter: blur(80px); opacity: 0.6; animation: float 10s infinite ease-in-out; z-index: 0; }
        @keyframes float { 0% { transform: translate(0, 0); } 50% { transform: translate(30px, -50px); } 100% { transform: translate(0, 0); } }
    </style>

    <div class="min-h-screen font-montserrat relative overflow-hidden flex flex-col">
        
        <div class="blob w-96 h-96 top-0 left-0 rounded-full mix-blend-multiply"></div>
        <div class="blob w-96 h-96 bottom-0 right-0 rounded-full mix-blend-multiply animation-delay-2000"></div>

        <div x-data="{ 
            step: 1,
            maxSteps: 4,
            direction: 'next',
            photoPreview: null,
            photoName: null,
            isClient: {{ old('is_client') ? 'true' : 'false' }},
            selectedFolderIds: {{ json_encode(old('accessible_folder_ids', [])) }},
            selectedPermissions: {{ json_encode(old('ff_granular_permissions', [])) }},
            roles: {{ json_encode($roles) }},
            selectedRoleId: '',
            folders: [],
            loadingFolders: true,
            
            goToStep(target) {
                if(target > this.step && !this.validateStep()) return;
                this.direction = target > this.step ? 'next' : 'prev';
                this.step = target;
            },
            next() {
                if(this.validateStep()) {
                    this.direction = 'next';
                    if(this.step < this.maxSteps) this.step++;
                }
            },
            prev() {
                this.direction = 'prev';
                if(this.step > 1) this.step--;
            },
            validateStep() {
                const container = document.getElementById(`step-${this.step}`);
                if(!container) return true;
                const inputs = container.querySelectorAll('input[required], select[required]');
                let valid = true;
                inputs.forEach(el => {
                    if(!el.checkValidity()) {
                        el.reportValidity();
                        valid = false;
                    }
                });
                return valid;
            },
            loadFolders(parentId = null) {
                this.loadingFolders = true;
                fetch('{{ route('admin.api.folders_for_client_access') }}?parent_id=' + (parentId || ''))
                    .then(r => r.json())
                    .then(data => {
                        this.folders = data.map(f => ({ ...f, isOpen: false, children: [] }));
                        this.loadingFolders = false;
                    });
            },
            toggleFolder(folder) {
                folder.isOpen = !folder.isOpen;
                if (folder.isOpen && folder.children.length === 0 && folder.has_children) {
                    fetch('{{ route('admin.api.folders_for_client_access') }}?parent_id=' + folder.id)
                        .then(r => r.json())
                        .then(data => {
                            folder.children = data.map(c => ({ ...c, isOpen: false, children: [] }));
                        });
                }
            },
            init() {
                this.loadFolders();
                if(this.isClient && document.getElementById('area_id')) {
                    document.getElementById('area_id').disabled = true;
                }
                this.$watch('selectedRoleId', (value) => {
                    const role = this.roles.find(r => r.id == value);
                    if (role) {
                        document.getElementById('ff_role_name').value = role.name;
                        // permissions is json/array in DB, so it comes as array here
                        this.selectedPermissions = role.permissions || [];
                    }
                });
            }
        }" class="flex-1 flex flex-col relative z-10 h-full max-h-screen">

            <header class="pt-8 px-6 lg:px-12 flex justify-between items-end mb-4">
                <div>
                    <h1 class="font-raleway font-black text-4xl text-[#2c3856] leading-none tracking-tight">Crear Usuario</h1>
                </div>
                <div class="flex space-x-2">
                    <template x-for="i in maxSteps">
                        <div class="h-1.5 rounded-full transition-all duration-500 ease-out" 
                             :class="i <= step ? 'w-8 bg-[#ff9c00]' : 'w-2 bg-gray-300'"></div>
                    </template>
                </div>
            </header>

            <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="flex-1 flex flex-col lg:flex-row overflow-hidden px-4 lg:px-8 pb-8 gap-6">
                @csrf

                <div class="hidden lg:flex flex-col w-64 shrink-0 space-y-2 pt-10">
                    <button type="button" @click="goToStep(1)" :class="step === 1 ? 'bg-white shadow-lg text-[#2c3856] scale-105' : 'text-gray-500 hover:bg-white/50'" class="px-6 py-4 rounded-2xl text-left transition-all duration-300 font-bold text-sm flex items-center group">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs mr-3 transition-colors" :class="step >= 1 ? 'bg-[#ff9c00] text-white' : 'bg-gray-200'">1</span>
                        Identidad
                    </button>
                    <button type="button" @click="goToStep(2)" :class="step === 2 ? 'bg-white shadow-lg text-[#2c3856] scale-105' : 'text-gray-500 hover:bg-white/50'" class="px-6 py-4 rounded-2xl text-left transition-all duration-300 font-bold text-sm flex items-center group">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs mr-3 transition-colors" :class="step >= 2 ? 'bg-[#ff9c00] text-white' : 'bg-gray-200'">2</span>
                        Roles
                    </button>
                    <button type="button" @click="goToStep(3)" :class="step === 3 ? 'bg-white shadow-lg text-[#2c3856] scale-105' : 'text-gray-500 hover:bg-white/50'" class="px-6 py-4 rounded-2xl text-left transition-all duration-300 font-bold text-sm flex items-center group">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs mr-3 transition-colors" :class="step >= 3 ? 'bg-[#ff9c00] text-white' : 'bg-gray-200'">3</span>
                        Permisos
                    </button>
                    <button type="button" @click="goToStep(4)" :class="step === 4 ? 'bg-white shadow-lg text-[#2c3856] scale-105' : 'text-gray-500 hover:bg-white/50'" class="px-6 py-4 rounded-2xl text-left transition-all duration-300 font-bold text-sm flex items-center group">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs mr-3 transition-colors" :class="step >= 4 ? 'bg-[#ff9c00] text-white' : 'bg-gray-200'">4</span>
                        Interfaz
                    </button>
                </div>

                <div class="flex-1 glass-card rounded-[2.5rem] relative overflow-hidden flex flex-col perspective-container shadow-2xl">
                    
                    <div class="flex-1 relative overflow-y-auto custom-scrollbar p-8 lg:p-12">
                        
                        <div x-show="step === 1" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300 absolute" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-10" class="w-full h-full" id="step-1">
                            <h2 class="text-2xl font-bold text-[#2c3856] mb-8">Información Personal</h2>
                            <div class="flex flex-col xl:flex-row gap-10">
                                <div class="w-full xl:w-1/3 flex flex-col items-center justify-start">
                                    <div class="relative group w-48 h-48 mx-auto">
                                        <div class="absolute inset-0 bg-gradient-to-tr from-[#ff9c00] to-[#ffb347] rounded-full blur-lg opacity-40 group-hover:opacity-60 transition-opacity"></div>
                                        <div class="relative w-full h-full rounded-full bg-white border-4 border-white shadow-xl overflow-hidden">
                                            <template x-if="!photoPreview">
                                                <div class="w-full h-full flex items-center justify-center bg-gray-50 text-gray-300">
                                                    <svg class="w-20 h-20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM12 12.5c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4z"></path></svg>
                                                </div>
                                            </template>
                                            <template x-if="photoPreview">
                                                <img :src="photoPreview" class="w-full h-full object-cover">
                                            </template>
                                            <label class="absolute inset-0 bg-black/40 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all cursor-pointer backdrop-blur-[2px]">
                                                <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                                                <span class="text-white text-[10px] font-bold uppercase tracking-widest">Cambiar</span>
                                                <input type="file" name="profile_photo" class="hidden" @change="photoName = $event.target.files[0].name; const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL($event.target.files[0]);">
                                            </label>
                                        </div>
                                    </div>
                                    <p class="mt-4 text-xs font-bold text-gray-400 uppercase tracking-widest" x-text="photoName || 'Sin foto seleccionada'"></p>
                                </div>
                                <div class="w-full xl:w-2/3 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-8">
                                    <div class="input-group relative">
                                        <input type="text" name="name" id="name" required placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg" value="{{ old('name') }}">
                                        <label for="name" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Nombre Completo</label>
                                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                                    </div>
                                    <div class="input-group relative">
                                        <input type="email" name="email" id="email" required placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg" value="{{ old('email') }}">
                                        <label for="email" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Email Corporativo</label>
                                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                    </div>
                                    <div class="input-group relative">
                                        <input type="tel" name="phone_number" id="phone_number" placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg" value="{{ old('phone_number') }}">
                                        <label for="phone_number" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Teléfono</label>
                                    </div>
                                    <div class="input-group relative">
                                        <select name="position" id="position" class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg">
                                            <option value=""></option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->name }}" {{ old('position') == $position->name ? 'selected' : '' }}>{{ $position->name }}</option>
                                            @endforeach
                                        </select>
                                        <label for="position" class="absolute left-0 top-2 text-gray-500 pointer-events-none transform -translate-y-6 scale-75">Cargo / Posición</label>
                                    </div>
                                    <div class="input-group relative">
                                        <input type="password" name="password" id="password" required placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg">
                                        <label for="password" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Contraseña</label>
                                    </div>
                                    <div class="input-group relative">
                                        <input type="password" name="password_confirmation" id="password_confirmation" required placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg">
                                        <label for="password_confirmation" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Confirmar Contraseña</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 2" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300 absolute" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-10" class="w-full h-full" id="step-2" style="display: none;">
                            <h2 class="text-2xl font-bold text-[#2c3856] mb-8 text-center">Definición de Rol</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto h-[60%]">
                                <label class="cursor-pointer relative group">
                                    <input type="checkbox" name="is_client" value="1" x-model="isClient" class="radio-tile sr-only" @change="if(isClient) { document.getElementById('area_id').disabled=true; document.getElementById('area_id').value=''; } else { document.getElementById('area_id').disabled=false; }">
                                    <div class="w-full h-full rounded-3xl border-2 border-gray-100 bg-white p-8 flex flex-col items-center justify-center transition-all duration-300 hover:border-gray-300 relative overflow-hidden">
                                        <div class="indicator w-6 h-6 rounded-full border-2 border-gray-300 absolute top-6 right-6 flex items-center justify-center transition-all duration-300">
                                            <svg class="w-3 h-3 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div class="w-20 h-20 rounded-2xl bg-orange-50 text-[#ff9c00] flex items-center justify-center mb-6 text-4xl group-hover:scale-110 transition-transform duration-500">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                        </div>
                                        <h3 class="text-xl font-black text-[#2c3856] mb-2 uppercase tracking-wide">Cliente Externo</h3>
                                        <p class="text-center text-gray-500 text-sm leading-relaxed px-4">Acceso "Read-Only" restringido a carpetas específicas. Sin acceso a módulos internos.</p>
                                    </div>
                                </label>
                                <div x-show="!isClient" class="h-full">
                                    <label class="cursor-pointer relative group h-full block">
                                        <input type="checkbox" name="is_area_admin" value="1" {{ old('is_area_admin') ? 'checked' : '' }} class="radio-tile sr-only">
                                        <div class="w-full h-full rounded-3xl border-2 border-gray-100 bg-white p-8 flex flex-col items-center justify-center transition-all duration-300 hover:border-gray-300 relative overflow-hidden">
                                            <div class="indicator w-6 h-6 rounded-full border-2 border-gray-300 absolute top-6 right-6 flex items-center justify-center transition-all duration-300">
                                                <svg class="w-3 h-3 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <div class="w-20 h-20 rounded-2xl bg-blue-50 text-[#2c3856] flex items-center justify-center mb-6 text-4xl group-hover:scale-110 transition-transform duration-500">
                                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                            </div>
                                            <h3 class="text-xl font-black text-[#2c3856] mb-2 uppercase tracking-wide">Admin de Área</h3>
                                            <p class="text-center text-gray-500 text-sm leading-relaxed px-4">Gestión total de archivos y usuarios dentro de su área asignada.</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div x-show="!isClient" class="mt-8 max-w-lg mx-auto" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                                <div class="input-group relative">
                                    <select id="area_id" name="area_id" class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg text-center">
                                        <option value=""></option>
                                        @foreach ($areas as $area)
                                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                                        @endforeach
                                    </select>
                                    <label for="area_id" class="absolute left-0 w-full text-center top-2 text-gray-500 pointer-events-none transform -translate-y-6 scale-75">Área Principal</label>
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 3" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300 absolute" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-10" class="w-full h-full" id="step-3" style="display: none;">
                            
                            <div x-show="isClient" class="h-full flex flex-col">
                                <div class="flex items-center justify-between mb-6">
                                    <h2 class="text-2xl font-bold text-[#2c3856]">Acceso a Carpetas</h2>
                                    <span class="text-xs font-bold bg-orange-100 text-[#ff9c00] px-3 py-1 rounded-full uppercase">Modo Cliente</span>
                                </div>
                                <div class="flex-1 bg-white/50 rounded-2xl border border-white/60 p-6 overflow-y-auto custom-scrollbar shadow-inner">
                                    <div x-show="loadingFolders" class="h-full flex flex-col items-center justify-center">
                                        <div class="w-10 h-10 border-4 border-[#ff9c00] border-t-transparent rounded-full animate-spin mb-4"></div>
                                        <p class="text-[#2c3856] font-bold text-xs uppercase tracking-widest">Sincronizando Archivos...</p>
                                    </div>
                                    <ul x-show="!loadingFolders" class="space-y-3">
                                        <template x-for="folder in folders" :key="folder.id">
                                            <li x-data="{ currentFolder: folder }" x-init="$nextTick(() => { $el.appendChild(document.getElementById('tree-node').content.cloneNode(true)); })"></li>
                                        </template>
                                    </ul>
                                </div>
                                <template id="tree-node">
                                    <div class="pl-4 border-l-2 border-gray-100/50">
                                        <div class="flex items-center py-2 group">
                                            <button type="button" @click="toggleFolder(currentFolder)" class="mr-2 text-gray-400 hover:text-[#ff9c00] transition-colors" x-show="currentFolder.has_children">
                                                <svg class="w-5 h-5 transform transition-transform" :class="{'rotate-90': currentFolder.isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            </button>
                                            <span class="w-7" x-show="!currentFolder.has_children"></span>
                                            
                                            <label class="flex items-center cursor-pointer flex-1">
                                                <input type="checkbox" :value="currentFolder.id" x-model="selectedFolderIds" class="folder-check sr-only">
                                                <div class="w-5 h-5 rounded border-2 border-gray-300 bg-white mr-3 flex items-center justify-center transition-all duration-200">
                                                    <svg class="w-3 h-3 text-white opacity-0 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                                <div class="flex items-center">
                                                    <svg class="w-5 h-5 text-[#ff9c00] mr-2 opacity-80" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"/></svg>
                                                    <span class="text-sm font-bold text-gray-600 group-hover:text-[#2c3856] transition-colors" x-text="currentFolder.name"></span>
                                                </div>
                                            </label>
                                        </div>
                                        <div x-show="currentFolder.isOpen" x-collapse>
                                            <ul class="mt-1">
                                                <template x-for="childFolder in currentFolder.children" :key="childFolder.id">
                                                    <li x-data="{ currentFolder: childFolder }" x-init="$nextTick(() => { $el.appendChild(document.getElementById('tree-node').content.cloneNode(true)); })"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </template>
                                <input type="hidden" name="accessible_folder_ids[]" :value="selectedFolderIds.join(',')">
                            </div>

                            <div x-show="!isClient" class="h-full flex flex-col items-center justify-center">
                                <h2 class="text-2xl font-bold text-[#2c3856] mb-2">Accesos Transversales</h2>
                                <p class="text-gray-500 mb-8 text-center max-w-md">Seleccione áreas adicionales a las que este usuario podrá acceder además de su área principal.</p>
                                
                                <div class="w-full max-w-3xl grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($areas as $area)
                                        <label class="cursor-pointer relative group">
                                            <input type="checkbox" name="accessible_area_ids[]" value="{{ $area->id }}" class="radio-tile sr-only">
                                            <div class="p-4 rounded-xl border border-gray-200 bg-white transition-all duration-300 hover:shadow-lg relative overflow-hidden group-hover:-translate-y-1">
                                                <span class="text-sm font-bold text-[#2c3856] relative z-10">{{ $area->name }}</span>
                                                <div class="indicator w-4 h-4 rounded-full border border-gray-300 absolute top-2 right-2 flex items-center justify-center transition-all">
                                                    <div class="w-2 h-2 bg-white rounded-full opacity-0"></div>
                                                </div>
                                                <div class="absolute bottom-0 left-0 w-full h-1 bg-gradient-to-r from-[#2c3856] to-[#ff9c00] opacity-0 transition-opacity duration-300 group-hover:opacity-100"></div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div x-show="step === 4" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-x-10" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-300 absolute" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 -translate-x-10" class="w-full h-full" id="step-4" style="display: none;">
                            <div class="text-center mb-10">
                                <h2 class="text-2xl font-bold text-[#2c3856]">Personalización de Interfaz</h2>
                                <p class="text-gray-500">Active los módulos visibles en el sidebar del usuario</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                                @foreach($availableModules as $key => $label)
                                    <label class="cursor-pointer relative group">
                                        <input type="checkbox" name="visible_modules[]" value="{{ $key }}" class="peer sr-only" {{ (isset($user) && $user->hasModuleAccess($key)) || (is_array(old('visible_modules')) && in_array($key, old('visible_modules'))) ? 'checked' : '' }}>
                                        <div class="p-6 rounded-2xl bg-white border border-gray-100 shadow-sm transition-all duration-300 peer-checked:bg-[#2c3856] peer-checked:border-[#2c3856] peer-checked:shadow-xl group-hover:scale-[1.02]">
                                            <div class="flex justify-between items-start mb-4">
                                                <div class="w-10 h-10 rounded-lg bg-gray-50 flex items-center justify-center text-gray-400 peer-checked:bg-white/10 peer-checked:text-[#ff9c00] transition-colors">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                                                </div>
                                                <div class="w-5 h-5 rounded-full border-2 border-gray-200 peer-checked:border-[#ff9c00] peer-checked:bg-[#ff9c00] flex items-center justify-center">
                                                    <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                </div>
                                            </div>
                                            <h3 class="font-bold text-gray-600 peer-checked:text-white text-lg transition-colors">{{ $label }}</h3>
                                            <p class="text-xs text-gray-400 mt-1 peer-checked:text-gray-300">Visible en barra lateral</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-10 pt-8 border-t border-gray-200">
                                <div class="text-center mb-8">
                                    <h3 class="text-xl font-bold text-[#ff9c00]">Mosaicos Friends & Family</h3>
                                    <p class="text-gray-400 text-sm">Seleccione qué tarjetas verá el usuario en el dashboard FF</p>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                    @foreach(\App\Models\User::availableFfTiles() as $key => $label)
                                        <label class="cursor-pointer relative group">
                                            <input type="checkbox" name="ff_visible_tiles[]" value="{{ $key }}" class="peer sr-only" 
                                                {{ (isset($user) && $user->canSeeFfTile($key)) || (is_array(old('ff_visible_tiles')) && in_array($key, old('ff_visible_tiles'))) ? 'checked' : '' }}>
                                            
                                            <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 text-center transition-all duration-300 peer-checked:bg-[#ff9c00] peer-checked:border-[#ff9c00] peer-checked:shadow-md hover:scale-105">
                                                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide peer-checked:text-white">{{ $label }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>  

                            <div class="mt-10 pt-8 border-t border-gray-200">
                                <div class="text-center mb-8">
                                    <h3 class="text-xl font-bold text-[#ff9c00]">Permisos Granulares Friends & Family</h3>
                                    <p class="text-gray-400 text-sm">Defina el rol y permisos específicos</p>
                                </div>

                                <div class="max-w-md mx-auto mb-6">
                                    <div class="input-group relative">
                                        <select id="role_id" name="role_id" x-model="selectedRoleId" class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg">
                                            <option value="">Personalizado / Ninguno</option>
                                            <template x-for="role in roles" :key="role.id">
                                                <option :value="role.id" x-text="role.name"></option>
                                            </template>
                                        </select>
                                        <label for="role_id" class="absolute left-0 top-2 text-gray-500 pointer-events-none transform -translate-y-6 scale-75">Seleccionar Rol (Plantilla)</label>
                                    </div>
                                </div>

                                <div class="max-w-md mx-auto mb-8">
                                    <div class="input-group relative">
                                        <input type="text" name="ff_role_name" id="ff_role_name" placeholder=" " class="block w-full px-0 py-2 bg-transparent border-0 border-b-2 border-gray-300 focus:ring-0 focus:border-[#ff9c00] transition-colors text-[#2c3856] font-semibold text-lg" value="{{ old('ff_role_name') }}">
                                        <label for="ff_role_name" class="absolute left-0 top-2 text-gray-500 pointer-events-none">Nombre del Rol F&F (Opcional)</label>
                                    </div>
                                </div>

                                <div class="space-y-6">
                                    @foreach(\App\Models\User::getGroupedPermissions() as $moduleName => $subGroups)
                                        <div x-data="{ open: false }" class="border border-gray-200 rounded-2xl bg-white overflow-hidden shadow-sm transition-all duration-300 hover:shadow-md">
                                            <button type="button" @click="open = !open" class="w-full flex items-center justify-between p-5 bg-gray-50 hover:bg-gray-100 transition-colors">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-2 h-8 rounded-full {{ $moduleName === 'WMS' ? 'bg-[#ff9c00]' : 'bg-[#2c3856]' }}"></div>
                                                    <h3 class="text-lg font-bold text-[#2c3856] uppercase tracking-wide">{{ $moduleName }}</h3>
                                                </div>
                                                <svg class="w-5 h-5 text-gray-400 transform transition-transform duration-300" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </button>
                                            
                                            <div x-show="open" x-collapse>
                                                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                                                    @foreach($subGroups as $groupName => $permissions)
                                                        <div class="bg-white border border-gray-100 rounded-xl p-0 overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                                            <div class="bg-gray-50/50 px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                                                                <h4 class="font-bold text-xs uppercase tracking-widest text-[#2c3856]">{{ $groupName }}</h4>
                                                                <span class="bg-blue-50 text-blue-600 text-[10px] font-bold px-2 py-0.5 rounded-full">{{ count($permissions) }} Permisos</span>
                                                            </div>
                                                            <div class="p-4 space-y-3">
                                                                @foreach($permissions as $key => $label)
                                                                    <label class="flex items-start cursor-pointer group">
                                                                        <div class="relative flex items-start mt-0.5">
                                                                            <input type="checkbox" name="ff_granular_permissions[]" value="{{ $key }}" class="peer sr-only" 
                                                                                x-model="selectedPermissions">
                                                                            <div class="w-4 h-4 border-2 border-gray-300 rounded bg-white peer-checked:bg-[#ff9c00] peer-checked:border-[#ff9c00] flex items-center justify-center transition-all">
                                                                                <svg class="w-3 h-3 text-white opacity-0 peer-checked:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                                                            </div>
                                                                        </div>
                                                                        <span class="ml-3 text-sm text-gray-600 group-hover:text-[#2c3856] transition-colors leading-tight select-none">{{ $label }}</span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>                          
                        </div>

                    </div>

                    <div class="p-6 border-t border-white/50 flex justify-between items-center bg-white/30 backdrop-blur-md">
                        <button type="button" x-show="step > 1" @click="prev()" class="px-6 py-3 rounded-xl text-[#2c3856] font-bold hover:bg-white/50 transition-colors flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Anterior
                        </button>
                        <div x-show="step === 1"></div>
                        
                        <button type="button" x-show="step < maxSteps" @click="next()" class="px-8 py-3 bg-[#2c3856] text-white rounded-xl font-bold shadow-[0_10px_20px_-5px_rgba(44,56,86,0.4)] hover:shadow-[0_15px_30px_-5px_rgba(44,56,86,0.5)] hover:-translate-y-1 transition-all flex items-center">
                            Siguiente
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>

                        <button type="submit" x-show="step === maxSteps" class="px-8 py-3 bg-gradient-to-r from-[#ff9c00] to-[#ffb347] text-white rounded-xl font-bold shadow-[0_10px_20px_-5px_rgba(255,156,0,0.4)] hover:shadow-[0_15px_30px_-5px_rgba(255,156,0,0.5)] hover:-translate-y-1 transition-all flex items-center animate-pulse">
                            Crear Usuario
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>

                </div>
            </form>
            
            <div class="py-4 text-center z-10">
                 <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-gray-400 hover:text-[#2c3856] uppercase tracking-widest transition-colors">Cancelar y Salir</a>
            </div>

        </div>
    </div>
</x-app-layout>