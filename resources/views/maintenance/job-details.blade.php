<div class="text-left">
    <div class="mb-5 pb-5 border-b border-slate-100 dark:border-slate-800">
        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100 flex items-center justify-between mb-2">
            <span>{{ $job->nomor_job }}</span>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ in_array($job->state, ['done', '2binvoiced']) || !$job->state ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                {{ ucfirst(str_replace('_', ' ', $job->state ?? 'done')) }}
            </span>
        </h3>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Vehicle</p>
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $job->mobil->nomor_polisi ?? '-' }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $job->mobil->model ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Supplier</p>
                <p class="text-sm font-medium text-slate-800 dark:text-slate-200">{{ $job->supplier->nama_supplier ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-100 dark:bg-slate-800">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600 dark:text-slate-300">Description</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Qty</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold text-slate-600 dark:text-slate-300">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($job->dtransaksi as $detail)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
                        <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ $detail->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ $detail->jumlah ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right text-slate-600 dark:text-slate-400 whitespace-nowrap">{{ ($detail->harga ?? 0) != 0 ? 'Rp ' . number_format($detail->harga, 0, ',', '.') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-slate-500 dark:text-slate-400">No detail rows found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-slate-100 dark:bg-slate-800 rounded-xl p-4 flex justify-between items-end">
        <div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Subtotal (Parts/Service)</p>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Rp {{ number_format($job->harga_total ?? 0, 0, ',', '.') }}</p>
            
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2 mb-1">Tax Element (PPN)</p>
            <p class="text-sm font-medium text-slate-700 dark:text-slate-300">Rp {{ number_format($job->harga_pajak ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-1 font-bold">Grand Total</p>
            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400">Rp {{ number_format( ($job->harga_total ?? 0) + ($job->harga_pajak ?? 0) , 0, ',', '.') }}</p>
        </div>
    </div>
</div>
