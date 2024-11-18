<div class="table-responsive table-pills">
    <table class="display expandable-table text-nowrap" style="min-width: 100%">
        <thead>
            <tr class="text-center">
                <th>No</th>
                <th>Photo</th>
                <th>PMS Code</th>
                <th>Item Name</th>
                @if (
                    (Auth::user() && Auth::user()->role === 'Super Admin') ||
                        Auth::user()->role === 'Director' ||
                        Auth::user()->role === 'Fleet Admin' ||
                        Auth::user()->role === 'Purchasing Logistic Admin' ||
                        Auth::user()->role === 'Kapal')
                    <th>Edit</th>
                @endif
                <th>Total Quantity</th>
                <th>Detail Quantity</th>
                <th>Condition</th>
                <th>Location</th>
                @if (
                    (Auth::user() && Auth::user()->role === 'Super Admin') ||
                        Auth::user()->role === 'Fleet Admin' ||
                        Auth::user()->role === 'Purchasing Logistic Admin' ||
                        Auth::user()->role === 'Kapal')
                    <th>Action</th>
                @endif
            </tr>
        </thead>
        <tbody id="shipWarehousesTableBody">
            @php
                $n = 1;
            @endphp
            @foreach ($shipWarehouses as $warehouse)
                @php
                    // Sum up the quantities for 'Baik', 'Bekas Bisa Pakai', and 'Rekondisi'
                    $totalConditionQuantity = $warehouse->conditions
                        ->whereIn('condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi', 'Bekas Tidak Bisa Pakai'])
                        ->sum('quantity');
                    $totalUsableQuantity = $warehouse->conditions
                        ->whereIn('condition', ['Baru', 'Bekas Bisa Pakai', 'Rekondisi'])
                        ->sum('quantity');

                    // Determine if the total quantity is below the minimum
                    $isBelowMinimum = $totalUsableQuantity < $warehouse->minimum_quantity;
                @endphp
                @foreach ($warehouse->conditions as $index => $condition)
                    <tr class="text-center"
                        @if ($isBelowMinimum) style="background-color: #ffecec; height: 10px;" @endif>
                        @if ($index == 0)
                            <th rowspan="{{ count($warehouse->conditions) }}">
                                {{ $n++ }}</th>
                            <td class="col-2 align-middle" rowspan="{{ count($warehouse->conditions) }}">
                                @if ($warehouse->item_photo)
                                    <img src="images/uploads/item-photos/{{ $warehouse->item_photo }}"
                                        class="item-image rounded" alt="Unavailable">
                                @else
                                    <span class="text-muted">No Photo</span>
                                @endif
                            </td>
                            <td rowspan="{{ count($warehouse->conditions) }}">
                                {{ $warehouse->item_pms }}</td>
                            <td id="item-name" rowspan="{{ count($warehouse->conditions) }}">
                                {{ $warehouse->item_name }}</td>
                            @if (
                                (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                    Auth::user()->role === 'Director' ||
                                    Auth::user()->role === 'Fleet Admin' ||
                                    Auth::user()->role === 'Purchasing Logistic Admin' ||
                                    Auth::user()->role === 'Kapal')
                                <td rowspan="{{ count($warehouse->conditions) }}">
                                    <div class="btn-group" role="group">
                                        @if (
                                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                Auth::user()->role === 'Fleet Admin' ||
                                                Auth::user()->role === 'Purchasing Logistic Admin' ||
                                                Auth::user()->role === 'Kapal')
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                                                data-target="#updateShipWarehousesModal"
                                                data-shipwarehouses="{{ json_encode($warehouse) }}">
                                                <i class="mdi mdi-lead-pencil"></i>
                                            </button>
                                        @endif
                                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Director')
                                            <a href="/deleteShipWarehouses/{{ $warehouse->id }}"
                                                class="btn btn-outline-danger btn-delete">
                                                <i class="mdi mdi-delete"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            @endif
                            <td rowspan="{{ count($warehouse->conditions) }}">
                                {{ $totalConditionQuantity }}</td>
                        @endif
                        <td>{{ $condition->quantity }}</td>
                        <td>{{ $condition->condition }}</td>
                        <td>{{ $condition->location }}</td>
                        @if (
                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                Auth::user()->role === 'Purchasing Logistic Admin' ||
                                Auth::user()->role === 'Kapal')
                            <td>
                                <div class="btn-group" role="group">
                                    @if (in_array($condition->condition, ['Baru', 'Bekas Bisa Pakai', 'Rekondisi']))
                                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                                            <button type="button" class="btn btn-outline-warning" data-toggle="modal"
                                                data-target="#addShipWarehouseUsageModal"
                                                data-ship-warehouses="{{ json_encode(['condition' => $condition, 'warehouse' => $warehouse]) }}">
                                                <i class="mdi mdi-export"></i>
                                            </button>
                                        @endif
                                        @if (
                                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                Auth::user()->role === 'Purchasing Logistic Admin' ||
                                                Auth::user()->role === 'Kapal')
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                                                data-target="#updateShipWarehouseConditionsModal"
                                                data-condition="{{ json_encode($condition) }}">
                                                <i class="mdi mdi-lead-pencil"></i>
                                            </button>
                                        @endif
                                    @elseif (in_array($condition->condition, ['Bekas Tidak Bisa Pakai']))
                                        @if ((Auth::user() && Auth::user()->role === 'Super Admin') || Auth::user()->role === 'Kapal')
                                            <button type="button" class="btn btn-outline-danger" data-toggle="modal"
                                                data-target="#addShipWarehouseSendOfficeModal"
                                                data-ship-warehouses="{{ json_encode(['condition' => $condition, 'warehouse' => $warehouse]) }}">
                                                <i class="mdi mdi-export"></i>
                                            </button>
                                        @endif
                                        @if (
                                            (Auth::user() && Auth::user()->role === 'Super Admin') ||
                                                Auth::user()->role === 'Purchasing Logistic Admin' ||
                                                Auth::user()->role === 'Kapal')
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal"
                                                data-target="#updateShipWarehouseConditionsModal"
                                                data-condition="{{ json_encode($condition) }}">
                                                <i class="mdi mdi-lead-pencil"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>
