<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\File;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'role' => ['required', 'string', 'max:70'], // Add role validation
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $filename = null;

        if (isset($input['profile_photo'])) {
            // Get the uploaded file
            $file = $input['profile_photo'];

            // Check if it's an instance of UploadedFile
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $extension = $file->getClientOriginalExtension();
                $filename = $input['email'] . '.' . $extension;
                $path = public_path('images/uploads/user-photos/' . $filename);

                // Set maximum file size to 150KB (150,000 bytes)
                $maxFileSize = 150000; // 150KB

                // Get the file size
                $fileSize = $file->getSize();

                // Create new ImageManager instance with imagick driver
                $manager = new \Intervention\Image\ImageManager(['driver' => 'imagick']);

                if ($fileSize > $maxFileSize) {
                    // Compress and save the image
                    $image = $manager->make($file->getRealPath());
                    $image->resize(800, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 75); // Save with 75% quality
                } else {
                    // Save the image without compression
                    $file->move(public_path('images/uploads/user-photos'), $filename);
                }
            } else {
                throw new \Exception('Invalid file upload.');
            }
        }
        // dd($filename);

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => $input['role'],
            'profile_photo' => $filename,
        ]);
    }
}
