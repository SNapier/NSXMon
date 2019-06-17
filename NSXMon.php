#!/usr/bin/php
<?php

define("PROGRAM", 'NSXMon.php');
define("VERSION", '0.1.0');
define("STATUS_OK", 0);
define("STATUS_WARNING", 1);
define("STATUS_CRITICAL", 2);
define("STATUS_UNKNOWN", 3);
define("NEGATE", 0);
define("DEBUG", false);

//Plugin Action
function main() {
    $options = parse_args();
    if($options){
        //Get the Username, Password and NSX Manager Address from the auth file
        $keys = keyRing($options);
        if(!$keys){
            nagios_exit("UNKNOWN: NO KEYS FROM KEYRING - CHECK AUTH FILE (\"".$options['authfile']."\") FOR ERRORS", STATUS_UNKNOWN);
        }
        //Here we will parse the options array and see what function to pass off too
        //The root of the options is the --monitor or -m variable
        $monitor = $options['monitor'];
        $action = $options['action'];

        //Negate Vairable
        //If set then we will define to true and eval before each exit
        if($options['negate'] != ""){
            $negate = $options['negate'];
        }else{
            $negate = false;
        }

        switch ($monitor){
            case "system":
                //do stuff for system
                //switch for action
                switch($action){
                    case "":
                        nagios_exit("UNKNOWN: FAILED TO GET ACTION FOR CHECK COMMAND!", STATUS_UNKNOWN);
                        break;
                    case "getsum":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           //We have a system summary
                           $mymsg = "";
                           $myperf = "";
                           foreach($ss as $si => $sv){
                                 if($si == "hostName"){
                                    $myperf = $myperf . " hostName=".$sv;
                                    $mymsg = $mymsg . "hostName=".$sv.", ";
                                 }
                                 if($si == "ipv4Address"){
                                    $myperf = $myperf . " ipv4Address=".$sv;
                                    $mymsg = $mymsg . "ipv4Address=".$sv.", ";
                                 }
                                 if($si == "applianceName"){
                                    $myperf = $myperf . " applianceName=".$sv;
                                    $mymsg = $mymsg . "applianceName=".$sv.", ";
                                 }
                                 if($si == "ipv6Address"){
                                    if($sv != "" | $sv != NULL){
                                        $myipv6 = $sv;
                                    }else{
                                        $myipv6 = "0";
                                    }
                                    $myperf = $myperf . " ipv6Address=".$myipv6;
                                    $mymsg = $mymsg . "ipv6Address=".$myipv6.", ";
                                 }
                                 if($si == "dnsName"){
                                    $myperf = $myperf . " dnsName=".$sv;
                                    $mymsg = $mymsg . "dnsName=".$sv.", ";
                                 }
                                 if($si == "domainName"){
                                    $myperf = $myperf . " domainName=".$sv;
                                    $mymsg = $mymsg . "domainName=".$sv.", ";
                                 }
                                 if($si == "currentSystemDate"){
                                    $myperf = $myperf . " currentSystemDate=".$sv;
                                    $mymsg = $mymsg . "currentSystemDate=".$sv.", ";
                                 }
                                 if($si == "uptime"){
                                    $myperf = $myperf . " uptime=".$sv;
                                    $mymsg = $mymsg . "uptime=".$sv.", ";
                                 }
                                 if($si == "versionInfo"){
                                    $version = $sv->majorVersion.".".$sv->minorVersion.".".$sv->patchVersion.".".$sv->buildNumber;
                                    $myperf = $myperf . " version=".$version;
                                    $mymsg = $mymsg . "version=".$version.", ";
                                 }
                                 if($si == "cpuInfoDto"){
                                    $myperf = $myperf . " totalNumberCPU=".$sv->totalNoOfCPUs;
                                    $mymsg = $mymsg . "totalNumberCPU=".$sv->totalNoOfCPUs.", ";
                                    $myperf = $myperf . "totalCPUCapacity=".$sv->capacity;
                                    $mymsg = $mymsg . "totalCPUCapacity=".$sv->capacity.", ";
                                    $myperf = $myperf . "usedCPUCapacity=".$sv->usedCapacity;
                                    $mymsg = $mymsg . "usedCPUCapacity=".$sv->usedCapacity.", ";
                                    $myperf = $myperf . "freeCPUCapacity=".$sv->freeCapacity;
                                    $mymsg = $mymsg . "freeCPUCapacity=".$sv->freeCapacity.", ";
                                    $myperf = $myperf . "cpuPercentage=".$sv->usedPercentage;
                                    $mymsg = $mymsg . "cpuPercentage=".$sv->usedPercentage.", ";
                                    $myperf = $myperf . "indicatorCPU=".$sv->cpuUsageIndicator;
                                    $mymsg = $mymsg . "indicatorCPU=".$sv->cpuUsageIndicator.", ";
                                 }
                                 if($si == "memInfoDto"){
                                    $myperf = $myperf . "totalMemory=".$sv->totalMemory;
                                    $mymsg = $mymsg . "totalMemory=".$sv->totalMemory.", ";
                                    $myperf = $myperf . "usedMemory=".$sv->usedMemory;
                                    $mymsg = $mymsg . "usedMemory=".$sv->usedMemory.", ";
                                    $myperf = $myperf . "freeMemory=".$sv->freeMemory;
                                    $mymsg = $mymsg . "freeMemory=".$sv->freeMemory.", ";
                                    $myperf = $myperf . "memoryPercent=".$sv->usedPercentage;
                                    $mymsg = $mymsg . "memoryPercent=".$sv->usedPercentage.", ";
                                 }
                                 if($si == "storageInfoDto"){
                                    $myperf = $myperf . "totalStorage=".$sv->totalStorage;
                                    $mymsg = $mymsg . "totalStorage=".$sv->totalStorage.", ";
                                    $myperf = $myperf . "usedStorage=".$sv->usedStorage;
                                    $mymsg = $mymsg . "usedStorage=".$sv->usedStorage.", ";
                                    $myperf = $myperf . "freeStorage=".$sv->freeStorage;
                                    $mymsg = $mymsg . "freeStorage=".$sv->freeStorage.", ";
                                    $myperf = $myperf . "storagePercent=".$sv->usedPercentage;
                                    $mymsg = $mymsg . "storagePercent=".$sv->usedPercentage.", ";
                                 }
                           }
                        }
                        nagios_exit("OK: (NEGATED) SYSTEM SUMMARY (".$mymsg.") | ".$myperf, STATUS_OK);
                        break;
                    case "uptime":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "uptime"){
                                    $myuptime = $ssv;
                                }
                                if($ssk == "currentSystemDate"){
                                    $mydate = $ssv;
                                }
                           }
                        }
                        //no eval uptime
                        nagios_exit("OK: System Uptime = \"".$myuptime."\". The current system date is \"".$mydate."\". | Perfdata=0", STATUS_OK);
                        break;
                    case "version":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/global/info';
                        $si = getAPI($options);
                        if(!$si){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM INFO!", STATUS_UNKNOWN);
                        }else{
                           foreach($si as $sik => $sikv){
                                if($sik == "currentLoggedInUser"){
                                   $myuser = $sikv;
                                }
                                if($sik == "versionInfo"){
                                    foreach($sikv as $sikvk => $sikvv){
                                        if($sikvk == "majorVersion"){
                                            $mymajor = $sikvv;
                                        }
                                        if($sikvk == "minorVersion"){
                                            $myminor = $sikvv;
                                        }
                                        if($sikvk == "patchVersion"){
                                            $mypatch = $sikvv;
                                        }
                                        if($sikvk == "buildNumber"){
                                            $mybuild = $sikvv;
                                        }
                                    }
                                }
                                if($sik == "readOnlyAccess"){
                                   if($sikv == false){
                                        $myreadonly = "!FATAL FLAW - I CAN DO THINGS!";
                                   }else{
                                        $myreadonly = "OK";
                                   }

                                }
                           }
                        }
                        //no eval system info
                        nagios_exit("OK: SYSTEM INFO User=\"".$myuser."\", Version=\"".$mymajor.".".$myminor.".".$mypatch.".".$mybuild."\", ReadOnly=\"".$myreadonly."\" | Perfdata=0", STATUS_OK);
                        break;
                    case "cpu-usage":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "cpuInfoDto"){
                                    foreach($ssv as $cpuk => $cpuv){
                                        if($cpuk == "totalNoOfCPUs"){
                                            $mycputotal = $cpuv;
                                        }
                                        if($cpuk == "capacity"){
                                            $mycpucap = $cpuv;
                                        }
                                        if($cpuk == "usedCapacity"){
                                            $mycpucapused = $cpuv;
                                        }
                                        if($cpuk == "freeCapacity"){
                                            $mycpucapfree = $cpuv;
                                        }
                                        if($cpuk == "usedPercentage"){
                                            $mycpupercent = $cpuv;
                                        }
                                        if($cpuk == "cpuUsageIndicator"){
                                            $mycpuindicator = $cpuv;
                                        }
                                    }
                                }
                           }
                        }
                        //Is Negate Set
                        if(!$negate){
                            //eval cpu-usage
                            if($mycpupercent > $options['critical']){
                                //This is a critical exit
                                nagios_exit("CRITICAL: CPU-Utilization of \"".$mycpupercent."%\" is greater than \"".$options['critical']."%\". | perfdata=0", STATUS_CRITICAL);
                            }else{
                                //This is an OK exit
                                nagios_exit("OK: CPU-Utilization of \"".$mycpupercent."%\" . | perfdata=0", STATUS_OK);
                            }
                        }else{
                            //This is has a forced exit of OK
                            nagios_exit("OK: (NEGATED) - CPU-Utilization of \"".$mycpupercent."%\". | perfdata=0", NEGATE);
                        }
                        break;
                    case "cpu-cap":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "cpuInfoDto"){
                                    foreach($ssv as $cpuk => $cpuv){
                                        if($cpuk == "totalNoOfCPUs"){
                                            $mycputotal = $cpuv;
                                        }
                                        if($cpuk == "capacity"){
                                            $mycpucap = $cpuv;
                                        }
                                        if($cpuk == "usedCapacity"){
                                            $mycpucapused = $cpuv;
                                        }
                                        if($cpuk == "freeCapacity"){
                                            $mycpucapfree = $cpuv;
                                        }
                                        if($cpuk == "usedPercentage"){
                                            $mycpupercent = $cpuv;
                                        }
                                        if($cpuk == "cpuUsageIndicator"){
                                            $mycpuindicator = $cpuv;
                                        }
                                    }
                                }
                           }
                        }
                        //eval cpu-cap
                        if($mycpucap >= $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: CPU-Capacity of \"".$mycpucap."\" is greater than \"".$options['critical']."\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: CPU-Capacity of \"".$mycpucap."\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                    case "cpu-count":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "cpuInfoDto"){
                                    foreach($ssv as $cpuk => $cpuv){
                                        if($cpuk == "totalNoOfCPUs"){
                                            $mycputotal = $cpuv;
                                        }
                                        if($cpuk == "capacity"){
                                            $mycpucap = $cpuv;
                                        }
                                        if($cpuk == "usedCapacity"){
                                            $mycpucapused = $cpuv;
                                        }
                                        if($cpuk == "freeCapacity"){
                                            $mycpucapfree = $cpuv;
                                        }
                                        if($cpuk == "usedPercentage"){
                                            $mycpupercent = $cpuv;
                                        }
                                        if($cpuk == "cpuUsageIndicator"){
                                            $mycpuindicator = $cpuv;
                                        }
                                    }
                                }
                           }
                        }
                        //eval cpu-count
                        if($mycputotal > $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: CPU-Count of \"".$mycputotal."\" is greater than \"".$options['critical']."\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: CPU-Count of \"".$mycputotal."\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                    case "mem-total":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "memInfoDto"){
                                    foreach($ssv as $memk => $memv){
                                        if($memk == "totalMemory"){
                                            $mymemtotal = $memv;
                                        }
                                        if($memk == "usedMemory"){
                                            $mymemused = $memv;
                                        }
                                        if($memk == "freeMemory"){
                                            $mymemfree = $memv;
                                        }
                                        if($memk == "memUsedPercent"){
                                            $mymempercent = $memv;
                                        }
                                    }
                                }
                           }
                        }
                        //eval mem-total
                        if($mymemtotal >= $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: MEMORY-UTILIZATION of \"".$mymemtotal."\" is greater than \"".$options['critical']."\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: MEMORY-UTILIZATION of \"".$mymemtotal."\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                    case "mem-usage":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "memInfoDto"){
                                    foreach($ssv as $memk => $memv){
                                        if($memk == "totalMemory"){
                                            $mymemtotal = $memv;
                                        }
                                        if($memk == "usedMemory"){
                                            $mymemused = $memv;
                                        }
                                        if($memk == "freeMemory"){
                                            $mymemfree = $memv;
                                        }
                                        if($memk == "usedPercentage"){
                                            $mymempercent = $memv;
                                        }
                                    }
                                }
                           }
                        }
                        //eval mem-usage
                        if($mymempercent >= $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: MEMORY-UTILIZATION of \"".$mymempercent."%\" is greater than \"".$options['critical']."%\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: MEMORY-UTILIZATION of \"".$mymempercent."%\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                    case "storage-total":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "storageInfoDto"){
                                    foreach($ssv as $storek => $storev){
                                        if($storek == "totalStorage"){
                                            $mystoretotal = $storev;
                                        }
                                        if($memk == "usedStorage"){
                                            $mystoreused = $storev;
                                        }
                                        if($memk == "freeStorage"){
                                            $mystorefree = $storev;
                                        }
                                        if($storek == "usedPercentage"){
                                            $mystorepercent = $storev;
                                        }
                                    }
                                }
                           }
                        }
                        //eval storage-total
                        if($mystoretotal >= $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: STORAGE-UTILIZATION of \"".$mystoretotal."\" is greater than \"".$options['critical']."\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: STORAGE-UTILIZATION of \"".$mystoretotal."\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                    case "storage-usage":
                        $options['urlsuffix'] = '/api/1.0/appliance-management/summary/system';
                        $ss = getAPI($options);
                        if(!$ss){
                           nagios_exit("UNKNOWN: FAILED TO GET SYSTEM SUMMARY!", STATUS_UNKNOWN);
                        }else{
                           foreach($ss as $ssk => $ssv){
                                if($ssk == "storageInfoDto"){
                                    foreach($ssv as $storek => $storev){
                                        if($storek == "totalStorage"){
                                            $mystoretotal = $storev;
                                        }
                                        if($storek == "usedStorage"){
                                            $mystoreused = $storev;
                                        }
                                        if($storek == "freeStorage"){
                                            $mystorefree = $storev;
                                        }
                                        if($storek == "usedPercentage"){
                                            $mystorepercent = $storev;
                                        }
                                    }
                                }
                           }
                        }
                        //eval storae-usage
                        if($mystorepercent >= $options['critical']){
                            //This is a critical exit
                            nagios_exit("CRITICAL: STORAGE-UTILIZATION of \"".$mystorepercent."%\" is greater than \"".$options['critical']."%\". | perfdata=0", STATUS_CRITICAL);
                        }else{
                            //This is an OK exit
                            nagios_exit("OK: STORAGE-UTILIZATION of \"".$mystorepercent."%\" . | perfdata=0", STATUS_OK);
                        }
                        break;
                }
                break;
            case "system-alarm":
                //do stuff for system
                //switch for action
                switch($action){
                    case "":
                        nagios_exit("UNKNOWN: FAILED TO GET ACTION FOR CHECK COMMAND SYSTEM-ALARM!", STATUS_UNKNOWN);
                        break;
                    case "show-resolvable":
                        $options['urlsuffix'] = '/api/2.0/services/systemalarms?pageSize=1000';
                        $sa = getAPI($options);
                        if(!$sa){
                            nagios_exit("UNKNOWN: FAILED TO GET SYSTEM ALARM CONTENTS!", STATUS_UNKNOWN);
                        }else{
                            //echo print_r($sa, true);
                            //For system events we will want the ability to extend the specificity
                            // Evaluate the number of critical events for a specific eventid
                            $mycriticalcount = "1";
                            if($options['criticalcount'] != ""){
                                $mycriticalcount = $options['criticalcount'];
                            }
                            // Evaluate the number of critical events for a specific objectid
                            $mycriticalobject = "";
                            if($options['criticalobject'] != ""){
                                $mycriticalobject = $options['criticalobject'];
                            }

                            //Should be set globally at the begining of main
                            //TODO
                            $now = time();
                            //To verify the event to time you can enable this option
                            //echo "Now = ".date(DATE_ATOM, $now)."\n";
                            if($options['scanrange'] != ""){
                                $myscanrange = $options['scanrange'];
                            }else{
                                $myscanrange = "600";
                            }
                            $neg10 = $now - $myscanrange;
                            //To verify the event to time you can enable this option
                            //echo "Neg10 = ".date(DATE_ATOM, $neg10)."\n";
                            $ai = 0;
                            $mysystemalarms = "";
                            foreach($sa as $sak => $sav){
                                //echo print_r($sav, true);
                                foreach($sav->data as $savk => $savv){
                                    //VMware Timestamp is in microtime, with the following we strip the extra 3 digits from the time
                                    //and return athe standard timestamp
                                    $timestamp = substr($savv->timestamp, 0, 10);
                                    if($timestamp >= $neg10 && $savv->resolvable == true){
                                        //echo print_r($savv, true);
                                        if($mycriticalobject != ""){
                                            //we are searching
                                            if($savv->objectId == $mycriticalobject){
                                                if($ai != "0"){
                                                    $mysystemalarms = $mysystemalarms .",".$savv->alarmId;
                                                }else{
                                                    $mysystemalarms = $myauditfails . $savv->alarmId;
                                                }
                                                $ai ++;
                                            }
                                        }else{
                                            if($ai != "0"){
                                                $mysystemalarms = $mysystemalarms .",".$savv->alarmId;
                                            }else{
                                                $mysystemalarms = $myauditfails . $savv->alarmId;
                                            }
                                            $ai ++;
                                        }
                                    }
                                }
                            }
                        }
                        if($ai >= $mycriticalcount){
                            nagios_exit("CRITICAL: FOUND (".$ai.") RESOLVABLE ALARMS. ALARMID (".$mysystemalarms."). SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_CRITICAL);
                        }else{
                            nagios_exit("OK: FOUND (".$ai.") RESOLVABLE ALARMS. SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_OK);
                        }
                        break;
                    case "show-unresolvable":
                        $options['urlsuffix'] = '/api/2.0/services/systemalarms?pageSize=1000';
                        $sa = getAPI($options);
                        if(!$sa){
                            nagios_exit("UNKNOWN: FAILED TO GET SYSTEM ALARM CONTENTS!", STATUS_UNKNOWN);
                        }else{
                            //echo print_r($sa, true);
                            //For system events we will want the ability to extend the specificity
                            // Evaluate the number of critical events for a specific eventid
                            $mycriticalcount = "1";
                            if($options['criticalcount'] != ""){
                                $mycriticalcount = $options['criticalcount'];
                            }
                            // Evaluate the number of critical events for a specific objectid
                            $mycriticalobject = "";
                            if($options['criticalobject'] != ""){
                                $mycriticalobject = $options['criticalobject'];
                            }

                            //Should be set globally at the begining of main
                            //TODO
                            $now = time();
                            //To verify the event to time you can enable this option
                            //echo "Now = ".date(DATE_ATOM, $now)."\n";
                            if($options['scanrange'] != ""){
                                $myscanrange = $options['scanrange'];
                            }else{
                                $myscanrange = "600";
                            }
                            $neg10 = $now - $myscanrange;
                            //To verify the event to time you can enable this option
                            //echo "Neg10 = ".date(DATE_ATOM, $neg10)."\n";
                            $ai = 0;
                            $mysystemalarms = "";
                            foreach($sa as $sak => $sav){
                                //echo print_r($sav, true);
                                foreach($sav->data as $savk => $savv){
                                    //VMware Timestamp is in microtime, with the following we strip the extra 3 digits from the time
                                    //and return athe standard timestamp
                                    $timestamp = substr($savv->timestamp, 0, 10);
                                    if($timestamp >= $neg10 && $savv->resolvable == false){
                                        //echo print_r($savv, true);
                                        if($mycriticalobject != ""){
                                            //we are searching
                                            if($savv->objectId == $mycriticalobject){
                                                if($ai != "0"){
                                                    $mysystemalarms = $mysystemalarms .",".$savv->alarmId;
                                                }else{
                                                    $mysystemalarms = $myauditfails . $savv->alarmId;
                                                }
                                                $ai ++;
                                            }
                                        }else{
                                            if($ai != "0"){
                                                $mysystemalarms = $mysystemalarms .",".$savv->alarmId;
                                            }else{
                                                $mysystemalarms = $myauditfails . $savv->alarmId;
                                            }
                                            $ai ++;
                                        }
                                    }
                                }
                            }
                        }
                        if($ai >= $mycriticalcount){
                            nagios_exit("CRITICAL: FOUND (".$ai.") UNRESOLVABLE ALARMS. ALARMID (".$mysystemalarms."). SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_CRITICAL);
                        }else{
                            nagios_exit("OK: FOUND (".$ai.") UNRESOLVABLE ALARMS. SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_OK);
                        }
                        break;
                }
                break;
            case "audit-log":
                //do stuff for system
                //switch for action
                switch($action){
                    case "":
                        nagios_exit("UNKNOWN: FAILED TO GET ACTION FOR CHECK COMMAND AUDIT-LOG!", STATUS_UNKNOWN);
                        break;
                    case "show-fail":
                        $options['urlsuffix'] = '/api/2.0/auditlog?pageSize=1000&sortBy=timestamp&sortOrderAscending=false';
                        $al = getAPI($options);
                        if(!$al){
                            nagios_exit("UNKNOWN: FAILED TO GET AUDIT LOG CONTENTS!", STATUS_UNKNOWN);
                        }else{
                            //echo print_r($al, true);
                            //For system events we will want the ability to extend the specificity
                            // Evaluate the number of critical events for a specific eventid
                            $mycriticalcount = "1";
                            if($options['criticalcount'] != ""){
                                $mycriticalcount = $options['criticalcount'];
                            }
                            // Evaluate the number of critical events for a specific objectid
                            $mycriticalobject = "";
                            if($options['criticalobject'] != ""){
                                $mycriticalobject = $options['criticalobject'];
                            }

                            //Should be set globally at the begining of main
                            //TODO
                            $now = time();
                            //To verify the event to time you can enable this option
                            //echo "Now = ".date(DATE_ATOM, $now)."\n";
                            if($options['scanrange'] != ""){
                                $myscanrange = $options['scanrange'];
                            }else{
                                $myscanrange = "600";
                            }
                            $neg10 = $now - $myscanrange;
                            //To verify the event to time you can enable this option
                            //echo "Neg10 = ".date(DATE_ATOM, $neg10)."\n";
                            $ai = 0;
                            $myauditfails = "";
                            foreach($al as $alk => $alv){
                                //echo print_r($alv, true);
                                foreach($alv->data as $alvk => $alvv){
                                    //VMware Timestamp is in microtime, with the following we strip the extra 3 digits from the time
                                    //and return athe standard timestamp
                                    $timestamp = substr($alvv->timestamp, 0, 10);
                                    if($timestamp >= $neg10 && $alvv->status == "FAILURE"){
                                        //echo print_r($alvv, true);
                                        //To verify the event to time you can enable this option
                                        //echo "ALVTime = ".date(DATE_ATOM, $timestamp)."\n";
                                        if($mycriticalobject != ""){
                                            //we are searching
                                            if($alvv->operation == $mycriticalobject){
                                                if($ai != "0"){
                                                    $myauditfails = $myauditfails .",".$alvv->id;
                                                }else{
                                                    $myauditfails = $myauditfails . $alvv->id;
                                                }
                                                $ai ++;
                                            }
                                        }else{
                                            if($ai != "0"){
                                                $myauditfails = $myauditfails .",".$alvv->id;
                                            }else{
                                                $myauditfails = $myauditfails . $alvv->id;
                                            }
                                            $ai ++;
                                        }
                                    }
                                }
                            }
                        }
                        if($ai >= $mycriticalcount){
                            nagios_exit("CRITICAL: FOUND (".$ai.") FAILURE RECORDS IN THE AUDIT LOG. EVENTID (".$myauditfails."). SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_CRITICAL);
                        }else{
                            nagios_exit("OK: FOUND (".$ai.") FAILURE RECORDS IN THE AUDIT LOG. SAMPLE-COUNT (1000) SCAN-RANGE (Within -".$myscanrange." Seconds) CRITICAL-COUNT(".$mycriticalcount.")| Perfdata=0", STATUS_OK);
                        }
                        break;
                }
                break;
            case "snmp":
                //do stuff for snmp
                switch ($action){
                    case "":
                        //empty
                        break;
                    case "list-all":
                        $event = array();
                        $e = '0';
                        $options['urlsuffix'] = '/api/2.0/services/snmp/trap';
                        $sl = getAPI($options);
                        if(!$sl){
                           nagios_exit("UNKNOWN: FAILED TO GET LIST OF SNMP TRAPS!", STATUS_UNKNOWN);
                        }else{
                           foreach($sl as $sevt){
                                $TotalEventCount = count($sevt);
                                foreach($sevt as $evt){
                                    $event[$e]['OID'] = $evt->oid;
                                    $event[$e]['ID'] = $evt->eventId;
                                    $event[$e]['CNAME'] = $evt->componentName;
                                    $event[$e]['STATUS'] = $evt->enabled;
                                    $e ++;
                                }
                           }
                           if($TotalEventCount > "0"){
                                $elist = "";
                                foreach($event as $emsg){
                                    $elist = $elist . "(id=".$emsg['ID'].",oid=".$emsg['OID'].",cname=".$emsg['CNAME'].")";
                                }
                           }
                           nagios_exit("OK: TOTAL OF (".$TotalEventCount.") SNMP EVENTS DEFINED. \"".$elist."\" . | eventCount=$TotalEventCount;", STATUS_OK);
                        }
                        break;
                    case "list-enabled":
                        $event = array();
                        $e = '0';
                        $options['urlsuffix'] = '/api/2.0/services/snmp/trap';
                        $sl = getAPI($options);
                        if(!$sl){
                           nagios_exit("UNKNOWN: FAILED TO GET LIST OF SNMP TRAPS!", STATUS_UNKNOWN);
                        }else{
                           foreach($sl as $sevt){
                                $TotalEventCount = count($sevt);
                                $TotalEnabledCount = 0;
                                foreach($sevt as $evt){
                                    if($evt->enabled == "true"){
                                        $event[$e]['OID'] = $evt->oid;
                                        $event[$e]['ID'] = $evt->eventId;
                                        $event[$e]['CNAME'] = $evt->componentName;
                                        $event[$e]['STATUS'] = $evt->enabled;
                                        $e ++;
                                        $TotalEnabledCount ++;
                                    }
                                }
                           }
                           if($TotalEnabledCount > "0"){
                                $elist = "";
                                foreach($event as $emsg){
                                    $elist = $elist . "(id=".$emsg['ID'].",oid=".$emsg['OID'].",cname=".$emsg['CNAME'].")";
                                }
                           }
                           nagios_exit("OK: TOTAL OF (".$TotalEnabledCount.") SNMP EVENTS ENABLED OF A TOTAL (".$TotalEventCount.") EVENTS DEFINED \"".$elist."\" . | enabledCount=$TotalEnabledCount;", STATUS_OK);
                        }
                        break;
                    case "trap-info":
                        //Information for SNMP Specific OID
                        //<TODO>
                        break;
                }
                break;
            case "system-event":
                //do stuff for system event list
                switch($action){
                    case "":
                        //Nothing Doing
                        break;
                    case "show-crit":
                        //Look for specific event in the retrned results
                        // GET /api/2.0/systemevent
                        // 2 optional parameters
                        // startIndex -> then position in results to start with | int
                        // pagesize -> number of total results to fetch | int

                        //For system events we will want the ability to extend the specificity
                        // Evaluate the number of critical events for a specific eventid
                        $mycriticalcount = "1";
                        if($options['criticalcount'] != ""){
                            $mycriticalcount = $options['criticalcount'];
                        }
                        // Evaluate the number of critical events for a specific objectid
                        $mycriticalobject = "";
                        if($options['criticalobject'] != ""){
                            $mycriticalobject = $options['criticalobject'];
                        }
                        // duration of history to scan on the nsx manager
                        $myscanrange = "600";
                        if($options['scanrange'] != ""){
                            $myscanrange = $options['scanrange'];
                        }

                        $options['urlsuffix'] = '/api/2.0/systemevent?pageSize=1000&sortBy=timestamp';
                        $se = getAPI($options);
                        if(!$se){
                           nagios_exit("UNKNOWN: FAILED TO GET LIST OF SYSTEM EVENTS!", STATUS_UNKNOWN);
                        }else{
                            $critCount = 0;
                            $mymsg = "";
                            foreach($se as $sek => $sev){
                                $events = $sev->data;
                                //Count the total number of events returned, this should equal the page size attribute
                                $eventCount = count($events);
                                $now = time();
                                $neg10 = $now - $myscanrange;
                                foreach($events as $ek => $ev){
                                    $timestamp = substr($ev->timestamp, 0, 10);
                                    //look at each event and see if the event is of a severity of critical
                                    if ($ev->severity == "Critical"){
                                        //We found a critical so we should see if the eventId is in our critical list
                                        if($mycriticalobject != ""){
                                            if (isset($ev->objectId) && $ev->objectId == $mycriticalobject){
                                                if(($ev->eventCode == $options['critical']) && ($timestamp >= $neg10)){
                                                $critCount ++;
                                                $mymsg = $mymsg . $critCount."=Timestamp:\"".$timestamp."\" ";
                                                }
                                            }
                                        }else{
                                            if($options['critical'] != ""){
                                                if($ev->eventCode == $options['critical'] && $timestamp >= $neg10){
                                                    $critCount ++;
                                                    $mymsg = $mymsg . $critCount."=ObjectId:\"".$ev->objectId."\"Message:\"".$ev->message."\",";
                                                }
                                            }else{
                                                //This is an error point, we do not have an object or an object id to search for
                                                //we should throw an error and exit unknown
                                                nagios_exit("UNKNOWN: FAILED TO GET EVENTID TO SEARCH FOR!", STATUS_UNKNOWN);
                                            }

                                        }
                                    }
                                }
                            }
                            //Evaluate the Results
                            if($mycriticalobject != "" && $critCount != '0' && $critCount >= $mycriticalcount && $negate != "true"){
                                nagios_exit("CRITICAL: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\"(".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", STATUS_CRITICAL);
                            }elseif($critCount != '0' && $critCount >= $mycriticalcount && $negate != "true"){
                                nagios_exit("CRITICAL: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", STATUS_CRITICAL);
                            }elseif($mycriticalobject != "" && $critCount != '0' && $critCount >= $mycriticalcount && $negate == "true"){
                                nagios_exit("OK: (NEGATED)-(".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", NEGATE);
                            }elseif($critCount != '0' && $critCount >= $mycriticalcount && $negate == "true"){
                                nagios_exit("OK: (NEGATED)-(".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", NEGATE);
                            }elseif($mycriticalobject != "" && $critCount == '0'){
                                nagios_exit("OK: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\" EventCount=\"".$eventCount."\" | perfdata=0", STATUS_OK);
                            }else{
                                nagios_exit("OK: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\". EventCount=\"".$eventCount."\" | perfdata=0", STATUS_OK);
                            }
                        }
                        break;
                    case "show-high":
                        //Look for specific event in the retrned results
                        // GET /api/2.0/systemevent
                        // 2 optional parameters
                        // startIndex -> then position in results to start with | int
                        // pagesize -> number of total results to fetch | int

                        //For system events we will want the ability to extend the specificity
                        // Evaluate the number of critical events for a specific eventid
                        $mycriticalcount = "1";
                        if($options['criticalcount'] != ""){
                            $mycriticalcount = $options['criticalcount'];
                        }
                        // Evaluate the number of critical events for a specific objectid
                        $mycriticalobject = "";
                        if($options['criticalobject'] != ""){
                            $mycriticalobject = $options['criticalobject'];
                        }
                        // duration of history to scan on the nsx manager
                        $myscanrange = "600";
                        if($options['scanrange'] != ""){
                            $myscanrange = $options['scanrange'];
                        }
                        $options['urlsuffix'] = '/api/2.0/systemevent?pageSize=1000&sortBy=timestamp';
                        $se = getAPI($options);
                        if(!$se){
                           nagios_exit("UNKNOWN: FAILED TO GET LIST OF SYSTEM EVENTS!", STATUS_UNKNOWN);
                        }else{
                            $critCount = 0;
                            $mymsg = "";
                            foreach($se as $sek => $sev){
                                $events = $sev->data;
                                //Count the total number of events returned, this should equal the page size attribute
                                $eventCount = count($events);
                                $now = time();
                                $neg10 = $now - $myscanrange;
                                foreach($events as $ek => $ev){
                                    $timestamp = substr($ev->timestamp, 0, 10);
                                    //look at each event and see if the event is of a severity of High
                                    if ($ev->severity == "High"){
                                        //We found a High Level Event so we should see if the eventId is in our critical list
                                        if($mycriticalobject != ""){
                                            if (isset($ev->objectId) && $ev->objectId == $mycriticalobject){
                                                if(($ev->eventCode == $options['critical']) && ($timestamp >= $neg10)){
                                                $critCount ++;
                                                $mymsg = $mymsg . $critCount."=Timestamp:\"".$timestamp."\" ";
                                                }
                                            }
                                        }else{
                                            if($options['critical'] != ""){
                                                if($ev->eventCode == $options['critical'] && $timestamp >= $neg10){
                                                    $critCount ++;
                                                    $mymsg = $mymsg . $critCount."=ObjectId:\"".$ev->objectId."\"Message:\"".$ev->message."\",";
                                                }
                                            }else{
                                                //This is an error point, we do not have an object or an object id to search for
                                                //we should throw an error and exit unknown
                                                nagios_exit("UNKNOWN: FAILED TO GET EVENTID TO SEARCH FOR!", STATUS_UNKNOWN);
                                            }

                                        }
                                    }
                                }
                            }
                            //Evaluate the Results
                            if($mycriticalobject != "" && $critCount != '0' && $critCount >= $mycriticalcount && $negate != "true"){
                                nagios_exit("CRITICAL: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\"(".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", STATUS_CRITICAL);
                            }elseif($critCount != '0' && $critCount >= $mycriticalcount && $negate != "true"){
                                nagios_exit("CRITICAL: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", STATUS_CRITICAL);
                            }elseif($mycriticalobject != "" && $critCount != '0' && $critCount >= $mycriticalcount && $negate == "true"){
                                nagios_exit("OK: (NEGATED)-(".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", NEGATE);
                            }elseif($critCount != '0' && $critCount >= $mycriticalcount && $negate == "true"){
                                nagios_exit("OK: (NEGATED)-(".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" (".$mymsg.") . EventCount=\"".$eventCount."\" | perfdata=0", NEGATE);
                            }elseif($mycriticalobject != "" && $critCount == '0'){
                                nagios_exit("OK: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\" ON OBJECTID \"".$mycriticalobject."\" EventCount=\"".$eventCount."\" | perfdata=0", STATUS_OK);
                            }else{
                                nagios_exit("OK: (".$critCount.") EVENTS FOUND FOR EVENTID \"".$options['critical']."\". EventCount=\"".$eventCount."\" | perfdata=0", STATUS_OK);
                            }
                        }
                        break;
                }
                break;
        }
    }else{
        //We didn't receive anything from the nagios command input for options
        //We will exit with unknown and pass the error
        nagios_exit("UNKNOWN: NO OPTIONS PASSED FROM COMMAND - CHECK THE NAGIOS COMMAND AND FILE PERMISSIONS FOR ISSUES", STATUS_UNKNOWN);
    }
}

//Auth File Content Parse
// File Path = "/usr/local/nagios/libexec/cfg/<file-name>.cfg"
// Auth File Structure
/*
    #------------------#
    #    properties    #
    #------------------#
    NSXMGRURL=https://192.168.1.1
    USERNAME=user
    PASSWORD=password
    KEY=predefined auth encode for username and password
*/
function keyRing($options){
    //get lines for username and password from the authfile
    $keys = new SplFileObject($options['authfile'], 'r' );
        $keys->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::READ_AHEAD | SplFileObject::DROP_NEW_LINE);
        while(!$keys->eof()){
        foreach($keys as $key){
            //Get the first char of the line
            $fchar = mb_substr($key, 0, 1);
            //Ignore all lines starting with the pund (#) sign
            if($fchar != "#"){
                list($authpart,$authvalue) = explode("=", $key);
                if($authpart == "NSXMGRURL"){
                    $mykeys['nsxmgrurl'] = $authvalue;
                }elseif($authpart == "USERNAME"){
                    $mykeys['username'] = $authvalue;
                }elseif($authpart == "PASSWORD"){
                    $mykeys['password'] = "$authvalue";
                }elseif($authpart == "KEY"){
                    $mykeys['key'] = "$authvalue";
                }else{
                    nagios_exit('UNKNOWN VALUE FROM AUTH FILE!', STATUS_UNKNOWN);
                }
            }
        }
    }
    //return the array for use in authentication
    return $mykeys;
}

function getAPI($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
    $upwd = "$usr:$pwd";

    //Example of hardocded URL
    //$url = 'https://'.$options['hostname'].'/api/1.0/appliance-management/summary/system';
    //Replace hard coded URL create the URL based on values from options array
    $urlprefix = "https://";
    //The url suffix may contain query parametrs for needed API data
    //
    if($options['urlsuffix'] != "" | $options['urlsuffix'] != NULL ){
        $urlsuffix = $options['urlsuffix'];
    }else{
        //Give the default response as system summary
        //$urlsuffix = '/api/1.0/appliance-management/summary/system';
        //This Should be an error catch and exit for nagios
        nagios_exit("UNKNOWN: !PLUGIN ERROR!, NO CURL URL PASSED TO GETAPI. WHY ME?", STATUS_UNKNOWN);
    }
    $url = $urlprefix.$options['hostname'].$urlsuffix;

    //THE CURL COMMAND
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url);

    //OUR MONITORING LIBRARY SHOULD NEVER HAVE PUT COMMANDS SAVE FOR AUTHENTICATION
    //RELATED REQUIREMENTS
    curl_setopt($ch, CURLOPT_HTTPGET, true);

    //JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

    //USE THE KEYS TO AUTHENTICATE
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$upwd");

    //EXECUTE THE CURL COMMAND
    $result = curl_exec($ch);
    curl_close($ch);

    //WE SHOULD NOW HAVE RESULTS IN JSON
    //DECODE TO ARRAY AND RETURN TO THE CALLER
    $apidata = json_decode($result);
    return $apidata;
}

// USAGE - HELP
// TODO
// Examples
function fullusage() {
print(
        "NSXmon.php - v".VERSION."
         Usage: ".PROGRAM." -H \"<hostname>\"  -f \"/path/to/authfile.cfg\" -m \"<monitor>\" -a \"<action>\" -c \"<critical>\" -C \"<criticalCount>\" -O \"<objectId>\" -s \"<scanrange>\" -n\"<true, false>\"
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

     "
    );
}


//Helper functions
//TODO Credit To PHP Lib Creator
function debug_logging($message) {
    if(DEBUG) {
        echo $message;
    }
}

function plugin_error($error_message) {
    print("***ERROR***:\n\n{$error_message}\n\n");
    fullusage();
    nagios_exit('', STATUS_UNKNOWN);
}

function nagios_exit($stdout='', $exitcode=0) {
    print($stdout);
    exit($exitcode);
    echo "";
}

function parse_args() {
    $specs = array(array('short' => 'h',
                         'long' => 'help',
                         'required' => false),
                   array('short' => 'H',
                         'long' => 'hostname',
                         'required' => true),
                   array('short' => 'f',
                         'long' => 'authfile',
                         'required' => true),
                   array('short' => 'm',
                         'long' => 'monitor',
                         'required' => true),
                   array('short' => 'a',
                         'long' => 'action',
                         'required' => true),
                   array('short' => 'c',
                         'long' => 'critical',
                         'required' => false),
                   array('short' => 'C',
                         'long' => 'criticalcount',
                         'required' => false),
                   array('short' => 'O',
                         'long' => 'criticalobject',
                         'required' => false),
                   array('short' => 's',
                         'long' => 'scanrange',
                         'required' => false),
                   array('short' => 'n',
                         'long' => 'negate',
                         'required' => false),
    );

    $options = parse_specs($specs);
    return $options;
}

function parse_specs($specs) {
    $shortopts = '';
    $longopts = array();
    $opts = array();

    // Create the array that will be passed to getopt
    // Accepts an array of arrays, where each contained array has three
    // entries, the short option, the long option and required
    foreach($specs as $spec) {
        if(!empty($spec['short'])) {
            $shortopts .= "{$spec['short']}:";
        }
        if(!empty($spec['long'])) {
            $longopts[] = "{$spec['long']}:";
        }
    }

    // Parse with the builtin getopt function
    $parsed = getopt($shortopts, $longopts);

    // Make sure the input variables are sane. Also check to make sure that
    // all flags marked required are present.
    foreach($specs as $spec) {
        $l = $spec['long'];
        $s = $spec['short'];

        if(array_key_exists($l, $parsed) && array_key_exists($s, $parsed)) {
            plugin_error("Command line parsing error: Inconsistent use of flag: ".$spec['long']);
        }
        if(array_key_exists($l, $parsed)) {
            $opts[$l] = $parsed[$l];
        }
        elseif(array_key_exists($s, $parsed)) {
            $opts[$l] = $parsed[$s];
        }
        elseif($spec['required'] == true) {
            plugin_error("Command line parsing error: Required variable ".$spec['long']." not present.");
        }
    }
    return $opts;
}

//Last Line - Call the Main Function
main();
?>
