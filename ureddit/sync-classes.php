<?php
	require_once("URedditUser.class.php");
	require_once('../config.php');
	require_once('../course/lib.php');
	require_once('../lib/accesslib.php');
	require_login();

	function GetShortenedName($name){
		$find = array("/And/i", "/Analysis/i", "/Introduction/i", "/to/i");
		$replace = array("&", "Anal", "Intro", "");
		$name = preg_replace($find, $replace, $name);

		$name = ((strlen($name) > 10)? substr($name, 0, 10) : $name);

		return preg_replace("'\s+'", ' ', $name);
	}

	function GetURL() {
		$pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
		    $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} 
		else 
		{
		    $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}

	

	global $USER, $CFG;
	if($USER->auth != "ureddit"){
		header("Content-type: text/plain");
		echo "You must be logged in through UReddit to use this feature.";
		exit;
	}

	$r = new URedditUser($USER->username);
	$allTaughtClasses = $r->GetTaughtClasses();
	$classes = array();


	foreach($allTaughtClasses as $class){
		foreach(get_courses("all", "c.sortorder ASC", "c.idnumber") as $existingCourse){
			if($existingCourse->idnumber == $class->id){
				continue(2);
			}
		}

		$classes[] = $class;
	}
	

	if(isset($_POST["import"])){
		foreach($classes as $class){
			if($class->id == $_POST["import"]){
				$data = array(
						"category" => 1,
						"fullname" => $class->GetName() . " [#" . $class->id . "]",
						"shortname" => $class->id . ' - ' . GetShortenedName($class->GetName()),
						"idnumber" => $class->id,
						"summary_editor" => array(
								"text" => $class->GetSummary(),
								"format" => 1,
								"itemid" => 1
							),
						"format" => "weeks",
						"numsections" => "10",
						"startdate" => time(),
						"hiddensections" => 0,
						"newsitems" => 5,
						"showgrades" => 1,
						"showreports" => 0,
						"maxbytes" => 8388608,
						"enrol_guest_status_2" => 1,
						"groupmode" => 0,
						"groupmodeforce" => 0,
						"defaultgroupingid" => 0,
						"visible" => 1,
						"lang" => "",
						"enablecompletion" => 0,
						"completionstartonenrol" => 0,
						"restrictmodules" => 0,
						"mform_showadvanced_last" => 0,
						"role_1" => "",
						"role_2" => "",
						"role_3" => "",
						"role_4" => "",
						"role_5" => "",
						"role_6" => "",
						"role_7" => "",
						"role_8" => "",
						"id" => ""
					);
				$data = (object)$data;

				$editoroptions = array("maxfiles" => -1, "maxbytes" => 0, "trusttext" => "", "noclean" => 1);

		    	$course = create_course($data, $editoroptions);

		        // Get the context of the newly created course
		        $context = get_context_instance(CONTEXT_COURSE, $course->id, MUST_EXIST);

		        if (!empty($CFG->creatornewroleid) and !is_viewing($context, NULL, 'moodle/role:assign') and !is_enrolled($context, NULL, 'moodle/role:assign')) {
		            // deal with course creators - enrol them internally with default role
		            enrol_try_internal_enrol($course->id, $USER->id, $CFG->creatornewroleid);

		        }
		        if (!is_enrolled($context)) {
		            // Redirect to manual enrolment page if possible
		            $instances = enrol_get_instances($course->id, true);
		            foreach($instances as $instance) {
		                if ($plugin = enrol_get_plugin($instance->enrol)) {
		                    if ($plugin->get_manual_enrol_link($instance)) {
		                        // we know that the ajax enrol UI will have an option to enrol
		                        redirect(new moodle_url('/enrol/users.php', array('id'=>$course->id)));
		                    }
		                }
		            }
		        }

				header('Location: ' . GetURL());
				exit;
			}
		}
	}else{
		if(count($classes) == 0){
			header("Content-type: text/plain");
			echo "You have no classes to import.";
			exit;
		}

		?>

		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		  <title>University of Reddit Class Importer</title>
		  <style type="text/css">
		      *{margin:0;padding:0}p{padding:3px}body{font-family:verdana}ul,ol{margin-left:30px}div.class-desc a{color:black}div#header{background-color:#cee3f8;font-size:12px;width:100%;padding:3px 0;margin:0;border-bottom:1px solid #5f98cf;height:80px}div#header img{margin:0 3px}div#links{float:right;margin:0 3px;padding:65px 10px 0 0}#main{width:800px;font-family:verdana;font-size:12px;margin:10px}#main a{color:black}.pagetitle{font-size:1.9em;font-weight:bold;margin-bottom:10px}.pagetitle a:link,a:visited,a:hover,a:active{color:black}.pagetitle a:hover{text-decoration:none}.category{width:800px;margin:0 0 20px 0;padding:0 10px;border-left:0 solid #c3c3c3}.category-name{margin:5px 0;font-size:1.5em;font-weight:bold;width:100%;padding-bottom:3px;border-bottom:0 solid #d3d3d3}.class{margin:0 0 20px 15px;border:1px solid #c3c3c3;background-color:#f5f5f5;padding:10px 10px 5px 10px;-moz-border-radius:5px;-webkit-border-radius:5px;-khtml-border-radius:5px;border-radius:5px}.class-white{margin:0 0 20px 15px;padding:5px 10px 10px 10px}.class-name{font-size:1.1em;font-weight:bold;margin-bottom:5px}.class-desc{margin-left:15px;margin-bottom:3px}.class-info{font-size:.9em;font-style:italic;margin-left:30px;padding-bottom:5px}.class-info-noindent{font-size:.9em;font-style:italic;margin-left:0}img{border:0}input.teach{width:600px}textarea.teach{width:600px;height:170px;font-family:verdana;font-size:15px}.signup-button{padding:0 3px;margin:.25em 5px 0 0;line-height:1.5em;height:1.5em;font-size:.75em;min-width:1px;background-color:#cee3f8;border:1px solid #5f98cf;float:left}.teacher-button{padding:0 3px;margin:.25em 5px 0 0;line-height:1.5em;height:1.5em;font-size:.75em;min-width:1px;background-color:#9f6;border:1px solid #5fa43c;float:left}.deregister-button{padding:0 3px;margin:.25em 5px 0 0;line-height:1.5em;height:1.5em;font-size:.75em;min-width:1px;background-color:#f66;border:1px solid #c33;float:left}.showhide{font-size:10px;font-weight:normal;color:#555}a:link.link-showhide,a:visited.link-showhide,a:active.link-showhide,a:hover.link-showhide{font-size:10px;text-decoration:none;cursor:pointer}a:link.nav,a:visited.nav,a:active.nav,a:hover.nav{font-size:12px;color:black;text-decoration:none}.link-signup-button{cursor:pointer;color:black;text-decoration:none;border:none;padding:none;margin:none;background:transparent;font-size:9px;}a:link.nav-current,a:visited.nav-current,a:active.nav-current,a:hover.nav-current{font-size:12px;color:black;text-decoration:none;font-weight:bold;cursor:pointer}a:link.link-class-desc,a:visited.link-class-desc,a:active.link-class-desc,a:hover.link-class-desc{color:black}#footer{width:800px;font-size:10px;color:#939393;padding:5px;margin-bottom:10px}#footer a{color:#636363}
		  </style>
		</script>
		</head>

		<body>
		  <div id="header">
		    <a href="http://ureddit.com/"><img src="http://ureddit.com/images/logo.png" alt="Universift of Reddit" /></a>
		    <div id="links">&nbsp;</div>
		  </div>
		  <div id="main">
		    <div class="pagetitle">UReddit Importer</div>

		    <p>This page will let you import your University of Reddit classes into the Moodle installation.</p>

		    <div id="category">
		      <div class="category">
		        <div class="category-name">Classes you manage</div>
		        <div>

		        <?php foreach($classes as $class): ?>
		          <div class="class">
		            <div>
		              <div class="signup-button">
		                <td><form method="post"><input type="hidden" name="import" value="<?php echo $class->id; ?>" /><input class="link-signup-button" type="submit" value="Import this class" /></form></td>
		              </div>
		            </div>

		            <div class="class-name">
		              <?php echo $class->GetName(); ?> (ID <?php echo $class->id; ?>)
		            </div>
		          </div>
		        <?php endforeach; ?>
		        </div>
		      </div>
		    </div>
		  </div>

		  <div id="footer">
		    This site is not in any way affiliated with <a href="http://reddit.com"
		    target="_blank">Reddit.com</a>, <a href="http://condenast.com" target=
		    "_blank">Conde Nast</a>, or <a href="http://ureddit.com" target="_blank">UReddit.com</a>.
		  </div>
		</body>
		</html>
	<?php } ?>