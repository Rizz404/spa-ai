<x-layouts.admin title="Treatment rooms">
    <x-slot:actions>
        <x-ui.link-button :href="route('admin.treatment-rooms.create')" size="sm">Add treatment room</x-ui.link-button>
    </x-slot:actions>

    <x-ui.table :columns="['Name', 'Type', 'Capacity', 'Fee', 'Images', 'Status', '']" :empty="$treatmentRooms->isEmpty()" empty-text="No treatment rooms yet.">
        @foreach ($treatmentRooms as $treatmentRoom)
            <tr>
                <td class="px-4 py-3 font-medium text-text">{{ $treatmentRoom->name }}</td>
                <td class="px-4 py-3 text-muted">{{ \Illuminate\Support\Str::headline($treatmentRoom->room_type) }}</td>
                <td class="px-4 py-3">{{ $treatmentRoom->min_guests }}–{{ $treatmentRoom->max_guests }} guests</td>
                <td class="px-4 py-3">{{ (float) $treatmentRoom->reservation_fee > 0 ? $spa->currency.' '.number_format((float) $treatmentRoom->reservation_fee, 0, ',', '.') : 'Free' }}</td>
                <td class="px-4 py-3">{{ $treatmentRoom->images_count }}</td>
                <td class="px-4 py-3">
                    <x-ui.badge :tone="$treatmentRoom->is_active ? 'success' : 'neutral'">{{ $treatmentRoom->is_active ? 'Active' : 'Hidden' }}</x-ui.badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.treatment-rooms.edit', $treatmentRoom) }}" class="text-muted hover:underline">Edit</a>
                    <x-ui.confirm-form :action="route('admin.treatment-rooms.destroy', $treatmentRoom)" message="Delete this treatment room?" confirm-label="Delete" trigger-class="ml-3 text-danger hover:underline">Delete</x-ui.confirm-form>
                </td>
            </tr>
        @endforeach
    </x-ui.table>
</x-layouts.admin>
