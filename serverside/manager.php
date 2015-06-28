<?php
//if(file_exists("./MANAGER_LOCK")){die('Closing time!');}; // Doesn't work. Whatever.
header("Access-Control-Allow-Origin: *");
if(!empty($_GET['input']) && !empty($_POST['url'])){
	$filter_url = filter_var($_POST['url'],FILTER_SANITIZE_URL);
	$file_name = md5($filter_url);
	file_put_contents('unsorted/'.$file_name,$filter_url);
} else{
	if(!empty($_GET['action'])){
		switch($_GET['action']){
			case $_GET['action'] == 'download' && !empty($_POST['id']) && !empty($_POST['category']):
				print "Server download: \n";
				$file_id = preg_replace('/[^a-zA-Z0-9]/','',$_POST['id']);
				print "id: ".$file_id."\n";
				$filter_url = file_get_contents("./unsorted/".$file_id);
				print "Requested URL: ".$filter_url."\n";
				$category = strtolower(preg_replace('/[^a-zA-Z0-9]/','',$_POST['category']));
				if(!file_exists("./".$category)){die('No folder exists! (Did you delete it while this was working?');}
				print "Category: ".$category."\n";
				$path_parts = pathinfo($filter_url);
				$allowed_exts = array("jpg","jpeg","png","bmp","gif");
				if(!empty($path_parts['extension']) && !in_array(strtolower($path_parts['extension']),$allowed_exts)){$path_parts['extension'] .= ".png";} //nasty way but whatever
				if(empty($path_parts['extension'])){$path_parts['extension'] = 'png';}

				$file_name = !empty(basename($filter_url,'.'.$path_parts['extension'])) ? basename($filter_url,'.'.$path_parts['extension']) : $file_id;
				$file_name .= ".".$path_parts['extension'];
				print "Chosen filename: ".$file_name."\n";
				if(!file_exists("./".$category.'/'.$file_name)){
					$ch = curl_init($filter_url);
					$fp = fopen(dirname(__FILE__)."/".$category.'/'.$file_name, 'w+');
					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_TIMEOUT, 1000);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
					if(curl_exec($ch)){
						print "OK";
						unlink("./unsorted/".$file_id);
					}
					curl_close($ch);
					fclose($fp);
				} else {
					die("File already exists!");
				}
			break;
			case $_GET['action'] == 'delete' && !empty($_POST['id']):
				print "Deletion:\n";
				$file_id = preg_replace('/[^a-zA-Z0-9]/','',$_POST['id']); 
				print "id : ".$file_id." \n";
				if(file_exists('unsorted/'.$file_id) && unlink('unsorted/'.$file_id)){
						print "OK";
						die();
				} else {
					die('No file to delete!');
				}
			break;
		}
	} else {
		?>
<!DOCTYPE html>
<html>
	<head>
		<title>IMAGE MANAGER - FP Scrapper</title>
		<style>
			html{background-color:rgb(230,230,255);background-attachment:cover}
			.item{padding:20px}
			.item:hover{background-color:#CCFF99;border:solid 2px black;padding:18px}
			.item img{max-width:90%;height:auto;max-height:90vh}
			.animated { 
				-webkit-animation-duration: 1s; 
				animation-duration: 1s; 
				-webkit-animation-fill-mode: both; 
				animation-fill-mode: both; 
			} 

			@-webkit-keyframes fadeOutLeft { 
				0% { 
					opacity: 1; 
					-webkit-transform: translateX(0); 
				} 
				100% { 
					opacity: 0; 
					-webkit-transform: translateX(-20px); 
				} 
			} 
			@keyframes fadeOutLeft { 
				0% { 
					opacity: 1; 
					transform: translateX(0); 
				} 
				100% { 
					opacity: 0; 
					transform: translateX(-20px);
				} 
			} 
			.fadeOutLeft { 
				-webkit-animation-name: fadeOutLeft; 
				animation-name: fadeOutLeft; 
			}
		</style>
		<script>
		var server_url = [location.protocol, '//', location.host, location.pathname].join('');
		function getObjectID(item){
			return item.parentElement.attributes['data-id'].value;
		};
		function checkItems(){
			var list = document.getElementsByClassName('item');
			if(list.length == 0){
				document.getElementsByTagName('body')[0].innerHTML = "<h2 style='text-align:center'>No more images for now! Reload the page to get more.</h2>";
			};
		};
		function DoDelete(item){
			var objectID = getObjectID(item);
			var http = new XMLHttpRequest();
			var url = server_url+"?action=delete";
			var params = "id="+objectID;
			http.open("POST", url, true);
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.setRequestHeader("Content-length", params.length);
			http.setRequestHeader("Connection", "close");
			http.onreadystatechange = function() {
				if(http.readyState == 4 && http.status == 200) {
					console.log(http.responseText);
					item.parentElement.className = item.parentElement.className + " animated fadeOutLeft";
					setTimeout(function(){item.parentElement.remove();checkItems();},1000);
				};
			};
			http.send(params);
			
		};
		function DoDownload(item){
			var objectID = getObjectID(item);
			
			var http = new XMLHttpRequest();
			var url = server_url+"?action=download";
			var category = item.parentElement.getElementsByTagName('select')[0].value;
			var params = "id="+objectID+"&category="+category;
			http.open("POST", url, true);
			http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			http.setRequestHeader("Content-length", params.length);
			http.setRequestHeader("Connection", "close");
			http.onreadystatechange = function() {
				if(http.readyState == 4 && http.status == 200) {
					console.log(http.responseText);
					item.parentElement.className = item.parentElement.className + " animated fadeOutLeft";
					setTimeout(function(){item.parentElement.remove();checkItems();},1000);
					checkItems();
				};
			};
			http.send(params);
			
		};
		</script>
	</head>
	<body>
			<?php
			$allImages = glob("./unsorted/*");
			$allcats = glob("*",GLOB_ONLYDIR);
			function removeInternal($vl){
				if($vl == "unsorted" or $vl == "sections_images"){return false;} else {return $vl;}
			}
			$allcats = array_filter($allcats,'removeInternal');
			if(count($allImages) > 0){
				foreach($allImages as $k => $singleImage){
					if($k <= 100){
						$imageURL = file_get_contents($singleImage);
						print '<div class="item selected" data-id="'.basename($singleImage).'"><a href="'.$imageURL.'"><img src="'.$imageURL.'" alt="oh shit broken image link asdfgh" /></a><br />Add to <select>';
						foreach($allcats as $singlecat){
							print '<option value="'.$singlecat.'">'.ucfirst($singlecat).'</option>';
						};
						print '</select><input type="button" value="Add it" onClick="DoDownload(this);" /><input type="button" value="Delete" onClick="DoDelete(this);" /></div>';
					};
				}
			} else {
				print "<h2 style='text-align:center'>No more images to filter! (This page will be disabled now.)";
				//file_put_contents("./MANAGER_LOCK","AAAAAAA");
			}?>
	</body>
</html>	
		
	<?php };
	}
?>