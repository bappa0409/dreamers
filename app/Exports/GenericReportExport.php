<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class GenericReportExport implements FromCollection,WithHeadings,WithEvents,WithCustomStartCell
{
    public function __construct(
        protected array $rows,
        protected array $headings,
        protected string $reportTitle,
        protected string $organizationName,
        protected ?string $logoPath=null,
        protected ?string $from=null,
        protected ?string $to=null
    ){}

    public function collection(): Collection
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function registerEvents(): array
    {
        return[
            AfterSheet::class=>function(AfterSheet $event){
                $sheet=$event->sheet->getDelegate();
                $lastColumn=$sheet->getHighestColumn();
                $lastRow=$sheet->getHighestRow();

                /*
                |--------------------------------------------------------------------------
                | Header
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells("A1:{$lastColumn}1");
                $sheet->mergeCells("A2:{$lastColumn}2");
                $sheet->mergeCells("A3:{$lastColumn}3");

                $sheet->setCellValue('A1',$this->organizationName);
                $sheet->setCellValue('A2',$this->reportTitle);

                $period=$this->from||$this->to
                    ?'Report Period: '.($this->from??'Beginning').' - '.($this->to??'Present')
                    :'All-time Report';

                $sheet->setCellValue('A3',$period);

                /*
                |--------------------------------------------------------------------------
                | Header Style
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1')->getFont()
                    ->setBold(true)
                    ->setSize(17);

                $sheet->getStyle('A2')->getFont()
                    ->setBold(true)
                    ->setSize(13);

                $sheet->getStyle('A3')->getFont()
                    ->setSize(10);

                $sheet->getStyle("A1:{$lastColumn}3")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle('A3')->getFont()
                    ->getColor()
                    ->setRGB('64748B');

                /*
                |--------------------------------------------------------------------------
                | Table Header
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle("A5:{$lastColumn}5")
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB('3730A3');

                $sheet->getStyle("A5:{$lastColumn}5")
                    ->getFill()
                    ->setFillType('solid')
                    ->getStartColor()
                    ->setRGB('EEF2FF');

                $sheet->getStyle("A5:{$lastColumn}5")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | Data Styling
                |--------------------------------------------------------------------------
                */

                if($lastRow>=5){
                    $sheet->getStyle("A5:{$lastColumn}{$lastRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('E2E8F0');

                    $sheet->getStyle("A6:{$lastColumn}{$lastRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_TOP)
                        ->setWrapText(true);
                }

                /*
                |--------------------------------------------------------------------------
                | Column Width
                |--------------------------------------------------------------------------
                */

                foreach(range('A',$lastColumn) as $column){
                    $sheet->getColumnDimension($column)
                        ->setAutoSize(true);
                }

                $sheet->getRowDimension(1)->setRowHeight(34);
                $sheet->getRowDimension(2)->setRowHeight(24);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(5)->setRowHeight(24);

                /*
                |--------------------------------------------------------------------------
                | Logo
                |--------------------------------------------------------------------------
                */

                if($this->logoPath&&is_file($this->logoPath)){
                    $drawing=new Drawing();

                    $drawing->setName($this->organizationName);
                    $drawing->setDescription('Organization Logo');
                    $drawing->setPath($this->logoPath);
                    $drawing->setHeight(42);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(3);
                    $drawing->setWorksheet($sheet);
                }

                /*
                |--------------------------------------------------------------------------
                | Freeze
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A6');
            },
        ];
    }
}