<table class="display expandable-table text-nowrap" style="min-width: 100%">
    <thead>
        <tr class="text-center">
            <th>No</th>
            <th>Item Name</th>
            <th>Condition</th>
            <th>Quantity Used</th>
            <th>Used Item</th>
            <th>Usage Date</th>
            <th>PIC</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $n = 1;
        @endphp
        @foreach ($shipWarehouseUsages as $item)
            <tr class="text-center" style="{{ $item->note === 'Adjustment' ? 'background-color: #ffecec;' : '' }}">
                <th>{{ $n++ }}</th>
                <td>{{ $item->shipWarehouseConditions->shipWarehouses->items->item_name }}</td>
                <td>{{ $item->shipWarehouseConditions->condition }}</td>
                <td>{{ $item->quantity_used }}</td>
                <td>{{ $item->used_item_condition }}</td>
                <td>{{ \Carbon\Carbon::parse($item->usage_date)->format('d/m/Y') }}</td>
                <td>{{ $item->pic }}</td>
                <td>
                    <div class="btn-group" role="group">
                        @if (
                            ($item->status === 'Diajukan' && Auth::user() && Auth::user()->role === 'Port Engineer') ||
                                ($item->status === 'Diajukan' && Auth::user() && Auth::user()->role === 'Super Admin'))
                            <a type="button" href="/confirmAdjustmentShipWarehouseUsage/{{ $item->id }}"
                                class="btn btn-outline-success btn-approve-adjustment-usage">
                                <i class="mdi mdi-account-check"></i>
                            </a>
                        @endif
                        <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                            data-target="#detailShipWarehouseUsageModal" data-photo="{{ $item->photo }}"
                            data-description="{{ $item->description }}">
                            <i class="mdi mdi-eye"></i>
                        </button>
                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                            <a href="/deleteShipWarehouseUsage/{{ $item->id }}"
                                class="btn btn-outline-danger btn-delete-usage"
                                data-created-at="{{ $item->created_at }}">
                                <i class="mdi mdi-delete"></i>
                            </a>
                        @endif
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- Prevent delete if created at > 24 hours --}}
<script>
    $(document).ready(function() {
        $('.btn-delete-usage').on('click', function(event) {
            event.preventDefault(); // Prevent default action immediately

            // Ambil nilai created-at dari tombol delete
            const createdAt = new Date($(this).data('created-at'));
            const now = new Date();
            const hoursDiff = Math.abs(now - createdAt) / 36e5; // Calculate difference in hours

            // Jika data lebih dari 24 jam, berikan alert
            if (hoursDiff > 24) {
                Swal.fire({
                    title: "Cannot delete data older than 24 hours!",
                    icon: 'info',
                    showCancelButton: false,
                    confirmButtonColor: '#46a146',
                    confirmButtonText: 'Got it'
                }).then((result) => {});
                return;
                // Hentikan proses penghapusan
            } else {
                // Jika data kurang dari 24 jam, tampilkan SweetAlert untuk konfirmasi
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#46a146',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika pengguna mengkonfirmasi dengan "Yes", redirect ke URL penghapusan
                        const deleteUrl = $(this).attr('href'); // Ambil URL dari tombol delete
                        window.location.href = deleteUrl;
                    }
                });
            }
        });

        $('.btn-approve-adjustment-usage').on('click', function(event) {
            event.preventDefault(); // Prevent default action immediately
            Swal.fire({
                title: 'Are you sure?',
                text: "Data will be approved and quantity will be decreased!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#46a146',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika pengguna mengkonfirmasi dengan "Yes", redirect ke URL penghapusan
                    const confirmUrl = $(this).attr('href'); // Ambil URL dari tombol delete
                    window.location.href = confirmUrl;
                }
            });
        });
    });
</script>
