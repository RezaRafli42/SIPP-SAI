<div class="table-responsive-scroll">
    <table class="display expandable-table text-nowrap table-pills table-striped table-borderless" style="min-width: 100%"
        data-toggle="table" data-sortable="true">
        <thead>
            <tr class="text-center">
                <th data-sortable="true">No</th>
                <th data-sortable="true">Delivery Order No.</th>
                <th data-sortable="true">Sender</th>
                <th data-sortable="true">Send Date</th>
                <th data-sortable="true">Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="purchaseOrdersTableBody">
            @php
                $n = 1;
            @endphp
            @foreach ($inventoryTransfers as $inventoryTransfers)
                <tr class="text-center">
                    <th scope="row">{{ $n++ }}</th>
                    <td>{{ $inventoryTransfers->delivery_order_number }}</td>
                    <td>{{ $inventoryTransfers->sender_up }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($inventoryTransfers->send_date)->format('d/m/Y') }}</td>
                    <td>{{ $inventoryTransfers->status }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a class="btn btn-outline-primary" data-toggle="modal"
                                data-target="#detailInventoryTransferModal"
                                data-inventorytransfer="{{ json_encode($inventoryTransfers) }}">
                                <i class="mdi mdi-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Table Sort --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
