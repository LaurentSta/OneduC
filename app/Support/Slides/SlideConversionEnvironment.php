<?php

namespace App\Support\Slides;

use Illuminate\Support\Facades\Process;

class SlideConversionEnvironment
{
    public function sofficePath(): ?string
    {
        return $this->resolveBinary((string) config('services.slides.soffice_binary', 'soffice'));
    }

    public function pdfToCairoPath(): ?string
    {
        return $this->resolveBinary((string) config('services.slides.pdftocairo_binary', 'pdftocairo'));
    }

    /**
     * @return array{powerpoint_ready: bool, pdf_ready: bool, soffice_path: ?string, pdftocairo_path: ?string}
     */
    public function status(): array
    {
        $sofficePath = $this->sofficePath();
        $pdfToCairoPath = $this->pdfToCairoPath();

        return [
            'powerpoint_ready' => $sofficePath !== null && $pdfToCairoPath !== null,
            'pdf_ready' => $pdfToCairoPath !== null,
            'soffice_path' => $sofficePath,
            'pdftocairo_path' => $pdfToCairoPath,
        ];
    }

    private function resolveBinary(string $binary): ?string
    {
        $binary = trim($binary);

        if ($binary === '') {
            return null;
        }

        if (str_contains($binary, DIRECTORY_SEPARATOR)) {
            return is_file($binary) && is_executable($binary) ? $binary : null;
        }

        $result = Process::timeout(5)->run(['which', $binary]);
        $path = trim($result->output());

        return $result->successful() && $path !== '' ? strtok($path, PHP_EOL) : null;
    }
}
