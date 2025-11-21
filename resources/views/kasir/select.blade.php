<h2>Pilih Kasir</h2>

<form method="POST" action="{{ route('kasir.login.pin') }}">
    @csrf
    <select name="kasir_id">
        @foreach ($kasirs as $kasir)
            <option value="{{ $kasir->id_kasir }}">{{ $kasir->nama }}</option>
        @endforeach
    </select>

    <button type="submit">Lanjut</button>
</form>
