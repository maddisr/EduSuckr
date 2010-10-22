<?php
    /* Require configuration */
    require_once("config.php");
    /* Require libraries */
    require_once("SimplePie/simplepie.inc");
    /* Require database */
    require_once("db.php");
    /* Require statistic functions */
    require_once("statistics.php");
    class Suckr {
        
        function suckBlogs() {
            $statistics = new Statistics;
            $post_stat_id = $statistics->performSuck("post");
            $comment_stat_id = $statistics->performSuck("comment");
            global $db;
            $now = time();
            $query = "(SELECT posts, comments, course_guid, 'Nameless' as fullname, course_blog as blog FROM ".DB_PREFIX."educourses WHERE !deleted AND start_agregate<=".$now." AND stop_agregate>=".$now.") union all (SELECT p.posts as posts, p.comments as comments, p.course_guid as course_guid, CONCAT(p.firstname, ' ', p.lastname) as fullname, blog FROM ".DB_PREFIX."participants p LEFT JOIN ".DB_PREFIX."educourses c ON p.course_guid=c.course_guid WHERE c.start_agregate<=".$now." AND c.stop_agregate>=".$now.")";
            $result = $db->query($query);
            $posts_count = 0;
            $comments_count = 0;
            if(mysql_num_rows($result)) {
                while($feed = mysql_fetch_assoc($result)) {
                    if (!SILENT_MODE) {
                        echo "Course: ".$feed['blog']." (".$feed['course_guid'].") by ".$feed['fullname']."<br />";
                    }
                    $posts_count = $posts_count + $this->suckFeed($feed['posts'], $feed['course_guid'], $feed['fullname'], "post");
                    $comments_count = $comments_count + $this->suckFeed($feed['comments'], $feed['course_guid'], $feed['fullname'], "comment");
                }
            }
            $statistics->completeSuck($post_stat_id, $posts_count);
            $statistics->completeSuck($comment_stat_id, $comments_count);
        }
        
        function suckFeed($feed_url, $course, $author, $type="post") {
            $feed = new SimplePie();
		    $feed->set_feed_url($feed_url);
			$feed->enable_cache(false);
		    $feed->set_output_encoding('UTF-8');
		    $feed->init();
		    $feed->handle_content_type();
		    $success = 0;
		    		
		    //XXX We do not handleerrors here, as invalid XML is processed fine, but might raise errors
		    //TODO Think about preprocessing Feed before giving it to SimplePie parser
		    //if ($feed->error()) error_log('Problems with RSS feed: ' . $feed->error());
		    $items = $feed->get_items(0, 0);
		   
			foreach ($items as $item) {
			    $title = $item->get_title();
			    $link = $item->get_permalink();
			    $date = $item->get_date('U');
			    $content = $item->get_content();
			    $base = $item->get_base();
			    $author_name = $author;
	            $f_author = $item->get_author();
	            $blogger_id = 0;
	            $f_author_name = 0;
	            if (is_object($f_author)) {
	                $f_author_name = $f_author->get_name();
	                if (!$f_author_name) $f_author_name = 0;
	                preg_match('@^(?:http://www.blogger.com/profile/)?([^/]+)@i', $f_author->get_link(), $matches);	                
	                if (count($matches)>1 && is_numeric($matches[1])) {
	                    $blogger_id = $matches[1];
	                }
	            }
                if ($title && $link && $date && $content) {
                    if ($type=="post") {
                        $post_written = $this->writePost($title, $link, $base, $date, $content, $author_name, $blogger_id);
                        $post_rel_written = $this->writePostRelation($course, $link);
                        if ($post_written && $post_rel_written) {
                             $success++;
                             if (!SILENT_MODE) {
                                 echo "Related post: ".$link." was added or updated in database<br />";
                             }
                        }
                    } else {
                        $ok = "ok";
                        $desc_start = substr($item->get_description(), 0, 5);
                        if (!strcmp($desc_start, "[…]")) {
                            $ok = "not";
                        }
                        $comment_written = $this->writeComment($title, $link, $base, $date, $content, $f_author_name, $blogger_id);
                        $comment_rel_written = $this->writeCommentRelation($course, $link);
                        if ($comment_written && $comment_rel_written) {
                            $success++;
                            if (!SILENT_MODE) {
                                echo "Related comment: ".$link." was added or updated in database - Status ".$ok." and ".$desc_start."<br />";
                            }
                        }
                    }
                }
			}
		    // destroy feed
		    $feed->__destruct();
		    unset($feed);
		    return $success;
        }
        
        function writePostRelation($course, $link) {
            global $db;
            $query = "INSERT IGNORE into ".DB_PREFIX."course_rels_posts (course_guid, link) values (".$course.", '".$link."')";
            return $db->query($query);  
        }
        
        function writeCommentRelation($course, $link) {
            global $db;
            $query = "INSERT IGNORE into ".DB_PREFIX."course_rels_comments (course_guid, link) values (".$course.", '".$link."')";
            return $db->query($query);  
        }
        
        function writePost($title, $link, $base, $date, $content, $author_name, $blogger_id) {
            global $db;
            $data = array(
                'link' => $link,
                'base' => $base,
                'title' => $title,
                'date' => $date,
                'content' => $content,
                'author' => $author_name,
                'blogger_id' => $blogger_id,
                'modified' => 'NOW()'
            );
            return $db->insert(DB_PREFIX."posts", $data, array('link', 'base'));
        }
        
        function writeComment($title, $link, $base, $date, $content, $author_name, $blogger_id) {
            global $db;
            $pre_query = "SELECT id, author FROM ".DB_PREFIX."posts WHERE '".$link."' LIKE CONCAT(link,'%')";
            $pre_result = $db->query($pre_query);
            $pre_res = mysql_fetch_array($pre_result);
            $data = array(
                'link' => $link,
                'base' => $base,
                'title' => $title,
                'date' => $date,
                'content' => $content,
                'author' => $author_name,
                'blogger_id' => $blogger_id,
                'post_id' => $pre_res['id'],
                'post_author' => $pre_res['author'],
                'modified' => 'NOW()'
            );
            return $db->insert(DB_PREFIX."comments", $data, array('link', 'base'));
        }
    
    }
?>