@php
    $isEdit = $archive->exists;
@endphp

<div class="ea-form-grid">
    <div class="ea-field ea-span-2">
        <label for="name">Nama Arsip / Drive</label>
        <input
            id="name"
            type="text"
            name="name"
            maxlength="150"
            value="{{ old('name', $archive->name) }}"
            placeholder="Contoh: Prosedur Departemen"
            required
        >
        @error('name')
            <small class="ea-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="ea-field">
        <label for="category">Kategori</label>
        <input
            id="category"
            type="text"
            name="category"
            maxlength="80"
            list="eaCategoryOptions"
            value="{{ old('category', $archive->category) }}"
            placeholder="Pilih / ketik kategori"
            required
        >

        <datalist id="eaCategoryOptions">
            @foreach($categories as $item)
                <option value="{{ $item }}"></option>
            @endforeach
        </datalist>

        @error('category')
            <small class="ea-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="ea-field">
        <label for="sort_order">Urutan Tampil</label>
        <input
            id="sort_order"
            type="number"
            min="0"
            max="9999"
            name="sort_order"
            value="{{ old('sort_order', $archive->sort_order ?? 10) }}"
            required
        >
        @error('sort_order')
            <small class="ea-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="ea-field ea-span-4">
        <label for="drive_url">Link Google Drive</label>
        <input
            id="drive_url"
            type="url"
            name="drive_url"
            maxlength="2048"
            value="{{ old('drive_url', $archive->drive_url) }}"
            placeholder="https://drive.google.com/drive/folders/..."
            required
        >
        <small class="ea-help">
            Hanya link Google Drive / Google Docs. SYNRGYPRO menyimpan link, bukan file fisiknya.
        </small>
        @error('drive_url')
            <small class="ea-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="ea-field ea-span-4">
        <label for="description">Deskripsi</label>
        <textarea
            id="description"
            name="description"
            maxlength="500"
            rows="4"
            placeholder="Keterangan singkat isi folder / fungsi arsip"
        >{{ old('description', $archive->description) }}</textarea>
        @error('description')
            <small class="ea-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="ea-field ea-span-4">
        <input
            type="hidden"
            name="is_active"
            value="0"
        >

        <label class="ea-switch-row">
            <input
                type="checkbox"
                name="is_active"
                value="1"
                {{ old('is_active', $archive->is_active ?? true) ? 'checked' : '' }}
            >
            <span>
                <strong>Arsip Aktif</strong>
                <small>
                    Jika nonaktif, registry tetap tersimpan tetapi ditandai NONAKTIF.
                </small>
            </span>
        </label>
    </div>
</div>
