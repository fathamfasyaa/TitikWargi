<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\ReportCategory;
use App\Enums\ReportSeverity;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        // Midday, so "today" never changes in the middle of a test.
        $this->travelTo(today()->setTime(12, 0));
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reports.create'))->assertRedirect(route('login'));
        $this->post(route('reports.store'), $this->validData())->assertRedirect(route('login'));

        $this->assertDatabaseCount('reports', 0);
    }

    public function test_user_sees_the_report_form(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('reports.create'));

        $response->assertOk()->assertSee('id="report-form"', escape: false);
    }

    public function test_banned_user_sees_the_reason_instead_of_the_form(): void
    {
        $response = $this->actingAs(User::factory()->banned()->create())->get(route('reports.create'));

        $response->assertOk()
            ->assertSee('Akun Anda sedang diblokir')
            ->assertDontSee('id="report-form"', escape: false);
    }

    public function test_banned_user_cannot_send_a_report(): void
    {
        $response = $this->actingAs(User::factory()->banned()->create())
            ->post(route('reports.store'), $this->validData());

        $response->assertForbidden();
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_valid_report_is_saved_with_its_location_and_photos(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('reports.store'), $this->validData([
            'photos' => [
                UploadedFile::fake()->image('a.jpg', 800, 600),
                UploadedFile::fake()->image('b.png', 600, 800),
            ],
        ]));

        $response->assertRedirect(route('home'))->assertSessionHas('status');

        $report = Report::withCoordinates()->with('photos')->sole();
        $this->assertTrue($report->user->is($user));
        $this->assertSame(ReportCategory::Pothole, $report->category);
        $this->assertSame(ReportSeverity::Severe, $report->severity);
        $this->assertSame('Jl. Siliwangi', $report->address);
        $this->assertEqualsWithDelta(-6.817, $report->latitude, 0.000001);
        $this->assertEqualsWithDelta(107.142, $report->longitude, 0.000001);

        $this->assertCount(2, $report->photos);
        foreach ($report->photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
            $this->assertStringEndsWith('.jpg', $photo->path);
        }
    }

    public function test_saved_photo_is_resized_and_has_no_exif_metadata(): void
    {
        $this->actingAs(User::factory()->create())->post(route('reports.store'), $this->validData([
            'photos' => [$this->photoWithExif(2400, 1200)],
        ]));

        $photo = Report::sole()->photos()->sole();
        $contents = Storage::disk('public')->get($photo->path);

        // The longest side is scaled down to 1600 px.
        $this->assertSame([1600, 800], [$photo->width, $photo->height]);
        $this->assertSame([1600, 800], array_slice(getimagesizefromstring($contents), 0, 2));

        // The EXIF block and its content are gone.
        $this->assertStringNotContainsString('Exif', $contents);
        $this->assertStringNotContainsString('SECRET-GPS', $contents);
    }

    public function test_report_without_photos_is_rejected_with_a_message(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), $this->validData(['photos' => []]));

        $response->assertSessionHasErrors(['photos' => 'Foto wajib diisi.']);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_with_more_than_three_photos_is_rejected(): void
    {
        $photos = array_map(fn () => UploadedFile::fake()->image('photo.jpg'), range(1, 4));

        $response = $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), $this->validData(['photos' => $photos]));

        $response->assertSessionHasErrors(['photos' => 'Foto maksimal 3 buah.']);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_report_outside_cianjur_is_rejected(): void
    {
        // Central Bandung.
        $response = $this->actingAs(User::factory()->create())
            ->post(route('reports.store'), $this->validData(['latitude' => -6.9175, 'longitude' => 107.6191]));

        $response->assertSessionHasErrors(['longitude' => 'Lokasi harus berada di wilayah Cianjur.']);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_sixth_report_of_the_day_is_forbidden(): void
    {
        $user = User::factory()->create();
        Report::factory()->for($user)->count(5)->create();

        $this->actingAs($user)->get(route('reports.create'))
            ->assertSee('Anda sudah membuat 5 laporan hari ini');

        $this->actingAs($user)->post(route('reports.store'), $this->validData())
            ->assertForbidden();

        $this->assertDatabaseCount('reports', 5);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'photos' => [UploadedFile::fake()->image('photo.jpg', 800, 600)],
            'latitude' => -6.817,
            'longitude' => 107.142,
            'category' => ReportCategory::Pothole->value,
            'severity' => ReportSeverity::Severe->value,
            'address' => 'Jl. Siliwangi',
            'description' => 'Lubang dalam di lajur kiri.',
        ], $overrides);
    }

    /**
     * A real JPEG with an EXIF block (APP1) that contains a marker text.
     */
    private function photoWithExif(int $width, int $height): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        ob_start();
        imagejpeg($image);
        $jpeg = ob_get_clean();

        // Minimal little-endian TIFF header with an empty IFD, followed by the marker.
        $exifData = "Exif\0\0"."II*\0\x08\0\0\0"."\0\0"."\0\0\0\0".'SECRET-GPS';
        $app1 = "\xFF\xE1".pack('n', strlen($exifData) + 2).$exifData;

        // Insert APP1 right after the JPEG start marker (FFD8).
        $path = tempnam(sys_get_temp_dir(), 'exif').'.jpg';
        file_put_contents($path, substr($jpeg, 0, 2).$app1.substr($jpeg, 2));

        return new UploadedFile($path, 'with-exif.jpg', 'image/jpeg', null, true);
    }
}
