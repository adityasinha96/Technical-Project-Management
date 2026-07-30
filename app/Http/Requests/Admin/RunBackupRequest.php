<?php

namespace App\Http\Requests\Admin;

use App\Enums\BackupType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RunBackupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(
            'backups.run'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'backup_type' => [
                'required',
                Rule::enum(
                    BackupType::class
                ),
            ],
        ];
    }
}