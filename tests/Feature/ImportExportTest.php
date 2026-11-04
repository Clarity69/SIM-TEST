<?php

namespace Tests\Feature;

use App\Exports\MahasiswaExport;
use App\Exports\MahasiswaTemplateExport;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use App\Notifications\MahasiswaBulkChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private function csv(string $isi): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('mahasiswa.csv', $isi);
    }

    private function prodiTi(): Prodi
    {
        return Prodi::factory()->create(['kode_prodi' => '55201', 'singkatan' => 'TI', 'nama_prodi' => 'Teknik Informatika']);
    }

    public function test_upload_validates_each_row_and_shows_preview(): void
    {
        $this->prodiTi();
        Mahasiswa::factory()->create(['nim' => '202412099']);
        $user = User::factory()->create();

        $csv = "nim,nama,jenis_kelamin,tanggal_lahir,prodi,angkatan,status,telepon,email,alamat\n"
            ."202512001,Ahmad Fauzi,L,2006-01-02,TI,,Aktif,81234567890,ahmad@example.com,Bontang\n"
            ."202512002,Bunga Lestari,P,,55201,2025,,,,\n"
            ."202412099,Rudi Hartono,L,,TI,2025,Aktif,,,\n"
            ."202512004,Sari Dewi,P,,XX,2025,Aktif,,,\n"
            ."202512001,Duplikat,L,,TI,2025,Aktif,,,\n";

        $response = $this->actingAs($user)->post(route('mahasiswa.import.upload'), ['file' => $this->csv($csv)]);
        $response->assertRedirect();

        $this->actingAs($user)->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('2 baris valid')
            ->assertSee('3 baris bermasalah')
            ->assertSee('NIM sudah digunakan.')
            ->assertSee('Program studi tidak dikenali.')
            ->assertSee('NIM ganda di file (sama dengan baris 2).');
    }

    public function test_confirming_import_inserts_valid_rows_and_notifies_admins(): void
    {
        Notification::fake();
        $prodi = $this->prodiTi();
        $admin = User::factory()->admin()->create();
        $operator = User::factory()->create(['name' => 'Operator Impor']);

        $csv = "nim,nama,jenis_kelamin,prodi,telepon\n202512001,Ahmad Fauzi,L,TI,81234567890\n202512002,Bunga Lestari,Perempuan,teknik informatika,\n";
        $preview = $this->actingAs($operator)->post(route('mahasiswa.import.upload'), ['file' => $this->csv($csv)])->headers->get('Location');
        $token = basename($preview);

        $this->actingAs($operator)->post(route('mahasiswa.import.store', $token))->assertRedirect($preview);

        $ahmad = Mahasiswa::where('nim', '202512001')->sole();
        $this->assertSame('Laki-laki', $ahmad->jenis_kelamin);
        $this->assertSame($prodi->id, $ahmad->prodi_id);
        $this->assertSame(2025, $ahmad->angkatan);         // dari 4 digit awal NIM
        $this->assertSame('Aktif', $ahmad->status);        // default
        $this->assertSame('081234567890', $ahmad->telepon); // nol di depan dikembalikan
        $this->assertSame('Perempuan', Mahasiswa::where('nim', '202512002')->value('jenis_kelamin'));

        Notification::assertSentTo($admin, MahasiswaBulkChanged::class, fn ($n) => $n->jumlah === 2 && $n->oleh === 'Operator Impor');
        $this->actingAs($operator)->get($preview)->assertSee('2 mahasiswa berhasil diimpor');
    }

    public function test_import_session_belongs_to_its_uploader(): void
    {
        $this->prodiTi();
        $preview = $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.import.upload'), ['file' => $this->csv("nim,nama,jenis_kelamin,prodi\n202512001,A,L,TI\n")])
            ->headers->get('Location');

        $this->actingAs(User::factory()->create())->get($preview)->assertNotFound();
    }

    public function test_file_without_expected_headers_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.import.upload'), ['file' => $this->csv("kolom1,kolom2\na,b\n")])
            ->assertSessionHasErrors('file');
    }

    public function test_template_and_filtered_export_downloads(): void
    {
        Excel::fake();
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('mahasiswa.import.template'));
        Excel::assertDownloaded('template-impor-mahasiswa.xlsx', fn (MahasiswaTemplateExport $export) => count($export->sheets()) === 2);

        $prodi = $this->prodiTi();
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Masuk Filter', 'status' => 'Aktif']);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Tidak Masuk', 'status' => 'Lulus']);

        $this->actingAs($user)->get(route('mahasiswa.export', ['status' => 'Aktif', 'format' => 'csv']));
        Excel::assertDownloaded('data-mahasiswa-'.now()->format('Ymd-His').'.csv', function (MahasiswaExport $export) {
            $nama = $export->query()->pluck('nama')->all();

            return $nama === ['Masuk Filter'];
        });
    }

    public function test_bulk_status_change_and_bulk_delete(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $admin = User::factory()->admin()->create();
        [$a, $b, $c] = Mahasiswa::factory()->count(3)->create(['status' => 'Aktif'])->all();

        $this->actingAs($user)->post(route('mahasiswa.bulk'), ['aksi' => 'status', 'status' => 'Lulus', 'ids' => [$a->id, $b->id]])
            ->assertSessionHas('success');
        $this->assertSame(['Lulus', 'Lulus', 'Aktif'], [$a->fresh()->status, $b->fresh()->status, $c->fresh()->status]);

        $this->actingAs($user)->post(route('mahasiswa.bulk'), ['aksi' => 'hapus', 'ids' => [$a->id, $c->id]])
            ->assertSessionHas('success');
        $this->assertSoftDeleted($a);
        $this->assertSoftDeleted($c);
        $this->assertNotSoftDeleted($b);
        $this->assertSame($user->id, Mahasiswa::withTrashed()->find($a->id)->deleted_by);
        Notification::assertSentTo($admin, MahasiswaBulkChanged::class, fn ($n) => $n->jumlah === 2);
    }

    public function test_bulk_export_contains_only_selected_rows(): void
    {
        Excel::fake();
        [$a, $b] = Mahasiswa::factory()->count(2)->create()->all();

        $this->actingAs(User::factory()->create())->post(route('mahasiswa.bulk'), ['aksi' => 'export', 'ids' => [$b->id]]);

        Excel::assertDownloaded('mahasiswa-terpilih-'.now()->format('Ymd-His').'.xlsx', fn (MahasiswaExport $export) => $export->query()->pluck('id')->all() === [$b->id]);
    }

    public function test_bulk_requires_selection(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.bulk'), ['aksi' => 'hapus', 'ids' => []])
            ->assertSessionHasErrors('ids');
    }
}
