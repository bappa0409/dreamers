<?php

namespace App\Http\Controllers\Api;

use App\Exports\GenericReportExport;
use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ){}

    public function summary(Request $request)
    {
        $this->validateFilters($request);

        return response()->json([
            'success'=>true,
            'data'=>$this->reportService->summary($request),
        ]);
    }

    public function branding()
    {
        return response()->json([
            'success'=>true,
            'data'=>$this->getBranding(false),
        ]);
    }

    public function module(Request $request,string $module)
    {
        $this->validateModule($module);

        $validated=$this->validateFilters($request);

        $perPage=min(
            (int)($validated['per_page']??15),
            100
        );

        $query=$this->reportService
            ->query($module,$request);

        return response()->json([
            'success'=>true,
            'data'=>$query->paginate($perPage),
            'meta'=>$this->reportService->meta($module),
        ]);
    }

    public function export(
        Request $request,
        string $module,
        string $format
    ){
        $this->validateModule($module);
        $this->validateFormat($format);
        $this->validateFilters($request);

        $query=$this->reportService
            ->query($module,$request);

        $defaultLimit=$format==='pdf'?5000:20000;
        $settingKey=$format==='pdf'
            ?'report_pdf_export_max_rows'
            :'report_excel_export_max_rows';

        $maxRows=max(
            100,
            min(
                (int)setting($settingKey,$defaultLimit),
                50000
            )
        );

        $total=(clone $query)->count();

        if($total>$maxRows){
            return response()->json([
                'success'=>false,
                'message'=>"This export contains {$total} rows. Narrow the filters to {$maxRows} rows or fewer before exporting.",
            ],422);
        }

        $records=$query
            ->limit($maxRows)
            ->get();

        $report=$this->reportService
            ->definition($module,$records);

        $branding=$this->getBranding();

        $filename=$module
            .'-report-'
            .now()->format('Y-m-d-His');

        return match($format){
            'pdf'=>$this->exportPdf(
                $filename,
                $report,
                $branding,
                $request
            ),

            'xlsx'=>$this->exportExcel(
                $filename,
                $report,
                $branding,
                $request
            ),
        };
    }

    protected function exportPdf(
        string $filename,
        array $report,
        array $branding,
        Request $request
    ){
        /*
        |--------------------------------------------------------------------------
        | Default mPDF Config
        |--------------------------------------------------------------------------
        */

        $defaultConfig=(new ConfigVariables())
            ->getDefaults();

        $fontDirs=$defaultConfig['fontDir'];

        $defaultFontConfig=(new FontVariables())
            ->getDefaults();

        $fontData=$defaultFontConfig['fontdata'];

        /*
        |--------------------------------------------------------------------------
        | Custom Font Directory
        |--------------------------------------------------------------------------
        */

        $fontDir=storage_path('fonts');

        $tempDir=storage_path('app/mpdf-temp');

        if(!is_dir($tempDir)){
            mkdir(
                $tempDir,
                0755,
                true
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Font Files
        |--------------------------------------------------------------------------
        */

        $notoSansRegular=
            $fontDir
            .DIRECTORY_SEPARATOR
            .'NotoSans-Regular.ttf';

        $notoSansBold=
            $fontDir
            .DIRECTORY_SEPARATOR
            .'NotoSans-Bold.ttf';

        $notoBanglaRegular=
            $fontDir
            .DIRECTORY_SEPARATOR
            .'NotoSansBengali-Regular.ttf';

        $notoBanglaBold=
            $fontDir
            .DIRECTORY_SEPARATOR
            .'NotoSansBengali-Bold.ttf';

        /*
        |--------------------------------------------------------------------------
        | English Font
        |--------------------------------------------------------------------------
        */

        if(is_file($notoSansRegular)){
            $fontData['notosans']=[
                'R'=>'NotoSans-Regular.ttf',
            ];

            if(is_file($notoSansBold)){
                $fontData['notosans']['B']=
                    'NotoSans-Bold.ttf';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Bangla Font
        |--------------------------------------------------------------------------
        |
        | useOTL is important for Bangla conjuncts, vowel marks and shaping.
        |
        */

        if(is_file($notoBanglaRegular)){
            $fontData['notosansbengali']=[
                'R'=>'NotoSansBengali-Regular.ttf',
                'useOTL'=>0xFF,
            ];

            if(is_file($notoBanglaBold)){
                $fontData['notosansbengali']['B']=
                    'NotoSansBengali-Bold.ttf';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | mPDF Configuration
        |--------------------------------------------------------------------------
        */

        $config=[
            'mode'=>'utf-8',

            'format'=>'A4-L',

            'orientation'=>'L',

            'margin_left'=>8,
            'margin_right'=>8,
            'margin_top'=>8,

            /*
            |--------------------------------------------------------------------------
            | Footer Space
            |--------------------------------------------------------------------------
            |
            | This reserves enough room for the fixed footer on every page.
            |
            */

            'margin_bottom'=>15,
            'margin_footer'=>5,

            'tempDir'=>$tempDir,

            'fontDir'=>array_merge(
                $fontDirs,
                [$fontDir]
            ),

            'fontdata'=>$fontData,

            /*
            |--------------------------------------------------------------------------
            | Automatic Script Detection
            |--------------------------------------------------------------------------
            |
            | English uses Noto Sans.
            | Bangla data can automatically fall back to Bengali font.
            |
            */

            'autoScriptToLang'=>true,
            'autoLangToFont'=>true,
        ];

        /*
        |--------------------------------------------------------------------------
        | Default Font
        |--------------------------------------------------------------------------
        */

        if(is_file($notoSansRegular)){
            $config['default_font']='notosans';
        }

        $mpdf=new Mpdf($config);

        /*
        |--------------------------------------------------------------------------
        | PDF Metadata
        |--------------------------------------------------------------------------
        */

        $mpdf->SetTitle(
            $report['title']
            .' - '
            .$branding['name']
        );

        $mpdf->SetAuthor(
            $branding['name']
        );

        $mpdf->SetCreator(
            config('app.name')
        );

        /*
        |--------------------------------------------------------------------------
        | Render PDF Blade
        |--------------------------------------------------------------------------
        */

        $html=view(
            'reports.pdf',
            [
                'report'=>$report,
                'organization'=>$branding,
                'from'=>$request->input('from'),
                'to'=>$request->input('to'),
            ]
        )->render();

        /*
        |--------------------------------------------------------------------------
        | Write PDF
        |--------------------------------------------------------------------------
        */

        $mpdf->WriteHTML($html);

        /*
        |--------------------------------------------------------------------------
        | Download
        |--------------------------------------------------------------------------
        */

        $content=$mpdf->Output(
            '',
            Destination::STRING_RETURN
        );

        return response(
            $content,
            200,
            [
                'Content-Type'=>'application/pdf',

                'Content-Disposition'=>
                    'attachment; filename="'
                    .$filename
                    .'.pdf"',

                'Content-Length'=>
                    strlen($content),

                'Cache-Control'=>
                    'private, no-store, no-cache, must-revalidate',

                'Pragma'=>'no-cache',
            ]
        );
    }

    protected function exportExcel(
        string $filename,
        array $report,
        array $branding,
        Request $request
    ){
        return Excel::download(
            new GenericReportExport(
                rows:$report['rows'],
                headings:$report['headings'],
                reportTitle:$report['title'],
                organizationName:$branding['name'],
                logoPath:$branding['logo_path'],
                from:$request->input('from'),
                to:$request->input('to')
            ),
            "{$filename}.xlsx"
        );
    }

    protected function getBranding(
        bool $withPath=true
    ): array{
        /*
        |--------------------------------------------------------------------------
        | Organization Name
        |--------------------------------------------------------------------------
        */

        $name=setting(
            'organization_name',

            setting(
                'company_name',

                config(
                    'app.name',
                    'Association Management'
                )
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Organization Logo
        |--------------------------------------------------------------------------
        */

        $logo=setting(
            'site_logo',

            setting(
                'organization_logo',

                setting(
                    'logo',
                    null
                )
            )
        );

        $logoPath=null;
        $logoUrl=null;

        if($logo){
            $logo=str_replace(
                '\\',
                '/',
                trim((string)$logo)
            );

            $logo=ltrim(
                $logo,
                '/'
            );

            /*
            |--------------------------------------------------------------------------
            | storage/... Path
            |--------------------------------------------------------------------------
            */

            if(str_starts_with(
                $logo,
                'storage/'
            )){
                $relative=substr(
                    $logo,
                    strlen('storage/')
                );

                if(
                    Storage::disk('public')
                        ->exists($relative)
                ){
                    $logoPath=
                        Storage::disk('public')
                            ->path($relative);

                    $logoUrl=
                        Storage::disk('public')
                            ->url($relative);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Public Disk Relative Path
            |--------------------------------------------------------------------------
            */

            if(
                !$logoPath&&
                Storage::disk('public')
                    ->exists($logo)
            ){
                $logoPath=
                    Storage::disk('public')
                        ->path($logo);

                $logoUrl=
                    Storage::disk('public')
                        ->url($logo);
            }

            /*
            |--------------------------------------------------------------------------
            | Public Directory
            |--------------------------------------------------------------------------
            */

            if(
                !$logoPath&&
                is_file(
                    public_path($logo)
                )
            ){
                $logoPath=
                    public_path($logo);

                $logoUrl=
                    asset($logo);
            }
        }

        return[
            'name'=>(string)$name,

            'logo_url'=>$logoUrl,

            'logo_path'=>
                $withPath
                    ?$logoPath
                    :null,
        ];
    }

    protected function validateModule(
        string $module
    ): void{
        abort_unless(
            in_array(
                $module,
                ReportService::MODULES,
                true
            ),
            404,
            'Invalid report module.'
        );
    }

    protected function validateFormat(
        string $format
    ): void{
        abort_unless(
            in_array(
                $format,
                [
                    'pdf',
                    'xlsx',
                ],
                true
            ),
            404,
            'Invalid export format.'
        );
    }

    protected function validateFilters(
        Request $request
    ): array{
        return $request->validate([
            'search'=>
                'nullable|string|max:150',

            'status'=>
                'nullable|string|max:50',

            'from'=>
                'nullable|date',

            'to'=>
                'nullable|date|after_or_equal:from',

            'per_page'=>
                'nullable|integer|min:5|max:100',
        ]);
    }
}