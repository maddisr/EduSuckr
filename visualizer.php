<?php
    /* Require configuration */
    require_once("includes/config.php");
    /* Require database */
    require_once("includes/db.php");
    /* Require api functions */
    require_once("includes/api.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>JSViz Force Directed Layout: Random Circuit</title>

		<!--	
			Licensed under the Apache License, Version 2.0 (the "License");
			you may not use this file except in compliance with the License.
 			You may obtain a copy of the License at
 
				http://www.apache.org/licenses/LICENSE-2.0

 			Unless required by applicable law or agreed to in writing, software
 			distributed under the License is distributed on an "AS IS" BASIS,
 			WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 			See the License for the specific language governing permissions and
 			limitations under the License.

 			Author: Kyle Scholz      http://kylescholz.com/
 			Copyright: 2006-2007
 		-->
		
		<!-- JSViz Libraries -->
		<script language="JavaScript" src="includes/jsviz/physics/ParticleModel.js"></script>
		<script language="JavaScript" src="includes/jsviz/physics/Magnet.js"></script>
		<script language="JavaScript" src="includes/jsviz/physics/Spring.js"></script>
		<script language="JavaScript" src="includes/jsviz/physics/Particle.js"></script>
		<script language="JavaScript" src="includes/jsviz/physics/RungeKuttaIntegrator.js"></script>
		
		<script language="JavaScript" src="includes/jsviz/layout/graph/ForceDirectedLayout.js"></script>
		<script language="JavaScript" src="includes/jsviz/layout/view/HTMLGraphView.js"></script>
		<script language="JavaScript" src="includes/jsviz/layout/view/SVGGraphView.js"></script>

		<script language="JavaScript" src="includes/jsviz/util/Timer.js"></script>
		<script language="JavaScript" src="includes/jsviz/util/EventHandler.js"></script>

		<script language="JavaScript" src="includes/jsviz/io/DataGraph.js"></script>
		<script language="JavaScript" src="includes/jsviz/io/HTTP.js"></script>
		<script language="JavaScript" src="includes/jsviz/io/XMLTreeLoader.js"></script>
		<script language="JavaScript">
            var nodes_data=<?php echo getCourseLinkingConnectionsStatistics($_GET['guid']); ?>
        </script>
		<script language="JavaScript" src="includes/jsviz/visualizer/network_diagram.js"></script>

		<style type="text/css">
			body { margin: 0; padding: 0; }
		</style>
	</head>
	<body onload="init()">
	    <div id="debug" style="position:absolute"></div>
	    <div id="graph" style="position:absolute;overflow:hidden;width:720px;height:400px;background-color:#ffffff;border:2px solid #bbbb22;"></div>
	</body>
</html>