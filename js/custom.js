Ext.regModel('forums', {
    idProperty : 'sec',
    fields : [
        {name : 'sec', type: 'string'},
        {name : 'brd', type: 'string'},
        {name : 'name', type : 'string'}]
});
Ext.setup({
	onReady : function() {
    var forumData  = new Ext.data.TreeStore({
        model : 'forums',
        proxy : {
            type : 'ajax',
            url : 'post.php',
            reader : {
                type : 'tree',
                root : 'items'
            }
        }//, autoLoad : true
    });
        /*
		new Ext.Panel({
			fullscreen : true,
			layout : {
				type : 'fit',
				align : 'center',
				pack : 'center'
			},
			items : {
				xtype : 'list',
				store : forumData,
				itemTpl : '<div><strong>{name}</strong></div>'
			},

			dockedItems : [{
						xtype : 'toolbar',
                        title : '分类板块',
						dock : 'top',
						layout : {
							pack : 'center',
							align : 'center'
						},
						defaults : {
							xtype : 'button',
							ui : 'round'
						}
					}]
		});
        */
        new Ext.NestedList({
            fullscreen : true,
            title : '分类板块',
            displayField : 'name',
            /*
            getTitleTextTpl: function() {
                return '{' + this.displayField + '}<tpl if="leaf !== true">/</tpl>';
            },
            getItemTextTpl: function() {
                return '{' + this.displayField + '}<tpl if="leaf !== true">/</tpl>';
            },
            */
            store : forumData
        });
	}
});
