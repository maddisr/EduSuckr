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
            $query = "(SELECT posts, comments, course_guid FROM ".DB_PREFIX."educourses WHERE !deleted) union all (SELECT posts, comments, course_guid FROM ".DB_PREFIX."participants)";
            $result = $db->query($query);
            $posts_count = 0;
            $comments_count = 0;
            if(mysql_num_rows($result)) {
                while($feed = mysql_fetch_assoc($result)) {
                    $posts_count = $posts_count + $this->suckFeed($feed['posts'], $feed['course_guid'], "post");
                    $comments_count = $comments_count + $this->suckFeed($feed['comments'], $feed['course_guid'], "comment");
                }
            }
            $statistics->completeSuck($post_stat_id, $posts_count);
            $statistics->completeSuck($comment_stat_id, $comments_count);
        }
        
        function suckFeed($feed_url, $course, $type="post") {
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
			    $title = mysql_real_escape_string($item->get_title());
			    $link = mysql_real_escape_string($item->get_permalink());
			    $date = mysql_real_escape_string($item->get_date('U'));
			    $content = mysql_real_escape_string($item->get_content());
			    $base = mysql_real_escape_string($item->get_base());
	            $author = $item->get_author();
	            $author_name = $author->get_name();
	            if (!$author_name) $author_name = 0;
	            preg_match('@^(?:http://www.blogger.com/profile/)?([^/]+)@i', $author->get_link(), $matches);
	            $blogger_id = 0;
	            if (count($matches)>1 && is_numeric($matches[1])) {
	                $blogger_id = $matches[1];
	            }
                if ($title && $link && $date && $content) {
                    if ($type=="post") {
                        $post_written = $this->writePost($title, $link, $base, $date, $content, $author_name, $blogger_id);
                        $post_rel_written = $this->writePostRelation($course, $link);
                        if ($post_written && $post_rel_written) $success++;
                    } else {
                        $comment_written = $this->writeComment($title, $link, $base, $date, $content, $author_name, $blogger_id);
                        $comment_rel_written = $this->writeCommentRelation($course, $link);
                        if ($comment_written && $comment_rel_written) $success++;
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
            $query = "INSERT into ".DB_PREFIX."posts (link, base, title, date, content, author, blogger_id) values ('".$link."', '".$base."', '".$title."', '".$date."', '".$content."', '".$author_name."', '".$blogger_id."') ON DUPLICATE KEY UPDATE title='".$title."', date='".$date."', content='".$content."', author='".$author_name."', blogger_id='".$blogger_id."', modified=NOW()";
            return $db->query($query);  
        }
        
        function writeComment($title, $link, $base, $date, $content, $author_name, $blogger_id) {
            global $db;
            $query = "INSERT into ".DB_PREFIX."comments (link, base, title, date, content, author, blogger_id, post_id) values ('".$link."', '".$base."', '".$title."', '".$date."', '".$content."', '".$author_name."', '".$blogger_id."',(SELECT id FROM ".DB_PREFIX."posts WHERE '".$link."' LIKE CONCAT(link,'%'))) ON DUPLICATE KEY UPDATE title='".$title."', date='".$date."', content='".$content."', author='".$author_name."', blogger_id='".$blogger_id."', post_id=(SELECT id FROM ".DB_PREFIX."posts WHERE '".$link."' LIKE CONCAT(link,'%')), modified=NOW()";
            return $db->query($query);  
        }
    
    }
?>