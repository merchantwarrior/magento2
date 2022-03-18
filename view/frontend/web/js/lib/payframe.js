function payframe(firstPar,_apikey,_payframeDivId,_src, _submitUrl, _styleInput, _acceptedCardTypesInput, _methodInput){
    let accessToken, uuid, apikey, payframeDivId, src, submitUrl, styleInput, acceptedCardTypesInput, methodInput;
    if(typeof firstPar === 'object') {

        if(typeof firstPar.accessToken !== 'undefined'){accessToken = firstPar.accessToken;}
        //add uuid/apikey support?
        if(typeof firstPar.payframeDivId !== 'undefined'){payframeDivId = firstPar.payframeDivId;}
        if(typeof firstPar.src !== 'undefined'){src = firstPar.src;}
        if(typeof firstPar.submitUrl !== 'undefined'){submitUrl = firstPar.submitUrl;}
        if(typeof firstPar.style !== 'undefined'){styleInput = firstPar.style;}
        if(typeof firstPar.acceptedCardTypes !== 'undefined'){acceptedCardTypesInput = firstPar.acceptedCardTypes;}
        if(typeof firstPar.method !== 'undefined'){methodInput = firstPar.method;}

        //skip straight to vars (not *Input validation first) in object case?

        if(typeof accessToken === 'undefined' || typeof payframeDivId === 'undefined' || typeof src === 'undefined' || typeof submitUrl === 'undefined') {
            throw 'Please initiate payframe as - var payframe = new payframe(payframeData); where payframeData contains accessToken, payframeDivId, src, submitUrl, style(optional), acceptedCardTypes(optional), method(optional)';
        }
    } else {
        uuid = firstPar;
        apikey = _apikey;
        payframeDivId = _payframeDivId;
        src = _src;
        submitUrl = _submitUrl;
        styleInput = _styleInput;
        acceptedCardTypesInput = _acceptedCardTypesInput;
        methodInput = _methodInput;
        if(!uuid || !apikey || !payframeDivId || !src || !submitUrl){
            throw 'please initiate payframe as - var payframe = new payframe(uuid,apikey,payframeDivId,src,submitUrl,style(optional),acceptedCardTypes(optional),method(optional)); - payframeDivId is the div you wish the payframe to appear in';
            return false;
        }
    }
    if(document.getElementById(payframeDivId) == null) {
        throw "payframeDivId entered '" + payframeDivId + "' doesn't exist";
    }
    this.mwCallback = null;/*function(){
		throw 'please add a callback function to run when token has generated - eg mwPayframe.mwCallback = function(){ --code };';
	};*/
    this.loading = function loading() {

    };
    this.loaded = function loaded() {

    };
    this.mwCardTypeUpdated = function() {

    };
    this.mwSurchargeUpdated = function() {

    };
    this.mwPayframeValidated = function(payframeValid, mwMessage) {

    }
    var mwPayframe = this;
    this.active = false;
    if(typeof accessToken !== 'undefined') {
        this.accessToken = accessToken;
    } else {
        this.merchantUUID = uuid;
        this.apiKey = apikey;
    }
    if(submitUrl.toLowerCase() == 'camp') {
        submitUrl = 'https://base.merchantwarrior.com/payframe/';
    } else if(submitUrl.toLowerCase() == 'prod' || submitUrl.toLowerCase() == 'production') {
        submitUrl = 'https://api.merchantwarrior.com/payframe/';
    } else if(typeof submitUrl == 'string' && (submitUrl.toLowerCase().indexOf('merchantwarrior.com') > 0 || submitUrl.toLowerCase().indexOf('merchantwarrior.test') > 0)) {
        submitUrl = submitUrl;
        if(submitUrl.substring(submitUrl.length-1, submitUrl.length) != "/") {
            submitUrl += "/";
        }
    } else {
        submitUrl = 'https://api.merchantwarrior.com/payframe/';
    }
    this.submitUrl = submitUrl;
    mwPayframe.amountElementID = '';
    this.div = document.getElementById(payframeDivId);
    this.mwIframe = document.createElement("iframe");

    let iframeSrc = src.toLowerCase();
    if(iframeSrc == 'camp') {
        iframeSrc = 'https://securetest.merchantwarrior.com/payframe/';
    } else if(iframeSrc == 'prod' || iframeSrc == 'production') {
        iframeSrc = 'https://secure.merchantwarrior.com/payframe/';
    } else if(typeof iframeSrc == 'string' && (iframeSrc.indexOf('merchantwarrior.com') > 0 || iframeSrc.indexOf('merchantwarrior.test') > 0)) {
        if(iframeSrc.substring(iframeSrc.length-1, iframeSrc.length) != "/") {
            iframeSrc += "/";
        }
    } else {
        iframeSrc = 'https://secure.merchantwarrior.com/payframe/';
    }
    this.iframeSrc = iframeSrc;
    //var payform = document.getElementById(payformId);
    this.tokenStatus = 'NO_TOKEN';
    this.result = '';

    //Default callback implementation vars
    this.demoMode = false;
    this.jqueryLoaded = false;
    this.submitButton = {
        obj: null,
        width: null,
        height: null,
        html: null
    };

    //Check inputs
    var acceptedCardTypes;
    var method;
    var style;
    if(typeof styleInput == 'string') {
        if(checkCardTypes(styleInput)) {
            acceptedCardTypes = styleInput;
        } else if(checkMethodTypes(styleInput)){
            method = styleInput;
        } else if(acceptedCardTypes){

        } else {
            acceptedCardTypes = '';
        }
    } else if (styleInput && styleInput.constructor === Array) {
        if(checkCardTypes(styleInput.join())) {
            acceptedCardTypes = styleInput;
        } else if(acceptedCardTypes){

        } else {
            acceptedCardTypes = '';
        }
    } else if(typeof styleInput === 'object'){
        style = styleInput;
    } else {
        //console.log("style not present or invalid: " + styleInput);
    }
    if(typeof acceptedCardTypesInput == 'string') {
        if(checkCardTypes(acceptedCardTypesInput)) {
            acceptedCardTypes = acceptedCardTypesInput;
        } else if(checkMethodTypes(acceptedCardTypesInput)){
            method = acceptedCardTypesInput;
        }else if(acceptedCardTypes){

        }  else {
            acceptedCardTypes = '';
        }
    } else if (acceptedCardTypesInput && acceptedCardTypesInput.constructor === Array) {
        if(checkCardTypes(acceptedCardTypesInput)) {
            acceptedCardTypes = acceptedCardTypesInput;
        } else if(acceptedCardTypes){

        } else {
            acceptedCardTypes = '';
        }
    } else if(typeof acceptedCardTypesInput === 'object') {
        style = acceptedCardTypesInput;
    } else {
        //console.log("card types not present or invalid: " + acceptedCardTypesInput);
    }
    if(typeof methodInput == 'string') {
        if(checkCardTypes(methodInput)) {
            acceptedCardTypes = methodInput;
        } else if(checkMethodTypes(methodInput)){
            method = methodInput;
        }else if(acceptedCardTypes){

        }  else {
            acceptedCardTypes = '';
        }
    } else if (methodInput && methodInput.constructor === Array) {
        if(checkCardTypes(methodInput)) {
            acceptedCardTypes = methodInput;
        } else if(acceptedCardTypes){

        } else {
            acceptedCardTypes = '';
        }
    } else if(typeof methodInput === 'object') {
        style = methodInput;
    } else {
        //console.log("method type not present or invalid: " + methodInput);
    }
    function checkCardTypes(input) {
        var inputToCheck = input.toLowerCase();

        if(inputToCheck.indexOf('amex') >= 0)
            return true;
        if(inputToCheck.indexOf('diners') >= 0)
            return true;
        if(inputToCheck.indexOf('jcb') >= 0)
            return true;
        if(inputToCheck.indexOf('laser') >= 0)
            return true;
        if(inputToCheck.indexOf('visa') >= 0)
            return true;
        if(inputToCheck.indexOf('mastercard') >= 0)
            return true;
        /*if(inputToCheck.indexOf('maestro') >= 0)
            return true;*/
        if(inputToCheck.indexOf('discover') >= 0)
            return true;
        if(inputToCheck.indexOf('unionpay') >= 0)
            return true;
        return false;
    }

    function checkMethodTypes(input) {
        var inputToCheck = input.toLowerCase();
        if(inputToCheck.indexOf('getpayframetoken') >= 0)
            return true;
        if(inputToCheck.indexOf('addcard') >= 0)
            return true;
        return false;
    }
    if(method) {
        checkValidInput('method', method, "'addcard', 'getpayframetoken'");
        if(method.toLowerCase() == 'addcard'){
            this.method = 'addCard';
        } else if(method.toLowerCase() == 'getpayframetoken') {
            this.method = 'getPayframeToken';
        }
    } else {
        //console.log("Using default method: getPayframeToken");
        this.method = 'getPayframeToken';
    }
    //Accepted Card Types
    var cardTypes = '';
    var types = [];
    if(typeof acceptedCardTypes === 'string') {
        types = acceptedCardTypes.split(',');
    } else if(acceptedCardTypes && acceptedCardTypes.constructor === Array) {
        types = acceptedCardTypes;
    } else {
        //console.log("Accepted types null: " + acceptedCardTypes);
    }
    for (var i = 0; i < types.length; i++) {
        cardTypes += convertCardTypeInput(types[i]);
        if(i < types.length-1) {
            cardTypes += ', ';
        }
    }
    function convertCardTypeInput(type) {
        if(type) {
            var strippedType = type.toLowerCase().replace(/[^\w\s]|_/g, "").replace(/\s+/g, "");
            switch(strippedType) {
                case 'amex':
                    return 'amex';
                case 'diners':
                    return 'diners';
                case 'dinersclub':
                    return 'diners';
                case 'discover':
                    return 'discover';
                case 'jcb':
                    return 'jcb';
                case 'laser':
                    return 'laser';
                /*case 'maestro':
                    return 'maestro';*/
                case 'mc':
                case 'mastercard':
                    return 'mastercard';
                case 'visa':
                    return 'visa';
                case 'unionpay':
                    return 'unionpay';
                default:
                    throw "Invalid card type entered: " + type;
            }
        } else {
            return '';
        }

    }
    let iframePath = "";
    if(typeof this.accessToken !== 'undefined') {
        iframePath += "?accessToken=" + this.accessToken;
    } else {
        iframePath += "?merchantUUID=" + uuid + "&apiKey=" + this.apiKey;
    }
    iframePath += "&cards=" + cardTypes;
    this.mwIframe.src = this.iframeSrc +  iframePath;
    if(style && style.customPayframe) {
        this.mwIframe.src += "&customPayframe";
        if(style.payframeID) {
            this.mwIframe.src += "&payframeID="+style.payframeID;
        }
        this.mwIframe.src += "&method="+ this.method;
    }
    this.mwIframe.name = 'mwIframe';
    this.mwIframe.id = 'mwIframe';
    this.mwIframe.scroll = 'no';

    //Check invalid style inputs
    if(style) {
        if(style.cardImageAlignment)
            checkValidInput('cardImageAlignment', style.cardImageAlignment, "'left', 'right', or 'none'");
        if(style.cardImageAnimation)
            checkValidInput('cardImageAnimation', style.cardImageAnimation);
        if(style.cardImageSize)
            checkValidInput('cardImageSize', style.cardImageSize, "'full' or 'half'");
        if(style.cardTypeDisplay)
            checkValidInput('cardTypeDisplay', style.cardTypeDisplay, "'left', 'right', 'middle', or 'none'");
        if(style.fieldAutoTabbing)
            checkValidInput('fieldAutoTabbing', style.fieldAutoTabbing);
        if(style.formLayout)
            var layout = style.formLayout;
        if(typeof layout == 'undefined') {layout = '0';}
        if(typeof layout == 'number') {layout = layout.toString();}
        if(layout == '2-row') {layout = '0';}
        if(layout == '4-row') {layout = '1';}
        checkValidInput('formLayout', layout, "'0' or '1'");
    }

    function checkValidInput(name, value, options) {
        if(!options){options  = "'enabled' or 'disabled'";}
        if(options.indexOf(value.toLowerCase()) >= 0) {
            return true;
        } else {
            throwInputError(name, options);
        }
    }
    function throwInputError(name, propOptions) {
        if(!propOptions) {propOptions = "'enabled' or 'disabled'";}
        throw "Invalid input for " + name + ". Please enter " + propOptions;
    }
    var minHeight;
    if(style) {
        var fieldHeight = (style.fieldHeight)?parseInt(style.fieldHeight): 41;//40
        var marginTop = (style.marginTop)?parseInt(style.marginTop):(style.margin)?parseInt(style.margin)-0: 3;//3
        var marginBottom = (style.marginBottom)?parseInt(style.marginBottom):(style.margin)?parseInt(style.margin)-0: 3;//3
        var errorLabel = (style.errorDisplayMode && style.errorDisplayMode.toLowerCase() == 'popup')? 3: 20;
        if(style.errorLabelStyle && style.errorLabelStyle['lineHeight']){errorLabel *= parseFloat(style.errorLabelStyle['lineHeight']);}
        var errorPaddingTop = (style.errorLabelStyle)?((style.errorLabelStyle['paddingTop'])?parseInt(style.errorLabelStyle['paddingTop']):(style.errorLabelStyle['padding'])?parseInt(style.errorLabelStyle['padding']):3):3;
        var errorPaddingBottom = (style.errorLabelStyle)?((style.errorLabelStyle['paddingBottom'])?parseInt(style.errorLabelStyle['paddingBottom']):(style.errorLabelStyle['padding'])?parseInt(style.errorLabelStyle['padding']):0):0;
        var errorBorder = (style.errorLabelStyle && style.errorLabelStyle['border'])?2:0;
        var fieldLabelHeight = (style.fieldLabelStyle && (!style.fieldLabelStyle['display'] || (style.fieldLabelStyle['display'] && style.fieldLabelStyle['display'].toLowerCase() != 'none')))?20:0;
        if(style.fieldLabelStyle && fieldLabelHeight != 0 && style.fieldLabelStyle['lineHeight']){fieldLabelHeight *= parseFloat(style.fieldLabelStyle['lineHeight']);}
        var fieldLabelMarginTop = (style.fieldLabelStyle && fieldLabelHeight != 0)?((style.fieldLabelStyle['marginTop'])?parseInt(style.fieldLabelStyle['marginTop']):(style.fieldLabelStyle['margin'])?parseInt(style.fieldLabelStyle['margin']):0):0;
        var fieldLabelMarginBottom = (style.fieldLabelStyle && fieldLabelHeight != 0)?((style.fieldLabelStyle['marginBottom'])?parseInt(style.fieldLabelStyle['marginBottom']):(style.fieldLabelStyle['margin'])?parseInt(style.fieldLabelStyle['margin']):0):0;
        if(style.payframeHeightScaling && style.payframeHeightScaling.toLowerCase() == 'dynamic') {
            minHeight = (fieldHeight + (fieldLabelHeight + fieldLabelMarginTop + fieldLabelMarginBottom) + ( marginTop + marginBottom));
        } else {
            minHeight = (fieldHeight + (fieldLabelHeight + fieldLabelMarginTop + fieldLabelMarginBottom) + (errorLabel + errorPaddingTop + errorPaddingBottom + errorBorder) + ( marginTop + marginBottom));
        }

    } else {
        minHeight = (40 + 20 +(3 *2)) ;
    }
    var cardTypeBuffer;
    if(style && style.cardTypeDisplay && style.cardTypeDisplay.toLowerCase() == 'none') {
        cardTypeBuffer = 0;
    }else if(style && style.fieldLabelStyle && (!style.fieldLabelStyle['display'] || (style.fieldLabelStyle['display'] && style.fieldLabelStyle['display'].toLowerCase() != 'none'))) {
        cardTypeBuffer = 0;
    } else {
        if(style && style.fieldHeight) {
            cardTypeBuffer = parseInt(style.fieldHeight) * 0.4;
        } else {
            cardTypeBuffer = 20;
        }
    }
    var topMargin = 10;
    var width;
    var height;
    if(style) {
        if(style.width) {
            width = style.width;
        } else if(style.formLayout) {//switch based on formLayout
            var layout = style.formLayout;
            if(typeof layout == 'number') {
                layout = layout.toString();
            }
            layout = layout.toLowerCase();
            switch(layout) {
                case '2-row':
                case '0':
                    width = '500px';
                    break;
                case '4-row':
                case '1':
                    width = '300px';
                    break;
                default:
                    width = '500px';
            }
        } else {
            width = '600px';
        }
        if(style.height) {
            height = style.height;
        } else if(style.formLayout) {//switch based on formLayout
            var layout = style.formLayout;
            if(typeof layout == 'number') {
                layout = layout.toString();
            }
            layout = layout.toLowerCase();
            if(layout == '0' || layout == '2-row') {
                height = ((minHeight * 2) + cardTypeBuffer + topMargin) + "px";
            } else if(layout == '1' || layout == '4-row') {
                if(this.method == 'addCard') {
                    height = ((minHeight * 3) + cardTypeBuffer + topMargin) + "px";
                } else {
                    height = ((minHeight * 4) + cardTypeBuffer + topMargin) + "px";
                }
            } else {
                height = ((minHeight * 2) + cardTypeBuffer + topMargin) + "px";
            }
        } else {
            height = ((minHeight * 2) + cardTypeBuffer + topMargin) + 'px';
        }
    } else {
        height = ((minHeight * 2) + cardTypeBuffer + topMargin) + 'px';
        width = '500px';
    }
    this.mwIframe.height = height;
    this.mwIframe.width = width;

    this.deploy = function(){
        mwPayframe.active = true;
        mwPayframe.div.style.display = 'none';
        mwPayframe.div.appendChild(this.mwIframe);
        document.getElementById(mwPayframe.mwIframe.id).setAttribute('allowtransparency','true');
        document.getElementById(mwPayframe.mwIframe.id).setAttribute('frameborder',0);
        mwPayframe.responseCode = null;
        mwPayframe.responseMessage = null;
        mwPayframe.tokenStatus = 'NO_TOKEN';
        mwPayframe.payframeKey = null;
        mwPayframe.payframeToken = null;
        mwPayframe.cardID = null;
        mwPayframe.customerData = null;
        if(typeof mwPayframe.loading == 'function'){mwPayframe.loading();}
        if(this.mwCallback == null) {
            this.demoMode = true;
            if(typeof jQuery != 'undefined' && $ == jQuery) {this.jqueryLoaded = true;}
            this.mwCallback = function() {
                if(this.jqueryLoaded && mwPayframe.responseCode && mwPayframe.responseCode == '0') {
                    if(this.submitButton.obj) {
                        $(this.submitButton.obj).removeClass('mw-loading');
                        $(this.submitButton.obj).addClass('mw-success');
                        $(".mw-circle-loader").addClass('mw-load-complete');
                        $(".mw-checkmark").css('display', 'block');
                    }
                } else {
                    if(this.jqueryLoaded && this.submitButton.obj && this.submitButton.html) {
                        //$(".mw-circle-loader").css('display', 'none');
                        //set submit html
                        $(this.submitButton.obj).removeClass('mw-loading');
                        $(this.submitButton.obj).html(this.submitButton.html);
                    }
                }
            }
        }
    };

    this.bindEvent = function bindEvent(element, eventName, eventHandler) {
        if (element.addEventListener){
            element.addEventListener(eventName, eventHandler, true);
        } else if (element.attachEvent) {
            element.attachEvent('on' + eventName, eventHandler);
        }
    };

    var startTime = new Date();
    this.bindEvent(this.mwIframe, "load", function() {
        mwPayframe.div.style.display = 'block';
        var loadTime = Date.now() - startTime;
        //console.log("Iframe loaded: " + loadTime);
        setTimeout(function() {
            var msg = mwPayframe.formatStyleMessage();
            msg.messageType = 'style';
            msg.submitUrl = mwPayframe.submitUrl;
            var amountElement = document.getElementById(mwPayframe.amountElementID);
            if(amountElement != null) {
                //add amount validation
                var amountRegex = new RegExp(/^\d*(\.\d{2})$/);
                if (!amountRegex.test(amountElement.value) || amountElement.value < '0.01') {
                    console.log('Invalid amount');
                } else {
                    msg.amount = amountElement.value;
                    mwPayframe.bindEvent(amountElement, 'change', function() {
                        var amount = amountElement.value;
                        var amountRegex = new RegExp(/^\d*(\.\d{2})$/);
                        if (!amountRegex.test(amount) || amount < '0.01') {
                            console.log('Invalid amount');
                            return;
                        }
                        //validate amount
                        var msg = {
                            messageType: 'action',
                            action : 'amountUpdated',
                            'amount': amount,
                        };
                        var jsnMsg = JSON.stringify(msg);
                        mwPayframe.sendMessage(jsnMsg);
                    });
                }
            }
            var jsnMsg = JSON.stringify(msg);
            mwPayframe.sendMessage(jsnMsg);
        }, 20);

    });

    this.reset = function reset() {
        if(!mwPayframe.active) {
            console.log('Unable to reset, payframe disabled.');
            return;
        }
        //console.log('reseting payframe');
        if(this.demoMode && this.jqueryLoaded && this.submitButton.obj && this.submitButton.html) {
            this.submitButton.obj.removeClass('mw-success');
            this.submitButton.obj.removeClass('mw-loading');
            this.submitButton.obj.html(this.submitButton.html);
        }
        mwPayframe.responseCode = null;
        mwPayframe.responseMessage = null;
        mwPayframe.tokenStatus = 'NO_TOKEN';
        mwPayframe.payframeKey = null;
        mwPayframe.payframeToken = null;
        mwPayframe.cardID = null;
        mwPayframe.cardType = null;
        mwPayframe.cardBin = null;
        mwPayframe.cardValid = false;
        mwPayframe.surcharge = null;
        mwPayframe.customerData = null;
        var msg = {
            messageType: 'action',
            action : 'reset'
        };
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
    }

    this.disable = function disable() {
        mwPayframe.active = false;
        var msg = {
            messageType: 'action',
            action : 'disablePayframe',
            mwMethod: method
        };
        if(style && style.customPayframe) {
            msg.customPayframe = true;
        }
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
        var invisInput = document.createElement('input');
        invisInput.style.width = '0px';
        invisInput.style.height = '0px';
        invisInput.style.margin = '0px';
        invisInput.style.padding = '0px';
        invisInput.style.border = '0px';
        invisInput.style.opacity = '0px';
        this.div.appendChild(invisInput);
        invisInput.focus();
        invisInput.blur();
        this.div.removeChild(invisInput)
    }

    this.resetSize = function resetSize() {
        if(!mwPayframe.active || (style && style.customPayframe)) {
            if(!mwPayframe.active) {
                console.log('Unable to resetsize, payframe disabled.');
            }
            return;
        }
        var msg = {
            messageType: 'action',
            action : 'resetSize'
        };
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
    }

    this.refresh = function refresh() {
        if(!mwPayframe.active || (style && style.customPayframe)) {
            if(!mwPayframe.active) {
                console.log('Unable to refresh, payframe disabled.');
            }
            return;
        }
        var msg = mwPayframe.formatStyleMessage();
        msg.messageType = 'action';
        msg.action = 'refresh';
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
    }

    this.validatePayframe = function validatePayframe() {
        if(!mwPayframe.active) {
            console.log('Unable to validatePayframe, payframe disabled.');
            return;
        }
        var msg = {
            messageType: 'action',
            action : 'validatePayframe'
        };
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
    }

    this.sendPostMessage = function sendPostMessage(funcName, funcData) {
        if(!mwPayframe.active || !style || !style.customPayframe || !funcName) {
            if(!mwPayframe.active) {
                console.log('Unable to sendPostMessage, payframe disabled.');
            }
            return;
        }
        var msg = {
            messageType: 'action',
            action : 'callCustomFunction',
            'funcName': funcName,
            'funcData': funcData,
        };
        var jsnMsg = JSON.stringify(msg);
        mwPayframe.sendMessage(jsnMsg);
    }

    this.formatStyleMessage = function(messageType, action) {
        var msg;
        if(style) {
            msg = {
                mwMethod: method,
                mwWidth: (style.width)? width: width,
                mwHeight: (style.height)? height: height,
                mwFormLayout: (style.formLayout)? style.formLayout: '0',
                mwFieldHeight: (style.fieldHeight)? style.fieldHeight: '',
                mwBorder : (style.border)? style.border:'',
                mwBorderBottom : (style.borderBottom)? style.borderBottom:'',
                mwBorderColor : (style.borderColor)? style.borderColor:'',
                mwBorderLeft : (style.borderLeft)? style.borderLeft:'',
                mwBorderRadius : (style.borderRadius)? style.borderRadius:'',
                mwBorderRight : (style.borderRight)? style.borderRight:'',
                mwBorderTop : (style.borderTop)? style.borderTop:'',
                mwTextColor : (style.textColor)? style.textColor:'',
                mwBackgroundColor : (style.backgroundColor)? style.backgroundColor:'',
                mwErrorTextColor: (style.errorTextColor)? style.errorTextColor:'',
                mwErrorBackgroundColor: (style.errorBackgroundColor)? style.errorBackgroundColor:'',
                mwErrorBorderColor: (style.errorBorderColor)?style.errorBorderColor:'',
                mwDirection: (style.direction)? style.direction:'',
                mwFont: (style.font)? style.font:'',
                mwFontFamily: (style.fontFamily)? style.fontFamily:'',
                mwFontSize: (style.fontSize)? style.fontSize:'',
                mwFontSizeAdjust: (style.fontSizeAdjust)? style.fontSizeAdjust:'',
                mwFontStretch: (style.fontStretch)? style.fontStretch:'',
                mwFontStyle: (style.fontStyle)? style.fontStyle:'',
                mwFontVariant: (style.fontVariant)? style.fontVariant:'',
                mwFontVariantAlternates: (style.fontVariantAlternates)? style.fontVariantAlternates:'',
                mwFontVariantCaps: (style.fontVariantCaps)? style.fontVariantCaps:'',
                mwFontVariantEastAsian: (style.fontVariantEastAsian)? style.fontVariantEastAsian:'',
                mwFontVariantLigatures: (style.fontVariantLigatures)? style.fontVariantLigatures:'',
                mwFontVariantNumeric: (style.fontVariantNumeric)? style.fontVariantNumeric:'',
                mwFontWeight: (style.fontWeight)? style.fontWeight:'',
                mwLetterSpacing: (style.letterSpacing)? style.letterSpacing:'',
                mwLineHeight: (style.lineHeight)? style.lineHeight:'',
                mwMargin: (style.margin)? style.margin:'',
                mwMarginBottom: (style.marginBottom)? style.marginBottom:'',
                mwMarginLeft: (style.marginLeft)? style.marginLeft:'',
                mwMarginRight: (style.marginRight)? style.marginRight:'',
                mwMarginTop: (style.marginTop)? style.marginTop:'',
                mwOpacity: (style.opacity)? style.opacity:'',
                mwOutline: (style.outline)? style.outline:'',
                mwOutlineColor: (style.outlineColor)? style.outlineColor:'',
                mwOutlineStyle: (style.outlineStyle)? style.outlineStyle:'',
                mwOutlineWidth: (style.outlineWidth)? style.outlineWidth:'',
                mwPadding: (style.padding)? style.padding:'',
                mwPaddingBottom: (style.paddingBottom)? style.paddingBottom:'',
                mwPaddingLeft: (style.paddingLeft)? style.paddingLeft:'',
                mwPaddingRight: (style.paddingRight)? style.paddingRight:'',
                mwPaddingTop: (style.paddingTop)? style.paddingTop:'',
                mwTextShadow: (style.textShadow)? style.textShadow:'',
                mwTransition: (style.transition)? style.transition:'',
                mwMozAppearance: (style.mozAppearance)? style.mozAppearance:'',
                mwMozOsxFontSmoothing: (style.mozOsxFontSmoothing)? style.mozOsxFontSmoothing:'',
                mwMozTapHighlightColor: (style.mozTapHighlightColor)? style.mozTapHighlightColor:'',
                //mwMozTransition: (style.mozTransition)? style.mozTransition:'',
                mwWebkitAppearance: (style.webkitAppearance)? style.webkitAppearance:'',
                mwWebkitFontSmoothing: (style.webkitFontSmoothing)? style.webkitFontSmoothing:'',
                mwWebkitTapHighlightColor: (style.webkitTapHighlightColor)? style.webkitTapHighlightColor:'',
                //mwWebkitTransition: (style.webkitTransition)? style.webkitTransition:'',
                mwCardImageAlignment: (style.cardImageAlignment)? style.cardImageAlignment:'',
                mwCardImageSize: (style.cardImageSize)? style.cardImageSize: '',
                mwCardImageAnimation: (style.cardImageAnimation)? style.cardImageAnimation:'',
                mwCardIconAlignment: (style.cardIconAlignment)?style.cardIconAlignment:'',
                mwFieldAutoTabbing: (style.fieldAutoTabbing)? style.fieldAutoTabbing: '',
                mwCardTypeDisplay: (style.cardTypeDisplay)? style.cardTypeDisplay: '',
                mwCardTypeWidth: (style.cardTypeWidth)? style.cardTypeWidth: '',
                mwErrorDisplayMode: (style.errorDisplayMode)? style.errorDisplayMode: '',
                mwCardIconSet: (style.cardIconSet)?style.cardIconSet: '',
                mwPayframeHeightScaling: (style.payframeHeightScaling)?style.payframeHeightScaling: '',
                mwFontSrc: (style.fontSrc)?style.fontSrc:'',
                mwFieldValidStyle: (style.fieldValidStyle)?style.fieldValidStyle:'',
                mwFieldErrorStyle: (style.fieldErrorStyle)?style.fieldErrorStyle:'',
                mwFieldFocusStyle: (style.fieldFocusStyle)?style.fieldFocusStyle: '',
                mwPlaceholderText: (style.placeholderText)?style.placeholderText:'',
                mwPlaceholderStyle: (style.placeholderStyle)?style.placeholderStyle: '',
                //mwPlaceholderInputEnteredStyle: (style.placeholderInputEnteredStyle)?style.placeholderInputEnteredStyle: '',
                mwPlaceholderValidStyle: (style.placeholderValidStyle)?style.placeholderValidStyle:'',
                mwPlaceholderErrorStyle: (style.placeholderErrorStyle)?style.placeholderErrorStyle:'',
                mwPlaceholderFocusStyle: (style.placeholderFocusStyle)?style.placeholderFocusStyle: '',
                mwCustomErrorMessages: (style.customErrorMessages)?style.customErrorMessages: '',
                mwErrorLabelStyle: (style.errorLabelStyle)?style.errorLabelStyle: '',
                mwFieldLabelText: (style.fieldLabelText)?style.fieldLabelText:'',
                mwFieldLabelStyle: (style.fieldLabelStyle)?style.fieldLabelStyle:'',
                //Custom Payframe Fields
                mwCustomPayframe: (style.customPayframe)?style.customPayframe:'',
                mwPayframeID: (style.payframeID)?style.payframeID:'',
                mwCustomFieldIds: (style.customFieldIds)?style.customFieldIds:'',
            };
        } else {
            msg = {
                mwMethod: method
            };
        }
        return msg;
    }


    this.sendMessage = function(msg) {
        //Check iframe hasn't been destroyed
        if(this.mwIframe.contentWindow) {
            this.mwIframe.contentWindow.postMessage(msg, this.iframeSrc);
        }
    };
    this.addInputToPayform = function(name,value){
        var newInput = document.createElement("input");
        newInput.type = 'hidden';
        newInput.name = name;
        newInput.value = value;
        payform.appendChild(newInput);
    };
    this.bindEvent(window, 'message', function (e) {
        //console.log("recieved event from: " + e.origin);
        if(e.origin.indexOf('merchantwarrior') >= 0 && e.data) {
            var payframeData = JSON.parse(e.data);
            if(payframeData.mwOutput && payframeData.mwMessage && mwPayframe.active) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            } else {
                return;
            }
        } else {
            return;
        }
        if(payframeData.mwOutput == '0'){
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage;
            mwPayframe.method = payframeData.mwMethod;
            if(payframeData.mwCustomerData) mwPayframe.customerData = payframeData.mwCustomerData;
            if(payframeData.mwMethod == 'addCard') {
                mwPayframe.cardID = payframeData.mwCardID;
                if(payframeData.mwCardKey)mwPayframe.cardKey = payframeData.mwCardKey;
                mwPayframe.mwCallback(mwPayframe.cardID);
            } else if(payframeData.mwMethod == 'getPayframeToken') {
                mwPayframe.payframeKey = payframeData.mwKey;
                mwPayframe.payframeToken = payframeData.mwToken;
                mwPayframe.tokenStatus = 'HAS_TOKEN';
                mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
            } else {
                mwPayframe.tokenStatus = 'NO_TOKEN';
                mwPayframe.payframeKey = 'error';
                mwPayframe.payframeToken = 'error';
                mwPayframe.cardID = 'error';
                mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
            }
        } else if(payframeData.mwOutput == '-1') {
            //Invalid credentials or error loading custom payframe
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage;
            mwPayframe.tokenStatus = 'NO_TOKEN';
            mwPayframe.payframeKey = 'error';
            mwPayframe.payframeToken = 'error';
            mwPayframe.cardID = 'error';
            mwPayframe.customerData = null;
            mwPayframe.loaded();
            mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
        } else if(payframeData.mwOutput == '-2') {
            //validation failed
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage;
            mwPayframe.tokenStatus = 'NO_TOKEN';
            mwPayframe.payframeKey = 'error';
            mwPayframe.payframeToken = 'error';
            mwPayframe.cardID = 'error';
            mwPayframe.customerData = null;
            if(payframeData.mwPayframeMode && payframeData.mwPayframeMode == 'custom') {
                //validation of custom layout failed, stop merchant from processing more
                mwPayframe.responseMessage += ". Payframe disabled.";
                console.log('disabling payframe');
                mwPayframe.disable();
            }
            mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
        } else if(payframeData.mwOutput == '-3') {
            //validation failed
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage;
            mwPayframe.tokenStatus = 'NO_TOKEN';
            mwPayframe.payframeKey = 'error';
            mwPayframe.payframeToken = 'error';
            mwPayframe.cardID = 'error';
            mwPayframe.customerData = null;
            mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
        } else if(payframeData.mwOutput == 'payframeLoaded') {
            if(typeof mwPayframe.loaded == 'function') {
                mwPayframe.loaded();
            }
        } else if(payframeData.mwOutput == 'scalingDimensions') {
            if(payframeData.mwPayframeHeight) {
                var payframe = document.getElementById(mwPayframe.mwIframe.id);
                //console.log('payframe height: ' + payframeData.mwPayframeHeight);
                payframe.height = payframeData.mwPayframeHeight;
            }
        } else if(payframeData.mwOutput == 'cardTypeUpdated') {
            if(payframeData.mwCardType) {
                mwPayframe.cardType = payframeData.mwCardType;
                mwPayframe.cardBin = payframeData.mwCardBin;
                mwPayframe.cardValid = payframeData.mwCardValid;
                mwPayframe.mwCardTypeUpdated(payframeData.mwCardType, payframeData.mwCardBin, payframeData.mwCardValid);
            }
        } else if(payframeData.mwOutput == 'surchargeUpdated') {
            if(payframeData.mwSurcharge) {
                mwPayframe.surcharge = payframeData.mwSurcharge;
                mwPayframe.mwSurchargeUpdated(payframeData.mwMessage, payframeData.mwSurcharge);
            } else {
                mwPayframe.mwSurchargeUpdated(payframeData.mwMessage);
            }
        } else if(payframeData.mwOutput == 'payframeValidated') {
            //call payframeValidated
            mwPayframe.mwPayframeValidated(payframeData.mwPayframeValid, payframeData.mwMessage);
        } else if(payframeData.mwOutput == 'callCustomFunction') {
            //console.log('custom func');
            if(payframeData.mwFuncName && typeof window[payframeData.mwFuncName] === 'function') {
                if(payframeData.mwFuncData) {
                    window[payframeData.mwFuncName](payframeData.mwFuncData);
                } else {
                    window[payframeData.mwFuncName]();
                }
            }
        } else if(payframeData.mwOutput == 'payframeLoadError') {
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage + ". Payframe disabled.";
            mwPayframe.tokenStatus = 'NO_TOKEN';
            mwPayframe.payframeKey = 'error';
            mwPayframe.payframeToken = 'error';
            mwPayframe.cardID = 'error';
            mwPayframe.customerData = null;
            mwPayframe.disable();
            mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
        } else {
            //console.log("error");
            mwPayframe.responseCode = payframeData.mwOutput;
            mwPayframe.responseMessage = payframeData.mwMessage;
            //mwPayframe.method = 'error';
            mwPayframe.tokenStatus = 'NO_TOKEN';
            mwPayframe.payframeKey = 'error';
            mwPayframe.payframeToken = 'error';
            mwPayframe.cardID = 'error';
            mwPayframe.customerData = null;
            mwPayframe.mwCallback(mwPayframe.tokenStatus, mwPayframe.payframeToken, mwPayframe.payframeKey);
        }
        //mwPayframe.mwCallback();
    });
    this.submitPayframe = function submitPayframe(button){
        if(mwPayframe.tokenStatus === 'NO_TOKEN' && mwPayframe.active){
            if(this.demoMode && this.jqueryLoaded) {
                button = $(button);
                this.submitButton.obj = button;
                this.submitButton.width = button.css('width');
                this.submitButton.height = button.css('height');
                this.submitButton.html = button.html();
                //Create animation classes
                var animStyleExists = false;
                $(document.styleSheets).each(function(index, val) {
                    var rules;
                    if(val.cssRules){rules = val.cssRules;}
                    if(val.rules){rules = val.rules;}
                    $(rules).each(function(index, rule) {
                        if(rule.selectorText == '.mw-circle-loader') {animStyleExists = true;}
                    });
                });
                if(!animStyleExists) {
                    var buttonID = button[0].id;
                    var buttonColor = "#24C385";
                    var successColor = "white";
                    var loaderSize = button.height() * 0.75;//height - padding
                    var checkHeight = loaderSize/2;
                    var checkWidth = checkHeight/2;
                    var checkLeft = loaderSize/6;// + loaderSize/12;
                    var checkThickness = '3px';
                    var animClass = document.createElement('style');
                    animClass.type = 'text/css';
                    var animStyle = '';
                    if(buttonID) {
                        animStyle += "#" + buttonID + ".mw-loading {width: " + this.submitButton.width + ";height: " + this.submitButton.height +";}"
                            + "#" + buttonID + ".mw-success {border-color: " + buttonColor + ";background-color: " + buttonColor + ";width: " + this.submitButton.width + ";height: "+ this.submitButton.height +";}";
                    } else {
                        animStyle += ".mw-loading {width: " + this.submitButton.width + ";height: " + this.submitButton.height +";}"
                            + " .mw-success {border-color: " + buttonColor + ";background-color: " + buttonColor + ";width: " + this.submitButton.width + ";height: "+ this.submitButton.height +";}";
                    }
                    animStyle += " .mw-circle-loader {border: 1px solid rgba(0, 0, 0, 0.2);border-left-color: "+ successColor +";"
                        + "animation: loader-spin 1.2s infinite linear;position: relative;display: inline-block;border-radius: 50%;width: " + loaderSize +"px;height: "+ loaderSize +"px;}"
                        + " .mw-load-complete {-webkit-animation: none;animation: none;border-color: "+ successColor +";transition: border 500ms ease-out;}"
                        + " .mw-checkmark {display: none;}"
                        + " .mw-checkmark.draw:after {animation-duration: 800ms;animation-timing-function: ease;animation-name: checkmark;transform: scaleX(-1) rotate(135deg);}"
                        + " .mw-checkmark:after {opacity: 1;height: "+ checkHeight +"px;width: "+ checkWidth +"px;transform-origin: left top;border-right: "+ checkThickness +" solid "+ successColor +";"
                        + "border-top: "+ checkThickness +" solid "+ successColor +";content: '';left: "+ checkLeft +"px;top: "+ checkHeight +"px;position: absolute;}"
                        + " @keyframes loader-spin {0% {transform: rotate(0deg);}100% {transform: rotate(360deg);}}"
                        + " @keyframes checkmark {0% {height: 0;width: 0;opacity: 1;}20% {height: 0px;width: "+ checkWidth +"px;opacity: 1;}"
                        + "40% {height: "+ checkHeight +"px;width: "+ checkWidth +"px;opacity: 1;}100% {height: "+ checkHeight +"px;width: "+ checkWidth +"px;opacity: 1;}}";
                    animClass.innerHTML = animStyle;
                    document.getElementsByTagName('head')[0].appendChild(animClass);
                }
                //replace submit html
                button.removeClass('mw-success');
                button.addClass('mw-loading');
                button.html('<div class="mw-circle-loader"><div class="mw-checkmark draw"></div></div>');
            }
            var msg = {
                messageType: 'action',
                submitUrl: this.submitUrl,
                method: this.method,
                action : 'submit'
            };
            if(typeof this.accessToken !== 'undefined') {
                msg.accessToken = this.accessToken;
            } else {
                msg.merchantUUID = this.merchantUUID;
                msg.apiKey = this.apiKey;
            }
            var jsnMsg = JSON.stringify(msg);
            mwPayframe.sendMessage(jsnMsg);
            mwPayframe.tokenStatus = 'PENDING';
            return true;
        }
        if(!mwPayframe.active) {
            console.log('Unable to submitPayframe, payframe disabled.');
        }
        return false;
    };
}

