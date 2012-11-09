<?php

    function getProgressTable($guid) {
        $body_data = array();
        $course_starting_date = 0;
        global $db;
        // get course starting date
        $query = "SELECT course_starting_date FROM ".DB_PREFIX."educourses WHERE course_guid=".$guid;
        $result = $db->query($query);
        if(mysql_num_rows($result)) {
            $course_starting_date_r = mysql_fetch_assoc($result);
            $course_starting_date = $course_starting_date_r['course_starting_date'];
        }
        // get assignments from DB
        $query = "SELECT * FROM ".DB_PREFIX."assignments WHERE course_guid=".$guid." ORDER BY deadline ASC";
        $result = $db->query($query);
        $assignments = array();
        if(mysql_num_rows($result)) {
            while($assignment = mysql_fetch_object($result)) {
                $assignments[] = $assignment;
            }
        }
        // get participants from DB
        $query = "SELECT * FROM ".DB_PREFIX."participants WHERE course_guid=".$guid." AND status='active' ORDER BY lastname, firstname ASC";
        $result = $db->query($query);
        if(mysql_num_rows($result)) {
            while($participant = mysql_fetch_object($result)) {
                // table contents
		        if ($participant && $assignments && is_array($assignments)) {
			        $assignments_results = getSingleParticipantProgress($assignments, $participant->blog_base, $course_starting_date, $guid);
			        $body_data[] = array('participant'=>$participant,'assignment'=>$assignments_results);
		        }               
            }
        }      
		return serialize($body_data);
    }

    function getSingleParticipantProgress($assignments, $posts_url, $course_starting_date, $guid) {
	    $returned = array();
		$i = 0;
		$prev_a_start = 0;
		foreach ($assignments as $key => $assignment) {
		  	// States: 0 - no link or blog post, 1 - blog post in certain time frame, 2 - link to assignment
			$returned[$key] = array('state' => 0);
			$timeframe_empty = true;
			$timeframe_result = array('state' => 1);
			$frame_start_ts = $prev_a_start;
			if ($i == 0) {
				$frame_start_ts = $course_starting_date;
			}
			$prev_a_start = (int) $assignment->deadline + 86400;
			// Add one day, so that people would have time till the end of the day
			$frame_end_ts = (int) $assignment->deadline + 86399;
			$i++;
			// get posts from DB
			global $db;
            $query = "SELECT id, p.link as link, title  FROM ".DB_PREFIX."posts p LEFT JOIN ".DB_PREFIX."course_rels_posts r ON p.link=r.link WHERE course_guid=".$guid." AND content LIKE '%".mysql_real_escape_string($assignment->blog_post_url)."%' AND p.link LIKE '".mysql_real_escape_string($posts_url)."%' AND !hidden ORDER BY date DESC";
            $result = $db->query($query);
            if( mysql_num_rows($result) ) {
                $item = mysql_fetch_object($result);
	        $returned[$key] = array('state' => 2, 'link' => $item->link, 'title' => $item->title, 'id' => $item->id);
            } else {
                $query = "SELECT id, p.link as link, title FROM ".DB_PREFIX."posts p LEFT JOIN ".DB_PREFIX."course_rels_posts r ON p.link=r.link WHERE course_guid=".$guid." AND p.link LIKE '".mysql_real_escape_string($posts_url)."%' AND date>".$frame_start_ts." AND date<=".$frame_end_ts." AND !r.hidden ORDER BY date DESC";
                $result = $db->query($query);
                if( mysql_num_rows($result) ) {
                    $item = mysql_fetch_object($result);
	            $returned[$key] = array('state' => 1, 'link' => $item->link, 'title' => $item->title, 'id' => $item->id);
                } else {
                    $returned[$key] = array('state' => 0);    
                }
            }
		}
		return $returned;
	}
	
	function getCourseLinkingConnections($educourse_guid) {
	    return serialize(getCourseLinkingConnectionsData($educourse_guid));
	}

	function getCourseLinkingConnectionsData($educourse_guid) {
		$educourse_connections = array();
		$connected_participants = array();
		$all_participants = array();
	    global $db;
	    $participants = $db->getCourseParticipants($educourse_guid);
		foreach ($participants as $key => $participant) {
			$all_participants[] = $participant->firstname . ' ' . $participant->lastname;
	        $query = "SELECT * FROM ".DB_PREFIX."posts pos LEFT JOIN ".DB_PREFIX."participants par ON link LIKE CONCAT(blog_base,'%') LEFT JOIN ".DB_PREFIX."course_rels_posts r ON pos.link=r.link WHERE pos.content LIKE '%".mysql_real_escape_string($participant->blog_base)."%' AND par.participant_guid!=".$participant->participant_guid." AND par.course_guid=".$educourse_guid." AND r.course_guid=".$educourse_guid." AND !hidden";
            $result = $db->query($query);
            while($item = mysql_fetch_object($result)) {
		        if (!in_array($participant->firstname . ' ' . $participant->lastname, $connected_participants))
					$connected_participants[] = $participant->firstname . ' ' . $participant->lastname;
				if (!in_array($item->firstname . ' ' . $item->lastname, $connected_participants))
					$connected_participants[] = $item->firstname . ' ' . $item->lastname;
			    $educourse_connections[] = array('person' => $item->firstname . ' ' . $item->lastname , 'links' => $participant->firstname . ' ' . $participant->lastname, 'size' => 1);
		    }
		    $query = "SELECT * FROM ".DB_PREFIX."comments pos LEFT JOIN ".DB_PREFIX."participants par ON link LIKE CONCAT(blog_base,'%') LEFT JOIN ".DB_PREFIX."course_rels_comments r ON pos.link=r.link WHERE pos.content LIKE '%".mysql_real_escape_string($participant->blog_base)."%' AND par.participant_guid!=".$participant->participant_guid." AND par.course_guid=".$educourse_guid." AND r.course_guid=".$educourse_guid." AND !hidden";
            $result = $db->query($query);
            while($item = mysql_fetch_object($result)) {
		        if (!in_array($participant->firstname . ' ' . $participant->lastname, $connected_participants))
					$connected_participants[] = $participant->firstname . ' ' . $participant->lastname;
				if (!in_array($item->firstname . ' ' . $item->lastname, $connected_participants))
					$connected_participants[] = $item->firstname . ' ' . $item->lastname;
				$educourse_connections[] = array('person' => $participant->firstname . ' ' . $participant->lastname , 'links' => $item->firstname . ' ' . $item->lastname, 'size' => 1);
		    }
		}
	    
	    // Add participants that have no connections to file as standalone
		$unconnected_participants = array_diff($all_participants, $connected_participants);
		if (is_array($unconnected_participants) && sizeof($unconnected_participants) > 0) {
			foreach ($unconnected_participants as $up) {
				$educourse_connections[] = array('person' => $up, 'links' => $up, 'size' => 1);
				
			}
		}
		return $educourse_connections;
	}
	
	function getCourseLinkingConnectionsStatistics($educourse_guid) {
	    $data = array();
	    $elems = array();
	    foreach (getCourseLinkingConnectionsData($educourse_guid) as $d) {
	       if (!array_key_exists($d['person']."->".$d['links'], $data) && !array_key_exists($d['links']."->".$d['person'], $data) && $d['links']!=$d['person']) {
	           $data[$d['person']."->".$d['links']] = "{'person':'".$d['person']."','links':'".$d['links']."'}";
	       }
	       if (!array_key_exists($d['person'], $elems)) {
	           $elems[$d['person']] = $d['size'];
	       } else {
	           $elems[$d['person']]++;
	       }
	    }
	    $e = array();
	    $max = 0;
	    foreach($elems as $s) {
            if ($s > $max) {
                $max = $s;
             }
        }
	    foreach ($elems as $key => $val) {
	        $size = round((log($val) / log($max + .0001)) * 100) + 30;
            if ($size < 60) {
                $size = 60;
            }
            if ($size > 180) {
                $size = 180;
            }
	        $e []= "{'person':'".$key."','size':'".$size."'}";
	    }
	    $statistics = "[[ ".join($e, ",")." ], [ ".join($data, ",")." ]];";
	    return $statistics;
	}

?>
