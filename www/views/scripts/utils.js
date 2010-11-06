
// event bindings
jQuery(function($){

	// browse.tpl, search.tpl -- show icons on hover
	var orig_opac = $('table.data tr').children('td.icons').children('div.icon').css('opacity');
	$('table.data tr').hover(
		function(){	$(this).children('td.icons').children('div.icon').css('opacity',1); },
		function(){	$(this).children('td.icons').children('div.icon').css('opacity',orig_opac); }
	);
	
	$('.nzb_check_all').change(function(){
		$('table.data tr td input:checkbox').attr('checked',$(this).attr('checked'));
	});

	// browse.tpl, search.tpl
	$('.icon_cart').click(function(e){
		if ($(this).hasClass('icon_cart_clicked')) return false;
		var guid = $(this).parent().parent().attr('id').substring(4);
		$.post( SERVERROOT + "cart.php?add=" + guid, function(resp){
			$(e.target).addClass('icon_cart_clicked').attr('title','added to cart');
		});
		return false;
	});
	$('.icon_sab').click(function(e){ // replace with cookies?
		if ($(this).hasClass('icon_sab_clicked')) return false;

		var guid = $(this).parent().parent().attr('id').substring(4);
		var priority = $.cookie('sabnzbd_'+UID+'__priority');
		if (priority == null || priority == "")
			priority = "1";
			
		var fullsaburl = $.cookie('sabnzbd_'+UID+'__host') + "api/?mode=addurl&priority=" + priority + "&apikey=" + $.cookie('sabnzbd_'+UID+'__apikey');
		var nzburl = SERVERROOT + "download/sab/nzb/" + guid + "&i=" + UID + "&r=" + RSSTOKEN;

		$.post( fullsaburl+"&name="+escape(nzburl), function(resp){
			$(e.target).addClass('icon_sab_clicked').attr('title','added to queue');
		});
		return false;
	});
	$("table.data a.modal_nfo").colorbox({	 // NFO modal
		href: function(){ return $(this).attr('href') +'&modal'; },
		title: function(){ return $(this).parent().parent().children('a.title').text(); },
		innerWidth:"800px", innerHeight:"90%", initialWidth:"800px", initialHeight:"90%", speed:0, opacity:0.7
	});
	$("table.data a.modal_imdb").colorbox({	 // IMDB modal
		href: function(){ return SERVERROOT + "movie/"+$(this).attr('name').substring(4)+'&modal'; },
		title: function(){ return $(this).parent().parent().children('a.title').text(); },
		innerWidth:"800px", innerHeight:"450px", initialWidth:"800px", initialHeight:"450px", speed:0, opacity:0.7
	}).click(function(){
		$('#colorbox').removeClass().addClass('cboxMovie');	
	});
	$('#nzb_multi_operations_form').submit(function(){return false;});
	$('input.nzb_multi_operations_download').click(function(){
		var ids = "";
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	if ($(row).val()!="on")
		    	ids += $(row).val()+',';
	    });
	    ids = ids.substring(0,ids.length-1);
	    if (ids)
			window.location = SERVERROOT + "getnzb.php?zip=1&id="+ids;
	});
	$('input.nzb_multi_operations_cart').click(function()
	{
	
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	var $cartIcon = $(row).parent().parent().children('td.icons').children('.icon_cart');
	    	var guid = $(row).parent().parent().attr('id').substring(4);
			if (guid && !$cartIcon.hasClass('icon_cart_clicked')){
				$.post( SERVERROOT + "cart.php?add=" + guid, function(resp){
					$cartIcon.addClass('icon_cart_clicked').attr('title','added to cart');
				});
			}
		});
	});
	$('input.nzb_multi_operations_sab').click(function()
	{
		var priority = $.cookie('sabnzbd_'+UID+'__priority');
		if (priority == null || priority == "")
			priority = "1";	

		var fullsaburl = $.cookie('sabnzbd_'+UID+'__host') + "api/?mode=addurl&priority=" + priority + "&apikey=" + $.cookie('sabnzbd_'+UID+'__apikey');
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	var $sabIcon = $(row).parent().parent().children('td.icons').children('.icon_sab');
	    	var guid = $(row).parent().parent().attr('id').substring(4);
			if (guid && !$sabIcon.hasClass('icon_sab_clicked')){
				var nzburl = SERVERROOT + "download/sab/nzb/" + guid + "&i=" + UID + "&r=" + RSSTOKEN;
				$.post( fullsaburl+"&name="+escape(nzburl), function(resp){
					$sabIcon.addClass('icon_sab_clicked').attr('title','added to queue');
				});
			}
		});
	});
	//front end admin functions
	$('input.nzb_multi_operations_edit').click(function(){
		var ids = "";
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	if ($(row).val()!="on")
		    	ids += '&id[]='+$(row).val();
	    });
	    if (ids)
			$('input.nzb_multi_operations_edit').colorbox({
				href: function(){ return SERVERROOT + "ajax_release-admin.php?action=edit"+ids; },
				title: 'Edit Release',
				innerWidth:"400px", innerHeight:"250px", initialWidth:"400px", initialHeight:"250px", speed:0, opacity:0.7
			});
	});
	$('input.nzb_multi_operations_delete').click(function(){
		var ids = "";
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	if ($(row).val()!="on")
		    	ids += '&id[]='+$(row).val();
	    });
	    if (ids)
			if (confirm('Are you sure you want to delete the selected releases?')) {
				$.post(SERVERROOT + "ajax_release-admin.php?action=dodelete"+ids, function(resp){
					window.location = window.location;
				});
			}
	});
	$('input.nzb_multi_operations_rebuild').click(function(){
		var ids = "";
	    $("table.data INPUT[type='checkbox']:checked").each( function(i, row) {
	    	if ($(row).val()!="on")
		    	ids += '&id[]='+$(row).val();
	    });
	    if (ids)
			if (confirm('Are you sure you want to rebuild the selected releases?')) {
				$.post(SERVERROOT + "ajax_release-admin.php?action=dorebuild"+ids, function(resp){
					window.location = window.location;
				});
			}
	});

	// headermenu.tpl
	$('#headsearch')
		.focus(function(){if(this.value == 'Enter keywords') this.value = '';})
		.blur (function(){if(this.value == '') this.value = 'Enter keywords';});
	$('#headsearch_form').submit(function(){
		$('headsearch_go').trigger('click');
		return false;
	});
	$('#headsearch_go').click(function(){
		if ($('#headsearch').val() && $('#headsearch').val() != 'Enter keywords')
		{
			document.location= WWW_TOP + "/search/" + $('#headsearch').val() + ($("#headcat").val()!=-1 ? "&t="+$("#headcat").val() : "");
		}
	});

	// search.tpl
	$('#search_search_button').click(function(){
		if ($('#search').val())
			document.location=WWW_TOP + "/search/" + $('#search').val();
		return false;
	});

	// searchraw.tpl
	$('#searchraw_search_button').click(function(){
		if ($('#search').val())
			document.location=WWW_TOP + "/searchraw/" + $('#search').val();
		return false;
	});
	$('#searchraw_download_selected').click(function(){
		if ($('#dl input:checked').length)
			$('#dl').trigger('submit');
		return false;
	});

	// login.tpl, register.tpl, search.tpl, searchraw.tpl
	if ($('#username').length)
		$('#username').focus();
	if ($('#search').length)
		$('#search').focus();

	// viewfilelist.tpl
	$('#viewfilelist_download_selected').click(function(){
		if ($('#fileform input:checked').length)
			$('#fileform').trigger('submit');
		return false;
	});

	// misc
	$('.confirm_action').click(function(){ return confirm('Are you sure?'); });


	// searchraw.tpl, viewfilelist.tpl -- checkbox operations
	// selections
	var last1, last2;
	$(".checkbox_operations .select_all").click(function(){
	    $("table.data INPUT[type='checkbox']").attr('checked', true).trigger('change');
		return false;
	});
	$(".checkbox_operations .select_none").click(function(){
	    $("table.data INPUT[type='checkbox']").attr('checked', false).trigger('change');
		return false;
	});
	$(".checkbox_operations .select_invert").click(function(){
	    $("table.data INPUT[type='checkbox']").each( function() {
	        $(this).attr('checked', !$(this).attr('checked')).trigger('change');
	    });
		return false;
	});
	$(".checkbox_operations .select_range").click(function(){
		if (last1 && last2 && last1 < last2)
	    	$("table.data INPUT[type='checkbox']").slice(last1,last2).attr('checked', true).trigger('change');
		else if (last1 && last2)
	    	$("table.data INPUT[type='checkbox']").slice(last2,last1).attr('checked', true).trigger('change');
		return false;
	});
	$('table.data td.selection INPUT[type="checkbox"]').click(function(e) {
	    // range event interaction -- see further above
		var rowNum = $(e.target).parent().parent()[0].rowIndex ;
	    if (last1) last2 = last1;
		last1 = rowNum;
	});
	$('table.data a.data_filename').click(function(e) { // click filenames to select
	    // range event interaction -- see further above
		var rowNum = $(e.target).parent().parent()[0].rowIndex ;
	    if (last1) last2 = last1;
		last1 = rowNum;

		var $checkbox = $('table.data tr:nth-child('+(rowNum+1)+') td.selection INPUT[type="checkbox"]');
		$checkbox.attr('checked', !$checkbox.attr('checked'));
    
		return false;
	});



	// SABnzbd integration
	if ($.cookie('sabnzbd_'+UID+'__host')) {
		$('table.data .icon_sab').show();	// sab icons hidden by default
		$('.nzb_multi_operations_sab').show();	// sab icons hidden by default
		$('table.data td.icons').addClass('icons_with_sab');
		
		// set profile.tpl credentials into profile on page load
		if ($('#profile_sab_host').val()) 
		{
			$('#profile_sab_apikey').val($.cookie('sabnzbd_'+UID+'__apikey'));
			$('#profile_sab_host').val($.cookie('sabnzbd_'+UID+'__host'));
			if ($.cookie('sabnzbd_'+UID+'__priority') != null)
				$('#profile_sab_priority').val($.cookie('sabnzbd_'+UID+'__priority'));
		}
	}
	// profile.tpl
	$('#profile_sab_save').click(function(){	// store sabnzbd info to cookie
		$.cookie('sabnzbd_'+UID+'__apikey', $.trim($('#profile_sab_apikey').val()), { expires: 365 });
		$.cookie('sabnzbd_'+UID+'__host', $.trim($('#profile_sab_host').val()), { expires: 365 });
		$.cookie('sabnzbd_'+UID+'__priority', $('#profile_sab_priority').val(), { expires: 365 });
		$(this).next('.icon').addClass('icon_check'); // save status notification
	});
	$('#profile_sab_clear').click(function(){	// store sabnzbd info to cookie
		$.cookie('sabnzbd_'+UID+'__apikey', '');
		$.cookie('sabnzbd_'+UID+'__host', '');
		$.cookie('sabnzbd_'+UID+'__priority', '');
		$('#profile_sab_apikey, #profile_sab_host, #profile_sab_priority').val('');
		$('#profile_sab_save').next('.icon').removeClass('icon_check'); // save status notification
	});


	// show/hide invite form
	$('#lnkSendInvite').click(function()
	{
		$('#divInvite').slideToggle('fast');
	});

	// send an invite
	$('#frmSendInvite').submit(function() 
	{
		var inputEmailto = $("#txtInvite").val();
		if (isValidEmailAddress(inputEmailto))
		{
		
			// no caching of results
			var rand_no = Math.random();
			$.ajax({
			  url       : WWW_TOP + '/ajax_profile.php?action=1&rand=' + rand_no,
			  data      : { emailto: inputEmailto},
			  dataType  : "html",
			  success   : function(data)
			  {
				$("#txtInvite").val("");
				$('#divInvite').slideToggle('fast');
				$("#divInviteSuccess").text(data).show();
				$("#divInviteError").hide();
			  },
			  error: function(xhr,err,e) { alert( "Error in ajax_profile: " + err ); }
			});			
		}
		else
		{
			$("#divInviteSuccess").hide();
			$("#divInviteError").text("Invalid email").show();
		}
		return false;
	});

});


$.extend({ // http://plugins.jquery.com/project/URLEncode
URLEncode:function(c){var o='';var x=0;c=c.toString();var r=/(^[a-zA-Z0-9_.]*)/;
  while(x<c.length){var m=r.exec(c.substr(x));
    if(m!=null && m.length>1 && m[1]!=''){o+=m[1];x+=m[1].length;
    }else{if(c[x]==' ')o+='+';else{var d=c.charCodeAt(x);var h=d.toString(16);
    o+='%'+(h.length<2?'0':'')+h.toUpperCase();}x++;}}return o;},
URLDecode:function(s){var o=s;var binVal,t;var r=/(%[^%]{2})/;
  while((m=r.exec(o))!=null && m.length>1 && m[1]!=''){b=parseInt(m[1].substr(1),16);
  t=String.fromCharCode(b);o=o.replace(m[1],t);}return o;}
});


function isValidEmailAddress(emailAddress) 
{
	var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
	return pattern.test(emailAddress);
}