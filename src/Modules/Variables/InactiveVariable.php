<?php
namespace ItsLaivy\DataAPI\Modules\Variables;

use ItsLaivy\DataAPI\Modules\Receptor;
use ItsLaivy\DataAPI\Modules\Variable;

class InactiveVariable {

    private readonly string $name;
    private readonly string $data;

    private readonly Receptor $receptor;

    public function __construct(Receptor $receptor, string $name, string $data) {
        $this->name = $name;
        $this->data = $data;
        $this->receptor = $receptor;

        if (array_key_exists($name, $receptor->getActiveVariables())) {
            return;
        }

        if (array_key_exists($name, Variable::$VARIABLES)) {
            new ActiveVariable($receptor, Variable::$VARIABLES[$name], $this->data);
        } else {
            $receptor->getInactiveVariables()[$name] = $this;
        }
    }

    public function getReceptor(): Receptor {
        return $this->receptor;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getData(): string {
        return $this->data;
    }

}