<?php

namespace App\Domains\ModulesFormateur\Support;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use RuntimeException;
use Smalot\PdfParser\Parser as PdfParser;

class ExtracteurTexteDocument
{
    public function extract(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        $text = match ($extension) {
            'pdf' => $this->extractPdf($file),
            'docx' => $this->extractWord($file),
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
}
