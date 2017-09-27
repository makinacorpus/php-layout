<?php

namespace MakinaCorpus\Layout\Render;

/**
 * Provides some utility methods to inject content into HTML
 */
trait HtmlInjectionTrait
{
    /**
     * Render given attributes
     *
     * @param string[] $attributes
     *   Arbitrary attributes to display
     *
     * @return string
     *   Attributes as a string
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
     * Arbitrary inject HTML into the first div found
     *
     * @param string $input
     *   Rendered HTML item from the nested renderer
     * @param string $addition
     *   HTML to inject
     * @param string[] $attributes
     *   Arbitrary attributes to inject
     *
     * @return string
     *   Rendered HTML with injected content
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
            return $addition . $input;
        }

        if ($attributes) {
            $input = substr_replace($input, $this->renderAttributes($attributes), $index - 1, 0);
        }
        if ($addition) {
            $input = substr_replace($input, $addition, $index + 1, 0);
        }

        return $input;
    }
}
