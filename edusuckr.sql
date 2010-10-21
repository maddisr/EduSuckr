use edusuckr;

CREATE TABLE IF NOT EXISTS prefix_educourses (
    id int(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    course_guid bigint(20) DEFAULT NULL UNIQUE KEY,
    title char(255),
    description text,
    posts char(255),
    comments char(255),
    course_tag char(255),
    course_blog char(255),
    course_wiki char(255),
    signup_deadline bigint(20),
    course_starting_date bigint(20),
    course_ending_date bigint(20),
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP,
    start_agregate bigint(20),
    stop_agregate bigint(20),
    deleted boolean DEFAULT 0   
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_participants (
    participant_guid bigint(20) NOT NULL PRIMARY KEY,
    course_guid bigint(20),
    firstname char(255),
    lastname char(255),
    email char(255),
    blog char(255),
    posts char(255),
    comments char(255),
    blogger_id varchar(20) DEFAULT 0,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP,
    status enum ('active','inactive','teacher') NOT NULL DEFAULT 'active',
    FOREIGN KEY (course_guid) REFERENCES prefix_educourses (course_guid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_assignments (
    assignment_id bigint(20) NOT NULL PRIMARY KEY,
    course_guid bigint(20),
    title char(255),
    description char(255),
    blog_post_url char(255),
    deadline bigint(20),
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP,
    FOREIGN KEY (course_guid) REFERENCES prefix_educourses (course_guid) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_posts (
    link char(255) NOT NULL PRIMARY KEY,
    base char(255),
    title char(255),
    date char(255),
    content TEXT,
    author char(255),
    blogger_id char(20) DEFAULT 0,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP,
    hidden int(1) DEFAULT 0,
    id bigint(20) NOT NULL UNIQUE KEY AUTO_INCREMENT
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_comments (
    link char(255) NOT NULL PRIMARY KEY,
    base char(255),
    title char(255),
    date char(255),
    content TEXT,
    author char(255),
    blogger_id char(20) DEFAULT 0,
    created TIMESTAMP DEFAULT NOW(),
    modified TIMESTAMP,
    post_id bigint(20) NOT NULL,
    post_author char(255),
    hidden int(1) DEFAULT 0,
    id bigint(20) NOT NULL UNIQUE KEY AUTO_INCREMENT
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_course_rels_posts (
    course_guid bigint(20) NOT NULL,
    link char(255) NOT NULL,
    FOREIGN KEY (course_guid) REFERENCES prefix_educourses (course_guid) ON DELETE CASCADE,
    FOREIGN KEY (link) REFERENCES prefix_posts (link) ON DELETE CASCADE,
    PRIMARY KEY (course_guid, link)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_course_rels_comments (
    course_guid bigint(20) NOT NULL,
    link char(255) NOT NULL,
    FOREIGN KEY (course_guid) REFERENCES prefix_educourses (course_guid) ON DELETE CASCADE,
    FOREIGN KEY (link) REFERENCES prefix_comments (link) ON DELETE CASCADE,
    PRIMARY KEY (course_guid, link)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS prefix_statistics (
    id int(5) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    performed TIMESTAMP DEFAULT NOW(),
    completed TIMESTAMP,
    error int(1) DEFAULT 0,
    count bigint(20),
    type enum ('post','comment') NOT NULL DEFAULT 'post'
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
