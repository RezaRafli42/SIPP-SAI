<link rel="shortcut icon" href="images/logo-sai.png" />
<title>Register</title>
<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <img style="max-width: 20vh; margin: auto; margin-bottom: 20px"
                src="{{ asset('images/logo/logo-press-compress.png') }}" alt="Logo" />
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                    required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                    required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="role" value="{{ __('Role') }}" />
                <select id="role" class="block mt-1 w-full" name="role" required
                    style="background-color: #111827">
                    <option value="Director">{{ __('Director') }}</option>
                    <option value="Fleet Manager">{{ __('Fleet Manager') }}</option>
                    <option value="Purchasing Logistic Manager">{{ __('Purchasing Logistic Manager') }}</option>
                    <option value="Purchasing Logistic Supervisor">{{ __('Purchasing Logistic Supervisor') }}</option>
                    <option value="Port Engineer">{{ __('Port Engineer') }}</option>
                    <option value="Kapal">{{ __('Kapal') }}</option>
                    <option value="Purchasing Logistic Admin">{{ __('Purchasing Logistic Admin') }}</option>
                    <option value="Fleet Admin">{{ __('Fleet Admin') }}</option>
                    <option value="Material Control">{{ __('Material Control') }}</option>
                    <!-- Add more roles as needed -->
                </select>
            </div>

            <div class="mt-4">
                <x-label for="profile_photo" value="{{ __('Profile Photo') }}" />
                <x-input id="profile_photo" class="block mt-1 w-full" type="file" name="profile_photo" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />
                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                    'terms_of_service' =>
                                        '<a target="_blank" href="' .
                                        route('terms.show') .
                                        '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">' .
                                        __('Terms of Service') .
                                        '</a>',
                                    'privacy_policy' =>
                                        '<a target="_blank" href="' .
                                        route('policy.show') .
                                        '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">' .
                                        __('Privacy Policy') .
                                        '</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="flex items-center justify-end mt-4">
                {{-- <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                    href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a> --}}

                <x-button class="ms-4">
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
