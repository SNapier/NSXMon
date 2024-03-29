# NSXMon
Nagios Library to monitor VMware NSX Devices

# Command Line
Usage: /usr/bin/php NSXMon.php -H "<hostname>"  -f "/path/to/authfile.cfg" -m "<monitor>" -a "<action>" -c "<critical>" -C "<criticalCount>" -O "<objectId>" -s "<scanrange>" -n "<true, false>"

# NagiosXI Command 
Name: NSXMon

/usr/bin/php -q $USER1$/NSXMon.php -H "$HOSTADDRESS$" -f "$ARG1$" -m "$ARG2$" -a "$ARG3$" -c "$ARG4$" -C "$ARG5$" -O "$ARG6$" -s "$ARG7$" -n "$ARG8$"

# Auth File
1. pass the full path to the file in $ARG1 of the check command.
2. See auth-file-example,cfg for content and format requirements

# NSXMon Check Options
     
     SYSTEM
     -m | --monitor (system)
     -a | --action system(uptime, version, cpu-usage, cpu-count, cpu-cap, mem-usage, mem-total, storage-usage, storage-total)
     -c | --critcal
     -n | --negate (default is false) - forces check to exit with OK state

     SYSTEM-EVENT
     -m | --monitor* (system-event)
     -a | --action* system-event(show-crit, show-high)
     -c | --critcal* (eventId to match against)
     -C | --criticalcount (The number of number of matched events before alert)
     -O | --criticalobject (Specific origin objectId to match against)
     -s | --scanrange (Only scan events newer than the last X seconds) - seconds only
     -n | --negate (default is empty\false) - forces check to exit with OK state

     SYSTEM-ALARM
     -m | --monitor* (system-alarm)
     -a | --action* system-event(show-resolvable, show-unresolvable)
     -c | --critcal ()
     -C | --criticalcount (The number of number of matched events before alert) default = 1
     -O | --criticalobject (Specific alarm objectId to match against)
     -s | --scanrange (Only scan events newer than the last X seconds) - default = 600

     AUDIT-LOG
     -m | --monitor (audit-log)
     -a | --action audit-log(show-fail)
     -c | --critcal
     -C | --criticalcount (The number of number of matched events before alert) -> default = 1
     -O | --criticalobject (Specific operation type to match against)
     -s | --scanrange (Only scan events newer than the last X seconds) - default  = 600

     SNMP
     -m | --monitor (snmp)
     -a | --action snmp(list-all, list-enabled, trap-info)
     -c | --critcal
     
# Service Check Examples

SHOW-CRITICAL-SYSTEM-EVENTS

/usr/bin/php -q NSXMon.php -H "yourhostname" -f "/usr/local/nagios/libexec/nsxmgrauthfile.cfg" -m "system-event" -a "show-crit" -c "1" -w "0" -n "" -x "0"

SHOW-RESOLVABLE-SYSTEM-EVENTS

/usr/bin/php -q NSXMon.php -H "yourhostname" -f "/usr/local/nagios/libexec/nsxmgrauthfile.cfg" -m "system-alarm" -a "show-resolvable" -c "" -C "5" -O "" -s "" -n ""

MEM-USAGE (CRITICAL > 95%)

/usr/bin/php -q NSXMon.php -H "yourhostname" -f "/usr/local/nagios/libexec/nsxmgrauthfile.cfg" -m "system" -a "mem-usage" -c "95" -C "" -O "" -s "" -n ""


# VMware Setup
1. Create Read-Only user via the API with full permissions to the API.
2. Use this username and password in the auth file.
