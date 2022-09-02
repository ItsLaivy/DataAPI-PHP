<?php
namespace ItsLaivy\DataAPI\Modules\Variables;

use ItsLaivy\DataAPI\Modules\Receptor;
use function ItsLaivy\DataAPI\getVariable;

class InactiveVariable {

    private readonly string $name;
    private readonly string $data;

    private readonly Receptor $receptor;

    public function __construct(Receptor $receptor, string $name, string $data) {
        $this->name = $name;
        $this->data = $data;
        $this->receptor = $receptor;

        if (isset($_SESSION['dataapi']['active_variables'][$receptor->getBruteId()][$name])) {
            return;
        }

        if (isset($_SESSION['dataapi']['Variables'][$receptor->getTable()->getIdentification()][$name])) {
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
    public function getData(): string {
        return $this->data;
    }

}