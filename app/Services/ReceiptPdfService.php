<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Generates single-page PDF receipts (subscription payments, loan
 * repayments, share purchases) using the same mPDF/font setup as
 * the report exports, but in a compact portrait layout.
 */
class ReceiptPdfService
{
    public function download(
        array $branding,
        array $receipt,
        string $filename
    ){
        $content=$this->render($branding,$receipt);

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

    protected function render(
        array $branding,
        array $receipt
    ): string{
        [$mpdf]=$this->buildMpdf('A5','P');

        $mpdf->SetTitle(
            $receipt['title']
            .' - '
            .$branding['name']
        );

        $mpdf->SetAuthor(
            $branding['name']
        );

        $mpdf->SetCreator(
            config('app.name')
        );

        $html=view(
            'receipts.receipt',
            [
                'receipt'=>$receipt,
                'organization'=>$branding,
            ]
        )->render();

        $mpdf->WriteHTML($html);

        $content=$mpdf->Output(
            '',
            Destination::STRING_RETURN
        );

        unset($mpdf,$html);

        return $content;
    }

    /**
     * Same as download(), but for multi-line journal vouchers
     * (account / particulars / debit / credit rows) instead of the
     * single-item receipt layout used by payments and repayments.
     */
    public function downloadJournalVoucher(
        array $branding,
        array $voucher,
        string $filename
    ){
        $content=$this->renderJournalVoucher($branding,$voucher);

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

    protected function renderJournalVoucher(
        array $branding,
        array $voucher
    ): string{
        [$mpdf,]=$this->buildMpdf('A4','P');

        $mpdf->SetTitle(
            $voucher['title']
            .' - '
            .$branding['name']
        );

        $mpdf->SetAuthor(
            $branding['name']
        );

        $mpdf->SetCreator(
            config('app.name')
        );

        $html=view(
            'receipts.journal-voucher',
            [
                'voucher'=>$voucher,
                'organization'=>$branding,
            ]
        )->render();

        $mpdf->WriteHTML($html);

        $content=$mpdf->Output(
            '',
            Destination::STRING_RETURN
        );

        unset($mpdf,$html);

        return $content;
    }

    /**
     * Shared mPDF bootstrap (fonts + temp dir) used by both the
     * compact receipt layout and the journal voucher layout.
     */
    protected function buildMpdf(
        string $format='A5',
        string $orientation='P'
    ): array{
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

        if(is_file($notoSansRegular)){
            $fontData['notosans']=[
                'R'=>'NotoSans-Regular.ttf',
            ];

            if(is_file($notoSansBold)){
                $fontData['notosans']['B']=
                    'NotoSans-Bold.ttf';
            }
        }

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

            'format'=>$format,

            'orientation'=>$orientation,

            'margin_left'=>10,
            'margin_right'=>10,
            'margin_top'=>10,
            'margin_bottom'=>14,
            'margin_footer'=>5,

            'tempDir'=>$tempDir,

            'fontDir'=>array_merge(
                $fontDirs,
                [$fontDir]
            ),

            'fontdata'=>$fontData,

            'autoScriptToLang'=>true,
            'autoLangToFont'=>true,
        ];

        if(is_file($notoSansRegular)){
            $config['default_font']='notosans';
        }

        return [new Mpdf($config)];
    }

    /**
     * Same branding resolution used for report PDF exports, kept
     * here so receipt downloads don't depend on ReportController.
     */
    public function branding(
        bool $withPath=true
    ): array{
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

            'address'=>(string)setting(
                'organization_address',
                ''
            ),

            'phone'=>(string)setting(
                'organization_phone',
                ''
            ),

            'email'=>(string)setting(
                'organization_email',
                ''
            ),

            'website'=>(string)setting(
                'organization_website',
                ''
            ),
        ];
    }
}