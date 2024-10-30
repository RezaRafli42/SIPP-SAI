<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{-- <title>{{ $title }}</title> --}}
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css"
        integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        * {
            font-size: small;
        }

        .header-container {
            text-align: center;
        }

        .header-title {
            background-color: #aa0404;
            color: white;
            padding: 10px 0;
        }

        .header-title h5 {
            margin: 0;
            font-size: 1.2rem;
        }

        .header-subtitle {
            background-color: #db0606;
            color: white;
            padding: 5px 0;
        }

        .header-subtitle p {
            margin: 0;
            font-size: 1rem;
        }

        .supplier {
            width: 65%;
            margin-top: 1rem;
            margin-bottom: .8rem;
        }

        .supplier table {
            margin-top: .8rem;
        }

        .note {
            margin-bottom: .8rem;
        }

        .parent table tr td:first-child {
            width: 100px;
        }

        .parent table tr td:nth-child(2) {
            width: 10px;
        }

        .note table tr td:first-child {
            width: 140px;
        }

        .note table tr td:nth-child(2) {
            width: 10px;
        }

        /* Atur tabel body agar rapi */
        .body {
            margin-top: 0px;
            margin-bottom: 20px;
        }

        .body table {
            width: 100%;
            margin-top: 1rem;
            border-spacing: 0;
            /* Hilangkan jarak antar kolom */
            border-collapse: collapse;
            /* Gabungkan border menjadi satu */
        }

        .body table th,
        .body table td {
            padding: 8px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid black;
        }

        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .note table {
            width: 100%;
            /* Pastikan tabel dalam bagian note juga memiliki lebar penuh */
            margin-top: 1rem;
        }

        .note table td {
            padding: 4px;
            /* Tambahkan padding agar lebih rapi */
            vertical-align: top;
            /* Atur teks agar berada di atas */
        }

        .footer {
            margin-top: 1rem;
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer p:last-child {
            margin-bottom: 0;
        }
    </style>
</head>

<body>
    <div class="header-container">
        <div class="header-title">
            <h5>PURCHASE ORDER</h5>
            <h5>PT.SAMUDERA ATLANTIS INTERNATIONAL & PT.ADHIGUNA KERUKTAMA</h5>
        </div>
        <div class="header-subtitle">
            <p>Komplek Rukan Puri Mutiara Blok BG No. 2-3</p>
        </div>
    </div>
    <p>{{ $waktu }}</p>
    <div class="parent">
        <table>
            <tbody>
                <tr class="text-center">
                    <td>No. </td>
                    <td> : </td>
                    <td> {{ $dataSatuan->purchase_order_number }}</td>
                </tr>
                <tr class="text-center">
                    <td>Attach </td>
                    <td> : </td>
                    <td style="font-weight: bold">-</td>
                </tr>
                <tr class="text-center">
                    <td>Subject </td>
                    <td> : </td>
                    <td style="font-weight: bold;"> Pesanan Pembelian</td>
                </tr>
            </tbody>
        </table>
        <div class="supplier">
            <p>To : <br><b style="text-transform: uppercase">{{ $dataSatuan->supplier_name }}</b>
                <br>{{ $dataSatuan->supplier_address }}
            </p>
            <table>
                <tbody>
                    <tr class="text-center">
                        <td>Nomor Telp. </td>
                        <td> : </td>
                        <td> {{ $dataSatuan->supplier_contact }}</td>
                    </tr>
                    <tr class="text-center">
                        <td>Email</td>
                        <td> : </td>
                        <td> {{ $dataSatuan->supplier_email }}</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>
    <p style="margin-top: 1rem;">Dengan hormat,<br>Sesuai penawaran harga yang kami terima, kami ingin memesan barang
        sebagai berikut :
    </p>
    <div class="body">
        <table class="table" style="text-align: center; width: 100%;">
            <thead>
                <tr class="text-center">
                    <th scope="col">No.</th>
                    <th scope="col">PMS Code</th>
                    <th scope="col">Description</th>
                    <th scope="col">Qty</th>
                    <th scope="col">Unit</th>
                    <th scope="col">Price Satuan ({{ $currency }})</th>
                    <th scope="col">Amount ({{ $currency }})</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $n = 1;
                    $subTotal = 0;
                    $totalPpn = 0;
                @endphp
                @foreach ($data as $item)
                    @php
                        $amount = $item->price * $item->quantity; // Total per item
                        $ppnAmount = $amount * ($item->ppn / 100); // Menggunakan nilai PPN dari kolom
                        $subTotal += $amount; // Akumulasi subtotal
                        $totalPpn += $ppnAmount; // Akumulasi PPN
                    @endphp
                    <tr class="text-center">
                        <td>{{ $n++ }}</td>
                        <td>{{ $item->item_pms }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->item_unit }}</td>
                        <td>{{ number_format($item->price, 2) }}</td>
                        <td>{{ number_format($amount, 2) }}</td>
                    </tr>
                @endforeach
                <!-- Row untuk subtotal, PPN, dan total keseluruhan -->
                <tr class="text-center">
                    <td colspan="6" style="text-align: right; font-weight: bold;">Sub Total</td>
                    <td>{{ number_format($subTotal, 2) }}</td>
                </tr>
                <tr class="text-center">
                    <td colspan="6" style="text-align: right; font-weight: bold;">PPN</td>
                    <td>{{ number_format($totalPpn, 2) }}</td>
                </tr>
                <tr class="text-center">
                    <td colspan="6" style="text-align: right; font-weight: bold;">Total All</td>
                    <td>{{ number_format($subTotal + $totalPpn, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="note">
        <table>
            <tbody>
                <tr class="text-center">
                    <td><strong>Note :</strong></td>
                </tr>
                <tr class="text-center">
                    <td>- PIC</td>
                    <td>:</td>
                    <td>{{ $dataSatuan->pic }} ({{ $dataSatuan->pic_contact }})</td> <!-- Menggunakan $dataSatuan -->
                </tr>
                <tr class="text-center">
                    <td>- Alamat Pengiriman</td>
                    <td>:</td>
                    <td>{{ $dataSatuan->delivery_address }}</td> <!-- Menggunakan $dataSatuan -->
                </tr>
                <tr class="text-center">
                    <td>- Catatan</td>
                    <td>:</td>
                    <td>{{ $dataSatuan->note }}</td> <!-- Menggunakan $dataSatuan -->
                </tr>
            </tbody>
        </table>
    </div>
    <p>Supplier wajib mencantumkan aspek dan spesifikasi K3 untuk barang yang memiliki risiko terhadap
        K3</p>
    <p>Demikian PO ini kami buat, atas perhatian dan kerjasama yang baik kami ucapkan terima kasih</p>
    @php
        $totalAll = $subTotal + $totalPpn;
    @endphp

    <div class="footer" style="position: absolute; bottom: 0;">
        @if ($totalAll < 30000000)
            <p>Hormat kami,</p>
            <p><b>PT.SAMUDERA ATLANTIS INTERNATIONAL</b></p>
            <br>
            <p style="margin-top: 2.5rem"><u>Ferry Sukianto</u></p>
            <p style="margin-top: -10px">Purchasing Manager</p>
        @else
            <p>Hormat kami,</p>
            <p><b>PT.SAMUDERA ATLANTIS INTERNATIONAL</b></p>
            <br>
            <p style="margin-top: 2.5rem"><u>Dimas Senoaji Pangestu</u></p>
            <p style="margin-top: -10px">Direktur</p>
        @endif
    </div>
</body>

</html>
