<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupVendorPasswords extends Command
{
    protected $signature = 'vendor:setup-passwords';

    protected $description = 'Setup default passwords for all vendors/subcons in internal_companies table';

    public function handle()
    {
        $vendors = DB::table('internal_companies')
            ->where('group_id', '!=', 58)
            ->get();

        if ($vendors->isEmpty()) {
            $this->error('No vendors found to setup passwords.');

            return 1;
        }

        $this->info("Setting up passwords for {$vendors->count()} vendors...\n");

        $defaultPassword = 'VendorPassword123'; // Default password for all vendors
        $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

        $updated = 0;
        foreach ($vendors as $vendor) {
            $success = DB::table('internal_companies')
                ->where('id', $vendor->id)
                ->update(['password_hash' => $hashedPassword]);

            if ($success) {
                $this->line("✓ {$vendor->company_name} - Password set");
                $updated++;
            }
        }

        $this->info("\n".str_repeat('=', 60));
        $this->info('Setup Complete!');
        $this->info("Updated: $updated vendors");
        $this->info("Default Password: $defaultPassword");
        $this->info('Vendors can now login using their company name and this password.');
        $this->info(str_repeat('=', 60));

        return 0;
    }
}
