<?php

namespace Interritor\FormPdf\Service;

use Mpdf\MpdfException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\Fpdi;
use \Mpdf\Mpdf;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Core\Environment;
use Symfony\Component\Yaml\Yaml;

class PdfService
{
    const PDF_NAME = 'form.pdf';
    const PDF_TEMP_PREFIX = 'form-tempfile-';
    const PDF_TEMP_SUFFIX = '-generated';
    const DEFAULT_FONT = 'Helvetica';
    private StandaloneView $standaloneView;

    public function __construct(StandaloneView $standaloneView)
    {
        $this->standaloneView = $standaloneView;
    }

    /**
     * @param $pdfFile
     * @param $htmlFile
     * @param array $values
     * @return Mpdf
     * @throws MpdfException
     * @throws CrossReferenceException
     * @throws PdfParserException
     * @throws PdfTypeException
     */
    public function generate(
        $pdfFile,
        $values = [],
        $formLabel = "default"
    )
    {

        $path = Environment::getPublicPath() . sprintf('/fileadmin/pdf-config/%s_form-mapping.yaml', $formLabel);
        
        if (file_exists($path)) {
            $mapping = Yaml::parseFile($path);
        } else {
            throw new \RuntimeException(sprintf('Mapping file not found: %s',$path));
        }

        if (!$pdfFile) {
            return null;
        }


        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFile);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $pdf->AddPage();
            $templateId = $pdf->importPage($pageNo);
            $pdf->useTemplate($templateId);
        
            
            if (!empty($mapping[$pageNo])) {
                foreach ($mapping[$pageNo] as $item) {
                    $value = $values[$item['field']] ?? '';
                    if ($value) {
                        $font =  $item['font'] ?? self::DEFAULT_FONT;
                        $pdf->SetFont($font, '', $item['fontSize'] ?? 12);
                        $pdf->SetXY($item['x'] ?? 0, $item['y'] ?? 0);
                        $pdf->Write(10, $value);
                    }
                }
            }
        
        
        }
       
        return $pdf;

        //Dateispeichern
        //$pdf->Output('test.pdf', 'F');
    }

    /**
     * @param $htmlFile
     * @param $values
     * @return mixed
     */
    private function parse($htmlFile, $values)
    {
        $this->standaloneView->setFormat('html');

        $this->standaloneView->setTemplatePathAndFilename($htmlFile);
        $this->standaloneView->assignMultiple($values);
        return $this->standaloneView->render();
    }
}
