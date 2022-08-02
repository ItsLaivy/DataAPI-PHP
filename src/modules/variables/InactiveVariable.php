<?php
namespace DataAPI\System;

class InactiveVariable {

    private readonly string $name;
    private readonly mixed $data;

    private readonly Receptor $receptor;

    public function __construct(Receptor $receptor, string $name, string $data) {
        $this->name = $name;
        $this->data = unserialize($data);
        $this->receptor = $receptor;

        if (isset($_SESSION['dataapi']['active_variables'][$receptor->getBruteId()][$name])) {
            return;
        }

        if (isset($_SESSION['dataapi']['variables'][$receptor->getTable()->getIdentification()][$name])) {
            $var = getVariable($receptor->getTable(), $name);
            new ActiveVariable($receptor, $var, $this->data);
        } else {
            $_SESSION['dataapi']['log']['created']['inactive_variables'] += 1;
            $_SESSION['dataapi']['inactive_variables'][$receptor->getBruteId()][$name] = $this;
        }
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
    public function getData(): mixed {
        return $this->data;
    }

}