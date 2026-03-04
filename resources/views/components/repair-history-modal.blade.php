    <!-- Repair History Modal -->
    <div x-show="repairHistoryModal.open" x-cloak class="fixed inset-0 z-[9999] flex items-center justify-center p-4" @keydown.escape.window="repairHistoryModal.open = false">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="repairHistoryModal.open = false"></div>
        <div class="relative bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 w-full max-w-3xl max-h-[80vh] flex flex-col">
            <!-- Header -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100 flex items-center gap-2">
                        🔧 Repair History
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                        Lot: <span class="font-mono font-medium text-indigo-600 dark:text-indigo-400" x-text="repairHistoryModal.lotNumber"></span>
                    </p>
                </div>
                <button @click="repairHistoryModal.open = false" class="p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <!-- Body -->
            <div class="flex-1 overflow-auto p-5 custom-scrollbar">
                <!-- Loading -->
                <div x-show="repairHistoryModal.loading" class="flex items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    <span class="ml-3 text-slate-500 dark:text-slate-400">Fetching from Odoo...</span>
                </div>
                <!-- Error -->
                <div x-show="repairHistoryModal.error" class="text-center py-8">
                    <div class="text-red-500 dark:text-red-400 text-sm" x-text="repairHistoryModal.error"></div>
                </div>
                <!-- Empty -->
                <div x-show="!repairHistoryModal.loading && !repairHistoryModal.error && repairHistoryModal.data.length === 0" class="text-center py-8">
                    <div class="text-slate-400 dark:text-slate-500">No repair history found for this vehicle.</div>
                </div>
                <!-- Data Table -->
                <div x-show="!repairHistoryModal.loading && !repairHistoryModal.error && repairHistoryModal.data.length > 0">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-800 text-xs uppercase text-slate-500 dark:text-slate-400">
                            <tr>
                                <th class="p-3 rounded-tl-lg">Order</th>
                                <th class="p-3">Status</th>
                                <th class="p-3">Date</th>
                                <th class="p-3">Type</th>
                                <th class="p-3">Vendor</th>
                                <th class="p-3">KM</th>
                                <th class="p-3">Est. End</th>
                                <th class="p-3 rounded-tr-lg">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <template x-for="(r, i) in repairHistoryModal.data" :key="i">
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30">
                                    <td class="p-3 font-mono font-medium text-orange-600 dark:text-orange-400 text-xs" x-text="r.name"></td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold" :class="{
                                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400': r.state === 'under_repair',
                                            'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400': r.state === 'done',
                                            'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300': r.state !== 'under_repair' && r.state !== 'done'
                                        }" x-text="r.state"></span>
                                    </td>
                                    <td class="p-3 text-xs text-slate-500 dark:text-slate-400" x-text="r.schedule_date || '-'"></td>
                                    <td class="p-3">
                                        <span x-show="r.service_type" class="px-2 py-0.5 rounded text-[10px] font-bold" :class="r.service_type === 'accident' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'" x-text="r.service_type"></span>
                                        <span x-show="!r.service_type" class="text-slate-300">-</span>
                                    </td>
                                    <td class="p-3 text-xs text-slate-600 dark:text-slate-400" x-text="r.vendor || '-'"></td>
                                    <td class="p-3 text-xs text-slate-500 dark:text-slate-400">
                                        <span x-show="r.km_pickup" x-text="Number(r.km_pickup).toLocaleString()"></span>
                                        <span x-show="!r.km_pickup" class="text-slate-300">-</span>
                                    </td>
                                    <td class="p-3 text-xs text-slate-500 dark:text-slate-400" x-text="r.estimation_end_date || '-'"></td>
                                    <td class="p-3 text-xs text-slate-500 dark:text-slate-400" x-text="r.repair_end_datetime || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
