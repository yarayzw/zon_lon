
(function($){
    //收集form数据
    $.fn.toJson = function() {
        var arrayValue = this.serializeArray();
        var json = {};
        $.each(arrayValue, function() {
            var item = this;
            if (json[item["name"]]) {
                json[item["name"]] += "," + item["value"];
            } else {
                json[item["name"]] = item["value"];
            }
        });
        return json;
    };


})(jQuery);

