@props(['action', 'label', 'message'])

<form method="POST" action="{{ $action }}" class="mt-2"
      onsubmit="return window.confirm(this.dataset.confirm)" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
            aria-label="Hapus {{ $label }}">Hapus</button>
</form>
