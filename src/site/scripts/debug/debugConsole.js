if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {

    (
        function() {
            "use strict";

            String.prototype.replaceAll = function(search, replacement) {
                var target = this;
                return target.replace(new RegExp(search, 'g'), replacement);
            };

            var baseLogFunction = console.log;

            var preLoadMessages = [];
            console.log = function() {
                baseLogFunction.apply(console, arguments);
                preLoadMessages.push(arguments);
            }

            window.onload = (e) => {
                var mylogNode = document.createElement("div");
                mylogNode.style = "position: fixed;      z-index: 16777200;      bottom: 0;      right: 0;      width: 400px;      height: 300px;      overflow: scroll;      background-color: #151515;      color: white;          -webkit-box-shadow: -5px -5px 20px 0px rgba(138,0,0,1);      box-shadow: -5px -5px 20px 0px rgba(138,0,0,1);";

                document.body.appendChild(mylogNode);

                console.log = function() {
                    baseLogFunction.apply(console, arguments);
                    var args = Array.prototype.slice.call(arguments);
                    for (var i = 0; i < args.length; i++) {
                        var val = args[i];
                        if (typeof val === 'object')
                            val = JSON.stringify(val, null, '\t').replaceAll('\t', '&nbsp;&nbsp;&nbsp;&nbsp;').replaceAll('\n', '<br/>');
                        var node = createLogNode(val);
                        mylogNode.appendChild(node);
                    }
                }

                function createLogNode(message) {
                    var node = document.createElement("div");
                    node.style = "border: 1px solid rgba(50,50,50, 0.3);";
                    node.innerHTML = message;
                    return node;
                }

                for (let message of preLoadMessages)
                    console.log(...message);
                preLoadMessages = null;

                window.onerror = function(message, url, linenumber) {
                    console.log("JavaScript error: " + message + " on line " +
                        linenumber + " for " + url);
                };
            };
        })();
}