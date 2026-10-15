<x-layouts.admin title="Catalog">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.treatments.create')" size="sm">Add treatment</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Category', 'Price', 'Status', '']" :empty="$treatments->isEmpty()" empty-text="No treatments yet.">
        @foreach ($treatments as $treatment)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $treatment->name }}@if ($treatment->is_featured)<x-ui.badge tone="warning" class="ml-2">Featured</x-ui.badge>@endif</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($treatment->category) }}</td>
                <td class="px-4 py-3">{{ $spa->currency }} {{ number_format((float) $treatment->price, 0, ',', '.') }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$treatment->is_active ? 'success' : 'neutral'">{{ $treatment->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                    @if ($treatment->is_unavailable)
                        <x-ui.badge tone="danger" class="ml-1">Sold out</x-ui.badge>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.treatments.edit', $treatment) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.treatments.destroy', $treatment)" message="Delete this treatment?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
