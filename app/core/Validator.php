<?php
// Form validation - chain rules, then check fails() before saving anything.
// Every rule takes the field name, any limits, and an optional message of our own.
// A field keeps its first error, so "required" isn't replaced by a format error.
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data) { $this->data = $data; }

    // presence
    public function required(string $field, string $message = ''): static
    {
        if ($this->value($field) === '') {
            $this->fail($field, $message ?: $this->label($field).' is required.');
        }
        return $this;
    }

    // string length
    public function maxLen(string $field, int $max, string $message = ''): static
    {
        if (mb_strlen($this->value($field)) > $max) {
            $this->fail($field, $message ?: $this->label($field)." must not exceed $max characters.");
        }
        return $this;
    }

    public function minLen(string $field, int $min, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && mb_strlen($v) < $min) {
            $this->fail($field, $message ?: $this->label($field)." must be at least $min characters.");
        }
        return $this;
    }

    // types
    public function date(string $field, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v === '') return $this;
        $d = DateTime::createFromFormat('Y-m-d', $v);
        if (!$d || $d->format('Y-m-d') !== $v) {
            $this->fail($field, $message ?: $this->label($field).' must be a valid date.');
        } elseif ($d > new DateTime()) {
            $this->fail($field, $this->label($field).' cannot be in the future.');
        }
        return $this;
    }

    public function numeric(string $field, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && !is_numeric($v)) {
            $this->fail($field, $message ?: $this->label($field).' must be a number.');
        }
        return $this;
    }

    public function range(string $field, float $min, float $max, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && is_numeric($v) && ((float)$v < $min || (float)$v > $max)) {
            $this->fail($field, $message ?: $this->label($field)." must be between $min and $max.");
        }
        return $this;
    }

    public function positiveInt(string $field, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && (filter_var($v, FILTER_VALIDATE_INT) === false || (int)$v <= 0)) {
            $this->fail($field, $message ?: $this->label($field).' must be a positive whole number.');
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && !in_array($v, $allowed, true)) {
            $this->fail($field, $message ?: $this->label($field).' has an invalid value.');
        }
        return $this;
    }

    public function pattern(string $field, string $regex, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v !== '' && !preg_match($regex, $v)) {
            $this->fail($field, $message ?: $this->label($field).' is not in the right format.');
        }
        return $this;
    }

    // Sri Lankan formats
    public function nic(string $field, string $message = ''): static
    {
        $v = strtoupper($this->value($field));
        if ($v !== '' && !preg_match('/^\d{9}[VX]$/', $v) && !preg_match('/^\d{12}$/', $v)) {
            $this->fail($field, $message ?: 'NIC must be 9 digits + V/X (old) or 12 digits (new).');
        }
        return $this;
    }

    public function phone(string $field, string $message = ''): static
    {
        $v = preg_replace('/[\s\-()]/', '', $this->value($field));
        if ($v !== '' && !preg_match('/^(?:\+94|0)[0-9]{9}$/', $v)) {
            $this->fail($field, $message ?: 'Enter a valid phone number, e.g. 0771234567.');
        }
        return $this;
    }

    public function labResult(string $field, string $message = ''): static
    {
        $v = $this->value($field);
        if ($v === '') return $this;
        if (!is_numeric($v))          $this->fail($field, $message ?: 'Result value must be a number.');
        elseif ((float)$v < 0)        $this->fail($field, 'Result value cannot be negative.');
        elseif ((float)$v > 9999999)  $this->fail($field, 'Result value looks too large - please check it.');
        return $this;
    }

    // results
    public function fails(): bool        { return !empty($this->errors); }
    public function errors(): array      { return $this->errors; }
    public function firstError(): string { return $this->errors ? reset($this->errors) : ''; }

    public function get(string $field, mixed $default = null): mixed
    {
        return isset($this->data[$field]) ? trim((string)$this->data[$field]) : $default;
    }

    private function value(string $field): string
    {
        return trim((string)($this->data[$field] ?? ''));
    }

    private function fail(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) $this->errors[$field] = $message;
    }

    private function label(string $field): string
    {
        return ucfirst(str_replace('_', ' ', $field));
    }
}
