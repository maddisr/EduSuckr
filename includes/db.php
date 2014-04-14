<?php
    
    class ESDB {
        
        function __construct() {
            $this->connect();
        }
        
        function connect() {
            $this->link = mysql_connect(DB_HOST,DB_USERNAME,DB_PASSWORD, true);
            mysql_set_charset("UTF8");
            
            if (!$this->link) {
                die('Could not connect: ' . mysql_error());
            }
            mysql_select_db(DB_NAME, $this->link);
        }

        function disconnect() {
            mysql_close();
        }

        function query($query) {
            $ret = mysql_query($query, $this->link) or print(mysql_error());
            return $ret;
        }
        
        function insert($table, $data, $dontupdate=array()) {
            foreach ($data as $e => $v) {
                if (!is_numeric($v)) $data[$e]= "'".mysql_real_escape_string($v)."'";
            }
            $columns = array_keys($data);
            $values = array_values($data);
            $escaped_values = array();
            $up = array();
            foreach ($data as $col => $val) {
                if ($dontupdate && in_array($col, $dontupdate)) continue;
                $up []= $col."=".$val;
            }
            $query = "INSERT into ".$table." (".implode(", ", $columns).") values (".implode(", ", $values).") ON DUPLICATE KEY UPDATE ".implode(", ", $up);
            return $this->query($query);
        }
        
        function setEduCourse($param) {
            if ($this->insert(DB_PREFIX."educourses", $param, array('course_guid'))) {
                return 1;
            }
            return 0;
        }
        
        function removeEduCourse($id) {            
            if ($id) {
                $query = "DELETE from ".DB_PREFIX."educourses WHERE course_guid=".$id;
                return $this->query($query);
            } 
            return 0;
        }

        function listEduCources() {
            $query = "SELECT course_guid FROM ".DB_PREFIX."educourses WHERE !deleted";
            $result = $this->query($query);
            $feeds = array();
            if(mysql_num_rows($result)) {
                while($feed = mysql_fetch_assoc($result)) {
                    $feeds[] = $feed['course_guid'];
                }
            }
            return $feeds;
        }

        function addParticipant($param) {
            if ($param['participant_guid']) {
                return $this->insert(DB_PREFIX."participants", $param, array('participant_guid', 'course_guid'));
            } 
            return 0;
        }
        
        function removeParticipant($id) {            
            if ($id) {
                $query = "DELETE from ".DB_PREFIX."participants WHERE participant_guid=".$id;
                return $this->query($query);
            } 
            return 0;
        }
        
        function setAssignment($param) {
            $title = mysql_real_escape_string($param['title']);
            $description = mysql_real_escape_string($param['description']);
            $query = "INSERT into ".DB_PREFIX."assignments (assignment_id, course_guid, title, description, blog_post_url, deadline) values (".$param['assignment_id'].",".$param['course_guid'].",'".$title."','".$description."','".$param['blog_post_url']."','".$param['deadline']."') ON DUPLICATE KEY UPDATE title='".$title."', description='".$description."', blog_post_url='".$param['blog_post_url']."', deadline='".$param['deadline']."'";
            $result = $this->query($query);
            if ($result) {
                return 1;
            }
            return 0;
        }
        
        function removeAssignment($id) {    
            if ($id) {
                $query = "DELETE from ".DB_PREFIX."assignments WHERE assignment_id=".$id;
                return $this->query($query);
            } 
            return 0;
        }
        
        function getCourseParticipants($course_guid) {
            $query = "SELECT * FROM ".DB_PREFIX."participants WHERE course_guid=".$course_guid." AND (status='active' OR status='teacher') ORDER BY lastname, firstname ASC";
            $result = $this->query($query);
            $participants = array();
            if(mysql_num_rows($result)) {
                while($participant = mysql_fetch_object($result)) {
                    $participants []= $participant;
                }
            }
            return $participants;
        }
        
        function getCoursePosts($param) {
            return $this->getCourseData($param, "post");
        }
        
        function getCourseComments($param) {
            return $this->getCourseData($param, "comment");
        }

        function getCourseData($param, $type="post") {
            $limit = 5;
            if ($param[1]) $limit = $param[1];
            $post_id = "";
            if ($type=="comment") $post_id = " ,post_id, post_author";
            $query = "SELECT DISTINCT id, c.link as link, title, SUBSTRING(content,1,250) as content, date, author, blogger_id, base".$post_id." FROM ".DB_PREFIX.$type."s c LEFT JOIN ".DB_PREFIX."course_rels_".$type."s r ON c.link=r.link WHERE r.course_guid=".$param[0]." AND !r.hidden ORDER BY date DESC LIMIT ".$limit;
            $result = $this->query($query);
            $comments = array();
            if(mysql_num_rows($result)) {
                while($comment = mysql_fetch_array($result)) {
                    if ($comment['blogger_id']) {
                        if ($fn = $this->getFullnameByBloggerId($comment['blogger_id'])) {
                            $comment['author'] = $fn;
                        }
                    }
                    $comments []= $comment;
                }
            }
            return serialize($comments);
        }

		function getParticipantPosts($param) {
			// Check in case base does not have slash in the end
			$backup_base = preg_replace('{/$}', '', $param[1]);
			$query = "SELECT DISTINCT id, c.link AS link, title, content, date, author, blogger_id, base FROM ".DB_PREFIX."posts c LEFT JOIN ".DB_PREFIX."course_rels_posts r ON c.link=r.link WHERE r.course_guid={$param[0]} AND !r.hidden AND (c.base='{$param[1]}' OR c.base='{$backup_base}') ORDER BY date DESC";
			$result = $this->query($query);
            $comments = array();
            if($result && mysql_num_rows($result)) {
                while($comment = mysql_fetch_array($result)) {
                    if ($comment['blogger_id']) {
                        if ($fn = $this->getFullnameByBloggerId($comment['blogger_id'])) {
                            $comment['author'] = $fn;
                        }
                    }
                    $comments []= $comment;
                }
            }
            return serialize($comments);
		}
		
		function getParticipantComments($param) {
			// Check in case base does not have slash in the end
			$backup_base = preg_replace('{/$}', '', $param[1]);
			$query = "SELECT DISTINCT c.link AS link, base, title, date, content, author, blogger_id, post_id, post_author, id FROM ".DB_PREFIX."comments c LEFT JOIN ".DB_PREFIX."course_rels_comments r ON c.link=r.link WHERE r.course_guid={$param[0]} AND !r.hidden ORDER BY date DESC";
			$result = $this->query($query);
            $comments = array();
            if($result && mysql_num_rows($result)) {
                while($comment = mysql_fetch_array($result)) {
                    if ($comment['blogger_id']) {
                        if ($fn = $this->getFullnameByBloggerId($comment['blogger_id'])) {
                            $comment['author'] = $fn;
                        }
                    }
                    $comments []= $comment;
                }
            }
            return serialize($comments);
		}

	   /**
         * Determines if a post is hidden within a course context.
         *
         * @param int    $course_guid Course unique identifier
         * @param string $post_link   Post address
         *
         * @return int Either 0 or 1; defaults to 0
         */
        function getIsCoursePostHidden($course_guid, $post_link) {
            $query = "SELECT hidden FROM ".DB_PREFIX."course_rels_posts WHERE course_guid={$course_guid} AND link = '{$post_link}'";
            $result = $this->query($query);
            if ($result) {
                $data = mysql_fetch_object($result);
                return $data->hidden;
            }
            return 0;
        }
        
        function getCoursePostById($id, $course_guid) {
            $post = $this->getPostById($id);
            if ($post) {
                // Append hidden or not information
                $post['hidden'] = $this->getIsCoursePostHidden($course_guid, $post['link']);
				$post['assignment_id'] = $this->getCoursePostAssignmentID($course_guid, $post['link']);
                $comments = $this->getCommentsByPost($id, $course_guid);
                return serialize(array('post'=>$post,'comments'=>$comments));
            }
            return 0;
        }
        
        function getPostById($id) {
            if ($id && is_numeric($id)) {
                $query = "SELECT id, link, title, content, date, author, blogger_id, base FROM ".DB_PREFIX."posts WHERE id=".$id;
                $result = $this->query($query);
                $post = mysql_fetch_array($result);
                if ($post['blogger_id']) {
                    if ($fn = $this->getFullnameByBloggerId($post['blogger_id'])) {
                        $post['author'] = $fn;
                    }
                }
                return $post;
            }
            return 0;
		}

		function getCommentById($id) {
			if ($id && is_numeric($id)) {
				$query = "SELECT id, link, title, content, date, author, blogger_id, base, post_id, post_author FROM ".DB_PREFIX."comments WHERE id=".$id;
				$result = $this->query($query);
				$comment = mysql_fetch_array($result);
				if ($comment['blogger_id']) {
					if ($fn = $this->getFullnameByBloggerId($comment['blogger_id'])) {
						$comment['author'] = $fn;
					}
				}
				return $comment;
			}
			return 0;
		}

        function getCommentsByPost($post, $course_guid) {
            $comments = array();
            if ($post && $course_guid) {
                $query = "SELECT DISTINCT id, c.link as link, title, content, date, author, blogger_id, base, post_id, post_author, r.participant_id AS participant_id FROM ".DB_PREFIX."comments c LEFT JOIN ".DB_PREFIX."course_rels_comments r ON c.link=r.link WHERE c.post_id=".$post." AND !r.hidden AND r.course_guid=".$course_guid." ORDER BY date ASC";
                $result = $this->query($query);
                while($comment = mysql_fetch_array($result)) {
                    if ($comment['blogger_id']) {
                        if ($fn = $this->getFullnameByBloggerId($comment['blogger_id'])) {
                            $comment['author'] = $fn;
                        }
                    }
                    $comments []= $comment;
                }
            }
            return $comments;
        }
        
        function getFullnameByBloggerId($id) {
            $query = "SELECT CONCAT(firstname, ' ', lastname) as fullname FROM ".DB_PREFIX."participants WHERE blogger_id='".$id."'";
            $result = $this->query($query);
            while($res = mysql_fetch_array($result)) {
                return $res['fullname'];
            }
            return 0;
        }
        
        function getFullnameByLink($link) {
            $query = "SELECT CONCAT(firstname, ' ', lastname) as fullname FROM ".DB_PREFIX."participants WHERE blog LIKE '".$link."%'";
            $result = $this->query($query);
            while($res = mysql_fetch_array($result)) {
                return $res['fullname'];
            }
            return 0;
		}

        /**
         * Searches for post using comment URL.
         * Returns post URL if possible.
         *
         * @param string $link Comment URL
         *
         * @return mixed
         */
        function getPostLinkByCommentLink($link) {
            $query = "SELECT link FROM ".DB_PREFIX."posts WHERE '".$link."' LIKE CONCAT(link,'%')";
            $result = $this->query($query);
            if ($result) {
                if ($data = mysql_fetch_object($result)) {
                    if ($data) {
                        return $data->link;
                    }
                }
            }
            return 0;
        }

		function hidePostById($param) {
			$id = (int) $param[0];
			$course_guid = (int) $param[1];
			if ($id && $course_guid && is_numeric($id) && is_numeric($course_guid)) {
				$post = $this->getPostById($id);
				if ($post) {
					$query = "UPDATE ".DB_PREFIX."course_rels_posts SET hidden=1 WHERE course_guid=$course_guid AND link='".$post['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
        
		function hideCommentById($param) {
			$id = (int)$param[0];
			$course_guid = (int)$param[1];
			if ($id && $course_guid && is_numeric($id) && is_numeric($course_guid)) {
				$comment = $this->getCommentById($id);
				if ($comment) {
					$query = "UPDATE ".DB_PREFIX."course_rels_comments SET hidden=1 WHERE course_guid=$course_guid AND link='".$comment['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
        
		function unhidePostById($param) {
			$id = (int)$param[0];
			$course_guid = (int)$param[1];
			if ($id && $course_guid && is_numeric($id) && is_numeric($course_guid)) {
				$post = $this->getPostById($id);
				if ($post) {
					$query = "UPDATE ".DB_PREFIX."course_rels_posts SET hidden=0 WHERE course_guid=$course_guid AND link='".$post['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
        
		function unhideCommentById($param) {
			$id = (int)$param[0];
			$course_guid = (int)$param[1];
			if ($id && $course_guid && is_numeric($id) && is_numeric($course_guid)) {
				$comment = $this->getCommentById($id);
				if ($comment) {
					$query = "UPDATE ".DB_PREFIX."course_rels_comments SET hidden=0 WHERE course_guid=$course_guid AND link='".$comment['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}

        function getHiddenByCourse($course_guid, $type="post") {
            if ($course_guid && is_numeric($course_guid) && in_array($type, array("post", "comment"))) {
                $query = "SELECT DISTINCT id, c.link as link, title, date, author FROM ".DB_PREFIX.$type."s c LEFT JOIN ".DB_PREFIX."course_rels_".$type."s r ON c.link=r.link WHERE r.course_guid=".$course_guid." AND r.hidden ORDER BY date DESC";
                $result = $this->query($query);
                $ress = array();
                while($res = mysql_fetch_array($result)) {
                    $ress []= $res;
                }
                return serialize($ress);
            }
            return 0;
        }
		
		/**
         * Determines if a post is hidden within a course context.
         *
         * @param int    $course_guid Course unique identifier
         * @param string $post_link   Post address
         *
         * @return int Either 0 or 1; defaults to 0
         */
        function getCoursePostAssignmentID($course_guid, $post_link) {
            $query = "SELECT assignment_id FROM ".DB_PREFIX."course_rels_posts WHERE course_guid={$course_guid} AND link = '{$post_link}'";
            $result = $this->query($query);
            if ($result) {
                $data = mysql_fetch_object($result);
                return (int) $data->assignment_id;
            }
            return 0;
        }
		
		function connectPostWithAssignment($course_guid, $post_id, $assignment_id) {
		    $course_guid = (int) $course_guid;
			$post_id = (int) $post_id;
			$assignment_id = (int) $assignment_id;
			if ($post_id && $course_guid && $assignment_id && is_numeric($post_id) && is_numeric($course_guid) && is_numeric($assignment_id)) {
				
				$post = $this->getPostById($post_id);
				// TODO check if assignment exists
				if ($post) {
					$query = "UPDATE ".DB_PREFIX."course_rels_posts SET assignment_id=$assignment_id WHERE course_guid=$course_guid AND link='".$post['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
		function disconnectPostWithAssignment($course_guid, $post_id) {
		    $course_guid = (int) $course_guid;
			$post_id = (int) $post_id;
			if ($post_id && $course_guid && is_numeric($post_id) && is_numeric($course_guid)) {
				
				$post = $this->getPostById($post_id);
				if ($post) {
					$query = "UPDATE ".DB_PREFIX."course_rels_posts SET assignment_id=NULL WHERE course_guid=$course_guid AND link='".$post['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
	
		function connectCommentWithParticipant($course_guid, $id, $participant_id) {
			$course_guid = (int) $course_guid;
			$id = (int) $id;
			$participant_id = (int) $participant_id;
			
			
			if ($course_guid && $id && $participant_id && is_numeric($course_guid) && is_numeric($id) && is_numeric($participant_id)) {
				
				$comment = $this->getCommentById($id);
				if ($comment) {
					$query = "UPDATE ".DB_PREFIX."course_rels_comments SET participant_id=$participant_id WHERE course_guid=$course_guid AND link='".$comment['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
	
		function disconnectCommentWithParticipant($course_guid, $id) {
			$course_guid = (int) $course_guid;
			$id = (int) $id;
			
			
			if ($course_guid && $id && is_numeric($course_guid) && is_numeric($id)) {
				
				$comment = $this->getCommentById($id);
				if ($comment) {
					$query = "UPDATE ".DB_PREFIX."course_rels_comments SET participant_id=NULL WHERE course_guid=$course_guid AND link='".$comment['link']."'";
					return $this->query($query);
				}
			}
			return 0;
		}
	
	}
        
        
    
   
   $db = new ESDB;
   
?>
