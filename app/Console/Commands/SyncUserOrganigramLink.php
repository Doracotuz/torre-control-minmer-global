<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\OrganigramMember;

class SyncUserOrganigramLink extends Command
{
    protected $signature = 'app:sync-user-organigram';
    protected $description = 'Sincroniza los usuarios con los miembros del organigrama basándose en el email.';

    public function handle()
    {
        $this->info('Iniciando sincronización...');
        $members = OrganigramMember::whereNull('user_id')->get();
        $updatedCount = 0;

        foreach ($members as $member) {
            $user = User::where('email', $member->email)->first();
            if ($user) {
                $member->user_id = $user->id;
                $member->save();
                $updatedCount++;
                $this->line(' > Miembro "' . $member->name . '" enlazado al usuario "' . $user->name . '".');
            }
        }

        $this->info("Sincronización completada. Se actualizaron {$updatedCount} registros.");
        return 0;
    }
}