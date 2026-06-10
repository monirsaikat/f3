<?php

namespace App\Traits;

/**
 * Rule-based input validation for API controllers.
 *
 * Usage:
 *   $data = $this->validate($this->body(), [
 *       'name'     => 'required|max:120',
 *       'email'    => 'required|email|max:191',
 *       'password' => 'required|min:6|confirmed',
 *       'gender'   => 'in:Male,Female,Other',
 *       'bio'      => 'nullable|max:500',
 *   ]);
 *
 * Returns the cleaned (trimmed, filtered) array on success.
 * Calls $this->json() + exit with HTTP 422 on failure.
 *
 * Available rules (pipe-delimited):
 *   required            — field must be present and non-empty
 *   email               — must pass filter_var FILTER_VALIDATE_EMAIL
 *   min:n               — string length ≥ n
 *   max:n               — string length ≤ n
 *   confirmed           — must equal {field}_confirm in the input
 *   in:a,b,c            — value must be one of the listed options
 *   nullable            — marks the field as optional (no-op rule, for readability)
 */
trait ValidatesRequest
{
    protected function validate(array $data, array $rules): array
    {
        $errors  = [];
        $cleaned = [];

        foreach ($rules as $field => $ruleStr) {
            $value = $data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }

            $label = ucfirst(str_replace('_', ' ', $field));

            foreach (explode('|', $ruleStr) as $ruleToken) {
                [$name, $param] = array_pad(explode(':', $ruleToken, 2), 2, null);
                $empty = ($value === null || $value === '');

                if ($name === 'required' && $empty) {
                    $errors[$field][] = "$label is required";
                } elseif ($name === 'email' && !$empty && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "$label must be a valid email address";
                } elseif ($name === 'min' && !$empty && mb_strlen((string) $value) < (int) $param) {
                    $errors[$field][] = "$label must be at least $param characters";
                } elseif ($name === 'max' && !$empty && mb_strlen((string) $value) > (int) $param) {
                    $errors[$field][] = "$label may not exceed $param characters";
                } elseif ($name === 'confirmed' && $value !== ($data["{$field}_confirm"] ?? null)) {
                    $errors[$field][] = "$label confirmation does not match";
                } elseif ($name === 'in' && !$empty && !in_array($value, explode(',', $param ?? ''), true)) {
                    $errors[$field][] = "$label must be one of: $param";
                }
                // 'nullable' is a no-op marker — no validation, just documents intent
            }

            if ($value !== null && $value !== '') {
                $cleaned[$field] = $value;
            }
        }

        if ($errors) {
            $this->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $errors], 422);
            exit;
        }

        return $cleaned;
    }
}
