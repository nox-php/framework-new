<?php

namespace Nox\Framework\Installer\Console\Commands;

use Illuminate\Console\Command;
use Nox\Framework\Auth\Models\User;
use Silber\Bouncer\BouncerFacade;

class SeedNoxDefaults extends Command
{
    protected $signature = 'nox:seed';

    protected $description = 'Seed default database data for a Nox installation.';

    public function handle(): void
    {
        $this->createRolesAndAbilities();

        $this->components->info('Nox database data has been successfully seeded.');
    }

    protected function createRolesAndAbilities(): void
    {
        $this->createSuperAdminRole();
        $this->createAdminRole();
        $this->createUserRole();
    }

    protected function createSuperAdminRole(): void
    {
        $superAdmin = BouncerFacade::role()
            ->firstOrCreate([
                'name' => 'superadmin',
                'title' => 'Super Administrator',
            ]);

        BouncerFacade::allow($superAdmin)->everything();
    }

    protected function createAdminRole(): void
    {
        $admin = BouncerFacade::role()
            ->firstOrCreate([
                'name' => 'admin',
                'title' => 'Administrator',
            ]);

        BouncerFacade::allow($admin)->to('view_admin');

        BouncerFacade::allow($admin)->to('view', User::class);
        BouncerFacade::allow($admin)->to('view_any', User::class);
        BouncerFacade::allow($admin)->to('create', User::class);
        BouncerFacade::allow($admin)->to('update', User::class);
        BouncerFacade::allow($admin)->to('restore', User::class);
        BouncerFacade::allow($admin)->to('restore_any', User::class);
        BouncerFacade::allow($admin)->to('replicate', User::class);
        BouncerFacade::allow($admin)->to('reorder', User::class);
        BouncerFacade::allow($admin)->to('delete', User::class);
        BouncerFacade::allow($admin)->to('delete_any', User::class);
        BouncerFacade::allow($admin)->to('force_delete', User::class);
        BouncerFacade::allow($admin)->to('force_delete_any', User::class);
    }

    protected function createUserRole(): void
    {
        BouncerFacade::role()
            ->firstOrCreate([
                'name' => 'user',
                'title' => 'User',
            ]);
    }
}
