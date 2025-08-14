<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Módulo de Customer Service') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Mosaico 1: Gestión de Productos -->
                @if(Auth::user()->is_area_admin)
                <a href="{{ route('customer-service.products.index') }}" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <!-- Imagen de Fondo -->
                    <img src="https://theluxonomist.20minutos.es/wp-content/uploads/2022/11/Moet-Chandon-LVMH-A.jpg" alt="Gestión de Productos" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <!-- Capa de Color Desvanecido -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <!-- Contenido de Texto -->
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Gestión de Productos</h3>
                        <p class="mt-1 text-gray-300">Administra el catálogo de SKUs, marcas y promocionales.</p>
                    </div>
                </a>
                @endif

                <!-- Mosaico 2: Gestión de Clientes -->
                <a href="{{ route('customer-service.customers.index') }}" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <img src="https://visionglobal.com.mx/wp-content/uploads/2020/02/belvedere-vodka-comparte-cocteles-para-celebrar-el-14-de-febrero1.jpg" alt="Gestión de Clientes" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Gestión de Clientes</h3>
                        <p class="mt-1 text-gray-300">Administra el catálogo de clientes y sus canales.</p>
                    </div>
                </a>

                <!-- Mosaico 3 (Almacénes) -->
                <a href="{{ route('customer-service.warehouses.index') }}" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <img src="https://topshelfwineandspirits.com/cdn/shop/products/VeuveClicquotRichCollection-Beautyshot.jpg?v=1628025396&width=3789" alt="Gestión de Almacenes" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Gestión de Almacenes</h3>
                        <p class="mt-1 text-gray-300">Administra el catálogo de almacenes.</p>
                    </div>
                </a>

                <!-- Mosaico 4: Gestión de Pedidos -->
                <a href="{{ route('customer-service.orders.index') }}" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <img src="https://media.glamour.mx/photos/63238e7b11a242c6ed8a73ab/master/w_1600%2Cc_limit/Whispering-Angel-cover.jpg" alt="Gestión de Pedidos" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Gestión de Pedidos</h3>
                        <p class="mt-1 text-gray-300">Carga y procesa las órdenes de compra.</p>
                    </div>
                </a>

                <!-- Mosaico 5 (Placeholder) -->
                <a href="#" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <img src="https://placehold.co/600x600/9B59B6/FFFFFF?text=Análisis" alt="Mosaico 5" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Módulo 5 (Próximamente)</h3>
                        <p class="mt-1 text-gray-300">Descripción del quinto módulo.</p>
                    </div>
                </a>

                <!-- Mosaico 6 (Placeholder) -->
                <a href="#" class="group relative block h-64 rounded-xl overflow-hidden shadow-lg">
                    <img src="https://placehold.co/600x600/1ABC9C/FFFFFF?text=Config" alt="Mosaico 6" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-transparent"></div>
                    <div class="relative h-full flex flex-col justify-end p-6 text-white">
                        <h3 class="text-2xl font-bold tracking-tight">Módulo 6 (Próximamente)</h3>
                        <p class="mt-1 text-gray-300">Descripción del sexto módulo.</p>
                    </div>
                </a>

            </div>
        </div>
    </div>
</x-app-layout>
