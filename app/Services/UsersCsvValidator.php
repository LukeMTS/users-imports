<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UsersCsvValidator
{
    public function validate(\Illuminate\Http\UploadedFile $file): array
    {
        $errors = [];
        $users = [];
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_map('trim', $rows[0]);
        unset($rows[0]);

        foreach ($rows as $index => $row) {
            $data = array_combine($header, $row);

            $validator = Validator::make($data, [
                'name' => 'required|string|min:3|max:255',
                'email' => 'required|email|unique:users,email',
                'birthdate' => 'required|date_format:Y-m-d',
            ]);
    
            if ($validator->fails()) {
                $errors['linha ' . ($index + 1)] = $validator->errors()->all();
            } else {
                $users[] = array_merge($data, ['password' => Hash::make(Str::random(10))]);
            }
        }

        return [
            'success' => empty($errors),
            'errors' => $errors, 
            'data' => $users
        ];
    }
}
