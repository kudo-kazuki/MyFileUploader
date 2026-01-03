<?php
function validate(array $data, array $rules): array
{
    $errors = [];

    foreach ($rules as $key => $ruleSet) {
        $rulesArray = explode('|', $ruleSet);
        $value = $data[$key] ?? null;

        
        // 文字列なら前後空白を除去
        if (is_string($value)) {
            $value = trim($value);
        }

        // nullable が含まれていて、値が null または空文字ならスキップ
        if (in_array('nullable', $rulesArray, true) && ($value === null || $value === '')) {
            continue;
        }

        foreach ($rulesArray as $rule) {
            if ($rule === 'required' && ($value === null || $value === '')) {
                $errors[$key][] = 'この項目は必須です';
            }

            if (str_starts_with($rule, 'max:')) {
                $max = (int) explode(':', $rule)[1];
                if (is_string($value) && mb_strlen($value) > $max) {
                    $errors[$key][] = "最大{$max}文字までです";
                }
            }

            if (str_starts_with($rule, 'min:')) {
                $min = (int) explode(':', $rule)[1];
                if (is_string($value) && mb_strlen($value) < $min) {
                    $errors[$key][] = "最低{$min}文字以上である必要があります";
                }
                if (is_numeric($value) && $value < $min) {
                    $errors[$key][] = "{$min}以上の数値である必要があります";
                }
            }

            if ($rule === 'numeric' && !is_numeric($value)) {
                $errors[$key][] = '数値である必要があります';
            }

            if ($rule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$key][] = 'メールアドレスの形式が正しくありません';
            }

            if ($rule === 'date') {
                // 日付形式のチェック
                if (!strtotime($value)) {
                    $errors[$key][] = '日付形式が正しくありません';
                }
            }
        }
    }

    return $errors;
}
