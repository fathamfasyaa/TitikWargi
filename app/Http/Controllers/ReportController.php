<?php

namespace App\Http\Controllers;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Throwable;

class ReportController extends Controller
{
    /**
     * Show the report form.
     */
    public function create(): View
    {
        // If the user may not report (banned or daily limit), show the reason instead of the form.
        $permission = Gate::inspect('create', Report::class);

        return view('reports.create', [
            'deniedMessage' => $permission->denied() ? $permission->message() : null,
            'categories' => ReportCategory::cases(),
            'severities' => ReportSeverity::cases(),
            'area' => config('titikwargi.report_area'),
        ]);
    }

    /**
     * Save a new report with its photos.
     */
    public function store(StoreReportRequest $request): RedirectResponse
    {
        // Process all photos first, so a broken photo stops the report before anything is saved.
        $photos = array_map($this->preparePhoto(...), $request->file('photos'));

        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $photos, &$storedPaths) {
                $report = new Report($request->safe()->only(['category', 'severity', 'address', 'description']));
                $report->location = Report::pointFromCoordinates(
                    $request->float('latitude'),
                    $request->float('longitude'),
                );
                $report->user()->associate($request->user());
                $report->save();

                foreach ($photos as $photo) {
                    $path = 'reports/'.now()->format('Y/m').'/'.Str::uuid().'.jpg';
                    Storage::disk('public')->put($path, $photo['contents']);
                    $storedPaths[] = $path;

                    $report->photos()->create([
                        'path' => $path,
                        'width' => $photo['width'],
                        'height' => $photo['height'],
                    ]);
                }
            });
        } catch (Throwable $exception) {
            // The database changes were rolled back, so remove the files too.
            Storage::disk('public')->delete($storedPaths);

            throw $exception;
        }

        return redirect()->route('home')->with('status', 'Terima kasih! Laporan Anda sudah terkirim.');
    }

    /**
     * Resize the photo and encode it again as JPEG.
     *
     * Encoding again removes all metadata (EXIF), including the GPS position
     * and the phone model stored inside the original photo.
     *
     * @return array{contents: string, width: int, height: int}
     */
    private function preparePhoto(UploadedFile $file): array
    {
        $maxDimension = config('titikwargi.photo_max_dimension');

        try {
            // autoOrientation turns the photo upright before the metadata is removed.
            $image = ImageManager::usingDriver(GdDriver::class, autoOrientation: true, strip: true)
                ->decodePath($file->getRealPath());
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'photos' => 'Salah satu foto tidak bisa dibaca. Coba pilih foto lain.',
            ]);
        }

        $image->scaleDown(width: $maxDimension, height: $maxDimension);

        return [
            'contents' => (string) $image->encodeUsingFormat(Format::JPEG, quality: 80),
            'width' => $image->width(),
            'height' => $image->height(),
        ];
    }
}
