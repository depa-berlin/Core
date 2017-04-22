<?php

/**
 * Singleton Entwurfsmuster-Implementierung für PHP
 * http://de.wikipedia.org/wiki/Singleton_(Entwurfsmuster)#Implementierung_in_PHP_.28ab_Version_5.29
 * 
 * Aufruf per:
 * $singleton = Singleton::getInstance()
 */

namespace Depa\Core;

abstract class Singleton
{
    /**
     * Liefert die Instanz der Singleton-Klasse
     */
    public static function getInstance()
    {
    	
    }
    
    /**
     * Konstruktor ist private und muss auch in der erbenden Klasse private sein!
     */
    private function __construct ()
    {
    }

    /**
     * Klonen per 'clone()' von außen verbieten.
     */
    private function __clone ()
    {
    }
}