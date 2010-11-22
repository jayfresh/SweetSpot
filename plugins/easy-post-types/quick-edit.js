
(function(){

function QuickEdit( id, row, formTemplate, values ) {
  
  this.id = id;
  this.formTemplate = formTemplate;
  this.values = values;
  this.row = row;
  this.formShowing = false;
  
  QuickEdit.objects[id] = this;
};

QuickEdit.objects = {};

QuickEdit.prototype = {
  
  revert : function(){
    // reset the values
    this.form.hide();
    this.row.show();
    this.formShowing = false;
    this.form.remove();
  },
  
  save : function(){
    $('img.waiting', this.form).show();
    
    var data = this.form.serialize();
    // validate
    // AJAX call
    // this.revert
    // update row, this.row
    // update values, this.values
  },
  
  edit : function(){
    this.createForm();
    this.row.hide();
    this.form.show();
    
    for( key in QuickEdit.objects ){
      if( key != this.id && QuickEdit.objects[key].formShowing ){
        QuickEdit.objects[key].revert();
      }
    }
    this.formShowing = true;
  },
  
  createForm : function(){
    
    var self = this;
    this.form = this.formTemplate.clone();
    this.form
      .insertAfter(this.row)
      .toggleClass('alternate', this.row.hasClass('alternate'))
      .find('a.cancel')
        .click(function(event){
          self.revert();
          event.stopPropagation();
          return false;
        })
        .end()
      .find('a.save')
        .click(function(event){
          self.save();
          event.stopPropagation();
          return false;
        });
    
    
    // set the values
    var text = this.form.find( ':text' );
    var checkbox = this.form.find( ':checkbox' );
    var t, c, name;
    for( var i=0; i<text.length; ++i ){
      t = text.eq(i);
      name = t.attr('name');
      t.val(this.values.find('span.'+name).text());
    }
    
    for( var i=0; i<checkbox.length; ++i ){
      c = checkbox.eq(i);
      name = c.attr('name');
      c.attr( 'checked', this.values.find('span.'+name).text() == 'true' );
    }
  }
}


function init_quickedit() {
}


$(document).ready(init_quickedit);
})();