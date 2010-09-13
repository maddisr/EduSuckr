<?php
/* Register Complex Types */
    /* add type Array */
    $server->wsdl->addComplexType('Array',
        'complexType',
        'array',
        '',
        'SOAP-ENC:Array',
        array(),
        array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
        'xsd:string'
    );
    /* add type EduCourse */
    $server->wsdl->addComplexType('EduCourse',
	    'complexType',
	    'struct',
	    'all',
	    '',
	    array(
		    'course_guid' => array('name' => 'course_guid',
		     	'type' => 'xsd:int'),
		    'title' => array('name' => 'title',
		 	    'type' => 'xsd:string'),
		    'description' => array('name' => 'description',
		 	    'type' => 'xsd:string'),
		 	'posts' => array('name' => 'posts',
		 	    'type' => 'xsd:string'),
		 	'comments' => array('name' => 'comments',
		 	    'type' => 'xsd:string'),
		    'course_tag' => array('name' => 'course_tag',
		     	'type' => 'xsd:string'),
		    'course_blog' => array('name' => 'course_blog',
		 	    'type' => 'xsd:string'),
		 	'course_wiki' => array('name' => 'course_wiki',
		 	    'type' => 'xsd:string'),
		 	'signup_deadline' => array('name' => 'signup_deadline',
		 	    'type' => 'xsd:string'),
		 	'course_starting_date' => array('name' => 'course_starting_date',
		 	    'type' => 'xsd:string'),
		 	'course_ending_date' => array('name' => 'course_ending_date',
		     	'type' => 'xsd:string')
	    )
    );
    /* add type Participant */
    $server->wsdl->addComplexType('Participant',
	    'complexType',
	    'struct',
	    'all',
	    '',
	    array(
	        'participant_id' => array('name' => 'participant_id',
		     	'type' => 'xsd:int'),
		    'course_guid' => array('name' => 'course_guid',
		     	'type' => 'xsd:int'),
		    'firstname' => array('name' => 'firstname',
		 	    'type' => 'xsd:string'),
		    'lastname' => array('name' => 'lastname',
		     	'type' => 'xsd:string'),
		    'email' => array('name' => 'email',
		 	    'type' => 'xsd:string'),
		 	'blog' => array('name' => 'blog',
		 	    'type' => 'xsd:string'),
		 	'posts' => array('name' => 'posts',
		 	    'type' => 'xsd:string'),
		 	'comments' => array('name' => 'comments',
		 	    'type' => 'xsd:string'),
		 	'blogger_id' => array('name' => 'blogger_id',
		 	    'type' => 'xsd:string'),
		 	'status' => array('name' => 'status',
		 	    'type' => 'xsd:string')
	    )
    );
    /* add type Assignment */
    $server->wsdl->addComplexType('Assignment',
	    'complexType',
	    'struct',
	    'all',
	    '',
	    array(
	        'assignment_id' => array('name' => 'assignment_id',
		     	'type' => 'xsd:int'),
		    'course_guid' => array('name' => 'course_guid',
		     	'type' => 'xsd:int'),
		    'title' => array('name' => 'title',
		 	    'type' => 'xsd:string'),
		    'description' => array('name' => 'description',
		     	'type' => 'xsd:string'),
		    'blog_post_url' => array('name' => 'blog_post_url',
		 	    'type' => 'xsd:string'),
		 	'deadline' => array('name' => 'deadline',
		 	    'type' => 'xsd:string')
	    )
    );
?>