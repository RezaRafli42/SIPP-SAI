<style>
    .fixed-size-image {
        width: 100%;
        height: auto;
        max-height: 400px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .mb-3 {
        margin-bottom: 15px;
    }
</style>

<table class="display expandable-table text-nowrap table-pills table-striped" style="min-width: 100%" data-toggle="table"
    data-sortable="true">
    <thead>
        <tr class="text-center">
            <th data-sortable="true">No</th>
            <th data-sortable="true">PR No.</th>
            <th data-sortable="true">Request Date</th>
            <th data-sortable="true">Item Count</th>
            <th>Document</th>
            <th data-sortable="true">Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody id="purchaseRequestsTableBody">
        @php
            $n = 1;
        @endphp
        @foreach ($purchaseRequests as $purchaseRequest)
            <tr class="text-center">
                <th>{{ $n++ }}</th>
                <td>{{ $purchaseRequest->purchase_request_number }}</td>
                <td>
                    {{ \Carbon\Carbon::parse($purchaseRequest->request_date)->format('d/m/Y') }}
                </td>
                <td>
                    {{ $purchaseRequest->item_count }}
                </td>
                <td>
                    <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#documentModal"
                        data-documents="{{ $purchaseRequest->document }}">
                        View Documents
                    </button>
                </td>
                <td>{{ $purchaseRequest->status }}</td>
                <td>
                    <div class="btn-group" role="group">
                        <a class="btn btn-outline-primary" data-toggle="modal" data-target="#detailPurchaseRequestModal"
                            data-purchaserequest="{{ json_encode($purchaseRequest) }}">
                            <i class="mdi mdi-eye"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


{{-- Handle Document --}}
<script>
    $(document).ready(function() {
        $('#documentModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Button that triggered the modal
            var images = button.data('documents'); // Directly use the data as an array

            var modalBody = $('#modalBodyContent');
            modalBody.empty(); // Clear previous images

            // Handle the case where images are null, undefined, or an empty array
            if (images && Array.isArray(images) && images.length > 0) {
                images.forEach(function(image) {
                    var imagePath = '/images/uploads/purchaseRequests-photos/' + image;
                    var imgElement = `
                    <div class="mb-3">
                        <img src="${imagePath}" class="img-fluid fixed-size-image">
                    </div>
                `;
                    modalBody.append(imgElement);
                });
            } else {
                modalBody.append('<p>No images available.</p>');
            }
        });
    });
</script>
{{-- Table Sort --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap-table/dist/bootstrap-table.min.js"></script>
