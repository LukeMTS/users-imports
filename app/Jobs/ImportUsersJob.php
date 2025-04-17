<?php

namespace App\Jobs;

use App\Models\ImportsInfo;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportUsersJob implements ShouldQueue
{
    use Queueable;

    protected array $users;
    protected int $importId;
    /**
     * Create a new job instance.
     */
    public function __construct(array $users, int $importId)
    {
        $this->users = $users;
        $this->importId = $importId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $now = now()->format('Y-m-d H:i:s');

            $data = array_map(function ($user) use ($now) {
                return array_merge($user, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }, $this->users);

            User::insert($data);

            Log::info('Usuários importados com sucesso');

            ImportsInfo::where('id', $this->importId)->update([
                'status' => ImportsInfo::STATUS_COMPLETED,
                'updated_at' => $now,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar usuários: ' . $e->getMessage());

            ImportsInfo::where('id', $this->importId)->update([
                'status' => ImportsInfo::STATUS_FAILED,
                'updated_at' => $now,
            ]);
        }
    }
}
