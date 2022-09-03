<?php
namespace ItsLaivy\DataAPI\Modules\Variables;

use Exception;
use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\Variable;

class ActiveVariable {

    private readonly Receptor $receptor;
    private readonly Variable $variable;

    private mixed $data;

    public function __construct(Receptor $receptor, Variable $variable, string $data) {
        $this->variable = $variable;
        $this->data = $variable->isSerialize() ? unserialize($data) : $data;
        $this->receptor = $receptor;

        echo "<br>Count ¹: '".count($receptor->getActiveVariables())."'<br>";
        $receptor->activeVariables[$variable->getName()] = $this;
        echo "Count ²: '".count($receptor->getActiveVariables())."'<br>";
    }

    /**
     * @return Receptor receptor da variável inativa
     */
    public function getReceptor(): Receptor {
        return $this->receptor;
    }

    /**
     * @return Variable a variável
     */
    public function getVariable(): Variable {
        return $this->variable;
    }

    /**
     * @return mixed valor da variável
     */
    public function getData(): mixed {
        return $this->data;
    }

    public function setData(mixed $value): void {
        if (!$this->getVariable()->isSerialize() && !method_exists($value, "__toString")) {
            throw new exception("Cannot set variable '".$this->getVariable()->getName()."' from receptor '".$this->receptor->getBruteId()."' because the variable isn't serializable and value cannot be converted as a string!");
        }

        $this->data = $value;
    }

}