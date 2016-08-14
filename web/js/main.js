$(document).ready(function(){
	var flashbagDiv = $("div.flashbag");
	var deletedDescription = flashbagDiv.text();
	deletedDescription = deletedDescription && deletedDescription.trim();
	if (deletedDescription.length === 0) {
		flashbagDiv.text("please fill in description without any more empty text");
	}
	flashbagDiv.fadeOut(2000);
});