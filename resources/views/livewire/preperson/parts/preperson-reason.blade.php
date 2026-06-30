@use(App\Enums\Preperson\UnidentifiedReason)

<fieldset class="fieldset">
    <legend class="legend">
        {{ __('patients.unidentified_reason') }}
    </legend>

    <div
        class="mb-6 p-6 rounded-xl bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 flex flex-col gap-3">
        <div class="flex items-center gap-2">
            @icon('alert-circle', 'w-5 h-5 text-red-600 dark:text-red-400')
            <h4 class="font-bold text-red-600 dark:text-red-400 text-lg">
                {{ __('patients.unidentified_warning_title') }}
            </h4>
        </div>
        <div class="text-red-500 dark:text-red-300 text-sm leading-relaxed whitespace-pre-line">
            {{ __('patients.unidentified_warning_text') }}
        </div>
    </div>

    <div class="form-row-2">
        <div class="form-group">
            <select
                x-model="unidentifiedReason"
                name="reason"
                id="reason"
                class="input-select peer @error('form.reasonContext.unidentifiedReason') input-error @enderror"
                required
            >
                <option value="" selected>{{ __('forms.select') }}</option>
                @foreach(UnidentifiedReason::options() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <label for="reason" class="label">
                {{ __('patients.unidentified_reason') }}
            </label>

            @error('form.reasonContext.unidentifiedReason')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    </div>

    <!-- EMERGENCY_HOSPITALIZATION -->
    <div class="form-row-2" x-show="unidentifiedReason === 'EMERGENCY_HOSPITALIZATION'" x-cloak>
        <div class="form-group group">
            <div class="relative w-full">
                <input
                    wire:model="form.reasonContext.ambulanceCardNumber"
                    type="text"
                    name="ambulanceCardNumber"
                    id="ambulanceCardNumber"
                    class="input peer @error('form.reasonContext.ambulanceCardNumber') input-error @enderror"
                    placeholder=" "
                    autocomplete="off"
                />
                <label for="ambulanceCardNumber" class="label">
                    {{ __('patients.ambulance_card_number') }}
                </label>
                <button
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600"
                    x-show="$wire.form.reasonContext.ambulanceCardNumber"
                    @click="$wire.set('form.reasonContext.ambulanceCardNumber', '')"
                >
                    @icon('close', 'w-4 h-4')
                </button>
            </div>

            @error('form.reasonContext.ambulanceCardNumber')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    </div>

    <!-- POLICE_HOSPITALIZATION -->
    <div class="form-row-2" x-show="unidentifiedReason === 'POLICE_HOSPITALIZATION'" x-cloak>
        <div class="form-group group">
            <div class="relative w-full">
                <input
                    wire:model="form.reasonContext.policeReportId"
                    type="text"
                    name="policeReportId"
                    id="policeReportId"
                    class="input peer @error('form.reasonContext.policeReportId') input-error @enderror"
                    placeholder=" "
                    autocomplete="off"
                    :required="unidentifiedReason === 'POLICE_HOSPITALIZATION'"
                />
                <label for="policeReportId" class="label">
                    {{ __('patients.police_report_id') }}
                </label>
                <button
                    type="button"
                    class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600"
                    x-show="$wire.form.reasonContext.policeReportId"
                    @click="$wire.set('form.reasonContext.policeReportId', '')"
                >
                    @icon('close', 'w-4 h-4')
                </button>
            </div>

            @error('form.reasonContext.policeReportId')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>

        <div class="form-group group">
            <div class="datepicker-wrapper">
                <input
                    wire:model="form.reasonContext.policeReportDate"
                    datepicker-max-date="{{ now()->format(config('app.date_format')) }}"
                    type="text"
                    name="policeReportDate"
                    id="policeReportDate"
                    class="datepicker-input with-leading-icon input peer @error('form.reasonContext.policeReportDate') input-error @enderror"
                    placeholder=" "
                    autocomplete="off"
                    :required="unidentifiedReason === 'POLICE_HOSPITALIZATION'"
                />
                <label for="policeReportDate" class="wrapped-label">
                    {{ __('patients.police_report_date') }}
                </label>
            </div>

            @error('form.reasonContext.policeReportDate')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    </div>

    <!-- NEWBORN_WITHOUT_CERTIFICATE -->
    <div class="form-row-2" x-show="unidentifiedReason === 'NEWBORN_WITHOUT_CERTIFICATE'" x-cloak>
        <div class="form-group group">
            <div class="relative">
                @icon('clock', 'absolute left-2.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500 dark:text-gray-400 pointer-events-none')
                <input
                    wire:model="form.reasonContext.childBirthTime"
                    type="time"
                    name="childBirthTime"
                    id="childBirthTime"
                    class="with-leading-icon input peer @error('form.reasonContext.childBirthTime') input-error @enderror"
                    placeholder=" "
                    autocomplete="off"
                    :required="unidentifiedReason === 'NEWBORN_WITHOUT_CERTIFICATE'"
                />
                <label for="childBirthTime" class="wrapped-label">
                    {{ __('patients.child_birth_time') }}
                </label>
            </div>

            @error('form.reasonContext.childBirthTime')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    </div>

    <!-- OTHER_HOSPITALIZATION -->
    <div class="form-row-2" x-show="unidentifiedReason === 'OTHER_HOSPITALIZATION'" x-cloak>
        <div class="form-group group">
            <label for="unidentifiedOtherReason" class="label-secondary">
                {{ __('patients.unidentified_other_reason') }} *
            </label>
            <textarea
                wire:model="form.reasonContext.unidentifiedOtherReason"
                id="unidentifiedOtherReason"
                name="unidentifiedOtherReason"
                rows="4"
                class="textarea @error('form.reasonContext.unidentifiedOtherReason') input-error @enderror"
                placeholder="Текст для введення"
                autocomplete="off"
                :required="unidentifiedReason === 'OTHER_HOSPITALIZATION'"
            ></textarea>

            @error('form.reasonContext.unidentifiedOtherReason')
            <p class="text-error">
                {{ $message }}
            </p>
            @enderror
        </div>
    </div>
</fieldset>
