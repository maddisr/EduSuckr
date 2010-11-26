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

	        $select_courses = "SELECT * FROM ".DB_PREFIX."educourses";

		$courses_result = $db->query($select_courses);
		while($course = mysql_fetch_assoc($courses_result)) {
			$courses_count++;
			//$message .= 'Course: ' . $course['title'] . '<br />';
			// Get gidden posts, old style
			$select_posts = "SELECT id, c.link as link, title FROM ".DB_PREFIX."posts c LEFT JOIN ".DB_PREFIX."course_rels_posts r ON c.link=r.link WHERE r.course_guid=".$course['course_guid']." AND c.hidden";
			$posts_result = $db->query($select_posts);
			$posts = array();
			if ($posts_result) {
				while($pst = mysql_fetch_assoc($posts_result)) {
					$posts[] = $pst;
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
                                    $message .= '<span style="color:red;">FAILED </span>';
                                    $message .= '<span style="color:yellow;">ALTERNATE TRY: </span>';
                                    $alternate_post_update = 'UPDATE '.DB_PREFIX.'course_rels_posts SET hidden=1 WHERE course_guid='.$course['course_guid'].' AND link="'.$post['link'].'";';
                                    $post_updated = $db->query($alternate_post_update);
                                    if ($post_updated) {
                                        $message .= '<span style="color:green;">SUCCEED</span><br />';
                                    } else {
                                        $message .= '<span style="color:red;">FAILED [ '.$alternate_post_update.' ]</span><br />';
                                    }
				}
			}
			// Get hidden comments, old style
			$query = "SELECT id, c.link as link, title FROM ".DB_PREFIX."comments c LEFT JOIN ".DB_PREFIX."course_rels_comments r ON c.link=r.link WHERE r.course_guid=".$course['course_guid']." AND c.hidden";
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
                                    $message .= '<span style="color:red;">FAILED </span>';
                                    $message .= '<span style="color:yellow;">ALTERNATE TRY: </span>';
                                    $alternate_comment_update = 'UPDATE '.DB_PREFIX.'course_rels_comments SET hidden=1 WHERE course_guid='.$course['course_guid'].' AND link="'.$comment['link'].'";';
                                    $comment_updated = $db->query($alternate_comment_update);
                                    if ($comment_updated) {
                                        $message .= '<span style="color:green;">SUCCEED</span><br />';
                                    } else {
                                        $message .= '<span style="color:red;">FAILED [ '.$alternate_comment_update.' ]</span><br />';
                                    }
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
