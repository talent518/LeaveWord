var isIE=$.browser.msie,
	isIE6=isIE && $.browser.version=='6.0',
	isIE7=isIE && $.browser.version=='7.0',
	isIE8=isIE && $.browser.version=='8.0';
var isOpera=$.browser.opera;
var isMoz=$.browser.mozilla;
var isSafari=$.browser.safari;

String.prototype.left=function(len){
	if(len<=0) return this;
	return this.substr(0,len);
};
String.prototype.right=function(len){
	return this.substr(this.length-len);
};
String.prototype.repeat=function(len){
	var result='';
	for(var i=0;i<len;i++){
		result+=this;
	}
	return result;
};

//%2s,%2d,%2.2f
function sprintf(){
	var s=arguments[0] || '',r=[],c=0;
	for(var i=1;i<arguments.length;i++){
		r[i]=arguments[i];
	}

	return s.replace(/%([0-9.]+)?(s|d|f)/ig,function(a){
		c++;
		a=a.match(/([0-9.]+)|(s|d|f)/ig);
		if(a.length!=2){
			a[1]=a[0];
			a[0]=0;
		}
		a[1]=a[1].toLowerCase();
		if(a[1]=='f'){
			a=String(parseFloat(a[0])).split('.');
			a[0]=parseInt(a[0]);
			a[1]=parseInt(a[1]);
			var _r=String(parseFloat(r[c])).split('.'),f=_r[0].indexOf('-')!=-1;r[0]=(f?r[0].substr(1):r[0]);
			return((f?'-':'')+'0'.repeat(a[0]-_r[0].length)+_r[0]+(_r[1]?'.'+_r[1]+'0'.repeat(a[1]-_r[1].length):''));
		}else if(a[1]=='d'){
			a[0]=parseInt(a[0]);
			r[c]=parseInt(r[c]);
			return(r[c]<0?'-':'')+('0'.repeat(a[0]-String(r[c]).length)+(r[c]>0?r[c]:-r[c]));
		}else{
			a[0]=parseInt(a[0]);
			return(' '.repeat(a[0]-r[c].length)+r[c]);
		}
	});
};

;(function($){
	$.mapArray=function(arrays,callback){
		var newArray=new Array(),key,value;
		for(key in arrays){
			value=arrays[key];
			if($.isFunction(callback)){
				newArray.push(callback.call(this,key,value));
			}else if(typeof(callback)=='string'){
				newArray.push(callback.replace('{key}',new String(key)).replace('{value}',new String(value)));
			}else{
				newArray.push(key+'="'+value+'"');
			}
		}
		return newArray;
	};

	$.fn.fblur=function(warn){
		var $this=$(this);
		if(!$this.is('textarea,input') || ($this.attr('type')!='text' && $this.attr('type')!='password')){
			return this;
		}
		if($this.data('fblur')){
			$this.data('fblur').val(warn);
			return this;
		}
		var ipt=String($this.clone().val('').get(0).outerHTML).replace(/type\=\".+?\"/,'type="text"');
		var fblur=$(ipt).insertBefore(this);
		$this.hide().data('fblur',fblur).addClass('focusb');
		fblur.removeAttr('id');
		fblur.removeAttr('name');
		fblur.addClass('fblur');
		fblur.css({cursor:'pointer'});
		fblur.attr('title',warn);
		fblur.val(warn);
		fblur.focus(function(){
			fblur.hide();
			$this.show().focus();
		});
		$this.blur(function(){
			var val=$(this).val();
			if(val=='' || val==undefined){
				$this.hide();
				fblur.show();
			}
		});
		return this;
	};
})(jQuery);

(function($){
	$.getJson=function(url,data,callback){
		if($.isFunction(data)){
			callback=data;
			data={};
		}
		$.getJSON(url,data,$.isFunction(callback)?callback:function(json){
			if(json.redirect){
				alert(json.message);
				if(json.url){
					location.href=json.url;
				}
			}else{
				if(json.url && confirm(json.message)){
					location.href=json.url;
				}else{
					alert(json.message);
				}
			}
		});
	};
	$.postJson=function(url,data,callback){
		if($.isFunction(data)){
			callback=data;
			data={};
		}
		$.post(url,data,$.isFunction(callback)?callback:function(json){
			if(json.redirect){
				alert(json.message);
				if(json.url){
					location.href=json.url;
				}
			}else{
				if(json.url && confirm(json.message)){
					location.href=json.url;
				}else{
					alert(json.message);
				}
			}
		},'json');
	};
	if($.validator && $.fn.ajaxSubmit){
		var $ajaxSubmit=$.fn.ajaxSubmit;
		$.fn.ajaxSubmit=function(options){
			if($.isFunction(options)){
				options={success:options};
			}
			options.dataType='json';
			return $ajaxSubmit.call(this,options);
		};
		$.validator.setDefaults({
			submitQuiet:true,
			submitHandler:function(form){
				var settings=this.settings;
				$(form).ajaxSubmit(function(json){
					if($.isFunction(settings.callback)){
						settings.callback(json);
						return;
					}else if(typeof(json)=='object'){
						if(json.redirect){
							alert(json.message);
							if(json.url){
								location.href=json.url;
							}
						}else{
							if(json.url && confirm(json.message)){
								location.href=json.url;
							}else{
								alert(json.message);
							}
						}
					}else{
						alert('JSON Data Type Error!');
					}
				});
				return false;
			}
		});
	}
})(jQuery);

jQuery(function($){
	if(isIE6){
		$('input[type=button],button[type=button]').addClass('button');
		$('input[type=submit],button[type=submit]').addClass('submit');
	}
	$('#hd').ajaxStart(function(evt, request, settings){
		$(this).addClass('loading');
	}).ajaxSend(function(evt, request, settings){
		$(this).addClass('loading');
	}).ajaxComplete(function(event,request, settings){
		$(this).removeClass('loading');
	}).ajaxSuccess(function(event,request, settings){
		$(this).removeClass('loading');
	}).ajaxError(function(event,request, settings){
		$(this).removeClass('loading');
	}).ajaxStop(function(event,request, settings){
		$(this).removeClass('loading');
	});
});
