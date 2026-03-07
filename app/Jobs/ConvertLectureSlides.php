<?php

namespace App\Jobs;

use App\Models\ModuleLecture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

class ConvertLectureSlides implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 900;

    public function __construct(
        public int $lectureId,
        public string $uploadedFilePath
    ) {
    }

    public function handle(): void
    {
        $lecture = ModuleLecture::find($this->lectureId);

        if (!$lecture) {
            Storage::disk('local')->delete($this->uploadedFilePath);
            return;
        }

        $lecture->update([
            'slides_status' => 'processing',
            'slides_error' => null,
        ]);

        try {
            $sourcePath = Storage::disk('local')->path($this->uploadedFilePath);
            if (!is_file($sourcePath)) {
                throw new RuntimeException("Fichier source introuvable: {$this->uploadedFilePath}");
            }

            $extension = strtolower((string) pathinfo($sourcePath, PATHINFO_EXTENSION));
            if (!in_array($extension, ['ppt', 'pptx', 'pdf'], true)) {
                throw new RuntimeException("Format non supporté: .{$extension}");
            }

            $slidesRelativeDir = 'slides/lecture_' . $lecture->id;
            Storage::disk('public')->deleteDirectory($slidesRelativeDir);
            Storage::disk('public')->makeDirectory($slidesRelativeDir);
            $slidesAbsoluteDir = Storage::disk('public')->path($slidesRelativeDir);

            $pdfPath = $sourcePath;
            $generatedPdfPath = null;
            $libreOfficeProfileDir = null;
            $libreOfficeHomeDir = null;

            if (in_array($extension, ['ppt', 'pptx'], true)) {
                $tmpBase = storage_path('app/tmp/libreoffice');
                File::ensureDirectoryExists($tmpBase, 0755, true);

                $libreOfficeProfileDir = $tmpBase . DIRECTORY_SEPARATOR . 'profile_' . $lecture->id . '_' . Str::random(8);
                $libreOfficeHomeDir = $tmpBase . DIRECTORY_SEPARATOR . 'home_' . $lecture->id . '_' . Str::random(8);

                File::ensureDirectoryExists($libreOfficeProfileDir, 0755, true);
                File::ensureDirectoryExists($libreOfficeHomeDir . DIRECTORY_SEPARATOR . '.config', 0755, true);
                File::ensureDirectoryExists($libreOfficeHomeDir . DIRECTORY_SEPARATOR . '.cache', 0755, true);

                $profileUri = 'file://' . str_replace(DIRECTORY_SEPARATOR, '/', $libreOfficeProfileDir);

                $convertPptToPdf = Process::timeout(360)
                    ->env([
                        'HOME' => $libreOfficeHomeDir,
                        'XDG_CONFIG_HOME' => $libreOfficeHomeDir . DIRECTORY_SEPARATOR . '.config',
                        'XDG_CACHE_HOME' => $libreOfficeHomeDir . DIRECTORY_SEPARATOR . '.cache',
                        'SAL_USE_VCLPLUGIN' => 'gen',
                    ])
                    ->run([
                    'soffice',
                    '--headless',
                    '--nologo',
                    '--nodefault',
                    '--nolockcheck',
                    '--nofirststartwizard',
                    '-env:UserInstallation=' . $profileUri,
                    '--convert-to',
                    'pdf',
                    '--outdir',
                    $slidesAbsoluteDir,
                    $sourcePath,
                    ]);

                if (!$convertPptToPdf->successful()) {
                    $error = trim($convertPptToPdf->errorOutput()) ?: trim($convertPptToPdf->output());
                    throw new RuntimeException($error !== '' ? $error : 'Échec de conversion PPTX vers PDF.');
                }

                $baseName = pathinfo($sourcePath, PATHINFO_FILENAME);
                $expectedPdf = $slidesAbsoluteDir . DIRECTORY_SEPARATOR . $baseName . '.pdf';

                if (is_file($expectedPdf)) {
                    $generatedPdfPath = $expectedPdf;
                } else {
                    $pdfCandidates = glob($slidesAbsoluteDir . DIRECTORY_SEPARATOR . '*.pdf') ?: [];
                    if (count($pdfCandidates) !== 1) {
                        throw new RuntimeException('PDF intermédiaire introuvable après conversion PPTX.');
                    }
                    $generatedPdfPath = $pdfCandidates[0];
                }

                $pdfPath = $generatedPdfPath;
            }

            $convertPdfToImages = Process::timeout(360)->run([
                'pdftocairo',
                '-jpeg',
                '-r',
                '150',
                $pdfPath,
                $slidesAbsoluteDir . DIRECTORY_SEPARATOR . 'slide',
            ]);

            if (!$convertPdfToImages->successful()) {
                throw new RuntimeException(trim($convertPdfToImages->errorOutput()) ?: 'Échec de conversion PDF vers images.');
            }

            $rawSlideFiles = glob($slidesAbsoluteDir . DIRECTORY_SEPARATOR . 'slide-*.jpg') ?: [];
            natsort($rawSlideFiles);
            $rawSlideFiles = array_values($rawSlideFiles);

            if (empty($rawSlideFiles)) {
                throw new RuntimeException('Aucune image de slide générée.');
            }

            foreach ($rawSlideFiles as $index => $rawFile) {
                $targetName = 'slide_' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT) . '.jpg';
                $targetPath = $slidesAbsoluteDir . DIRECTORY_SEPARATOR . $targetName;

                if ($rawFile !== $targetPath) {
                    rename($rawFile, $targetPath);
                }
            }

            if ($generatedPdfPath && is_file($generatedPdfPath)) {
                @unlink($generatedPdfPath);
            }

            if ($libreOfficeProfileDir) {
                File::deleteDirectory($libreOfficeProfileDir);
            }
            if ($libreOfficeHomeDir) {
                File::deleteDirectory($libreOfficeHomeDir);
            }

            $lecture->update([
                'content_type' => 'slides',
                'slides_status' => 'ready',
                'slides_path' => $slidesRelativeDir,
                'slides_error' => null,
                'slides_converted_at' => now(),
                'slide_count' => count($rawSlideFiles),
            ]);
        } catch (Throwable $e) {
            $lecture->update([
                'slides_status' => 'failed',
                'slides_error' => Str::limit($e->getMessage(), 1000),
            ]);

            Log::error('Échec conversion slides', [
                'lecture_id' => $lecture->id,
                'uploaded_file' => $this->uploadedFilePath,
                'error' => $e->getMessage(),
            ]);
        } finally {
            Storage::disk('local')->delete($this->uploadedFilePath);
        }
    }
}
