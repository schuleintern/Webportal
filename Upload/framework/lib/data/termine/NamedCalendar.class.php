<?php
class NamedCalendar extends \Eluceo\iCal\Domain\Entity\Calendar {
    private ?string $name = null;

    /**
     * @param string|null $name
     * @return NamedCalendar
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    public function getName() {
        return $this->name;
    }
}
