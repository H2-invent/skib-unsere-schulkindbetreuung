<?php
/**
 * Created by PhpStorm.
 * User: Emanuel
 * Date: 03.10.2019
 * Time: 19:01
 */

namespace App\Service;

use Symfony\Component\Validator\Constraints\DateTime;




class IcsService
{
    const DT_FORMAT = 'Ymd\THis\Z';
    protected $properties = array();
    private $available_properties = array(
        'description',
        'dtend',
        'dtstart',
        'location',
        'summary',
        'url',
        'rrule',
    );
    private $appointments = array();

    public function set($key, $val = false) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
        } else {
            if (in_array($key, $this->available_properties)) {
                $this->properties[$key] = $this->sanitize_val($val, $key);
            }
        }
        $this->appointments[]= $this->properties;
    }
    public function add($key){

        array_push($this->appointments,$key);
    }
    public function to_string() {
        $rows = $this->build_props();
        return implode("\r\n", $rows);
    }
    private function build_props() {
        // Build ICS properties - add header
        $ics_props = array(
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
            'CALSCALE:GREGORIAN',

        );
        // Build ICS properties - add header
        foreach ($this->appointments as $data){
            $ics_props[]= 'BEGIN:VEVENT';

            $props = array();
            foreach($data as $k => $v) {
                $props[strtoupper($k . ($k === 'url' ? ';VALUE=URI' : ''))] = $v;
            }
            // Set some default values
            $props['DTSTAMP'] = $this->format_timestamp('now');
            $props['UID'] = uniqid('sd',true);
            // Append properties
            foreach ($props as $k => $v) {
                $ics_props[] = "$k:$v";
            }

              $ics_props[] = 'END:VEVENT';
        }

        // Build ICS properties - add footer

        $ics_props[] = 'END:VCALENDAR';
        return $ics_props;
    }
    private function sanitize_val($val, $key = false) {
        switch($key) {
            case 'dtend':
            case 'dtstamp':
            case 'dtstart':
                $val = $this->format_timestamp($val);
                break;
            default:
                $val = $this->escape_string($val);
        }
        return $val;
    }
    private function format_timestamp($timestamp) {
        $dt = new \DateTime($timestamp);
        return $dt->format(self::DT_FORMAT);
    }
    private function escape_string($str) {
        return preg_replace('/([\,;])/','\\\$1', $str);
    }


}