			function init() {

				/* 1) Create a new SnowflakeLayout.
				 * 
				 * If you're going to place the graph in an HTML Element, other
				 * the <body>, remember that it must have a known size and
				 * position (via element.offsetWidth, element.offsetHeight,
				 * element.offsetTop, element.offsetLeft).
				 */
				var layout = new ForceDirectedLayout( document.getElementById("graph"), false );
				layout.view.skewBase=400;
				layout.setSize();
				layout.config._default = {
					model: function( dataNode ) {
						return {
							mass:0.5
						}
					},
					view: function( dataNode, modelNode ) {
						if ( layout.svg ) {
							var nodeElement = document.createElementNS("http://www.w3.org/2000/svg", "circle");
							nodeElement.setAttribute('stroke', '#888888');
							nodeElement.setAttribute('stroke-width', '.25px');
							nodeElement.setAttribute('fill', dataNode.color);
							nodeElement.setAttribute('r', 10 + 'px');
							nodeElement.onmousedown =  new EventHandler( layout, layout.handleMouseDownEvent, modelNode.id )
							return nodeElement;
						} else {
							var nodeElement = document.createElement( 'div' );
							nodeElement.style.position = "absolute";
							nodeElement.style.color = dataNode.color;
							nodeElement.style.fontSize = dataNode.size+"%";
							nodeElement.innerHTML = dataNode.title;
							//nodeElement.onmousedown =  new EventHandler( layout, layout.handleMouseDownEvent, modelNode.id )
							return nodeElement;
						}
					}
				}

        		layout.forces.spring._default = function( nodeA, nodeB, isParentChild ) {
					return {
						springConstant: 0.2,
						dampingConstant: 0.2,
						restLength: 60
					}
				}
				
        		layout.forces.magnet = function() {
					return {
						magnetConstant: -500,
						minimumDistance: 60
					}
				}

				
				/* 3) Override the default edge properties builder.
				 * 
				 * @return DOMElement
				 */ 
				layout.viewEdgeBuilder = function( dataNodeSrc, dataNodeDest ) {
					if ( this.svg ) {
						return {
							'stroke': dataNodeSrc.color,
							'stroke-width': '2px',
							'stroke-dasharray': '2,4'
						}
					} else {
						return {
							'pixelColor': dataNodeSrc.color,
							'pixelWidth': '2px',
							'pixelHeight': '2px',
							'pixels': 10
						}
					}
				}

				/* 4) Load up some stuff by hand
				 * 
				 */
				
				layout.model.ENTROPY_THROTTLE=false;
				var nodes = nodes_data[0];
				var rels = nodes_data[1];
				var gnodes = [];
				for ( var i=0; i<nodes.length; i++ ) {
					var node = new DataGraphNode();
					node.color="#000000";
					node.mass=0.5;
					node.title=nodes[i]['person'];
					node.size=nodes[i]['size'];
					layout.newDataGraphNode( node );
					gnodes[nodes[i]['person']] = node;				
				}
				for ( var i=0; i<rels.length; i++ ) {
				    layout.newDataGraphEdge( gnodes[rels[i]['person']], gnodes[rels[i]['links']] );							
				}
				/* 5) Control the addition of nodes and edges with a timer.
				 * 
				 * This enables the graph to start organizng as data is loaded.
				 * Use a larger tick time for smoother animation, but slower
				 * build time.
				 */
				 
				var buildTimer = new Timer( 150 );
				buildTimer.subscribe( layout );
				buildTimer.start();
			}
		
