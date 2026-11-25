<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PendingGIUnitProofTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('fleet_number');
            $table->timestamps();
        });
        Schema::create('spareparts', function (Blueprint $table) {
            $table->id();
            $table->string('part_number')->nullable();
            $table->string('description');
            $table->string('category');
            $table->string('unit');
            $table->string('rack_location');
            $table->integer('min_stock')->default(0);
            $table->integer('current_stock')->default(0);
            $table->integer('limit_high_usage')->default(10);
            $table->integer('limit_medium_usage')->default(5);
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->string('notifiable_type');
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('type');
            $table->string('status');
            $table->date('date');
            $table->foreignId('user_id');
            $table->foreignId('vehicle_id')->nullable();
            $table->string('requester_name')->nullable();
            $table->string('proof_image_url')->nullable();
            $table->string('proof_image_before_url')->nullable();
            $table->string('source_name')->nullable();
            $table->string('source_type')->nullable();
            $table->string('purchase_order')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id');
            $table->foreignId('sparepart_id');
            $table->integer('qty');
            $table->string('proof_image_url')->nullable();
            $table->string('proof_image_before_url')->nullable();
            $table->timestamps();
        });
        Schema::create('bast_sequences', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->unique();
            $table->integer('last_number')->default(0);
        });

        DB::table('users')->insert(['id' => 1, 'name' => 'Supervisor', 'username' => 'supervisor-db', 'password' => bcrypt('password'), 'role' => 'supervisor']);
        DB::table('vehicles')->insert(['id' => 1, 'fleet_number' => 'F-1']);
        DB::table('spareparts')->insert(['id' => 1, 'description' => 'Part A', 'category' => 'test', 'unit' => 'pcs', 'rack_location' => 'A1', 'current_stock' => 8]);
        DB::table('transactions')->insert(['id' => 1, 'invoice_no' => 'GI-1', 'type' => 'OUT', 'status' => 'pending', 'date' => '2026-11-24', 'user_id' => 1, 'vehicle_id' => 1]);
        DB::table('transaction_details')->insert([
            ['transaction_id' => 1, 'sparepart_id' => 1, 'qty' => 1],
            ['transaction_id' => 1, 'sparepart_id' => 1, 'qty' => 1],
        ]);

        $this->actingAs(\App\Models\User::findOrFail(1));
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('bast_sequences');
        Schema::dropIfExists('spareparts');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_pending_legacy_quantity_shows_one_before_after_pair_per_unit(): void
    {
        $this->get(route('transactions.pending'))->assertOk()->assertSee('2 pcs');
    }

    public function test_pending_gi_cannot_complete_without_a_proof_pair_for_every_unit(): void
    {
        $this->put(route('transactions.pending.update', 1), ['action' => 'verify'])
            ->assertSessionHasErrors();

        $this->assertDatabaseHas('transactions', ['id' => 1, 'status' => 'pending']);
        $this->assertDatabaseHas('transaction_details', ['id' => 1, 'qty' => 2]);
    }

    public static function proofUploads(): array
    {
        return [
            'before image for first unit' => ['proofs.0.before'],
            'after image for first unit' => ['proofs.0.after'],
            'before image for second unit' => ['proofs.1.before'],
            'after image for second unit' => ['proofs.1.after'],
        ];
    }

    #[DataProvider('proofUploads')]
    public function test_every_unit_requires_its_own_before_and_after_file(string $missing): void
    {
        $files = [
            'proofs' => [
                ['before' => $this->imageFile('before-1.jpg'), 'after' => $this->imageFile('after-1.jpg')],
                ['before' => $this->imageFile('before-2.jpg'), 'after' => $this->imageFile('after-2.jpg')],
            ],
        ];
        unset($files['proofs'][explode('.', $missing)[1]][explode('.', $missing)[2]]);

        $this->put(route('transactions.pending.update', 1), ['action' => 'verify', 'proofs' => array_values($files['proofs'])])
            ->assertSessionHasErrors("proofs." . explode('.', $missing)[1] . "." . explode('.', $missing)[2]);

        $this->assertDatabaseHas('transactions', ['id' => 1, 'status' => 'pending']);
    }

    public function test_legacy_quantity_is_split_into_unit_rows_after_all_proofs_upload(): void
    {
        $response = $this->put(route('transactions.pending.update', 1), [
            'action' => 'verify',
            'proofs' => [
                ['before' => $this->imageFile('before-1.jpg'), 'after' => $this->imageFile('after-1.jpg')],
                ['before' => $this->imageFile('before-2.jpg'), 'after' => $this->imageFile('after-2.jpg')],
            ],
        ]);

        $response->assertRedirect(route('transactions.pending'));
        $this->assertDatabaseHas('transactions', ['id' => 1, 'status' => 'completed']);
        $this->assertDatabaseCount('transaction_details', 2);
        $this->assertSame([1, 1], DB::table('transaction_details')->orderBy('id')->pluck('qty')->all());
        foreach (DB::table('transaction_details')->orderBy('id')->get() as $detail) {
            $this->assertNotEmpty($detail->proof_image_before_url);
            $this->assertNotEmpty($detail->proof_image_url);
        }
    }

    public function test_transaction_history_shows_one_header_and_expandable_detail_rows(): void
    {
        DB::table('transactions')->where('id', 1)->update(['status' => 'completed']);
        DB::table('transaction_details')->where('id', 1)->update([
            'qty' => 1,
            'proof_image_before_url' => 'installations/before.jpg',
            'proof_image_url' => 'installations/after.jpg',
        ]);
        Storage::disk('local')->put('installations/before.jpg', 'before');
        Storage::disk('local')->put('installations/after.jpg', 'after');

        $this->get(route('transactions.index', ['tab' => 'OUT']))
            ->assertOk()
            ->assertSee('GI-1')
            ->assertSee('View details (2)')
            ->assertSee('Part A')
            ->assertSee('1 pcs')
            ->assertSee(route('transactions.proof', ['detail' => 1, 'type' => 'before']))
            ->assertSee(route('transactions.proof', ['detail' => 1, 'type' => 'after']))
            ->assertSee('Before')
            ->assertSee('After');
    }

    public function test_installation_proofs_require_login_and_return_private_file(): void
    {
        DB::table('transactions')->where('id', 1)->update(['status' => 'completed']);
        DB::table('transaction_details')->where('id', 1)->update(['proof_image_before_url' => 'installations/before.jpg']);
        Storage::disk('local')->put('installations/before.jpg', $this->imageFile('proof.jpg')->getContent());

        auth()->logout();
        $this->get(route('transactions.proof', ['detail' => 1, 'type' => 'before']))->assertRedirect(route('login'));

        $this->actingAs(\App\Models\User::findOrFail(1))
            ->get(route('transactions.proof', ['detail' => 1, 'type' => 'before']))
            ->assertOk()
            ->assertHeader('content-type', 'image/jpeg');
    }

    public function test_legacy_history_details_fall_back_to_header_proof_images(): void
    {
        DB::table('transactions')->where('id', 1)->update([
            'status' => 'completed',
            'proof_image_before_url' => 'installations/legacy-before.jpg',
            'proof_image_url' => 'installations/legacy-after.jpg',
        ]);
        Storage::disk('local')->put('installations/legacy-before.jpg', $this->imageFile('legacy-before.jpg')->getContent());
        Storage::disk('local')->put('installations/legacy-after.jpg', $this->imageFile('legacy-after.jpg')->getContent());

        $this->get(route('transactions.index', ['tab' => 'OUT']))
            ->assertOk()
            ->assertSee(route('transactions.proof', ['detail' => 1, 'type' => 'before']))
            ->assertSee(route('transactions.proof', ['detail' => 1, 'type' => 'after']));

        $this->get(route('transactions.proof', ['detail' => 1, 'type' => 'before']))->assertOk()->assertHeader('content-type', 'image/jpeg');
        $this->get(route('transactions.proof', ['detail' => 1, 'type' => 'after']))->assertOk()->assertHeader('content-type', 'image/jpeg');
    }

    public function test_dashboard_recent_activity_groups_split_units_by_transaction_and_part(): void
    {
        DB::table('transactions')->where('id', 1)->update(['status' => 'completed']);
        DB::table('transaction_details')->insert([
            ['transaction_id' => 1, 'sparepart_id' => 1, 'qty' => 1],
            ['transaction_id' => 1, 'sparepart_id' => 1, 'qty' => 1],
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Part A')
            ->assertSee('4 pcs')
            ->assertSee('GI-1');
    }

    public function test_sparepart_history_groups_split_unit_rows_and_sums_quantity(): void
    {
        DB::table('transactions')->where('id', 1)->update(['status' => 'completed']);

        $this->get(route('spareparts.show', 1))
            ->assertOk()
            ->assertSee('2 pcs')
            ->assertSee('GI-1');
    }

    private function imageFile(string $name): \Illuminate\Http\Testing\File
    {
        return \Illuminate\Http\Testing\File::image($name, 20, 20);
    }
}
