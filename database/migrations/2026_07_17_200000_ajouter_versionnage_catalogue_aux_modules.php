<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table): void {
            $table->foreignId('created_by')->nullable()->after('formateur_id')->constrained('users')->nullOnDelete();
            $table->uuid('catalogue_key')->nullable()->after('is_trainer_authored')->index();
            $table->unsignedSmallInteger('version_number')->default(1)->after('catalogue_key');
            $table->string('publication_state', 20)->default('draft')->after('version_number')->index();
            $table->timestamp('published_at')->nullable()->after('publication_state');
            $table->foreignId('source_module_id')->nullable()->after('published_at')->constrained('modules')->nullOnDelete();
            $table->unique(['catalogue_key', 'version_number'], 'modules_catalogue_version_unique');
        });

        DB::table('modules')
            ->select(['id', 'formateur_id', 'is_trainer_authored', 'status', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->chunkById(200, function ($modules): void {
                foreach ($modules as $module) {
                    DB::table('modules')->where('id', $module->id)->update([
                        'created_by' => $module->is_trainer_authored ? $module->formateur_id : null,
                        'catalogue_key' => (string) Str::uuid(),
                        'version_number' => 1,
                        'publication_state' => $module->status ? 'published' : 'draft',
                        'published_at' => $module->status ? ($module->updated_at ?? $module->created_at ?? now()) : null,
                    ]);
                }
            });

        Schema::table('modules', function (Blueprint $table): void {
            $table->dropForeign(['formateur_id']);
        });

        Schema::table('modules', function (Blueprint $table): void {
            $table->foreignId('formateur_id')->nullable()->change();
            $table->foreign('formateur_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        DB::table('modules')->whereNull('formateur_id')->update([
            'formateur_id' => DB::table('users')->where('role', 'admin')->value('id')
                ?? DB::table('users')->where('role', 'formateur')->value('id'),
        ]);

        Schema::table('modules', function (Blueprint $table): void {
            $table->dropForeign(['formateur_id']);
            $table->dropUnique('modules_catalogue_version_unique');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['source_module_id']);
            $table->dropColumn([
                'created_by',
                'catalogue_key',
                'version_number',
                'publication_state',
                'published_at',
                'source_module_id',
            ]);
        });

        Schema::table('modules', function (Blueprint $table): void {
            $table->foreignId('formateur_id')->nullable(false)->change();
            $table->foreign('formateur_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
