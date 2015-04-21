 var $j = jQuery.noConflict();
 $j(document).ready(function(){

 	$j("#set-default").click(function(){
 		$j('input[name=line_url]').val("https://sdl-stickershop.line.naver.jp/products/0/0/3//1027905/LINEStorePC/preview.png");
 		$j('input[name=line_name]').val("Bubble");
 		$j('input[name=line_text]').val("Express your feeling!");
 		$j('input[name=line_1]').val("emo-0-0");
 		$j('input[name=line_2]').val("emo-0-1");
 		$j('input[name=line_3]').val("emo-0-2");
 		$j('input[name=line_4]').val("emo-0-3");
 		$j('input[name=line_5]').val("emo-1-0");
 	});

 });


 function setvote(postid , column){

	$j('#plus'+column).addClass("trans");

 	$j.post(
		// see tip #1 for how we declare global javascript variables
		admin_ajax,
		{
			// here we declare the parameters to send along with the request
			// this means the following action hooks will be fired:
			// wp_ajax_nopriv_myajax-submit and wp_ajax_myajax-submit
			action : 'myajax-submit',

			// other parameters can be added along with "action"
			post_id : postid,
			position : column
		},
		function( response ) {
    		var obj = JSON.parse(response);
    		var sum = 0;
    		for(var x in obj){
			  sum += parseInt(obj[x]);
			}

    		$j('#txtline_1').text(Math.round(obj['column1'] * 100 / sum) + "%");
	 		$j('#txtline_2').text(Math.round(obj['column2'] * 100 / sum) + "%");
	 		$j('#txtline_3').text(Math.round(obj['column3'] * 100 / sum) + "%");
	 		$j('#txtline_4').text(Math.round(obj['column4'] * 100 / sum) + "%");
	 		$j('#txtline_5').text(Math.round(obj['column5'] * 100 / sum) + "%");

	 		$j('#plus'+column).removeClass("trans");
		}
	);

 }