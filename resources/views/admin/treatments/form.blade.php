@php($isEdit = $treatment->exists)

<x-layouts.admin :title="$isEdit ? 'Edit treatment' : 'Add treatment'">
    <form method="POST" action="{{ $isEdit ? route('admin.treatments.update', $treatment) : route('admin.treatments.store') }}" class="space-y-8">
        @csrf
        @if ($isEdit) @method('PUT') @endif

        <x-ui.card heading="Basics">
            <div class="mt-4 grid grid-cols-2 gap-4">
                <x-ui.select name="category" label="Category">
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(old('category', $treatment->category) === $category)>{{ \Illuminate\Support\Str::headline($category) }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.input name="price" type="number" label="Price" step="500" :value="old('price', $treatment->price)" required />
                <x-ui.input name="name" label="Name (Indonesian, default)" :value="old('name', $treatment->name)" required class="col-span-2" />
                <x-ui.textarea name="description" label="Description (Indonesian, default)" :value="old('description', $treatment->description)" class="col-span-2" />
                <x-ui.input name="translations[en][name]" label="Name (English)" :value="old('translations.en.name', $treatment->translations['en']['name'] ?? '')" />
                <x-ui.input name="translations[ja][name]" label="Name (Japanese)" :value="old('translations.ja.name', $treatment->translations['ja']['name'] ?? '')" />
                <x-ui.textarea name="translations[en][description]" label="Description (English)" :value="old('translations.en.description', $treatment->translations['en']['description'] ?? '')" />
                <x-ui.textarea name="translations[ja][description]" label="Description (Japanese)" :value="old('translations.ja.description', $treatment->translations['ja']['description'] ?? '')" />
            </div>
        </x-ui.card>

        <x-ui.card heading="Details">
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <x-ui.input name="duration_label" label="Session label (optional)" :value="old('duration_label', $treatment->duration_label)" placeholder="Optional session label" />
                <x-ui.input name="duration_minutes" type="number" label="Duration (minutes)" min="15" max="120" required :value="old('duration_minutes', $treatment->duration_minutes)" />
                <x-ui.input name="sort_order" type="number" label="Sort order" :value="old('sort_order', $treatment->sort_order)" />
            </div>
            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-ui.input name="tags" label="Tags (comma-separated)" :value="old('tags', implode(', ', $treatment->tags ?? []))" placeholder="relaxation, botanical, signature" />
                <x-ui.input name="contraindications" label="Precautions (comma-separated)" :value="old('contraindications', implode(', ', $treatment->contraindications ?? []))" placeholder="pregnancy, sensitive_skin" />
                <x-ui.input name="image_url" label="Image path or URL" :value="old('image_url', $treatment->image_url)" placeholder="images/spa/treatment.png or https://…" class="sm:col-span-2" />
            </div>
            <div class="mt-4 flex flex-wrap gap-6">
                <x-ui.checkbox name="is_featured" label="Featured" :checked="old('is_featured', $treatment->is_featured)" />
                <x-ui.checkbox name="is_unavailable" label="Temporarily unavailable" :checked="old('is_unavailable', $treatment->is_unavailable)" />
                <x-ui.checkbox name="is_active" label="Published (visible on the website)" :checked="old('is_active', $treatment->is_active)" />
            </div>
        </x-ui.card>

        <div class="flex justify-end gap-3">
            <x-ui.link-button :href="route('admin.treatments.index')" variant="secondary">Cancel</x-ui.link-button>
            <x-ui.button type="submit">Save treatment</x-ui.button>
        </div>
    </form>
</x-layouts.admin>
