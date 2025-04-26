<?php
class FormRenderer {
    public static function renderField($type, $name, $label, $errors = [], $values = [], $attrs = []) {
        $html = '<label>'.htmlspecialchars($label).':</label>';
        
        if ($type === 'textarea') {
            $html .= '<textarea name="'.htmlspecialchars($name).'" ';
            $html .= self::buildAttributes($attrs, $errors, $name);
            $html .= '>'.htmlspecialchars($values[$name] ?? '').'</textarea>';
        } else {
            $html .= '<input type="'.htmlspecialchars($type).'" name="'.htmlspecialchars($name).'" ';
            $html .= 'value="'.htmlspecialchars($values[$name] ?? '').'" ';
            $html .= self::buildAttributes($attrs, $errors, $name).'>';
        }
        
        if (!empty($errors[$name])) {
            $html .= '<div class="error-message">'.htmlspecialchars($errors[$name]).'</div>';
        }
        
        return $html;
    }

    public static function renderRadio($name, $value, $label, $values = []) {
        $checked = ($values[$name] ?? '') === $value ? 'checked' : '';
        return '<input type="radio" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($value).'" '.$checked.'> '.htmlspecialchars($label);
    }

    public static function renderSelectLanguages($selected = [], $db = null) {
        if (!$db) {
            $db = new DatabaseRepository();
        }
        $languages = $db->getAllLanguages();
        
        $html = '<select name="languages[]" multiple required>';
        foreach ($languages as $lang) {
            $selectedAttr = in_array($lang['id'], $selected) ? 'selected' : '';
            $html .= '<option value="'.$lang['id'].'" '.$selectedAttr.'>'.htmlspecialchars($lang['language_name']).'</option>';
        }
        return $html.'</select>';
    }

    private static function buildAttributes($attrs, $errors, $name) {
        $result = '';
        foreach ($attrs as $attr => $val) {
            $result .= htmlspecialchars($attr).'="'.htmlspecialchars($val).'" ';
        }
        if (!empty($errors[$name])) {
            $result .= 'class="error"';
        }
        return $result;
    }
}