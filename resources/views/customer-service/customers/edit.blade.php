<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editando Cliente: <span class="text-blue-600">{{ $customer->name }}</span></h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')
                    @if ($errors->any())<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert"><p class="font-bold">Hay errores:</p><ul class="mt-2 list-disc list-inside text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label for="client_id" class="block text-sm font-medium text-gray-700">ID Cliente</label><input type="text" name="client_id" id="client_id" value="{{ old('client_id', $customer->client_id) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label for="name" class="block text-sm font-medium text-gray-700">Nombre del Cliente</label><input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div class="md:col-span-2"><label for="channel" class="block text-sm font-medium text-gray-700">Canal</label><select name="channel" id="channel" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><option value="">Selecciona un canal</option>@foreach($channels as $channel)<option value="{{ $channel }}" {{ old('channel', $customer->channel) == $channel ? 'selected' : '' }}>{{ $channel }}</option>@endforeach</select></div>
                    </div>
                    <div class="flex justify-end gap-4 mt-8"><a href="{{ route('customer-service.customers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancelar</a><button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Actualizar Cliente</button></div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
