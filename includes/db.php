<?php
    
    class DB {
        
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
        
        function setEduCourse($param) {
            $title = mysql_real_escape_string($param['title']);
            $description = mysql_real_escape_string($param['description']);
            $query = "INSERT into ".DB_PREFIX."educourses (course_guid, title, description, posts, comments, course_tag, course_blog, course_wiki, signup_deadline, course_starting_date, course_ending_date, start_agregate, stop_agregate) values (".$param['course_guid'].",'".$title."','".$description."','".$param['posts']."','".$param['comments']."','".$param['course_tag']."','".$param['course_blog']."','".$param['course_wiki']."','".$param['signup_deadline']."','".$param['course_starting_date']."','".$param['course_ending_date']."','".$param['start_agregate']."','".$param['stop_agregate']."') ON DUPLICATE KEY UPDATE title='".$title."', description='".$description."', posts='".$param['posts']."', comments='".$param['comments']."', course_tag='".$param['course_tag']."', course_blog='".$param['course_blog']."', course_wiki='".$param['course_wiki']."', signup_deadline='".$param['signup_deadline']."', course_starting_date='".$param['course_starting_date']."', course_ending_date='".$param['course_ending_date']."',start_agregate='".$param['start_agregate']."',stop_agregate='".$param['stop_agregate']."'";
            $result = $this->query($query);
            if ($result) {
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
            if ($param['participant_id']) {
                $firstname = mysql_real_escape_string($param['firstname']);
                $lastname = mysql_real_escape_string($param['lastname']);
                $query = "INSERT into ".DB_PREFIX."participants (participant_guid, course_guid, firstname, lastname, email, blog, posts, comments, blogger_id, status) values (".$param['participant_id'].",".$param['course_guid'].",'".$firstname."','".$lastname."','".$param['email']."','".$param['blog']."','".$param['posts']."','".$param['comments']."','".$param['blogger_id']."','".$param['status']."') ON DUPLICATE KEY UPDATE firstname='".$firstname."', lastname='".$lastname."', email='".$param['email']."', blog='".$param['blog']."', posts='".$param['posts']."', comments='".$param['comments']."', blogger_id='".$param['blogger_id']."', status='".$param['status']."'";
                return $this->query($query);
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
            $query = "SELECT DISTINCT id, c.link as link, title, SUBSTRING(content,1,250) as content, date, author, blogger_id, base".$post_id." FROM ".DB_PREFIX.$type."s c LEFT JOIN ".DB_PREFIX."course_rels_".$type."s r ON c.link=r.link WHERE r.course_guid=".$param[0]." AND !c.hidden ORDER BY date DESC LIMIT ".$limit;
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
        
        function getCoursePostById($id) {
            $post = $this->getPostById($id);
            if ($post) {
                $comments = $this->getCommentsByPost($id);
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
        
        function getCommentsByPost($post) {
            $comments = array();
            if ($post) {
                $query = "SELECT id, link, title, content, date, author, blogger_id, base, post_id, post_author FROM ".DB_PREFIX."comments WHERE post_id=".$post." ORDER BY date ASC";
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
        
        function hidePostById($id) {
            if ($id && is_numeric($id)) {
                $query = "UPDATE ".DB_PREFIX."posts SET hidden=1 WHERE id=".$id;
                return $this->query($query);
            }
            return 0;
        }
        
        function hideCommentById($id) {
            if ($id && is_numeric($id)) {
                $query = "UPDATE ".DB_PREFIX."comments SET hidden=1 WHERE id=".$id;
                return $this->query($query);
            }
            return 0;
        }
        
        function unhidePostById($id) {
            if ($id && is_numeric($id)) {
                $query = "UPDATE ".DB_PREFIX."posts SET hidden=0 WHERE id=".$id;
                return $this->query($query);
            }
            return 0;
        }
        
        function unhideCommentById($id) {
            if ($id && is_numeric($id)) {
                $query = "UPDATE ".DB_PREFIX."comments SET hidden=0 WHERE id=".$id;
                return $this->query($query);
            }
            return 0;
        }
        
        function getHiddenByCourse($course_guid, $type="post") {
            if ($course_guid && is_numeric($course_guid) && in_array($type, array("post", "comment"))) {
                $query = "SELECT DISTINCT id, c.link as link, title, date, author FROM ".DB_PREFIX.$type."s c LEFT JOIN ".DB_PREFIX."course_rels_".$type."s r ON c.link=r.link WHERE r.course_guid=".$course_guid." AND c.hidden ORDER BY date DESC";
                $result = $this->query($query);
                $ress = array();
                while($res = mysql_fetch_array($result)) {
                    $ress []= $res;
                }
                return serialize($ress);
            }
            return 0;
        }
        
        
    }
   
   $db = new DB;
   
?>