<div class="grop-columns-2">
    <div class="container-v">
        <div class="group-form-v">
            <label for="department">Departamento<span class="text-red-500">*</span></label>
            <select id="department" required wire:model.live="deparmentName">
                <option value="" hidden>Seleccione</option>
                @foreach ($departments as $department)
                    <option value="{{ $department['id'] }}"> {{ $department['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            @error('password')
                <x-toast.error-toast :message="$message"/>
            @enderror
        </div>
    </div>
    <div class="container-v">
        <div class="group-form-v">
            <label for="municipality">Municipio<span class="text-red-500">*</span></label>
            <select id="municipality" required @disabled(empty($municipalities)) wire:model.live='municipality'>
                <option value="" hidden>Seleccione</option>
                @foreach ($municipalities as $municipality)
                    <option value="{{ $municipality['id'] }}"> {{ $municipality['name'] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            @error('password')
                <x-toast.error-toast :message="$message"/>
            @enderror
        </div>
    </div>
</div>
