@extends('layouts.admin')

@section('title', 'Pratinjau & Validasi Data Kolektif')
@section('page_title', 'Pratinjau Data Pendaftaran Kolektif')

@section('content')
<div class="space-y-8">

    <!-- Top Summary Banner (Dark Glass) -->
    <div class="glass-card rounded-3xl p-6 sm:p-8 border border-slate-800 shadow-2xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-4">
            <div>
                <span class="px-3 py-1 text-xs font-bold rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/30 uppercase tracking-wider">
                    Langkah 2: Pratinjau & Validasi Data
                </span>
                <h2 class="text-xl sm:text-2xl font-black text-white mt-2 font-display">Pengecekan Data & Kalkulasi Biaya</h2>
                <p class="text-xs text-slate-400">Sistem telah memvalidasi seluruh baris data peserta dan mengecek sisa kuota cabang lomba secara otomatis.</p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('peserta.collective.wizard') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 text-slate-300 font-bold text-xs hover:bg-slate-700 border border-slate-700 transition">
                    Upload Ulang File
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800 space-y-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Peserta Terbaca</span>
                <p class="text-2xl font-black text-white font-display">{{ count($parsedRows) }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-1">
                <span class="text-[11px] font-bold text-emerald-400 uppercase tracking-wider">Data Valid & Siap</span>
                <p class="text-2xl font-black text-emerald-300 font-display">{{ $validRowCount }}</p>
            </div>

            <div class="p-4 rounded-2xl {{ $errorRowCount > 0 ? 'bg-rose-500/10 border border-rose-500/30' : 'bg-slate-900/80 border border-slate-800' }} space-y-1">
                <span class="text-[11px] font-bold {{ $errorRowCount > 0 ? 'text-rose-400' : 'text-slate-400' }} uppercase tracking-wider">Data Bermasalah</span>
                <p class="text-2xl font-black {{ $errorRowCount > 0 ? 'text-rose-300' : 'text-slate-300' }} font-display">{{ $errorRowCount }}</p>
            </div>

            <div class="p-4 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-700 text-white space-y-1 shadow-lg shadow-emerald-600/20">
                <span class="text-[10px] font-bold text-emerald-200 uppercase tracking-wider">Total Tagihan Invoice</span>
                <p class="text-xl sm:text-2xl font-black font-display font-mono">Rp {{ number_format($finalAmount, 0, ',', '.') }}</p>
                <p class="text-[10px] text-emerald-100 font-medium">Termasuk kode unik: +{{ $uniqueCode }}</p>
            </div>
        </div>

        @if($errorRowCount > 0)
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 flex items-start gap-3 text-rose-300 text-xs">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-400 shrink-0 mt-0.5"></i>
                <div>
                    <span class="font-bold block text-rose-200">Perhatian: Ditemukan {{ $errorRowCount }} baris data bermasalah!</span>
                    <span class="text-slate-300">Baris yang bertanda merah tidak akan didaftarkan ke sistem. Anda dapat memperbaiki file Excel dan mengunggah ulang, atau melanjutkan hanya untuk {{ $validRowCount }} data yang valid.</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Data Table Preview (Dark Glass) -->
    <div class="glass-card rounded-3xl border border-slate-800 shadow-2xl overflow-hidden space-y-4 p-6 sm:p-8">
        <h3 class="text-base font-black text-white font-display">Rincian Baris Data Excel Peserta</h3>

        <div class="overflow-x-auto rounded-2xl border border-slate-800 shadow-xl">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="text-[10px] font-bold uppercase tracking-wider bg-slate-950/80 text-slate-400 border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4 text-center">Baris</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Nama Lengkap Peserta</th>
                        <th class="py-3 px-4">NISN / JK</th>
                        <th class="py-3 px-4">Cabang Lomba</th>
                        <th class="py-3 px-4">Nama Tim / Regu</th>
                        <th class="py-3 px-4">Biaya Lomba</th>
                        <th class="py-3 px-4">Asal Sekolah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800 font-medium">
                    @foreach($parsedRows as $row)
                        <tr class="hover:bg-slate-800/40 transition {{ !$row['is_valid'] ? 'bg-rose-500/10' : '' }}">
                            <td class="py-3 px-4 text-center font-mono font-bold text-slate-400">
                                #{{ $row['row_number'] }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($row['is_valid'])
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>Valid</span>
                                    </span>
                                @else
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                            <span>Error</span>
                                        </span>
                                        @foreach($row['errors'] as $err)
                                            <p class="text-[10px] text-rose-400 font-semibold leading-tight">{{ $err }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-white text-sm">
                                {{ $row['name'] ?: '-' }}
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px]">
                                <div class="text-white">{{ $row['nisn'] ?: '-' }}</div>
                                <span class="text-slate-400 font-sans font-bold">({{ $row['gender'] }})</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-mono font-bold text-emerald-400 bg-slate-900 px-1.5 py-0.5 rounded text-[10px] border border-slate-700">
                                        {{ $row['competition_code'] }}
                                    </span>
                                    <span class="font-bold text-white">{{ $row['competition_name'] }}</span>
                                </div>
                                @if(!empty($row['sub_category']))
                                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        🏸 {{ $row['sub_category'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                {{ $row['team_name'] ?: '-' }}
                            </td>
                            <td class="py-3 px-4 font-black text-emerald-400 whitespace-nowrap font-mono">
                                Rp {{ number_format($row['fee'], 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 text-slate-300">
                                {{ $row['institution_name'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($validRowCount > 0)
            <form action="{{ route('peserta.collective.confirm') }}" method="POST" enctype="multipart/form-data" class="pt-6 border-t border-slate-800 space-y-6">
                @csrf
                <input type="hidden" name="payload" value="{{ json_encode($parsedRows) }}">

                <!-- Bank Info & Amount Reminder Box (Dark) -->
                <div class="p-6 rounded-3xl bg-slate-950 border border-slate-800 text-white space-y-4 shadow-xl">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold border border-emerald-500/30">
                                <i data-lucide="credit-card" class="w-4 h-4"></i>
                            </div>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-400">Rekening Tujuan Pembayaran Resmi</span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Total yang Harus Ditransfer</span>
                            <span class="text-lg font-black text-amber-400 font-mono">Rp {{ number_format($finalAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 space-y-0.5">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Nama Bank / Rekening</span>
                            <span class="font-bold text-white text-sm">{{ $bankInfo['bank_name'] ?? 'BSI' }}</span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 space-y-0.5">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Nomor Rekening</span>
                            <span class="font-bold text-emerald-400 font-mono text-sm select-all">{{ $bankInfo['bank_account_number'] ?? '7123456789' }}</span>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-900 border border-slate-800 space-y-0.5">
                            <span class="text-[10px] text-slate-400 block uppercase font-bold">Atas Nama Pemilik</span>
                            <span class="font-bold text-white text-sm">{{ $bankInfo['bank_account_holder'] ?? 'Panitia TALENTA' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Required Upload Slip Box (Dark) -->
                <div class="p-6 rounded-3xl border-2 border-dashed border-emerald-500/50 hover:border-emerald-400 bg-emerald-950/20 transition space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shadow-sm border border-emerald-500/30">
                                <i data-lucide="receipt" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <label for="payment_proof" class="block text-sm font-black text-white">
                                    Unggah Bukti Transfer / Slip Pembayaran Kolektif <span class="text-rose-400">* (Wajib)</span>
                                </label>
                                <p class="text-xs text-slate-400">Struk ATM, M-Banking, atau kwitansi pembayaran (JPG, PNG, PDF maks 5 MB)</p>
                            </div>
                        </div>

                        <span class="self-start sm:self-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-500 text-white shadow-sm">
                            Wajib Terlampir
                        </span>
                    </div>

                    <input type="file" id="payment_proof" name="payment_proof" required accept="image/*,application/pdf" class="block w-full text-xs text-slate-300 file:mr-4 file:py-3 file:px-5 file:rounded-2xl file:border-0 file:text-xs file:font-black file:bg-emerald-600 file:text-slate-950 hover:file:bg-emerald-500 cursor-pointer bg-slate-900 p-2 rounded-2xl border border-slate-700">

                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-300 flex items-start gap-2.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400 shrink-0 mt-0.5"></i>
                        <span><strong>Satu Kali Kirim:</strong> Seluruh {{ $validRowCount }} data siswa dan bukti pembayaran dikirim secara bersamaan. Tombol kirim tidak dapat diproses jika slip bukti transfer belum dipilih.</span>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-2">
                    <a href="{{ route('peserta.collective.wizard') }}" class="px-5 py-3 rounded-2xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 border border-slate-700 transition text-center">
                        Batal / Upload Ulang File
                    </a>

                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-gradient-to-r from-emerald-400 via-teal-400 to-cyan-400 hover:from-emerald-300 hover:to-cyan-300 text-slate-950 font-black text-xs uppercase tracking-wider shadow-xl shadow-emerald-500/25 hover:scale-[1.02] active:scale-[0.98] transition duration-200 cursor-pointer">
                        <i data-lucide="send" class="w-4 h-4"></i>
                        <span>Kirim Pendaftaran Kolektif & Bukti Pembayaran ({{ $validRowCount }} Peserta)</span>
                    </button>
                </div>
            </form>
        @else
            <div class="pt-6 border-t border-slate-800 flex items-center justify-between">
                <p class="text-xs text-rose-400 font-bold">Semua data pada Excel memiliki kesalahan. Silakan perbaiki file Excel Anda.</p>
                <a href="{{ route('peserta.collective.wizard') }}" class="px-5 py-2.5 rounded-xl bg-slate-800 text-slate-300 text-xs font-bold hover:bg-slate-700 border border-slate-700 transition">
                    Upload Ulang File
                </a>
            </div>
        @endif
    </div>

</div>
@endsection
