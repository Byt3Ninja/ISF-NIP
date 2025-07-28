<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		if( Schema::hasTable('organizations') )
		{
			Schema::table('organizations', function (Blueprint $table) {
				if( !Schema::hasColumn('organizations', 'short_code') )
				{
					$table->string('short_code', 10)->collation('utf8mb4_unicode_ci')->unique()->nullable()->default(null)->after('id');
				}
			});

			DB::table('organizations')->whereNull('short_code')->get()->each(function ($org) {
				do {
					$shortCode = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz0123456789'), 0, 5);
				} while (DB::table('organizations')->where('short_code', $shortCode)->exists());

				DB::table('organizations')->where('id', $org->id)->update(['short_code' => $shortCode]);
			});

			DB::unprepared("
				DROP TRIGGER IF EXISTS before_insert_organizations;
				
				CREATE TRIGGER before_insert_organizations
				BEFORE INSERT ON organizations
				FOR EACH ROW
				BEGIN
					DECLARE slug VARCHAR(10);
					DECLARE tries INT DEFAULT 0;

					label_retry: LOOP
						SET slug = LOWER(CONCAT(
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1),
							SUBSTRING('abcdefghijklmnopqrstuvwxyz0123456789', FLOOR(1 + RAND() * 36), 1)
						));

						IF NOT EXISTS (
							SELECT 1 FROM organizations WHERE short_code = slug COLLATE utf8mb4_unicode_ci
						) THEN
							SET NEW.short_code = slug;
							LEAVE label_retry;
						END IF;

						SET tries = tries + 1;
						IF tries > 10 THEN
							SIGNAL SQLSTATE '45000'
							SET MESSAGE_TEXT = 'Unable to generate unique short_code after 10 attempts';
						END IF;
					END LOOP;
				END
			");
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		if( Schema::hasTable('organizations') )
		{
			DB::unprepared("DROP TRIGGER IF EXISTS before_insert_organizations");
			Schema::table('organizations', function (Blueprint $table) {
				if( Schema::hasColumn('organizations', 'short_code') ) 
				{
					$table->dropUnique(['short_code']);
					$table->dropColumn('short_code');
				}
			});
		}
	}
};
