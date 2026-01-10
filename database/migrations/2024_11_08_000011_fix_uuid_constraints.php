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
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Add UUID validation function if it doesn't exist
        DB::statement("
            CREATE OR REPLACE FUNCTION is_valid_uuid(uuid_text TEXT)
            RETURNS BOOLEAN AS $$
            BEGIN
                RETURN uuid_text ~ '^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$';
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Add UUID casting helper function
        DB::statement("
            CREATE OR REPLACE FUNCTION safe_uuid_cast(input_text TEXT)
            RETURNS UUID AS $$
            BEGIN
                -- If it's already a valid UUID, return it
                IF is_valid_uuid(input_text) THEN
                    RETURN input_text::UUID;
                END IF;
                
                -- If it's a 32-character string without hyphens, format it
                IF length(input_text) = 32 AND input_text ~ '^[0-9a-f]{32}$' THEN
                    RETURN (
                        substr(input_text, 1, 8) || '-' ||
                        substr(input_text, 9, 4) || '-' ||
                        substr(input_text, 13, 4) || '-' ||
                        substr(input_text, 17, 4) || '-' ||
                        substr(input_text, 21, 12)
                    )::UUID;
                END IF;
                
                -- If it's numeric, generate a new UUID (this should be logged)
                IF input_text ~ '^[0-9]+$' THEN
                    RAISE WARNING 'Converting numeric ID % to UUID', input_text;
                    RETURN gen_random_uuid();
                END IF;
                
                -- Default: generate new UUID
                RAISE WARNING 'Invalid UUID format %, generating new UUID', input_text;
                RETURN gen_random_uuid();
            END;
            $$ LANGUAGE plpgsql;
        ");

        // Update services table to ensure all enterprise_id values are valid UUIDs
        if (Schema::hasTable('services')) {
            DB::statement("
                UPDATE services 
                SET enterprise_id = safe_uuid_cast(enterprise_id::TEXT)
                WHERE NOT is_valid_uuid(enterprise_id::TEXT);
            ");
        }

        // Update customer_orders table to ensure all enterprise_id values are valid UUIDs
        if (Schema::hasTable('customer_orders')) {
            DB::statement("
                UPDATE customer_orders 
                SET enterprise_id = safe_uuid_cast(enterprise_id::TEXT)
                WHERE NOT is_valid_uuid(enterprise_id::TEXT);
            ");
        }

        // Add check constraints to ensure UUID format
        if (Schema::hasTable('services')) {
            DB::statement("
                ALTER TABLE services 
                DROP CONSTRAINT IF EXISTS services_enterprise_id_uuid_check;
            ");
            
            DB::statement("
                ALTER TABLE services 
                ADD CONSTRAINT services_enterprise_id_uuid_check 
                CHECK (is_valid_uuid(enterprise_id::TEXT));
            ");
        }

        DB::statement("
            ALTER TABLE enterprises 
            DROP CONSTRAINT IF EXISTS enterprises_enterprise_id_uuid_check;
        ");
        
        DB::statement("
            ALTER TABLE enterprises 
            ADD CONSTRAINT enterprises_enterprise_id_uuid_check 
            CHECK (is_valid_uuid(enterprise_id::TEXT));
        ");

        // Add indexes for better performance on UUID columns
        if (Schema::hasTable('services')) {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_services_enterprise_id_text ON services USING btree ((enterprise_id::text));");
        }
        DB::statement("CREATE INDEX IF NOT EXISTS idx_enterprises_enterprise_id_text ON enterprises USING btree ((enterprise_id::text));");
        
        if (Schema::hasTable('customer_orders')) {
            DB::statement("CREATE INDEX IF NOT EXISTS idx_customer_orders_enterprise_id_text ON customer_orders USING btree ((enterprise_id::text));");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Drop the constraints
        DB::statement("ALTER TABLE services DROP CONSTRAINT IF EXISTS services_enterprise_id_uuid_check;");
        DB::statement("ALTER TABLE enterprises DROP CONSTRAINT IF EXISTS enterprises_enterprise_id_uuid_check;");
        
        // Drop the indexes
        DB::statement("DROP INDEX IF EXISTS idx_services_enterprise_id_text;");
        DB::statement("DROP INDEX IF EXISTS idx_enterprises_enterprise_id_text;");
        DB::statement("DROP INDEX IF EXISTS idx_customer_orders_enterprise_id_text;");
        
        // Drop the functions
        DB::statement("DROP FUNCTION IF EXISTS safe_uuid_cast(TEXT);");
        DB::statement("DROP FUNCTION IF EXISTS is_valid_uuid(TEXT);");
    }
};
