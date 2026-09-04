<?php

namespace App\Core;

use App\Core\Database;

class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public static function make(array $data, array $rules): self
    {
        $v = new self($data, $rules);
        $v->validate();
        return $v;
    }

    public function validate(): bool
    {
        $this->errors = [];

        foreach ($this->rules as $field => $fieldRules) {
            $ruleList = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);
            $val = $this->data[$field] ?? null;

            foreach ($ruleList as $r) {
                $parts = explode(':', $r, 2);
                $ruleName = $parts[0];
                $param = $parts[1] ?? null;

                if ($ruleName === 'required') {
                    if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                        $this->errors[$field][] = "กรุณาระบุข้อมูล {$field}";
                    }
                } elseif ($ruleName === 'numeric') {
                    if ($val !== null && $val !== '' && !is_numeric($val)) {
                        $this->errors[$field][] = "ช่อง {$field} ต้องเป็นตัวเลข";
                    }
                } elseif ($ruleName === 'min') {
                    if (is_numeric($val) && (float)$val < (float)$param) {
                        $this->errors[$field][] = "ช่อง {$field} ต้องไม่น้อยกว่า {$param}";
                    } elseif (is_string($val) && mb_strlen($val) < (int)$param) {
                        $this->errors[$field][] = "ช่อง {$field} ต้องมีความยาวอย่างน้อย {$param} ตัวอักษร";
                    }
                } elseif ($ruleName === 'max') {
                    if (is_numeric($val) && (float)$val > (float)$param) {
                        $this->errors[$field][] = "ช่อง {$field} ต้องไม่เกิน {$param}";
                    } elseif (is_string($val) && mb_strlen($val) > (int)$param) {
                        $this->errors[$field][] = "ช่อง {$field} ต้องมีความยาวไม่เกิน {$param} ตัวอักษร";
                    }
                } elseif ($ruleName === 'date') {
                    if ($val && !strtotime($val)) {
                        $this->errors[$field][] = "รูปแบบวันที่ใน {$field} ไม่ถูกต้อง";
                    }
                }
            }
        }

        return empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        foreach ($this->errors as $errList) {
            if (!empty($errList)) {
                return $errList[0];
            }
        }
        return null;
    }
}
