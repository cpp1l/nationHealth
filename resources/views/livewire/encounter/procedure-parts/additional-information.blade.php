@php
    $procedureErrorPath = $context === 'encounter' ? 'form.procedures.*' : 'form.procedure';
@endphp

<fieldset class="fieldset">
    <legend class="legend">
        {{ __('forms.additional_info') }}
    </legend>

    {{-- Procedure information source --}}
    <div class="flex gap-20 md:mb-5 mb-4">
        <h2 class="default-p font-bold">{{ __('patients.information_source') }}</h2>

        <div class="flex items-center">
            <input @change="
                        modalProcedure.primarySource = true;
                        modalProcedure.reportOriginCode = '';
                        modalProcedure.reportOriginText = '';
                "
                x-model.boolean="modalProcedure.primarySource"
                id="performer"
                type="radio"
                value="true"
                name="primarySource"
                class="default-radio"
                :checked="modalProcedure.primarySource === true"
            >
            <label for="performer" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                {{ __('patients.performer') }}
            </label>
        </div>

        <div class="flex items-center">
            <input @change="modalProcedure.primarySource = false"
                x-model.boolean="modalProcedure.primarySource"
                id="patient"
                type="radio"
                value="false"
                name="primarySource"
                class="default-radio"
                :checked="modalProcedure.primarySource === false"
            >
            <label for="patient" class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">
                {{ __('patients.other_source') }}
            </label>
        </div>
    </div>

    {{-- When the performer is chosen --}}
    <div x-show="modalProcedure.primarySource === true" class="form-row-2">
        <div class="form-group group">
            <input type="text"
                   name="procedurePerformer"
                   id="procedurePerformer"
                   class="input peer"
                   placeholder=" "
                   autocomplete="off"
                   disabled
                   value="{{ $employeeFullName }}"
            >
            <label for="procedurePerformer" class="label">
                {{ __('patients.doctor_who_performed') }}
            </label>
        </div>
    </div>

    {{-- When the other source is choosen  --}}
    <div x-show="modalProcedure.primarySource === false">
        <div class="form-row-modal">
            <div>
                <select class="input-select peer"
                        x-model="modalProcedure.reportOriginCode"
                        id="reportOrigin"
                        type="text"
                        required
                >
                    <option value="" selected>{{ __('forms.select') }} {{ mb_strtolower(__('patients.source_link')) }} *</option>
                    @foreach($this->dictionaries['eHealth/report_origins'] as $key => $reportOrigin)
                        <option value="{{ $key }}">{{ $reportOrigin }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Start effective period datetime --}}
    <div x-show="modalProcedure.status === 'completed'" x-cloak>
        <div class="form-row-3">
            <div class="form-group group">
                <div class="datepicker-wrapper">
                    <input x-model="modalProcedure.performedPeriodStartDate"
                        datepicker-max-date="{{ now()->format(config('app.date_format')) }}"
                        type="text"
                        name="performedPeriodStartDate"
                        id="performedPeriodStartDate"
                        class="datepicker-input with-leading-icon input peer"
                        placeholder=" "
                        required
                        autocomplete="off"
                    >
                    <label for="performedPeriodStartDate" class="wrapped-label">
                        {{ __('patients.procedure_start_date_and_time') }}
                    </label>

                    @error($procedureErrorPath . '.performedPeriodStartDate')
                        <p class="text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group group !w-1/2" onclick="document.getElementById('performedPeriodStartTime').showPicker()">
                <div class="relative flex items-center">
                    @icon('mingcute-time-fill', 'svg-input left-2.5')
                    <input x-model="modalProcedure.performedPeriodStartTime"
                        @input="$event.target.blur()"
                        datepicker-max-date="{{ now()->format(config('app.date_format')) }}"
                        type="time"
                        name="performedPeriodStartTime"
                        id="performedPeriodStartTime"
                        class="input peer !pl-10"
                        autocomplete="off"
                        required
                    >
                </div>

                @error($procedureErrorPath . '.performedPeriodStartTime')
                    <p class="text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- End effective period datetime --}}
    <div x-show="modalProcedure.status === 'completed'" x-cloak>
        <div class="form-row-3">
            <div class="form-group group">
                <div class="datepicker-wrapper">
                    <input x-model="modalProcedure.performedPeriodEndDate"
                        datepicker-max-date="{{ now()->format(config('app.date_format')) }}"
                        type="text"
                        name="performedPeriodEndDate"
                        id="performedPeriodEndDate"
                        class="datepicker-input with-leading-icon input peer"
                        placeholder=" "
                        required
                        autocomplete="off"
                    >
                    <label for="performedPeriodEndDate" class="wrapped-label">
                        {{ __('patients.procedure_end_date_and_time') }}
                    </label>

                    @error($procedureErrorPath . '.performedPeriodEndDate')
                        <p class="text-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group group !w-1/2" onclick="document.getElementById('performedPeriodEndTime').showPicker()">
                <div class="relative flex items-center">
                    @icon('mingcute-time-fill', 'svg-input left-2.5')
                    <input x-model="modalProcedure.performedPeriodEndTime"
                        @input="$event.target.blur()"
                        datepicker-max-date="{{ now()->format(config('app.date_format')) }}"
                        type="time"
                        name="performedPeriodEndTime"
                        id="performedPeriodEndTime"
                        class="input peer !pl-10"
                        autocomplete="off"
                        required
                    >
                </div>

                @error($procedureErrorPath . '.performedPeriodEndTime')
                    <p class="text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Note --}}
    <div class="form-row">
        <div>
            <label for="note" class="label-modal">
                {{ __('patients.notes') }}
            </label>
            <div>
                <textarea rows="4"
                          x-model="modalProcedure.note"
                          id="note"
                          name="note"
                          class="textarea"
                          placeholder="{{ __('forms.write_comment_here') }}"
                ></textarea>
            </div>
        </div>
    </div>

    <div class="form-row-2">
        <div class="w-full max-w-107.5">
            <p class="label-modal mb-2 block text-sm">
                {{ __('equipments.label') }}
            </p>

            <div class="space-y-4">
                <template x-for="(usedReference, index) in modalProcedure.usedReferences" :key="index">
                    <div class="flex items-end gap-3">
                        <div class="flex-1">
                            <template x-if="!modalProcedure.divisionId">
                                <div class="form-group group">
                                    <input type="text"
                                        class="input peer"
                                        placeholder=" "
                                        disabled
                                    >
                                    <label class="label">
                                        {{ __('equipments.search') }}
                                    </label>
                                </div>
                            </template>

                            @foreach($equipmentOptionsByDivision as $divisionUuid => $options)
                                <div x-show="modalProcedure.divisionId === @js($divisionUuid)" x-cloak>
                                    <x-forms.combobox
                                        class="w-full"
                                        model="usedReference"
                                        modelKey="id"
                                        :options="$options"
                                        bindValue="uuid"
                                        bindParam="name"
                                        :label="__('equipments.search')"
                                    />
                                </div>
                            @endforeach

                            <template x-if="modalProcedure.divisionId && !Object.keys(@js($equipmentOptionsByDivision)).includes(modalProcedure.divisionId)">
                                <p class="text-xs text-gray-500 mt-1">
                                    Немає доступного обладнання для обраного місця надання послуг
                                </p>
                            </template>
                        </div>

                        <button
                            type="button"
                            @click.prevent="removeUsedReference(index)"
                            class="shrink-0 text-error hover:opacity-80"
                        >
                            @icon('delete', 'w-5 h-5')
                        </button>
                    </div>
                </template>
            </div>

            @error($procedureErrorPath . '.usedReferences.*.id')
                <p class="text-error mt-2">{{ $message }}</p>
            @enderror

            <button type="button" @click.prevent="addUsedReference()" class="item-add mt-4">
                {{ __('equipments.add') }}
            </button>
        </div>
    </div>
</fieldset>
