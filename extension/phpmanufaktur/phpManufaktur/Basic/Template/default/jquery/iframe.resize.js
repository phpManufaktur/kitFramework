if (typeof 'jQuery' != 'undefined') {
  $(document).ready(function() {
	  // setting the iFrame height dynamically by the height of the inbounded document
	  var if_height = $('#kf_body').height() + 30 + "px";
	  parent.document.getElementById("kf_iframe").style.height = if_height;
  });
}