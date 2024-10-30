<?php

namespace App\Exports;

use App\Models\InventoryTransfers;
use App\Models\InventoryTransferItems;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class DeliveryOrderExport implements FromCollection, WithStyles, WithDrawings
{
  use Exportable;

  protected $id;
  protected $data;
  protected $deliveryOrderNumber;
  protected $sendDate;
  protected $senderUP;
  protected $senderTitle;
  protected $senderContact;
  protected $recipientName;
  protected $recipientProjectPosition;
  protected $recipientTitle;
  protected $recipientUP;
  protected $itemName;
  protected $itemQuantity;
  protected $itemUnit;
  protected $itemKoli;
  protected $itemCondition;


  public function __construct($id)
  {
    $this->id = $id;
  }

  public function collection()
  {
    // Fetching data with the correct relationships and columns
    $this->data = InventoryTransfers::select(
      'inventory_transfers.id',
      'inventory_transfers.delivery_order_number',
      'inventory_transfers.send_date',
      'inventory_transfers.sender_up',
      'inventory_transfers.sender_title',
      'inventory_transfers.sender_contact',
      'inventory_transfers.recipient_name',
      'inventory_transfers.recipient_project_position',
      'inventory_transfers.recipient_title',
      'inventory_transfers.recipient_up',
      'purchase_request_items.quantity as send_quantity',
      'inventory_transfer_items.koli',
      'inventory_transfer_items.condition',
      'items.item_name',
      'items.item_unit'
    )
      ->leftJoin('inventory_transfer_items', 'inventory_transfers.id', '=', 'inventory_transfer_items.inventory_transfer_id')
      ->leftJoin('purchase_request_items', 'inventory_transfer_items.purchase_request_item_id', '=', 'purchase_request_items.id')
      ->leftJoin('items', 'purchase_request_items.item_id', '=', 'items.id')
      ->where('inventory_transfers.id', $this->id)
      ->get();

    $this->deliveryOrderNumber = $this->data->pluck('delivery_order_number');
    $this->sendDate = $this->data->pluck('send_date');
    $this->senderUP = $this->data->pluck('sender_up');
    $this->senderTitle = $this->data->pluck('sender_title');
    $this->senderContact = $this->data->pluck('sender_contact');
    $this->recipientName = $this->data->pluck('recipient_name');
    $this->recipientProjectPosition = $this->data->pluck('recipient_project_position');
    $this->recipientTitle = $this->data->pluck('recipient_title');
    $this->recipientUP = $this->data->pluck('recipient_up');
    $this->itemName = $this->data->pluck('item_name');
    $this->itemQuantity = $this->data->pluck('send_quantity');
    $this->itemKoli = $this->data->pluck('koli');
    $this->itemCondition = $this->data->pluck('condition');
    $this->itemUnit = $this->data->pluck('item_unit');
    $this->sendDate = $this->sendDate->map(function ($date) {
      return Carbon::parse($date)->format('d-m-Y');
    });

    return $this->data;
  }



  public function styles(Worksheet $sheet)
  {
    $sheet->getStyle('A1:J50')->getFont()->setName('Arial Narrow');

    $highestColumn = $sheet->getHighestColumn();
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

    for ($row = 1; $row <= 15; $row++) {
      for ($col = 1; $col <= $highestColumnIndex; $col++) {
        $sheet->setCellValueByColumnAndRow($col, $row, '');
      }
    }

    // CENTER
    $sheet->getStyle('A1:J60')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

    // CENTER HEADER TEXT
    $sheet->getStyle('A15:J15')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // BORDER
    $sheet->getStyle('A1:C4')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('D1:J4')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A5:J7')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A8:D13')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('E8:J13')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A14')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('B15:D15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('E15:F15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('G15:I15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('J15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);

    // WARNA
    $sheet->getStyle('A15:J15')->getFill()->applyFromArray(['fillType' => 'solid', 'rotation' => 0, 'color' => ['rgb' => 'C00000'],]);
    $sheet->getStyle('A15:J15')->applyFromArray([
      'font' => [
        'bold' => true,
        'size' => 12, // Ganti dengan ukuran yang Anda inginkan
        'color' => ['argb' => 'FFFFFFFF'], // Putih
      ],
      'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'C00000'], // Merah
      ],
    ]);

    // KEPALA
    $sheet->mergeCells("D1:J4");
    $sheet->setCellValue('D1', 'SURAT JALAN & TANDA TERIMA BARANG ');
    $sheet->getStyle('D1')->getFont()->setSize(24);
    $sheet->getStyle('D1')->getFont()->setBold(true);
    $sheet->getStyle('D1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // DOKUMEN
    $sheet->setCellValue('A5', 'No. Dokumen');
    $sheet->setCellValue('A6', 'No. Surat Jalan');
    $sheet->setCellValue('A7', 'Tanggal');
    $sheet->setCellValue('C5', ':');
    $sheet->setCellValue('C6', ':');
    $sheet->setCellValue('C7', ':');
    $sheet->setCellValue('D5', 'F-SAI.PUR-01.02');
    $sheet->setCellValue('D6', $this->deliveryOrderNumber->first());
    $sheet->setCellValue('D7', $this->sendDate->first());

    // PENGIRIM
    $sheet->mergeCells("A8:D8");
    $sheet->setCellValue('A8', 'PENGIRIM :');
    $sheet->getStyle('A8')->getFont()->setBold(true);

    $sheet->mergeCells("A9:B9");
    $sheet->setCellValue('A9', 'Nama Perusahaan');

    $sheet->mergeCells("A10:B10");
    $sheet->setCellValue('A10', 'Alamat');

    $sheet->mergeCells("A12:B12");
    $sheet->setCellValue('A12', 'UP.');

    $sheet->mergeCells("A13:B13");
    $sheet->setCellValue('A13', 'No. Telpon');

    $sheet->setCellValue('C9', ':');
    $sheet->setCellValue('C10', ':');
    $sheet->setCellValue('C12', ':');
    $sheet->setCellValue('C13', ':');

    $sheet->setCellValue('D9', 'PT. SAMUDERA ATLANTIS INTERNATIONAL');
    $sheet->setCellValue('D10', 'Komplek Rukan Puri Mutiara Blok BG No. 2-3');
    $sheet->setCellValue('D11', 'Jl. Griya Utama, Sunter Agung - Jakarta Utara');
    $sheet->setCellValue('D12', $this->senderUP->first());
    $sheet->setCellValue('D13', $this->senderContact->first());

    // PENERIMA
    $sheet->mergeCells("E8:J8");
    $sheet->mergeCells("G9:J9");
    $sheet->mergeCells("G10:J10");
    $sheet->mergeCells("G11:J11");
    $sheet->mergeCells("G12:J12");
    $sheet->setCellValue('E8', 'PENERIMA :');
    $sheet->getStyle('E8')->getFont()->setBold(true);
    $sheet->setCellValue('E9', 'Nama');
    $sheet->setCellValue('E10', 'Posisi Project');
    $sheet->setCellValue('E11', 'Bagian');
    $sheet->setCellValue('E12', 'UP.');

    $sheet->setCellValue('F9', ':');
    $sheet->setCellValue('F10', ':');
    $sheet->setCellValue('F11', ':');
    $sheet->setCellValue('F12', ':');

    $sheet->setCellValue('G9', $this->recipientName->first());
    $sheet->setCellValue('G10', $this->recipientProjectPosition->first());
    $sheet->setCellValue('G11', $this->recipientTitle->first());
    $sheet->setCellValue('G12', $this->recipientUP->first());

    //
    $sheet->mergeCells("A14:J14");
    $sheet->setCellValue('A14', 'Telah kami serahkan barang-barang dengan keterangan sebagai berikut :');

    // TABLE ISI
    // TABLE HEADER MERGE
    $sheet->mergeCells("B15:D15");
    $sheet->mergeCells("E15:F15");
    $sheet->mergeCells("G15:I15");

    // TABLE HEADER TITLES
    $sheet->setCellValue('A15', 'NO');
    $sheet->setCellValue('B15', 'URAIAN');
    $sheet->setCellValue('E15', 'SPESIFIKASI');
    $sheet->setCellValue('G15', 'QUANTITY');
    $sheet->setCellValue('J15', 'KETERANGAN');

    // Table content
    $rowNumber = 16;
    $index = 1;

    foreach ($this->data as $item) {
      $sheet->mergeCells("B{$rowNumber}:D{$rowNumber}");
      $sheet->mergeCells("E{$rowNumber}:F{$rowNumber}");
      $sheet->mergeCells("H{$rowNumber}:I{$rowNumber}");

      $sheet->setCellValue("A{$rowNumber}", $index++);
      $sheet->setCellValue("B{$rowNumber}", $item->item_name);
      $sheet->setCellValue("E{$rowNumber}", 'A'); // Example specification, adjust as needed
      $sheet->setCellValue("G{$rowNumber}", $item->send_quantity); // Accessing quantity from purchase_request_items
      $sheet->setCellValue("H{$rowNumber}", $item->item_unit);
      $sheet->setCellValue("J{$rowNumber}", $item->condition . ' (Koli ' . $item->koli . ')'); // Accessing koli from inventory_transfer_items

      $sheet->getRowDimension($rowNumber)->setRowHeight(45);
      $sheet->getStyle("A{$rowNumber}:J{$rowNumber}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

      $rowNumber++;
    }


    // Apply border to all data rows
    $dataEndRow = $rowNumber - 1;
    $sheet->getStyle("A15:J{$dataEndRow}")->applyFromArray([
      'borders' => [
        'allBorders' => [
          'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);

    // KAKI
    // Set table footer
    $footerStartRow = $rowNumber + 1; // Leave a gap of one row after data

    $sheet->mergeCells("A{$footerStartRow}:J{$footerStartRow}");
    $sheet->mergeCells("A" . ($footerStartRow + 4) . ":D" . ($footerStartRow + 4));
    $sheet->mergeCells("E" . ($footerStartRow + 3) . ":J" . ($footerStartRow + 3));
    $sheet->mergeCells("E" . ($footerStartRow + 4) . ":J" . ($footerStartRow + 4));
    $sheet->mergeCells("A" . ($footerStartRow + 9) . ":D" . ($footerStartRow + 9));
    $sheet->mergeCells("A" . ($footerStartRow + 10) . ":D" . ($footerStartRow + 10));
    $sheet->mergeCells("E" . ($footerStartRow + 9) . ":J" . ($footerStartRow + 9));
    $sheet->mergeCells("E" . ($footerStartRow + 10) . ":J" . ($footerStartRow + 10));

    // $sheet->getStyle("A{$footerStartRow}:J" . ($footerStartRow + 10))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Set footer content
    $sheet->setCellValue("A{$footerStartRow}", 'Barang-barang tersebut telah kami terima sesuai dengan keterangan diatas.');
    $sheet->setCellValue("A" . ($footerStartRow + 4), 'Penerima,');
    $sheet->setCellValue("A" . ($footerStartRow + 9), $this->recipientName->first());
    $sheet->setCellValue("A" . ($footerStartRow + 10), $this->recipientTitle->first());
    $sheet->setCellValue("E" . ($footerStartRow + 3), 'Jakarta, ' . $this->sendDate->first());
    $sheet->setCellValue("E" . ($footerStartRow + 4), 'Yang Menyerahkan,');
    $sheet->setCellValue("E" . ($footerStartRow + 9), $this->senderUP->first());
    $sheet->setCellValue("E" . ($footerStartRow + 10), $this->senderTitle->first());

    $sheet->getStyle(($footerStartRow + 3) . ':' . ($footerStartRow + 10))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("E" . ($footerStartRow + 9))->applyFromArray([
      'font' => [
        'bold' => true,
      ],
    ]);
    $sheet->getStyle("A" . ($footerStartRow + 9))->applyFromArray([
      'font' => [
        'bold' => true,
      ],
    ]);
    $sheet->getStyle("A" . ($footerStartRow + 2) . ":D" . ($footerStartRow + 10))->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle("E" . ($footerStartRow + 2) . ":J" . ($footerStartRow + 10))->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);

    // Apply underline to footer
    $sheet->getStyle("A" . ($footerStartRow + 9))->applyFromArray([
      'font' => [
        'underline' => true,
      ],
    ]);
    $sheet->getStyle("E" . ($footerStartRow + 9))->applyFromArray([
      'font' => [
        'underline' => true,
      ],
    ]);

    // Apply borders
    $sheet->getStyle('A1:C4')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('D1:J4')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A5:J7')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A8:D13')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('E8:J13')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A14')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('A15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('B15:D15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('E15:F15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('G15:I15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);
    $sheet->getStyle('J15')->applyFromArray([
      'borders' => [
        'outline' => [
          'borderStyle' => Border::BORDER_MEDIUM,
          'color' => ['argb' => '000000'],
        ],
      ],
    ]);


    // Warna
    $sheet->getStyle('A15:J15')->getFill()->applyFromArray([
      'fillType' => 'solid',
      'rotation' => 0,
      'color' => ['rgb' => 'C00000'],
    ]);
    $sheet->getStyle('A15:J15')->applyFromArray([
      'color' => [
        'color' => ['argb' => 'C00000'],
      ],
    ]);

    // LEBAR
    $sheet->getColumnDimension('A')->setWidth(3.67);
    $sheet->getColumnDimension('B')->setWidth(12);
    $sheet->getColumnDimension('C')->setWidth(1.11);
    $sheet->getColumnDimension('D')->setWidth(41.89);
    $sheet->getColumnDimension('E')->setWidth(15.67);
    $sheet->getColumnDimension('F')->setWidth(1.33);
    $sheet->getColumnDimension('G')->setWidth(6.22);
    $sheet->getColumnDimension('H')->setWidth(6.33);
    $sheet->getColumnDimension('I')->setWidth(0.94);
    $sheet->getColumnDimension('J')->setWidth(24.56);

    // TINGGI
    $sheet->getRowDimension(14)->setRowHeight(21.8);
    $sheet->getRowDimension(15)->setRowHeight(23.3);
  }

  public function drawings()
  {
    $drawing1 = new Drawing();
    $drawing1->setName('Logo');
    $drawing1->setPath(public_path('/images/logo/logo-press-compress.png'));
    $drawing1->setCoordinates('A1');
    $drawing1->setWidth(90);
    $drawing1->setHeight(75);
    return [$drawing1];
  }
}
