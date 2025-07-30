<?php

namespace Database\Seeders;


use App\Models\Attorney;
use App\Models\EventType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use phpDocumentor\Reflection\DocBlock\Tags\Formatter\AlignFormatter;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $system_admin = \App\Models\User::create([
            'name' => 'Horus',
            'email' => 'horus@flcourts18.org',
            'password' => Hash::make('horus')
         ]);


        $system_admin_role = Role::create([
            'name' => 'System Admin',
            'guard_name' => 'web'
        ]);

        $ja = Role::create([
            'name' => 'JA',
            'guard_name' => 'web'
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $system_admin_role->id,
            'model_type' => 'App\Models\User',
            'model_id' => $system_admin->id
        ]);

        $osca_admin = \App\Models\User::create([
            'name' => 'Osca',
            'email' => 'osca@flcourts18.org',
            'password' => Hash::make('osca')
        ]);

        DB::table('model_has_roles')->insert([
            'role_id' => $system_admin_role->id,
            'model_type' => 'App\Models\User',
            'model_id' => $osca_admin->id
        ]);

        DB::table('permissions')->insert([
            'name' => 'modify attorneys',
            'guard_name' => 'web',
        ]);

        $permission = DB::table('permissions')->where('name','modify attorneys')->first();

        DB::table('role_has_permissions')->insert([
            'permission_id' => $permission->id,
            'role_id' => $system_admin_role->id,
        ]);

        if(env('APP_ENV') != 'local'){
            Artisan::call('ldap:import', [
                'provider' => 'users', '--no-interaction', '--filter' => "(mail=*@flcourts18.org)"
            ]);
        }

        // Import small tables from Legacy Data. See JacsImport command for more robust tables
        $this->call([
            CountySeeder::class,
            CourtTypeSeeder::class,
            HolidaySeeder::class,
            EventTypeSeeder::class,
            EventStatusSeeder::class
        ]);
    }
}
