@props(['status'])

<span @class([
    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
    'bg-amber-100 text-amber-800' => $status === 'pending',
    'bg-green-100 text-green-800' => $status === 'confirmed',
    'bg-gray-200 text-gray-700' => $status === 'completed',
    'bg-red-100 text-red-700' => $status === 'cancelled',
])>{{ \App\Models\Reservation::STATUSES[$status] ?? $status }}</span>
