<?php

namespace App\Console\Commands;

use App\Models\Spa;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CreateAdminCommand extends Command
{
    public const PASSWORD_MIN = 12;

    protected $signature = 'spa:create-admin {--email=} {--name=} {--spa= : Existing spa slug}';

    protected $description = 'Create an owner account for an existing spa using a hidden password prompt';

    public function handle(): int
    {
        $spa = Spa::where('slug', $this->option('spa'))->first();
        if (! $spa) {
            $this->error('Spa not found. Supply --spa with an existing tenant slug.');

            return self::FAILURE;
        }
        $data = ['email' => mb_strtolower(trim((string) $this->option('email'))), 'name' => trim((string) $this->option('name'))];
        $validator = Validator::make($data, [
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'name' => ['required', 'string', 'max:100'],
        ]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        if (! $this->input->isInteractive()) {
            $this->error('Run without --no-interaction so the password can be read securely.');

            return self::FAILURE;
        }
        $password = $this->secret('Password (minimum '.self::PASSWORD_MIN.' characters)');
        $confirmation = $this->secret('Confirm password');
        if (! is_string($password) || mb_strlen($password) < self::PASSWORD_MIN || $password !== $confirmation) {
            $this->error('Passwords must match and contain at least '.self::PASSWORD_MIN.' characters.');

            return self::FAILURE;
        }
        DB::transaction(function () use ($spa, $data, $password) {
            $user = User::create([...$data, 'password' => Hash::make($password), 'email_verified_at' => now()]);
            $spa->users()->syncWithoutDetaching([$user->id => ['role' => 'owner', 'status' => 'active']]);
        });
        $this->info('Admin '.$data['email'].' created.');

        return self::SUCCESS;
    }
}
