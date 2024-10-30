<?php

namespace App\Exports;

use App\Models\PurchaseRequests;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class PurchaseRequestExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithCustomStartCell
{
  protected $month;
  protected $year;
  protected $shipID;

  public function __construct($month, $year, $shipID)
  {
    $this->month = $month;
    $this->year = $year;
    $this->shipID = $shipID;
  }

  public function collection()
  {
    return PurchaseRequests::with('items', 'ships')
      ->where('ship_id', $this->shipID)
      ->whereYear('request_date', $this->year)
      ->whereMonth('request_date', $this->month)
      ->get();
  }

  public function startCell(): string
  {
    return 'A5'; // Start filling data from cell A5
  }

  public function registerEvents(): array
  {
    return [
      AfterSheet::class => function (AfterSheet $event) {
        $sheet = $event->sheet;

        // Retrieve ship name
        $shipName = PurchaseRequests::where('ship_id', $this->shipID)->first()->ships->ship_name;

        // Set the main title and subtitle
        $sheet->setCellValue('A1', 'PT. SAMUDERA ATLANTIS INTERNATIONAL');
        $sheet->setCellValue('A2', 'Purchase Requests ' . $shipName);
        $sheet->setCellValue('A3', Carbon::createFromFormat('m', $this->month)->format('F') . ' ' . $this->year);

        // Merge cells for the title and subtitle
        $sheet->mergeCells('A1:G1');
        $sheet->mergeCells('A2:G2');
        $sheet->mergeCells('A3:G4');

        // Set styles for title and subtitle
        $sheet->getStyle('A1')->applyFromArray([
          'font' => [
            'size' => 16,
            'bold' => true,
          ],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
          ],
        ]);

        $sheet->getStyle('A2')->applyFromArray([
          'font' => [
            'size' => 14,
            'bold' => true,
          ],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
          ],
        ]);

        $sheet->getStyle('A3')->applyFromArray([
          'font' => [
            'size' => 12,
            'bold' => true,
          ],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
          ],
        ]);

        // Apply styling to the table header
        $sheet->getStyle('A5:G5')->applyFromArray([
          'font' => [
            'bold' => true,
            'color' => ['argb' => 'FFFFFFFF'],
          ],
          'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'color' => ['argb' => 'C00000'],
          ],
          'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
          ],
        ]);

        // Set borders for the table only up to the highest data row
        $highestRowWithData = $sheet->getHighestDataRow('A'); // Assuming column A is always filled
        $sheet->getStyle("A5:G" . ($highestRowWithData + 2))->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_THIN,
              'color' => ['argb' => 'FF000000'],
            ],
          ],
        ]);

        // Set borders for title
        $sheet->getStyle('A1:G2')->applyFromArray([
          'borders' => [
            'allBorders' => [
              'borderStyle' => Border::BORDER_MEDIUM,
              'color' => ['argb' => 'FF000000'],
            ],
          ],
        ]);

        $sheet->getStyle('A3:G4')->applyFromArray([
          'borders' => [
            'outline' => [
              'borderStyle' => Border::BORDER_MEDIUM,
              'color' => ['argb' => 'FF000000'],
            ],
          ],
        ]);
      },
    ];
  }

  public function headings(): array
  {
    return [
      'NO.', // Added NO. column
      'PR Number',
      'PMS Code',
      'Item Name',
      'Quantity',
      'Unit',
      'Request Date',
    ];
  }

  public function map($purchaseRequest): array
  {
    $rows = [];
    static $rowNumber = 1; // For numbering PRs

    $firstRow = true; // Track if this is the first row for the current PR

    foreach ($purchaseRequest->items as $item) {
      $rows[] = [
        $firstRow ? $rowNumber++ : '', // Incremental row number only for first row
        $firstRow ? $purchaseRequest->purchase_request_number : '', // PR Number only for first row
        $item->items->item_pms,
        $item->items->item_name,
        $item->quantity,
        $item->items->item_unit,
        $firstRow ? Carbon::parse($purchaseRequest->request_date)->format('d M Y') : '', // Date only for first row
      ];
      $firstRow = false; // After first row, set to false
    }

    return $rows;
  }

  public function columnWidths(): array
  {
    return [
      'A' => 5,  // NO.
      'B' => 30, // PR Number
      'C' => 15, // Item Code
      'D' => 30, // Item Name
      'E' => 10, // Quantity
      'F' => 10, // Unit
      'G' => 15, // Request Date
    ];
  }

  public function styles(Worksheet $sheet)
  {
    // Set the default row height
    $sheet->getDefaultRowDimension()->setRowHeight(20);

    // Wrap text for all cells
    $highestRowWithData = $sheet->getHighestDataRow('A');
    $rangeEnd = $highestRowWithData + 2; // Tambahkan 10 baris setelah baris data terakhir
    $sheet->getStyle("A6:G{$rangeEnd}")->getAlignment()->setWrapText(true);

    // Set bold for header row
    $sheet->getStyle('A5:G5')->getFont()->setBold(true);
  }
}
