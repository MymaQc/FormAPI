<?php

declare(strict_types=1);

namespace jojoe77777\FormAPI;

use Closure;
use pocketmine\form\Form as IForm;
use pocketmine\player\Player;

abstract class Form implements IForm {

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var Closure|null
     */
    private ?Closure $callable;

    /**
     * @param callable|null $callable
     */
    public function __construct(?callable $callable = null) {
        $this->setCallable($callable);
    }

    /**
     * @return Closure|null
     */
    public function getCallable(): ?Closure {
        return $this->callable;
    }

    /**
     * @param callable|null $callable
     * @return void
     */
    public function setCallable(?callable $callable = null): void {
        $this->callable = $callable !== null ? $callable(...) : null;
    }

    /**
     * @param Player $player
     * @param mixed $data
     * @return void
     */
    public function handleResponse(Player $player, mixed $data): void {
        $this->processData($data);
        if ($this->callable !== null) {
            ($this->callable)($player, $data);
        }
    }

    /**
     * @param mixed $data
     * @return void
     */
    public function processData(mixed &$data): void {}

    /**
     * @return array
     */
    public function jsonSerialize(): array {
        return $this->data;
    }

}
