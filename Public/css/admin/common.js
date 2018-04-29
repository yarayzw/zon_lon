 var deleteAll = document.getElementById('deleteAll'); // 删除全部按钮  
 var selectInputs = document.getElementsByClassName('check'); // 所有勾选框
 var checkAllInputs = document.getElementsByClassName('check-all') // 全选框  
 for(var i = 0; i < selectInputs.length; i++ ){
        selectInputs[i].onclick = function () {
            if (this.className.indexOf('check-all') >= 0) { //如果是全选，则吧所有的选择框选中
                for (var j = 0; j < selectInputs.length; j++) {
                    selectInputs[j].checked = this.checked;
                }
            }
            if (!this.checked) { //只要有一个未勾选，则取消全选框的选中状态
                for (var i = 0; i < checkAllInputs.length; i++) {
                    checkAllInputs[i].checked = false;
                }
            }
        }
    }

 $(function(){
    $('#deleteAll').click(function() {
        $('#delfrom').submit();
    })
 })