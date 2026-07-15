<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;
use Smalot\PdfParser\XObject\Image as PdfImageXObject;
use ZipArchive;

class ExtracteurTexteDocument
{
    private const MAX_IMAGES = 10;

    private const OFFICE_IMAGE_MIMES = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    public function extract(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $text = match ($extension) {
            'pdf' => $this->extractPdf($file),
            'docx' => $this->extractWord($file),
            'pptx' => $this->extractPowerPoint($file),
            'txt' => (string) file_get_contents($file->getRealPath()),
            default => throw new RuntimeException('Format de document non supporté.'),
        };

        $text = trim($text);
        if ($text === '') {
            throw new RuntimeException('Impossible d\'extraire du texte de ce document.');
        }

        return $text;
    }

    private function extractPdf(UploadedFile $file): string
    {
        $parser = new PdfParser;

        return $parser->parseFile($file->getRealPath())->getText();
    }

    /**
     * Extrait les images intégrées d'un document (PDF ou Word) sous forme de fichiers temporaires.
     * PDF : seules les images encodées en JPEG (filtre DCTDecode) sont supportées — les autres
     * encodages (FlateDecode brut, CCITTFax, JPX...) sont silencieusement ignorés, leur décodage
     * n'étant pas pris en charge par smalot/pdfparser.
     *
     * @return array<int, array{path: string, mime: string}>
     */
    public function extractImages(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match ($extension) {
            'pdf' => $this->extractImagesFromPdf($file),
            'docx' => $this->extractImagesFromWord($file),
            'pptx' => $this->extractImagesFromOffice($file, 'ppt/media/'),
            default => [],
        };
    }

    /**
     * @return array<int, array{path: string, mime: string}>
     */
    private function extractImagesFromPdf(UploadedFile $file): array
    {
        try {
            $pdf = (new PdfParser)->parseFile($file->getRealPath());
        } catch (\Throwable) {
            return [];
        }

        $images = [];
        $seen = [];

        foreach ($pdf->getPages() as $page) {
            foreach ($page->getXObjects() as $xobject) {
                if (count($images) >= self::MAX_IMAGES) {
                    break 2;
                }

                if (! $xobject instanceof PdfImageXObject) {
                    continue;
                }

                $objectId = spl_object_id($xobject);
                if (isset($seen[$objectId])) {
                    continue;
                }
                $seen[$objectId] = true;

                if ((string) $xobject->get('Filter') !== 'DCTDecode') {
                    continue;
                }

                $content = $xobject->getContent();
                if (! is_string($content) || $content === '') {
                    continue;
                }

                $path = tempnam(sys_get_temp_dir(), 'oneduc_img_').'.jpg';
                file_put_contents($path, $content);
                $images[] = ['path' => $path, 'mime' => 'image/jpeg'];
            }
        }

        return $images;
    }

    /**
     * @return array<int, array{path: string, mime: string}>
     */
    private function extractImagesFromWord(UploadedFile $file): array
    {
        return $this->extractImagesFromOffice($file, 'word/media/');
    }

    /**
     * Extrait les images intégrées d'une archive OOXML (docx ou pptx) sous le préfixe de dossier donné.
     *
     * @return array<int, array{path: string, mime: string}>
     */
    private function extractImagesFromOffice(UploadedFile $file, string $mediaPrefix): array
    {
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            return [];
        }

        $images = [];

        for ($i = 0; $i < $zip->numFiles && count($images) < self::MAX_IMAGES; $i++) {
            $name = $zip->getNameIndex($i);
            if (! is_string($name) || ! str_starts_with($name, $mediaPrefix)) {
                continue;
            }

            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (! isset(self::OFFICE_IMAGE_MIMES[$ext])) {
                continue;
            }

            $content = $zip->getFromIndex($i);
            if (! is_string($content) || $content === '') {
                continue;
            }

            $path = tempnam(sys_get_temp_dir(), 'oneduc_img_').'.'.$ext;
            file_put_contents($path, $content);
            $images[] = ['path' => $path, 'mime' => self::OFFICE_IMAGE_MIMES[$ext]];
        }

        $zip->close();

        return $images;
    }

    private function extractWord(UploadedFile $file): string
    {
        $phpWord = WordIOFactory::load($file->getRealPath(), 'Word2007');

        $text = '';
        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text .= $this->elementText($element)."\n";
            }
        }

        return $text;
    }

    private function elementText(mixed $element): string
    {
        if (method_exists($element, 'getText')) {
            $value = $element->getText();

            return is_string($value) ? $value : '';
        }

        if (method_exists($element, 'getElements')) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= $this->elementText($child).' ';
            }

            return $text;
        }

        return '';
    }

    private function extractPowerPoint(UploadedFile $file): string
    {
        $zip = new ZipArchive;
        if ($zip->open($file->getRealPath()) !== true) {
            throw new RuntimeException('Impossible de lire ce fichier PowerPoint.');
        }

        $slideFiles = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name) && preg_match('#^ppt/slides/slide(\d+)\.xml$#', $name, $matches)) {
                $slideFiles[(int) $matches[1]] = $name;
            }
        }
        ksort($slideFiles);

        $slides = [];
        foreach ($slideFiles as $name) {
            $xml = $zip->getFromName($name);
            if (is_string($xml) && $xml !== '') {
                $slides[] = $this->powerPointSlideText($xml);
            }
        }

        $zip->close();

        return implode("\n\n", array_filter($slides, fn ($slide) => $slide !== ''));
    }

    /**
     * Extrait le texte visible d'une diapositive à partir de son XML brut (ppt/slides/slideN.xml),
     * en regroupant les runs <a:t> par paragraphe <a:p> sans dépendre d'un parseur OOXML complet.
     */
    private function powerPointSlideText(string $xml): string
    {
        $paragraphs = [];

        if (preg_match_all('#<a:p>(.*?)</a:p>#s', $xml, $paragraphMatches)) {
            foreach ($paragraphMatches[1] as $paragraphXml) {
                preg_match_all('#<a:t[^>]*>(.*?)</a:t>#s', $paragraphXml, $runMatches);
                $line = trim(html_entity_decode(implode('', $runMatches[1]), ENT_QUOTES | ENT_XML1, 'UTF-8'));
                if ($line !== '') {
                    $paragraphs[] = $line;
                }
            }
        }

        return implode("\n", $paragraphs);
    }
}
