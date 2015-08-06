
function FsWidget(cm)
{
    _this = this;
    
    Widget.apply(this, arguments);
}

FsWidget.prototype = Object.create(Widget.prototype);

FsWidget.prototype.generateDom = function()
{
    this.node = $("<div style='display: inline-block; background: rgba(255, 0, 0, .5); min-width: 10px; min-heigth: 10px;' contenteditable='true'>" + this.cti + "</div>");
    
    this.domNode = this.node[0];
};