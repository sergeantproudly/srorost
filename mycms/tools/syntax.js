// CHECKS
function stxCheckNotEmpty(fld){
	if(fld.value)return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'&&!hasClass(fld,'ckeditor')?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'Поле не может быть пустым');
		}
		return false;
	}
}
function stxCheckTheSame(fld,mode){
	if(typeof(mode)=='undefined')mode=1;
	var fld2=gei(fld.id+'_repeat');
	if(fld.value==fld2.value)return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'Пароль не совпадает с повторным');
		}
		return false;
	}
}
function stxCheckUploaded(fld,mode){
	if(typeof(mode)=='undefined')mode=1;
	if(!fld.getAttribute('uploading'))return true;
	else{
		var elem=domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,'Дождитесь загрузки файла');
		}
		return false;
	}
}
function stxCheck(fld,patternTitle,mode){
	if(typeof(mode)=='undefined')mode=1;
	var value=fld.value;
	var reg=new RegExp(stxPatterns[patternTitle]['pattern']);
	if(!value || reg.test(value))return true;
	else{
		var elem=fld.tagName.toLowerCase()=='textarea'?domP(domP(domP(fld))):domP(fld);
		addClass(elem,'inp-error');
		if(mode=1){
			stxAddErrorStatus(elem,stxPatterns[patternTitle]['error']);
		}
		return false;
	}
}

var stxPatterns={
	'number'	: {
		'pattern'	: '^\-?[0-9]+$',
		'error'		: 'Значением должно быть число'
	},
	'float'	: {
		'pattern'	: '^\-?[0-9,\.]+$',
		'error'		: 'Значением может быть число или число с запятой'
	},
	'date'		: {
		'pattern'	: '^[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4}( [0-9]{1,2}:[0-9]{1,2})*$',
		'error'		: 'Дата должна удовлетворять формату дд.мм.гггг'
	},
	'email'		: {
		'pattern'	: '^[a-zA-Z0-9_\.\-]+@([a-zA-Z0-9][a-zA-Z0-9\-]+\.)+[a-zA-Z]{2,6}$',
		'error'		: 'E-mail должен удовлетворять шаблону: something@something.something'
	},
	'phone'		: {
		'pattern'	: '^[0-9\+\-() ,\.]+$',
		'error'		: 'Телефон может содержать цифры, знаки плюса, минуса, скобки и точку'
	},
	'login'	: {
		'pattern'	: '^[a-zA-Z0-9\._]+$',
		'error'		: 'Логин может содержать латинские символы, цифры, точку и нижнее подчёркивание'
	},
	'password'	: {
		'pattern'	: '^[a-zA-Z0-9\._]+$',
		'error'		: 'Пароль может содержать латинские символы, цифры, точку и нижнее подчёркивание'
	}
};
function stxCheckElements(checks,mode){
	if(typeof(mode)=='undefined')mode=1;
	var correct=true;
	for(var i=0;i<checks.length;i++){
		for(var j=0;j<checks[i].pattern.length;j++){
			var object=checks[i].object;
			var pattern=checks[i].pattern[j];
			if(pattern=='important'){
				if(!stxCheckNotEmpty(object,mode))correct=false;
			}else if(pattern=='uploaded'){
				if(!stxCheckUploaded(object,mode))correct=false;
			}else if(pattern=='same'){
				if(!stxCheckTheSame(object,mode))correct=false;
			}else{
				if(!stxCheck(object,pattern,mode))correct=false;
			}
		}
	}
	return correct;
}
function stxAddErrorStatus(inp,message){
	var status=domC('div','status status-error',message);
	var space=domCT(' ');
	domAN(space,inp);
	domAN(status,inp);
}
function stxResetStatus(elem,mode){
	if(typeof(mode)=='undefined')mode=1;
	if(elem.tagName.toLowerCase()!='form'){
		var inp=elem.tagName.toLowerCase()=='textarea'?domP(domP(domP(elem))):domP(elem);
		removeClass(inp,'inp-error');
		if(mode==1){
			var status=domSC(domP(inp),'status');
			if(status){
				domD(status.previousSibling);
				domD(status);
			}
		}
	}else{
		var fld=null;
		for(var i=0;elem.elements[i];i++){
			fld=elem.elements[i];
			if((fld.tagName.toLowerCase()=='input')||(fld.tagName.toLowerCase()=='textarea')){			
				stxResetStatus(fld);
			}
		}
	}
}