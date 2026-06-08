@extends('livewire.auth.login-layout')

@section('showPassword')
    <div class="mt-6"
         x-show="isLocalAuth"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
    >
        <div class="form-group group">
            <input wire:model="password"
                   :required="isLocalAuth"
                   type="password"
                   placeholder=" "
                   autocomplete="off"
                   id="password"
                   aria-describedby="@error('password') hasPasswordErrorHelp @enderror"
                   class="input @error('password') input-error border-red-500 focus:border-red-500 @enderror peer"
            />

            @error('password')
                <p id="hasPasswordErrorHelp" class="text-error">
                    {{ $message }}
                </p>
            @enderror

            <label for="password" class="label z-10">
                {{ __('forms.password') }}
            </label>
        </div>
    </div>

    <div class="block mt-4">
        <div
            x-cloak
            x-show="!isSingleRoleAuth"
            class="form-group group"
        >
            <input
                x-model="isLocalAuth"
                type="checkbox"
                id="is_local_auth"
                class="default-checkbox text-blue-500 focus:ring-blue-300"
                :checked="isLocalAuth"
            >

            <label for="is_local_auth" class="ms-2 text-xs font-medium text-gray-500 dark:text-gray-300">
                {{ __('auth.login.register_legal_entity') }}
            </label>
        </div>

        <div
            x-cloak
            x-show="!isLocalAuth"
            class="form-group group mt-2"
        >
            <input
                x-model="isSingleRoleAuth"
                :checked="isSingleRoleAuth"
                type="checkbox"
                id="is_single_role_auth"
                class="default-checkbox text-blue-500 focus:ring-blue-300"
            >

            <label for="is_single_role_auth" class="ms-2 text-xs font-medium text-gray-500 dark:text-gray-300">
                {{ __('auth.login.single_role_auth') }}
            </label>
        </div>
    </div>
@endsection