function tdsCheck(firstPar, _apikey, _tdsDivId, _submitUrl, _style) {
    let accessToken, uuid, apikey, tdsDivId, submitUrl, style;
    if(typeof firstPar === 'object') {

        if(typeof firstPar.accessToken !== 'undefined'){accessToken = firstPar.accessToken;}
        if(typeof firstPar.tdsDivId !== 'undefined'){tdsDivId = firstPar.tdsDivId;}
        if(typeof firstPar.submitUrl !== 'undefined'){submitUrl = firstPar.submitUrl;}
        if(typeof firstPar.style !== 'undefined'){style = firstPar.style;}

        if(typeof accessToken === 'undefined' || typeof tdsDivId === 'undefined' || typeof submitUrl === 'undefined') {
            throw 'Please initiate tdsCheck as - var tdsCheck = new tdsCheck(payframeData); where tdsCheck contains accessToken, tdsDivId, submitUrl, style(optional)';
        }
    } else {
        uuid = firstPar;
        apikey = _apikey;
        tdsDivId = _tdsDivId;
        submitUrl = _submitUrl;
        style = _style;
        if(!uuid || !apikey || !tdsDivId || !submitUrl){
            throw 'please initiate tdsCheck as - var tdsCheck = new tdsCheck(uuid,apikey,tdsDivId, style(optional)); - tdsDivId is the div you wish the 3ds check to appear in';
            //return false;
        }
    }

    if(document.getElementById(tdsDivId) == null) {
        throw "tdsDivId entered '" + tdsDivId + "' doesn't exist";
    }
    if(submitUrl.toLowerCase() == 'camp') {
        submitUrl = 'https://base.merchantwarrior.com/payframe/';
    } else if(submitUrl.toLowerCase() == 'prod' || submitUrl.toLowerCase() == 'production') {
        submitUrl = 'https://api.merchantwarrior.com/payframe/';
    } else if(typeof submitUrl == 'string' && (submitUrl.toLowerCase().indexOf('merchantwarrior.com') > 0 || submitUrl.toLowerCase().indexOf('merchantwarrior.test') > 0)) {
        submitUrl = submitUrl;
        if(submitUrl.substring(submitUrl.length-1, submitUrl.length) != "/") {
            submitUrl += "/";
        }
    } else {
        submitUrl = 'https://api.merchantwarrior.com/payframe/';
    }
    this.div = document.getElementById(tdsDivId);
    this.mwPayframe = null;
    var tdsCheck = this;
    this.mwCallback = function(){
        throw 'please add a callback function to run when 3ds has been checked - eg tdsCheck.mwCallback = function(){ --code };';
    };
    var customerIP = 'auto';
    function ajaxSuccess(data, status) {
        var responseCode = data.getElementsByTagName("responseCode");
        if(responseCode && responseCode.length > 0 && responseCode[0].childNodes.length > 0) {
            var output = data.getElementsByTagName("responseCode")[0].childNodes[0].nodeValue;
            if(output == 0) {
                var enrolled = data.getElementsByTagName("enrolled");
                if(enrolled && enrolled.length > 0 && enrolled[0].childNodes.length > 0) {
                    enrolled = data.getElementsByTagName("enrolled")[0].childNodes[0].nodeValue;
                } else {
                    enrolled = '';
                }
                if(enrolled == 'Y') {
                    var acsUrl = data.getElementsByTagName("acsURL")[0].childNodes[0].nodeValue;
                    var paReq = data.getElementsByTagName("paReq")[0].childNodes[0].nodeValue;
                    if(style && style.subFrame == true) {
                        if(tdsCheck.mwPayframe == null) {
                            throw 'If you wish to use the tds subFrame, please link the tdsCheck and mwPayframe with tdsCheck.link(mwPayframe)';
                        }
                        tdsCheck.mwPayframe.disable();
                        var subWidth = '500px';
                        var subHeight = '600px';
                        if(style.width) subWidth = style.width;
                        if(style.height) subHeight = style.height;
                        var msg = {
                            messageType: 'action',
                            action : 'makeTDS',
                            width: subWidth,
                            height: subHeight,
                            submitUrl: submitUrl,
                            paReq: paReq,
                            acsUrl: acsUrl
                        };
                        if(typeof accessToken !== 'undefined') {
                            msg.accessToken = accessToken;
                        } else {
                            msg.merchantUUID = uuid;
                            msg.apiKey = apikey;
                        }
                        var jsnMsg = JSON.stringify(msg);
                        tdsCheck.mwPayframe.sendMessage(jsnMsg);
                    } else {
                        tdsCheck.tdsIframe = document.createElement("iframe");
                        var divisor = '?';
                        if(acsUrl.indexOf('?') >= 0) {
                            divisor = '&';
                        }
                        tdsCheck.tdsIframe.src = 'about:blank';//acsUrl + divisor + 'paReq=' + paReq;
                        tdsCheck.tdsIframe.id = 'mwTDSIframe';
                        tdsCheck.tdsIframe.name = 'mwTDSIframe';
                        var width = '500px'
                        var height = '500px';
                        var childNodes = tdsCheck.div.getElementsByTagName('iframe');
                        if(childNodes && childNodes.length == 1) {
                            width = childNodes[0].width;
                            height = childNodes[0].height;
                            tdsCheck.div.removeChild(childNodes[0]);
                        }
                        if(style) {
                            if(style.width) width = style.width;
                            if(style.height) height = style.height;
                        }
                        tdsCheck.tdsIframe.width = width;
                        tdsCheck.tdsIframe.height = height;
                        tdsCheck.tdsIframe.style.display = 'none';
                        tdsCheck.div.appendChild(tdsCheck.tdsIframe);
                        document.getElementById(tdsCheck.tdsIframe.id).setAttribute('allowtransparency','true');
                        document.getElementById(tdsCheck.tdsIframe.id).setAttribute('frameborder',0);

                        tdsCheck.tdsDocument = tdsCheck.tdsIframe.contentWindow.document;
                        var submitForm = tdsCheck.tdsDocument.createElement("form");
                        submitForm.setAttribute('target', tdsCheck.tdsIframe.id);
                        submitForm.setAttribute('method', 'Post');
                        submitForm.setAttribute('action', acsUrl);
                        var hiddenField = tdsCheck.tdsDocument.createElement('input');
                        hiddenField.setAttribute('name', 'PaReq');
                        hiddenField.setAttribute('value', paReq);
                        submitForm.appendChild(hiddenField);
                        hiddenField = tdsCheck.tdsDocument.createElement('input');
                        hiddenField.setAttribute('name', 'TermUrl');
                        let termUrl = submitUrl + '3dsecure_response.php';
                        if(typeof accessToken !== 'undefined') {
                            termUrl += '?accessToken='+accessToken;
                        } else {
                            termUrl += '?merchantUUID=' + uuid + '&apiKey=' + apikey;
                        }
                        hiddenField.setAttribute('value', termUrl);
                        submitForm.appendChild(hiddenField);
                        tdsCheck.tdsDocument.body.appendChild(submitForm);
                        tdsCheck.bindEvent(tdsCheck.tdsIframe, 'load', function() {
                            tdsCheck.tdsIframe.style.display = 'block';
                            if(typeof tdsCheck.loaded == 'function') {
                                tdsCheck.loaded();
                            }
                        });
                        submitForm.submit();
                    }

                } else if(enrolled == 'N') {
                    var eci = data.getElementsByTagName("eci")[0].childNodes[0].nodeValue;
                    tdsCheck.mwTDSMessage = 'Cardholder not enrolled in 3DS';
                    tdsCheck.liabilityShifted = false;
                    tdsCheck.mwTDSResult = '';
                    tdsCheck.mwTDSToken = '';
                    tdsCheck.mwTDSEnrolled = enrolled;
                    tdsCheck.mwEci = eci;
                    tdsCheck.mwXid = '';
                    tdsCheck.mwCavv = '';
                    if(typeof tdsCheck.loaded == 'function') {
                        tdsCheck.loaded();
                    }
                    tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
                } else {
                    tdsCheck.mwTDSMessage = 'Cardholder not enrolled in 3DS';
                    tdsCheck.liabilityShifted = false;
                    tdsCheck.mwTDSResult = '';
                    tdsCheck.mwTDSToken = '';
                    tdsCheck.mwTDSEnrolled = enrolled;
                    tdsCheck.mwEci = '';
                    tdsCheck.mwXid = '';
                    tdsCheck.mwCavv = '';
                    if(typeof tdsCheck.loaded == 'function') {
                        tdsCheck.loaded();
                    }
                    tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
                }
            }
            else {
                var responseMessage = data.getElementsByTagName("responseMessage")[0].childNodes[0].nodeValue;
                tdsCheck.mwTDSMessage = responseMessage;
                tdsCheck.liabilityShifted = false;
                tdsCheck.mwTDSResult = 'error';
                tdsCheck.mwTDSToken = '';
                tdsCheck.mwTDSEnrolled = '';
                tdsCheck.mwEci = '';
                tdsCheck.mwXid = '';
                tdsCheck.mwCavv = '';
                if(typeof tdsCheck.loaded == 'function') {
                    tdsCheck.loaded();
                }
                tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
            }
        } else {
            tdsCheck.mwTDSMessage = status;
            tdsCheck.liabilityShifted = false;
            tdsCheck.mwTDSResult = 'error';
            tdsCheck.mwTDSToken = '';
            tdsCheck.mwTDSEnrolled = '';
            tdsCheck.mwEci = '';
            tdsCheck.mwXid = '';
            tdsCheck.mwCavv = '';
            if(typeof tdsCheck.loaded == 'function') {
                tdsCheck.loaded();
            }
            tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
        }


    }
    function ajaxFail(xml, status, error) {
        tdsCheck.mwTDSMessage = status + ' - ' + error;
        tdsCheck.liabilityShifted = false;
        tdsCheck.mwTDSResult = 'error';
        tdsCheck.mwTDSToken = '';
        tdsCheck.mwTDSEnrolled = '';
        tdsCheck.mwEci = '';
        tdsCheck.mwXid = '';
        tdsCheck.mwCavv = '';
        tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
    }

    this.checkTDS = function checkTDS(payframeToken, mwKey, transactionAmount, transactionCurrency, transactionProduct) {
        if(!payframeToken || !mwKey || !transactionAmount || !transactionCurrency || !transactionProduct) {
            throw 'please call checkTDS with the details of the transaction - tdsCheck.checkTDS(payframeToken, mwKey, transactionAmount, transactionCurrency, transactionProduct)';
        }
        if(typeof tdsCheck.loading == 'function') {
            tdsCheck.loading();
        }
        var tdsData = {
            'method': 'checkEnrollment',
            'transactionAmount': transactionAmount,
            'transactionCurrency': transactionCurrency,
            'transactionProduct': transactionProduct,
            'customerIP': customerIP,
            'payframeToken': payframeToken,
            'payframeKey': mwKey
        }
        if(typeof accessToken !== 'undefined') {
            tdsData['accessToken'] = accessToken;
        } else {
            tdsData['merchantUUID'] = uuid;
            tdsData['apiKey'] = apikey;
        }
        //ajax to checkEnrolment
        if(typeof jQuery != 'undefined' && $ == jQuery) {
            $.ajax({
                type: 'POST',
                url: submitUrl,
                data: tdsData,
                dataType: 'xml',
                success:function(data, status) {
                    ajaxSuccess(data, status);
                },
                error: function(xml, status, error) {
                    ajaxFail(xml, status, error);
                }
            });
        } else {
            var xhttp = new XMLHttpRequest();
            xhttp.onreadystatechange = function() {
                if(this.readyState == 4) {//Request done
                    if (this.status == 200) {
                        /*console.log('http success status');
                        console.log(xhttp.status);
                        console.log('http success statusText');
                        console.log(xhttp.statusText);
                        console.log('http success responseXML');
                        console.log(xhttp.responseXML);*/
                        //document.getElementById("demo").innerHTML = this.responseText;
                        ajaxSuccess(xhttp.responseXML, xhttp.status + xhttp.statusText);
                    } else {
                        /*console.log('http error status');
                        console.log(xhttp.status);
                        console.log('http error statusText');
                        console.log(xhttp.statusText);
                        console.log('http error responseXML');
                        console.log(xhttp.responseXML);
                        console.log('http error readyState');
                        console.log(xhttp.readyState);
                        console.log('http error responseText');
                        console.log(xhttp.responseText);*/
                    }
                }
            };
            xhttp.open("POST", submitUrl, true);
            xhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            var requestString = '';
            Object.getOwnPropertyNames(tdsData).forEach(function(element) {
                if(requestString)requestString += '&';
                requestString += element +'=' + tdsData[element];
            });
            //console.log(requestString);
            xhttp.send(requestString);
        }

    }
    this.bindEvent = function bindEvent(element, eventName, eventHandler) {
        if (element.addEventListener){
            element.addEventListener(eventName, eventHandler, true);
        } else if (element.attachEvent) {
            element.attachEvent('on' + eventName, eventHandler);
        }
    };

    this.loading = null;
    this.loaded = null;
    this.link = function link(mwPayframe) {
        if(!mwPayframe) {return;}
        tdsCheck.mwPayframe = mwPayframe;
        if(this.loading == null && mwPayframe.loading) {
            this.loading = mwPayframe.loading;
        }
        if(this.loaded == null && mwPayframe.loaded) {
            this.loaded = mwPayframe.loaded;
        }
    };

    this.destroy = function destroy() {
        if(tdsCheck.tdsIframe) {
            tdsCheck.div.removeChild(document.getElementById(tdsCheck.tdsIframe.id));
        }
    };
    this.bindEvent(window, 'message', function (e) {
        if(e.origin.indexOf('merchantwarrior') >= 0 && e.data) {
            var tdsCheckData = JSON.parse(e.data);
            if(tdsCheckData.mwTDSResult) {
                e.stopPropagation();
                e.stopImmediatePropagation();
            } else {
                return;
            }
        } else {
            return;
        }
        if(tdsCheckData.mwTDSResult == 'Y' || (tdsCheckData.mwTDSResult == 'N' && tdsCheckData.mwEci == '6') ||
            (tdsCheckData.mwTDSResult == 'A' && (tdsCheckData.mwEci == '6' || tdsCheckData.mwEci == '1'))) {
            tdsCheck.mwTDSMessage = '3DS Successful';
            tdsCheck.liabilityShifted = true;
            tdsCheck.mwTDSResult = tdsCheckData.mwTDSResult;
            tdsCheck.mwTDSToken = tdsCheckData.mwTDSToken;
            tdsCheck.mwTDSEnrolled = 'Y';
            tdsCheck.mwEci = tdsCheckData.mwEci;
            tdsCheck.mwXid = tdsCheckData.mwXid;
            tdsCheck.mwCavv = tdsCheckData.mwCavv;
            tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
        } else if(tdsCheckData.mwTDSResult == 'scaleTDSPayframeDimensions' && tdsCheck.mwPayframe) {
            if(tdsCheckData.mwTDSPayframeHeight && tdsCheckData.mwTDSPayframeWidth) {
                tdsCheck.mwPayframe.mwIframe.width = tdsCheckData.mwTDSPayframeWidth;
                tdsCheck.mwPayframe.mwIframe.height = tdsCheckData.mwTDSPayframeHeight;
            }
        } else {
            tdsCheck.mwTDSMessage = '3DS Failed';
            tdsCheck.liabilityShifted = false;
            tdsCheck.mwTDSResult = tdsCheckData.mwTDSResult;
            tdsCheck.mwTDSToken = tdsCheckData.mwTDSToken;
            tdsCheck.mwTDSEnrolled = 'Y';
            tdsCheck.mwEci = tdsCheckData.mwEci;
            tdsCheck.mwXid = tdsCheckData.mwXid;
            tdsCheck.mwCavv = tdsCheckData.mwCavv;
            tdsCheck.mwCallback(tdsCheck.liabilityShifted, tdsCheck.mwTDSToken);
        }
    });
}
