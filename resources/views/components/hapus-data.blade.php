@props(['action', 'label', 'message'])

<form method="POST" action="{{ $action }}" class="dashboard-action-form"
      onsubmit="return window.confirm(this.dataset.confirm)" data-confirm="{{ $message }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="dashboard-action-delete"
            aria-label="Hapus {{ $label }}">Hapus</button>
</form>
