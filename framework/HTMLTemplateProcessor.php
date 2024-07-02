<?php

class HTMLTemplateProcessor
{
    public static function replaceVariables($html, $variables)
    {
        $pattern = '/{{\s*([^}]+)\s*}}/';

        $replaced = preg_replace_callback($pattern, function ($match) use ($variables) {
            $variableName = trim($match[1]);
            return isset($variables[$variableName]) ? $variables[$variableName] : $match[0];
        }, $html);

        return $replaced;
    }
}