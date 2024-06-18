<?php

abstract class AbstractTermin {
	
	protected $data;
	
	protected $table = "";
	
	private $deleteable = true;
		
	protected function __construct($data) {
		$this->data = $data;
	}
	
	public function getID() {
		return $this->data['eintragID'];
	}
	
	public function getTitle() {
		if($this->isWholeDay()) return $this->data['eintragTitel'];
		else return $this->getUhrzeitStart() . " Uhr: " . $this->data['eintragTitel'];
	}
	
	public function getTitleRaw() {
	    return $this->data['eintragTitel'];
	}
	
	public function getDatumStart() {
		return $this->data['eintragDatumStart'];
	}
	
	public function getDatumEnde() {
		return $this->data['eintragDatumEnde'];
	}
		
	public function isWholeDay() {
		return $this->data['eintragIsWholeDay'];
	}
	
	/**
	 * Formatierter Eintragzeitpunkt
	 * @see functions::makeDateFromTimestamp()
	 * @return string
	 */
	public function getEintragZeitpunkt() {
	    if(DB::getSettings()->getBoolean('datenschutz-kein-eintragzeitpunkt')) return 'n/a';
		return functions::makeDateFromTimestamp($this->data['eintragEintragZeitpunkt']);
	}
	
	public function getOrt() {
		return $this->data['eintragOrt'];
	}
	

	public function getKommentar() {
		return $this->data['eintragKommentar'];
	}
	
	public function getUhrzeitStart() {
		return $this->data['eintragUhrzeitStart'];
	}
	
	public function getUhrzeitEnde() {
		return $this->data['eintragUhrzeitEnde'];
	}
	
	public function getCreatorName() {
		$user = functions::getDisplayNameFromUserID($this->data['eintragUser']);
		return $user;
	}
	
	public function getCreatorUserID() {
	    return $this->data['eintragUser'];
	}
	
	/**
	 * 
	 * @return AbstractKalenderKategorie
	 */
	public function getKategorie() {
	    return null;
	}
	
	public function canDelete() {
	    return $this->deleteable;
	}
	
	public function setNotDeleteAble() {
	    $this->deleteable = false;
	}

    /**
     * @return string|null
     */
	public function getColor() {
	    return null;
    }

    /**
     * Transforms the event to an Event object from Eluceo iCal.
     *
     * Returns `false` if no title has been set, or the start or end date could not be parsed using the format `YYYY-MM-DD`.
     *
     * @return \Eluceo\iCal\Domain\Entity\Event|false
     */
    public function transform() {
        $title = $this->getTitleRaw();
        if (empty($title)) { return false; }  // fast skip if title is not set

        $startDate = $this->getDatumStart();
        $endDate = $this->getDatumEnde();

        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        if ($start === false) { return false; }  // skip on malformed dates

        $end = \DateTime::createFromFormat('Y-m-d', $endDate);
        if ($end === false) { return false; }  // ditto

        if (!$this->isWholeDay()) {
            $startTime = $this->getUhrzeitStart();
            $endTime = $this->getUhrzeitEnde();
            if ($startTime === null && $endTime === null) {  // if both times are missing, assume it's an all-day event even if the marker is missing
                $allDay = true;
            } else {
                // if only one of the two times is present, assume it's an event with no duration
                // i.e. start time == end time
                $startTime = $startTime === null ? $endTime : $startTime;
                $endTime = $endTime === null ? $startTime : $endTime;

                list($startHour, $startMinute) = explode(':', $startTime);
                list($endHour, $endMinute) = explode(':', $endTime);

                $start->setTime($startHour, $startMinute);
                $end->setTime($endHour, $endMinute);
                $allDay = false;
            }
        } else {
            $allDay = true;
        }

        return ICSFeed::getICSFeedObject(
            $this->getID(),
            $title,
            $start,
            $end,
            $this->getOrt(),
            $this->getKommentar() . ' (Eingetragen von ' . $this->getCreatorName() . ')',
            $allDay
        );
    }
}
