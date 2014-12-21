$(function(){

	var	$ = window.jQuery,
		jDoc = $(document),
		jWin = $(window),
		jBody = $("body");

	var legoCanvas = $(".lego-image"),
		legoBrick = $(".brick"),
		legoPanel = $(".control-panel"),
		legoActions = $(".actions"),
		legoColorWheel = $(".colorpicker");

	var legoPieces = $(".pieces"),
		brickHelper = $(".brick-helper");
	

		legoBrick.hover(
			function(e){
				var jThis = $(this),
					thisBrickClass = jThis.attr("class"),
					thisBrick = thisBrickClass.substr(thisBrickClass.lastIndexOf("-")+1,thisBrickClass.length)+"x1 brick";
				legoBrick.removeClass("hover");
				$(this).addClass("hover");

				var thisX = e.pageX,
					thisY = e.pageY;

				brickHelper.fadeIn().css({
					top:thisY - 80,
					left:thisX - brickHelper.width()/2
				}).html(thisBrick);
			},
			function(){

			}
		);

	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---UPLOAD BUTTON POSITION

	var thisY, limitY, newY;
	jWin.on("scroll",function(e){
		limitY = $("#upload").position().top  + $("#upload").height() +190;
		thisY = $(window).scrollTop() + $(window).height();
		if(thisY > limitY){
			$("#upload-image, .form-bg").addClass("fixed");
		} else {
			$("#upload-image, .form-bg").removeClass("fixed");
		}
	});


	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---IMAGE READY

	var oldScroll, newScroll;

	if($(".generated").size() > 0){
		$(".hidden").not(".printer").fadeIn();
		if($(".print-enabled").size() > 0){
			$(".printer").fadeIn();
		}
		var thisTop = $(".lego-image").offset().top;
		$("html, body").animate({
			"scrollTop":thisTop -30
		},1000,function(){
			oldScroll = $(window).scrollTop();
			jWin.on("scroll",function(e){
				newScroll = $(window).scrollTop();
				if(newScroll > oldScroll + 200){
					$(".helper").fadeOut();
				}
				
			});
		});

		if($(".generated").attr("data-size") != undefined){
			$("#get-size option[value='"+$(".generated").attr("data-size")+"']").prop("selected",true);
		}
	}

	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---IMAGE UPLOAD & PROCESS

	var imageForm = $("form#upload-image");

	imageForm.find(".file").on("change",function(){
		imageForm.submit();
	});



	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---BRICKS TRANSLATOR

	function translateBrick(thisBrickName){
		if(thisBrickName.lastIndexOf("hover") != -1){
			return thisBrickName.substr(6, thisBrickName.length -12)+"x1";
		} else {
			return thisBrickName.substr(6, thisBrickName.length)+"";
		}
		
	}



	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---LIST OF NEEDED BRICKS

	legoPieces.append("<dl></dl>");

	var legoDl = legoPieces.find("dl"),
		lastColor,
		thisList ="";

	var colorTxt = {
		"en":{
			"dt":"bricks",
			"dd":"bricks"
		},
		"fr":{
			"dt":"Briques Lego©",
			"dd":"Briques Lego©"
		}
	}

	function refreshBricks(){
		var colorList = {};
		var legoDl = legoPieces.find(".list"),
		lastColor,
		thisList ="";

		legoDl.empty();
		legoBrick.each(function(){
			var jThis = $(this),
				jThisColor = jThis.attr("data-colorname"),
				jThisClass = jThis.attr("class");
				if(jThisClass.lastIndexOf("hover") != -1){
					var jBrickType = jThisClass.substr(6, jThisClass.length -12)+"x1";
				} else {
					var jBrickType = jThisClass.substr(6, jThisClass.length)+"x1";
				}

			if(jThisColor != "" && !jThis.hasClass("disabled")){
				if(! colorList[jThisColor]){
					colorList[jThisColor] = {
						color: jThisColor
					};
					colorList[jThisColor][jBrickType] = {
						brickType : translateBrick(jBrickType),
						number : 1
					};
				} else {
					if(! colorList[jThisColor][jBrickType]){
						colorList[jThisColor][jBrickType] = {
							brickType : translateBrick(jBrickType),
							number : 1
						};
					} else {
						colorList[jThisColor][jBrickType].number +=1;
					}
				}
			}
		});


		var listI = 0;
		//---MAKE THIS LIST HAPPEN !
		for (var a in colorList){

			
				thisList += "<dl>";
			
			var thisColor = colorList[a].color;
			thisList += "<dt class='"+thisColor.toLowerCase()+"'><span></span><em>"+thisColor+" "+colorTxt["en"]["dt"]+"</em></dt>";
			
			for(var b in colorList[a]){
				if(b != "color"){
					var brickType = colorList[a][b].brickType,
					brickNumber = colorList[a][b].number;

					thisList +="<dd><strong>"+brickNumber+"</strong> "+brickType+" "+colorTxt["en"]["dd"]+"</dd>";
					
				}
			}

			thisList += "</dl>";

			listI++;
		}

		legoDl.html(thisList);

		var biggerHeight = 0,
			lastOne = 0;
		
		legoDl.find("dl").each(function(){
			var jThisHeight = $(this).height();


			if(jThisHeight > lastOne && jThisHeight > biggerHeight){
				biggerHeight = $(this).height();
			}
			lastOne = jThisHeight;
		});

		legoDl.find("dl").css("height",biggerHeight);

	}
	
	refreshBricks();


	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---SVG COLOR PICKER 

	Object.size = function(obj) {
	    var size = 0,
			key;
		for (key in obj) {
			if (obj.hasOwnProperty(key)) size++;
		}
		return size;
	};
	
	$.getJSON("ressources/colors.json", function(data){
		var colorSize = Object.size(data),
			rotationRatio = 360/colorSize,
			radianAngle = Math.PI*(rotationRatio)/180,
			colorLastPosition = 0,
			colorTotal = 0,
			xmlns = "http://www.w3.org/2000/svg",
			svgCanvas = document.getElementById("svg"),
			startRadianAngle = 0,
			endRadianAngle = radianAngle,
			centerCircle = 140,
			radiusCircle = 140,
			elem = document.createElementNS(xmlns, "path"),
			dataId = 0;

		for(var a in data){
			
			var elem = document.createElementNS(xmlns, "path"),
				x1 = centerCircle + radiusCircle*Math.cos(startRadianAngle),
    	        y1 = centerCircle + radiusCircle*Math.sin(startRadianAngle), 

				x2 = centerCircle + radiusCircle*Math.cos(endRadianAngle),
            	y2 = centerCircle + radiusCircle*Math.sin(endRadianAngle);                

            startRadianAngle = endRadianAngle;
			endRadianAngle = startRadianAngle + radianAngle;
			

			elem.setAttributeNS(null,"d", "M 140 140 L "+x1+" "+y1+" A 140 140 0 0,1 "+x2+" "+y2+"");
			elem.setAttributeNS(null,"fill", "rgba("+data[a].rgb+",1)");
			elem.setAttributeNS(null,"style", "display:none;");
			elem.setAttributeNS(null,"data-id", dataId); 
			elem.setAttributeNS(null,"data-colorname", data[a].name); 

			svgCanvas.appendChild(elem);
			
			dataId++;
		}

		
		//---COLOR WHEEL ACTIONS

		var colorEye = $(".colorpicker .cancel");
		$("#svg path").hover(
			function(){
				var jThis = $(this),
					jThisColor = jThis.attr("fill");
				
				colorEye.css("background-color",jThisColor).addClass("nobg");
			},
			function(){
				colorEye.attr("style","").removeClass("nobg");
			}
		);


		legoActions.find(".change-color").on("click",function(e){
			e.preventDefault;
			showColorWheel();
		});

		$("#svg path").on("click",function(e){
			var thisColor = $(this).attr("fill"),
				thisColorName = $(this).attr("data-colorname");

			oneSelectedBrick.css("background-color",thisColor).attr("data-colorname",thisColorName).removeClass("selected");
			refreshBricks();
			hideColorWheel();
			setTimeout(function(){
				hidePanel();
			},400);

		});

		legoColorWheel.find(".cancel").on("click",function(e){
			hideColorWheel();
		});
		
	});
	
	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---SAVE/RESET ACTIONS

	if($(".saved").size() > 0){
		$(".save").css({
			"opacity":"0.3",
			"cursor":"auto"

		});
	}

	$(".save-reset .save").click(function(e){

		if($(".saved").size() > 0){
			e.preventDefault();
		} else {
			var jThisSeed = $("input[name='previous-name']").attr("value");
			var jThisComplexity = $(".change-size option:selected").attr("value");
			window.location.href += "?art="+jThisSeed+"&complexity="+jThisComplexity;
		}
		
	});

	$(".save-reset .reset").click(function(){
		window.location.href = "/";
	});

	$(".save-reset .print").click(function(){
		var jThisSeed = $("input[name='previous-name']").attr("value");
		if(window.location.href.lastIndexOf("art=") == -1){
			window.open(window.location.href += "?print=true&art="+jThisSeed);
		} else {
			window.open(window.location.href += "&print=true");
		}
		
	});

	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---COMPLEXITY

	if(window.location.href.lastIndexOf("complexity=") != -1){
		var url = window.location.href,
			jThisComplexity = url.substr(url.lastIndexOf("complexity=")+11, 2);
			$(".change-size option[value="+jThisComplexity+"]").prop("selected",true);
	}

	$(".change-size button").click(function(e){
		e.preventDefault;
		var jThisComplexity = $(".change-size option:selected").attr("value");
		var jThisSeed = $("input[name='previous-name']").attr("value");

		var url = window.location.href;

		if(window.location.href.lastIndexOf("art=") == -1 && window.location.href.lastIndexOf("complexity=") == -1){
		
			window.location.href += "?complexity="+jThisComplexity+"&art="+jThisSeed;
		
		} else if(window.location.href.lastIndexOf("art=") != -1){

			if(window.location.href.lastIndexOf("complexity=") != -1){
				var regEx = /([?&]complexity)=([^#&]*)/g;
				var newurl = url.replace(regEx, '$1='+jThisComplexity);
				window.location.href = newurl;
			} else {
				window.location.href += "&complexity="+jThisComplexity;
			}
			
		}
		
	});



	//--------------------
	//--------------------
	//--------------------
	//--------------------
	//---BRICK ACTIONS

	oneSelectedBrick = "";

	legoBrick.on("click",function(e){
		e.preventDefault();
		var curX = e.pageX - 160,
			curY = e.pageY - 160;

		legoPanel.css({
			"top": curY,
			"left": curX
		});
		showPanel();

		legoBrick.removeClass("selected");
		$(this).addClass("selected");
		oneSelectedBrick = $(this);
	});


	//Show/Hide Control panel
	function showPanel(thisBrick){
		legoPanel.fadeIn();
	}
	function hidePanel(){
		legoPanel.fadeOut();
	}

	function showColorWheel(){
		legoColorWheel.fadeIn();
		legoColorWheel.find("path").each(function(){
			var jThis = $(this),
				iteration = 0,
				itNb = legoColorWheel.find("path").length;

			colorInterval = setInterval(function(){
				$("path[data-id='"+iteration+"']").fadeIn();
				iteration++;
				if(iteration == itNb){
					clearInterval(colorInterval);
					
				}
			},20);
		});
	}
	function hideColorWheel(){
		legoColorWheel.find("path").each(function(){
			var jThis = $(this),
				iteration = 0,
				itNb = legoColorWheel.find("path").length;

			colorInterval = setInterval(function(){
				$("path[data-id='"+iteration+"']").fadeOut();
				iteration++;
				if(iteration == itNb){
					clearInterval(colorInterval);
					
				}
			},20);
		});
		setTimeout(function(){
			legoColorWheel.fadeOut().removeClass("active");
		},400);
	}

	//Menu hovers
	legoActions.find("span").hover(
		function(){
			var jThis = $(this);
			if(jThis.hasClass("close")){
				legoActions.addClass("step-close");
			} else if(jThis.hasClass("add")){
				legoActions.addClass("step-1");
			} else if(jThis.hasClass("putback-color")){
				legoActions.addClass("step-2");
			} else if(jThis.hasClass("change-color")){
				legoActions.addClass("step-3");
			} else if(jThis.hasClass("remove-color")){
				legoActions.addClass("step-4");
			} else if(jThis.hasClass("remove")){
				legoActions.addClass("step-5");
			}
		},
		function(){
			legoActions.removeClass().addClass("actions")
		}
	);


	//Brick Actions
	function removeThisBrick(selectedBrick){
		selectedBrick.addClass("disabled");
		refreshBricks();
		hidePanel();
	}
	function addThisBrick(selectedBrick){
		selectedBrick.removeClass("disabled");
		refreshBricks();
		hidePanel();
	}

	function removeAllBricksThisColor(selectedBrick){
		thisColor = selectedBrick.attr("data-colorname");
		$(".brick[data-colorname='"+thisColor+"']").addClass("disabled");
		refreshBricks();
		hidePanel();
	}

	function putbackAllBricksThisColor(selectedBrick){
		thisColor = selectedBrick.attr("data-colorname");
		$(".brick[data-colorname='"+thisColor+"']").removeClass("disabled");
		refreshBricks();
		hidePanel();
	}



	legoActions.find(".remove").on("click",function(e){
		e.preventDefault;
		removeThisBrick($(".selected"));
		$(".selected").removeClass("selected");
	});

	legoActions.find(".add").on("click",function(e){
		e.preventDefault;
		addThisBrick($(".selected"));
		$(".selected").removeClass("selected");
	});

	legoActions.find(".remove-color").on("click",function(e){
		e.preventDefault;
		removeAllBricksThisColor($(".selected"));
		$(".selected").removeClass("selected");
	});

	legoActions.find(".putback-color").on("click",function(e){
		e.preventDefault;
		putbackAllBricksThisColor($(".selected"));
		$(".selected").removeClass("selected");
	});

	legoActions.find(".close").on("click",function(e){
		hidePanel();
	});


	

});
