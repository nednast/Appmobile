<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS DISTANCE');
        DB::unprepared('
            CREATE FUNCTION DISTANCE(lat1 FLOAT, lng1 FLOAT, lat2 FLOAT, lng2 FLOAT)
            RETURNS FLOAT
            DETERMINISTIC
            BEGIN
                RETURN ACOS(
                    SIN(RADIANS(lat1)) * SIN(RADIANS(lat2)) +
                    COS(RADIANS(lat1)) * COS(RADIANS(lat2)) * COS(RADIANS(lng1 - lng2))
                ) * 6371;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS DISTANCE');
    }
};