<?php

namespace App\Core;

class Validator
{
    private array $errors = [];
    private array $data = [];
    
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    
    /**
     * Validate required fields
     */
    public function required(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (empty(trim($value))) {
            $this->errors[$field] = "$label is required";
        }
        
        return $this;
    }
    
    /**
     * Validate email format
     */
    public function email(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "$label must be a valid email address";
        }
        
        return $this;
    }
    
    /**
     * Validate minimum length
     */
    public function minLength(string $field, int $min, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && strlen($value) < $min) {
            $this->errors[$field] = "$label must be at least $min characters long";
        }
        
        return $this;
    }
    
    /**
     * Validate maximum length
     */
    public function maxLength(string $field, int $max, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && strlen($value) > $max) {
            $this->errors[$field] = "$label must not exceed $max characters";
        }
        
        return $this;
    }
    
    /**
     * Validate phone number format
     */
    public function phone(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value)) {
            // Remove all non-digit characters for validation
            $cleanPhone = preg_replace('/[^0-9]/', '', $value);
            if (strlen($cleanPhone) < 10 || strlen($cleanPhone) > 15) {
                $this->errors[$field] = "$label must be a valid phone number";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate password strength
     */
    public function passwordStrength(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value)) {
            $strength = 0;
            $feedback = [];
            
            if (strlen($value) >= 6) $strength++;
            if (preg_match('/[a-z]/', $value)) $strength++;
            if (preg_match('/[A-Z]/', $value)) $strength++;
            if (preg_match('/[0-9]/', $value)) $strength++;
            if (preg_match('/[^a-zA-Z0-9]/', $value)) $strength++;
            
            if ($strength < 3) {
                $this->errors[$field] = "$label is too weak. Include uppercase, lowercase, numbers, and special characters";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate password confirmation
     */
    public function passwordConfirm(string $passwordField, string $confirmField): self
    {
        $password = $this->data[$passwordField] ?? '';
        $confirm = $this->data[$confirmField] ?? '';
        
        if (!empty($password) && $password !== $confirm) {
            $this->errors[$confirmField] = "Password confirmation does not match";
        }
        
        return $this;
    }
    
    /**
     * Validate unique email (excluding current user for updates)
     */
    public function uniqueEmail(string $field, string $modelClass, int $excludeId = null, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value)) {
            $model = new $modelClass();
            $existingUser = $model->findByEmail($value);
            
            if ($existingUser && (!$excludeId || $existingUser['id'] != $excludeId)) {
                $this->errors[$field] = "$label is already taken";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate numeric value
     */
    public function numeric(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = "$label must be a valid number";
        }
        
        return $this;
    }
    
    /**
     * Validate positive number
     */
    public function positive(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && (!is_numeric($value) || $value <= 0)) {
            $this->errors[$field] = "$label must be a positive number";
        }
        
        return $this;
    }
    
    /**
     * Validate date format
     */
    public function date(string $field, string $format = 'Y-m-d', string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value)) {
            $date = \DateTime::createFromFormat($format, $value);
            if (!$date || $date->format($format) !== $value) {
                $this->errors[$field] = "$label must be a valid date in format $format";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate future date
     */
    public function futureDate(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value)) {
            $date = \DateTime::createFromFormat('Y-m-d', $value);
            if ($date && $date <= new \DateTime()) {
                $this->errors[$field] = "$label must be a future date";
            }
        }
        
        return $this;
    }
    
    /**
     * Validate URL format
     */
    public function url(string $field, string $label = null): self
    {
        $label = $label ?: ucfirst(str_replace('_', ' ', $field));
        $value = $this->data[$field] ?? '';
        
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "$label must be a valid URL";
        }
        
        return $this;
    }
    
    /**
     * Custom validation rule
     */
    public function custom(string $field, callable $callback, string $message): self
    {
        $value = $this->data[$field] ?? '';
        
        if (!$callback($value)) {
            $this->errors[$field] = $message;
        }
        
        return $this;
    }
    
    /**
     * Check if validation passed
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
    
    /**
     * Check if validation failed
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    
    /**
     * Get all validation errors
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    /**
     * Get first error for a specific field
     */
    public function getError(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }
    
    /**
     * Get first error message
     */
    public function getFirstError(): ?string
    {
        return reset($this->errors) ?: null;
    }
    
    /**
     * Clear all errors
     */
    public function clearErrors(): self
    {
        $this->errors = [];
        return $this;
    }
    
    /**
     * Get validated data
     */
    public function getData(): array
    {
        return $this->data;
    }
    
    /**
     * Get specific field value
     */
    public function getValue(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }
}
