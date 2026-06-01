@php
    $searchQuery = $searchQuery ?? '';
    $searchResults = $searchResults ?? [];
@endphp

<x-dialog-drawer
    x-model="showMedicalDeviceSearchDrawer"
    noTeleport="true"
    topClass="top-[57px]"
    zIndex="42"
    customWidth="w-full sm:w-[calc(80%-15%)]"
    overlayWidth="80%"
    hasClose="true"
    onCloseClick="showMedicalDeviceSearchDrawer = false"
    title="{{ __('care-plan.medical_device_search') }}"
>
    <div x-data="{ showFilter: false }" class="flex flex-col h-full w-full">

    {{-- Search Input --}}
    <div class="mb-4">
        <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
                @icon('search-outline', 'w-5 h-5 text-gray-500')
            </div>
            <input type="text"
                   class="input peer ps-10 w-full"
                   placeholder="{{ __('care-plan.test_strips') }}"
                   wire:model.live.debounce.400ms="searchQuery"
                   wire:keydown.enter="searchMedicalDevices"
            />
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <button type="button" wire:click="searchMedicalDevices" class="button-primary flex items-center gap-2">
            @icon('search', 'w-4 h-4')
            <span>{{ __('forms.search') }}</span>
        </button>
        <button type="button" wire:click="$set('searchQuery', '')" class="button-primary-outline-red">
            {{ __('forms.reset_all_filters') }}
        </button>
        <button type="button"
                class="button-minor flex items-center gap-2"
                @click="showFilter = !showFilter"
        >
            @icon('adjustments', 'w-4 h-4')
            <span>{{ __('forms.additional_search_parameters') }}</span>
        </button>
    </div>

    {{-- Filters --}}
    <div x-show="showFilter" x-cloak x-transition class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="form-group group">
            <label class="label">
                {{ __('care-plan.medical_device_type') }}
            </label>
            <select class="input-select peer w-full">
                <option selected value="">{{ __('care-plan.glucose_test_reagent') }}</option>
            </select>
        </div>
        <div class="form-group group">
            <label class="label">
                {{ __('care-plan.medical_device_model_number') }}
            </label>
            <select class="input-select peer w-full">
                <option selected value="">{{ __('care-plan.yes') }}</option>
            </select>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="overflow-x-auto mb-6">
        <table class="w-full text-sm text-left">
            <thead class="thead-input">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">{{ __('care-plan.name') }}</th>
                    <th scope="col" class="px-4 py-3 font-medium">{{ __('care-plan.type') }}</th>
                    <th scope="col" class="px-4 py-3 font-medium">{{ __('care-plan.packaging') }}</th>
                    <th scope="col" class="px-4 py-3 font-medium">{{ __('care-plan.code') }}</th>
                    <th scope="col" class="px-4 py-3 font-medium text-right">Дія</th>
                </tr>
            </thead>
            <tbody>
                @forelse($searchResults as $device)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900 dark:text-white">{{ $device['name'] ?? $device['device_names'][0]['name'] ?? $device['description'] ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $device['type_name'] ?? $device['classification_type_name'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ is_string($device['packaging'] ?? null) ? $device['packaging'] : (is_string($device['package_description'] ?? null) ? $device['package_description'] : (is_array($device['packaging'] ?? null) ? json_encode($device['packaging']) : '-')) }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">
                            {{ $device['classification_type_code'] ?? $device['code'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="selectProduct({{ json_encode($device) }}, 'device_request')" class="button-primary-outline text-xs">
                                Обрати
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-400 italic">
                            @if(empty($searchQuery))
                                Введіть запит для пошуку медичних виробів
                            @else
                                Нічого не знайдено за запитом "{{ $searchQuery }}"
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        <button type="button"
                class="button-minor"
                @click="showMedicalDeviceSearchDrawer = false"
        >
            {{ __('forms.cancel') }}
        </button>
    </div>
    </div>
</x-dialog-drawer>
