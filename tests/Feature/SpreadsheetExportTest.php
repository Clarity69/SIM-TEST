<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class SpreadsheetExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_xlsx_exports_untrusted_values_as_text_and_preserves_leading_zeroes(): void
    {
        $student = Mahasiswa::factory()->create(['nim' => '00123456', 'nama' => '=1+1', 'telepon' => '081234567890', 'alamat' => '@SUM(1,1)']);

        $response = $this->actingAs(User::factory()->create())->get(route('mahasiswa.export'));

        $response->assertOk();
        $workbook = IOFactory::load($response->baseResponse->getFile()->getPathname());
        $sheet = $workbook->getActiveSheet();
        $this->assertSame($student->nim, $sheet->getCell('A2')->getValue());
        $this->assertSame('081234567890', $sheet->getCell('H2')->getValue());
        $this->assertSame('=1+1', $sheet->getCell('B2')->getValue());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('B2')->getDataType());
        $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('J2')->getDataType());
        $workbook->disconnectWorksheets();
    }

    public function test_csv_export_neutralizes_formula_prefixes_including_whitespace(): void
    {
        $prodi = Prodi::factory()->create();
        $values = ['=1+1', '+1+1', '-1+1', '@SUM(1,1)', ' =1+1', "\t=1+1", 'Nama Normal'];

        foreach ($values as $index => $value) {
            Mahasiswa::factory()->for($prodi)->create(['nim' => '20251200'.$index, 'nama' => $value]);
        }

        $response = $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.export', ['format' => 'csv', 'sort' => 'nim', 'direction' => 'asc']));

        $response->assertOk();
        $stream = fopen($response->baseResponse->getFile()->getPathname(), 'r');
        fgetcsv($stream, escape: '');

        foreach ($values as $index => $value) {
            $row = fgetcsv($stream, escape: '');
            $this->assertSame($index < 6 ? "'".$value : $value, $row[1]);
        }

        fclose($stream);
    }

    public function test_import_error_report_also_neutralizes_csv_formulas(): void
    {
        $this->actingAs(User::factory()->create());
        $upload = UploadedFile::fake()->createWithContent('mahasiswa.csv', "nim,nama,jenis_kelamin,prodi\n202512001,=1+1,L,TIDAK-ADA\n");
        $preview = $this->post(route('mahasiswa.import.upload'), ['file' => $upload])->headers->get('Location');

        $response = $this->get(route('mahasiswa.import.report', basename($preview)));

        $response->assertOk();
        $stream = fopen($response->baseResponse->getFile()->getPathname(), 'r');
        fgetcsv($stream, escape: '');
        $this->assertSame("'=1+1", fgetcsv($stream, escape: '')[2]);
        fclose($stream);
    }
}
