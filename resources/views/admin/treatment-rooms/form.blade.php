@php($isEdit = $treatmentRoom->exists)

<x-layouts.admin :title="$isEdit ? 'Edit treatment room' : 'Add treatment room'">
    <form method="POST" action="{{ $isEdit ? route('admin.treatment-rooms.update', $treatmentRoom) : route('admin.treatment-rooms.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $treatmentRoom->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $treatmentRoom->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $treatmentRoom->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $treatmentRoom->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $treatmentRoom->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $treatmentRoom->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Capacity & fees">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.select name="room_type" label="Type">
                    @foreach (\App\Models\TreatmentRoom::areaTypes() as $type)
                        <option value="{{ $type }}" @selected(old('room_type', $treatmentRoom->room_type) === $type)>{{ \Illuminate\Support\Str::headline($type) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="min_guests" type="number" label="Min guests" min="1" :value="old('min_guests', $treatmentRoom->min_guests)" required />
                <x-ui.input name="max_guests" type="number" label="Max guests" min="1" :value="old('max_guests', $treatmentRoom->max_guests)" required />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $treatmentRoom->sort_order)" />
                <x-ui.input name="reservation_fee" type="number" label="Reservation fee / table" step="1000" min="0" :value="old('reservation_fee', $treatmentRoom->reservation_fee ?? 0)" required />
                <x-ui.input name="minimum_spend" type="number" label="Minimum spend" step="1000" min="0" :value="old('minimum_spend', $treatmentRoom->minimum_spend)" />
            </div>
            <x-ui.input name="features" label="Features (comma-separated)" :value="old('features', implode(', ', $treatmentRoom->features ?? []))" placeholder="wifi, power_outlet, air_conditioning, pet_friendly" class="mt-4" />
            <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $treatmentRoom->is_active)" class="mt-4" />
        </x-ui.card>

        <x-ui.card heading="Photos">
            @if ($isEdit && $treatmentRoom->images->isNotEmpty())
                <div class="mt-4 space-y-2">
                    @foreach ($treatmentRoom->images as $image)
                        <div class="flex items-center gap-3 rounded-control border border-border p-2">
                            <img src="{{ $image->image_source }}" alt="{{ $image->alt_text }}" class="h-14 w-20 rounded-sm object-cover">
                            <div class="flex-1 text-xs text-muted">
                                <p class="truncate">{{ $image->image_url }}</p>
                                <p>Tags: {{ implode(', ', $image->tags ?? []) }}</p>
                            </div>
                            <label class="flex items-center gap-1 text-xs text-danger">
                                <input type="checkbox" name="delete_images[]" value="{{ $image->id }}" class="rounded-sm border-border-strong">
                                Delete
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="mt-4 text-xs text-muted">Add photos by path under public/ or by URL. Tag them so the AI Spa Concierge can pick the right one — e.g. "sofa, indoor" or "garden, terrace".</p>
            @for ($i = 0; $i < 3; $i++)
                <div class="mt-2 grid grid-cols-6 gap-2">
                    <input type="text" name="new_images[{{ $i }}][url]" aria-label="Photo {{ $i + 1 }} path or URL" placeholder="images/spa/room.png or https://…" class="col-span-3 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][tags]" aria-label="Photo {{ $i + 1 }} tags" placeholder="sofa, indoor" class="col-span-2 rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                    <input type="text" name="new_images[{{ $i }}][alt]" aria-label="Photo {{ $i + 1 }} alt text" placeholder="Alt text" class="rounded-control border border-border-strong bg-surface px-3 py-2 text-sm text-text placeholder:text-subtle">
                </div>
            @endfor
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.treatment-rooms.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save treatment room</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
