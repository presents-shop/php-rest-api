<?php

class EventDispatcher
{
    /**
     * An associative array to store event listeners.
     * @var array
     */
    protected $listeners = [];
    
    /**
     * Registers a listener for a specific event.
     * 
     * @param string $eventName The name of the event to listen for.
     * @param callable $listener The listener function or method to be executed when the event is dispatched.
     * @return void
     *
     * This method allows you to register a listener for a specific event. When the event is dispatched,
     * all registered listeners for that event will be invoked in the order they were added.
     */
    public function registerListener(string $eventName, callable $listener): void
    {
        // If no listeners are registered for the event, initialize an array
        if (!isset($this->listeners[$eventName])) {
            $this->listeners[$eventName] = [];
        }

        // Add the listener to the event's listener array
        $this->listeners[$eventName][] = $listener;
    }

    /**
     * Dispatches an event, triggering all registered listeners.
     * 
     * @param string $eventName The name of the event to dispatch.
     * @param mixed $eventData Optional data to pass to the event listeners.
     * @return void
     */
    public function dispatch(string $eventName, $eventData = null): void
    {
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                call_user_func($listener, $eventData);
            }
        }
    }
}