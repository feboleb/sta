function wcuf_override_console() 
{
    "use strict";
	
    var methods, generateNewMethod, i, j, cur, old, addEvent;

    if ("console" in window) {
        methods = [
            "log", "assert", "clear", "count",
            "debug", "dir", "dirxml", "error",
            "exception", "group", "groupCollapsed",
            "groupEnd", "info", "profile", "profileEnd",
            "table", "time", "timeEnd", "timeStamp",
            "trace", "warn"
        ];

        generateNewMethod = function (oldCallback, methodName) {
            return function () {
                var args;
                alert("called console." + methodName + ", with " + arguments.length + " argument(s)");
                args = Array.prototype.slice.call(arguments, 0);
                Function.prototype.apply.call(oldCallback, console, arguments);
            };
        };

        for (i = 0, j = methods.length; i < j; i++) {
            cur = methods[i];
            if (cur in console) {
                old = console[cur];
                console[cur] = generateNewMethod(old, cur);
            }
        }
    }

    window.onerror = function (msg, url, line) {
        alert("Some of your installed plugin are generating javascript errors that MAY prevent WCUF configurator to work properly.\nFix or disable them.\n\nError type: " + msg + "\nOn script: " + url + "\nLine: " + line + "\n\nTo DISABLE this warning message, go to Option menu and disalbe the 'Show warning message' option.");
		//document.getElementById('wcuf_error_box').innerHTML = "Some of your installed plugin are generating javascript errors that may prevent WCUF configurator to work properly.\nFix or disable them.\n\nError type: " + msg + "\nOn script: " + url + "\nLine: " + line;
    };

	/* var former = console.log;
	console.log = function(msg){
		former(msg);  //maintains existing logging via the console.
		jQuery("#wcuf_error_box").append("<div>" + msg + "</div>");
		former(jQuery("#wcuf_error_box"));
	}

	window.onerror = function(message, url, linenumber) {
		console.log("JavaScript error: " + message + " on line " + 
				linenumber + " for " + url);
	} */
}
wcuf_override_console();
