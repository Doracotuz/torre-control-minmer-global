<x-app-layout>
    <x-slot name="header"></x-slot>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        .font-montserrat { font-family: 'Montserrat', sans-serif; }
        
        .animate-entry {
            animation: slideUpFade 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
            transform: translateY(30px);
        }

        @keyframes slideUpFade {
            to { opacity: 1; transform: translateY(0); }
        }

        .modern-card {
            transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform-style: preserve-3d;
        }

        .modern-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(44, 56, 86, 0.25);
        }

        .card-img-container::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(44, 56, 86, 0.9) 0%, rgba(44, 56, 86, 0.4) 50%, rgba(44, 56, 86, 0.1) 100%);
            opacity: 0.8;
            transition: opacity 0.4s ease;
        }

        .modern-card:hover .card-img-container::after {
            opacity: 0.95;
            background: linear-gradient(to top, rgba(44, 56, 86, 1) 0%, rgba(44, 56, 86, 0.6) 100%);
        }

        .card-bg-img {
            transition: transform 1.2s ease;
        }

        .modern-card:hover .card-bg-img {
            transform: scale(1.15);
        }

        .glass-icon {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            transition: all 0.5s ease;
        }

        .modern-card:hover .glass-icon {
            background: #ff9c00;
            border-color: #ff9c00;
            color: white;
            transform: rotateY(180deg);
        }

        .glass-icon i {
            transition: transform 0.5s ease;
        }
        .modern-card:hover .glass-icon i {
            transform: rotateY(-180deg);
        }

        .action-arrow {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.4s ease 0.1s;
        }

        .modern-card:hover .action-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
        }
    </style>


    <div class="relative min-h-[calc(100vh-4rem)] p-6 lg:p-12 font-sans overflow-hidden">
        
        <div class="blob bg-blue-200/40 w-96 h-96 top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bg-orange-100/60 w-[500px] h-[500px] bottom-0 right-0 translate-x-1/3 translate-y-1/3"></div>

        <div class="relative z-10 max-w-8xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6 animate-entry" style="animation-delay: 0s;">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="h-[2px] w-10 bg-[#ff9c00]"></span>
                        <span class="text-[#ff9c00] text-xs font-bold tracking-[0.25em] uppercase">Panel de Control</span>
                    </div>
                    <h1 class="font-montserrat font-extrabold text-4xl md:text-5xl text-[#2c3856] leading-tight">
                        Administración
                    </h1>
                    <p class="text-slate-500 text-lg mt-2 font-light max-w-2xl">
                        Gestiona los catálogos maestros y configuraciones globales del sistema.
                    </p>
                </div>

                <a href="{{ route('ff.dashboard.index') }}" 
                   class="group flex items-center gap-3 px-6 py-3 bg-white border border-gray-100 rounded-full shadow-sm hover:shadow-md hover:border-[#ff9c00]/30 transition-all duration-300">
                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-[#ff9c00] transition-colors duration-300">
                        <i class="fas fa-arrow-left text-xs text-gray-500 group-hover:text-white transition-colors"></i>
                    </div>
                    <span class="font-semibold text-sm text-[#2c3856] tracking-wide">Volver al Dashboard</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                @if(Auth::user()->hasFfPermission('admin.clients'))
                <a href="{{ route('ff.admin.show', 'clients') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.1s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=600&q=80" 
                             class="card-bg-img w-full h-full object-cover" alt="Clientes">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Directorio</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Clientes</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Administra la base de datos de clientes, asignación de precios y perfiles comerciales.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endif

                @if(Auth::user()->hasFfPermission('admin.conditions'))
                <a href="{{ route('ff.admin.show', 'channels') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.2s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80" 
                             class="card-bg-img w-full h-full object-cover" alt="Canales">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-store-alt"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Estrategia</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Canales</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Configura puntos de venta físicos, digitales y plataformas de terceros.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endif

                @if(Auth::user()->hasFfPermission('admin.conditions'))
                <a href="{{ route('ff.admin.show', 'transport') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.3s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=600&q=80" 
                             class="card-bg-img w-full h-full object-cover" alt="Transporte">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-shipping-fast"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Logística</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Transporte</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Catálogo de transportistas, líneas fleteras y paqueterías autorizadas.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endif

                @if(Auth::user()->hasFfPermission('admin.conditions'))
                <a href="{{ route('ff.admin.show', 'payment') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.4s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=600&q=80" 
                             class="card-bg-img w-full h-full object-cover" alt="Pagos">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Finanzas</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Pagos</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Definición de plazos de crédito, métodos de pago y reglas comerciales.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endif

                @if(Auth::user()->hasFfPermission('admin.branches'))
                <a href="{{ route('ff.admin.show', 'warehouses') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.5s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80" 
                            class="card-bg-img w-full h-full object-cover" alt="Almacenes">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Infraestructura</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Almacenes</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Gestión de centros de distribución, bodegas y puntos de almacenamiento físico.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>  
                @endif
                
                @if(Auth::user()->hasFfPermission('admin.conditions'))
                <a href="{{ route('ff.admin.show', 'qualities') }}" class="modern-card group relative h-[500px] rounded-[2rem] overflow-hidden bg-white shadow-xl animate-entry" style="animation-delay: 0.6s;">
                    <div class="card-img-container absolute inset-0 z-0 h-full w-full">
                        <img src="https://iliiet.com/wp-content/uploads/2020/09/Inspector-de-Control-de-Calidad-Industrial-e1610242729851.jpg" 
                            class="card-bg-img w-full h-full object-cover" alt="Calidades">
                    </div>

                    <div class="relative z-10 h-full flex flex-col justify-between p-8 text-white">
                        <div class="flex justify-between items-start">
                            <div class="glass-icon w-16 h-16 rounded-2xl flex items-center justify-center text-3xl">
                                <i class="fas fa-medal"></i>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex items-center gap-2 mb-3 opacity-80">
                                <span class="h-px w-6 bg-[#ff9c00]"></span>
                                <span class="text-xs font-bold uppercase tracking-wider">Estándares</span>
                            </div>
                            <h3 class="font-montserrat font-bold text-3xl mb-3 leading-none group-hover:text-[#ff9c00] transition-colors">Calidades</h3>
                            <p class="text-gray-300 text-sm font-light leading-relaxed mb-6 opacity-0 group-hover:opacity-100 transition-opacity duration-500 absolute group-hover:relative">
                                Define los niveles de clasificación (A, B, C, Dañado) para el control de inventario.
                            </p>
                            
                            <div class="action-arrow flex items-center gap-2 text-[#ff9c00] font-bold text-sm uppercase tracking-wide">
                                <span>Gestionar</span>
                                <i class="fas fa-long-arrow-alt-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
                @endif                

            </div>

            <div class="mt-16 text-center animate-entry" style="animation-delay: 0.6s;">
                <div class="inline-flex items-center gap-4 opacity-40">
                    <span class="h-px w-12 bg-[#2c3856]"></span>
                    <span class="text-[10px] font-mono font-bold text-[#2c3856] uppercase tracking-[0.2em]">Control Tower - Minmer Global</span>
                    <span class="h-px w-12 bg-[#2c3856]"></span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>