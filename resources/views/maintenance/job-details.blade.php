<div class="text-left" id="job-detail-content">
    <div class="mb-5 pb-5 border-b" style="border-color: inherit;">
        <h3 class="text-xl font-bold flex items-center justify-between mb-2" style="color: inherit;">
            <span>{{ $job->nomor_job }}</span>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full {{ in_array($job->state, ['done', '2binvoiced', 'close']) || !$job->state ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                {{ ucfirst(str_replace('_', ' ', $job->state ?? 'done')) }}
            </span>
        </h3>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <div>
                <p class="text-xs uppercase tracking-wider mb-1" style="color: #94a3b8;">Vehicle</p>
                <p class="text-sm font-medium" style="color: inherit;">{{ $job->mobil->nomor_polisi ?? '-' }}</p>
                <p class="text-xs" style="color: #94a3b8;">{{ $job->mobil->model ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider mb-1" style="color: #94a3b8;">Supplier</p>
                <p class="text-sm font-medium" style="color: inherit;">{{ $job->supplier->nama_supplier ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="rounded-xl border overflow-hidden mb-5" style="border-color: rgba(148,163,184,0.2);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background-color: rgba(148,163,184,0.15);">
                        <th class="px-4 py-2.5 text-left text-xs font-semibold" style="color: #94a3b8;">Description</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold" style="color: #94a3b8;">Qty</th>
                        <th class="px-4 py-2.5 text-right text-xs font-semibold" style="color: #94a3b8;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($job->dtransaksi as $detail)
                    <tr style="border-top: 1px solid rgba(148,163,184,0.15);">
                        <td class="px-4 py-2.5" style="color: inherit;">{{ $detail->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap" style="color: #94a3b8;">{{ $detail->jumlah ?? '-' }}</td>
                        <td class="px-4 py-2.5 text-right whitespace-nowrap" style="color: #94a3b8;">{{ ($detail->harga ?? 0) != 0 ? 'Rp ' . number_format($detail->harga, 0, ',', '.') : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-6 text-center text-sm" style="color: #94a3b8;">No detail rows found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl p-4 flex justify-between items-end" style="background-color: rgba(148,163,184,0.1); border: 1px solid rgba(148,163,184,0.2);">
        <div>
            <p class="text-xs mb-1" style="color: #94a3b8;">Total (incl. tax)</p>
            <p class="text-sm font-medium" style="color: inherit;">Rp {{ number_format($job->harga_total ?? 0, 0, ',', '.') }}</p>

            <p class="text-xs mt-2 mb-1" style="color: #94a3b8;">Tax Element (PPN)</p>
            <p class="text-sm font-medium" style="color: inherit;">Rp {{ number_format($job->harga_pajak ?? 0, 0, ',', '.') }}</p>
        </div>
        <div class="text-right">
            <p class="text-xs uppercase tracking-widest mb-1 font-bold" style="color: #94a3b8;">Grand Total</p>
            <p class="text-2xl font-black" style="color: #6366f1;">Rp {{ number_format($job->harga_total ?? 0, 0, ',', '.') }}</p>
        </div>
    </div>
</div>
