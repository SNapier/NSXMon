#!/usr/bin/php
<?php

define("PROGRAM", 'NSXMon.php');
define("VERSION", '0.0.436');
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
                    case "uptime":
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysinfo
                        $si = getSysInfo($options);
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
                                        if($sikvk == "buildVesrion"){
                                            $mybuild = $sikvv;
                                        }
                                    }
                                }
                                if($sik == "readOnlyAccess"){
                                   $myreadonly = $sikv; 
                                }
                           }   
                        }
                        //no eval system info
                        nagios_exit("OK: SYSTEM INFO User=\"".$myuser."\", Version=\"".$mymajor.".".$myminor.".".$mypatch.".".$mybuild."\", ReadOnly=\"".$myreadonly."\" | Perfdata=0", STATUS_OK);
                        break;
                    case "cpu-usage":
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get sysSum
                        $ss = getSysSum($options);
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
                        //get AuditLog
                        $sa = getSystemAlarms($options);
                        if(!$sa){
                            nagios_exit("UNKNOWN: FAILED TO GET SYSTEM ALARM CONTENTS!", STATUS_UNKNOWN); 
                        }else{
                            //echo print_r($sa, true);
                            //For system events we will want the ability to extend the specificity
                            // Evaluate the number of critical events for a specific eventid
                            $mycriticalcount = "0";
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
                        //get AuditLog
                        $al = getAuditLog($options);
                        if(!$al){
                            nagios_exit("UNKNOWN: FAILED TO GET AUDIT LOG CONTENTS!", STATUS_UNKNOWN); 
                        }else{
                            //echo print_r($al, true);
                            //For system events we will want the ability to extend the specificity
                            // Evaluate the number of critical events for a specific eventid
                            $mycriticalcount = "0";
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
                        //get them all
                        $events = array();
                        $e = 0;
                        $sl = getSNMPList($options);
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
                           nagios_exit("OK: TOTAL OF (".$TotalEventCount.") SNMP EVENTS DEFINED. \"".$elist."\" . | eventCount=$TotalEventCount;0;0;", STATUS_OK);
                        }
                        break;
                    case "list-enabled":
                        //get only the enabled traps
                        $events = array();
                        $e = 0;
                        $sl = getSNMPList($options);
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
                           nagios_exit("OK: TOTAL OF (".$TotalEnabledCount.") SNMP EVENTS ENABLED OF A TOTAL (".$TotalEventCount.") EVENTS DEFINED \"".$elist."\" . | enabledCount=$TotalEnabledCount;0;0;", STATUS_OK);
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
                        
                        // get the data
                        $se = getSysEvents($options);
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
                                    //look at each event and see if the event is of a severity of critical
                                    if ($ev->severity == "Critical"){
                                        //We found a critical so we should see if the eventId is in our critical list
                                        if($mycriticalobject != ""){
                                            if (isset($ev->objectId) && $ev->objectId == $mycriticalobject){
                                                if(($ev->eventCode == $options['critical']) && ($ev->timestamp > $neg10)){
                                                $critCount ++;
                                                $mymsg = $mymsg . $critCount."=Timestamp:\"".$ev->timestamp."\" ";
                                                }
                                            }
                                        }else{
                                            if($options['critical'] != ""){
                                                if($ev->eventCode == $options['critical'] && $ev->timestamp > $neg10){
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
                        
                        // get the data
                        $se = getSysEvents($options);
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
                                    //look at each event and see if the event is of a severity of High
                                    if ($ev->severity == "High"){
                                        //We found a High Level Event so we should see if the eventId is in our critical list
                                        if($mycriticalobject != ""){
                                            if (isset($ev->objectId) && $ev->objectId == $mycriticalobject){
                                                if(($ev->eventCode == $options['critical']) && ($ev->timestamp > $neg10)){
                                                $critCount ++;
                                                $mymsg = $mymsg . $critCount."=Timestamp:\"".$ev->timestamp."\" ";
                                                }
                                            }
                                        }else{
                                            if($options['critical'] != ""){
                                                if($ev->eventCode == $options['critical'] && $ev->timestamp > $neg10){
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
    USERNAME="user"
    PASSWORD="password"
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
                }else{
                    nagios_exit('UNKNOWN VALUE FROM AUTH FILE!', STATUS_UNKNOWN);
                }
            }
        }
    }
    //return the array for use in authentication
    return $mykeys;
}

function getSysSum($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
	$url = 'https://'.$options['hostname'].'/api/1.0/appliance-management/summary/system';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $sysSum = json_decode($rcurl);
    return $sysSum;
}

function getSysInfo($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
	$url = 'https://'.$options['hostname'].'/api/1.0/appliance-management/global/info';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $sysInfo = json_decode($rcurl);
    return $sysInfo;
}

function getSystemAlarms($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
	$url = 'https://'.$options['hostname'].'/api/2.0/services/systemalarms?pageSize=10';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $sysInfo = json_decode($rcurl);
    return $sysInfo;
}

function getSNMPList($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
	$url = 'https://'.$options['hostname'].'/api/2.0/services/snmp/trap';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $snmpTraps = json_decode($rcurl);
    return $snmpTraps;
}

function getSysEvents($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
    //the will be done via the nagios command args and non default input vars
	$url = 'https://'.$options['hostname'].'/api/2.0/systemevent?pageSize=1000&sortBy=timestamp';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $sysEvents = json_decode($rcurl);
    return $sysEvents;
}

function getAuditLog($options){
    $mykeys = keyRing($options);
    $usr = $mykeys['username'];
    $pwd = $mykeys['password'];
    //the will be done via the nagios command args and non default input vars
	$url = 'https://'.$options['hostname'].'/api/2.0/auditlog?pageSize=1000&sortBy=timestamp&sortOrderAscending=false';
    $rcurl = exec("curl -X GET -ks -H 'Content-Type: application/json' -H 'Accept: application/json' -H 'Authorization: Basic bmFnaW9zbW9uOk40Z2lvJE0wbjF0b3Ihbkc=' -i '$url'");
    //Success will reutrn code 200 and data
    $auditLog = json_decode($rcurl);
    return $auditLog;
}

// USAGE - HELP
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
     -a | --action* system-event(show-resolvable)
     -c | --critcal ()
     -C | --criticalcount (The number of number of matched events before alert)
     -O | --criticalobject (Specific alarm objectId to match against)
     -s | --scanrange (Only scan events newer than the last X seconds) - default = 600
     
     AUDIT-LOG
     -m | --monitor (audit-log)
     -a | --action audit-log(show-failures)
     -c | --critcal
     -C | --criticalcount (The number of number of matched events before alert) -> default = 0
     -O | --criticalobject (Specific operation type to match against)
     -s | --scanrange (Only scan events newer than the last X seconds) - seconds only
     
     SNMP
     -m | --monitor (snmp)
     -a | --action snmp(list-all, list-enabled, trap-info)
     -c | --critcal
     
     "
    );
}


//Helper functions
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
                         'required' => false)
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
