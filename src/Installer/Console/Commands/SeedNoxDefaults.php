<?php

namespace Nox\Framework\Installer\Console\Commands;

use Illuminate\Console\Command;
use Nox\Framework\Auth\Models\User;
use Nox\Framework\Module\Models\Module;
use Nox\Framework\Theme\Models\Theme;
use Silber\Bouncer\BouncerFacade;
use Silber\Bouncer\Database\Role;
use Spatie\Activitylog\Models\Activity;

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

        BouncerFacade::forbidEveryone()->to('delete', Role::find(1));
        BouncerFacade::forbidEveryone()->to('delete', Role::find(3));
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

        $abilities = [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];

        $models = [
            User::class,
            Role::class,
            Activity::class,
            Module::class,
            Theme::class
        ];

        foreach ($models as $model) {
            foreach ($abilities as $ability) {
                BouncerFacade::allow($admin)->to($ability, $model);
            }
        }
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
