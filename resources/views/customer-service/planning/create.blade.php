<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Registro de Planificación Manual') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <form action="{{ route('customer-service.planning.store') }}" method="POST">
                    @csrf
                    @if ($errors->any())
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p><b>Error:</b> {{ $errors->first() }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div><label class="block text-sm font-medium text-gray-700">Razón Social / Contacto</label><input type="text" name="razon_social" value="{{ old('razon_social') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700">Dirección</label><input type="text" name="direccion" value="{{ old('direccion') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        <div><label class="block text-sm font-medium text-gray-700">SO (Opcional)</label><input type="text" name="so_number" value="{{ old('so_number') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Factura</label><input type="text" name="factura" value="{{ old('factura') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required></div>
                        
                        <div>
                            <label for="origen" class="block text-sm font-medium text-gray-700">Origen</label>
                            <select name="origen" id="origen" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Seleccione un origen</option>
                                <option value="MEX" {{ old('origen') == 'MEX' ? 'selected' : '' }}>MEX</option>
                                <option value="CUN" {{ old('origen') == 'CUN' ? 'selected' : '' }}>CUN</option>
                                <option value="MTY" {{ old('origen') == 'MTY' ? 'selected' : '' }}>MTY</option>
                                <option value="GDL" {{ old('origen') == 'GDL' ? 'selected' : '' }}>GDL</option>
                                <option value="SJD" {{ old('origen') == 'SJD' ? 'selected' : '' }}>SJD</option>
                                <option value="MIN" {{ old('origen') == 'MIN' ? 'selected' : '' }}>MIN</option>
                                <option value="SOTANO 5" {{ old('origen') == 'SOTANO 5' ? 'selected' : '' }}>SOTANO 5</option>
                            </select>
                        </div>

                        <div>
                            <label for="destino" class="block text-sm font-medium text-gray-700">Destino</label>
                            <select name="destino" id="destino" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" required>
                                <option value="">Selecciona una localidad</option>
                                <option value="AGS" {{ old('destino') == 'AGS' ? 'selected' : '' }}>AGS</option>
                                <option value="BCN" {{ old('destino') == 'BCN' ? 'selected' : '' }}>BCN</option>
                                <option value="CDMX" {{ old('destino') == 'CDMX' ? 'selected' : '' }}>CDMX</option>
                                <option value="CUU" {{ old('destino') == 'CUU' ? 'selected' : '' }}>CUU</option>
                                <option value="COA" {{ old('destino') == 'COA' ? 'selected' : '' }}>COA</option>
                                <option value="CUL" {{ old('destino') == 'CUL' ? 'selected' : '' }}>CUL</option>
                                <option value="CUN" {{ old('destino') == 'CUN' ? 'selected' : '' }}>CUN</option>
                                <option value="CVJ" {{ old('destino') == 'CVJ' ? 'selected' : '' }}>CVJ</option>
                                <option value="GDL" {{ old('destino') == 'GDL' ? 'selected' : '' }}>GDL</option>
                                <option value="GRO" {{ old('destino') == 'GRO' ? 'selected' : '' }}>GRO</option>
                                <option value="GTO" {{ old('destino') == 'GTO' ? 'selected' : '' }}>GTO</option>
                                <option value="HGO" {{ old('destino') == 'HGO' ? 'selected' : '' }}>HGO</option>
                                <option value="MEX" {{ old('destino') == 'MEX' ? 'selected' : '' }}>MEX</option>
                                <option value="MIC" {{ old('destino') == 'MIC' ? 'selected' : '' }}>MIC</option>
                                <option value="MID" {{ old('destino') == 'MID' ? 'selected' : '' }}>MID</option>
                                <option value="MLM" {{ old('destino') == 'MLM' ? 'selected' : '' }}>MLM</option>
                                <option value="MTY" {{ old('destino') == 'MTY' ? 'selected' : '' }}>MTY</option>
                                <option value="MZN" {{ old('destino') == 'MZN' ? 'selected' : '' }}>MZN</option>
                                <option value="NAY" {{ old('destino') == 'NAY' ? 'selected' : '' }}>NAY</option>
                                <option value="DGO" {{ old('destino') == 'DGO' ? 'selected' : '' }}>DGO</option>
                                <option value="ZAC" {{ old('destino') == 'ZAC' ? 'selected' : '' }}>ZAC</option>
                                <option value="OAX" {{ old('destino') == 'OAX' ? 'selected' : '' }}>OAX</option>
                                <option value="PUE" {{ old('destino') == 'PUE' ? 'selected' : '' }}>PUE</option>
                                <option value="QRO" {{ old('destino') == 'QRO' ? 'selected' : '' }}>QRO</option>
                                <option value="SIN" {{ old('destino') == 'SIN' ? 'selected' : '' }}>SIN</option>
                                <option value="SJD" {{ old('destino') == 'SJD' ? 'selected' : '' }}>SJD</option>
                                <option value="SLP" {{ old('destino') == 'SLP' ? 'selected' : '' }}>SLP</option>
                                <option value="SMA" {{ old('destino') == 'SMA' ? 'selected' : '' }}>SMA</option>
                                <option value="SON" {{ old('destino') == 'SON' ? 'selected' : '' }}>SON</option>
                                <option value="TAB" {{ old('destino') == 'TAB' ? 'selected' : '' }}>TAB</option>
                                <option value="TGZ" {{ old('destino') == 'TGZ' ? 'selected' : '' }}>TGZ</option>
                                <option value="TIJ" {{ old('destino') == 'TIJ' ? 'selected' : '' }}>TIJ</option>
                                <option value="TLX" {{ old('destino') == 'TLX' ? 'selected' : '' }}>TLX</option>
                                <option value="VER" {{ old('destino') == 'VER' ? 'selected' : '' }}>VER</option>
                                <option value="YUC" {{ old('destino') == 'YUC' ? 'selected' : '' }}>YUC</option>
                                <option value="ZAM" {{ old('destino') == 'ZAM' ? 'selected' : '' }}>ZAM</option>
                            </select>
                        </div>

                        <div><label class="block text-sm font-medium text-gray-700">Fecha Entrega</label><input type="date" name="fecha_entrega" value="{{ old('fecha_entrega') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Hora Cita</label><input type="text" name="hora_cita" value="{{ old('hora_cita') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Piezas</label><input type="number" name="pzs" value="{{ old('pzs') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div><label class="block text-sm font-medium text-gray-700">Cajas</label><input type="number" name="cajas" value="{{ old('cajas') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subtotal</label>
                            <input type="number" step="0.01" name="subtotal" value="{{ old('subtotal') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Maniobras</label>
                            <input type="number" name="maniobras" value="{{ old('maniobras', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                            <textarea name="observaciones" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ old('observaciones') }}</textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label for="canal" class="block text-sm font-medium text-gray-700">Canal</label>
                            <select name="canal" id="canal" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleccione un canal</option>
                                <option value="Corporate" {{ old('canal') == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                                <option value="Especialista" {{ old('canal') == 'Especialista' ? 'selected' : '' }}>Especialista</option>
                                <option value="Moderno" {{ old('canal') == 'Moderno' ? 'selected' : '' }}>Moderno</option>
                                <option value="On" {{ old('canal') == 'On' ? 'selected' : '' }}>On</option>
                                <option value="On trade" {{ old('canal') == 'On trade' ? 'selected' : '' }}>On trade</option>
                                <option value="Private" {{ old('canal') == 'Private' ? 'selected' : '' }}>Private</option>
                                <option value="POSM" {{ old('canal') == 'POSM' ? 'selected' : '' }}>POSM</option>
                                <option value="Interno" {{ old('canal') == 'Interno' ? 'selected' : '' }}>Interno</option>
                            </select>
                        </div>                                                
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('customer-service.planning.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>