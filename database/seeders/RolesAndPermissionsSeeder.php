<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Generate permissions first
        Artisan::call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--no-interaction' => true,
        ]);

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $allPermissions = Permission::all();
        $superAdmin->syncPermissions($allPermissions);

        // 2. Pastor
        $pastor = Role::firstOrCreate(['name' => 'pastor']);
        $pastorPermissions = [
            'ViewAny:Sermon', 'View:Sermon', 'Create:Sermon', 'Update:Sermon', 'Delete:Sermon',
            'ViewAny:Event', 'View:Event', 'Create:Event', 'Update:Event', 'Delete:Event',
            'ViewAny:Announcement', 'View:Announcement', 'Create:Announcement', 'Update:Announcement', 'Delete:Announcement',
            'ViewAny:Page', 'View:Page', 'Create:Page', 'Update:Page', 'Delete:Page',
            'ViewAny:Gallery', 'View:Gallery', 'Create:Gallery', 'Update:Gallery', 'Delete:Gallery',
            'ViewAny:Campus', 'View:Campus', 'Create:Campus', 'Update:Campus', 'Delete:Campus',
            'ViewAny:Member', 'View:Member',
        ];
        $pastor->syncPermissions($pastorPermissions);

        // 3. Treasurer
        $treasurer = Role::firstOrCreate(['name' => 'treasurer']);
        $treasurerPermissions = [
            'ViewAny:GivingRecord', 'View:GivingRecord', 'Create:GivingRecord',
            'ViewAny:PaymentTransaction', 'View:PaymentTransaction',
            'ViewAny:Member', 'View:Member',
        ];
        $treasurer->syncPermissions($treasurerPermissions);

        // 4. Volunteer
        $volunteer = Role::firstOrCreate(['name' => 'volunteer']);
        $volunteerPermissions = [
            'ViewAny:Sermon', 'View:Sermon',
            'ViewAny:Event', 'View:Event',
            'ViewAny:Announcement', 'View:Announcement',
        ];
        $volunteer->syncPermissions($volunteerPermissions);
    }
}
