<?php

namespace MakinaCorpus\Layout\Render;

/**
 * Provides some utility methods to inject content into HTML
 */
trait HtmlInjectionTrait
{
    /**
     * Escape string
     */
    private function escape(string $string) : string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Render given attributes
     */
    private function renderAttributes(array $attributes): string
    {
        if (!$attributes) {
            return '';
        }

        array_walk($attributes, function (&$value, $key) {
            if ($value === '' || null === $value) {
                $value = $this->escape($key);
            } else {
                $value = $this->escape($key).'="'.$this->escape($value).'"';
            }
        });

        return ' '.implode(' ', $attributes);
    }

    /**
     * Arbitrary inject HTML into the first tag found
     */
    private function injectHtml(string $input, string $addition, array $attributes = []) : string
    {
        if (!$addition && !$attributes) {
            return $input;
        }

        // @todo
        //   - do this a better way
        //   - it might break HTML if there's ">" in the attributes
        //   - it might add after if we match a /> instead of a >
        $index = strpos($input, '>');

        if (false === $index) {
            if ($attributes) {
                // We have no choice than to add an extra div, since we have none otherwise
                return '<div'.$this->renderAttributes($attributes).'>'.$input.'</div>';
            }
            return '<div>' . $addition . $input . '</div>';
        }

        // Addition always first, else it will move index offset
        if ($addition) {
            $input = substr_replace($input, $addition, $index + 1, 0);
        }
        if ($attributes) {
            $input = substr_replace($input, $this->renderAttributes($attributes), $index, 0);
        }

        return $input;
    }
}
