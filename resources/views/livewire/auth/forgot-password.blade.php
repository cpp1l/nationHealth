<div class="fragment">
    <x-authentication-card>

        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
            {{ __('auth.login.forgot_password') }}
        </h2>

        <form wire:submit.prevent="sendPasswordResetLink">
            <!-- Email Address -->
            <div class="form-group group">
                <input
                    required
                    type="email"
                    autocomplete="off"
                    placeholder=" "
                    id="email"
                    wire:model="email"
                    aria-describedby="@error('email') hasEmailErrorHelp @enderror"
                    class="input @error('email') input-error border-red-500 focus:border-red-500 @enderror peer"
                />

                @error('email')
                    <p id="hasEmailErrorHelp" class="text-error">
                        {{ $message }}
                    </p>
                @enderror

                <p id="passwordResetEmailHelp" class="text-note">
                    {{ __('forms.reset_password_email') }}
                </p>

                <label for="email" class="label z-10">
                    {{ __('forms.email') }}
                </label>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button
                    type="submit"
                    id="submitButton"
                    class="default-button cursor-pointer w-full"
                >
                    {{ __('forms.get_link')  }}
                </button>
            </div>
        </form>

        <div class="mt-1 mb-0 text-left">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ __('forms.return_to_login') . ': ' }}
                <a
                    wire:navigate
                    href="{{ route('login') }}"
                    class="text-blue-400 hover:text-blue-700 dark:text-blue-600"
                >
                    {{ __('forms.to_enter') }}
                </a>
            </p>
        </div>

    </x-authentication-card>

    <x-forms.loading />
    <livewire:components.x-message :key="now()->timestamp" />
</div>
