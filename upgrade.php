<?php
    exit; // This is only usable in case of migrations, notmally is closed
    require_once("includes/config.php");
    require_once("includes/db.php");

    function suckr_upgrade_hidden_to_rels() {
		global $db;
		$message = "";
		$courses_count = 0;
		$success_posts_count = 0;
		$success_comments_count = 0;
		$posts_count = 0;
		$comments_count = 0;

	    $query = "SELECT * FROM ".DB_PREFIX."educourses";

		$result = $db->query($query);
		while($course = mysql_fetch_assoc($result)) {
			$courses_count++;
			$message .= 'Course: ' . $course['title'] . '<br />';
			// Get gidden posts, old style
			$query = "SELECT DISTINCT id, c.link as link, title FROM ".DB_PREFIX."posts c LEFT JOIN ".DB_PREFIX."course_rels_posts r ON c.link=r.link WHERE r.course_guid=".$course['course_guid']." AND c.hidden";
			$result = $db->query($query);
			$posts = array();
			if ($result) {
				while($res = mysql_fetch_assoc($result)) {
					$posts[] = $res;
				}
			}
			foreach ($posts as $post) {
			    $message .= 'Hidden post: ' . $post['title'] . ' ';
				$posts_count++;
				$post_hid = $db->hidePostById($post['id'], $course['course_guid']);
				if ($post_hid) {
				    $message .= '<span style="color:green;">SUCCESS</span><br />';
					$success_posts_count++;
				} else {
					$message .= '<span style="color:red;">FAILED</span><br />';
				}
			}
			// Get hidden comments, old style
			$query = "SELECT DISTINCT id, c.link as link, title FROM ".DB_PREFIX."comments c LEFT JOIN ".DB_PREFIX."course_rels_comments r ON c.link=r.link WHERE r.course_guid=".$course['course_guid']." AND c.hidden";
			$result = $db->query($query);
			$comments = array();
			if ($result) {
				while($res = mysql_fetch_assoc($result)) {
					$comments[] = $res;
				}
			}
			foreach ($comments as $comment) {
				$message .= 'Hidden comment: ' . $comment['title'] . ' ';
				$comments_count++;
				$comment_hid = $db->hideCommentById($comment['id'], $course['course_guid']);
				if ($comment_hid) {
					$message .= '<span style="color:green;">SUCCESS</span><br />';
					$success_comments_count++;
				} else {
					$message .= '<span style="color:red;">FAILED</span><br />';
				}
			}
		}
		$message .= '<br /><br />All courses: ' . $courses_count . '<br />';
		$message .= 'Hidden posts success: ' . $success_posts_count . ' out of ' .$posts_count . '<br />';
		$message .= 'Hidden comments success: ' . $success_comments_count . ' out of '. $comments_count . '<br />';

		return $message;
	}

	// Run migraton
	//echo suckr_upgrade_hidden_to_rels();
?>
