<table class="display expandable-table text-nowrap" style="min-width: 100%">
    <thead>
        <tr class="text-center">
            <th>No</th>
            <th>Item Name</th>
            <th>Condition</th>
            <th>Quantity Send</th>
            <th>Send Date</th>
            <th>PIC</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php
            $n = 1;
        @endphp
        @foreach ($shipWarehouseSendOffice as $item)
            <tr class="text-center" style="{{ $item->note === 'Adjustment' ? 'background-color: #ffecec;' : '' }}">
                <th>{{ $n++ }}</th>
                <td>{{ $item->shipWarehouseConditions->shipWarehouses->items->item_name }}</td>
                <td>{{ $item->shipWarehouseConditions->condition }}</td>
                <td>{{ $item->quantity_send }}</td>
                <td>{{ \Carbon\Carbon::parse($item->send_date)->format('d/m/Y') }}</td>
                <td>{{ $item->pic }}</td>
                <td>{{ $item->status }}</td>
                <td>
                    @if (
                        (Auth::user() && Auth::user()->role === 'Super Admin') ||
                            Auth::user()->role === 'Purchasing Logistic Admin' ||
                            Auth::user()->role === 'Port Engineer')
                        <div class="btn-group" role="group">
                            @if (
                                ($item->status === 'Diajukan' && Auth::user() && Auth::user()->role === 'Port Engineer') ||
                                    ($item->status === 'Diajukan' && Auth::user() && Auth::user()->role === 'Super Admin'))
                                <a type="button" href="/confirmAdjustmentShipWarehouseSendOffice/{{ $item->id }}"
                                    class="btn btn-outline-success btn-approve-adjustment-send-office">
                                    <i class="mdi mdi-account-check"></i>
                                </a>
                            @endif
                            @if ($item->status == 'Send by Ship')
                                <a type="button" href="/confirmShipWarehouseSendOffice/{{ $item->id }}"
                                    class="btn btn-outline-success btn-confirm-send-office"
                                    data-send-office-id="{{ $item->id }}">
                                    <i class="mdi mdi-briefcase-check"></i>
                                </a>
                            @endif
                    @endif
                    <a type="button" data-toggle="modal" class="btn btn-outline-primary"
                        data-target="#detailShipWarehouseSendOfficeModal" data-photo="{{ $item->photo }}"
                        data-description="{{ $item->description }}">
                        <i class="mdi mdi-eye"></i>
                    </a>
                    @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                        @if ($item->status !== 'Received by Office')
                            <a type="button" href="/deleteShipWarehouseSendOffice/{{ $item->id }}"
                                class="btn btn-outline-danger btn-delete-send"
                                data-created-at="{{ $item->created_at }}">
                                <i class="mdi mdi-delete"></i>
                            </a>
                        @endif
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
        $('.btn-delete-send').on('click', function(event) {
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
    });
</script>

{{-- Confirm button --}}
<script>
    $(document).ready(function() {
        $('.btn-confirm-send-office').on('click', function(event) {
            event.preventDefault(); // Prevent default action immediately
            Swal.fire({
                title: 'Are you sure?',
                text: "Data will be transferred to Office Warehouse!",
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

        $('.btn-approve-adjustment-send-office').on('click', function(event) {
            event.preventDefault(); // Prevent default action immediately
            Swal.fire({
                title: 'Are you sure?',
                text: "Data will be approved and transferred to Office Warehouse!",
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
