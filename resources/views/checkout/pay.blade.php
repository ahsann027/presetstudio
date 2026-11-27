@extends('layouts.app')

@section('title', 'Pembayaran ' . $order->order_number)

@section('content')
    <h1 class="text-2xl font-bold mb-1">Pembayaran</h1>
    <p class="text-slate-600 mb-5">Nomor pesanan: <strong>{{ $order->order_number }}</strong></p>

    @if ($error)
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 mb-4">{{ $error }}</div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Metode pembayaran + instruksi (F6) --}}
        <div class="space-y-4">
            @if ($driver === 'simulation')
                <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg p-3 text-sm">
                    <strong>Mode simulasi.</strong> Ini hanya demo: tidak ada uang sungguhan yang diproses.
                </div>

                <form id="sim-form" method="POST" action="{{ route('checkout.simulate', $order->access_token) }}" class="space-y-3">
                    @csrf
                    @foreach ($methods as $key => $method)
                        <label class="block bg-white border rounded-xl p-4 cursor-pointer has-[:checked]:border-emerald-600 has-[:checked]:ring-1 has-[:checked]:ring-emerald-600">
                            <span class="flex items-center gap-2 font-semibold">
                                <input type="radio" name="method" value="{{ $key }}" {{ $loop->first ? 'checked' : '' }} required>
                                {{ $method['label'] }}
                            </span>
                            <ol class="list-decimal pl-9 text-sm text-slate-600 mt-2 space-y-0.5">
                                @foreach ($method['steps'] as $step)<li>{{ $step }}</li>@endforeach
                            </ol>
                        </label>
                    @endforeach
                    @error('method')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </form>
            @else
                @foreach ($methods as $method)
                    <details class="bg-white border rounded-xl p-4" {{ $loop->first ? 'open' : '' }}>
                        <summary class="font-semibold cursor-pointer">{{ $method['label'] }}</summary>
                        <ol class="list-decimal pl-5 text-sm text-slate-600 mt-2 space-y-0.5">
                            @foreach ($method['steps'] as $step)<li>{{ $step }}</li>@endforeach
                        </ol>
                    </details>
                @endforeach
            @endif
        </div>

        {{-- Ringkasan & tombol bayar --}}
        <aside class="bg-white border rounded-xl p-5 h-fit">
            <h2 class="font-bold text-lg mb-3">Ringkasan</h2>
            <p class="font-semibold">{{ $order->product_name }}</p>
            <p class="text-sm text-slate-500">{{ $order->buyer_name }} · {{ $order->buyer_email }}</p>
            <div class="border-t mt-4 pt-4 flex justify-between items-center mb-4">
                <span class="text-slate-600">Total bayar</span>
                <span class="text-2xl font-bold">{{ $order->formatted_amount }}</span>
            </div>

            @if ($driver === 'simulation')
                <button type="submit" form="sim-form" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg">
                    Simulasikan pembayaran berhasil
                </button>
            @elseif ($snapToken)
                <button type="button" id="pay-button" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-lg">
                    Pilih metode &amp; bayar
                </button>
            @endif

            <a href="{{ \App\Support\Whatsapp::link($order->whatsappConfirmMessage()) }}" target="_blank" rel="noopener"
               class="mt-3 block text-center w-full border border-emerald-600 text-emerald-700 hover:bg-emerald-50 font-semibold py-2.5 rounded-lg text-sm">
                Konfirmasi / tanya via WhatsApp
            </a>
        </aside>
    </div>
@endsection

@push('scripts')
    @if ($driver === 'midtrans' && $snapToken)
        <script src="{{ $snapJsUrl }}" data-client-key="{{ config('shop.midtrans.client_key') }}"></script>
        <script>
            // Pesanan baru dianggap lunas setelah server menerima notifikasi Midtrans,
            // jadi semua hasil (sukses/pending) diarahkan ke halaman status pesanan.
            const successUrl = @json(route('checkout.success', $order->access_token));
            document.getElementById('pay-button').addEventListener('click', () => {
                window.snap.pay(@json($snapToken), {
                    onSuccess: () => (window.location.href = successUrl),
                    onPending: () => (window.location.href = successUrl),
                    onError: () => alert('Pembayaran gagal. Silakan coba lagi.'),
                    onClose: () => {},
                });
            });
        </script>
    @endif
@endpush
