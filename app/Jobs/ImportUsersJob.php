<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ImportUsersJob implements ShouldQueue
{
    use Queueable;

    protected array $users;

    /**
     * Create a new job instance.
     */
    public function __construct(array $users)
    {
        $this->users = $users;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $data = array_merge($this->users, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            User::insert($data);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar usuários: ' . $e->getMessage());
        }
    }
}
