<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

use pocketmine\form\FormValidationException;

class CustomForm extends Form {

    /**
     * @var array
     */
    private array $labelMap = [];

    /**
     * @var array
     */
    private array $validationMethods = [];

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable) {
        parent::__construct($callable);
        $this->data["type"] = "custom_form";
        $this->data["title"] = "";
        $this->data["content"] = [];
    }

    /**
     * @param $data
     * @return void
     */
    public function processData(&$data): void {
        if ($data !== null && !is_array($data)) {
            throw new FormValidationException("Expected an array response, got " . gettype($data));
        }
        if (is_array($data)) {
            $validationMethodsCount = count($this->validationMethods);
            if (count($data) !== $validationMethodsCount) {
                throw new FormValidationException("Expected an array response with the size " . $validationMethodsCount . ", got " . count($data));
            }
            $new = [];
            foreach ($data as $i => $v) {
                $validationMethod = $this->validationMethods[$i] ?? null;
                if ($validationMethod === null) {
                    throw new FormValidationException("Invalid element " . $i);
                }
                $labelMapIndex = $this->labelMap[$i];
                if (!$validationMethod($v)) {
                    throw new FormValidationException("Invalid type given for element " . $labelMapIndex);
                }
                $new[$labelMapIndex] = $v;
            }
            $data = $new;
        }
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self {
        $this->data["title"] = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->data["title"];
    }

    /**
     * @param string $text
     * @return $this
     */
    public function addLabel(string $text): self {
        $this->addContent(["type" => "label", "text" => $text]);
        return $this;
    }

    /**
     * @param string $text
     * @param string|null $label
     * @return $this
     */
    public function addHeader(string $text, ?string $label = null): self {
        $this->addContent(["type" => "header", "text" => $text]);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_null($v);
        return $this;
    }

    /**
     * @return $this
     */
    public function addDivider(?string $label = null): self {
        $this->addContent(["type" => "divider", "text" => ""]);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_null($v);
        return $this;
    }

    /**
     * @param string $text
     * @param bool|null $default
     * @param string|null $label
     * @param string|null $tooltip
     * @return $this
     */
    public function addToggle(string $text, bool $default = null, ?string $label = null, ?string $tooltip = null): self {
        $content = ["type" => "toggle", "text" => $text];
        if ($default !== null) {
            $content["default"] = $default;
        }
        if ($tooltip !== null) {
            $content["tooltip"] = $tooltip;
        }
        $this->addContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_bool($v);
        return $this;
    }

    /**
     * @param string $text
     * @param int $min
     * @param int $max
     * @param int $step
     * @param int $default
     * @param string|null $label
     * @param string|null $tooltip
     * @return $this
     */
    public function addSlider(string $text, int $min, int $max, int $step = -1, int $default = -1, ?string $label = null, ?string $tooltip = null): self {
        $content = ["type" => "slider", "text" => $text, "min" => $min, "max" => $max];
        if ($step !== -1) {
            $content["step"] = $step;
        }
        if ($default !== -1) {
            $content["default"] = $default;
        }
        if ($tooltip !== null) {
            $content["tooltip"] = $tooltip;
        }
        $this->addContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => (is_float($v) || is_int($v)) && $v >= $min && $v <= $max;
        return $this;
    }

    /**
     * @param string $text
     * @param array $steps
     * @param int $defaultIndex
     * @param string|null $label
     * @param string|null $tooltip
     * @return $this
     */
    public function addStepSlider(string $text, array $steps, int $defaultIndex = -1, ?string $label = null, ?string $tooltip = null): self {
        $content = ["type" => "step_slider", "text" => $text, "steps" => $steps];
        if ($defaultIndex !== -1) {
            $content["default"] = $defaultIndex;
        }
        if ($tooltip !== null) {
            $content["tooltip"] = $tooltip;
        }
        $this->addContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_int($v) && isset($steps[$v]);
        return $this;
    }

    /**
     * @param string $text
     * @param array $options
     * @param int|null $default
     * @param string|null $label
     * @param string|null $tooltip
     * @return $this
     */
    public function addDropdown(string $text, array $options, ?int $default = null, ?string $label = null, ?string $tooltip = null): self {
        $content = ["type" => "dropdown", "text" => $text, "options" => $options];
        if ($default !== null) {
            $content["default"] = $default;
        }
        if ($tooltip !== null) {
            $content["tooltip"] = $tooltip;
        }
        $this->addContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_int($v) && isset($options[$v]);
        return $this;
    }

    /**
     * @param string $text
     * @param string $placeholder
     * @param string|null $default
     * @param string|null $label
     * @param string|null $tooltip
     * @return $this
     */
    public function addInput(string $text, string $placeholder = "", string $default = null, ?string $label = null, ?string $tooltip = null): self {
        $content = ["type" => "input", "text" => $text, "placeholder" => $placeholder];
        if ($default !== null) {
            $content["default"] = $default;
        }
        if ($tooltip !== null) {
            $content["tooltip"] = $tooltip;
        }
        $this->addContent($content);
        $this->labelMap[] = $label ?? count($this->labelMap);
        $this->validationMethods[] = static fn (mixed $v): bool => is_string($v);
        return $this;
    }

    /**
     * @param array $content
     * @return void
     */
    private function addContent(array $content): void {
        $this->data["content"][] = $content;
    }

}
