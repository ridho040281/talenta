@extends('layouts.admin')

@section('title', 'Verifikasi Tagihan Kolektif & Pembayaran')
@section('page_title', 'Verifikasi Tagihan Kolektif')

@section('content')
<div class="space-y-8">

    <!-- Top Summary Banner (AIStarterKit Design) -->
    <div class="ai-card rounded-3xl p-6 sm:p-8 border border-white/[0.08] shadow-xl space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/[0.08] pb-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="p-1 rounded-lg bg-[#7A5AF8]/20 text-[#A594FD] border border-[#7A5AF8]/30">
                        <i data-lucide="receipt" class="w-4 h-4"></i>
                    </span>
                    <h2 class="text-2xl font-black text-white ai-gradient-text">Tagihan & Pembayaran Kolektif</h2>
                </div>
                <p class="text-xs text-slate-400 mt-1">Verifikasi 1 bukti transfer untuk melunaskan seluruh cabang lomba kontingen pendaftar</p>
            </div>

            <!-- Filter Status Tabs (AI Pill Nav) -->
            <div class="flex items-center gap-1.5 bg-[#0C111D] border border-white/[0.08] p-1.5 rounded-2xl overflow-x-auto no-scrollbar">
                <a href="{{ route('admin.invoices.index', ['status' => 'all']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'all' ? 'bg-gradient-to-r from-[#7A5AF8] to-[#4E6EFF] text-white shadow-md shadow-[#7A5AF8]/30' : 'text-slate-400 hover:text-white' }}">
                    Semua ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.invoices.index', ['status' => 'pending']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 shadow-md font-black' : 'text-slate-400 hover:text-white' }}">
                    Perlu Verifikasi ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('admin.invoices.index', ['status' => 'verified']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'verified' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                    Lunas ({{ $stats['verified'] }})
                </a>
                <a href="{{ route('admin.invoices.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                    Ditolak ({{ $stats['rejected'] }})
                </a>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-[#0C111D] border border-white/[0.08] space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan</span>
                <p class="text-2xl font-black text-white">{{ $stats['total'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-amber-500/15 border border-amber-500/30 space-y-1">
                <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">Menunggu Verifikasi</span>
                <p class="text-2xl font-black text-amber-400">{{ $stats['pending'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 space-y-1">
                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Lunas Disetujui</span>
                <p class="text-2xl font-black text-emerald-400">{{ $stats['verified'] }}</p>
            </div>
            <div class="p-4 rounded-2xl bg-gradient-to-tr from-[#7A5AF8] to-[#4E6EFF] text-white space-y-1 shadow-lg shadow-[#7A5AF8]/25">
                <span class="text-[10px] font-bold text-indigo-100 uppercase tracking-wider">Total Dana Masuk</span>
                <p class="text-xl sm:text-2xl font-black">Rp {{ number_format($stats['total_nominal'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="ai-card rounded-3xl border border-white/[0.08] shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="text-xs font-bold uppercase tracking-wider bg-[#0C111D]/90 text-slate-400 border-b border-white/[0.08]">
                    <tr>
                        <th class="py-4 px-6">No. Invoice</th>
                        <th class="py-4 px-6">Asal Sekolah & Pendaftar</th>
                        <th class="py-4 px-6 text-center">Peserta</th>
                        <th class="py-4 px-6">Total Nominal</th>
                        <th class="py-4 px-6 text-center">Bukti Transfer</th>
                        <th class="py-4 px-6 text-center">Status</th>
                        <th class="py-4 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.04] font-medium text-xs">
                    @forelse($invoices as $inv)
                        <tr class="hover:bg-white/[0.025] transition">
                            <td class="py-4 px-6 font-mono font-bold text-[#84D0FF]">
                                <span class="block">{{ $inv->invoice_number }}</span>
                                <span class="block text-[11px] text-slate-400 font-normal">{{ $inv->created_at->format('d M Y, H:i') }}</span>
                            </td>
                            <td class="py-4 px-6">
                                <span class="block text-sm font-bold text-white">{{ $inv->user->institution_name ?? $inv->user->name }}</span>
                                <span class="block text-[11px] text-slate-400">Official: {{ $inv->user->name }} • {{ $inv->user->phone ?? '-' }}</span>
                            </td>
                            <td class="py-4 px-6 text-center font-bold text-slate-200">
                                <span class="px-2.5 py-1 rounded-xl bg-white/[0.05] border border-white/[0.08] text-slate-200">
                                    {{ $inv->registrations_count }} Peserta
                                </span>
                            </td>
                            <td class="py-4 px-6 font-black text-emerald-400 text-sm whitespace-nowrap">
                                {{ $inv->formatted_final_amount }}
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($inv->payment_proof)
                                    <a href="{{ asset('storage/' . $inv->payment_proof) }}" target="_blank" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#4E6EFF]/15 text-[#84D0FF] border border-[#4E6EFF]/30 font-bold hover:bg-[#4E6EFF]/25 transition text-[11px]">
                                        <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                        <span>Lihat Struk</span>
                                    </a>
                                @else
                                    <span class="text-slate-500 italic text-[11px]">Belum Upload</span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if($inv->status === 'verified')
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                        ✔ LUNAS
                                    </span>
                                @elseif($inv->status === 'rejected')
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                        ✕ DITOLAK
                                    </span>
                                @elseif($inv->payment_proof)
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                                        ⏳ PERLU CEK
                                    </span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black bg-white/[0.05] text-slate-400 border border-white/[0.08]">
                                        BELUM BAYAR
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="{{ route('admin.invoices.show', $inv->id) }}" class="gradient-btn inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-white font-bold text-xs shadow-md shadow-[#7A5AF8]/20 transition">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    <span>Periksa & Verifikasi</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-500">
                                Tidak ada data tagihan invoice pendaftaran.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="p-4 border-t border-white/[0.08]">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
