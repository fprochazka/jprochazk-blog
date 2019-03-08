var highlightText = function(query) {
    $("span.list-item.title:contains("+query+")").html(function(_, html) {
        return html.replace(new RegExp(query, 'g'), '<span class="highlight">'+query+'</span>');
    });
    $("div.list-item.content:contains("+query+")").html(function(_, html) {
        return html.replace(new RegExp(query, 'g'), '<span class="highlight">'+query+'</span>');
    });
};

$(document).ready(function(){
    var query = $('span#query').text();
    highlightText(query);
});