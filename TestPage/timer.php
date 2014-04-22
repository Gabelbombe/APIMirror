<?php

class Timer {
	private $start;
	private $has_started = false;

	// Begins the timer.  Can be started multiple times without subsequent calls to stop().  
	public function start() {
		$this->start = microtime(true);
		$this->has_started = true;
	}
	
	// Returns time difference between current call to stop() and last call to start()
	public function stop() {
		if(!$this->has_started) {
			println("Timer hasn't been started yet!");
		} else {
			$this->has_started = false;
			return microtime(true) - $this->start;
		}
	}
}