<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSolicitudRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $data = $this->isJson()
            ? $this->json()->all()
            : $this->all();

        $this->merge(array_merge($data, [
            'api_key' => $this->input('api_key') ?? $this->query('api_key'),
        ]));
    }

    protected function isWebhookTest(): bool
    {
        return $this->header('x-hook-test') === 'true';
    }

    public function rules(): array
    {
        if ($this->isWebhookTest()) {
            return [
                'api_key' => [
                    'required',
                    'string',
                    'in:' . config('services.wordpress.api_key'),
                ],
            ];
        }

        return [
            'api_key' => [
                'required',
                'string',
                'in:' . config('services.wordpress.api_key'),
            ],

            'name_1_first_name' => ['required', 'string', 'max:100'],
            'name_1_last_name' => ['required', 'string', 'max:150'],

            'email_1' => ['required', 'email', 'max:255'],

            'date_1' => ['required', 'date_format:d-m-Y'],

            'phone_1' => ['required', 'string', 'max:30'],

            'radio_1' => ['required', 'in:dni,nie,pasaporte'],
            'text_1' => ['required', 'string', 'max:20'],

            'address_1_street_address' => ['required', 'string', 'max:255'],
            'address_1_city' => ['required', 'string', 'max:100'],
            'address_1_state' => ['required', 'string', 'max:100'],
            'address_1_zip' => ['required', 'regex:/^\d{5}$/'],
            'address_1_country' => ['required', 'string', 'max:100'],

            'radio_2' => ['required', 'in:1,2'],
            'number_1' => ['nullable', 'integer', 'min:1'],

            'radio_3' => ['nullable', 'in:1,2'],
            'date_2' => ['nullable', 'date_format:d-m-Y'],

            'radio_4' => ['required', 'in:numerario,colaborador,honorario'],

            'g_recaptcha_response' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'api_key.required' => 'La clave de acceso es obligatoria.',
            'api_key.in' => 'La clave de acceso no es válida.',

            'name_1_first_name.required' => 'El nombre es obligatorio.',
            'name_1_last_name.required' => 'Los apellidos son obligatorios.',

            'email_1.required' => 'El correo electrónico es obligatorio.',
            'email_1.email' => 'El correo electrónico no es válido.',

            'date_1.required' => 'La fecha de nacimiento es obligatoria.',
            'date_1.date_format' => 'La fecha debe tener formato DD-MM-AAAA.',

            'phone_1.required' => 'El teléfono es obligatorio.',

            'radio_1.required' => 'El tipo de documento es obligatorio.',
            'radio_1.in' => 'Tipo de documento no válido.',

            'text_1.required' => 'El número del documento es obligatorio.',

            'address_1_street_address.required' => 'La dirección es obligatoria.',
            'address_1_city.required' => 'La ciudad es obligatoria.',
            'address_1_state.required' => 'La provincia es obligatoria.',
            'address_1_zip.required' => 'El código postal es obligatorio.',
            'address_1_country.required' => 'El país es obligatorio.',

            'radio_2.required' => 'Debes indicar si tienes hijos.',

            'radio_4.required' => 'La modalidad de socio es obligatoria.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        if ($this->isWebhookTest()) {
            return;
        }

        $validator->after(function (Validator $validator) {
            $this->validateAdultApplicant($validator);
            $this->validatePhone($validator);
            $this->validateChildrenFields($validator);
            $this->validateDocument($validator);
            $this->validateChildBirthDate($validator);
        });
    }

    protected function validateAdultApplicant(Validator $validator): void
    {
        $date = $this->input('date_1');

        if (! $date) {
            return;
        }

        try {
            $birthDate = Carbon::createFromFormat('d-m-Y', $date);
        } catch (\Throwable $e) {
            return;
        }

        if ($birthDate->isFuture()) {
            $validator->errors()->add(
                'date_1',
                'La fecha de nacimiento no puede ser futura.'
            );
        }

        if ($birthDate->greaterThan(now()->subYears(18))) {
            $validator->errors()->add(
                'date_1',
                'Debes ser mayor de 18 años.'
            );
        }
    }

    protected function validatePhone(Validator $validator): void
    {
        $phone = $this->input('phone_1');

        if (! $phone) {
            return;
        }

        $clean = preg_replace('/[^\d+]/', '', $phone);

        if (! preg_match('/^\+?\d{9,15}$/', $clean)) {
            $validator->errors()->add(
                'phone_1',
                'El teléfono no tiene un formato válido.'
            );
        }
    }

    protected function validateChildrenFields(Validator $validator): void
    {
        $hasChildren = $this->input('radio_2');
        $childrenCount = $this->input('number_1');
        $hasDownChild = $this->input('radio_3');
        $childBirth = $this->input('date_2');

        if ($hasChildren === '1' && ! $childrenCount) {
            $validator->errors()->add(
                'number_1',
                'Debes indicar cuántos hijos tienes.'
            );
        }

        if ($hasChildren === '2' && $childrenCount) {
            $validator->errors()->add(
                'number_1',
                'No debes indicar número de hijos.'
            );
        }

        if ($hasChildren === '2' && ! empty($hasDownChild)) {
            $validator->errors()->add(
                'radio_3',
                'No debes indicar información sobre hijo con síndrome de Down si has marcado que no tienes hijos.'
            );
        }

        if ($hasChildren === '2' && ! empty($childBirth)) {
            $validator->errors()->add(
                'date_2',
                'No debes indicar fecha del hijo si has marcado que no tienes hijos.'
            );
        }

        if ($hasDownChild === '1' && ! $childBirth) {
            $validator->errors()->add(
                'date_2',
                'Debes indicar la fecha de nacimiento del hijo.'
            );
        }

        if ($hasDownChild === '2' && ! empty($childBirth)) {
            $validator->errors()->add(
                'date_2',
                'No debes indicar fecha si has marcado que no tienes un hijo con síndrome de Down.'
            );
        }
    }

    protected function validateDocument(Validator $validator): void
    {
        $type = $this->input('radio_1');
        $number = strtoupper(trim((string) $this->input('text_1')));

        if (! $type || ! $number) {
            return;
        }

        if ($type === 'dni' && ! $this->isValidDni($number)) {
            $validator->errors()->add('text_1', 'El DNI no es válido.');
        }

        if ($type === 'nie' && ! $this->isValidNie($number)) {
            $validator->errors()->add('text_1', 'El NIE no es válido.');
        }

        if ($type === 'pasaporte' && ! $this->isValidPassport($number)) {
            $validator->errors()->add('text_1', 'El pasaporte no es válido.');
        }
    }

    protected function validateChildBirthDate(Validator $validator): void
    {
        if ($this->input('radio_3') !== '1') {
            return;
        }

        $parentDate = $this->input('date_1');
        $childDate = $this->input('date_2');

        if (! $parentDate || ! $childDate) {
            return;
        }

        try {
            $parent = Carbon::createFromFormat('d-m-Y', $parentDate);
            $child = Carbon::createFromFormat('d-m-Y', $childDate);
        } catch (\Throwable $e) {
            return;
        }

        if ($child->isFuture()) {
            $validator->errors()->add(
                'date_2',
                'La fecha del hijo no puede ser futura.'
            );
        }

        if ($child->lt($parent->copy()->addYears(16))) {
            $validator->errors()->add(
                'date_2',
                'La fecha del hijo no es coherente con la del solicitante.'
            );
        }
    }

    protected function isValidDni(string $dni): bool
    {
        if (! preg_match('/^\d{8}[A-Z]$/', $dni)) {
            return false;
        }

        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
        $number = (int) substr($dni, 0, 8);
        $letter = substr($dni, -1);

        return $letter === $letters[$number % 23];
    }

    protected function isValidNie(string $nie): bool
    {
        if (! preg_match('/^[XYZ]\d{7}[A-Z]$/', $nie)) {
            return false;
        }

        $map = ['X' => '0', 'Y' => '1', 'Z' => '2'];
        $number = $map[$nie[0]] . substr($nie, 1, 7);

        $letters = 'TRWAGMYFPDXBNJZSQVHLCKE';

        return $nie[8] === $letters[((int) $number) % 23];
    }

    protected function isValidPassport(string $passport): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{6,15}$/', $passport);
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Log::error('VALIDACION SOLICITUD FALLIDA', [
            'errors' => $validator->errors()->toArray(),
            'payload' => $this->all(),
        ]);

        parent::failedValidation($validator);
    }
}