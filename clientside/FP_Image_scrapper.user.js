// ==UserScript==
// @name        FP Image Scrapper
// @namespace   Comentarinformal FP Stuff
// @description Steal all the images from a thread, and uploads the link to the given URL
// @include     http://facepunch.com/showthread.php?t=XXXXXX*
// CHANGE THE ABOVE XXs FOR THE DESIRED THREAD'S NUMBER.
// @version     1
// @grant       none
// ==/UserScript==
$('.above_body').append("<div id='loadier' style='width: 100%; text-align: center; background-color: rgb(204, 0, 0); color: white; z-index: 100 ! important; height: 24px;'>Scrapping images... <span id='imagesDone'>0</span> / <span id='imagesTotal'>?</span>  <span id='currentURL'></span> <div id='isUrlDone' style='display:inline-block;width:auto;height:auto;opacity:0'><img  src='http://www.facepunch.com/fp/ratings/tick.png' alt='done' /></div></div>");
$('#header').css('box-shadow','0 0 0 0');
$('#loadier').hide().slideDown('fast');

var server_url = ''; //Change for your server's url to manager.php

var filteredImgs = $('img').filter(function(index){return $(this).attr('src').indexOf('facepunch.com') == -1 && $(this).attr('src').indexOf('//') > -1;});
var seen = {};
$(filteredImgs).each(function() {
    var txt = $(this).attr('src');
    if (seen[txt])
        $(this).remove();
    else
        seen[txt] = true;
});
$('#imagesTotal').text(filteredImgs.length);
if (filteredImgs.length > 0){
$(filteredImgs).each(function(index,el){
	setTimeout(function(){
		$('#isUrlDone').css('opacity','0');
		$('#imagesDone').text(index+1);
		$('#currentURL').text(el.src);
		var http = new XMLHttpRequest();
		var url = server_url+"?input=1";
		var params = "url="+el.src;
		console.log(params);
		http.open("POST", url, true);
		http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		http.setRequestHeader("Content-length", params.length);
		http.setRequestHeader("Connection", "close");
		http.onreadystatechange = function() {//Call a function when the state changes.
			if(http.readyState == 4 && http.status == 200) {
				$('#isUrlDone').css('opacity','1');
			}
		}
		http.send(params);
		if((parseInt(index)+1) == filteredImgs.length){
			parser = document.createElement('a');
			parser.href = window.location;
			if(document.getElementsByClassName('prev_next').length > 0){ //There's more pages
				newQuery = document.getElementsByClassName('prev_next')[1].children[0].href;
				setTimeout(function(){
				window.location = newQuery;
				},3000);
			$('#currentURL').text('');
			$('#isUrlDone').html('<img src="http://www.facepunch.com/fp/events/toobig.png" alt="moving" /> Moving to next page...');
			$('#isUrlDone').css('opacity','1');
			} else {
				alert("Reached end of thread!");
			}
			
		}
	},1000*(parseInt(index)+1));
});
} else {
	parser = document.createElement('a');
	parser.href = window.location;
	if(document.getElementsByClassName('prev_next').length > 2){ //There's more pages
		newQuery = document.getElementsByClassName('prev_next')[1].children[0].href;
		setTimeout(function(){
		window.location = newQuery;
		},3000);
	$('#currentURL').text(newQuery);
	$('#isUrlDone').html('<img src="http://www.facepunch.com/fp/events/toobig.png" alt="moving" /> Moving to next page...');
	$('#isUrlDone').css('opacity','1');
	} else {
		$('#isUrlDone').html('<img src="http://www.facepunch.com/fp/events/closed.png" alt="moving" /> Reached end of thread!');
		$('#isUrlDone').css('opacity','1');
	}
}