<?php
namespace ItsLaivy\DataAPI\Modules\Variables;

use ItsLaivy\DataAPI\Modules\Receptor;

class InactiveVariable {

    private readonly string $name;
    private readonly string $data;

    private readonly Receptor $receptor;

    public function __construct(Receptor $receptor, string $name, string $data, array $variables) {
        $this->name = $name;
        $this->data = $data;
        $this->receptor = $receptor;

        if (array_key_exists($name, $receptor->getActiveVariables())) {
            return;
        }

        foreach ($variables as $variable) {
            if ($variable->getName() == $name) {
                new ActiveVariable($receptor, $variable, $this->data);
                return;
            }
        }
        $receptor->getInactiveVariables()[$name] = $this;
    }

    /**
     * @return Receptor receptor da variável inativa
     */
    public function getReceptor(): Receptor {
        return $this->receptor;
    }

    /**
     * @return string nome da variável
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return mixed valor da variável
     */
    public function getData(): string {
        return $this->data;
    }

}