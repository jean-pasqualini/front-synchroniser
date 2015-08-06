(function() {
// posEq convenience function from CodeMirror source
function posEq(a, b) {return a.line == b.line && a.ch == b.ch;}

$(function() {
    
    CodeMirror.defineMode('mymode', function (config, parserConfig) {
    return {
        /**
         * @param {CodeMirror.StringStream} stream
         */
        token: function (stream) {
            // check for {
            if (stream.match('{')) {
                // trying to find }

                // if not a char
                if (!stream.eatWhile(/[\w]/)) {
                    return null;
                }

                if (stream.match('}')) {
                    console.log("ok");
                    return 'mymode';
                }
            }

            while (stream.next() && !stream.match('{', false)) {}

            return null;
            
        }
    };
});
    
    var cm = CodeMirror.fromTextArea($("#code")[0],
                                     {mode: "html",
                                      lineNumbers:true,
                                      autofocus:true,
                                      readOnly: true,
                                      extraKeys: {
                                          'Ctrl-,': false,
                                          'Ctrl-.': false
                                      },
                                      foldGutter: true
                                     })
                                     
                                     cm.foldCode(CodeMirror.Pos(0, 0));
                            
    $("#insertfs").click(function() { new FsWidget(cm)});

    // update the convenient display of text
    var updateContents = function(cm) { $("#content").text(cm.getValue())};
    updateContents(cm)
    cm.on("change", updateContents);

    cm.on("cursorActivity", function(cm) {
        if (cm.widgetEnter) {
            // check to see if movement is purely navigational, or if it
            // doing something like extending selection
            var cursorHead = cm.getCursor('head');
            var cursorAnchor = cm.getCursor('anchor');
            if (posEq(cursorHead, cursorAnchor)) {
                cm.widgetEnter();
            }
            cm.widgetEnter = undefined;
        }
    });
})

if ( !Object.create ) {
    // shim for ie8, etc.
    Object.create = function ( o ) {
        function F() {}
        F.prototype = o;
        return new F();
    };
}

function Widget(cm) {
    // the subclass must define this.domNode before calling this constructor
    var _this = this;
    this.cm = cm;
    //cm.replaceSelection("\u2af7"+cm.getSelection()+"\u2af8", "around");
    var from = cm.getCursor("from");
    var to = cm.getCursor("to");
    this.cti = cm.getSelection();
    this.generateDom();
    this.mark = cm.markText(from, to, {replacedWith: this.domNode, clearWhenEmpty: false});

    if (this.enter) {
        CodeMirror.on(this.mark, "beforeCursorEnter", function(e) {
            // register the enter function 
            // the actual movement happens if the cursor movement was a plain navigation
            // but not if it was a backspace or selection extension, etc.
            var direction = posEq(_this.cm.getCursor(), _this.mark.find().from) ? 'left' : 'right';
            cm.widgetEnter = $.proxy(_this, 'enterIfDefined', direction);
        });
    }

    cm.setCursor(to);
    cm.refresh()
}

window.Widget = Widget;

Widget.prototype.enterIfDefined = function(direction) {
    // check to make sure the mark still exists
    if (this.mark.find()) {
        this.enter(direction);
    } else {
        // if we don't do this and do:

        // G = <integer widget>
        //
        // 3x3 table widget 

        // then backspace to get rid of table widget,
        // the integer widget disappears until we type on the first
        // line again.  Calling this refresh takes care of things.
        this.cm.refresh();
    }
}

Widget.prototype.range = function() {
    var find = this.mark.find()
    find.from.ch+=1
    find.to.ch-=1
    return find;
}
Widget.prototype.setText = function(text) {
    var r = this.range()
    this.cm.replaceRange(text, r.from, r.to)
}
Widget.prototype.getText = function() {
    var r = this.range()
    return this.cm.getRange(r.from, r.to)
}

})()