<div class="bg-white rounded-2xl shadow-xl border border-gray-100 animate-pulse w-full flex flex-col">
    <div class="overflow-x-auto w-full rounded-2xl">
        <table class="w-full min-w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100 text-left">
                    <th class="px-6 py-4"><div class="h-3 w-20 bg-gray-200 rounded"></div></th>
                    <th class="px-6 py-4"><div class="h-3 w-32 bg-gray-200 rounded"></div></th>
                    <th class="px-6 py-4"><div class="h-3 w-16 bg-gray-200 rounded"></div></th>
                    <th class="px-6 py-4"><div class="h-3 w-24 bg-gray-200 rounded"></div></th>
                    <th class="px-6 py-4 text-right"><div class="h-3 w-12 bg-gray-200 rounded ml-auto"></div></th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 5; $i++)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-200"></div>
                                <div class="ml-4 space-y-2">
                                    <div class="h-3 w-24 bg-gray-200 rounded"></div>
                                    <div class="h-2 w-16 bg-gray-200 rounded"></div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2">
                                <div class="h-3 w-28 bg-gray-200 rounded"></div>
                                <div class="h-4 w-20 bg-gray-200 rounded-full"></div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="h-5 w-24 bg-gray-200 rounded-full"></div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="space-y-2">
                                <div class="h-3 w-28 bg-gray-200 rounded"></div>
                                <div class="h-2 w-20 bg-gray-200 rounded"></div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <div class="h-8 w-8 bg-gray-200 rounded-lg"></div>
                                <div class="h-8 w-8 bg-gray-200 rounded-lg"></div>
                                <div class="h-8 w-8 bg-gray-200 rounded-full"></div>
                            </div>
                        </td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>