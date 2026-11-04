<?php

namespace Tests\Feature;

use App\Mail\MahasiswaPdfMail;
use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use App\Notifications\MahasiswaChanged;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MahasiswaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(Prodi $prodi, array $overrides = []): array
    {
        return array_merge([
            'nim' => '202412001',
            'nama' => 'Siti Rahmawati',
            'jenis_kelamin' => 'Perempuan',
            'tanggal_lahir' => '2005-03-12',
            'alamat' => 'Jl. Merdeka 1, Bontang',
            'telepon' => '081234567890',
            'email' => 'siti@example.com',
            'prodi_id' => $prodi->id,
        ], $overrides);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('mahasiswa.index'))->assertRedirect(route('login'));
    }

    public function test_unverified_user_is_redirected_to_verification_notice(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get(route('mahasiswa.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_search_matches_nim_or_nama_case_insensitively(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Nurul Laksita', 'nim' => '202412010']);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Budi Santoso', 'nim' => '202412099']);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Andi Wijaya', 'nim' => '202412050']);

        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.index', ['search' => 'nurul']))
            ->assertOk()
            ->assertSee('Nurul Laksita')
            ->assertDontSee('Budi Santoso');

        $this->get(route('mahasiswa.index', ['search' => '412099']))
            ->assertSee('Budi Santoso')
            ->assertDontSee('Andi Wijaya');
    }

    public function test_index_filters_by_prodi_and_jenis_kelamin(): void
    {
        [$ti, $si] = Prodi::factory()->count(2)->create();
        Mahasiswa::factory()->for($ti)->create(['nama' => 'Ani TI Perempuan', 'jenis_kelamin' => 'Perempuan']);
        Mahasiswa::factory()->for($ti)->create(['nama' => 'Bayu TI Laki', 'jenis_kelamin' => 'Laki-laki']);
        Mahasiswa::factory()->for($si)->create(['nama' => 'Citra SI Perempuan', 'jenis_kelamin' => 'Perempuan']);

        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.index', ['prodi_id' => $ti->id, 'jenis_kelamin' => 'Perempuan']))
            ->assertSee('Ani TI Perempuan')
            ->assertDontSee('Bayu TI Laki')
            ->assertDontSee('Citra SI Perempuan');
    }

    public function test_index_sorts_by_whitelisted_column_and_ignores_unknown_column(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Zaki Akbar']);
        Mahasiswa::factory()->for($prodi)->create(['nama' => 'Anisa Putri']);

        $this->actingAs(User::factory()->create())
            ->get(route('mahasiswa.index', ['sort' => 'nama', 'direction' => 'asc']))
            ->assertSeeInOrder(['Anisa Putri', 'Zaki Akbar']);

        $this->get(route('mahasiswa.index', ['sort' => 'password; drop table', 'direction' => 'asc']))
            ->assertOk();
    }

    public function test_index_query_count_does_not_grow_with_rows(): void
    {
        $user = User::factory()->create();
        $prodis = Prodi::factory()->count(3)->create();
        Mahasiswa::factory()->for($prodis[0])->create();

        $hitung = function () use ($user): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($user)->get(route('mahasiswa.index'))->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        $satuBaris = $hitung();

        foreach ($prodis as $prodi) {
            Mahasiswa::factory()->count(3)->for($prodi)->create();
        }

        $this->assertSame($satuBaris, $hitung());
    }

    public function test_store_rejects_empty_payload_with_messages(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), [])
            ->assertSessionHasErrors([
                'nim' => 'Kolom NIM wajib diisi.',
                'nama',
                'jenis_kelamin',
                'prodi_id',
            ]);

        $this->assertDatabaseCount('mahasiswas', 0);
    }

    public function test_store_rejects_duplicate_nim_and_unknown_jenis_kelamin(): void
    {
        $prodi = Prodi::factory()->create();
        Mahasiswa::factory()->for($prodi)->create(['nim' => '202412001']);

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.store'), $this->payload($prodi, ['jenis_kelamin' => 'Lainnya']))
            ->assertSessionHasErrors(['nim', 'jenis_kelamin']);
    }

    public function test_store_creates_mahasiswa_and_notifies_only_opted_in_users(): void
    {
        Notification::fake();
        $prodi = Prodi::factory()->create();
        $operator = User::factory()->create(['name' => 'Operator Satu']);
        $optedOut = User::factory()->create(['notify_mahasiswa_changes' => false]);

        $this->actingAs($operator)
            ->post(route('mahasiswa.store'), $this->payload($prodi))
            ->assertRedirect(route('mahasiswa.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mahasiswas', ['nim' => '202412001', 'prodi_id' => $prodi->id]);
        Notification::assertSentTo($operator, MahasiswaChanged::class, fn (MahasiswaChanged $n) => $n->aksi === 'ditambahkan'
            && $n->mahasiswa['nim'] === '202412001'
            && $n->mahasiswa['prodi'] === $prodi->nama_prodi
            && $n->oleh === 'Operator Satu');
        Notification::assertNotSentTo($optedOut, MahasiswaChanged::class);
    }

    public function test_update_keeps_own_nim_without_unique_error(): void
    {
        $prodi = Prodi::factory()->create();
        $mahasiswa = Mahasiswa::factory()->for($prodi)->create(['nim' => '202412001']);

        $this->actingAs(User::factory()->create())
            ->put(route('mahasiswa.update', $mahasiswa), $this->payload($prodi, ['nama' => 'Nama Baru']))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('mahasiswa.index'));

        $this->assertSame('Nama Baru', $mahasiswa->fresh()->nama);
    }

    public function test_destroy_deletes_mahasiswa_and_sends_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $mahasiswa = Mahasiswa::factory()->create(['nim' => '202412055']);

        $this->actingAs($user)
            ->delete(route('mahasiswa.destroy', $mahasiswa))
            ->assertRedirect(route('mahasiswa.index'));

        $this->assertSoftDeleted($mahasiswa);
        Notification::assertSentTo($user, MahasiswaChanged::class, fn (MahasiswaChanged $n) => $n->aksi === 'dihapus'
            && $n->mahasiswa['nim'] === '202412055');
    }

    public function test_pdf_list_and_detail_are_streamed_as_pdf(): void
    {
        $mahasiswa = Mahasiswa::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get(route('mahasiswa.pdf', ['prodi_id' => $mahasiswa->prodi_id]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->get(route('mahasiswa.pdf.show', $mahasiswa))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_email_pdf_queues_report_with_active_filters_to_own_address(): void
    {
        Mail::fake();
        $prodi = Prodi::factory()->create();
        $user = User::factory()->create(['email' => 'operator@example.com']);

        $this->actingAs($user)
            ->post(route('mahasiswa.pdf.email', ['prodi_id' => $prodi->id, 'search' => 'nur']))
            ->assertRedirect()
            ->assertSessionHas('success');

        Mail::assertQueued(MahasiswaPdfMail::class, fn (MahasiswaPdfMail $mail) => $mail->hasTo('operator@example.com')
            && (int) $mail->filters['prodi_id'] === $prodi->id
            && $mail->filters['search'] === 'nur'
            && $mail->mahasiswa === null);
    }

    public function test_email_pdf_detail_queues_to_given_address(): void
    {
        Mail::fake();
        $mahasiswa = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.pdf.show.email', $mahasiswa), ['email' => 'kaprodi@example.com'])
            ->assertSessionHas('success');

        Mail::assertQueued(MahasiswaPdfMail::class, fn (MahasiswaPdfMail $mail) => $mail->hasTo('kaprodi@example.com')
            && $mail->mahasiswa->is($mahasiswa));
    }

    public function test_email_pdf_rejects_invalid_address(): void
    {
        Mail::fake();
        $mahasiswa = Mahasiswa::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('mahasiswa.pdf.show.email', $mahasiswa), ['email' => 'bukan-email'])
            ->assertSessionHasErrors('email');

        Mail::assertNothingQueued();
    }

    public function test_pdf_mail_attaches_rendered_pdf(): void
    {
        $mahasiswa = Mahasiswa::factory()->create(['nim' => '202412077']);
        $mail = new MahasiswaPdfMail('Administrator', mahasiswa: $mahasiswa);

        $attachments = $mail->attachments();

        $this->assertCount(1, $attachments);
        $this->assertStringStartsWith('biodata-202412077-', $attachments[0]->as);
        $data = $attachments[0]->attachWith(fn () => null, fn ($data) => $data());
        $this->assertStringStartsWith('%PDF', $data);
    }
}
